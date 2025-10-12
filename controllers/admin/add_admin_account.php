<?php
// Prevent any output before JSON
ob_start();

// Start session and include required files
session_start();

// Capture errors
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
});

try {
    require_once '../../models/functions.php';
} catch (Exception $e) {
    ob_end_clean();
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false, 
        'message' => 'File include error: ' . $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ]);
    exit();
}

// Clear any output buffer and set JSON header
ob_end_clean();
header('Content-Type: application/json');

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    echo json_encode([
        'success' => false, 
        'message' => 'Unauthorized access. Please login first.',
        'debug' => 'Session admin_id not set'
    ]);
    exit();
}

// Check if request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false, 
        'message' => 'Invalid request method.',
        'debug' => 'Method: ' . $_SERVER['REQUEST_METHOD']
    ]);
    exit();
}

try {
    // Get and sanitize input data
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
    $role = isset($_POST['role']) ? trim($_POST['role']) : 'admin';

    // Basic server-side validation
    if ($username === '' || $email === '' || $password === '') {
        echo json_encode([
            'success' => false, 
            'message' => 'Username, email and password are required.',
            'debug' => [
                'username' => $username,
                'email' => $email,
                'password_length' => strlen($password)
            ]
        ]);
        exit();
    }

    // Sanitize inputs
    $username = filter_var($username, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $email = filter_var($email, FILTER_SANITIZE_EMAIL);
    $phone = $phone !== '' ? filter_var($phone, FILTER_SANITIZE_NUMBER_INT) : '';
    $role = filter_var($role, FILTER_SANITIZE_FULL_SPECIAL_CHARS);

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode([
            'success' => false, 
            'message' => 'Invalid email address.',
            'debug' => 'Email provided: ' . $email
        ]);
        exit();
    }

    // Validate password length
    if (strlen($password) < 8) {
        echo json_encode([
            'success' => false, 
            'message' => 'Password must be at least 8 characters long.',
            'debug' => 'Password length: ' . strlen($password)
        ]);
        exit();
    }

    // Check database connection
    if (!isset($conn) || !$conn) {
        echo json_encode([
            'success' => false, 
            'message' => 'Database connection error.',
            'debug' => 'Connection variable not set or invalid'
        ]);
        exit();
    }

    // Check if username already exists
    $existingUsername = getAllRecords('admin', "WHERE username = '" . mysqli_real_escape_string($conn, $username) . "'");
    if (!empty($existingUsername)) {
        echo json_encode([
            'success' => false, 
            'message' => 'Username already exists.',
            'debug' => 'Username: ' . $username
        ]);
        exit();
    }

    // Check if email already exists
    $existingEmail = getAllRecords('admin', "WHERE email = '" . mysqli_real_escape_string($conn, $email) . "'");
    if (!empty($existingEmail)) {
        echo json_encode([
            'success' => false, 
            'message' => 'Email already exists.',
            'debug' => 'Email: ' . $email
        ]);
        exit();
    }

    // Hash password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Prepare data for insertion
    $data = [
        'username' => $username,
        'email' => $email,
        'password' => $hashedPassword,
        'phone' => $phone,
        'role' => $role
    ];

    // Insert admin record
    $insert = insertRecord('admin', $data);

    if ($insert) {
        echo json_encode([
            'success' => true, 
            'message' => 'Admin account created successfully.'
        ]);
    } else {
        echo json_encode([
            'success' => false, 
            'message' => 'Failed to create admin account.',
            'debug' => [
                'mysql_error' => mysqli_error($conn),
                'data' => $data
            ]
        ]);
    }

} catch (Exception $e) {
    echo json_encode([
        'success' => false, 
        'message' => 'System Error: ' . $e->getMessage(),
        'debug' => [
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ]
    ]);
}
?>
