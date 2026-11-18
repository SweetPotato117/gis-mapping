<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

function accountReply(int $status, string $message): never
{
    http_response_code($status);
    echo json_encode(['success' => $status < 400, 'message' => $message]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: POST');
    accountReply(405, 'Submit the account form to make changes.');
}
if (!isset($_SESSION['admin_id']) || ($_SESSION['admin_role'] ?? '') !== 'superadmin') {
    accountReply(403, 'Super admin access is required.');
}
if (isset($_SESSION['last_activity']) && time() - $_SESSION['last_activity'] > 7200) {
    accountReply(401, 'Your session expired. Sign in again.');
}
if (!isset($_SESSION['account_manager_token']) ||
    !is_string($_POST['csrf_token'] ?? null) ||
    !hash_equals($_SESSION['account_manager_token'], $_POST['csrf_token'])) {
    accountReply(403, 'Reload the account manager and try again.');
}

require_once __DIR__ . '/../../models/functions.php';

$action = $_POST['action'] ?? '';
$role = $_POST['role'] ?? '';
$id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
if (!in_array($action, ['create', 'update', 'delete'], true) ||
    !in_array($role, ['admin', 'superadmin'], true)) {
    accountReply(400, 'Choose a valid account action.');
}
if ($action !== 'create' && ($role !== 'admin' || !$id)) {
    accountReply(403, 'Only store admin accounts can be changed here.');
}

try {
    if ($action !== 'create') {
        $account = getRecord('admin', "id = $id AND role = 'admin'");
        if (!$account) accountReply(404, 'Store admin account not found.');
    }

    if ($action === 'delete') {
        if (!deleteRecord('admin', "id = $id AND role = 'admin'")) {
            accountReply(500, 'Could not delete the account.');
        }
        accountReply(200, 'Store admin account deleted.');
    }

    $username = trim((string)($_POST['username'] ?? ''));
    $email = trim((string)($_POST['email'] ?? ''));
    $phone = trim((string)($_POST['phone'] ?? ''));
    $password = (string)($_POST['password'] ?? '');
    if ($username === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        accountReply(422, 'Enter a username and a valid email address.');
    }
    if (($action === 'create' || $password !== '') && strlen($password) < 8) {
        accountReply(422, 'Use a password with at least 8 characters.');
    }

    $otherId = $action === 'update' ? $id : 0;
    $duplicate = $conn->prepare('SELECT username = ? AS same_username FROM admin WHERE id <> ? AND (username = ? OR email = ?) ORDER BY same_username DESC LIMIT 1');
    $duplicate->bind_param('siss', $username, $otherId, $username, $email);
    $duplicate->execute();
    $match = $duplicate->get_result()->fetch_assoc();
    if ($match) {
        accountReply(409, $match['same_username'] ? 'That username is already in use.' : 'That email address is already in use.');
    }

    $data = ['username' => $username, 'email' => $email, 'phone' => $phone];
    if ($password !== '') $data['password'] = password_hash($password, PASSWORD_DEFAULT);

    $saved = $action === 'create'
        ? insertRecord('admin', $data + ['role' => $role])
        : updateRecord('admin', $data, "id = $id AND role = 'admin'");
    if (!$saved) accountReply(500, 'Could not save the account.');
    accountReply(200, $action === 'create' ? 'Account created.' : 'Account updated.');
} catch (mysqli_sql_exception $error) {
    if ($error->getCode() === 1062) accountReply(409, 'That username or email address is already in use.');
    if ($error->getCode() === 1451) accountReply(409, 'This account is linked to other records and could not be deleted.');
    error_log('[account_manager] ' . $error->getMessage());
    accountReply(500, 'Could not save the account change.');
}
