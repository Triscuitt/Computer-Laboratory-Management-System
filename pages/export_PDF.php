<?php
require_once "../model/user.php";
session_start();
require_once "../dbconnection.php";
if ($_SESSION['User']->getRole() != 'admin') {
    header("Location: ../index.php");
    exit();
}
// Include FPDF library (adjust path if needed)
require_once('../fpdf/fpdf.php');

// Get export type and filters
$export_type = $_GET['type'] ?? 'equipment';
$lab_filter = $_GET['lab'] ?? 'all';

// Create PDF class with custom header/footer
class PDF extends FPDF
{
    function Header()
    {
        // Logo (if you have one)
        // $this->Image('logo.png', 10, 6, 30);

        // Title
        $this->SetFont('Arial', 'B', 16);
        $this->SetTextColor(41, 128, 185);
        $this->Cell(0, 10, 'DYCI Computer Lab Management System', 0, 1, 'C');
        $this->SetFont('Arial', '', 10);
        $this->SetTextColor(100, 100, 100);
        $this->Cell(0, 5, 'Generated: ' . date('F d, Y h:i A'), 0, 1, 'C');
        $this->Ln(5);
    }

    function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->SetTextColor(128, 128, 128);
        $this->Cell(0, 10, 'Page ' . $this->PageNo() . ' | DYCI Lab Admin', 0, 0, 'C');
    }

    // Colored table header
    function TableHeader($headers, $widths)
    {
        $this->SetFillColor(41, 128, 185);
        $this->SetTextColor(255, 255, 255);
        $this->SetFont('Arial', 'B', 9);

        for ($i = 0; $i < count($headers); $i++) {
            $this->Cell($widths[$i], 8, $headers[$i], 1, 0, 'C', true);
        }
        $this->Ln();
    }

    // Better text handling for long strings
    function CheckPageBreak($height)
    {
        if ($this->GetY() + $height > 270) {
            $this->AddPage();
            return true;
        }
        return false;
    }
}

// Create PDF instance
$conn = getConnection();
$pdf = new PDF();
$pdf->SetMargins(15, 15, 15);
$pdf->SetAutoPageBreak(true, 20);
$pdf->AddPage();

// Generate content based on type
switch ($export_type) {
    case 'equipment':
        generateEquipmentReport($pdf, $conn, $lab_filter);
        break;
    case 'users':
        generateUserReport($pdf, $conn);
        break;
    case 'equipment_errors':
        generateEquipmentErrorReport($pdf, $conn);
        break;
    case 'sessions':
        generateSessionReport($pdf, $conn, $lab_filter);
        break;
    default:
        die('Invalid export type');
}

// Output PDF
$filename = $export_type . '_report_' . date('Y-m-d') . '.pdf';
$pdf->Output('D', $filename); // D = Download
exit();

// ============================================
// REPORT GENERATION FUNCTIONS
// ============================================

function generateEquipmentReport($pdf, $conn, $lab_filter)
{
    // Report Title
    $pdf->SetFont('Arial', 'B', 14);
    $pdf->SetTextColor(44, 62, 80);
    $pdf->Cell(0, 10, 'Equipment Inventory Report', 0, 1, 'L');

    if ($lab_filter !== 'all') {
        $pdf->SetFont('Arial', 'B', 11);
        $pdf->SetTextColor(52, 152, 219);
        $pdf->Cell(0, 8, 'Laboratory: ' . $lab_filter, 0, 1, 'L');
    } else {
        $pdf->SetFont('Arial', 'B', 11);
        $pdf->SetTextColor(52, 152, 219);
        $pdf->Cell(0, 8, 'All Laboratories', 0, 1, 'L');
    }
    $pdf->Ln(3);

    // Build query
    if ($lab_filter !== 'all') {
        $sql = "SELECT equipment_id, name, serial_number, pc_id, lab_location, status, added_at 
                FROM equipment WHERE lab_location = ? ORDER BY lab_location, pc_id";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $lab_filter);
        $stmt->execute();
        $result = $stmt->get_result();
    } else {
        $sql = "SELECT equipment_id, name, serial_number, pc_id, lab_location, status, added_at 
                FROM equipment ORDER BY lab_location, pc_id";
        $result = $conn->query($sql);
    }

    // Table headers
    $headers = ['#', 'Equipment Name', 'Serial Number', 'PC ID', 'Lab', 'Status'];
    $widths = [10, 50, 35, 25, 25, 35];
    $pdf->TableHeader($headers, $widths);

    // Table data
    $pdf->SetFont('Arial', '', 8);
    $pdf->SetTextColor(0, 0, 0);

    $i = 1;
    $total = 0;
    $available = 0;
    $with_error = 0;
    $pulled_out = 0;

    while ($row = $result->fetch_assoc()) {
        $pdf->CheckPageBreak(7);

        $total++;
        if ($row['status'] === 'Available') $available++;
        elseif ($row['status'] === 'With Error') $with_error++;
        elseif ($row['status'] === 'Pulled out') $pulled_out++;

        // Alternate row colors
        if ($i % 2 == 0) {
            $pdf->SetFillColor(245, 245, 245);
        } else {
            $pdf->SetFillColor(255, 255, 255);
        }

        $pdf->Cell($widths[0], 7, $i, 1, 0, 'C', true);
        $pdf->Cell($widths[1], 7, substr($row['name'], 0, 28), 1, 0, 'L', true);
        $pdf->Cell($widths[2], 7, $row['serial_number'] ?: '-', 1, 0, 'L', true);
        $pdf->Cell($widths[3], 7, $row['pc_id'] ?: '-', 1, 0, 'L', true);
        $pdf->Cell($widths[4], 7, $row['lab_location'], 1, 0, 'C', true);

        // Status with color
        if ($row['status'] === 'Available') {
            $pdf->SetTextColor(39, 174, 96);
        } elseif ($row['status'] === 'With Error') {
            $pdf->SetTextColor(231, 76, 60);
        } elseif ($row['status'] === 'Pulled out') {
            $pdf->SetTextColor(149, 165, 166);
        }

        $pdf->Cell($widths[5], 7, $row['status'], 1, 1, 'C', true);
        $pdf->SetTextColor(0, 0, 0);

        $i++;
    }

    // Summary section
    $pdf->Ln(8);
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->SetTextColor(44, 62, 80);
    $pdf->Cell(0, 8, 'Summary Statistics', 0, 1, 'L');
    $pdf->SetFont('Arial', '', 10);

    $pdf->SetFillColor(240, 248, 255);
    $pdf->Cell(90, 7, 'Total Equipment:', 1, 0, 'L', true);
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(90, 7, $total, 1, 1, 'L', true);

    $pdf->SetFont('Arial', '', 10);
    $pdf->SetFillColor(212, 244, 230);
    $pdf->SetTextColor(39, 174, 96);
    $pdf->Cell(90, 7, 'Available:', 1, 0, 'L', true);
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(90, 7, $available, 1, 1, 'L', true);

    $pdf->SetFont('Arial', '', 10);
    $pdf->SetFillColor(248, 215, 218);
    $pdf->SetTextColor(231, 76, 60);
    $pdf->Cell(90, 7, 'With Error:', 1, 0, 'L', true);
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(90, 7, $with_error, 1, 1, 'L', true);

    $pdf->SetFont('Arial', '', 10);
    $pdf->SetFillColor(230, 230, 230);
    $pdf->SetTextColor(149, 165, 166);
    $pdf->Cell(90, 7, 'Pulled Out:', 1, 0, 'L', true);
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(90, 7, $pulled_out, 1, 1, 'L', true);
}

function generateUserReport($pdf, $conn)
{
    // Report Title
    $pdf->SetFont('Arial', 'B', 14);
    $pdf->SetTextColor(44, 62, 80);
    $pdf->Cell(0, 10, 'User Management Report', 0, 1, 'L');
    $pdf->SetFont('Arial', '', 10);
    $pdf->SetTextColor(100, 100, 100);
    $pdf->Cell(0, 6, 'Active Users in the System', 0, 1, 'L');
    $pdf->Ln(5);

    // Query
    $sql = "SELECT id, first_name, last_name, username, email, role, student_number, created_at 
            FROM users WHERE account_status = 1 ORDER BY role, created_at DESC";
    $result = $conn->query($sql);

    // Table headers
    $headers = ['#', 'Name', 'Username', 'Role', 'Student No.', 'Created'];
    $widths = [10, 45, 35, 30, 35, 25];
    $pdf->TableHeader($headers, $widths);

    // Table data
    $pdf->SetFont('Arial', '', 8);
    $pdf->SetTextColor(0, 0, 0);

    $i = 1;
    $role_counts = ['admin' => 0, 'technician' => 0, 'faculty' => 0, 'student' => 0];

    while ($row = $result->fetch_assoc()) {
        $pdf->CheckPageBreak(7);

        $role_counts[$row['role']]++;

        if ($i % 2 == 0) {
            $pdf->SetFillColor(245, 245, 245);
        } else {
            $pdf->SetFillColor(255, 255, 255);
        }

        $fullname = $row['first_name'] . ' ' . $row['last_name'];

        $pdf->Cell($widths[0], 7, $i, 1, 0, 'C', true);
        $pdf->Cell($widths[1], 7, substr($fullname, 0, 25), 1, 0, 'L', true);
        $pdf->Cell($widths[2], 7, substr($row['username'], 0, 18), 1, 0, 'L', true);
        $pdf->Cell($widths[3], 7, ucfirst($row['role']), 1, 0, 'C', true);
        $pdf->Cell($widths[4], 7, $row['student_number'] ?: '-', 1, 0, 'C', true);
        $pdf->Cell($widths[5], 7, date('M d, Y', strtotime($row['created_at'])), 1, 1, 'C', true);

        $i++;
    }

    // Summary
    $pdf->Ln(8);
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->SetTextColor(44, 62, 80);
    $pdf->Cell(0, 8, 'User Statistics by Role', 0, 1, 'L');
    $pdf->SetFont('Arial', '', 10);

    $colors = [
        'admin' => [231, 76, 60],
        'technician' => [243, 156, 18],
        'faculty' => [155, 89, 182],
        'student' => [39, 174, 96]
    ];

    foreach ($role_counts as $role => $count) {
        $color = $colors[$role];
        $pdf->SetFillColor(240, 248, 255);
        $pdf->SetTextColor($color[0], $color[1], $color[2]);
        $pdf->Cell(90, 7, ucfirst($role) . ':', 1, 0, 'L', true);
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(90, 7, $count, 1, 1, 'L', true);
        $pdf->SetFont('Arial', '', 10);
    }

    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->SetFillColor(220, 235, 250);
    $pdf->Cell(90, 7, 'TOTAL USERS:', 1, 0, 'L', true);
    $pdf->Cell(90, 7, array_sum($role_counts), 1, 1, 'L', true);
}

function generateEquipmentErrorReport($pdf, $conn)
{
    // Report Title
    $pdf->SetFont('Arial', 'B', 14);
    $pdf->SetTextColor(231, 76, 60);
    $pdf->Cell(0, 10, 'Equipment with Errors Report', 0, 1, 'L');
    $pdf->SetFont('Arial', '', 10);
    $pdf->SetTextColor(100, 100, 100);
    $pdf->Cell(0, 6, 'Critical: Equipment requiring immediate attention', 0, 1, 'L');
    $pdf->Ln(5);

    // Query
    $sql = "SELECT equipment_id, name, serial_number, pc_id, lab_location, added_at 
            FROM equipment WHERE status = 'With Error' ORDER BY lab_location, pc_id";
    $result = $conn->query($sql);

    // Table headers
    $headers = ['#', 'Equipment Name', 'Serial Number', 'PC ID', 'Lab Location'];
    $widths = [10, 60, 40, 30, 40];
    $pdf->TableHeader($headers, $widths);

    // Table data
    $pdf->SetFont('Arial', '', 8);
    $pdf->SetTextColor(0, 0, 0);

    $i = 1;
    $total_errors = 0;
    $lab_breakdown = [];

    while ($row = $result->fetch_assoc()) {
        $pdf->CheckPageBreak(7);
        $total_errors++;

        // Count errors per lab
        $lab = $row['lab_location'];
        $lab_breakdown[$lab] = ($lab_breakdown[$lab] ?? 0) + 1;

        // Red background for error rows
        $pdf->SetFillColor(255, 235, 235);

        $pdf->Cell($widths[0], 7, $i, 1, 0, 'C', true);
        $pdf->Cell($widths[1], 7, substr($row['name'], 0, 35), 1, 0, 'L', true);
        $pdf->Cell($widths[2], 7, $row['serial_number'] ?: '-', 1, 0, 'L', true);
        $pdf->Cell($widths[3], 7, $row['pc_id'] ?: '-', 1, 0, 'L', true);
        $pdf->Cell($widths[4], 7, $row['lab_location'], 1, 1, 'C', true);

        $i++;
    }

    if ($total_errors === 0) {
        $pdf->SetFont('Arial', 'I', 11);
        $pdf->SetTextColor(39, 174, 96);
        $pdf->Cell(0, 10, 'Excellent! No equipment with errors found.', 0, 1, 'C');
    } else {
        // Error breakdown by lab
        $pdf->Ln(8);
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->SetTextColor(44, 62, 80);
        $pdf->Cell(0, 8, 'Error Breakdown by Laboratory', 0, 1, 'L');
        $pdf->SetFont('Arial', '', 10);

        foreach ($lab_breakdown as $lab => $count) {
            $pdf->SetFillColor(255, 240, 240);
            $pdf->SetTextColor(231, 76, 60);
            $pdf->Cell(90, 7, $lab . ' Lab:', 1, 0, 'L', true);
            $pdf->SetFont('Arial', 'B', 10);
            $pdf->Cell(90, 7, $count . ' error(s)', 1, 1, 'L', true);
            $pdf->SetFont('Arial', '', 10);
        }

        $pdf->SetFont('Arial', 'B', 10);
        $pdf->SetFillColor(230, 230, 230);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->Cell(90, 7, 'TOTAL ERRORS:', 1, 0, 'L', true);
        $pdf->Cell(90, 7, $total_errors, 1, 1, 'L', true);
    }
}

function generateSessionReport($pdf, $conn, $lab_filter)
{
    // Report Title
    $pdf->SetFont('Arial', 'B', 14);
    $pdf->SetTextColor(44, 62, 80);
    $pdf->Cell(0, 10, 'Lab Session Records Report', 0, 1, 'L');

    if ($lab_filter !== 'all') {
        $pdf->SetFont('Arial', 'B', 11);
        $pdf->SetTextColor(52, 152, 219);
        $pdf->Cell(0, 8, 'Laboratory: ' . $lab_filter, 0, 1, 'L');
    }
    $pdf->Ln(5);

    // Query
    if ($lab_filter !== 'all') {
        $sql = "SELECT s.session_id, s.lab_name, s.session_code, s.created_at, s.is_active,
                u.first_name, u.last_name,
                (SELECT COUNT(*) FROM session_attendance a WHERE a.session_id = s.session_id) as attendees
                FROM lab_sessions s
                JOIN users u ON s.faculty_id = u.id
                WHERE s.lab_name = ?
                ORDER BY s.created_at DESC LIMIT 50";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $lab_filter);
        $stmt->execute();
        $result = $stmt->get_result();
    } else {
        $sql = "SELECT s.session_id, s.lab_name, s.session_code, s.created_at, s.is_active,
                u.first_name, u.last_name,
                (SELECT COUNT(*) FROM session_attendance a WHERE a.session_id = s.session_id) as attendees
                FROM lab_sessions s
                JOIN users u ON s.faculty_id = u.id
                ORDER BY s.created_at DESC LIMIT 50";
        $result = $conn->query($sql);
    }

    // Table headers
    $headers = ['Lab', 'Code', 'Faculty', 'Date & Time', 'Students', 'Status'];
    $widths = [35, 22, 40, 45, 20, 18];
    $pdf->TableHeader($headers, $widths);

    // Table data
    $pdf->SetFont('Arial', '', 7);
    $pdf->SetTextColor(0, 0, 0);

    $i = 1;
    $total_sessions = 0;
    $total_students = 0;

    while ($row = $result->fetch_assoc()) {
        $pdf->CheckPageBreak(7);
        $total_sessions++;
        $total_students += $row['attendees'];

        if ($i % 2 == 0) {
            $pdf->SetFillColor(245, 245, 245);
        } else {
            $pdf->SetFillColor(255, 255, 255);
        }

        $faculty_name = $row['first_name'] . ' ' . $row['last_name'];
        $status = $row['is_active'] ? 'Active' : 'Ended';

        $pdf->Cell($widths[0], 7, $row['lab_name'], 1, 0, 'L', true);
        $pdf->Cell($widths[1], 7, $row['session_code'], 1, 0, 'C', true);
        $pdf->Cell($widths[2], 7, substr($faculty_name, 0, 22), 1, 0, 'L', true);
        $pdf->Cell($widths[3], 7, date('M d, Y g:i A', strtotime($row['created_at'])), 1, 0, 'C', true);
        $pdf->Cell($widths[4], 7, $row['attendees'], 1, 0, 'C', true);

        // Status color
        if ($row['is_active']) {
            $pdf->SetTextColor(39, 174, 96);
        } else {
            $pdf->SetTextColor(149, 165, 166);
        }
        $pdf->Cell($widths[5], 7, $status, 1, 1, 'C', true);
        $pdf->SetTextColor(0, 0, 0);

        $i++;
    }

    // Summary
    if ($total_sessions > 0) {
        $pdf->Ln(8);
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->SetTextColor(44, 62, 80);
        $pdf->Cell(0, 8, 'Session Summary', 0, 1, 'L');
        $pdf->SetFont('Arial', '', 10);

        $pdf->SetFillColor(240, 248, 255);
        $pdf->Cell(90, 7, 'Total Sessions:', 1, 0, 'L', true);
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(90, 7, $total_sessions, 1, 1, 'L', true);

        $pdf->SetFont('Arial', '', 10);
        $pdf->SetFillColor(212, 244, 230);
        $pdf->Cell(90, 7, 'Total Student Logins:', 1, 0, 'L', true);
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(90, 7, $total_students, 1, 1, 'L', true);

        $pdf->SetFont('Arial', '', 10);
        $pdf->SetFillColor(255, 243, 224);
        $pdf->Cell(90, 7, 'Average Students per Session:', 1, 0, 'L', true);
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(90, 7, round($total_students / $total_sessions, 1), 1, 1, 'L', true);
    }
}
