<?php

namespace App\Exports;

use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SalesExport implements FromCollection, ShouldAutoSize, WithColumnFormatting, WithEvents, WithHeadings, WithMapping, WithStyles, WithTitle
{
    public function __construct(
        private Collection $sales,
        private Carbon $from,
        private Carbon $to,
    ) {}

    // Filas del archivo: las ventas ya filtradas en el controlador
    public function collection(): Collection
    {
        return $this->sales;
    }

    // Encabezados de la primera fila
    public function headings(): array
    {
        return ['N.°', 'Fecha', 'Cliente', 'DNI', 'Vendedor', 'IGV (S/)', 'Total (S/)'];
    }

    // Columnas que se escriben por cada venta
    public function map($sale): array
    {
        return [
            str_pad($sale->id, 6, '0', STR_PAD_LEFT),
            $sale->date->format('d/m/Y H:i'),
            $sale->customer->name,
            $sale->customer->dni,
            $sale->user->name,
            (float) $sale->igv,
            (float) $sale->total,
        ];
    }

    // Formato de moneda para IGV y Total
    public function columnFormats(): array
    {
        return [
            'F' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
            'G' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
        ];
    }

    // Encabezado en negrita con el color de la plantilla
    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'FF0A41']],
            ],
        ];
    }

    // Nombre de la hoja
    public function title(): string
    {
        return 'Ventas '.$this->from->format('d-m-Y').' al '.$this->to->format('d-m-Y');
    }

    // Fila final con los totales del reporte
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastRow = $this->sales->count() + 1;
                $totalRow = $lastRow + 1;

                $sheet->setCellValue("E{$totalRow}", 'TOTALES');
                $sheet->setCellValue("F{$totalRow}", $this->sales->isEmpty() ? 0 : "=SUM(F2:F{$lastRow})");
                $sheet->setCellValue("G{$totalRow}", $this->sales->isEmpty() ? 0 : "=SUM(G2:G{$lastRow})");

                $sheet->getStyle("E{$totalRow}:G{$totalRow}")->getFont()->setBold(true);
                $sheet->getStyle("F{$totalRow}:G{$totalRow}")->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
                $sheet->getStyle("E{$totalRow}:G{$totalRow}")->getBorders()->getTop()->setBorderStyle('thin');
            },
        ];
    }
}
