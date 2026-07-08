<?php

namespace App\Filament\Resources\Orders\RelationManagers;

use App\Models\OrderItem;
use Filament\Actions\Action;
use App\Models\Product;
use App\Services\OrderPricingService;
use Closure;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use App\Rules\AgentDiscountRule;

class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    protected OrderPricingService $pricing;

    public function boot(): void
    {
        $this->pricing = app(OrderPricingService::class);
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([

            Hidden::make('id'),

            Select::make('product_id')
                ->label('Product')
                ->relationship(
                    name: 'product',
                    titleAttribute: 'name',
                    modifyQueryUsing: fn ($query) => $query
                        ->where('is_active', true)
                        ->orderBy('name')
                )
                ->searchable()
                ->preload()
                ->required()
                ->rules([

                    function () {

                        return function (
                            string $attribute,
                            $value,
                            Closure $fail
                        ) {

                            $order = $this->getOwnerRecord();

                            $query = $order->items()
                                ->where('product_id', $value);

                            $currentRecord = $this->getMountedTableActionRecord();

                            if ($currentRecord instanceof OrderItem) {
                                $query->whereKeyNot($currentRecord->getKey());
                            }

                            if ($query->exists()) {

                                $fail(
                                    'This product already exists in this order.'
                                );

                            }

                        };

                    },

                ]),

            TextInput::make('quantity')
                ->numeric()
                ->default(1)
                ->required(),

            TextInput::make('discount_percent')
                ->label('Agent Discount %')
                ->numeric()
                ->default(0)
                ->minValue(0)
                ->rules([
                    'numeric',
                    'min:0',
                    new AgentDiscountRule(),
                ])
                ->helperText(
                    fn () => 'Maximum allowed: '
                        . (auth()->user()?->max_discount_percent ?? 0)
                        . '%'
                ),

        ]);
    }

    public function table(Table $table): Table
    {
        return $table

            ->columns([

                TextColumn::make('product.name')
                    ->label('Product')
                    ->searchable(),

                TextColumn::make('quantity')
                    ->numeric(2),

                TextColumn::make('unit_price')
                    ->money('GBP')
                    ->label('Customer Unit'),

                TextColumn::make('discount_percent')
                    ->suffix('%')
                    ->label('Agent %'),

                TextColumn::make('net_unit_price')
                    ->money('GBP')
                    ->label('Net Unit')
                    ->weight('bold'),

                TextColumn::make('vat_percent')
                    ->suffix('%')
                    ->label('VAT %'),

                TextColumn::make('line_total')
                    ->money('GBP')
                    ->weight('bold')
                    ->label('Total'),

            ])

            ->headerActions([

                CreateAction::make()

                    ->using(function (
                        array $data,
                        Action $action,
                    ) {

                        $order = $this->getOwnerRecord();

                        $customer = $order->customer;

                        $product = Product::findOrFail($data['product_id']);

                        $pricing = $this->pricing->calculate(
                            product: $product,
                            customer: $customer,
                            quantity: (float) $data['quantity'],
                            agentDiscountPercent: (float) ($data['discount_percent'] ?? 0),
                        );

                        return $order->items()->create([
                            ...$data,
                            ...$pricing,
                        ]);
                    }),

            ])

            ->recordActions([

                EditAction::make()

                    ->using(function (
                        OrderItem $record,
                        array $data,
                        Action $action,
                    ) {

                        $order = $this->getOwnerRecord();

                        $customer = $order->customer;

                        $product = Product::findOrFail($data['product_id']);

                        $pricing = $this->pricing->calculate(
                            product: $product,
                            customer: $customer,
                            quantity: (float) $data['quantity'],
                            agentDiscountPercent: (float) ($data['discount_percent'] ?? 0),
                        );

                        $record->update([
                            ...$data,
                            ...$pricing,
                        ]);

                        return $record;
                    }),

                DeleteAction::make(),

            ]);
    }
}