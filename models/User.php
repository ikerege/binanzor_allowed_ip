<?php

require_once 'config/database.php';
require_once 'core/Helpers.php';

class User {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function create($userData) {
        $sql = "INSERT INTO users (name, username, email, password, role, balance, created_at) 
                VALUES (:name, :username, :email, :password, :role, :balance, NOW())";
        
        $params = [
            ':name' => Helpers::sanitize($userData['name']),
            ':username' => Helpers::sanitize($userData['username']),
            ':email' => Helpers::sanitize($userData['email']),
            ':password' => Helpers::hashPassword($userData['password']),
            ':role' => $userData['role'] ?? 'user',
            ':balance' => $userData['balance'] ?? 100.00
        ];
        
        $result = $this->db->execute($sql, $params);
        
        if ($result) {
            $userId = $this->db->lastInsertId();
            // Log the registration bonus transaction
            $this->logTransaction($userId, 'admin_adjustment', 100.00, 0.00, 100.00, 'Registration bonus');
            return $userId;
        }
        
        return false;
    }
    
    public function findByEmail($email) {
        $sql = "SELECT * FROM users WHERE email = :email LIMIT 1";
        return $this->db->fetch($sql, [':email' => $email]);
    }
    
    public function findByUsername($username) {
        $sql = "SELECT * FROM users WHERE username = :username LIMIT 1";
        return $this->db->fetch($sql, [':username' => $username]);
    }
    
    public function findById($id) {
        $sql = "SELECT * FROM users WHERE id = :id LIMIT 1";
        return $this->db->fetch($sql, [':id' => $id]);
    }
    
    public function authenticate($email, $password) {
        $user = $this->findByEmail($email);
        
        if ($user && Helpers::verifyPassword($password, $user['password'])) {
            return $user;
        }
        
        return false;
    }
    
    public function updateBalance($userId, $amount, $description = '', $type = 'admin_adjustment') {
        $user = $this->findById($userId);
        if (!$user) return false;
        
        $balanceBefore = $user['balance'];
        $balanceAfter = $balanceBefore + $amount;
        
        $sql = "UPDATE users SET balance = balance + :amount WHERE id = :user_id";
        $result = $this->db->execute($sql, [
            ':amount' => $amount,
            ':user_id' => $userId
        ]);
        
        if ($result) {
            $this->logTransaction($userId, $type, abs($amount), $balanceBefore, $balanceAfter, $description);
        }
        
        return $result;
    }
    
    public function deductBalance($userId, $amount, $description = '', $type = 'bet_placed') {
        $user = $this->findById($userId);
        if (!$user || $user['balance'] < $amount) return false;
        
        $balanceBefore = $user['balance'];
        $balanceAfter = $balanceBefore - $amount;
        
        $sql = "UPDATE users SET balance = balance - :amount WHERE id = :user_id AND balance >= :amount";
        $result = $this->db->execute($sql, [
            ':amount' => $amount,
            ':user_id' => $userId
        ]);
        
        if ($result) {
            $this->logTransaction($userId, $type, $amount, $balanceBefore, $balanceAfter, $description);
        }
        
        return $result;
    }
    
    public function getBalance($userId) {
        $sql = "SELECT balance FROM users WHERE id = :user_id";
        $result = $this->db->fetch($sql, [':user_id' => $userId]);
        return $result ? $result['balance'] : 0;
    }
    
    public function getAllUsers() {
        $sql = "SELECT id, name, username, email, role, balance, status, created_at FROM users WHERE role = 'user' ORDER BY created_at DESC";
        return $this->db->fetchAll($sql);
    }
    
    public function updateProfile($userId, $data) {
        $sql = "UPDATE users SET name = :name, username = :username, email = :email WHERE id = :user_id";
        return $this->db->execute($sql, [
            ':name' => Helpers::sanitize($data['name']),
            ':username' => Helpers::sanitize($data['username']),
            ':email' => Helpers::sanitize($data['email']),
            ':user_id' => $userId
        ]);
    }
    
    public function changePassword($userId, $newPassword) {
        $sql = "UPDATE users SET password = :password WHERE id = :user_id";
        return $this->db->execute($sql, [
            ':password' => Helpers::hashPassword($newPassword),
            ':user_id' => $userId
        ]);
    }
    
    public function emailExists($email, $excludeUserId = null) {
        $sql = "SELECT COUNT(*) as count FROM users WHERE email = :email";
        $params = [':email' => $email];
        
        if ($excludeUserId) {
            $sql .= " AND id != :exclude_id";
            $params[':exclude_id'] = $excludeUserId;
        }
        
        $result = $this->db->fetch($sql, $params);
        return $result['count'] > 0;
    }
    
    public function usernameExists($username, $excludeUserId = null) {
        $sql = "SELECT COUNT(*) as count FROM users WHERE username = :username";
        $params = [':username' => $username];
        
        if ($excludeUserId) {
            $sql .= " AND id != :exclude_id";
            $params[':exclude_id'] = $excludeUserId;
        }
        
        $result = $this->db->fetch($sql, $params);
        return $result['count'] > 0;
    }
    
    public function updateStatus($userId, $status) {
        $sql = "UPDATE users SET status = :status WHERE id = :user_id";
        return $this->db->execute($sql, [
            ':status' => $status,
            ':user_id' => $userId
        ]);
    }
    
    // Transaction methods
    public function logTransaction($userId, $type, $amount, $balanceBefore, $balanceAfter, $description = '', $referenceId = null) {
        $sql = "INSERT INTO transactions (user_id, type, amount, balance_before, balance_after, description, reference_id, created_at) 
                VALUES (:user_id, :type, :amount, :balance_before, :balance_after, :description, :reference_id, NOW())";
        
        return $this->db->execute($sql, [
            ':user_id' => $userId,
            ':type' => $type,
            ':amount' => $amount,
            ':balance_before' => $balanceBefore,
            ':balance_after' => $balanceAfter,
            ':description' => $description,
            ':reference_id' => $referenceId
        ]);
    }
    
    public function getUserTransactions($userId, $limit = 50) {
        $sql = "SELECT * FROM transactions WHERE user_id = :user_id ORDER BY created_at DESC";
        if ($limit) {
            $sql .= " LIMIT :limit";
        }
        
        $params = [':user_id' => $userId];
        if ($limit) {
            $params[':limit'] = $limit;
        }
        
        return $this->db->fetchAll($sql, $params);
    }
    
    public function getAllTransactions($limit = 100) {
        $sql = "SELECT t.*, u.name, u.username 
                FROM transactions t 
                JOIN users u ON t.user_id = u.id 
                ORDER BY t.created_at DESC";
        if ($limit) {
            $sql .= " LIMIT :limit";
        }
        
        $params = [];
        if ($limit) {
            $params[':limit'] = $limit;
        }
        
        return $this->db->fetchAll($sql, $params);
    }
    
    // Deposit methods
    public function createDepositRequest($userId, $amount, $paymentMethod, $transactionRef = '', $notes = '') {
        $sql = "INSERT INTO deposit_requests (user_id, amount, payment_method, transaction_ref, notes, created_at) 
                VALUES (:user_id, :amount, :payment_method, :transaction_ref, :notes, NOW())";
        
        return $this->db->execute($sql, [
            ':user_id' => $userId,
            ':amount' => $amount,
            ':payment_method' => $paymentMethod,
            ':transaction_ref' => $transactionRef,
            ':notes' => $notes
        ]);
    }
    
    public function getDepositRequests($status = null) {
        $sql = "SELECT dr.*, u.name, u.username, u.email 
                FROM deposit_requests dr 
                JOIN users u ON dr.user_id = u.id";
        
        if ($status) {
            $sql .= " WHERE dr.status = :status";
        }
        
        $sql .= " ORDER BY dr.created_at DESC";
        
        $params = $status ? [':status' => $status] : [];
        return $this->db->fetchAll($sql, $params);
    }
    
    public function getUserDepositRequests($userId) {
        $sql = "SELECT * FROM deposit_requests WHERE user_id = :user_id ORDER BY created_at DESC";
        return $this->db->fetchAll($sql, [':user_id' => $userId]);
    }
    
    public function processDepositRequest($requestId, $status, $adminId, $adminNotes = '') {
        $request = $this->getDepositRequestById($requestId);
        if (!$request) return false;
        
        $sql = "UPDATE deposit_requests SET status = :status, admin_notes = :admin_notes, 
                processed_by = :admin_id, processed_at = NOW() WHERE id = :request_id";
        
        $result = $this->db->execute($sql, [
            ':status' => $status,
            ':admin_notes' => $adminNotes,
            ':admin_id' => $adminId,
            ':request_id' => $requestId
        ]);
        
        if ($result && $status === 'approved') {
            // Add amount to user balance
            $this->updateBalance($request['user_id'], $request['amount'], 
                "Deposit approved - Request #$requestId", 'deposit');
        }
        
        return $result;
    }
    
    public function getDepositRequestById($requestId) {
        $sql = "SELECT dr.*, u.name, u.username, u.email 
                FROM deposit_requests dr 
                JOIN users u ON dr.user_id = u.id 
                WHERE dr.id = :request_id";
        return $this->db->fetch($sql, [':request_id' => $requestId]);
    }
    
    // Withdrawal methods
    public function createWithdrawalRequest($userId, $amount, $paymentMethod, $paymentDetails, $notes = '') {
        // Check if user has sufficient balance
        $user = $this->findById($userId);
        if (!$user || $user['balance'] < $amount) {
            return false;
        }
        
        $sql = "INSERT INTO withdrawal_requests (user_id, amount, payment_method, payment_details, notes, created_at) 
                VALUES (:user_id, :amount, :payment_method, :payment_details, :notes, NOW())";
        
        return $this->db->execute($sql, [
            ':user_id' => $userId,
            ':amount' => $amount,
            ':payment_method' => $paymentMethod,
            ':payment_details' => $paymentDetails,
            ':notes' => $notes
        ]);
    }
    
    public function getWithdrawalRequests($status = null) {
        $sql = "SELECT wr.*, u.name, u.username, u.email 
                FROM withdrawal_requests wr 
                JOIN users u ON wr.user_id = u.id";
        
        if ($status) {
            $sql .= " WHERE wr.status = :status";
        }
        
        $sql .= " ORDER BY wr.created_at DESC";
        
        $params = $status ? [':status' => $status] : [];
        return $this->db->fetchAll($sql, $params);
    }
    
    public function getUserWithdrawalRequests($userId) {
        $sql = "SELECT * FROM withdrawal_requests WHERE user_id = :user_id ORDER BY created_at DESC";
        return $this->db->fetchAll($sql, [':user_id' => $userId]);
    }
    
    public function processWithdrawalRequest($requestId, $status, $adminId, $adminNotes = '') {
        $request = $this->getWithdrawalRequestById($requestId);
        if (!$request) return false;
        
        $sql = "UPDATE withdrawal_requests SET status = :status, admin_notes = :admin_notes, 
                processed_by = :admin_id, processed_at = NOW() WHERE id = :request_id";
        
        $result = $this->db->execute($sql, [
            ':status' => $status,
            ':admin_notes' => $adminNotes,
            ':admin_id' => $adminId,
            ':request_id' => $requestId
        ]);
        
        if ($result && $status === 'approved') {
            // Deduct amount from user balance
            $this->updateBalance($request['user_id'], -$request['amount'], 
                "Withdrawal approved - Request #$requestId", 'withdrawal');
        }
        
        return $result;
    }
    
    public function getWithdrawalRequestById($requestId) {
        $sql = "SELECT wr.*, u.name, u.username, u.email 
                FROM withdrawal_requests wr 
                JOIN users u ON wr.user_id = u.id 
                WHERE wr.id = :request_id";
        return $this->db->fetch($sql, [':request_id' => $requestId]);
    }
}