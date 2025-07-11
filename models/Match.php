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
                home_odds, away_odds, draw_odds, over_25_odds, under_25_odds, 
                btts_yes_odds, btts_no_odds, status, created_at) 
                VALUES (:home_team, :away_team, :match_date, :league, 
                :home_odds, :away_odds, :draw_odds, :over_25_odds, :under_25_odds,
                :btts_yes_odds, :btts_no_odds, :status, NOW())";
        
        $params = [
            ':home_team' => Helpers::sanitize($matchData['home_team']),
            ':away_team' => Helpers::sanitize($matchData['away_team']),
            ':match_date' => $matchData['match_date'],
            ':league' => Helpers::sanitize($matchData['league']),
            ':home_odds' => $matchData['home_odds'],
            ':away_odds' => $matchData['away_odds'],
            ':draw_odds' => $matchData['draw_odds'],
            ':over_25_odds' => $matchData['over_25_odds'] ?? 1.90,
            ':under_25_odds' => $matchData['under_25_odds'] ?? 1.90,
            ':btts_yes_odds' => $matchData['btts_yes_odds'] ?? 1.80,
            ':btts_no_odds' => $matchData['btts_no_odds'] ?? 2.00,
            ':status' => $matchData['status'] ?? 'upcoming'
        ];
        
        return $this->db->execute($sql, $params);
    }
    
    public function findById($id) {
        $sql = "SELECT * FROM matches WHERE id = :id LIMIT 1";
        return $this->db->fetch($sql, [':id' => $id]);
    }
    
    public function getUpcomingMatches() {
        $sql = "SELECT * FROM matches WHERE status = 'upcoming' AND match_date > NOW() AND betting_locked = FALSE
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
                over_25_odds = :over_25_odds,
                under_25_odds = :under_25_odds,
                btts_yes_odds = :btts_yes_odds,
                btts_no_odds = :btts_no_odds,
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
            ':over_25_odds' => $data['over_25_odds'],
            ':under_25_odds' => $data['under_25_odds'],
            ':btts_yes_odds' => $data['btts_yes_odds'],
            ':btts_no_odds' => $data['btts_no_odds'],
            ':status' => $data['status'],
            ':id' => $id
        ];
        
        return $this->db->execute($sql, $params);
    }
    
    public function updateResult($id, $homeScore, $awayScore) {
        // Calculate additional fields
        $totalGoals = $homeScore + $awayScore;
        $bothTeamsScored = ($homeScore > 0 && $awayScore > 0) ? 1 : 0;
        
        $sql = "UPDATE matches SET 
                home_score = :home_score,
                away_score = :away_score,
                total_goals = :total_goals,
                both_teams_scored = :both_teams_scored,
                status = 'finished'
                WHERE id = :id";
        
        return $this->db->execute($sql, [
            ':home_score' => $homeScore,
            ':away_score' => $awayScore,
            ':total_goals' => $totalGoals,
            ':both_teams_scored' => $bothTeamsScored,
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
    
    public function lockBetting($id) {
        $sql = "UPDATE matches SET betting_locked = TRUE WHERE id = :id";
        return $this->db->execute($sql, [':id' => $id]);
    }
    
    public function unlockBetting($id) {
        $sql = "UPDATE matches SET betting_locked = FALSE WHERE id = :id";
        return $this->db->execute($sql, [':id' => $id]);
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
        $sql = "SELECT m.*, COUNT(b.id) as bet_count, SUM(b.stake) as total_bet_amount
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
    
    public function getMatchesStartingSoon($hours = 2) {
        $sql = "SELECT * FROM matches 
                WHERE status = 'upcoming' 
                AND match_date BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL :hours HOUR)
                AND betting_locked = FALSE
                ORDER BY match_date ASC";
        return $this->db->fetchAll($sql, [':hours' => $hours]);
    }
    
    public function lockBettingForStartedMatches() {
        $sql = "UPDATE matches 
                SET betting_locked = TRUE 
                WHERE status = 'upcoming' 
                AND match_date <= NOW() 
                AND betting_locked = FALSE";
        return $this->db->execute($sql);
    }
    
    public function getMatchOdds($matchId) {
        $sql = "SELECT home_odds, away_odds, draw_odds, over_25_odds, under_25_odds, 
                btts_yes_odds, btts_no_odds 
                FROM matches WHERE id = :match_id";
        return $this->db->fetch($sql, [':match_id' => $matchId]);
    }
    
    public function updateOdds($matchId, $odds) {
        $sql = "UPDATE matches SET 
                home_odds = :home_odds,
                away_odds = :away_odds,
                draw_odds = :draw_odds,
                over_25_odds = :over_25_odds,
                under_25_odds = :under_25_odds,
                btts_yes_odds = :btts_yes_odds,
                btts_no_odds = :btts_no_odds
                WHERE id = :match_id";
        
        $params = [
            ':home_odds' => $odds['home_odds'],
            ':away_odds' => $odds['away_odds'],
            ':draw_odds' => $odds['draw_odds'],
            ':over_25_odds' => $odds['over_25_odds'],
            ':under_25_odds' => $odds['under_25_odds'],
            ':btts_yes_odds' => $odds['btts_yes_odds'],
            ':btts_no_odds' => $odds['btts_no_odds'],
            ':match_id' => $matchId
        ];
        
        return $this->db->execute($sql, $params);
    }
    
    public function getMatchStats($matchId) {
        $sql = "SELECT 
                m.*,
                COUNT(b.id) as total_bets,
                SUM(b.stake) as total_stake,
                COUNT(CASE WHEN b.bet_type = '1x2' AND b.selected_option = 'home' THEN 1 END) as home_bets,
                COUNT(CASE WHEN b.bet_type = '1x2' AND b.selected_option = 'away' THEN 1 END) as away_bets,
                COUNT(CASE WHEN b.bet_type = '1x2' AND b.selected_option = 'draw' THEN 1 END) as draw_bets,
                COUNT(CASE WHEN b.bet_type = 'over_under' AND b.selected_option = 'over' THEN 1 END) as over_bets,
                COUNT(CASE WHEN b.bet_type = 'over_under' AND b.selected_option = 'under' THEN 1 END) as under_bets,
                COUNT(CASE WHEN b.bet_type = 'btts' AND b.selected_option = 'yes' THEN 1 END) as btts_yes_bets,
                COUNT(CASE WHEN b.bet_type = 'btts' AND b.selected_option = 'no' THEN 1 END) as btts_no_bets
                FROM matches m
                LEFT JOIN bets b ON m.id = b.match_id
                WHERE m.id = :match_id
                GROUP BY m.id";
        
        return $this->db->fetch($sql, [':match_id' => $matchId]);
    }
    
    public function searchMatches($searchTerm) {
        $searchTerm = '%' . $searchTerm . '%';
        $sql = "SELECT * FROM matches 
                WHERE home_team LIKE :search 
                OR away_team LIKE :search 
                OR league LIKE :search
                ORDER BY match_date DESC";
        return $this->db->fetchAll($sql, [':search' => $searchTerm]);
    }
    
    public function getMatchesByDateRange($startDate, $endDate) {
        $sql = "SELECT * FROM matches 
                WHERE match_date BETWEEN :start_date AND :end_date
                ORDER BY match_date ASC";
        return $this->db->fetchAll($sql, [
            ':start_date' => $startDate,
            ':end_date' => $endDate
        ]);
    }
    
    public function getTodaysMatches() {
        $sql = "SELECT * FROM matches 
                WHERE DATE(match_date) = CURDATE()
                ORDER BY match_date ASC";
        return $this->db->fetchAll($sql);
    }
    
    public function getUpcomingMatchesCount() {
        $sql = "SELECT COUNT(*) as count FROM matches 
                WHERE status = 'upcoming' AND match_date > NOW()";
        $result = $this->db->fetch($sql);
        return $result['count'];
    }
    
    public function getPopularMatches($limit = 10) {
        $sql = "SELECT m.*, COUNT(b.id) as bet_count, SUM(b.stake) as total_stake
                FROM matches m
                LEFT JOIN bets b ON m.id = b.match_id
                WHERE m.status IN ('upcoming', 'live')
                GROUP BY m.id
                ORDER BY bet_count DESC, total_stake DESC
                LIMIT :limit";
        return $this->db->fetchAll($sql, [':limit' => $limit]);
    }
}