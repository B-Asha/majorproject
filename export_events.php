<?php
require 'vendor/autoload.php'; // Include Composer's autoloader

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

include('db_connection.php');

// Fetch events data (you can modify this query as per your needs)
$sql = "SELECT event_name, event_date, department FROM events";
$result = $conn->query($sql);

// Create a new spreadsheet
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Set column headers
$sheet->setCellValue('A1', 'Event Name')
      ->setCellValue('B1', 'Event Date')
      ->setCellValue('C1', 'Department');

// Populate the spreadsheet with events data
$row = 2; // Starting row for data
while ($event = $result->fetch_assoc()) {
    $sheet->setCellValue('A' . $row, $event['event_name']);
    $sheet->setCellValue('B' . $row, $event['event_date']);
    $sheet->setCellValue('C' . $row, $event['department']);
    $row++;
}

// Set the content type and headers to download the file
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="events.xlsx"');
header('Cache-Control: max-age=0');

// Write the file to the output buffer
$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
?>
