<?php
// No PHP code needed at the top for this page
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Authentication - Cauayan City Map</title>
    
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    
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
            --danger-color: #dc3545;
            --success-color: #28a745;
            --text-primary: #333;
            --text-secondary: #666;
            --border-color: #e0e0e0;
            --shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #2d7a3e 0%, #ffffffff 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .auth-container {
            background: white;
            border-radius: 20px;
            box-shadow: var(--shadow);
            overflow: hidden;
            width: 100%;
            max-width: 900px;
            min-height: 600px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            position: relative;
        }

        /* Info Panel */
        .info-panel {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
            color: white;
            padding: 60px 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .info-panel::before {
            content: '';
            position: absolute;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            top: -50%;
            left: -50%;
            animation: rotate 30s linear infinite;
        }

        @keyframes rotate {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        .info-content {
            position: relative;
            z-index: 1;
        }

        .info-panel h1 {
            font-size: 32px;
            margin-bottom: 20px;
            font-weight: 700;
        }

        .info-panel p {
            font-size: 16px;
            line-height: 1.6;
            margin-bottom: 30px;
            opacity: 0.9;
        }

        .info-panel .logo {
            font-size: 80px;
            margin-bottom: 20px;
        }

        .toggle-btn {
            background: white;
            color: var(--primary-color);
            border: 2px solid white;
            padding: 12px 40px;
            border-radius: 30px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            margin-top: 20px;
        }

        .toggle-btn:hover {
            background: transparent;
            color: white;
        }

        /* Form Panel */
        .form-panel {
            padding: 60px 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .form-wrapper {
            position: relative;
            width: 100%;
        }

        .form-container {
            position: absolute;
            width: 100%;
            transition: all 0.6s ease;
            opacity: 0;
            transform: translateX(100%);
            pointer-events: none;
        }

        .form-container.active {
            opacity: 1;
            transform: translateX(0);
            pointer-events: all;
            position: relative;
        }

        .form-header {
            margin-bottom: 30px;
        }

        .form-header h2 {
            color: var(--text-primary);
            font-size: 28px;
            margin-bottom: 10px;
        }

        .form-header p {
            color: var(--text-secondary);
            font-size: 14px;
        }

        .form-group {
            margin-bottom: 20px;
            position: relative;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: var(--text-primary);
            font-weight: 600;
            font-size: 14px;
        }

        .input-wrapper {
            position: relative;
        }

        .input-wrapper i.fa-envelope,
        .input-wrapper i.fa-lock,
        .input-wrapper i.fa-user,
        .input-wrapper i.fa-phone {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-secondary);
            font-size: 16px;
            pointer-events: none;
        }

        .form-group input {
            width: 100%;
            padding: 14px 14px 14px 45px;
            border: 2px solid var(--border-color);
            border-radius: 10px;
            font-size: 14px;
            transition: all 0.3s;
            font-family: inherit;
        }

        .form-group input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 4px rgba(45, 122, 62, 0.1);
        }

        .form-group input.error {
            border-color: var(--danger-color);
        }

        .error-message {
            color: var(--danger-color);
            font-size: 12px;
            margin-top: 5px;
            display: none;
        }

        .error-message.show {
            display: block;
        }

        .password-toggle {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: var(--text-secondary);
            font-size: 16px;
            z-index: 10;
        }

        .password-toggle:hover {
            color: var(--primary-color);
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        .submit-btn {
            width: 100%;
            padding: 14px;
            background: var(--primary-color);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            margin-top: 10px;
        }

        .submit-btn:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(45, 122, 62, 0.3);
        }

        .submit-btn:active {
            transform: translateY(0);
        }

        .submit-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .form-footer {
            text-align: center;
            margin-top: 20px;
            color: var(--text-secondary);
            font-size: 14px;
        }

        .form-footer a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 600;
            cursor: pointer;
        }

        .form-footer a:hover {
            text-decoration: underline;
        }

        .divider {
            display: flex;
            align-items: center;
            margin: 25px 0;
            color: var(--text-secondary);
            font-size: 14px;
        }

        .divider::before,
        .divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: var(--border-color);
        }

        .divider::before {
            margin-right: 15px;
        }

        .divider::after {
            margin-left: 15px;
        }

        .alert {
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: none;
            animation: slideDown 0.3s ease;
        }

        .alert.show {
            display: block;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .auth-container {
                grid-template-columns: 1fr;
                max-width: 450px;
            }

            .info-panel {
                padding: 40px 30px;
            }

            .info-panel h1 {
                font-size: 24px;
            }

            .info-panel .logo {
                font-size: 60px;
            }

            .form-panel {
                padding: 40px 30px;
            }

            .form-row {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 480px) {
            body {
                padding: 10px;
            }

            .form-panel {
                padding: 30px 20px;
            }

            .info-panel {
                padding: 30px 20px;
            }
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <!-- Info Panel -->
        <div class="info-panel">
            <div class="info-content">
                <div class="logo">🗺️</div>
                <h1>Cauayan City Map</h1>
                <p>Manage locations, restaurants, hotels, and tourist destinations in Cauayan City, Isabela</p>
                <button class="toggle-btn" id="toggleBtn">Create Account</button>
            </div>
        </div>

        <!-- Form Panel -->
        <div class="form-panel">
            <div class="form-wrapper">
                <!-- Login Form -->
                <div class="form-container active" id="loginForm">
                    <div class="alert alert-error" id="loginAlert"></div>
                    
                    <div class="form-header">
                        <h2>Welcome Back!</h2>
                        <p>Sign in to continue to admin dashboard</p>
                    </div>

                    <form id="loginFormSubmit">
                        <div class="form-group">
                            <label for="loginEmail">Email Address</label>
                            <div class="input-wrapper">
                                <i class="fas fa-envelope"></i>
                                <input type="email" id="loginEmail" name="email" placeholder="Enter your email" required>
                            </div>
                            <span class="error-message" id="loginEmailError">Please enter a valid email</span>
                        </div>

                        <div class="form-group">
                            <label for="loginPassword">Password</label>
                            <div class="input-wrapper">
                                <i class="fas fa-lock"></i>
                                <input type="password" id="loginPassword" name="password" placeholder="Enter your password" required>
                                <i class="fas fa-eye password-toggle" id="loginPasswordToggle"></i>
                            </div>
                            <span class="error-message" id="loginPasswordError">Password is required</span>
                        </div>

                        <button type="submit" class="submit-btn">
                            <i class="fas fa-sign-in-alt"></i> Sign In
                        </button>
                    </form>

                    <div class="form-footer">
                        Don't have an account? <a id="showRegister">Register here</a>
                    </div>
                </div>

                <!-- Register Form -->
                <div class="form-container" id="registerForm">
                    <div class="alert alert-success" id="registerAlert"></div>
                    
                    <div class="form-header">
                        <h2>Create Account</h2>
                        <p>Fill in the details to get started</p>
                    </div>

                    <form id="registerFormSubmit">
                        <div class="form-group">
                            <label for="registerUsername">Username</label>
                            <div class="input-wrapper">
                                <i class="fas fa-user"></i>
                                <input type="text" id="registerUsername" name="username" placeholder="Choose a username" required>
                            </div>
                            <span class="error-message" id="usernameError">Username must be at least 3 characters</span>
                        </div>

                        <div class="form-group">
                            <label for="registerEmail">Email Address</label>
                            <div class="input-wrapper">
                                <i class="fas fa-envelope"></i>
                                <input type="email" id="registerEmail" name="email" placeholder="Enter your email" required>
                            </div>
                            <span class="error-message" id="emailError">Please enter a valid email</span>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="registerPhone">Phone Number</label>
                                <div class="input-wrapper">
                                    <i class="fas fa-phone"></i>
                                    <input type="tel" id="registerPhone" name="phone" placeholder="Phone number">
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="registerPassword">Password</label>
                            <div class="input-wrapper">
                                <i class="fas fa-lock"></i>
                                <input type="password" id="registerPassword" name="password" placeholder="Create a password" required>
                                <i class="fas fa-eye password-toggle" id="registerPasswordToggle"></i>
                            </div>
                            <span class="error-message" id="passwordError">Password must be at least 6 characters</span>
                        </div>

                        <div class="form-group">
                            <label for="confirmPassword">Confirm Password</label>
                            <div class="input-wrapper">
                                <i class="fas fa-lock"></i>
                                <input type="password" id="confirmPassword" name="confirm_password" placeholder="Confirm your password" required>
                                <i class="fas fa-eye password-toggle" id="confirmPasswordToggle"></i>
                            </div>
                            <span class="error-message" id="confirmPasswordError">Passwords do not match</span>
                        </div>

                        <button type="submit" class="submit-btn">
                            <i class="fas fa-user-plus"></i> Create Account
                        </button>
                    </form>

                    <div class="form-footer">
                        Already have an account? <a id="showLogin">Sign in here</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Toggle between login and register
        const loginFormDiv = document.getElementById('loginForm');
        const registerFormDiv = document.getElementById('registerForm');
        const toggleBtn = document.getElementById('toggleBtn');
        const showRegisterLink = document.getElementById('showRegister');
        const showLoginLink = document.getElementById('showLogin');

        let isLoginView = true;

        function toggleForms() {
            if (isLoginView) {
                loginFormDiv.classList.remove('active');
                registerFormDiv.classList.add('active');
                toggleBtn.textContent = 'Sign In';
                isLoginView = false;
            } else {
                registerFormDiv.classList.remove('active');
                loginFormDiv.classList.add('active');
                toggleBtn.textContent = 'Create Account';
                isLoginView = true;
            }
        }

        toggleBtn.addEventListener('click', toggleForms);
        showRegisterLink.addEventListener('click', toggleForms);
        showLoginLink.addEventListener('click', toggleForms);

        // Password toggle visibility - FIXED
        document.getElementById('loginPasswordToggle').addEventListener('click', function() {
            const input = document.getElementById('loginPassword');
            if (input.type === 'password') {
                input.type = 'text';
                this.classList.remove('fa-eye');
                this.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                this.classList.remove('fa-eye-slash');
                this.classList.add('fa-eye');
            }
        });

        document.getElementById('registerPasswordToggle').addEventListener('click', function() {
            const input = document.getElementById('registerPassword');
            if (input.type === 'password') {
                input.type = 'text';
                this.classList.remove('fa-eye');
                this.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                this.classList.remove('fa-eye-slash');
                this.classList.add('fa-eye');
            }
        });

        document.getElementById('confirmPasswordToggle').addEventListener('click', function() {
            const input = document.getElementById('confirmPassword');
            if (input.type === 'password') {
                input.type = 'text';
                this.classList.remove('fa-eye');
                this.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                this.classList.remove('fa-eye-slash');
                this.classList.add('fa-eye');
            }
        });

        // Login Form Validation and Submit
        const loginFormSubmit = document.getElementById('loginFormSubmit');
        loginFormSubmit.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const email = document.getElementById('loginEmail').value;
            const password = document.getElementById('loginPassword').value;
            
            let isValid = true;
            
            // Email validation
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                document.getElementById('loginEmail').classList.add('error');
                document.getElementById('loginEmailError').classList.add('show');
                isValid = false;
            } else {
                document.getElementById('loginEmail').classList.remove('error');
                document.getElementById('loginEmailError').classList.remove('show');
            }
            
            // Password validation
            if (password.length < 6) {
                document.getElementById('loginPassword').classList.add('error');
                document.getElementById('loginPasswordError').classList.add('show');
                isValid = false;
            } else {
                document.getElementById('loginPassword').classList.remove('error');
                document.getElementById('loginPasswordError').classList.remove('show');
            }
            
            if (isValid) {
                // Prepare data for PHP
                const formData = new FormData();
                formData.append('email', email);
                formData.append('password', password);
                
                // Show loading state
                const submitBtn = this.querySelector('.submit-btn');
                const originalText = submitBtn.innerHTML;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Signing in...';
                submitBtn.disabled = true;
                
                // Send login request
                fetch('../controllers/admin/admin_login.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Show success message with role info
                        const roleText = data.admin_data.role === 'superadmin' ? 'Superadmin' : 'Admin';
                        showAlert('loginAlert', `Welcome ${roleText}! Redirecting...`, 'success');
                        
                        // Use the redirect URL from backend response
                        setTimeout(() => {
                            window.location.href = data.redirect;
                        }, 1000);
                    } else {
                        showAlert('loginAlert', data.message, 'error');
                        submitBtn.innerHTML = originalText;
                        submitBtn.disabled = false;
                    }
                })
                .catch(error => {
                    console.error('Login error:', error);
                    showAlert('loginAlert', 'An error occurred. Please try again.', 'error');
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                });
            }
        });

        // Register Form Validation and Submit
        const registerFormSubmit = document.getElementById('registerFormSubmit');
        registerFormSubmit.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const username = document.getElementById('registerUsername').value;
            const email = document.getElementById('registerEmail').value;
            const phone = document.getElementById('registerPhone').value;
            const password = document.getElementById('registerPassword').value;
            const confirmPassword = document.getElementById('confirmPassword').value;
            
            let isValid = true;
            
            // Username validation
            if (username.length < 3) {
                document.getElementById('registerUsername').classList.add('error');
                document.getElementById('usernameError').classList.add('show');
                isValid = false;
            } else {
                document.getElementById('registerUsername').classList.remove('error');
                document.getElementById('usernameError').classList.remove('show');
            }
            
            // Email validation
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                document.getElementById('registerEmail').classList.add('error');
                document.getElementById('emailError').classList.add('show');
                isValid = false;
            } else {
                document.getElementById('registerEmail').classList.remove('error');
                document.getElementById('emailError').classList.remove('show');
            }
            
            // Password validation
            if (password.length < 6) {
                document.getElementById('registerPassword').classList.add('error');
                document.getElementById('passwordError').classList.add('show');
                isValid = false;
            } else {
                document.getElementById('registerPassword').classList.remove('error');
                document.getElementById('passwordError').classList.remove('show');
            }
            
            // Confirm password validation
            if (password !== confirmPassword) {
                document.getElementById('confirmPassword').classList.add('error');
                document.getElementById('confirmPasswordError').classList.add('show');
                isValid = false;
            } else {
                document.getElementById('confirmPassword').classList.remove('error');
                document.getElementById('confirmPasswordError').classList.remove('show');
            }
            
            if (isValid) {
                // Prepare data for PHP
                const formData = new FormData();
                formData.append('username', username);
                formData.append('email', email);
                formData.append('phone', phone);
                formData.append('password', password);
                
                // Show loading state
                const submitBtn = this.querySelector('.submit-btn');
                const originalText = submitBtn.innerHTML;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creating account...';
                submitBtn.disabled = true;
                
                // Send to PHP
                fetch('../controllers/admin/admin_register.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showAlert('registerAlert', 'Account created successfully! Please login.', 'success');
                        setTimeout(() => toggleForms(), 2000);
                    } else {
                        showAlert('registerAlert', data.message, 'error');
                    }
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                })
                .catch(error => {
                    showAlert('registerAlert', 'An error occurred. Please try again.', 'error');
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                });
            }
        });

        // Show alert function
        function showAlert(alertId, message, type) {
            const alert = document.getElementById(alertId);
            alert.className = `alert alert-${type} show`;
            alert.textContent = message;
            
            setTimeout(() => {
                alert.classList.remove('show');
            }, 5000);
        }
    </script>
</body>
</html>
