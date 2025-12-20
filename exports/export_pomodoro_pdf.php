<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../libs/tcpdf/tcpdf.php';

// Check login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];

// Fetch pomodoro sessions
$stmt = $conn->prepare("
    SELECT session_type, duration, created_at
    FROM pomodoro_sessions
    WHERE user_id = ?
    ORDER BY created_at DESC
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

// Create PDF
$pdf = new TCPDF();
$pdf->SetCreator('StudyFlow');
$pdf->SetAuthor('StudyFlow');
$pdf->SetTitle('Pomodoro Report');
$pdf->SetMargins(15, 15, 15);
$pdf->AddPage();

// PDF Title
$pdf->SetFont('helvetica', 'B', 16);
$pdf->Cell(0, 10, 'Pomodoro Study Report', 0, 1, 'C');

$pdf->Ln(4);
$pdf->SetFont('helvetica', '', 11);
$pdf->Cell(0, 8, 'User: ' . $username, 0, 1);
$pdf->Cell(0, 8, 'Generated on: ' . date('Y-m-d H:i'), 0, 1);

$pdf->Ln(5);

// Table Header
$pdf->SetFont('helvetica', 'B', 11);
$pdf->Cell(60, 8, 'Date', 1);
$pdf->Cell(40, 8, 'Type', 1);
$pdf->Cell(40, 8, 'Duration (min)', 1);
$pdf->Ln();

// Table Data
$pdf->SetFont('helvetica', '', 11);

while ($row = $result->fetch_assoc()) {
    $pdf->Cell(60, 8, date('Y-m-d H:i', strtotime($row['created_at'])), 1);
    $pdf->Cell(40, 8, ucfirst($row['session_type']), 1);
    $pdf->Cell(40, 8, floor($row['duration'] / 60), 1);
    $pdf->Ln();
}

// Output PDF
$pdf->Output('pomodoro_report.pdf', 'D');
exit;
