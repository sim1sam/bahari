<?php

namespace App\Support;

use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportSpreadsheet
{
    private const TITLE_FILL = '1F4E79';

    private const TITLE_FONT = 'FFFFFF';

    private const HEADER_FILL = '2E75B6';

    private const HEADER_FONT = 'FFFFFF';

    private const FOOTER_FILL = 'D6E3F0';

    private const META_FILL = 'E7EFF8';

    /**
     * @param  array{
     *     title: string,
     *     subtitle?: string|null,
     *     meta?: array<int, array<int, mixed>>,
     *     headers: array<int, string>,
     *     rows?: array<int, array<int, mixed>>,
     *     footer?: array<int, mixed>|null
     * }  $report
     */
    public static function download(string $filename, array $report, string $sheetTitle = 'Report'): StreamedResponse
    {
        $title = (string) ($report['title'] ?? $sheetTitle);
        $subtitle = trim((string) ($report['subtitle'] ?? ''));
        $meta = $report['meta'] ?? [];
        $headers = array_values($report['headers'] ?? []);
        $rows = $report['rows'] ?? [];
        $footer = $report['footer'] ?? null;

        $columnCount = max(count($headers), 1);
        foreach ($rows as $row) {
            $columnCount = max($columnCount, count($row));
        }
        if (is_array($footer)) {
            $columnCount = max($columnCount, count($footer));
        }

        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle(mb_substr($sheetTitle, 0, 31));

        $lastColumn = Coordinate::stringFromColumnIndex($columnCount);
        $rowIndex = 1;

        // Title row — merged, centered, colored
        $sheet->setCellValue('A'.$rowIndex, $title);
        $sheet->mergeCells('A'.$rowIndex.':'.$lastColumn.$rowIndex);
        $sheet->getRowDimension($rowIndex)->setRowHeight(28);
        $sheet->getStyle('A'.$rowIndex.':'.$lastColumn.$rowIndex)->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 16,
                'color' => ['rgb' => self::TITLE_FONT],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => self::TITLE_FILL],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);
        $rowIndex++;

        // Period / subtitle — centered directly under title
        if ($subtitle !== '') {
            $sheet->setCellValue('A'.$rowIndex, $subtitle);
            $sheet->mergeCells('A'.$rowIndex.':'.$lastColumn.$rowIndex);
            $sheet->getRowDimension($rowIndex)->setRowHeight(20);
            $sheet->getStyle('A'.$rowIndex.':'.$lastColumn.$rowIndex)->applyFromArray([
                'font' => [
                    'bold' => true,
                    'size' => 11,
                    'color' => ['rgb' => '1F4E79'],
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => self::META_FILL],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ]);
            $rowIndex++;
        }

        // Extra meta rows (e.g. basis) — also centered under title
        foreach ($meta as $metaRow) {
            $metaValues = array_values($metaRow);
            $metaText = count($metaValues) === 1
                ? (string) $metaValues[0]
                : trim(implode(': ', array_map(fn ($value) => (string) $value, $metaValues)));

            $sheet->setCellValue('A'.$rowIndex, $metaText);
            $sheet->mergeCells('A'.$rowIndex.':'.$lastColumn.$rowIndex);
            $sheet->getStyle('A'.$rowIndex.':'.$lastColumn.$rowIndex)->applyFromArray([
                'font' => ['bold' => true, 'size' => 10, 'color' => ['rgb' => '1F4E79']],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => self::META_FILL],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ]);
            $rowIndex++;
        }

        // Spacer
        $rowIndex++;

        // Table header — bold + colored
        $headerRow = $rowIndex;
        foreach ($headers as $col => $header) {
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($col + 1).$headerRow, $header);
        }
        $sheet->getRowDimension($headerRow)->setRowHeight(22);
        $sheet->getStyle('A'.$headerRow.':'.$lastColumn.$headerRow)->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 11,
                'color' => ['rgb' => self::HEADER_FONT],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => self::HEADER_FILL],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '1F4E79'],
                ],
            ],
        ]);
        $rowIndex++;

        $dataStart = $rowIndex;

        foreach ($rows as $dataRow) {
            $values = array_values($dataRow);
            foreach ($values as $col => $value) {
                $sheet->setCellValue(Coordinate::stringFromColumnIndex($col + 1).$rowIndex, $value);
            }
            $rowIndex++;
        }

        $dataEnd = $rowIndex - 1;

        if ($dataEnd >= $dataStart) {
            $sheet->getStyle('A'.$dataStart.':'.$lastColumn.$dataEnd)->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => 'BDD7EE'],
                    ],
                ],
                'alignment' => [
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ]);
        }

        if (is_array($footer) && $footer !== []) {
            $footerValues = array_values($footer);
            foreach ($footerValues as $col => $value) {
                $sheet->setCellValue(Coordinate::stringFromColumnIndex($col + 1).$rowIndex, $value);
            }
            $sheet->getStyle('A'.$rowIndex.':'.$lastColumn.$rowIndex)->applyFromArray([
                'font' => ['bold' => true, 'size' => 11],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => self::FOOTER_FILL],
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => '1F4E79'],
                    ],
                ],
            ]);
        }

        for ($column = 1; $column <= $columnCount; $column++) {
            $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($column))->setAutoSize(true);
        }

        return response()->streamDownload(function () use ($spreadsheet) {
            (new Xlsx($spreadsheet))->save('php://output');
            $spreadsheet->disconnectWorksheets();
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    /**
     * Flatten a styled report into CSV/plain rows.
     *
     * @param  array{
     *     title: string,
     *     subtitle?: string|null,
     *     meta?: array<int, array<int, mixed>>,
     *     headers: array<int, string>,
     *     rows?: array<int, array<int, mixed>>,
     *     footer?: array<int, mixed>|null
     * }  $report
     * @return array<int, array<int, mixed>>
     */
    public static function toPlainRows(array $report): array
    {
        $lines = [[$report['title'] ?? 'Report']];

        if (! empty($report['subtitle'])) {
            $lines[] = [$report['subtitle']];
        }

        foreach ($report['meta'] ?? [] as $metaRow) {
            $values = array_values($metaRow);
            $lines[] = [count($values) === 1 ? $values[0] : implode(': ', $values)];
        }

        $lines[] = [];
        $lines[] = array_values($report['headers'] ?? []);

        foreach ($report['rows'] ?? [] as $row) {
            $lines[] = array_values($row);
        }

        if (! empty($report['footer'])) {
            $lines[] = [];
            $lines[] = array_values($report['footer']);
        }

        return $lines;
    }
}
