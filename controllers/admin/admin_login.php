<?php
require_once '../../models/functions.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit();
}

$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$password = isset($_POST['password']) ? $_POST['password'] : '';

if ($email === '' || $password === '') {
    echo json_encode(['success' => false, 'message' => 'Email and password are required.']);
    exit();
}

// Fetch admin by email
$admins = getAllRecords('admin', "WHERE email = '" . addslashes($email) . "'");

if (empty($admins)) {
    echo json_encode(['success' => false, 'message' => 'Invalid email or password.']);
    exit();
}

$admin = $admins[0];

// Verify password
if (!password_verify($password, $admin['password'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid email or password.']);
    exit();
}

// Successful login: start session and store all admin data
session_start();

// Store admin ID and details
$_SESSION['admin_id'] = $admin['id'];
$_SESSION['admin_username'] = $admin['username'];
$_SESSION['admin_email'] = $admin['email'];
$_SESSION['admin_phone'] = $admin['phone'] ?? '';
$_SESSION['admin_role'] = $admin['role'] ?? 'admin'; // Store role (admin or superadmin)

// Set session timeout (optional - 2 hours)
$_SESSION['last_activity'] = time();

// Determine redirect based on role
$redirect_url = '';
if ($admin['role'] === 'superadmin') {
    $redirect_url = '../admin/admin.php'; // Superadmin goes to admin folder
} else {
    $redirect_url = '../store_admin/admin.php'; // Regular admin goes to store_admin folder
}

echo json_encode([
    'success' => true,
    'message' => 'Login successful.',
    'redirect' => $redirect_url,
    'admin_data' => [
        'id' => $admin['id'],
        'username' => $admin['username'],
        'email' => $admin['email'],
        'role' => $admin['role'] ?? 'admin'
    ]
]);
exit();
?>
