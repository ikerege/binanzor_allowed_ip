<?php

require_once 'config/database.php';
require_once 'core/Helpers.php';

class Settings {
    private $db;
    private static $cache = [];
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function get($key, $default = null) {
        // Check cache first
        if (isset(self::$cache[$key])) {
            return self::$cache[$key];
        }
        
        $sql = "SELECT setting_value FROM settings WHERE setting_key = :key LIMIT 1";
        $result = $this->db->fetch($sql, [':key' => $key]);
        
        $value = $result ? $result['setting_value'] : $default;
        
        // Cache the value
        self::$cache[$key] = $value;
        
        return $value;
    }
    
    public function set($key, $value, $description = '') {
        // Check if setting exists
        $existing = $this->get($key);
        
        if ($existing !== null) {
            // Update existing setting
            $sql = "UPDATE settings SET setting_value = :value, description = :description WHERE setting_key = :key";
        } else {
            // Create new setting
            $sql = "INSERT INTO settings (setting_key, setting_value, description) VALUES (:key, :value, :description)";
        }
        
        $result = $this->db->execute($sql, [
            ':key' => $key,
            ':value' => $value,
            ':description' => $description
        ]);
        
        // Update cache
        if ($result) {
            self::$cache[$key] = $value;
        }
        
        return $result;
    }
    
    public function getAll() {
        $sql = "SELECT * FROM settings ORDER BY setting_key";
        return $this->db->fetchAll($sql);
    }
    
    public function delete($key) {
        $sql = "DELETE FROM settings WHERE setting_key = :key";
        $result = $this->db->execute($sql, [':key' => $key]);
        
        // Remove from cache
        if ($result && isset(self::$cache[$key])) {
            unset(self::$cache[$key]);
        }
        
        return $result;
    }
    
    public function exists($key) {
        $sql = "SELECT COUNT(*) as count FROM settings WHERE setting_key = :key";
        $result = $this->db->fetch($sql, [':key' => $key]);
        return $result['count'] > 0;
    }
    
    public function bulkUpdate($settings) {
        $success = true;
        
        foreach ($settings as $key => $value) {
            if (!$this->set($key, $value)) {
                $success = false;
            }
        }
        
        return $success;
    }
    
    // Specific setting getters with proper type casting
    public function getMinDeposit() {
        return (float) $this->get('min_deposit', 10.00);
    }
    
    public function getMaxDeposit() {
        return (float) $this->get('max_deposit', 10000.00);
    }
    
    public function getMinWithdrawal() {
        return (float) $this->get('min_withdrawal', 20.00);
    }
    
    public function getMaxWithdrawal() {
        return (float) $this->get('max_withdrawal', 5000.00);
    }
    
    public function getMinBet() {
        return (float) $this->get('min_bet', 1.00);
    }
    
    public function getMaxBet() {
        return (float) $this->get('max_bet', 1000.00);
    }
    
    public function getNewUserBonus() {
        return (float) $this->get('new_user_bonus', 100.00);
    }
    
    public function getSiteName() {
        return $this->get('site_name', 'BetFootball Pro');
    }
    
    public function isMaintenanceMode() {
        return (bool) $this->get('maintenance_mode', false);
    }
    
    public function enableMaintenanceMode() {
        return $this->set('maintenance_mode', '1', 'Maintenance mode status');
    }
    
    public function disableMaintenanceMode() {
        return $this->set('maintenance_mode', '0', 'Maintenance mode status');
    }
    
    // Clear cache (useful for testing or when settings are updated externally)
    public static function clearCache() {
        self::$cache = [];
    }
    
    // Get settings by pattern
    public function getByPattern($pattern) {
        $sql = "SELECT * FROM settings WHERE setting_key LIKE :pattern ORDER BY setting_key";
        return $this->db->fetchAll($sql, [':pattern' => $pattern]);
    }
    
    // Get grouped settings (useful for admin interface)
    public function getGrouped() {
        $settings = $this->getAll();
        $grouped = [];
        
        foreach ($settings as $setting) {
            $key = $setting['setting_key'];
            $parts = explode('_', $key);
            $group = $parts[0];
            
            if (!isset($grouped[$group])) {
                $grouped[$group] = [];
            }
            
            $grouped[$group][] = $setting;
        }
        
        return $grouped;
    }
}