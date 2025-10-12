<?php
require_once '../../models/dbconn.php';
require_once '../../models/functions.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit();
}

$username = isset($_POST['username']) ? trim($_POST['username']) : '';
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$password = isset($_POST['password']) ? $_POST['password'] : '';
$phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';

// Basic server-side validation
if ($username === '' || $email === '' || $password === '') {
    echo json_encode(['success' => false, 'message' => 'Username, email and password are required.']);
    exit();
}

// Sanitize inputs
$username = filter_var($username, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$email = filter_var($email, FILTER_SANITIZE_EMAIL);
$phone = $phone !== '' ? filter_var($phone, FILTER_SANITIZE_NUMBER_INT) : '';

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Invalid email address.']);
    exit();
}

// Check if email already exists
$existingAdmin = getAllRecords('admin', "WHERE email = '" . addslashes($email) . "'");
if (!empty($existingAdmin)) {
    echo json_encode(['success' => false, 'message' => 'Email already exists.']);
    exit();
}

// Hash password
$password = password_hash($password, PASSWORD_DEFAULT);

$data = [
    'username' => $username,
    'email' => $email,
    'password' => $password,
    'phone' => $phone,
    'role' => 'admin'
];

$insert = insertRecord('admin', $data);

if ($insert) {
    echo json_encode(['success' => true, 'message' => 'Registration successful. Please log in.']);
    exit();
} else {
    echo json_encode(['success' => false, 'message' => 'Registration failed.']);
    exit();
}

?>
