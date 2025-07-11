<?php
$title = 'Register - Football Betting Platform';
require_once 'views/layout/header.php';
?>

<div class="container">
    <div class="row justify-content-center py-5">
        <div class="col-md-6 col-lg-5">
            <div class="card">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <i class="bi bi-person-plus-fill display-1 text-primary"></i>
                        <h2 class="mt-3">Join BetFootball!</h2>
                        <p class="text-muted">Create your account and get $100 bonus</p>
                    </div>
                    
                    <form action="/register" method="POST" id="registerForm">
                        <div class="mb-3">
                            <label for="username" class="form-label">
                                <i class="bi bi-person me-2"></i>Username
                            </label>
                            <input type="text" class="form-control" name="username" id="username" required 
                                   placeholder="Choose a username" minlength="3">
                            <div class="form-text">At least 3 characters</div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="email" class="form-label">
                                <i class="bi bi-envelope me-2"></i>Email Address
                            </label>
                            <input type="email" class="form-control" name="email" id="email" required 
                                   placeholder="Enter your email">
                        </div>
                        
                        <div class="mb-3">
                            <label for="password" class="form-label">
                                <i class="bi bi-lock me-2"></i>Password
                            </label>
                            <div class="position-relative">
                                <input type="password" class="form-control" name="password" id="password" required 
                                       placeholder="Create a password" minlength="6">
                                <button type="button" class="btn position-absolute top-50 end-0 translate-middle-y border-0 bg-transparent" 
                                        onclick="togglePassword('password')">
                                    <i class="bi bi-eye" id="password-toggle"></i>
                                </button>
                            </div>
                            <div class="form-text">At least 6 characters</div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="confirm_password" class="form-label">
                                <i class="bi bi-lock-fill me-2"></i>Confirm Password
                            </label>
                            <div class="position-relative">
                                <input type="password" class="form-control" name="confirm_password" id="confirm_password" required 
                                       placeholder="Confirm your password">
                                <button type="button" class="btn position-absolute top-50 end-0 translate-middle-y border-0 bg-transparent" 
                                        onclick="togglePassword('confirm_password')">
                                    <i class="bi bi-eye" id="confirm_password-toggle"></i>
                                </button>
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="terms" required>
                                <label class="form-check-label" for="terms">
                                    I agree to the <a href="#" class="text-decoration-none">Terms of Service</a> 
                                    and <a href="#" class="text-decoration-none">Privacy Policy</a>
                                </label>
                            </div>
                        </div>
                        
                        <div class="alert alert-success mb-4">
                            <i class="bi bi-gift me-2"></i>
                            <strong>Welcome Bonus:</strong> Get $100 free betting credit upon registration!
                        </div>
                        
                        <button type="submit" class="btn btn-primary w-100 py-3">
                            <i class="bi bi-person-plus me-2"></i>
                            Create Account
                        </button>
                    </form>
                    
                    <div class="text-center mt-4">
                        <p class="mb-0">
                            Already have an account? 
                            <a href="/login" class="text-decoration-none">
                                <strong>Sign in here</strong>
                            </a>
                        </p>
                    </div>
                    
                    <div class="text-center mt-4 pt-4 border-top">
                        <div class="row g-3">
                            <div class="col-4">
                                <div class="text-center">
                                    <i class="bi bi-shield-check text-success h5 mb-1"></i>
                                    <div class="small text-muted">Secure</div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="text-center">
                                    <i class="bi bi-lightning-fill text-warning h5 mb-1"></i>
                                    <div class="small text-muted">Instant</div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="text-center">
                                    <i class="bi bi-gift text-info h5 mb-1"></i>
                                    <div class="small text-muted">$100 Bonus</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function togglePassword(fieldId) {
    const field = document.getElementById(fieldId);
    const toggle = document.getElementById(fieldId + '-toggle');
    
    if (field.type === 'password') {
        field.type = 'text';
        toggle.className = 'bi bi-eye-slash';
    } else {
        field.type = 'password';
        toggle.className = 'bi bi-eye';
    }
}

// Form validation
document.getElementById('registerForm').addEventListener('submit', function(e) {
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirm_password').value;
    
    if (password !== confirmPassword) {
        e.preventDefault();
        alert('Passwords do not match!');
        return false;
    }
    
    if (password.length < 6) {
        e.preventDefault();
        alert('Password must be at least 6 characters long!');
        return false;
    }
    
    const username = document.getElementById('username').value;
    if (username.length < 3) {
        e.preventDefault();
        alert('Username must be at least 3 characters long!');
        return false;
    }
    
    return true;
});

// Real-time password confirmation
document.getElementById('confirm_password').addEventListener('input', function() {
    const password = document.getElementById('password').value;
    const confirmPassword = this.value;
    
    if (confirmPassword && password !== confirmPassword) {
        this.classList.add('is-invalid');
    } else {
        this.classList.remove('is-invalid');
    }
});
</script>

<?php require_once 'views/layout/footer.php'; ?>