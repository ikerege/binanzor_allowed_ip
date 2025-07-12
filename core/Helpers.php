<?php

class Helpers {
    public static function formatMoney($amount) {
        return '$' . number_format($amount, 2);
    }
    
    public static function formatDate($date) {
        return date('M d, Y', strtotime($date));
    }
    
    public static function formatDateTime($datetime) {
        return date('M d, Y g:i A', strtotime($datetime));
    }
    
    public static function sanitize($data) {
        if (is_array($data)) {
            return array_map([self::class, 'sanitize'], $data);
        }
        return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
    }
    
    public static function validateEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL);
    }
    
    public static function generateToken($length = 32) {
        return bin2hex(random_bytes($length));
    }
    
    public static function hashPassword($password) {
        return password_hash($password, PASSWORD_DEFAULT);
    }
    
    public static function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }
    
    public static function redirect($url) {
        header("Location: $url");
        exit();
    }
    
    public static function calculateOdds($homeTeam, $awayTeam) {
        // Simple odds calculation based on team names (for demo)
        $homeOdds = rand(150, 300) / 100;
        $awayOdds = rand(150, 300) / 100;
        $drawOdds = rand(280, 350) / 100;
        
        return [
            'home' => $homeOdds,
            'away' => $awayOdds,
            'draw' => $drawOdds
        ];
    }
    
    public static function isValidBetAmount($amount, $userBalance) {
        return is_numeric($amount) && $amount > 0 && $amount <= $userBalance;
    }
    
    public static function getCurrentUrl() {
        return "http" . (isset($_SERVER['HTTPS']) ? "s" : "") . "://" . 
               $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    }
    
    public static function timeAgo($datetime) {
        $time = time() - strtotime($datetime);
        
        if ($time < 60) return 'just now';
        if ($time < 3600) return floor($time/60) . ' minutes ago';
        if ($time < 86400) return floor($time/3600) . ' hours ago';
        if ($time < 2592000) return floor($time/86400) . ' days ago';
        
        return date('M d, Y', strtotime($datetime));
    }
}