<?php
session_start();
require_once '../models/dbconn.php';
require_once '../models/functions.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit;
}

// Optional: Check session timeout (2 hours)
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 7200)) {
    session_unset();
    session_destroy();
    header('Location: index.php?timeout=1');
    exit;
}
$_SESSION['last_activity'] = time();

// Get admin role and info
$admin_role = $_SESSION['admin_role'] ?? 'admin';
$admin_id = $_SESSION['admin_id'];
$admin_username = $_SESSION['admin_username'];
$is_superadmin = ($admin_role === 'superadmin');

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    
    $action = $_POST['action'] ?? '';
    
    // Fetch all admins
    if ($action === 'fetch') {
        $admins = getAllRecords('admin', "ORDER BY id DESC");
        echo json_encode(['success' => true, 'data' => $admins]);
        exit();
    }
    
    // Delete admin
    if ($action === 'delete') {
        $id = $_POST['id'] ?? 0;
        
        // Prevent deleting superadmin
        $admin = getRecord('admin', "id = $id");
        if ($admin && $admin['role'] === 'superadmin') {
            echo json_encode(['success' => false, 'message' => 'Cannot delete super admin account.']);
            exit();
        }
        
        $result = deleteRecord('admin', "id = $id");
        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Admin deleted successfully.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to delete admin.']);
        }
        exit();
    }
    
    // Update admin (edit mode)
    if ($action === 'update') {
        $id = $_POST['id'] ?? 0;
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $role = trim($_POST['role'] ?? '');
        $password = $_POST['password'] ?? '';
        
        // Validate inputs
        if ($username === '' || $email === '' || $role === '') {
            echo json_encode(['success' => false, 'message' => 'Username, email and role are required.']);
            exit();
        }
        
        // Prevent editing superadmin
        $admin = getRecord('admin', "id = $id");
        if ($admin && $admin['role'] === 'superadmin') {
            echo json_encode(['success' => false, 'message' => 'Cannot edit super admin account.']);
            exit();
        }
        
        // Check duplicate username
        $existingUsername = getAllRecords('admin', "WHERE username = '" . mysqli_real_escape_string($conn, $username) . "' AND id != $id");
        if (!empty($existingUsername)) {
            echo json_encode(['success' => false, 'message' => 'Username already exists.']);
            exit();
        }
        
        // Check duplicate email
        $existingEmail = getAllRecords('admin', "WHERE email = '" . mysqli_real_escape_string($conn, $email) . "' AND id != $id");
        if (!empty($existingEmail)) {
            echo json_encode(['success' => false, 'message' => 'Email already exists.']);
            exit();
        }
        
        $data = [
            'username' => $username,
            'email' => $email,
            'phone' => $phone,
            'role' => $role
        ];
        
        // Update password if provided
        if ($password !== '') {
            if (strlen($password) < 8) {
                echo json_encode(['success' => false, 'message' => 'Password must be at least 8 characters long.']);
                exit();
            }
            $data['password'] = password_hash($password, PASSWORD_DEFAULT);
        }
        
        $result = updateRecord('admin', $data, "id = $id");
        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Admin updated successfully.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update admin.']);
        }
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Account Management - Cauayan City</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --primary-color: #2d7a3e;
            --primary-dark: #1f5b2d;
            --secondary-color: #4a90e2;
            --success-color: #28a745;
            --danger-color: #dc3545;
            --dark-bg: #1e1e2d;
            --sidebar-bg: #2c2c3e;
            --panel-bg: #ffffff;
            --border-color: #e0e0e0;
            --text-primary: #333;
            --text-secondary: #666;
            --shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            --topnav-height: 60px;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f5f5;
            padding: 0;
            margin: 0;
        }

        /* Top Navigation Bar */
        .topnav {
            height: var(--topnav-height);
            background: var(--dark-bg);
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 24px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
        }

        .nav-brand h2 {
            font-size: 20px;
            font-weight: 600;
            color: white;
        }

        /* Hamburger Menu Button */
        .hamburger-menu {
            background: transparent;
            border: none;
            cursor: pointer;
            padding: 8px;
            display: flex;
            flex-direction: column;
            gap: 6px;
            justify-content: center;
            align-items: center;
            transition: all 0.3s ease;
            width: 40px;
            height: 40px;
        }

        .hamburger-menu .bar {
            width: 26px;
            height: 3px;
            background: white;
            border-radius: 3px;
            transition: all 0.3s ease;
            display: block;
        }

        .hamburger-menu:hover .bar {
            background: var(--primary-color);
        }

        /* Active state - X animation */
        .hamburger-menu.active .bar:nth-child(1) {
            transform: translateY(9px) rotate(45deg);
        }

        .hamburger-menu.active .bar:nth-child(2) {
            opacity: 0;
            transform: translateX(-10px);
        }

        .hamburger-menu.active .bar:nth-child(3) {
            transform: translateY(-9px) rotate(-45deg);
        }

        /* Dropdown Menu */
        .dropdown-menu {
            position: absolute;
            top: var(--topnav-height);
            right: 0;
            background: var(--sidebar-bg);
            min-width: 240px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.3);
            border-radius: 0 0 0 12px;
            overflow: hidden;
            max-height: 0;
            opacity: 0;
            transition: max-height 0.3s ease, opacity 0.3s ease;
            z-index: 999;
        }

        .dropdown-menu.show {
            max-height: 500px;
            opacity: 1;
        }

        .dropdown-item {
            display: flex;
            align-items: center;
            padding: 16px 24px;
            color: rgba(255, 255, 255, 0.85);
            text-decoration: none;
            transition: all 0.2s ease;
            border-left: 4px solid transparent;
            cursor: pointer;
        }

        .dropdown-item:hover {
            background: rgba(255, 255, 255, 0.08);
            color: white;
            border-left-color: var(--primary-color);
            padding-left: 28px;
        }

        .dropdown-item.active {
            background: rgba(45, 122, 62, 0.2);
            color: white;
            border-left-color: var(--primary-color);
        }

        .dropdown-item i {
            margin-right: 14px;
            font-size: 18px;
            width: 24px;
            text-align: center;
            color: rgba(255, 255, 255, 0.7);
        }

        .dropdown-item:hover i {
            color: var(--primary-color);
        }

        .container {
            max-width: 1400px;
            margin: 80px auto 20px;
            padding: 0 20px;
        }

        .page-header {
            background: var(--dark-bg);
            color: white;
            padding: 24px 32px;
            border-radius: 12px 12px 0 0;
            box-shadow: var(--shadow);
        }

        .page-header h1 {
            font-size: 28px;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .page-header p {
            color: rgba(255, 255, 255, 0.8);
            font-size: 14px;
        }

        .content-wrapper {
            background: white;
            border-radius: 0 0 12px 12px;
            box-shadow: var(--shadow);
            overflow: hidden;
        }

        .action-bar {
            padding: 24px 32px;
            background: #f8f9fa;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 16px;
        }

        .search-box {
            display: flex;
            align-items: center;
            background: white;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 10px 16px;
            flex: 1;
            max-width: 400px;
        }

        .search-box i {
            color: var(--text-secondary);
            margin-right: 10px;
        }

        .search-box input {
            border: none;
            outline: none;
            width: 100%;
            font-size: 14px;
        }

        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-primary {
            background: var(--primary-color);
            color: white;
        }

        .btn-primary:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(45, 122, 62, 0.3);
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
        }

        .btn-secondary:hover {
            background: #5a6268;
        }

        .btn-danger {
            background: var(--danger-color);
            color: white;
        }

        .btn-danger:hover {
            background: #c82333;
        }

        .table-container {
            padding: 32px;
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead {
            background: #f8f9fa;
            border-bottom: 2px solid var(--border-color);
        }

        th {
            padding: 16px;
            text-align: left;
            font-weight: 600;
            color: var(--text-primary);
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        td {
            padding: 16px;
            border-bottom: 1px solid var(--border-color);
            color: var(--text-primary);
        }

        tbody tr {
            transition: background 0.2s;
        }

        tbody tr:hover {
            background: #f8f9fa;
        }

        .role-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: capitalize;
        }

        .role-superadmin {
            background: #fff3cd;
            color: #856404;
        }

        .role-admin {
            background: #d1ecf1;
            color: #0c5460;
        }

        .action-buttons {
            display: flex;
            gap: 8px;
        }

        .icon-btn {
            width: 36px;
            height: 36px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s;
            font-size: 14px;
        }

        .icon-btn.edit {
            background: #e3f2fd;
            color: #1976d2;
        }

        .icon-btn.edit:hover {
            background: #1976d2;
            color: white;
        }

        .icon-btn.delete {
            background: #ffebee;
            color: #c62828;
        }

        .icon-btn.delete:hover {
            background: #c62828;
            color: white;
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 2000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.6);
            animation: fadeIn 0.3s;
        }

        .modal.show {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .modal-content {
            background: white;
            width: 90%;
            max-width: 600px;
            border-radius: 12px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
            animation: slideUp 0.3s;
            overflow: hidden;
        }

        .modal-header {
            background: var(--primary-color);
            color: white;
            padding: 20px 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-header h3 {
            font-size: 20px;
            font-weight: 600;
        }

        .close {
            font-size: 32px;
            font-weight: 300;
            cursor: pointer;
            color: white;
            transition: transform 0.2s;
        }

        .close:hover {
            transform: rotate(90deg);
        }

        .modal-body {
            padding: 32px 24px;
            max-height: 70vh;
            overflow-y: auto;
        }

        .form-group {
            margin-bottom: 24px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--text-primary);
            font-size: 14px;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 12px 14px;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s;
        }

        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(45, 122, 62, 0.1);
        }

        .form-hint {
            display: block;
            margin-top: 6px;
            font-size: 12px;
            color: var(--text-secondary);
        }

        .required {
            color: var(--danger-color);
        }

        .modal-footer {
            padding: 20px 24px;
            background: #f8f9fa;
            border-top: 1px solid var(--border-color);
            display: flex;
            gap: 12px;
            justify-content: flex-end;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: var(--text-secondary);
        }

        .empty-state i {
            font-size: 64px;
            margin-bottom: 20px;
            color: var(--border-color);
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes slideUp {
            from {
                transform: translateY(50px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .hamburger-menu {
                width: 36px;
                height: 36px;
            }
            
            .hamburger-menu .bar {
                width: 22px;
                height: 2.5px;
            }
            
            .hamburger-menu.active .bar:nth-child(1) {
                transform: translateY(8.5px) rotate(45deg);
            }
            
            .hamburger-menu.active .bar:nth-child(3) {
                transform: translateY(-8.5px) rotate(-45deg);
            }
            
            .dropdown-menu {
                min-width: 200px;
                border-radius: 0 0 0 8px;
            }
            
            .dropdown-item {
                padding: 14px 20px;
                font-size: 14px;
            }

            .action-bar {
                flex-direction: column;
                align-items: stretch;
            }

            .search-box {
                max-width: 100%;
            }

            .table-container {
                padding: 16px;
            }

            table {
                font-size: 13px;
            }

            th, td {
                padding: 12px 8px;
            }
        }

        @media (max-width: 480px) {
            .hamburger-menu {
                width: 32px;
                height: 32px;
                gap: 5px;
            }
            
            .hamburger-menu .bar {
                width: 20px;
                height: 2px;
            }
            
            .hamburger-menu.active .bar:nth-child(1) {
                transform: translateY(7px) rotate(45deg);
            }
            
            .hamburger-menu.active .bar:nth-child(3) {
                transform: translateY(-7px) rotate(-45deg);
            }
        }
    </style>
</head>
<body>
    <!-- Top Navigation Bar -->
    <div class="topnav">
        <div class="nav-brand">
            <h2>Cauayan City Map</h2>
        </div>
        <div class="nav-right">
            <!-- Hamburger Menu Button -->
            <button class="hamburger-menu" id="hamburgerBtn" aria-label="Menu">
                <span class="bar"></span>
                <span class="bar"></span>
                <span class="bar"></span>
            </button>
        </div>

        <!-- Dropdown Menu -->
        <div class="dropdown-menu" id="dropdownMenu">
            <a href="account.php" class="dropdown-item">
                <i class="fas fa-user"></i>
                <span>Account</span>
            </a>
            <a href="admin.php" class="dropdown-item">
                <i class="fas fa-map-marked-alt"></i>
                <span>Map Management</span>
            </a>
            <a href="add_account.php" class="dropdown-item active">
                <i class="fas fa-user-cog"></i>
                <span>Account Manager</span>
            </a>
            <a href="../controllers/admin/admin_logout.php" class="dropdown-item">
                <i class="fas fa-sign-out-alt"></i>
                <span>Logout</span>
            </a>
        </div>
    </div>

    <div class="container">
        <div class="page-header">
            <h1><i class="fas fa-users-cog"></i> Admin Account Management</h1>
            <p>Manage administrator accounts and permissions</p>
        </div>

        <div class="content-wrapper">
            <div class="action-bar">
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" id="searchInput" placeholder="Search by name, email, or phone...">
                </div>
                <button class="btn btn-primary" onclick="openAddModal()">
                    <i class="fas fa-plus"></i> Add New Admin
                </button>
            </div>

            <div class="table-container">
                <table id="adminTable">
                    <thead>
                        <tr>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Role</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="adminTableBody">
                        <!-- Data will be populated by JavaScript -->
                    </tbody>
                </table>
            </div>

            <div id="emptyState" class="empty-state" style="display: none;">
                <i class="fas fa-users-slash"></i>
                <h3>No Admins Found</h3>
                <p>Start by adding your first administrator account</p>
            </div>
        </div>
    </div>

    <!-- Add/Edit Admin Modal -->
    <div id="adminModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="modalTitle">Add New Admin</h3>
                <span class="close" onclick="closeModal()">&times;</span>
            </div>
            <div class="modal-body">
                <form id="adminForm">
                    <input type="hidden" id="adminId">
                    
                    <div class="form-group">
                        <label>Username <span class="required">*</span></label>
                        <input type="text" id="username" required>
                        <span class="form-hint">Unique username for login</span>
                    </div>

                    <div class="form-group">
                        <label>Email <span class="required">*</span></label>
                        <input type="email" id="email" required>
                        <span class="form-hint">Valid email address</span>
                    </div>

                    <div class="form-group">
                        <label>Password <span class="required" id="passwordRequired">*</span></label>
                        <input type="password" id="password">
                        <span class="form-hint" id="passwordHint">Minimum 8 characters</span>
                    </div>

                    <div class="form-group">
                        <label>Phone</label>
                        <input type="text" id="phone">
                        <span class="form-hint">Contact number</span>
                    </div>

                    <div class="form-group">
                        <label>Role <span class="required">*</span></label>
                        <select id="role" required>
                            <option value="">Select Role</option>
                            <option value="admin">Admin</option>
                            <option value="superadmin">Superadmin</option>
                        </select>
                        <span class="form-hint">Assign appropriate role</span>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" onclick="closeModal()">Cancel</button>
                <button class="btn btn-primary" onclick="saveAdmin()">
                    <i class="fas fa-save"></i> Save Admin
                </button>
            </div>
        </div>
    </div>

    <script>
        // Hamburger menu toggle
        document.addEventListener('DOMContentLoaded', function() {
            const hamburgerBtn = document.getElementById('hamburgerBtn');
            const dropdownMenu = document.getElementById('dropdownMenu');

            hamburgerBtn.addEventListener('click', function() {
                this.classList.toggle('active');
                dropdownMenu.classList.toggle('show');
            });

            // Close dropdown when clicking outside
            document.addEventListener('click', function(event) {
                if (!event.target.closest('.topnav')) {
                    hamburgerBtn.classList.remove('active');
                    dropdownMenu.classList.remove('show');
                }
            });
        });

        let admins = [];
        let editMode = false;
        let currentAdminId = null;

        document.addEventListener('DOMContentLoaded', function() {
            fetchAdmins();
            setupSearch();
        });

        // Fetch admins from database
        function fetchAdmins() {
            const formData = new FormData();
            formData.append('action', 'fetch');

            fetch('', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    admins = data.data;
                    renderTable();
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        }

        function renderTable(data = admins) {
            const tbody = document.getElementById('adminTableBody');
            const emptyState = document.getElementById('emptyState');

            if (data.length === 0) {
                tbody.innerHTML = '';
                emptyState.style.display = 'block';
                return;
            }

            emptyState.style.display = 'none';
            tbody.innerHTML = data.map(admin => `
                <tr>
                    <td>${admin.username}</td>
                    <td>${admin.email}</td>
                    <td>${admin.phone || 'N/A'}</td>
                    <td>
                        <span class="role-badge role-${admin.role}">
                            ${admin.role}
                        </span>
                    </td>
                    <td>
                        <div class="action-buttons">
                            <button class="icon-btn edit" onclick="editAdmin('${admin.id}')" title="Edit">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="icon-btn delete" onclick="deleteAdmin('${admin.id}')" title="Delete">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            `).join('');
        }

        function setupSearch() {
            document.getElementById('searchInput').addEventListener('input', function(e) {
                const searchTerm = e.target.value.toLowerCase();
                const filtered = admins.filter(admin =>
                    admin.username.toLowerCase().includes(searchTerm) ||
                    admin.email.toLowerCase().includes(searchTerm) ||
                    (admin.phone && admin.phone.toLowerCase().includes(searchTerm))
                );
                renderTable(filtered);
            });
        }

        function openAddModal() {
            editMode = false;
            document.getElementById('modalTitle').textContent = 'Add New Admin';
            document.getElementById('adminForm').reset();
            document.getElementById('adminId').value = '';
            document.getElementById('password').required = true;
            document.getElementById('passwordRequired').style.display = 'inline';
            document.getElementById('passwordHint').textContent = 'Minimum 8 characters';
            document.getElementById('adminModal').classList.add('show');
        }

        function editAdmin(id) {
            editMode = true;
            currentAdminId = id;

            const admin = admins.find(a => String(a.id) === String(id));
            
            if (admin) {
                document.getElementById('modalTitle').textContent = 'Edit Admin';
                document.getElementById('adminId').value = admin.id;
                document.getElementById('username').value = admin.username;
                document.getElementById('email').value = admin.email;
                document.getElementById('phone').value = admin.phone || '';
                document.getElementById('role').value = admin.role;
                document.getElementById('password').value = '';
                document.getElementById('password').required = false;
                document.getElementById('passwordRequired').style.display = 'none';
                document.getElementById('passwordHint').textContent = 'Leave blank to keep current password';
                document.getElementById('adminModal').classList.add('show');
            } else {
                alert('Error: Could not find admin to edit');
            }
        }

        function saveAdmin() {
            const form = document.getElementById('adminForm');
            if (!form.checkValidity()) {
                form.reportValidity();
                return;
            }

            const formData = new FormData();
            formData.append('username', document.getElementById('username').value);
            formData.append('email', document.getElementById('email').value);
            formData.append('phone', document.getElementById('phone').value);
            formData.append('role', document.getElementById('role').value);

            const password = document.getElementById('password').value;
            if (password) {
                formData.append('password', password);
            }

            if (editMode) {
                formData.append('action', 'update');
                formData.append('id', currentAdminId);
                
                fetch('', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.text())
                .then(text => {
                    try {
                        const data = JSON.parse(text);
                        if (data.success) {
                            alert(data.message);
                            closeModal();
                            fetchAdmins();
                        } else {
                            alert(data.message);
                        }
                    } catch (e) {
                        console.error('JSON Parse Error:', e);
                        console.error('Response Text:', text);
                        alert('Server Error: Invalid JSON response. Check console for details.');
                    }
                })
                .catch(error => {
                    console.error('Fetch Error:', error);
                    alert('Network error: ' + error.message);
                });
            } else {
                fetch('../controllers/admin/add_admin_account.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.text())
                .then(text => {
                    try {
                        const data = JSON.parse(text);
                        if (data.success) {
                            alert(data.message);
                            closeModal();
                            fetchAdmins();
                        } else {
                            alert(data.message);
                        }
                    } catch (e) {
                        console.error('JSON Parse Error:', e);
                        console.error('Response Text:', text);
                        alert('Server Error: Invalid JSON response. Check console for details.');
                    }
                })
                .catch(error => {
                    console.error('Fetch Error:', error);
                    alert('Network error: ' + error.message);
                });
            }
        }

        function deleteAdmin(id) {
            if (confirm('Are you sure you want to delete this admin account?')) {
                const formData = new FormData();
                formData.append('action', 'delete');
                formData.append('id', id);

                fetch('', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(data.message);
                        fetchAdmins();
                    } else {
                        alert(data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred');
                });
            }
        }

        function closeModal() {
            document.getElementById('adminModal').classList.remove('show');
            document.getElementById('adminForm').reset();
        }

        window.onclick = function(event) {
            const modal = document.getElementById('adminModal');
            if (event.target === modal) {
                closeModal();
            }
        }
    </script>
</body>
</html>
