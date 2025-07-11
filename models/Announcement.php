<?php

require_once 'config/database.php';
require_once 'core/Helpers.php';

class Announcement {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function create($data) {
        $sql = "INSERT INTO announcements (title, content, type, is_active, created_by, created_at) 
                VALUES (:title, :content, :type, :is_active, :created_by, NOW())";
        
        return $this->db->execute($sql, [
            ':title' => Helpers::sanitize($data['title']),
            ':content' => Helpers::sanitize($data['content']),
            ':type' => $data['type'] ?? 'info',
            ':is_active' => $data['is_active'] ?? true,
            ':created_by' => $data['created_by']
        ]);
    }
    
    public function findById($id) {
        $sql = "SELECT a.*, u.name as creator_name 
                FROM announcements a
                JOIN users u ON a.created_by = u.id
                WHERE a.id = :id LIMIT 1";
        return $this->db->fetch($sql, [':id' => $id]);
    }
    
    public function getAll() {
        $sql = "SELECT a.*, u.name as creator_name 
                FROM announcements a
                JOIN users u ON a.created_by = u.id
                ORDER BY a.created_at DESC";
        return $this->db->fetchAll($sql);
    }
    
    public function getActive() {
        $sql = "SELECT a.*, u.name as creator_name 
                FROM announcements a
                JOIN users u ON a.created_by = u.id
                WHERE a.is_active = TRUE
                ORDER BY a.created_at DESC";
        return $this->db->fetchAll($sql);
    }
    
    public function update($id, $data) {
        $sql = "UPDATE announcements SET 
                title = :title,
                content = :content,
                type = :type,
                is_active = :is_active
                WHERE id = :id";
        
        return $this->db->execute($sql, [
            ':title' => Helpers::sanitize($data['title']),
            ':content' => Helpers::sanitize($data['content']),
            ':type' => $data['type'],
            ':is_active' => $data['is_active'],
            ':id' => $id
        ]);
    }
    
    public function delete($id) {
        $sql = "DELETE FROM announcements WHERE id = :id";
        return $this->db->execute($sql, [':id' => $id]);
    }
    
    public function toggleStatus($id) {
        $sql = "UPDATE announcements SET is_active = NOT is_active WHERE id = :id";
        return $this->db->execute($sql, [':id' => $id]);
    }
    
    public function getByType($type) {
        $sql = "SELECT a.*, u.name as creator_name 
                FROM announcements a
                JOIN users u ON a.created_by = u.id
                WHERE a.type = :type AND a.is_active = TRUE
                ORDER BY a.created_at DESC";
        return $this->db->fetchAll($sql, [':type' => $type]);
    }
    
    public function getRecent($limit = 5) {
        $sql = "SELECT a.*, u.name as creator_name 
                FROM announcements a
                JOIN users u ON a.created_by = u.id
                WHERE a.is_active = TRUE
                ORDER BY a.created_at DESC
                LIMIT :limit";
        return $this->db->fetchAll($sql, [':limit' => $limit]);
    }
}