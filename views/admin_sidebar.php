<nav class="account-sidebar" aria-label="Administration">
    <?php foreach ($navigation as $key => [$url, $label, $icon]): ?>
        <?php if ($key === 'store-admins'): ?><span class="account-nav-heading">Accounts</span><?php endif; ?>
        <a href="<?= $url ?>" <?= $key === $adminPage ? 'aria-current="page"' : '' ?>>
            <i class="fa-solid <?= $icon ?>" aria-hidden="true"></i> <?= $label ?>
        </a>
    <?php endforeach; ?>
    <a href="../controllers/admin/admin_logout.php"><i class="fa-solid fa-arrow-right-from-bracket" aria-hidden="true"></i> Sign out</a>
</nav>
