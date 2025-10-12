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
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Settings - Cauayan City Map</title>
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/account.css">
</head>
<body>
    <!-- Top Navigation Bar -->
    <div class="topnav">
        <div class="nav-brand">
            <h2>Cauayan City Map</h2>
        </div>
        <!-- Hamburger Menu Button -->
        <button class="hamburger-menu" id="hamburgerBtn" aria-label="Menu">
            <span class="bar"></span>
            <span class="bar"></span>
            <span class="bar"></span>
        </button>
        <!-- Dropdown Menu -->
        <div class="dropdown-menu" id="dropdownMenu">
            <a href="account.php" class="dropdown-item active">
                <i class="fas fa-user"></i>
                <span>Account</span>
            </a>
            <a href="admin.php" class="dropdown-item">
                <i class="fas fa-map-marked-alt"></i>
                <span>Map Management</span>
            </a>
            <a href="add_account.php" class="dropdown-item">
                <i class="fas fa-user-cog"></i>
                <span>Account Manager</span>
            </a>
            <a href="../controllers/admin/admin_logout.php" class="dropdown-item">
                <i class="fas fa-sign-out-alt"></i>
                <span>Logout</span>
            </a>
        </div>
    </div>

    <!-- Loading Overlay -->
    <div class="loading-overlay" id="loadingOverlay">
        <div class="spinner"></div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="account-container">
            <div class="page-header">
                <h1>Account Settings</h1>
                <p>Manage your profile information and account preferences</p>
            </div>

            <!-- Profile Grid -->
            <div class="profile-grid">
                <!-- Profile Card -->
                <div class="profile-card">
                    <div class="avatar-wrapper">
                        <img class="avatar" src="https://ui-avatars.com/api/?name=<?php echo urlencode($admin_username); ?>&size=150&background=2d7a3e&color=fff" alt="Profile Picture">
                    </div>
                    <h3 class="profile-name"><?php echo htmlspecialchars($admin_username); ?></h3>
                    <p class="profile-email"><?php echo htmlspecialchars($admin_email); ?></p>
                    <div class="profile-stats">
                        <div class="stat-item">
                            <div class="stat-value"><?php echo ucfirst(htmlspecialchars($admin_role)); ?></div>
                            <div class="stat-label">Role</div>
                        </div>
                    </div>
                </div>

                <!-- Settings Card -->
                <div class="settings-card">
                    <div class="section-header">
                        <h2><i class="fas fa-user-edit"></i> Basic Information</h2>
                    </div>
                    <div class="section-content">
                        <div id="basicInfoAlert" class="alert"></div>
                        <form id="basicInfoForm">
                            <div class="form-group">
                                <label for="username">Username</label>
                                <div class="input-group">
                                    <i class="fas fa-user"></i>
                                    <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($admin_username); ?>" required>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="email">Email Address</label>
                                <div class="input-group">
                                    <i class="fas fa-envelope"></i>
                                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($admin_email); ?>" required>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="phone">Phone Number</label>
                                <div class="input-group">
                                    <i class="fas fa-phone"></i>
                                    <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($admin_phone); ?>" placeholder="Optional">
                                </div>
                            </div>
                            <div class="btn-group">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Save Changes
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Change Password Section -->
            <div class="settings-card">
                <div class="section-header">
                    <h2><i class="fas fa-lock"></i> Change Password</h2>
                </div>
                <div class="section-content">
                    <div id="passwordAlert" class="alert"></div>
                    <form id="passwordForm">
                        <div class="form-group">
                            <label for="currentPassword">Current Password</label>
                            <div class="input-group">
                               
                                <input type="password" id="currentPassword" name="current_password" required>
                                <i class="fas fa-eye password-toggle" data-target="currentPassword"></i>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="newPassword">New Password</label>
                                <div class="input-group">
                                    
                                    <input type="password" id="newPassword" name="new_password" required>
                                    <i class="fas fa-eye password-toggle" data-target="newPassword"></i>
                                </div>
                                <small>Must be at least 8 characters</small>
                            </div>
                            <div class="form-group">
                                <label for="confirmPassword">Confirm New Password</label>
                                <div class="input-group">
                                    
                                    <input type="password" id="confirmPassword" name="confirm_password" required>
                                    <i class="fas fa-eye password-toggle" data-target="confirmPassword"></i>
                                </div>
                            </div>
                        </div>
                        <div class="btn-group">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Update Password
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Danger Zone -->
            <div class="danger-zone">
                <div class="section-header">
                    <h2><i class="fas fa-exclamation-triangle"></i> Danger Zone</h2>
                </div>
                <div class="section-content">
                    <h3>Delete Account</h3>
                    <p>Deleting your account is permanent and cannot be undone. All your data will be lost.</p>
                    <button type="button" class="btn btn-danger" id="deleteAccountBtn">
                        <i class="fas fa-trash-alt"></i> Delete Account
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="../assets/js/account.js"></script>
</body>
</html>
