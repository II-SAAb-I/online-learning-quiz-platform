<?php
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/session.php';
require_once '../includes/auth.php';

require_login();

$user_id = get_user_id();
$cert_id = intval($_GET['id'] ?? 0);
$course_id = intval($_GET['course_id'] ?? 0);

if ($cert_id) {
    $sql = "SELECT cert.*, c.title as course_title, u.full_name
            FROM certificates cert
            JOIN courses c ON cert.course_id = c.id
            JOIN users u ON cert.user_id = u.id
            WHERE cert.id = ? AND cert.user_id = ?";
    
    $result = db_query($conn, $sql, "ii", [$cert_id, $user_id]);
    
    if (!$result || db_num_rows($result) == 0) {
        set_message('error', 'Certificate not found');
        redirect('my-certificates.php');
    }
    
    $cert = db_fetch_assoc($result);
} elseif ($course_id) {
    $check_sql = "SELECT e.is_completed, c.title
                  FROM enrollments e
                  JOIN courses c ON e.course_id = c.id
                  WHERE e.course_id = ? AND e.user_id = ? AND e.is_completed = 1";
    
    $check_result = db_query($conn, $check_sql, "ii", [$course_id, $user_id]);
    
    if (!$check_result || db_num_rows($check_result) == 0) {
        set_message('error', 'Course not completed or not found');
        redirect('my-certificates.php');
    }
    
    $course = db_fetch_assoc($check_result);
    
    $existing_sql = "SELECT id FROM certificates WHERE course_id = ? AND user_id = ?";
    $existing_result = db_query($conn, $existing_sql, "ii", [$course_id, $user_id]);
    
    if (db_num_rows($existing_result) > 0) {
        $existing = db_fetch_assoc($existing_result);
        redirect('download-certificate.php?id=' . $existing['id']);
    }
    
    $verification_code = strtoupper(uniqid('CERT-'));
    
    $insert_sql = "INSERT INTO certificates (course_id, user_id, verification_code) VALUES (?, ?, ?)";
    db_query($conn, $insert_sql, "iis", [$course_id, $user_id, $verification_code]);
    
    $new_cert_id = db_insert_id($conn);
    
    redirect('download-certificate.php?id=' . $new_cert_id);
} else {
    set_message('error', 'Invalid request');
    redirect('my-certificates.php');
}

require_once '../vendor/autoload.php';

class PDF extends FPDF {
    function Header() {
        $this->SetFont('Arial', 'B', 24);
        $this->SetTextColor(79, 70, 229);
        $this->Cell(0, 20, 'Certificate of Completion', 0, 1, 'C');
        $this->Ln(10);
    }
}

$pdf = new PDF('L', 'mm', 'A4');
$pdf->AddPage();

$pdf->SetFont('Arial', '', 14);
$pdf->SetTextColor(0, 0, 0);
$pdf->Cell(0, 10, 'This is to certify that', 0, 1, 'C');
$pdf->Ln(5);

$pdf->SetFont('Arial', 'B', 28);
$pdf->SetTextColor(79, 70, 229);
$pdf->Cell(0, 15, $cert['full_name'], 0, 1, 'C');
$pdf->Ln(5);

$pdf->SetFont('Arial', '', 14);
$pdf->SetTextColor(0, 0, 0);
$pdf->Cell(0, 10, 'has successfully completed the course', 0, 1, 'C');
$pdf->Ln(5);

$pdf->SetFont('Arial', 'B', 20);
$pdf->SetTextColor(79, 70, 229);
$pdf->MultiCell(0, 10, $cert['course_title'], 0, 'C');
$pdf->Ln(10);

$pdf->SetFont('Arial', '', 12);
$pdf->SetTextColor(100, 100, 100);
$pdf->Cell(0, 10, 'Date: ' . format_date($cert['generated_at']), 0, 1, 'C');
$pdf->Cell(0, 10, 'Verification Code: ' . $cert['verification_code'], 0, 1, 'C');

$filename = 'certificate_' . $cert['verification_code'] . '.pdf';

$pdf->Output('D', $filename);
exit;
?>