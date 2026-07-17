<?php

namespace App\Filament\Pages;

use App\DataExchange\Services\DataExchangeService;
use BackedEnum;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Concerns\RestrictsFileUploadsToSchemaComponents;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use RuntimeException;
use Throwable;
use UnitEnum;

class DataExchange extends Page implements HasSchemas
{
    use InteractsWithSchemas;
    use RestrictsFileUploadsToSchemaComponents;

    protected static string | UnitEnum | null $navigationGroup = '📦 Items';

    protected static string | BackedEnum | null $navigationIcon =
        Heroicon::OutlinedArrowsRightLeft;

    protected static ?int $navigationSort = 40;

    protected static ?string $navigationLabel = 'Data Exchange';

    protected static ?string $title = 'Data Exchange';

    protected string $view = 'filament.pages.data-exchange';

    /**
     * Form state.
     *
     * @var array<string, mixed>|null
     */
    public ?array $data = [];

    /**
     * Result of the latest product import.
     *
     * @var array<string, mixed>|null
     */
    public ?array $importResult = null;

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Import Products')
                    ->description(
                        'Upload an Excel file, or a ZIP package containing '
                        . 'Products.xlsx and an optional images folder.'
                    )
                    ->schema([
                        FileUpload::make('import_file')
                            ->label('Import File')
                            ->helperText(
                                'Accepted formats: XLSX, XLS, CSV and ZIP. '
                                . 'A ZIP file may contain product images.'
                            )
                            ->acceptedFileTypes([
                                'application/zip',
                                'application/x-zip-compressed',
                                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                                'application/vnd.ms-excel',
                                'text/csv',
                                'text/plain',
                            ])
                            ->mimeTypeMap([
                                'zip' => 'application/zip',
                                'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                                'xls' => 'application/vnd.ms-excel',
                                'csv' => 'text/csv',
                            ])
                            /*
                             * Size is expressed in kilobytes.
                             * 614400 KB = 600 MB.
                             */
                            ->maxSize(614400)
                            ->required()
                            ->storeFiles(false)
                            ->previewable(false)
                            ->pasteable(false)
                            ->uploadingMessage(
                                'Uploading import package...'
                            ),
                    ]),
            ])
            ->statePath('data');
    }

    public function importProducts(
        DataExchangeService $dataExchangeService
    ): void {
        $state = $this->form->getState();

        $uploadedFile = $state['import_file'] ?? null;

        if (! $uploadedFile instanceof TemporaryUploadedFile) {
            throw new RuntimeException(
                'The selected import file is not available.'
            );
        }

        /*
         * Clear the previous result before starting another import.
         */
        $this->importResult = null;

        try {
            $this->importResult =
                $dataExchangeService->importProducts($uploadedFile);

            Notification::make()
                ->title('Product import completed')
                ->body($this->buildSuccessNotificationBody())
                ->success()
                ->send();

            /*
             * Clear the selected temporary file after processing.
             * The result remains visible on the page.
             */
            $this->form->fill();
        } catch (Throwable $exception) {
            report($exception);

            Notification::make()
                ->title('Product import failed')
                ->body($exception->getMessage())
                ->danger()
                ->persistent()
                ->send();
        }
    }

    private function buildSuccessNotificationBody(): string
    {
        if ($this->importResult === null) {
            return 'The import finished successfully.';
        }

        $created = (int) ($this->importResult['created'] ?? 0);
        $updated = (int) ($this->importResult['updated'] ?? 0);
        $skipped = (int) ($this->importResult['skipped'] ?? 0);
        $imagesImported =
            (int) ($this->importResult['images_imported'] ?? 0);
        $imagesReplaced =
            (int) ($this->importResult['images_replaced'] ?? 0);

        return implode(' | ', [
            "Created: {$created}",
            "Updated: {$updated}",
            "Skipped: {$skipped}",
            "Images imported: {$imagesImported}",
            "Images replaced: {$imagesReplaced}",
        ]);
    }
}