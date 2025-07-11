<?php

require_once 'config/database.php';
require_once 'core/Helpers.php';

class Match {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function create($matchData) {
        $sql = "INSERT INTO matches (home_team, away_team, match_date, league, 
                home_odds, away_odds, draw_odds, status, created_at) 
                VALUES (:home_team, :away_team, :match_date, :league, 
                :home_odds, :away_odds, :draw_odds, :status, NOW())";
        
        $params = [
            ':home_team' => Helpers::sanitize($matchData['home_team']),
            ':away_team' => Helpers::sanitize($matchData['away_team']),
            ':match_date' => $matchData['match_date'],
            ':league' => Helpers::sanitize($matchData['league']),
            ':home_odds' => $matchData['home_odds'],
            ':away_odds' => $matchData['away_odds'],
            ':draw_odds' => $matchData['draw_odds'],
            ':status' => $matchData['status'] ?? 'upcoming'
        ];
        
        return $this->db->execute($sql, $params);
    }
    
    public function findById($id) {
        $sql = "SELECT * FROM matches WHERE id = :id LIMIT 1";
        return $this->db->fetch($sql, [':id' => $id]);
    }
    
    public function getUpcomingMatches() {
        $sql = "SELECT * FROM matches WHERE status = 'upcoming' AND match_date > NOW() 
                ORDER BY match_date ASC";
        return $this->db->fetchAll($sql);
    }
    
    public function getLiveMatches() {
        $sql = "SELECT * FROM matches WHERE status = 'live' ORDER BY match_date ASC";
        return $this->db->fetchAll($sql);
    }
    
    public function getFinishedMatches($limit = 20) {
        $sql = "SELECT * FROM matches WHERE status = 'finished' 
                ORDER BY match_date DESC LIMIT :limit";
        return $this->db->fetchAll($sql, [':limit' => $limit]);
    }
    
    public function getAllMatches() {
        $sql = "SELECT * FROM matches ORDER BY match_date DESC";
        return $this->db->fetchAll($sql);
    }
    
    public function getMatchesByStatus($status) {
        $sql = "SELECT * FROM matches WHERE status = :status ORDER BY match_date ASC";
        return $this->db->fetchAll($sql, [':status' => $status]);
    }
    
    public function updateMatch($id, $data) {
        $sql = "UPDATE matches SET 
                home_team = :home_team,
                away_team = :away_team,
                match_date = :match_date,
                league = :league,
                home_odds = :home_odds,
                away_odds = :away_odds,
                draw_odds = :draw_odds,
                status = :status
                WHERE id = :id";
        
        $params = [
            ':home_team' => Helpers::sanitize($data['home_team']),
            ':away_team' => Helpers::sanitize($data['away_team']),
            ':match_date' => $data['match_date'],
            ':league' => Helpers::sanitize($data['league']),
            ':home_odds' => $data['home_odds'],
            ':away_odds' => $data['away_odds'],
            ':draw_odds' => $data['draw_odds'],
            ':status' => $data['status'],
            ':id' => $id
        ];
        
        return $this->db->execute($sql, $params);
    }
    
    public function updateResult($id, $homeScore, $awayScore) {
        $sql = "UPDATE matches SET 
                home_score = :home_score,
                away_score = :away_score,
                status = 'finished'
                WHERE id = :id";
        
        return $this->db->execute($sql, [
            ':home_score' => $homeScore,
            ':away_score' => $awayScore,
            ':id' => $id
        ]);
    }
    
    public function updateStatus($id, $status) {
        $sql = "UPDATE matches SET status = :status WHERE id = :id";
        return $this->db->execute($sql, [
            ':status' => $status,
            ':id' => $id
        ]);
    }
    
    public function deleteMatch($id) {
        // First check if there are any bets on this match
        $betsSql = "SELECT COUNT(*) as count FROM bets WHERE match_id = :match_id";
        $betsResult = $this->db->fetch($betsSql, [':match_id' => $id]);
        
        if ($betsResult['count'] > 0) {
            return false; // Cannot delete match with existing bets
        }
        
        $sql = "DELETE FROM matches WHERE id = :id";
        return $this->db->execute($sql, [':id' => $id]);
    }
    
    public function getMatchesWithBets() {
        $sql = "SELECT m.*, COUNT(b.id) as bet_count, SUM(b.amount) as total_bet_amount
                FROM matches m 
                LEFT JOIN bets b ON m.id = b.match_id 
                GROUP BY m.id 
                ORDER BY m.match_date DESC";
        return $this->db->fetchAll($sql);
    }
    
    public function getLeagues() {
        $sql = "SELECT DISTINCT league FROM matches ORDER BY league";
        return $this->db->fetchAll($sql);
    }
    
    public function getMatchesByLeague($league) {
        $sql = "SELECT * FROM matches WHERE league = :league ORDER BY match_date ASC";
        return $this->db->fetchAll($sql, [':league' => $league]);
    }
}