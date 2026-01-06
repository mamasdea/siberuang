<?php

namespace App\Services;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Illuminate\Support\Facades\DB;

class ExcelConverterService
{
    /**
     * Detect Excel file format and return file info
     */
    public function detectFormat($filePath)
    {
        $spreadsheet = IOFactory::load($filePath);
        $sheetNames = $spreadsheet->getSheetNames();
        $sheetCount = count($sheetNames);

        // Detect format based on sheet structure
        if ($sheetCount == 4) {
            // Check if it's already in template format (2025 format)
            $sheet1 = $spreadsheet->getSheetByName($sheetNames[0]);
            $headerA1 = $sheet1->getCell('A1')->getValue();
            $headerA1Sheet2 = $spreadsheet->getSheetByName($sheetNames[1])->getCell('A1')->getValue();

            if ($headerA1 == 'id' && $headerA1Sheet2 == 'id') {
                return [
                    'format' => 'template',
                    'format_name' => 'Format Template (4 Sheets)',
                    'needs_conversion' => false,
                    'sheet_count' => $sheetCount,
                    'message' => 'File sudah dalam format template yang benar. Siap untuk diimport.'
                ];
            }
        }

        if ($sheetCount == 1) {
            // This is flat format (2026 format), needs conversion
            $sheet = $spreadsheet->getActiveSheet();
            $highestRow = $sheet->getHighestRow();

            return [
                'format' => 'flat',
                'format_name' => 'Format Flat (1 Sheet)',
                'needs_conversion' => true,
                'sheet_count' => $sheetCount,
                'total_rows' => $highestRow,
                'message' => 'File akan dikonversi ke format template.'
            ];
        }

        return [
            'format' => 'unknown',
            'format_name' => 'Format Tidak Dikenali',
            'needs_conversion' => false,
            'sheet_count' => $sheetCount,
            'message' => 'Format file tidak dikenali. Pastikan menggunakan template yang benar.'
        ];
    }

    /**
     * Convert flat format to template format and return preview data
     */
    public function convertAndPreview($filePath)
    {
        $spreadsheet = IOFactory::load($filePath);
        $sheet = $spreadsheet->getActiveSheet();

        // Get max IDs for sequential generation
        $maxProgramId = DB::table('programs')->max('id') ?? 0;
        $maxKegiatanId = DB::table('kegiatans')->max('id') ?? 0;
        $maxSubKegiatanId = DB::table('sub_kegiatans')->max('id') ?? 0;
        $maxRkaId = DB::table('rkas')->max('id') ?? 0;

        // Sequential counters
        $programCounter = 0;
        $kegiatanCounter = 0;
        $subKegiatanCounter = 0;
        $rkaCounter = 0;

        // Read all data
        $data = [];
        $highestRow = $sheet->getHighestRow();

        for ($row = 4; $row <= $highestRow; $row++) {
            $kodeRekening = trim($sheet->getCell('A' . $row)->getValue() ?? '');
            $uraian = trim($sheet->getCell('B' . $row)->getValue() ?? '');
            $anggaran = $sheet->getCell('C' . $row)->getValue() ?? 0;

            if ($kodeRekening && $uraian && $kodeRekening != '5') {
                $kodeParts = explode('.', $kodeRekening);
                $level = count($kodeParts);

                $data[] = [
                    'kode' => $kodeRekening,
                    'uraian' => $uraian,
                    'anggaran' => $anggaran,
                    'level' => $level,
                ];
            }
        }

        // Categorize data
        $programs = [];
        $kegiatans = [];
        $subKegiatans = [];
        $belanjas = [];

        // Map untuk tracking ID
        $programIdMap = []; // kode => id
        $kegiatanIdMap = []; // kode => id
        $subKegiatanIdMap = []; // kode => id

        $currentProgramId = null;
        $currentKegiatanId = null;
        $currentSubKegiatanId = null;

        foreach ($data as $item) {
            $kode = $item['kode'];

            // Program: 2.XX.XX (level 3)
            if (preg_match('/^2\.\d{2}\.\d{2}$/', $kode)) {
                $programCounter++;
                $programId = $maxProgramId + $programCounter;

                $programIdMap[$kode] = $programId;
                $currentProgramId = $programId;

                $programs[] = [
                    'id' => $programId,
                    'kode' => $kode,
                    'nama' => $item['uraian']
                ];
            }
            // Kegiatan: 2.XX.XX.X.XX (level 5)
            elseif (preg_match('/^2\.\d{2}\.\d{2}\.\d\.\d{2}$/', $kode)) {
                $kegiatanCounter++;
                $kegiatanId = $maxKegiatanId + $kegiatanCounter;

                // Extract parent program kode (2.XX.XX from 2.XX.XX.X.XX)
                $parts = explode('.', $kode);
                $parentProgramKode = $parts[0] . '.' . $parts[1] . '.' . $parts[2];

                // Lookup parent ID from map
                $parentProgramId = $programIdMap[$parentProgramKode] ?? null;

                $kegiatanIdMap[$kode] = $kegiatanId;
                $currentKegiatanId = $kegiatanId;

                $kegiatans[] = [
                    'id' => $kegiatanId,
                    'program_id' => $parentProgramId,
                    'kode' => $kode,
                    'nama' => $item['uraian']
                ];
            }
            // Sub Kegiatan: 2.XX.XX.X.XX.XXXX (level 6)
            elseif (preg_match('/^2\.\d{2}\.\d{2}\.\d\.\d{2}\.\d{4}$/', $kode)) {
                $subKegiatanCounter++;
                $subKegiatanId = $maxSubKegiatanId + $subKegiatanCounter;

                // Extract parent kegiatan kode (2.XX.XX.X.XX from 2.XX.XX.X.XX.XXXX)
                $parts = explode('.', $kode);
                $parentKegiatanKode = implode('.', array_slice($parts, 0, 5));

                // Lookup parent ID from map
                $parentKegiatanId = $kegiatanIdMap[$parentKegiatanKode] ?? null;

                $subKegiatanIdMap[$kode] = $subKegiatanId;
                $currentSubKegiatanId = $subKegiatanId;

                $subKegiatans[] = [
                    'id' => $subKegiatanId,
                    'kegiatan_id' => $parentKegiatanId,
                    'kode' => $kode,
                    'nama' => $item['uraian']
                ];
            }
            // Belanja: starts with 5 AND level 6 only
            elseif (preg_match('/^5\./', $kode) && $currentSubKegiatanId !== null) {
                // Calculate level (count segments)
                $level = count(explode('.', $kode));

                // ONLY process level 6 (e.g., 5.1.01.01.01.0001)
                if ($level === 6) {
                    $rkaCounter++;
                    $belanjaId = $maxRkaId + $rkaCounter;

                    $belanjas[] = [
                        'id' => $belanjaId,
                        'sub_kegiatan_id' => $currentSubKegiatanId,
                        'kode_belanja' => $kode,
                        'nama_belanja' => $item['uraian'],
                        'anggaran' => $item['anggaran']
                    ];
                }
                // Level 4 and 5 are silently ignored
            }
        }

        return [
            'programs' => $programs,
            'kegiatans' => $kegiatans,
            'subKegiatans' => $subKegiatans,
            'belanjas' => $belanjas,
            'summary' => [
                'programs_count' => count($programs),
                'kegiatans_count' => count($kegiatans),
                'sub_kegiatans_count' => count($subKegiatans),
                'belanjas_count' => count($belanjas),
            ]
        ];
    }

    /**
     * Create Excel file from converted data
     */
    public function createExcelFile($convertedData, $outputPath)
    {
        $newSpreadsheet = new Spreadsheet();

        // Sheet 1: Programs
        $newSpreadsheet->setActiveSheetIndex(0);
        $sheet1 = $newSpreadsheet->getActiveSheet();
        $sheet1->setTitle('Sheet1');

        $sheet1->setCellValue('A1', 'id');
        $sheet1->setCellValue('B1', 'kode');
        $sheet1->setCellValue('C1', 'nama');

        $row = 2;
        foreach ($convertedData['programs'] as $program) {
            $sheet1->setCellValue('A' . $row, $program['id']);
            $sheet1->setCellValue('B' . $row, $program['kode']);
            $sheet1->setCellValue('C' . $row, $program['nama']);
            $row++;
        }

        // Sheet 2: Kegiatans
        $newSpreadsheet->createSheet();
        $newSpreadsheet->setActiveSheetIndex(1);
        $sheet2 = $newSpreadsheet->getActiveSheet();
        $sheet2->setTitle('Sheet2');

        $sheet2->setCellValue('A1', 'id');
        $sheet2->setCellValue('B1', 'program_id');
        $sheet2->setCellValue('C1', 'kode');
        $sheet2->setCellValue('D1', 'nama');

        $row = 2;
        foreach ($convertedData['kegiatans'] as $kegiatan) {
            $sheet2->setCellValue('A' . $row, $kegiatan['id']);
            $sheet2->setCellValue('B' . $row, $kegiatan['program_id']);
            $sheet2->setCellValue('C' . $row, $kegiatan['kode']);
            $sheet2->setCellValue('D' . $row, $kegiatan['nama']);
            $row++;
        }

        // Sheet 3: Sub Kegiatans
        $newSpreadsheet->createSheet();
        $newSpreadsheet->setActiveSheetIndex(2);
        $sheet3 = $newSpreadsheet->getActiveSheet();
        $sheet3->setTitle('Sheet3');

        $sheet3->setCellValue('A1', 'id');
        $sheet3->setCellValue('B1', 'kegiatan_id');
        $sheet3->setCellValue('C1', 'kode');
        $sheet3->setCellValue('D1', 'nama');

        $row = 2;
        foreach ($convertedData['subKegiatans'] as $subKegiatan) {
            $sheet3->setCellValue('A' . $row, $subKegiatan['id']);
            $sheet3->setCellValue('B' . $row, $subKegiatan['kegiatan_id']);
            $sheet3->setCellValue('C' . $row, $subKegiatan['kode']);
            $sheet3->setCellValue('D' . $row, $subKegiatan['nama']);
            $row++;
        }

        // Sheet 4: Belanjas
        $newSpreadsheet->createSheet();
        $newSpreadsheet->setActiveSheetIndex(3);
        $sheet4 = $newSpreadsheet->getActiveSheet();
        $sheet4->setTitle('Sheet4');

        $sheet4->setCellValue('A1', 'id');
        $sheet4->setCellValue('B1', 'sub_kegiatan_id');
        $sheet4->setCellValue('C1', 'kode_belanja');
        $sheet4->setCellValue('D1', 'nama_belanja');
        $sheet4->setCellValue('E1', 'anggaran');

        $row = 2;
        foreach ($convertedData['belanjas'] as $belanja) {
            $sheet4->setCellValue('A' . $row, $belanja['id']);
            $sheet4->setCellValue('B' . $row, $belanja['sub_kegiatan_id']);
            $sheet4->setCellValue('C' . $row, $belanja['kode_belanja']);
            $sheet4->setCellValue('D' . $row, $belanja['nama_belanja']);
            $sheet4->setCellValue('E' . $row, $belanja['anggaran']);
            $row++;
        }

        // Save file
        $writer = new Xlsx($newSpreadsheet);
        $writer->save($outputPath);

        return true;
    }
}
