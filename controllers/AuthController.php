<?php

require_once 'models/User.php';
require_once 'core/Session.php';
require_once 'core/Helpers.php';

class AuthController {
    private $userModel;
    
    public function __construct() {
        $this->userModel = new User();
    }
    
    public function showLogin() {
        if (Session::isLoggedIn()) {
            Helpers::redirect('/dashboard');
        }
        
        require_once 'views/auth/login.php';
    }
    
    public function showRegister() {
        if (Session::isLoggedIn()) {
            Helpers::redirect('/dashboard');
        }
        
        require_once 'views/auth/register.php';
    }
    
    public function login() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Helpers::redirect('/login');
        }
        
        $email = Helpers::sanitize($_POST['email']);
        $password = $_POST['password'];
        
        // Validation
        $errors = [];
        
        if (empty($email)) {
            $errors[] = 'Email is required';
        } elseif (!Helpers::validateEmail($email)) {
            $errors[] = 'Invalid email format';
        }
        
        if (empty($password)) {
            $errors[] = 'Password is required';
        }
        
        if (!empty($errors)) {
            Session::setFlash('error', implode('<br>', $errors));
            Helpers::redirect('/login');
        }
        
        // Authenticate user
        $user = $this->userModel->authenticate($email, $password);
        
        if ($user) {
            Session::set('user_id', $user['id']);
            Session::set('username', $user['username']);
            Session::set('user_role', $user['role']);
            Session::regenerate();
            
            Session::setFlash('success', 'Welcome back, ' . $user['username'] . '!');
            
            // Redirect based on role
            if ($user['role'] === 'admin') {
                Helpers::redirect('/admin');
            } else {
                Helpers::redirect('/dashboard');
            }
        } else {
            Session::setFlash('error', 'Invalid email or password');
            Helpers::redirect('/login');
        }
    }
    
    public function register() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Helpers::redirect('/register');
        }
        
        $username = Helpers::sanitize($_POST['username']);
        $email = Helpers::sanitize($_POST['email']);
        $password = $_POST['password'];
        $confirmPassword = $_POST['confirm_password'];
        
        // Validation
        $errors = [];
        
        if (empty($username)) {
            $errors[] = 'Username is required';
        } elseif (strlen($username) < 3) {
            $errors[] = 'Username must be at least 3 characters';
        } elseif ($this->userModel->usernameExists($username)) {
            $errors[] = 'Username already exists';
        }
        
        if (empty($email)) {
            $errors[] = 'Email is required';
        } elseif (!Helpers::validateEmail($email)) {
            $errors[] = 'Invalid email format';
        } elseif ($this->userModel->emailExists($email)) {
            $errors[] = 'Email already exists';
        }
        
        if (empty($password)) {
            $errors[] = 'Password is required';
        } elseif (strlen($password) < 6) {
            $errors[] = 'Password must be at least 6 characters';
        }
        
        if ($password !== $confirmPassword) {
            $errors[] = 'Passwords do not match';
        }
        
        if (!empty($errors)) {
            Session::setFlash('error', implode('<br>', $errors));
            Helpers::redirect('/register');
        }
        
        // Create user
        $userData = [
            'username' => $username,
            'email' => $email,
            'password' => $password,
            'role' => 'user',
            'balance' => 100.00 // Starting bonus
        ];
        
        if ($this->userModel->create($userData)) {
            Session::setFlash('success', 'Registration successful! You can now log in with $100 starting bonus.');
            Helpers::redirect('/login');
        } else {
            Session::setFlash('error', 'Registration failed. Please try again.');
            Helpers::redirect('/register');
        }
    }
    
    public function logout() {
        Session::destroy();
        Session::setFlash('success', 'You have been logged out successfully.');
        Helpers::redirect('/');
    }
    
    public function showProfile() {
        if (!Session::isLoggedIn()) {
            Helpers::redirect('/login');
        }
        
        $user = $this->userModel->findById(Session::getUserId());
        require_once 'views/auth/profile.php';
    }
    
    public function updateProfile() {
        if (!Session::isLoggedIn()) {
            Helpers::redirect('/login');
        }
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Helpers::redirect('/profile');
        }
        
        $username = Helpers::sanitize($_POST['username']);
        $email = Helpers::sanitize($_POST['email']);
        $userId = Session::getUserId();
        
        // Validation
        $errors = [];
        
        if (empty($username)) {
            $errors[] = 'Username is required';
        } elseif (strlen($username) < 3) {
            $errors[] = 'Username must be at least 3 characters';
        } elseif ($this->userModel->usernameExists($username, $userId)) {
            $errors[] = 'Username already exists';
        }
        
        if (empty($email)) {
            $errors[] = 'Email is required';
        } elseif (!Helpers::validateEmail($email)) {
            $errors[] = 'Invalid email format';
        } elseif ($this->userModel->emailExists($email, $userId)) {
            $errors[] = 'Email already exists';
        }
        
        if (!empty($errors)) {
            Session::setFlash('error', implode('<br>', $errors));
            Helpers::redirect('/profile');
        }
        
        // Update profile
        if ($this->userModel->updateProfile($userId, ['username' => $username, 'email' => $email])) {
            Session::set('username', $username);
            Session::setFlash('success', 'Profile updated successfully.');
        } else {
            Session::setFlash('error', 'Failed to update profile.');
        }
        
        Helpers::redirect('/profile');
    }
    
    public function changePassword() {
        if (!Session::isLoggedIn()) {
            Helpers::redirect('/login');
        }
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Helpers::redirect('/profile');
        }
        
        $currentPassword = $_POST['current_password'];
        $newPassword = $_POST['new_password'];
        $confirmPassword = $_POST['confirm_password'];
        $userId = Session::getUserId();
        
        // Validation
        $errors = [];
        
        if (empty($currentPassword)) {
            $errors[] = 'Current password is required';
        } else {
            $user = $this->userModel->findById($userId);
            if (!Helpers::verifyPassword($currentPassword, $user['password'])) {
                $errors[] = 'Current password is incorrect';
            }
        }
        
        if (empty($newPassword)) {
            $errors[] = 'New password is required';
        } elseif (strlen($newPassword) < 6) {
            $errors[] = 'New password must be at least 6 characters';
        }
        
        if ($newPassword !== $confirmPassword) {
            $errors[] = 'New passwords do not match';
        }
        
        if (!empty($errors)) {
            Session::setFlash('error', implode('<br>', $errors));
            Helpers::redirect('/profile');
        }
        
        // Change password
        if ($this->userModel->changePassword($userId, $newPassword)) {
            Session::setFlash('success', 'Password changed successfully.');
        } else {
            Session::setFlash('error', 'Failed to change password.');
        }
        
        Helpers::redirect('/profile');
    }
}