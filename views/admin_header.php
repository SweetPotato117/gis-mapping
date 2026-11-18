<?php
$navigation = [
    'map' => ['admin.php', 'Map management', 'fa-map']
];
if ($is_superadmin) {
    $navigation['store-admins'] = ['add_account.php?role=admin', 'Store admins', 'fa-store'];
    $navigation['super-admins'] = ['add_account.php?role=superadmin', 'Super admins', 'fa-shield-halved'];
}
$navigation['profile'] = ['account.php', 'My account', 'fa-user-gear'];
?>
<header class="account-topbar">
    <a class="account-brand" href="admin.php"><i class="fa-solid fa-map-location-dot" aria-hidden="true"></i> Cauayan City Map</a>
    <span><?= $is_superadmin ? 'Super admin portal' : 'Store admin portal' ?></span>
    <a class="account-profile-link" href="account.php"><i class="fa-regular fa-circle-user" aria-hidden="true"></i> My account</a>
</header>
