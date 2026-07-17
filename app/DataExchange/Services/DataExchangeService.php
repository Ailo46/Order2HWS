<?php

namespace App\DataExchange\Services;

use App\Exports\ProductsExport;
use App\Imports\ProductsImport;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use RuntimeException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Throwable;
use ZipArchive;

class DataExchangeService
{
    /**
     * Import products from:
     *
     * 1. A standalone Excel file
     * 2. A ZIP package containing an Excel file and product images
     *
     * @return array<string, mixed>
     */
    public function importProducts(UploadedFile $file): array
    {
        $workingDirectory = storage_path(
            'app/imports/' . Str::uuid()->toString()
        );

        File::ensureDirectoryExists($workingDirectory);

        try {
            $extension = strtolower(
                $file->getClientOriginalExtension()
            );

            return match ($extension) {
                'xlsx', 'xls', 'csv' => $this->importSpreadsheet(
                    $file->getRealPath()
                ),

                'zip' => $this->importZipPackage(
                    $file->getRealPath(),
                    $workingDirectory
                ),

                default => throw new RuntimeException(
                    'Unsupported import file type. Please upload an Excel or ZIP file.'
                ),
            };
        } finally {
            /*
             * The temporary import directory is removed even when
             * validation or spreadsheet processing fails.
             */
            File::deleteDirectory($workingDirectory);
        }
    }

    /**
     * Import a standalone spreadsheet.
     *
     * @return array<string, mixed>
     */
    private function importSpreadsheet(string $spreadsheetPath): array
    {
        if (! is_file($spreadsheetPath)) {
            throw new RuntimeException(
                'The uploaded spreadsheet could not be found.'
            );
        }

        $import = new ProductsImport();

        Excel::import($import, $spreadsheetPath);

        return array_merge(
            $import->summary(),
            [
                'images_imported' => 0,
                'images_replaced' => 0,
                'image_warnings' => [],
                'source_type' => 'excel',
            ]
        );
    }

    /**
     * Import a ZIP package containing a spreadsheet and optional images.
     *
     * Expected structure:
     *
     * ProductsImport.zip
     * ├── Products.xlsx
     * └── images/
     *     ├── PRODUCT-001.jpg
     *     └── PRODUCT-002.png
     *
     * @return array<string, mixed>
     */
    private function importZipPackage(
        string $zipPath,
        string $workingDirectory
    ): array {
        if (! class_exists(ZipArchive::class)) {
            throw new RuntimeException(
                'The PHP ZIP extension is not available.'
            );
        }

        if (! is_file($zipPath)) {
            throw new RuntimeException(
                'The uploaded ZIP file could not be found.'
            );
        }

        $extractedDirectory = $workingDirectory . DIRECTORY_SEPARATOR . 'extracted';

        File::ensureDirectoryExists($extractedDirectory);

        $this->extractZipSafely(
            $zipPath,
            $extractedDirectory
        );

        $spreadsheetPath = $this->findSpreadsheet(
            $extractedDirectory
        );

        if ($spreadsheetPath === null) {
            throw new RuntimeException(
                'No Excel or CSV file was found inside the ZIP package.'
            );
        }

        $imageResult = $this->importImages(
            $extractedDirectory
        );

        $import = new ProductsImport();

        Excel::import($import, $spreadsheetPath);

        return array_merge(
            $import->summary(),
            [
                'images_imported' => $imageResult['imported'],
                'images_replaced' => $imageResult['replaced'],
                'image_warnings' => $imageResult['warnings'],
                'source_type' => 'zip',
            ]
        );
    }

    /**
     * Extract ZIP entries without allowing directory traversal.
     */
    private function extractZipSafely(
        string $zipPath,
        string $destination
    ): void {
        $zip = new ZipArchive();

        $openResult = $zip->open($zipPath);

        if ($openResult !== true) {
            throw new RuntimeException(
                'The ZIP package could not be opened or is invalid.'
            );
        }

        try {
            for ($index = 0; $index < $zip->numFiles; $index++) {
                $entryName = $zip->getNameIndex($index);

                if (
                    $entryName === false
                    || $entryName === ''
                ) {
                    continue;
                }

                $normalizedEntryName = str_replace(
                    '\\',
                    '/',
                    $entryName
                );

                /*
                 * Reject absolute paths and ../ traversal attempts.
                 */
                if (
                    str_starts_with($normalizedEntryName, '/')
                    || preg_match(
                        '/^[A-Za-z]:\//',
                        $normalizedEntryName
                    ) === 1
                    || in_array(
                        '..',
                        explode('/', $normalizedEntryName),
                        true
                    )
                ) {
                    throw new RuntimeException(
                        "Unsafe path found inside ZIP: {$entryName}"
                    );
                }

                $destinationPath =
                    $destination
                    . DIRECTORY_SEPARATOR
                    . str_replace(
                        '/',
                        DIRECTORY_SEPARATOR,
                        $normalizedEntryName
                    );

                /*
                 * Directory entries end with a slash.
                 */
                if (str_ends_with($normalizedEntryName, '/')) {
                    File::ensureDirectoryExists($destinationPath);

                    continue;
                }

                File::ensureDirectoryExists(
                    dirname($destinationPath)
                );

                $inputStream = $zip->getStream($entryName);

                if ($inputStream === false) {
                    throw new RuntimeException(
                        "Could not read ZIP entry: {$entryName}"
                    );
                }

                $outputStream = fopen(
                    $destinationPath,
                    'wb'
                );

                if ($outputStream === false) {
                    fclose($inputStream);

                    throw new RuntimeException(
                        "Could not create extracted file: {$entryName}"
                    );
                }

                try {
                    stream_copy_to_stream(
                        $inputStream,
                        $outputStream
                    );
                } finally {
                    fclose($inputStream);
                    fclose($outputStream);
                }
            }
        } finally {
            $zip->close();
        }
    }

    /**
     * Locate the spreadsheet inside the extracted ZIP package.
     */
    private function findSpreadsheet(
        string $directory
    ): ?string {
        $supportedExtensions = [
            'xlsx',
            'xls',
            'csv',
        ];

        $spreadsheetFiles = collect(
            File::allFiles($directory)
        )
            ->filter(function ($file) use ($supportedExtensions): bool {
                return in_array(
                    strtolower($file->getExtension()),
                    $supportedExtensions,
                    true
                );
            })
            ->sortBy(function ($file): int {
                /*
                 * Prefer a spreadsheet named Products.
                 */
                return strtolower($file->getFilenameWithoutExtension())
                    === 'products'
                    ? 0
                    : 1;
            })
            ->values();

        if ($spreadsheetFiles->isEmpty()) {
            return null;
        }

        return $spreadsheetFiles
            ->first()
            ->getRealPath();
    }

    /**
     * Copy supported product images into public storage.
     *
     * Images may be inside an "images" directory or elsewhere in
     * the ZIP. Only the filename is used by ProductsImport.
     *
     * @return array{
     *     imported: int,
     *     replaced: int,
     *     warnings: array<int, string>
     * }
     */
    private function importImages(
        string $directory
    ): array {
        $supportedExtensions = [
            'jpg',
            'jpeg',
            'png',
            'webp',
        ];

        $importedCount = 0;
        $replacedCount = 0;
        $warnings = [];

        Storage::disk('public')->makeDirectory(
            'products'
        );

        foreach (File::allFiles($directory) as $imageFile) {
            $extension = strtolower(
                $imageFile->getExtension()
            );

            if (
                ! in_array(
                    $extension,
                    $supportedExtensions,
                    true
                )
            ) {
                continue;
            }

            $filename = $this->sanitizeImageFilename(
                $imageFile->getFilename()
            );

            if ($filename === null) {
                $warnings[] =
                    'An image with an invalid filename was skipped.';

                continue;
            }

            $storagePath = 'products/' . $filename;

            $alreadyExists = Storage::disk('public')
                ->exists($storagePath);

            $inputStream = fopen(
                $imageFile->getRealPath(),
                'rb'
            );

            if ($inputStream === false) {
                $warnings[] =
                    "Image could not be read: {$imageFile->getFilename()}";

                continue;
            }

            try {
                $stored = Storage::disk('public')->put(
                    $storagePath,
                    $inputStream
                );
            } catch (Throwable $exception) {
                $stored = false;

                $warnings[] =
                    "Image could not be stored: {$imageFile->getFilename()}";
            } finally {
                fclose($inputStream);
            }

            if (! $stored) {
                continue;
            }

            if ($alreadyExists) {
                $replacedCount++;
            } else {
                $importedCount++;
            }
        }

        return [
            'imported' => $importedCount,
            'replaced' => $replacedCount,
            'warnings' => $warnings,
        ];
    }

    private function sanitizeImageFilename(
        string $filename
    ): ?string {
        $filename = basename(
            str_replace('\\', '/', $filename)
        );

        $filename = preg_replace(
            '/[^A-Za-z0-9._\-]/',
            '-',
            $filename
        );

        if (
            $filename === null
            || $filename === ''
            || $filename === '.'
            || $filename === '..'
        ) {
            return null;
        }

        return $filename;
    }

    /**
     * Reserved for Sprint G.
     */
    public function exportProducts(): BinaryFileResponse
    {
        return Excel::download(
            new ProductsExport(),
            'products.xlsx'
        );
    }
}