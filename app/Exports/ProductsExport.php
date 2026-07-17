<?php

namespace App\Exports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ProductsExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        return Product::query()
            ->with([
                'brand',
                'category',
            ])
            ->orderBy('code')
            ->get()
            ->map(function (Product $product) {
                return [

                    'Brand'       => $product->brand?->name,

                    'ProductName' => $product->name,

                    'Size'        => trim(
                        $product->qty_per_pack .
                        ' ' .
                        ($product->size ?? '') .
                        ' ' .
                        ($product->sizeUnit?->name ?? '')
                    ),

                    'Code'        => $product->code,

                    'Category'    => $product->category?->name,

                    'Price'       => $product->base_price,

                    'ImagePath'   => $product->image,

                    'Qty'         => $product->stock_quantity,
                ];
            });
    }

    public function headings(): array
    {
        return [

            'Brand',

            'ProductName',

            'Size',

            'Code',

            'Category',

            'Price',

            'ImagePath',

            'Qty',
        ];
    }
}