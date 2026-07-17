<?php

namespace App\Imports;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\SizeUnit;
use App\Models\Unit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use RuntimeException;
use Throwable;

class ProductsImport implements ToCollection, WithHeadingRow, SkipsEmptyRows
{
    public int $createdCount = 0;

    public int $updatedCount = 0;

    public int $skippedCount = 0;

    /**
     * @var array<int, array<string, mixed>>
     */
    public array $errors = [];

    /**
     * Import all spreadsheet rows.
     */
    public function collection(Collection $collection): void
    {
        foreach ($collection as $index => $spreadsheetRow) {
            /*
             * Row 1 contains the spreadsheet headings.
             */
            $excelRowNumber = $index + 2;

            try {
                $row = $this->normalizeRow($spreadsheetRow);

                if ($this->rowIsEmpty($row)) {
                    $this->skippedCount++;

                    continue;
                }

                $this->importRow($row, $excelRowNumber);
            } catch (Throwable $exception) {
                $this->skippedCount++;

                $this->errors[] = [
                    'row' => $excelRowNumber,
                    'code' => $this->readValue(
                        $this->normalizeRow($spreadsheetRow),
                        ['code']
                    ),
                    'message' => $exception->getMessage(),
                ];
            }
        }
    }

    /**
     * Import one product row.
     *
     * @param array<string, mixed> $row
     */
    private function importRow(array $row, int $excelRowNumber): void
    {
        $code = $this->cleanText(
            $this->readValue($row, ['code'])
        );

        $productName = $this->cleanText(
            $this->readValue($row, ['productname', 'product'])
        );

        if ($code === null) {
            throw new RuntimeException(
                "Product Code is missing on Excel row {$excelRowNumber}."
            );
        }

        if ($productName === null) {
            throw new RuntimeException(
                "Product Name is missing for product code {$code}."
            );
        }

        DB::transaction(function () use (
            $row,
            $code,
            $productName,
            $excelRowNumber
        ): void {
            /*
             * Product code is the unique identity used to decide whether
             * a product should be created or updated.
             */
            $product = Product::withTrashed()
                ->whereRaw('LOWER(TRIM(code)) = ?', [
                    mb_strtolower(trim($code)),
                ])
                ->first();

            $isNewProduct = $product === null;

            if ($isNewProduct) {
                $product = new Product();
                $product->code = trim($code);
            } elseif ($product->trashed()) {
                $product->restore();
            }

            $brandName = $this->cleanText(
                $this->readValue($row, ['brand'])
            );

            $categoryName = $this->cleanText(
                $this->readValue($row, ['category'])
            );

            /*
             * Blank values do not remove existing references when
             * an existing product is imported again.
             */
            if ($brandName !== null) {
                $product->brand_id = $this
                    ->findOrCreateReference(
                        Brand::class,
                        $brandName,
                        'BR'
                    )
                    ->getKey();
            } elseif ($isNewProduct) {
                $product->brand_id = $this
                    ->findOrCreateReference(
                        Brand::class,
                        'Unbranded',
                        'BR'
                    )
                    ->getKey();
            }

            if ($categoryName !== null) {
                $product->category_id = $this
                    ->findOrCreateReference(
                        Category::class,
                        $categoryName,
                        'CAT'
                    )
                    ->getKey();
            } elseif ($isNewProduct) {
                $product->category_id = $this
                    ->findOrCreateReference(
                        Category::class,
                        'Uncategorised',
                        'CAT'
                    )
                    ->getKey();
            }

            $packaging = $this->parsePackaging(
                $this->readValue(
                    $row,
                    ['size', 'packsize', 'packaging']
                )
            );

            $sellingUnit = $this->findOrCreateReference(
                Unit::class,
                'Box',
                'UNIT'
            );

            /*
             * Size Units use alias-aware matching.
             *
             * Examples treated as the same unit:
             * ml, ML, Millilitre, Milliliter
             */
            $sizeUnit = $this->findOrCreateSizeUnit(
                $packaging['size_unit_name']
            );

            $price = $this->toDecimal(
                $this->readValue($row, ['price', 'baseprice'])
            );

            if ($price === null) {
                throw new RuntimeException(
                    "Price is missing or invalid for product code {$code}."
                );
            }

            /*
             * QtyType is optional.
             *
             * Missing QtyType defaults to Unit because the current
             * Order2HWS import sheet stores stock as individual units.
             *
             * Supported values:
             * Unit
             * Pack
             */
            $qtyType = $this->normalizeQtyType(
                $this->readValue(
                    $row,
                    ['qtytype', 'quantitytype', 'stocktype']
                )
            );

            $quantity = $this->toDecimal(
                $this->readValue(
                    $row,
                    ['qty', 'quantity', 'stock', 'stockquantity']
                )
            );

            if ($quantity === null || $quantity < 0) {
                throw new RuntimeException(
                    "Qty is missing or invalid for product code {$code}."
                );
            }

            $looseUnitQuantity = $this->toOptionalWholeNumber(
                $this->readValue(
                    $row,
                    ['unitqty', 'looseqty', 'singleqty']
                ),
                "UnitQty for product code {$code}"
            );

            $stockQuantity = $this->calculateStockQuantity(
                quantity: $quantity,
                qtyType: $qtyType,
                qtyPerPack: $packaging['qty_per_pack'],
                looseUnitQuantity: $looseUnitQuantity,
                productCode: $code,
                excelRowNumber: $excelRowNumber,
            );

            $product->fill([
                'name' => $productName,
                'unit_id' => $sellingUnit->getKey(),
                'qty_per_pack' => $packaging['qty_per_pack'],
                'size' => $packaging['size'],
                'size_unit_id' => $sizeUnit->getKey(),
                'can_be_sold_as_unit' => $packaging['qty_per_pack'] > 1,
                'base_price' => $price,
                'stock_quantity' => $stockQuantity,
                'is_active' => true,
            ]);

            $imageFilename = $this->normalizeImageFilename(
                $this->readValue(
                    $row,
                    ['imagepath', 'image', 'imagefile']
                )
            );

            /*
             * Store only the public-disk relative path.
             *
             * Expected physical location:
             * storage/app/public/products/example.png
             *
             * Expected browser URL:
             * /storage/products/example.png
             */
            if ($imageFilename !== null) {
                $product->image = 'products/' . $imageFilename;
            }

            if ($isNewProduct) {
                $product->fill([
                    'special_offer_percent' => 0,
                    'offer_active' => false,
                    'offer_start_at' => null,
                    'offer_end_at' => null,
                    'vat_percent' => 0,
                ]);
            }

            $product->save();

            if ($isNewProduct) {
                $this->createdCount++;
            } else {
                $this->updatedCount++;
            }
        });
    }

    /**
     * Convert the imported quantity into the smallest saleable unit.
     */
    private function calculateStockQuantity(
        float $quantity,
        string $qtyType,
        int $qtyPerPack,
        int $looseUnitQuantity,
        string $productCode,
        int $excelRowNumber
    ): int {
        $unitQuantity = match ($qtyType) {
            'unit' => $quantity,
            'pack' => $quantity * $qtyPerPack,
            default => throw new RuntimeException(
                "Unsupported QtyType for product code {$productCode}."
            ),
        };

        $unitQuantity += $looseUnitQuantity;

        /*
         * Physical stock cannot contain a fraction of the smallest
         * saleable unit.
         *
         * Valid:
         * 5.5 packs × 12 = 66 units
         *
         * Invalid:
         * 5.55 packs × 12 = 66.6 units
         */
        if (! $this->isWholeNumber($unitQuantity)) {
            throw new RuntimeException(
                "Qty on Excel row {$excelRowNumber} produces "
                . "a fractional unit for product code {$productCode}. "
                . "Qty: {$quantity}, QtyType: {$qtyType}, "
                . "Qty per pack: {$qtyPerPack}."
            );
        }

        return (int) round($unitQuantity);
    }

    private function normalizeQtyType(mixed $value): string
    {
        $qtyType = $this->cleanText($value);

        /*
         * Current sample spreadsheets use Qty as units.
         */
        if ($qtyType === null) {
            return 'unit';
        }

        $normalized = strtoupper(
            preg_replace('/[^A-Z0-9]/', '', strtoupper($qtyType)) ?? ''
        );

        return match ($normalized) {
            'UNIT', 'UNITS', 'EACH', 'EACHES', 'PIECE', 'PIECES',
            'PC', 'PCS', 'SINGLE', 'SINGLES' => 'unit',

            'PACK', 'PACKS', 'BOX', 'BOXES', 'CASE', 'CASES',
            'CARTON', 'CARTONS' => 'pack',

            default => throw new RuntimeException(
                "QtyType '{$qtyType}' is not supported. "
                . "Use Unit or Pack."
            ),
        };
    }

    /**
     * @return array<string, mixed>
     */
    private function normalizeRow(Collection|array $row): array
    {
        $normalized = [];

        foreach ($row as $key => $value) {
            $normalizedKey = preg_replace(
                '/[^a-z0-9]/',
                '',
                strtolower((string) $key)
            );

            if ($normalizedKey !== null && $normalizedKey !== '') {
                $normalized[$normalizedKey] = $value;
            }
        }

        return $normalized;
    }

    /**
     * @param array<string, mixed> $row
     * @param array<int, string> $possibleKeys
     */
    private function readValue(array $row, array $possibleKeys): mixed
    {
        foreach ($possibleKeys as $key) {
            $normalizedKey = preg_replace(
                '/[^a-z0-9]/',
                '',
                strtolower($key)
            );

            if (
                $normalizedKey !== null
                && array_key_exists($normalizedKey, $row)
            ) {
                return $row[$normalizedKey];
            }
        }

        return null;
    }

    /**
     * @param array<string, mixed> $row
     */
    private function rowIsEmpty(array $row): bool
    {
        foreach ($row as $value) {
            if ($this->cleanText($value) !== null) {
                return false;
            }
        }

        return true;
    }

    private function cleanText(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    private function toDecimal(mixed $value): ?float
    {
        if ($value === null || trim((string) $value) === '') {
            return null;
        }

        $normalized = preg_replace(
            '/[^0-9.\-]/',
            '',
            str_replace(',', '', (string) $value)
        );

        if (
            $normalized === null
            || $normalized === ''
            || ! is_numeric($normalized)
        ) {
            return null;
        }

        return (float) $normalized;
    }

    private function toOptionalWholeNumber(
        mixed $value,
        string $fieldDescription
    ): int {
        if ($this->cleanText($value) === null) {
            return 0;
        }

        $number = $this->toDecimal($value);

        if ($number === null || $number < 0) {
            throw new RuntimeException(
                "{$fieldDescription} is invalid."
            );
        }

        if (! $this->isWholeNumber($number)) {
            throw new RuntimeException(
                "{$fieldDescription} must be a whole number."
            );
        }

        return (int) round($number);
    }

    private function isWholeNumber(float $number): bool
    {
        return abs($number - round($number)) < 0.000001;
    }

    /**
     * Parse values such as:
     *
     * 12X700GR
     * 6 x 1 KG
     * 24×330ML
     * 500G
     *
     * @return array{
     *     qty_per_pack: int,
     *     size: float,
     *     size_unit_name: string
     * }
     */
    private function parsePackaging(mixed $value): array
    {
        $rawValue = strtoupper(
            preg_replace('/\s+/', '', (string) $value) ?? ''
        );

        $rawValue = str_replace(',', '.', $rawValue);

        if (
            preg_match(
                '/^(\d+)[X×](\d+(?:\.\d+)?)([A-Z]+)$/u',
                $rawValue,
                $matches
            ) === 1
        ) {
            return [
                'qty_per_pack' => max(1, (int) $matches[1]),
                'size' => (float) $matches[2],
                'size_unit_name' => $this->normalizeSizeUnitName(
                    $matches[3]
                ),
            ];
        }

        if (
            preg_match(
                '/^(\d+(?:\.\d+)?)([A-Z]+)$/u',
                $rawValue,
                $matches
            ) === 1
        ) {
            return [
                'qty_per_pack' => 1,
                'size' => (float) $matches[1],
                'size_unit_name' => $this->normalizeSizeUnitName(
                    $matches[2]
                ),
            ];
        }

        return [
            'qty_per_pack' => 1,
            'size' => 1,
            'size_unit_name' => 'Piece',
        ];
    }

    private function normalizeSizeUnitName(string $unit): string
    {
        return match ($this->sizeUnitKey($unit)) {
            'G' => 'Gram',
            'KG' => 'Kilogram',
            'ML' => 'Millilitre',
            'L' => 'Litre',
            'PCS' => 'Piece',
            default => Str::title(strtolower(trim($unit))),
        };
    }

    /**
     * Convert aliases into one canonical comparison key.
     */
    private function sizeUnitKey(string $value): string
    {
        $normalized = strtoupper(
            preg_replace(
                '/[^A-Z0-9]/',
                '',
                Str::ascii(trim($value))
            ) ?? ''
        );

        return match ($normalized) {
            'G', 'GR', 'GRAM', 'GRAMS' => 'G',

            'KG', 'KGS', 'KILO', 'KILOS',
            'KILOGRAM', 'KILOGRAMS' => 'KG',

            'ML', 'MLS', 'MILLILITRE', 'MILLILITRES',
            'MILLILITER', 'MILLILITERS' => 'ML',

            'L', 'LT', 'LTR', 'LITRE', 'LITRES',
            'LITER', 'LITERS' => 'L',

            'PC', 'PCS', 'PIECE', 'PIECES',
            'UNIT', 'UNITS', 'EACH', 'EACHES' => 'PCS',

            default => $normalized,
        };
    }

    /**
     * Find a SizeUnit by any equivalent alias before creating one.
     */
    private function findOrCreateSizeUnit(string $name): SizeUnit
    {
        $targetKey = $this->sizeUnitKey($name);

        /** @var SizeUnit|null $matchingRecord */
        $matchingRecord = SizeUnit::withTrashed()
            ->get()
            ->first(function (SizeUnit $record) use ($targetKey): bool {
                return $this->sizeUnitKey((string) $record->name)
                    === $targetKey
                    || $this->sizeUnitKey((string) $record->code)
                    === $targetKey;
            });

        if ($matchingRecord !== null) {
            if ($matchingRecord->trashed()) {
                $matchingRecord->restore();
            }

            if (! $matchingRecord->is_active) {
                $matchingRecord->is_active = true;
                $matchingRecord->save();
            }

            return $matchingRecord;
        }

        /** @var SizeUnit $createdRecord */
        $createdRecord = SizeUnit::create([
            'code' => $this->generateReferenceCode(
                SizeUnit::class,
                $name,
                'SIZE'
            ),
            'name' => $name,
            'description' => null,
            'sort_order' => 0,
            'is_active' => true,
        ]);

        return $createdRecord;
    }

    private function normalizeImageFilename(mixed $value): ?string
    {
        $path = $this->cleanText($value);

        if ($path === null) {
            return null;
        }

        $path = str_replace('\\', '/', $path);
        $filename = basename($path);
        $filename = trim($filename);

        return $filename === '' ? null : $filename;
    }

    /**
     * Find or create Brand, Category or Unit.
     */
    private function findOrCreateReference(
        string $modelClass,
        string $name,
        string $codePrefix
    ): Model {
        /** @var Model|null $record */
        $record = $modelClass::withTrashed()
            ->whereRaw('LOWER(TRIM(name)) = ?', [
                mb_strtolower(trim($name)),
            ])
            ->first();

        if ($record !== null) {
            if (
                method_exists($record, 'trashed')
                && $record->trashed()
            ) {
                $record->restore();
            }

            if (! $record->is_active) {
                $record->is_active = true;
                $record->save();
            }

            return $record;
        }

        return $modelClass::create([
            'code' => $this->generateReferenceCode(
                $modelClass,
                $name,
                $codePrefix
            ),
            'name' => $name,
            'description' => null,
            'sort_order' => 0,
            'is_active' => true,
        ]);
    }

    private function generateReferenceCode(
        string $modelClass,
        string $name,
        string $prefix
    ): string {
        $namePart = strtoupper(
            Str::slug(Str::ascii($name), '')
        );

        if ($namePart === '') {
            $namePart = 'ITEM';
        }

        $baseCode = Str::limit(
            strtoupper($prefix) . '-' . $namePart,
            26,
            ''
        );

        $candidate = $baseCode;
        $counter = 2;

        while (
            $modelClass::withTrashed()
                ->where('code', $candidate)
                ->exists()
        ) {
            $suffix = '-' . $counter;

            $candidate =
                Str::limit(
                    $baseCode,
                    30 - strlen($suffix),
                    ''
                )
                . $suffix;

            $counter++;
        }

        return $candidate;
    }

    /**
     * @return array<string, mixed>
     */
    public function summary(): array
    {
        return [
            'created' => $this->createdCount,
            'updated' => $this->updatedCount,
            'skipped' => $this->skippedCount,
            'errors' => $this->errors,
        ];
    }
}