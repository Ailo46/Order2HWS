<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use App\Models\Customer;
use App\Services\UserCustomerService;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\DB;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function afterSave(): void
    {
        app(UserCustomerService::class)->handle(
            $this->record,
            $this->data,
        );
    }

    protected function getHeaderActions(): array
    {
        return [

            DeleteAction::make()

                ->action(function () {

                    DB::transaction(function () {

                        Customer::where('email', $this->record->email)
                            ->delete();

                        $this->record->delete();

                    });

                    $this->redirect(
                        static::getResource()::getUrl('index')
                    );

                }),

        ];
    }
}