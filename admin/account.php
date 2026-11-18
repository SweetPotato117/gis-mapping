<?php
session_start();
require_once '../models/functions.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit();
}

// Handle POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    $action = $_POST['action'];
    $admin_id = $_SESSION['admin_id'];

    // Handle update_info action
    if ($action === 'update_info') {
        $username = mysqli_real_escape_string($conn, trim($_POST['username']));
        $email = mysqli_real_escape_string($conn, trim($_POST['email']));
        $phone = mysqli_real_escape_string($conn, trim($_POST['phone']));

        // Validate inputs
        if (empty($username) || empty($email)) {
            echo json_encode(['success' => false, 'message' => 'Username and email are required']);
            exit();
        }

        // Check if email is already taken by another user
        $checkEmail = getRecord('admin', "email = '$email' AND id != $admin_id");
        if ($checkEmail) {
            echo json_encode(['success' => false, 'message' => 'Email is already in use by another account']);
            exit();
        }

        // Check if username is already taken by another user
        $checkUsername = getRecord('admin', "username = '$username' AND id != $admin_id");
        if ($checkUsername) {
            echo json_encode(['success' => false, 'message' => 'Username is already taken']);
            exit();
        }

        // Update the record
        $updateData = [
            'username' => $username,
            'email' => $email,
            'phone' => $phone
        ];

        $result = updateRecord('admin', $updateData, "id = $admin_id");

        if ($result) {
            // Update session variables
            $_SESSION['admin_username'] = $username;
            $_SESSION['admin_email'] = $email;
            $_SESSION['admin_phone'] = $phone;
            echo json_encode(['success' => true, 'message' => 'Profile updated successfully!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update profile']);
        }
        exit();
    }

    // Handle change_password action
    if ($action === 'change_password') {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];

        // Validate inputs
        if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
            echo json_encode(['success' => false, 'message' => 'All password fields are required']);
            exit();
        }

        if ($new_password !== $confirm_password) {
            echo json_encode(['success' => false, 'message' => 'New passwords do not match']);
            exit();
        }

        if (strlen($new_password) < 8) {
            echo json_encode(['success' => false, 'message' => 'Password must be at least 8 characters']);
            exit();
        }

        // Get current user data
        $user = getRecord('admin', "id = $admin_id");
        if (!$user) {
            echo json_encode(['success' => false, 'message' => 'User not found']);
            exit();
        }

        // Verify current password
        if (!password_verify($current_password, $user['password'])) {
            echo json_encode(['success' => false, 'message' => 'Current password is incorrect']);
            exit();
        }

        // Hash new password
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

        // Update password
        $updateData = ['password' => $hashed_password];
        $result = updateRecord('admin', $updateData, "id = $admin_id");

        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Password changed successfully!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to change password']);
        }
        exit();
    }

    // Handle delete_account action
    if ($action === 'delete_account') {
        // Delete the account
        $result = deleteRecord('admin', "id = $admin_id");

        if ($result) {
            session_destroy();
            echo json_encode(['success' => true, 'message' => 'Account deleted successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to delete account']);
        }
        exit();
    }
}

// Get current user data for display
$admin_id = $_SESSION['admin_id'];
$admin = getRecord('admin', "id = $admin_id");
$admin_username = $admin['username'] ?? '';
$admin_email = $admin['email'] ?? '';
$admin_phone = $admin['phone'] ?? '';
$admin_role = $admin['role'] ?? 'admin';
?>
<?php require __DIR__ . '/../views/admin_profile.php';
