// Wait for DOM to be fully loaded
document.addEventListener('DOMContentLoaded', function() {
    // Hamburger Menu Toggle
    const hamburgerBtn = document.getElementById('hamburgerBtn');
    const dropdownMenu = document.getElementById('dropdownMenu');

    if (hamburgerBtn && dropdownMenu) {
        hamburgerBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            hamburgerBtn.classList.toggle('active');
            dropdownMenu.classList.toggle('show');
        });

        document.addEventListener('click', (e) => {
            if (!hamburgerBtn.contains(e.target) && !dropdownMenu.contains(e.target)) {
                hamburgerBtn.classList.remove('active');
                dropdownMenu.classList.remove('show');
            }
        });

        document.querySelectorAll('.dropdown-item').forEach(item => {
            item.addEventListener('click', () => {
                hamburgerBtn.classList.remove('active');
                dropdownMenu.classList.remove('show');
            });
        });
    }

    // Get all form elements with null checks
    const usernameInput = document.getElementById('username');
    const emailInput = document.getElementById('email');
    const phoneInput = document.getElementById('phone');
    const basicInfoForm = document.getElementById('basicInfoForm');
    const passwordForm = document.getElementById('passwordForm');
    const deleteAccountBtn = document.getElementById('deleteAccountBtn');
    const loadingOverlay = document.getElementById('loadingOverlay');
    const cancelBasicInfo = document.getElementById('cancelBasicInfo');
    const cancelPassword = document.getElementById('cancelPassword');
    const profileName = document.getElementById('profileName');
    const profileEmail = document.getElementById('profileEmail');
    const avatarInput = document.getElementById('avatarInput');
    const avatarImage = document.getElementById('avatarImage');
    const updatedAt = document.getElementById('updatedAt');

    // Store original form values ONLY if elements exist
    const originalFormData = {
        username: usernameInput ? usernameInput.value : '',
        email: emailInput ? emailInput.value : '',
        phone: phoneInput ? phoneInput.value : ''
    };

    function showLoading() {
        if (loadingOverlay) loadingOverlay.style.display = 'flex';
    }

    function hideLoading() {
        if (loadingOverlay) loadingOverlay.style.display = 'none';
    }

    function showAlert(elementId, message, type = 'success') {
        const alertElement = document.getElementById(elementId);
        if (!alertElement) return;
        
        alertElement.innerHTML = `
            <div class="alert alert-${type}">
                <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
                <span>${message}</span>
            </div>
        `;
        alertElement.style.display = 'block';
        setTimeout(() => { alertElement.style.display = 'none'; }, 5000);
    }

    // Password toggle
    document.querySelectorAll('.password-toggle').forEach(toggle => {
        toggle.addEventListener('click', function() {
            const targetId = this.getAttribute('data-target');
            const input = document.getElementById(targetId);
            if (!input) return;
            
            if (input.type === 'password') {
                input.type = 'text';
                this.setAttribute('aria-pressed', 'true');
                this.classList.remove('fa-eye');
                this.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                this.setAttribute('aria-pressed', 'false');
                this.classList.remove('fa-eye-slash');
                this.classList.add('fa-eye');
            }
        });
    });

    // Avatar upload
    if (avatarInput && avatarImage) {
        avatarInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    avatarImage.src = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        });
    }

    // Basic Info Form
    if (basicInfoForm && usernameInput && emailInput) {
        basicInfoForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData();
            formData.append('action', 'update_info');
            formData.append('username', usernameInput.value);
            formData.append('email', emailInput.value);
            formData.append('phone', phoneInput ? phoneInput.value : '');
            
            showLoading();
            
            try {
                const response = await fetch('account.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                hideLoading();
                
                if (data.success) {
                    showAlert('basicInfoAlert', data.message, 'success');
                    originalFormData.username = usernameInput.value;
                    originalFormData.email = emailInput.value;
                    originalFormData.phone = phoneInput ? phoneInput.value : '';
                    
                    if (profileName) profileName.textContent = usernameInput.value;
                    if (profileEmail) profileEmail.textContent = emailInput.value;
                    if (avatarImage) {
                        avatarImage.src = `https://ui-avatars.com/api/?name=${encodeURIComponent(usernameInput.value)}&size=150&background=096b64&color=fff`;
                    }
                    
                    const metaSpan = document.querySelector('.profile-meta span:last-child');
                    if (metaSpan) {
                        const now = new Date().toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });
                        metaSpan.innerHTML = `<i class="fas fa-clock"></i> Last updated ${now}`;
                    }
                } else {
                    showAlert('basicInfoAlert', data.message, 'error');
                }
            } catch (error) {
                hideLoading();
                showAlert('basicInfoAlert', 'An error occurred. Please try again.', 'error');
                console.error('Error:', error);
            }
        });
    }

    // Cancel Basic Info
    if (cancelBasicInfo) {
        cancelBasicInfo.addEventListener('click', function() {
            if (usernameInput) usernameInput.value = originalFormData.username;
            if (emailInput) emailInput.value = originalFormData.email;
            if (phoneInput) phoneInput.value = originalFormData.phone;
        });
    }

    // Password Form
    if (passwordForm) {
        passwordForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const currentPassword = document.getElementById('currentPassword');
            const newPassword = document.getElementById('newPassword');
            const confirmPassword = document.getElementById('confirmPassword');
            
            if (!currentPassword || !newPassword || !confirmPassword) {
                showAlert('passwordAlert', 'Form elements not found', 'error');
                return;
            }
            
            if (newPassword.value.length < 8) {
                showAlert('passwordAlert', 'New password must be at least 8 characters', 'error');
                return;
            }
            
            if (newPassword.value !== confirmPassword.value) {
                showAlert('passwordAlert', 'New passwords do not match', 'error');
                return;
            }
            
            const formData = new FormData();
            formData.append('action', 'change_password');
            formData.append('current_password', currentPassword.value);
            formData.append('new_password', newPassword.value);
            formData.append('confirm_password', confirmPassword.value);
            
            showLoading();
            
            try {
                const response = await fetch('account.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                hideLoading();
                
                if (data.success) {
                    showAlert('passwordAlert', data.message, 'success');
                    passwordForm.reset();
                } else {
                    showAlert('passwordAlert', data.message, 'error');
                }
            } catch (error) {
                hideLoading();
                showAlert('passwordAlert', 'An error occurred. Please try again.', 'error');
                console.error('Error:', error);
            }
        });
    }

    // Cancel Password
    if (cancelPassword) {
        cancelPassword.addEventListener('click', function() {
            if (passwordForm) passwordForm.reset();
        });
    }

    // Delete Account
    if (deleteAccountBtn) {
        deleteAccountBtn.addEventListener('click', async function() {
            const confirmation = confirm('Are you absolutely sure you want to delete your account? This action cannot be undone.');
            if (!confirmation) return;
            
            const doubleConfirm = prompt('Type "DELETE" to confirm:');
            if (doubleConfirm !== 'DELETE') {
                alert('Account deletion cancelled');
                return;
            }
            
            const formData = new FormData();
            formData.append('action', 'delete_account');
            
            showLoading();
            
            try {
                const response = await fetch('account.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                hideLoading();
                
                if (data.success) {
                    alert(data.message);
                    window.location.href = '../admin/index.php';
                } else {
                    alert('Error: ' + data.message);
                }
            } catch (error) {
                hideLoading();
                alert('An error occurred. Please try again.');
                console.error('Error:', error);
            }
        });
    }
});
