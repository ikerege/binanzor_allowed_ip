<?php

require_once 'models/User.php';
require_once 'models/Settings.php';
require_once 'core/Session.php';
require_once 'core/Helpers.php';

class AuthController {
    private $userModel;
    private $settings;
    
    public function __construct() {
        $this->userModel = new User();
        $this->settings = new Settings();
    }
    
    public function login() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            
            // Validate input
            if (empty($email) || empty($password)) {
                Session::setFlash('error', 'Please enter both email and password.');
                header('Location: /login');
                return;
            }
            
            // Validate email format
            if (!Helpers::validateEmail($email)) {
                Session::setFlash('error', 'Please enter a valid email address.');
                header('Location: /login');
                return;
            }
            
            // Attempt authentication
            $user = $this->userModel->authenticate($email, $password);
            
            if ($user) {
                // Check if user is suspended
                if ($user['status'] === 'suspended') {
                    Session::setFlash('error', 'Your account has been suspended. Please contact support.');
                    header('Location: /login');
                    return;
                }
                
                // Successful login
                Session::set('user_id', $user['id']);
                Session::set('user_name', $user['name']);
                Session::set('user_email', $user['email']);
                Session::set('user_role', $user['role']);
                Session::set('user_balance', $user['balance']);
                
                Session::setFlash('success', 'Welcome back, ' . htmlspecialchars($user['name']) . '!');
                
                // Redirect based on role
                if ($user['role'] === 'admin') {
                    header('Location: /admin');
                } else {
                    header('Location: /dashboard');
                }
                return;
            } else {
                Session::setFlash('error', 'Invalid email or password.');
                header('Location: /login');
                return;
            }
        }
        
        // Show login form
        require_once 'views/auth/login.php';
    }
    
    public function register() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = trim($_POST['name'] ?? '');
            $username = trim($_POST['username'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';
            $acceptTerms = isset($_POST['accept_terms']);
            
            // Validation
            $errors = [];
            
            if (empty($name) || strlen($name) < 2) {
                $errors[] = 'Name must be at least 2 characters long.';
            }
            
            if (empty($username) || strlen($username) < 3) {
                $errors[] = 'Username must be at least 3 characters long.';
            } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
                $errors[] = 'Username can only contain letters, numbers, and underscores.';
            }
            
            if (empty($email) || !Helpers::validateEmail($email)) {
                $errors[] = 'Please enter a valid email address.';
            }
            
            if (empty($password) || strlen($password) < 6) {
                $errors[] = 'Password must be at least 6 characters long.';
            }
            
            if ($password !== $confirmPassword) {
                $errors[] = 'Passwords do not match.';
            }
            
            if (!$acceptTerms) {
                $errors[] = 'You must accept the terms and conditions.';
            }
            
            // Check if email or username already exists
            if ($this->userModel->emailExists($email)) {
                $errors[] = 'Email address is already registered.';
            }
            
            if ($this->userModel->usernameExists($username)) {
                $errors[] = 'Username is already taken.';
            }
            
            if (!empty($errors)) {
                foreach ($errors as $error) {
                    Session::setFlash('error', $error);
                }
                header('Location: /register');
                return;
            }
            
            // Create user account with registration bonus
            $newUserBonus = $this->settings->getNewUserBonus();
            
            $userData = [
                'name' => $name,
                'username' => $username,
                'email' => $email,
                'password' => $password,
                'role' => 'user',
                'balance' => $newUserBonus
            ];
            
            $userId = $this->userModel->create($userData);
            
            if ($userId) {
                Session::setFlash('success', "Registration successful! You've received $" . number_format($newUserBonus, 2) . " bonus. Please login.");
                header('Location: /login');
                return;
            } else {
                Session::setFlash('error', 'Registration failed. Please try again.');
                header('Location: /register');
                return;
            }
        }
        
        // Show registration form
        require_once 'views/auth/register.php';
    }
    
    public function logout() {
        Session::destroy();
        Session::setFlash('success', 'You have been logged out successfully.');
        header('Location: /');
    }
    
    public function profile() {
        if (!Session::isLoggedIn()) {
            header('Location: /login');
            return;
        }
        
        $userId = Session::get('user_id');
        $user = $this->userModel->findById($userId);
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? '';
            
            if ($action === 'update_profile') {
                $this->updateProfile($user);
            } elseif ($action === 'change_password') {
                $this->changePassword($user);
            }
            return;
        }
        
        // Get user transactions
        $transactions = $this->userModel->getUserTransactions($userId, 20);
        
        require_once 'views/auth/profile.php';
    }
    
    private function updateProfile($user) {
        $name = trim($_POST['name'] ?? '');
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        
        $errors = [];
        
        if (empty($name) || strlen($name) < 2) {
            $errors[] = 'Name must be at least 2 characters long.';
        }
        
        if (empty($username) || strlen($username) < 3) {
            $errors[] = 'Username must be at least 3 characters long.';
        } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
            $errors[] = 'Username can only contain letters, numbers, and underscores.';
        }
        
        if (empty($email) || !Helpers::validateEmail($email)) {
            $errors[] = 'Please enter a valid email address.';
        }
        
        // Check if email/username exists for other users
        if ($this->userModel->emailExists($email, $user['id'])) {
            $errors[] = 'Email address is already registered to another user.';
        }
        
        if ($this->userModel->usernameExists($username, $user['id'])) {
            $errors[] = 'Username is already taken by another user.';
        }
        
        if (!empty($errors)) {
            foreach ($errors as $error) {
                Session::setFlash('error', $error);
            }
        } else {
            $updateData = [
                'name' => $name,
                'username' => $username,
                'email' => $email
            ];
            
            if ($this->userModel->updateProfile($user['id'], $updateData)) {
                // Update session data
                Session::set('user_name', $name);
                Session::set('user_email', $email);
                
                Session::setFlash('success', 'Profile updated successfully.');
            } else {
                Session::setFlash('error', 'Failed to update profile. Please try again.');
            }
        }
        
        header('Location: /profile');
    }
    
    private function changePassword($user) {
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_new_password'] ?? '';
        
        $errors = [];
        
        if (empty($currentPassword)) {
            $errors[] = 'Please enter your current password.';
        } elseif (!Helpers::verifyPassword($currentPassword, $user['password'])) {
            $errors[] = 'Current password is incorrect.';
        }
        
        if (empty($newPassword) || strlen($newPassword) < 6) {
            $errors[] = 'New password must be at least 6 characters long.';
        }
        
        if ($newPassword !== $confirmPassword) {
            $errors[] = 'New passwords do not match.';
        }
        
        if ($currentPassword === $newPassword) {
            $errors[] = 'New password must be different from current password.';
        }
        
        if (!empty($errors)) {
            foreach ($errors as $error) {
                Session::setFlash('error', $error);
            }
        } else {
            if ($this->userModel->changePassword($user['id'], $newPassword)) {
                Session::setFlash('success', 'Password changed successfully.');
            } else {
                Session::setFlash('error', 'Failed to change password. Please try again.');
            }
        }
        
        header('Location: /profile');
    }
    
    public function deposit() {
        if (!Session::isLoggedIn()) {
            header('Location: /login');
            return;
        }
        
        $userId = Session::get('user_id');
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $amount = (float) ($_POST['amount'] ?? 0);
            $paymentMethod = trim($_POST['payment_method'] ?? '');
            $transactionRef = trim($_POST['transaction_ref'] ?? '');
            $notes = trim($_POST['notes'] ?? '');
            
            $minDeposit = $this->settings->getMinDeposit();
            $maxDeposit = $this->settings->getMaxDeposit();
            
            $errors = [];
            
            if ($amount < $minDeposit) {
                $errors[] = "Minimum deposit amount is $" . number_format($minDeposit, 2);
            }
            
            if ($amount > $maxDeposit) {
                $errors[] = "Maximum deposit amount is $" . number_format($maxDeposit, 2);
            }
            
            if (empty($paymentMethod)) {
                $errors[] = 'Please select a payment method.';
            }
            
            if (!empty($errors)) {
                foreach ($errors as $error) {
                    Session::setFlash('error', $error);
                }
            } else {
                if ($this->userModel->createDepositRequest($userId, $amount, $paymentMethod, $transactionRef, $notes)) {
                    Session::setFlash('success', 'Deposit request submitted successfully. It will be reviewed by admin.');
                    header('Location: /dashboard');
                    return;
                } else {
                    Session::setFlash('error', 'Failed to submit deposit request. Please try again.');
                }
            }
        }
        
        // Get user's deposit history
        $depositRequests = $this->userModel->getUserDepositRequests($userId);
        
        require_once 'views/deposit.php';
    }
    
    public function withdraw() {
        if (!Session::isLoggedIn()) {
            header('Location: /login');
            return;
        }
        
        $userId = Session::get('user_id');
        $user = $this->userModel->findById($userId);
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $amount = (float) ($_POST['amount'] ?? 0);
            $paymentMethod = trim($_POST['payment_method'] ?? '');
            $paymentDetails = trim($_POST['payment_details'] ?? '');
            $notes = trim($_POST['notes'] ?? '');
            
            $minWithdrawal = $this->settings->getMinWithdrawal();
            $maxWithdrawal = $this->settings->getMaxWithdrawal();
            
            $errors = [];
            
            if ($amount < $minWithdrawal) {
                $errors[] = "Minimum withdrawal amount is $" . number_format($minWithdrawal, 2);
            }
            
            if ($amount > $maxWithdrawal) {
                $errors[] = "Maximum withdrawal amount is $" . number_format($maxWithdrawal, 2);
            }
            
            if ($amount > $user['balance']) {
                $errors[] = 'Insufficient balance for withdrawal.';
            }
            
            if (empty($paymentMethod)) {
                $errors[] = 'Please select a payment method.';
            }
            
            if (empty($paymentDetails)) {
                $errors[] = 'Please enter payment details.';
            }
            
            if (!empty($errors)) {
                foreach ($errors as $error) {
                    Session::setFlash('error', $error);
                }
            } else {
                if ($this->userModel->createWithdrawalRequest($userId, $amount, $paymentMethod, $paymentDetails, $notes)) {
                    Session::setFlash('success', 'Withdrawal request submitted successfully. It will be reviewed by admin.');
                    header('Location: /dashboard');
                    return;
                } else {
                    Session::setFlash('error', 'Failed to submit withdrawal request. Please try again.');
                }
            }
        }
        
        // Get user's withdrawal history
        $withdrawalRequests = $this->userModel->getUserWithdrawalRequests($userId);
        
        require_once 'views/withdraw.php';
    }
}