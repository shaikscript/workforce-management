<?php
/**
 * Helper Functions for AI SQUARE HUB
 */

// Sanitization and validation
function cleanInput($data) {
    return trim(htmlspecialchars($data, ENT_QUOTES, 'UTF-8'));
}

function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

function validatePhoneNumber($phone) {
    return preg_match('/^[0-9+]{10,15}$/', $phone);
}

// INR Number to Words conversion (for payslips)
function numberToWordsINR($number) {
    $number = floatval($number);
    if ($number < 0) return "Minus " . numberToWordsINR(abs($number));
    
    $words = [
        0 => 'Zero', 1 => 'One', 2 => 'Two', 3 => 'Three', 4 => 'Four',
        5 => 'Five', 6 => 'Six', 7 => 'Seven', 8 => 'Eight', 9 => 'Nine',
        10 => 'Ten', 11 => 'Eleven', 12 => 'Twelve', 13 => 'Thirteen',
        14 => 'Fourteen', 15 => 'Fifteen', 16 => 'Sixteen', 17 => 'Seventeen',
        18 => 'Eighteen', 19 => 'Nineteen', 20 => 'Twenty',
        30 => 'Thirty', 40 => 'Forty', 50 => 'Fifty', 60 => 'Sixty',
        70 => 'Seventy', 80 => 'Eighty', 90 => 'Ninety'
    ];
    
    $thousands = ['', 'Thousand', 'Lakh', 'Crore'];
    
    if ($number == 0) return 'Zero';
    
    $integerPart = floor($number);
    $decimalPart = round(($number - $integerPart) * 100);
    
    $result = '';
    $chunkIndex = 0;
    
    while ($integerPart > 0) {
        $chunk = $integerPart % 10000; // Indian system: 3 digits then 2 digits
        if ($chunk > 0) {
            $chunkWords = convertChunkToWords($chunk, $words);
            if ($thousands[$chunkIndex]) {
                $chunkWords .= ' ' . $thousands[$chunkIndex];
            }
            $result = $chunkWords . ($result ? ' ' . $result : '');
        }
        $integerPart = floor($integerPart / 10000);
        $chunkIndex++;
    }
    
    $result .= ' Rupees';
    
    if ($decimalPart > 0) {
        $result .= ' and ' . convertChunkToWords($decimalPart, $words) . ' Paise';
    }
    
    return $result . ' Only';
}

function convertChunkToWords($chunk, $words) {
    $result = '';
    
    if ($chunk >= 100) {
        $result .= $words[$chunk / 100] . ' Hundred';
        $chunk %= 100;
        if ($chunk > 0) $result .= ' and ';
    }
    
    if ($chunk >= 20) {
        $result .= $words[floor($chunk / 10) * 10];
        $remainder = $chunk % 10;
        if ($remainder > 0) {
            $result .= ' ' . $words[$remainder];
        }
    } elseif ($chunk > 0) {
        $result .= $words[$chunk];
    }
    
    return $result;
}

// Date and time helpers
function formatDate($date, $format = 'd M Y') {
    return date($format, strtotime($date));
}

function formatTime($time) {
    return date('h:i A', strtotime($time));
}

function calculateAge($birthDate) {
    $birthDate = new DateTime($birthDate);
    $today = new DateTime();
    $interval = $birthDate->diff($today);
    return $interval->y;
}

// Badge helpers for status
function getStatusBadge($status, $type = 'status') {
    $badges = [
        'active' => ['success', 'Active'],
        'inactive' => ['danger', 'Inactive'],
        'on_leave' => ['warning', 'On Leave'],
        'pending' => ['info', 'Pending'],
        'approved' => ['success', 'Approved'],
        'rejected' => ['danger', 'Rejected'],
        'published' => ['success', 'Published'],
        'draft' => ['secondary', 'Draft'],
        'open' => ['info', 'Open'],
        'in_review' => ['warning', 'In Review'],
        'resolved' => ['success', 'Resolved'],
        'closed' => ['secondary', 'Closed']
    ];
    
    $class = $badges[$status][0] ?? 'secondary';
    $label = $badges[$status][1] ?? $status;
    
    if ($type === 'pill') {
        return '<span class="badge bg-' . $class . ' rounded-pill">' . $label . '</span>';
    } else {
        return '<span class="badge bg-' . $class . '">' . $label . '</span>';
    }
}

// Rating badge helper
function getRatingBadge($score) {
    if ($score >= 90) return '<span class="badge bg-success">Excellent</span>';
    if ($score >= 80) return '<span class="badge bg-info text-dark">Very Good</span>';
    if ($score >= 70) return '<span class="badge bg-primary">Good</span>';
    if ($score >= 60) return '<span class="badge bg-warning text-dark">Average</span>';
    return '<span class="badge bg-danger">Needs Improvement</span>';
}

// File upload validation
function validateFileUpload($file, $allowedTypes, $maxSize = MAX_UPLOAD_SIZE) {
    $errors = [];
    
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errors[] = "File upload error: " . $file['error'];
        return $errors;
    }
    
    if ($file['size'] > $maxSize) {
        $errors[] = "File size exceeds limit (" . ($maxSize / 1024 / 1024) . " MB)";
    }
    
    $fileType = mime_content_type($file['tmp_name']);
    if (!in_array($fileType, $allowedTypes)) {
        $errors[] = "Invalid file type. Allowed: " . implode(', ', $allowedTypes);
    }
    
    return $errors;
}

// Generate secure random filename
function generateRandomFilename($extension) {
    return bin2hex(random_bytes(16)) . '.' . $extension;
}

// CSRF token validation helper
function verifyCsrfToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Session timeout check
function checkSessionTimeout() {
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > SESSION_TIMEOUT) {
        session_destroy();
        header('Location: ' . BASE_URL . 'index.php?error=timeout');
        exit();
    }
    $_SESSION['last_activity'] = time();
}

// Error handling
function handleError($error, $severity = 'danger') {
    $_SESSION['flash_message'] = [
        'message' => $error,
        'severity' => $severity
    ];
}

function displayFlashMessage() {
    if (isset($_SESSION['flash_message'])) {
        $msg = $_SESSION['flash_message'];
        echo '<div class="alert alert-' . $msg['severity'] . ' alert-dismissible fade show" role="alert">';
        echo $msg['message'];
        echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
        echo '</div>';
        unset($_SESSION['flash_message']);
    }
}

// Theme detection
function getCurrentTheme() {
    return isset($_COOKIE['theme']) ? $_COOKIE['theme'] : 'light';
}

// Company settings retrieval
function getCompanySettings() {
    global $db;
    $stmt = $db->query("SELECT * FROM company_settings LIMIT 1");
    return $stmt->fetch() ?: [];
}

?>
