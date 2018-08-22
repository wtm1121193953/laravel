<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/8/22
 * Time: 22:05
 */

namespace App\Exports;


use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class WalletBillExport implements FromQuery, WithHeadings, WithMapping
{
    use Exportable;

    protected $query;

    public function __construct($query)
    {
        $this->query = $query;
    }

    public function query()
    {
        return $this->query;
    }

    public function headings() : array
    {

    }

    public function map($row) : array
    {

    }
}