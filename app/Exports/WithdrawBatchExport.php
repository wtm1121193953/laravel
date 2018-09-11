<?php

namespace App\Exports;


use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class WithdrawBatchExport implements FromQuery, WithHeadings, WithMapping
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
        return [
            '添加批次时间',
            '批次编号',
            '批次类型',
            '批次总金额/笔数',
            '打款成功金额/笔数',
            '打款失败金额/笔数',
            '批次状态',
            '批次备注',
        ];
    }

    public function map($row) : array
    {
        return [
            $row->created_at,
            $row->batch_no,
            ['', '对公', '对私'][$row->type],
            $row->amount.'元/'.$row->total.'笔',
            $row->success_amount.'元/'.$row->success_total.'笔',
            $row->failed_amount.'元/'.$row->failed_total.'笔',
            ['', '结算中', '准备打款', '打款完成'][$row->status],
            $row->remark
        ];
    }
}