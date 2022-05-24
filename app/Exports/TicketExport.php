<?php

namespace App\Exports;

use App\Models\Activity;
use App\Models\Ticket;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Protection;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TicketExport implements FromCollection, WithStyles
{
    protected Activity $activity;

    public function __construct(Activity $activity)
    {
        $this->activity = $activity;
    }

    public function collection(): \Illuminate\Database\Eloquent\Collection|array|Collection
    {
        $data = $this->activity->tickets()->with(['user', 'group'])->get()->transform(function (Ticket $ticket) {
            return [
                'code' => $ticket->code,
                'first_name' => optional($ticket->user)->first_name,
                'last_name' => optional($ticket->user)->last_name,
                'username' => optional($ticket->user)->username,
                'email' => optional($ticket->user)->email,
                'phone' => optional($ticket->user)->phone,
                'group' => optional($ticket->group)->name,
                'seat_num' => $ticket->seat_num
            ];
        })->toArray();
        $data = array_merge($this->row(), $data);
        return collect($data);
    }

    public function styles(Worksheet $sheet)
    {
        // 合并单元格
        $sheet->mergeCells('A1:H1');
        // 设置单元格的值
        $sheet->getCell('A1')->setValue('Instructions for filling in: 1 Do not modify the table structure; 2. The red field is required and the black field is optional;');
        $sheet->getStyle('A1')->getFont()->getColor()->setARGB(Color::COLOR_RED);
        $sheet->getStyle('A1')->getFont()->setSize(20);
        // 设置字体颜色
        $sheet->getStyle('H')->getFont()->getColor()->setARGB(Color::COLOR_RED);
        $sheet->getStyle('A5:H5')->getFont()->setSize(16);
        // 设置背景色
        $sheet->getStyle('A5:H5')->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRGB('e9e9e9');
        // 设置列宽
        $sheet->getColumnDimension('A')->setWidth(40);
        $sheet->getColumnDimension('B')->setWidth(20);
        $sheet->getColumnDimension('C')->setWidth(20);
        $sheet->getColumnDimension('D')->setWidth(30);
        $sheet->getColumnDimension('E')->setWidth(40);
        $sheet->getColumnDimension('F')->setWidth(30);
        $sheet->getColumnDimension('G')->setWidth(20);
        $sheet->getColumnDimension('H')->setWidth(20);
        // 设置行高
        $sheet->getRowDimension('1')->setRowHeight(100);
        $sheet->getRowDimension('2')->setRowHeight(1);
        $sheet->getRowDimension('3')->setRowHeight(1);
        $sheet->getRowDimension('4')->setRowHeight(1);
        // 设置默认行高
        $sheet->getDefaultRowDimension()->setRowHeight(17);
        // setWrapText自动换行，setVertical垂直对齐方式
        $sheet->getStyle('A1')->getAlignment()->setVertical(Alignment::VERTICAL_TOP)->setWrapText(true);

        // 锁定单元格
        $sheet->getProtection()->setSheet(true);
        // 这里原本只想让这几个单元格不可编辑，但是不知道怎么就所有都不能编辑了，等找到更好的办法会再解决这里
        $sheet->getStyle('A:F')->getProtection()->setLocked(Protection::PROTECTION_PROTECTED);
        // 注意，这里要将可编辑的地方设为不受保护，不然整个excel都不能编辑了
        $sheet->getStyle('G:H')->getProtection()->setLocked(Protection::PROTECTION_UNPROTECTED);
    }

    private function row(): array
    {
        //设置表头
        return [
            [
                0 => 'Instructions for filling in: \n 1 Do not modify the table structure; \n 2. The red field is required and the black field is optional; \n',
                1 => null, 2 => null, 3 => null, 4 => null, 5 => null, 6 => null, 7 => null
            ],
            [0 => null, 1 => null, 2 => null, 3 => null, 4 => null, 5 => null, 6 => null, 7 => null],
            [0 => null, 1 => null, 2 => null, 3 => null, 4 => null, 5 => null, 6 => null, 7 => null],
            [0 => null, 1 => null, 2 => null, 3 => null, 4 => null, 5 => null, 6 => null, 7 => null],
            [
                0 => 'Ticket',
                1 => 'FirstName',
                2 => 'LastName',
                3 => 'Username',
                4 => 'Email',
                5 => 'Phone',
                6 => 'Group',
                7 => 'Seat',
            ],
        ];
    }
}
