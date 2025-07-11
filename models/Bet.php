<?php

require_once 'config/database.php';
require_once 'core/Helpers.php';

class Bet {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function create($betData) {
        // Validate bet data
        if (!$this->validateBetData($betData)) {
            return false;
        }
        
        $sql = "INSERT INTO bets (user_id, match_id, bet_type, selected_option, stake, odds, potential_payout, created_at) 
                VALUES (:user_id, :match_id, :bet_type, :selected_option, :stake, :odds, :potential_payout, NOW())";
        
        $params = [
            ':user_id' => $betData['user_id'],
            ':match_id' => $betData['match_id'],
            ':bet_type' => $betData['bet_type'], // '1x2', 'over_under', 'btts'
            ':selected_option' => $betData['selected_option'], // 'home', 'away', 'draw', 'over', 'under', 'yes', 'no'
            ':stake' => $betData['stake'],
            ':odds' => $betData['odds'],
            ':potential_payout' => $betData['stake'] * $betData['odds']
        ];
        
        return $this->db->execute($sql, $params);
    }
    
    private function validateBetData($betData) {
        // Check required fields
        $required = ['user_id', 'match_id', 'bet_type', 'selected_option', 'stake', 'odds'];
        foreach ($required as $field) {
            if (!isset($betData[$field]) || empty($betData[$field])) {
                return false;
            }
        }
        
        // Validate bet type and selected option combinations
        $validCombinations = [
            '1x2' => ['home', 'away', 'draw'],
            'over_under' => ['over', 'under'],
            'btts' => ['yes', 'no']
        ];
        
        if (!isset($validCombinations[$betData['bet_type']]) || 
            !in_array($betData['selected_option'], $validCombinations[$betData['bet_type']])) {
            return false;
        }
        
        // Validate stake and odds
        if ($betData['stake'] <= 0 || $betData['odds'] <= 1) {
            return false;
        }
        
        return true;
    }
    
    public function findById($id) {
        $sql = "SELECT b.*, m.home_team, m.away_team, m.match_date, m.league, 
                m.home_score, m.away_score, m.total_goals, m.both_teams_scored, m.status as match_status, u.name, u.username
                FROM bets b
                JOIN matches m ON b.match_id = m.id
                JOIN users u ON b.user_id = u.id
                WHERE b.id = :id LIMIT 1";
        return $this->db->fetch($sql, [':id' => $id]);
    }
    
    public function getUserBets($userId, $limit = null) {
        $sql = "SELECT b.*, m.home_team, m.away_team, m.match_date, m.league, 
                m.home_score, m.away_score, m.total_goals, m.both_teams_scored, m.status as match_status
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
        $sql = "SELECT b.*, u.name, u.username
                FROM bets b
                JOIN users u ON b.user_id = u.id
                WHERE b.match_id = :match_id
                ORDER BY b.created_at DESC";
        return $this->db->fetchAll($sql, [':match_id' => $matchId]);
    }
    
    public function getAllBets($limit = null) {
        $sql = "SELECT b.*, m.home_team, m.away_team, m.match_date, m.league,
                u.name, u.username, m.status as match_status
                FROM bets b
                JOIN matches m ON b.match_id = m.id
                JOIN users u ON b.user_id = u.id
                ORDER BY b.created_at DESC";
        
        if ($limit) {
            $sql .= " LIMIT :limit";
        }
        
        $params = [];
        if ($limit) {
            $params[':limit'] = $limit;
        }
        
        return $this->db->fetchAll($sql, $params);
    }
    
    public function getPendingBets() {
        $sql = "SELECT b.*, m.home_team, m.away_team, m.match_date, m.league,
                u.name, u.username, m.home_score, m.away_score, m.total_goals, m.both_teams_scored
                FROM bets b
                JOIN matches m ON b.match_id = m.id
                JOIN users u ON b.user_id = u.id
                WHERE b.status = 'pending' AND m.status = 'finished'
                ORDER BY b.created_at DESC";
        return $this->db->fetchAll($sql);
    }
    
    public function settleBet($betId, $status, $payout = 0) {
        $sql = "UPDATE bets SET status = :status, actual_payout = :payout WHERE id = :id";
        return $this->db->execute($sql, [
            ':status' => $status, // 'won', 'lost', 'cancelled'
            ':payout' => $payout,
            ':id' => $betId
        ]);
    }
    
    public function settleBetsForMatch($matchId) {
        // Get the match details
        require_once 'models/Match.php';
        $matchModel = new Match();
        $match = $matchModel->findById($matchId);
        
        if (!$match || $match['status'] !== 'finished') {
            return false;
        }
        
        // Get all pending bets for this match
        $sql = "SELECT * FROM bets WHERE match_id = :match_id AND status = 'pending'";
        $bets = $this->db->fetchAll($sql, [':match_id' => $matchId]);
        
        require_once 'models/User.php';
        $userModel = new User();
        
        foreach ($bets as $bet) {
            $isWinning = $this->isBetWinning($bet, $match);
            
            if ($isWinning) {
                // Winning bet
                $this->settleBet($bet['id'], 'won', $bet['potential_payout']);
                // Add winnings to user balance
                $userModel->updateBalance($bet['user_id'], $bet['potential_payout'], 
                    "Bet won: {$bet['bet_type']} - {$bet['selected_option']}", 'bet_won');
            } else {
                // Losing bet
                $this->settleBet($bet['id'], 'lost', 0);
            }
        }
        
        return true;
    }
    
    private function isBetWinning($bet, $match) {
        switch ($bet['bet_type']) {
            case '1x2':
                return $this->check1x2Bet($bet['selected_option'], $match);
            case 'over_under':
                return $this->checkOverUnderBet($bet['selected_option'], $match);
            case 'btts':
                return $this->checkBttsBet($bet['selected_option'], $match);
            default:
                return false;
        }
    }
    
    private function check1x2Bet($selectedOption, $match) {
        $homeScore = $match['home_score'];
        $awayScore = $match['away_score'];
        
        switch ($selectedOption) {
            case 'home':
                return $homeScore > $awayScore;
            case 'away':
                return $awayScore > $homeScore;
            case 'draw':
                return $homeScore == $awayScore;
            default:
                return false;
        }
    }
    
    private function checkOverUnderBet($selectedOption, $match) {
        $totalGoals = $match['total_goals'];
        
        switch ($selectedOption) {
            case 'over':
                return $totalGoals > 2.5; // Over 2.5 goals
            case 'under':
                return $totalGoals < 2.5; // Under 2.5 goals
            default:
                return false;
        }
    }
    
    private function checkBttsBet($selectedOption, $match) {
        $bothTeamsScored = $match['both_teams_scored'];
        
        switch ($selectedOption) {
            case 'yes':
                return $bothTeamsScored == 1;
            case 'no':
                return $bothTeamsScored == 0;
            default:
                return false;
        }
    }
    
    public function getUserStats($userId) {
        $sql = "SELECT 
                COUNT(*) as total_bets,
                SUM(stake) as total_wagered,
                SUM(CASE WHEN status = 'won' THEN actual_payout ELSE 0 END) as total_won,
                SUM(CASE WHEN status = 'lost' THEN stake ELSE 0 END) as total_lost,
                COUNT(CASE WHEN status = 'won' THEN 1 END) as bets_won,
                COUNT(CASE WHEN status = 'lost' THEN 1 END) as bets_lost,
                COUNT(CASE WHEN status = 'pending' THEN 1 END) as bets_pending,
                ROUND(AVG(odds), 2) as avg_odds,
                ROUND(AVG(stake), 2) as avg_stake
                FROM bets 
                WHERE user_id = :user_id";
        
        return $this->db->fetch($sql, [':user_id' => $userId]);
    }
    
    public function getRecentWinners($limit = 10) {
        $sql = "SELECT b.stake, b.actual_payout, b.bet_type, b.selected_option,
                m.home_team, m.away_team, u.name, u.username, b.created_at
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
                SUM(stake) as total_wagered,
                SUM(CASE WHEN status = 'won' THEN actual_payout ELSE 0 END) as total_payouts,
                COUNT(DISTINCT user_id) as unique_bettors,
                AVG(odds) as avg_odds,
                AVG(stake) as avg_stake
                FROM bets";
        
        return $this->db->fetch($sql);
    }
    
    public function getBetsByType($betType = null) {
        $sql = "SELECT b.bet_type, b.selected_option, COUNT(*) as count, 
                SUM(b.stake) as total_stake, 
                AVG(b.odds) as avg_odds
                FROM bets b";
        
        if ($betType) {
            $sql .= " WHERE b.bet_type = :bet_type";
        }
        
        $sql .= " GROUP BY b.bet_type, b.selected_option ORDER BY count DESC";
        
        $params = $betType ? [':bet_type' => $betType] : [];
        return $this->db->fetchAll($sql, $params);
    }
    
    public function getUserBetHistory($userId, $status = null, $limit = 50) {
        $sql = "SELECT b.*, m.home_team, m.away_team, m.match_date, m.league,
                m.home_score, m.away_score, m.total_goals, m.both_teams_scored, m.status as match_status
                FROM bets b
                JOIN matches m ON b.match_id = m.id
                WHERE b.user_id = :user_id";
        
        if ($status) {
            $sql .= " AND b.status = :status";
        }
        
        $sql .= " ORDER BY b.created_at DESC LIMIT :limit";
        
        $params = [':user_id' => $userId, ':limit' => $limit];
        if ($status) {
            $params[':status'] = $status;
        }
        
        return $this->db->fetchAll($sql, $params);
    }
    
    public function cancelBet($betId, $refund = true) {
        $bet = $this->findById($betId);
        if (!$bet || $bet['status'] !== 'pending') {
            return false;
        }
        
        // Update bet status
        $this->settleBet($betId, 'cancelled', 0);
        
        if ($refund) {
            // Refund the stake to user
            require_once 'models/User.php';
            $userModel = new User();
            $userModel->updateBalance($bet['user_id'], $bet['stake'], 
                "Bet cancelled - refund for bet #{$betId}", 'bet_refund');
        }
        
        return true;
    }
    
    public function getMatchBetsSummary($matchId) {
        $sql = "SELECT 
                bet_type, 
                selected_option, 
                COUNT(*) as bet_count,
                SUM(stake) as total_stake,
                AVG(odds) as avg_odds
                FROM bets 
                WHERE match_id = :match_id 
                GROUP BY bet_type, selected_option
                ORDER BY bet_type, selected_option";
        
        return $this->db->fetchAll($sql, [':match_id' => $matchId]);
    }
}