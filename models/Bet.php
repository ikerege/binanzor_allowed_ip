<?php

require_once 'config/database.php';
require_once 'core/Helpers.php';

class Bet {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function create($betData) {
        $sql = "INSERT INTO bets (user_id, match_id, bet_type, amount, odds, 
                potential_win, status, created_at) 
                VALUES (:user_id, :match_id, :bet_type, :amount, :odds, 
                :potential_win, :status, NOW())";
        
        $params = [
            ':user_id' => $betData['user_id'],
            ':match_id' => $betData['match_id'],
            ':bet_type' => $betData['bet_type'], // 'home', 'away', 'draw'
            ':amount' => $betData['amount'],
            ':odds' => $betData['odds'],
            ':potential_win' => $betData['amount'] * $betData['odds'],
            ':status' => 'pending'
        ];
        
        return $this->db->execute($sql, $params);
    }
    
    public function findById($id) {
        $sql = "SELECT b.*, m.home_team, m.away_team, m.match_date, m.league, 
                m.home_score, m.away_score, m.status as match_status, u.username
                FROM bets b
                JOIN matches m ON b.match_id = m.id
                JOIN users u ON b.user_id = u.id
                WHERE b.id = :id LIMIT 1";
        return $this->db->fetch($sql, [':id' => $id]);
    }
    
    public function getUserBets($userId, $limit = null) {
        $sql = "SELECT b.*, m.home_team, m.away_team, m.match_date, m.league, 
                m.home_score, m.away_score, m.status as match_status
                FROM bets b
                JOIN matches m ON b.match_id = m.id
                WHERE b.user_id = :user_id
                ORDER BY b.created_at DESC";
        
        if ($limit) {
            $sql .= " LIMIT :limit";
        }
        
        $params = [':user_id' => $userId];
        if ($limit) {
            $params[':limit'] = $limit;
        }
        
        return $this->db->fetchAll($sql, $params);
    }
    
    public function getMatchBets($matchId) {
        $sql = "SELECT b.*, u.username
                FROM bets b
                JOIN users u ON b.user_id = u.id
                WHERE b.match_id = :match_id
                ORDER BY b.created_at DESC";
        return $this->db->fetchAll($sql, [':match_id' => $matchId]);
    }
    
    public function getAllBets() {
        $sql = "SELECT b.*, m.home_team, m.away_team, m.match_date, 
                u.username, m.status as match_status
                FROM bets b
                JOIN matches m ON b.match_id = m.id
                JOIN users u ON b.user_id = u.id
                ORDER BY b.created_at DESC";
        return $this->db->fetchAll($sql);
    }
    
    public function getPendingBets() {
        $sql = "SELECT b.*, m.home_team, m.away_team, m.match_date, 
                u.username, m.home_score, m.away_score
                FROM bets b
                JOIN matches m ON b.match_id = m.id
                JOIN users u ON b.user_id = u.id
                WHERE b.status = 'pending' AND m.status = 'finished'
                ORDER BY b.created_at DESC";
        return $this->db->fetchAll($sql);
    }
    
    public function settleBet($betId, $status) {
        $sql = "UPDATE bets SET status = :status WHERE id = :id";
        return $this->db->execute($sql, [
            ':status' => $status, // 'won', 'lost'
            ':id' => $betId
        ]);
    }
    
    public function settleBetsForMatch($matchId) {
        // Get the match result
        $matchSql = "SELECT * FROM matches WHERE id = :match_id";
        $match = $this->db->fetch($matchSql, [':match_id' => $matchId]);
        
        if (!$match || $match['status'] !== 'finished') {
            return false;
        }
        
        // Determine match result
        $homeScore = $match['home_score'];
        $awayScore = $match['away_score'];
        
        if ($homeScore > $awayScore) {
            $result = 'home';
        } elseif ($awayScore > $homeScore) {
            $result = 'away';
        } else {
            $result = 'draw';
        }
        
        // Get all pending bets for this match
        $betsSql = "SELECT * FROM bets WHERE match_id = :match_id AND status = 'pending'";
        $bets = $this->db->fetchAll($betsSql, [':match_id' => $matchId]);
        
        foreach ($bets as $bet) {
            if ($bet['bet_type'] === $result) {
                // Winning bet
                $this->settleBet($bet['id'], 'won');
                // Add winnings to user balance
                $userModel = new User();
                $userModel->updateBalance($bet['user_id'], $bet['potential_win']);
            } else {
                // Losing bet
                $this->settleBet($bet['id'], 'lost');
            }
        }
        
        return true;
    }
    
    public function getUserStats($userId) {
        $sql = "SELECT 
                COUNT(*) as total_bets,
                SUM(amount) as total_wagered,
                SUM(CASE WHEN status = 'won' THEN potential_win ELSE 0 END) as total_won,
                SUM(CASE WHEN status = 'lost' THEN amount ELSE 0 END) as total_lost,
                COUNT(CASE WHEN status = 'won' THEN 1 END) as bets_won,
                COUNT(CASE WHEN status = 'lost' THEN 1 END) as bets_lost,
                COUNT(CASE WHEN status = 'pending' THEN 1 END) as bets_pending
                FROM bets 
                WHERE user_id = :user_id";
        
        return $this->db->fetch($sql, [':user_id' => $userId]);
    }
    
    public function getRecentWinners($limit = 10) {
        $sql = "SELECT b.amount, b.potential_win, b.bet_type, 
                m.home_team, m.away_team, u.username, b.created_at
                FROM bets b
                JOIN matches m ON b.match_id = m.id
                JOIN users u ON b.user_id = u.id
                WHERE b.status = 'won'
                ORDER BY b.created_at DESC
                LIMIT :limit";
        
        return $this->db->fetchAll($sql, [':limit' => $limit]);
    }
    
    public function getTotalStats() {
        $sql = "SELECT 
                COUNT(*) as total_bets,
                SUM(amount) as total_wagered,
                SUM(CASE WHEN status = 'won' THEN potential_win ELSE 0 END) as total_payouts,
                COUNT(DISTINCT user_id) as unique_bettors
                FROM bets";
        
        return $this->db->fetch($sql);
    }
}