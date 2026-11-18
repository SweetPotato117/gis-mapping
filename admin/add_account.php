<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit;
}
if (isset($_SESSION['last_activity']) && time() - $_SESSION['last_activity'] > 7200) {
    session_unset();
    session_destroy();
    header('Location: index.php?timeout=1');
    exit;
}
if (($_SESSION['admin_role'] ?? '') !== 'superadmin') {
    header('Location: ../store_admin/account.php');
    exit;
}
$_SESSION['last_activity'] = time();
$_SESSION['account_manager_token'] ??= bin2hex(random_bytes(32));

require_once __DIR__ . '/../models/functions.php';

$roles = [
    'admin' => ['Store admins', 'Store admin', 'fa-store'],
    'superadmin' => ['Super admins', 'Super admin', 'fa-shield-halved']
];
$role = ($_GET['role'] ?? '') === 'superadmin' ? 'superadmin' : 'admin';
[$roleLabel, $accountLabel, $roleIcon] = $roles[$role];
$is_superadmin = true;
$adminPage = $role === 'admin' ? 'store-admins' : 'super-admins';
$list = $conn->prepare('SELECT id, username, email, phone FROM admin WHERE role = ? ORDER BY username, id');
$list->bind_param('s', $role);
$list->execute();
$accounts = $list->get_result()->fetch_all(MYSQLI_ASSOC);

function accountText($value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $roleLabel ?> | Cauayan City Map</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/admin-shell.css?v=1">
    <link rel="stylesheet" href="../assets/css/addaccount.css?v=3">
    <script src="../assets/js/manage-accounts.js?v=1" defer></script>
</head>
<body class="admin-shell">
    <?php require __DIR__ . '/../views/admin_header.php'; ?>
    <div class="account-workspace">
        <?php require __DIR__ . '/../views/admin_sidebar.php'; ?>
        <main class="account-directory">
            <header class="directory-header">
                <h1><?= $roleLabel ?></h1>
                <p><?= $role === 'admin' ? 'Manage the accounts responsible for store locations.' : 'Review the accounts with access to all locations and account management.' ?></p>
            </header>
            <section class="directory-panel" aria-label="<?= $roleLabel ?>">
                <div class="directory-tools">
                    <button type="button" class="account-button primary" id="addAccount"><i class="fa-solid fa-plus" aria-hidden="true"></i> Add <?= strtolower($accountLabel) ?></button>
                    <label class="account-search">
                        <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
                        <input type="search" id="accountSearch" placeholder="Search <?= strtolower($roleLabel) ?>" aria-label="Search <?= strtolower($roleLabel) ?>">
                    </label>
                </div>
                <p class="directory-status" id="directoryStatus" role="status"><?= count($accounts) ?> <?= count($accounts) === 1 ? 'account' : 'accounts' ?></p>
                <div class="directory-table-wrap">
                    <table class="directory-table">
                        <thead><tr><th scope="col">Username</th><th scope="col">Email</th><th scope="col">Phone</th><th scope="col"><?= $role === 'admin' ? 'Actions' : 'Access' ?></th></tr></thead>
                        <tbody id="accountRows">
                            <?php foreach ($accounts as $account): ?>
                                <tr data-id="<?= (int)$account['id'] ?>">
                                    <th scope="row" data-field="username"><?= accountText($account['username']) ?></th>
                                    <td data-field="email"><?= accountText($account['email']) ?></td>
                                    <td data-field="phone"><?= accountText($account['phone'] ?? '') ?></td>
                                    <td>
                                        <?php if ($role === 'admin'): ?>
                                            <div class="account-row-actions">
                                                <button type="button" class="account-button" data-edit>Edit</button>
                                                <button type="button" class="account-button danger" data-delete>Delete</button>
                                            </div>
                                        <?php else: ?>
                                            <span class="account-access"><?= (int)$account['id'] === (int)$_SESSION['admin_id'] ? 'Full access · You' : 'Full access' ?></span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <p class="directory-empty" id="emptyAccounts" <?= $accounts ? 'hidden' : '' ?>>No <?= strtolower($roleLabel) ?> found.</p>
            </section>
        </main>
    </div>

    <dialog class="account-editor" id="accountEditor" aria-labelledby="accountEditorTitle">
        <form id="managedAccountForm">
            <header class="account-editor-header">
                <h2 id="accountEditorTitle">Add <?= strtolower($accountLabel) ?></h2>
                <button type="button" class="account-button editor-close" data-dismiss aria-label="Close form"><i class="fa-solid fa-xmark" aria-hidden="true"></i></button>
            </header>
            <div class="account-editor-fields">
                <input type="hidden" name="csrf_token" value="<?= accountText($_SESSION['account_manager_token']) ?>">
                <input type="hidden" name="id">
                <input type="hidden" name="role" value="<?= $role ?>">
                <p id="accountFormError" class="account-form-error" role="alert" hidden></p>
                <div id="accountEditFields">
                    <p class="account-editor-role"><i class="fa-solid <?= $roleIcon ?>" aria-hidden="true"></i> <?= $accountLabel ?></p>
                    <label for="managedUsername">Username</label>
                    <input type="text" name="username" id="managedUsername" autocomplete="off" maxlength="255" required>
                    <label for="managedEmail">Email address</label>
                    <input type="email" name="email" id="managedEmail" autocomplete="off" maxlength="255" required>
                    <label for="managedPhone">Phone <span>(optional)</span></label>
                    <input type="tel" name="phone" id="managedPhone" autocomplete="off" maxlength="20">
                    <label for="managedPassword">Password</label>
                    <input type="password" name="password" id="managedPassword" autocomplete="new-password" minlength="8" aria-describedby="managedPasswordHint">
                    <p id="managedPasswordHint">Use at least 8 characters.</p>
                </div>
                <p id="accountDeleteMessage" hidden></p>
            </div>
            <footer class="account-editor-actions">
                <button type="button" class="account-button" data-dismiss>Cancel</button>
                <button type="submit" class="account-button primary" id="submitManagedAccount">Create account</button>
            </footer>
        </form>
    </dialog>
</body>
</html>
