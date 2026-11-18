<?php
$is_superadmin = $admin_role === 'superadmin';
$adminPage = 'profile';
function profileText($value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>My account | Cauayan City Map</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/admin-shell.css?v=1">
    <link rel="stylesheet" href="../assets/css/account.css?v=3">
    <script src="../assets/js/account.js?v=3" defer></script>
</head>
<body class="admin-shell">
    <?php require __DIR__ . '/admin_header.php'; ?>
    <div class="account-workspace">
        <?php require __DIR__ . '/admin_sidebar.php'; ?>
        <main class="profile-workspace">
            <header class="profile-page-heading">
                <h1>My account</h1>
                <p>Manage your profile and sign-in details.</p>
            </header>

            <section class="profile-card profile-identity" aria-label="Signed-in account">
                <div class="profile-avatar" aria-hidden="true"><i class="fa-solid fa-user"></i></div>
                <div class="profile-identity-details">
                    <h2 id="profileName"><?= profileText($admin_username) ?></h2>
                    <p id="profileEmail"><?= profileText($admin_email) ?></p>
                </div>
                <span class="profile-role"><?= $is_superadmin ? 'Super admin' : 'Store admin' ?></span>
            </section>

            <section class="profile-card" aria-labelledby="profileDetailsHeading">
                <header><h2 id="profileDetailsHeading"><i class="fa-solid fa-address-card" aria-hidden="true"></i> Profile details</h2></header>
                <div id="basicInfoAlert" class="profile-feedback" role="status"></div>
                <form id="basicInfoForm" class="profile-form">
                    <div class="profile-fields">
                        <label>Username<input type="text" id="username" name="username" autocomplete="username" maxlength="255" value="<?= profileText($admin_username) ?>" required></label>
                        <label>Email address<input type="email" id="email" name="email" autocomplete="email" maxlength="255" value="<?= profileText($admin_email) ?>" required></label>
                        <label>Phone <span>(optional)</span><input type="tel" id="phone" name="phone" autocomplete="tel" maxlength="20" value="<?= profileText($admin_phone) ?>"></label>
                    </div>
                    <div class="profile-actions"><button type="submit" class="account-button primary">Save changes</button></div>
                </form>
            </section>

            <section class="profile-card" aria-labelledby="passwordHeading">
                <header><h2 id="passwordHeading"><i class="fa-solid fa-lock" aria-hidden="true"></i> Change password</h2></header>
                <div id="passwordAlert" class="profile-feedback" role="status"></div>
                <form id="passwordForm" class="profile-form">
                    <div class="profile-fields">
                        <?php
                        $passwordFields = [
                            ['currentPassword', 'current_password', 'Current password', 'current-password'],
                            ['newPassword', 'new_password', 'New password', 'new-password'],
                            ['confirmPassword', 'confirm_password', 'Confirm new password', 'new-password']
                        ];
                        foreach ($passwordFields as [$fieldId, $name, $label, $autocomplete]):
                        ?>
                            <div class="profile-password-field">
                                <label for="<?= $fieldId ?>"><?= $label ?></label>
                                <div class="profile-password-input">
                                    <input type="password" id="<?= $fieldId ?>" name="<?= $name ?>" autocomplete="<?= $autocomplete ?>" <?= $name === 'current_password' ? '' : 'minlength="8"' ?> <?= $name === 'new_password' ? 'aria-describedby="profilePasswordHint"' : '' ?> required>
                                    <button type="button" class="fas fa-eye password-toggle" data-target="<?= $fieldId ?>" aria-label="Show <?= strtolower($label) ?>" aria-pressed="false"></button>
                                </div>
                                <?php if ($name === 'new_password'): ?><small id="profilePasswordHint">Use at least 8 characters.</small><?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="profile-actions"><button type="submit" class="account-button primary">Update password</button></div>
                </form>
            </section>

            <section class="profile-card profile-delete" aria-labelledby="deleteHeading">
                <header><h2 id="deleteHeading"><i class="fa-solid fa-trash" aria-hidden="true"></i> Delete account</h2></header>
                <p>Permanently delete your account. This action cannot be undone.</p>
                <div class="profile-actions"><button type="button" class="account-button danger" id="deleteAccountBtn">Delete account</button></div>
            </section>
        </main>
    </div>

    <div class="loading-overlay" id="loadingOverlay" role="status" aria-label="Saving changes"><div class="spinner"></div></div>
</body>
</html>
