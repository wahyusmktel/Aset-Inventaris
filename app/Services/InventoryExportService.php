<?php

namespace App\Services;

use App\Models\InventoryItem;
use App\Models\School;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class InventoryExportService
{
    /**
     * Generate and download styled Excel file for inventory items.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param \App\Models\User $admin
     * @return StreamedResponse
     */
    public function export($query, $admin = null): StreamedResponse
    {
        $items = $query->with(['category', 'building', 'room', 'itemFunction', 'creator'])->get();
        $activeSchool = School::where('is_active', true)->first();
        $schoolName = $activeSchool ? strtoupper($activeSchool->name) : 'SMK TELKOM LAMPUNG';

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Data Inventaris');

        // Set default font
        $spreadsheet->getDefaultStyle()->getFont()->setName('Calibri')->setSize(10);

        // 1. BANNER HEADER PERUSAHAAN / SEKOLAH
        $sheet->mergeCells('A1:M1');
        $sheet->setCellValue('A1', $schoolName);
        $sheet->getStyle('A1')->getFont()->setSize(14)->setBold(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFFFFFFF'));
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle('A1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF1E3A8A'); // Navy Blue
        $sheet->getRowDimension(1)->setRowHeight(30);

        // Subtitle Laporan
        $sheet->mergeCells('A2:M2');
        $sheet->setCellValue('A2', 'LAPORAN DATA INVENTARISASI BARANG & ASET SEKOLAH');
        $sheet->getStyle('A2')->getFont()->setSize(11)->setBold(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFFFFFFF'));
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle('A2')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF2563EB'); // Primary Blue
        $sheet->getRowDimension(2)->setRowHeight(22);

        // Sub-info Ekspor
        $sheet->mergeCells('A3:M3');
        $exportTime = now()->translatedFormat('d F Y, H:i') . ' WIB';
        $sheet->setCellValue('A3', "Waktu Unduh: {$exportTime} | Dicetak oleh: {$admin->name} ({$admin->email}) | Total Terdata: " . count($items) . " Item");
        $sheet->getStyle('A3')->getFont()->setSize(9)->setItalic(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FF334155'));
        $sheet->getStyle('A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle('A3')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFF1F5F9'); // Light Slate
        $sheet->getRowDimension(3)->setRowHeight(18);

        // Row 4 is blank separator
        $sheet->getRowDimension(4)->setRowHeight(8);

        // 2. TABLE HEADERS (ROW 5)
        $headers = [
            'A5' => ['title' => 'NO', 'width' => 6, 'align' => Alignment::HORIZONTAL_CENTER],
            'B5' => ['title' => 'NAMA BARANG LENGKAP & SPESIFIKASI', 'width' => 38, 'align' => Alignment::HORIZONTAL_LEFT],
            'C5' => ['title' => 'SERIAL NUMBER (SN)', 'width' => 20, 'align' => Alignment::HORIZONTAL_CENTER],
            'D5' => ['title' => 'MERK / BRAND', 'width' => 16, 'align' => Alignment::HORIZONTAL_LEFT],
            'E5' => ['title' => 'KATEGORI', 'width' => 20, 'align' => Alignment::HORIZONTAL_LEFT],
            'F5' => ['title' => 'FUNGSI BARANG', 'width' => 24, 'align' => Alignment::HORIZONTAL_LEFT],
            'G5' => ['title' => 'LOKASI GEDUNG', 'width' => 16, 'align' => Alignment::HORIZONTAL_LEFT],
            'H5' => ['title' => 'RUANGAN / LAB', 'width' => 24, 'align' => Alignment::HORIZONTAL_LEFT],
            'I5' => ['title' => 'KONDISI', 'width' => 12, 'align' => Alignment::HORIZONTAL_CENTER],
            'J5' => ['title' => 'JUMLAH', 'width' => 10, 'align' => Alignment::HORIZONTAL_CENTER],
            'K5' => ['title' => 'ADMIN PENCATAT', 'width' => 18, 'align' => Alignment::HORIZONTAL_LEFT],
            'L5' => ['title' => 'WAKTU INPUT', 'width' => 18, 'align' => Alignment::HORIZONTAL_CENTER],
            'M5' => ['title' => 'CATATAN', 'width' => 28, 'align' => Alignment::HORIZONTAL_LEFT],
        ];

        foreach ($headers as $cell => $meta) {
            $sheet->setCellValue($cell, $meta['title']);
            $colLetter = substr($cell, 0, 1);
            $sheet->getColumnDimension($colLetter)->setWidth($meta['width']);
        }

        $headerRange = 'A5:M5';
        $sheet->getStyle($headerRange)->getFont()->setBold(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFFFFFFF'));
        $sheet->getStyle($headerRange)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF0F172A'); // Slate 900
        $sheet->getStyle($headerRange)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER)->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getRowDimension(5)->setRowHeight(26);

        // 3. POPULATE DATA ROWS
        $currentRow = 6;
        $totalQuantity = 0;

        foreach ($items as $index => $item) {
            $totalQuantity += (int) $item->quantity;
            $isEven = ($index % 2 === 0);
            $rowBg = $isEven ? 'FFFFFFFF' : 'FFF8FAFC'; // Zebra light striping

            $sheet->setCellValue('A' . $currentRow, $index + 1);
            $sheet->setCellValue('B' . $currentRow, $item->name);
            $sheet->setCellValue('C' . $currentRow, $item->has_no_serial_number ? '(Tanpa SN)' : ($item->serial_number ?: '-'));
            $sheet->setCellValue('D' . $currentRow, $item->brand ?: '-');
            $sheet->setCellValue('E' . $currentRow, $item->category ? $item->category->name : '-');
            $sheet->setCellValue('F' . $currentRow, $item->itemFunction ? $item->itemFunction->name : '-');
            $sheet->setCellValue('G' . $currentRow, $item->building ? $item->building->name : '-');
            $sheet->setCellValue('H' . $currentRow, $item->room ? $item->room->name : '-');
            $sheet->setCellValue('I' . $currentRow, $item->condition);
            $sheet->setCellValue('J' . $currentRow, $item->quantity);
            $sheet->setCellValue('K' . $currentRow, $item->creator ? $item->creator->name : 'Sistem');
            $sheet->setCellValue('L' . $currentRow, $item->created_at ? $item->created_at->format('d/m/Y H:i') : '-');
            $sheet->setCellValue('M' . $currentRow, $item->notes ?: '-');

            // Apply row default styling
            $sheet->getStyle("A{$currentRow}:M{$currentRow}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB($rowBg);
            $sheet->getStyle("A{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("C{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("I{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("J{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("L{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("A{$currentRow}:M{$currentRow}")->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

            // Condition Cell Badge Coloring
            if ($item->condition === 'Baik') {
                $sheet->getStyle("I{$currentRow}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFDCFCE7'); // Emerald 100
                $sheet->getStyle("I{$currentRow}")->getFont()->setBold(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FF15803D')); // Emerald 700
            } else {
                $sheet->getStyle("I{$currentRow}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFFEE2E2'); // Red 100
                $sheet->getStyle("I{$currentRow}")->getFont()->setBold(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFB91C1C')); // Red 700
            }

            $sheet->getRowDimension($currentRow)->setRowHeight(22);
            $currentRow++;
        }

        // 4. SUMMARY ROW / FOOTER
        $summaryRow = $currentRow;
        $sheet->mergeCells("A{$summaryRow}:I{$summaryRow}");
        $sheet->setCellValue("A{$summaryRow}", "TOTAL KESELURUHAN (" . count($items) . " JENIS BARANG)");
        $sheet->setCellValue("J{$summaryRow}", $totalQuantity);
        $sheet->mergeCells("K{$summaryRow}:M{$summaryRow}");
        $sheet->setCellValue("K{$summaryRow}", "TOTAL FISIK: {$totalQuantity} UNIT");

        $summaryRange = "A{$summaryRow}:M{$summaryRow}";
        $sheet->getStyle($summaryRange)->getFont()->setBold(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FF1E3A8A'));
        $sheet->getStyle($summaryRange)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFDBEAFE'); // Blue 100
        $sheet->getStyle("A{$summaryRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT)->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle("J{$summaryRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle("K{$summaryRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getRowDimension($summaryRow)->setRowHeight(24);

        // 5. BORDERS ON TABLE
        $tableRange = "A5:M{$summaryRow}";
        $sheet->getStyle($tableRange)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setARGB('FFCBD5E1'); // Slate 300

        // Output Stream Response
        $fileName = 'Inventaris_Barang_' . date('Ymd_His') . '.xlsx';

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control' => 'max-age=0',
        ]);
    }
}
