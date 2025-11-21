<?php

declare(strict_types=1);

namespace App\Exports;

use App\Models\Order;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

final class OrdersExport implements FromCollection, WithHeadings, WithMapping
{
    private Collection $orders;

    private int $rowNumber = 0;

    public function __construct(\Illuminate\Database\Eloquent\Collection $orders)
    {
        $this->orders = $orders;
    }

    public function collection(): \Illuminate\Database\Eloquent\Collection|Collection
    {
        return $this->orders;
    }

    public function headings(): array
    {
        return [
            '№',
            'Заказ',
            'Получатель',
            'Вес',
            'Дата забора груза',
            'Дата доставки',
            'Объявленная ценность',
            'Стоимость доставки',
            'Итого',
            'Расчет оплаты',
            'Тип оплаты',
        ];
    }

    public function map($row): array
    {
        return [
            ++$this->rowNumber,
            $row->id,
            $row->recipient->name,
            sprintf("%d кг", $row->weight),
            $row->delivery_date->format('d.m.Y'),
            $row->delivery_date->format('d.m.Y'),
            sprintf("%d руб.", $row->assessed_value),
            sprintf("%d руб.", $row->price),
            sprintf("%d руб.", $row->price),
            sprintf("%d руб.", $row->cod_price),
            $row->cod ? 'Наличные' : match ($row->payment_type) {
                'card' => 'Карта',
                default => 'Наличные',
            },
        ];
    }
}
