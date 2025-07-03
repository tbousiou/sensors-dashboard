<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../app/config/config.php';

require_once __DIR__ . '/../app/includes/auth.php';


requireAuth();


require_once __DIR__ . '/../app/includes/sensor_data.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Style\Fill;

function exportToExcel() {
    global $pdo;
    
    try {
        $spreadsheet = new Spreadsheet();
        
        // Get all sensors
        $sensors = getAllSensors();
        
        if (empty($sensors)) {
            throw new Exception("No sensors found");
        }
        
        $firstSheet = true;
        
        foreach ($sensors as $index => $sensor) {
            // Create or get sheet
            if ($firstSheet) {
                $sheet = $spreadsheet->getActiveSheet();
                $firstSheet = false;
            } else {
                $sheet = $spreadsheet->createSheet();
            }
            
            // Set sheet title (sanitize for Excel sheet name requirements)
            $sheetName = substr(preg_replace('/[^\w\s-]/', '', $sensor['name']), 0, 31);
            $sheet->setTitle($sheetName ?: "Sensor_" . $sensor['id']);
            
            // Header styling
            $headerStyle = [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4472C4']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
            ];
            
            // Sensor info section
            $sheet->setCellValue('A1', 'Sensor Information');
            $sheet->mergeCells('A1:D1');
            $sheet->getStyle('A1:D1')->applyFromArray($headerStyle);
            
            $sheet->setCellValue('A2', 'Name:');
            $sheet->setCellValue('B2', $sensor['name']);
            $sheet->setCellValue('A3', 'Volume per Hit:');
            $sheet->setCellValue('B3', $sensor['volume_per_hit'] . ' ' . $sensor['unit']);
            $sheet->setCellValue('A4', 'Status:');
            $sheet->setCellValue('B4', $sensor['status']);
            
            // Style sensor info
            $sheet->getStyle('A2:A4')->getFont()->setBold(true);
            
            // Get readings for this sensor
            $stmt = $pdo->prepare("
                SELECT timestamp 
                FROM readings 
                WHERE sensor_id = ? 
                ORDER BY timestamp DESC
            ");
            $stmt->execute([$sensor['id']]);
            $readings = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Readings section
            $readingsStartRow = 6;
            $sheet->setCellValue('A' . $readingsStartRow, 'Readings Data');
            $sheet->mergeCells('A' . $readingsStartRow . ':C' . $readingsStartRow);
            $sheet->getStyle('A' . $readingsStartRow . ':C' . $readingsStartRow)->applyFromArray($headerStyle);
            
            // Readings headers
            $headerRow = $readingsStartRow + 1;
            $sheet->setCellValue('A' . $headerRow, 'Timestamp');
            $sheet->setCellValue('B' . $headerRow, 'Cumulative Volume (' . $sensor['unit'] . ')');
            $sheet->setCellValue('C' . $headerRow, 'Reading #');
            
            $sheet->getStyle('A' . $headerRow . ':C' . $headerRow)->applyFromArray([
                'font' => ['bold' => true],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E7E6E6']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
            ]);
            
            // Add readings data
            $currentRow = $headerRow + 1;
            $readingNumber = count($readings);
            $totalReadings = count($readings);
            
            foreach ($readings as $index => $reading) {
                $volumeForThisReading = $sensor['volume_per_hit'];
                // Calculate cumulative volume from oldest to this reading
                $readingsFromOldest = $totalReadings - $index;
                $cumulativeVolume = $readingsFromOldest * $volumeForThisReading;
                
                $sheet->setCellValue('A' . $currentRow, $reading['timestamp']);
                $sheet->setCellValue('B' . $currentRow, $cumulativeVolume);
                $sheet->setCellValue('C' . $currentRow, $readingNumber);
               
                
                $currentRow++;
                $readingNumber--;
            }
            
            
            
            // Auto-size columns
            foreach (range('A', 'D') as $column) {
                $sheet->getColumnDimension($column)->setAutoSize(true);
            }
        }
        
        // Create summary sheet
        $summarySheet = $spreadsheet->createSheet();
        $summarySheet->setTitle('Summary');
        
        // Summary header
        $summarySheet->setCellValue('A1', 'Sensors Summary');
        $summarySheet->mergeCells('A1:E1');
        $summarySheet->getStyle('A1:E1')->applyFromArray($headerStyle);
        
        // Summary table headers
        $summarySheet->setCellValue('A2', 'Sensor Name');
        $summarySheet->setCellValue('B2', 'Total Readings');
        $summarySheet->setCellValue('C2', 'Total Volume');
        $summarySheet->setCellValue('D2', 'Unit');
        $summarySheet->setCellValue('E2', 'Status');
        
        $summarySheet->getStyle('A2:E2')->applyFromArray([
            'font' => ['bold' => true],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E7E6E6']]
        ]);
        
        // Add summary data
        $summaryRow = 3;
        foreach ($sensors as $sensor) {
            $stmt = $pdo->prepare("
                SELECT COUNT(*) as total_readings
                FROM readings 
                WHERE sensor_id = ?
            ");
            $stmt->execute([$sensor['id']]);
            $stats = $stmt->fetch(PDO::FETCH_ASSOC);
            $totalVolume = $stats['total_readings'] * $sensor['volume_per_hit'];
            
            $summarySheet->setCellValue('A' . $summaryRow, $sensor['name']);
            $summarySheet->setCellValue('B' . $summaryRow, $stats['total_readings']);
            $summarySheet->setCellValue('C' . $summaryRow, $totalVolume);
            $summarySheet->setCellValue('D' . $summaryRow, $sensor['unit']);
            $summarySheet->setCellValue('E' . $summaryRow, $sensor['status']);
            
            $summaryRow++;
        }
        
        // Auto-size summary columns
        foreach (range('A', 'E') as $column) {
            $summarySheet->getColumnDimension($column)->setAutoSize(true);
        }
        
        // Set summary as active sheet
        $spreadsheet->setActiveSheetIndex($spreadsheet->getSheetCount() - 1);
        
        // Generate filename
        $filename = 'sensor_data_' . date('Y-m-d_H-i-s') . '.xlsx';
        
        // Set headers for download
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        header('Cache-Control: max-age=1');
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
        header('Cache-Control: cache, must-revalidate');
        header('Pragma: public');
        
        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
        
    } catch (Exception $e) {
        error_log("Excel export error: " . $e->getMessage());
        header('HTTP/1.1 500 Internal Server Error');
        echo "Error generating Excel file: " . $e->getMessage();
        exit;
    }
}

exportToExcel();
?>
