<?php

require_once 'config/database.php';
require_once 'core/Helpers.php';

class User {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function create($userData) {
        $sql = "INSERT INTO users (username, email, password, role, balance, created_at) 
                VALUES (:username, :email, :password, :role, :balance, NOW())";
        
        $params = [
            ':username' => Helpers::sanitize($userData['username']),
            ':email' => Helpers::sanitize($userData['email']),
            ':password' => Helpers::hashPassword($userData['password']),
            ':role' => $userData['role'] ?? 'user',
            ':balance' => $userData['balance'] ?? 100.00
        ];
        
        return $this->db->execute($sql, $params);
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
    
    public function updateBalance($userId, $amount) {
        $sql = "UPDATE users SET balance = balance + :amount WHERE id = :user_id";
        return $this->db->execute($sql, [
            ':amount' => $amount,
            ':user_id' => $userId
        ]);
    }
    
    public function deductBalance($userId, $amount) {
        $sql = "UPDATE users SET balance = balance - :amount WHERE id = :user_id AND balance >= :amount";
        return $this->db->execute($sql, [
            ':amount' => $amount,
            ':user_id' => $userId
        ]);
    }
    
    public function getBalance($userId) {
        $sql = "SELECT balance FROM users WHERE id = :user_id";
        $result = $this->db->fetch($sql, [':user_id' => $userId]);
        return $result ? $result['balance'] : 0;
    }
    
    public function getAllUsers() {
        $sql = "SELECT id, username, email, role, balance, created_at FROM users ORDER BY created_at DESC";
        return $this->db->fetchAll($sql);
    }
    
    public function updateProfile($userId, $data) {
        $sql = "UPDATE users SET username = :username, email = :email WHERE id = :user_id";
        return $this->db->execute($sql, [
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
}