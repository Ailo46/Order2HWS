<?php

namespace App\Filament\Resources\Customers\Pages;

use App\Filament\Resources\Customers\CustomerResource;
use App\Filament\Resources\Users\UserResource;
use App\Models\User;
use App\Support\Roles;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditCustomer extends EditRecord
{
    protected static string $resource = CustomerResource::class;

    public function mount(int|string $record): void
    {
        parent::mount($record);

        /*
        |--------------------------------------------------------------------------
        | Redirect Dual Customers to User Form
        |--------------------------------------------------------------------------
        */

        if (
            in_array(
                $this->record->customerType?->name,
                [
                    'Cash & Carry Customer',
                    'Consumer',
                ],
                true
            )
        ) {

            $user = User::where('email', $this->record->email)->first();

            if (
                $user &&
                $user->hasAnyRole([
                    Roles::CUSTOMER_CC,
                    Roles::END_CONSUMER,
                ])
            ) {

                $this->redirect(
                    UserResource::getUrl('edit', [
                        'record' => $user,
                    ])
                );

                return;
            }
        }
    }

    protected function getHeaderActions(): array
    {
        return [

            DeleteAction::make(),

            ForceDeleteAction::make(),

            RestoreAction::make(),

        ];
    }
}