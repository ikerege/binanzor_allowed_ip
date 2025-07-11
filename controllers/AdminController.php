<?php

require_once 'models/User.php';
require_once 'models/Match.php';
require_once 'models/Bet.php';
require_once 'core/Session.php';
require_once 'core/Helpers.php';

class AdminController {
    private $userModel;
    private $matchModel;
    private $betModel;
    
    public function __construct() {
        $this->userModel = new User();
        $this->matchModel = new Match();
        $this->betModel = new Bet();
        
        // Check admin access
        if (!Session::isLoggedIn() || !Session::isAdmin()) {
            Helpers::redirect('/login');
        }
    }
    
    public function dashboard() {
        $stats = $this->getDashboardStats();
        require_once 'views/admin/dashboard.php';
    }
    
    private function getDashboardStats() {
        $totalUsers = count($this->userModel->getAllUsers());
        $totalMatches = count($this->matchModel->getAllMatches());
        $betStats = $this->betModel->getTotalStats();
        $recentBets = $this->betModel->getAllBets();
        $recentBets = array_slice($recentBets, 0, 10);
        
        return [
            'total_users' => $totalUsers,
            'total_matches' => $totalMatches,
            'total_bets' => $betStats['total_bets'] ?? 0,
            'total_wagered' => $betStats['total_wagered'] ?? 0,
            'total_payouts' => $betStats['total_payouts'] ?? 0,
            'recent_bets' => $recentBets
        ];
    }
    
    public function users() {
        $users = $this->userModel->getAllUsers();
        require_once 'views/admin/users.php';
    }
    
    public function matches() {
        $matches = $this->matchModel->getMatchesWithBets();
        require_once 'views/admin/matches.php';
    }
    
    public function showAddMatch() {
        require_once 'views/admin/add_match.php';
    }
    
    public function addMatch() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Helpers::redirect('/admin/matches');
        }
        
        $homeTeam = Helpers::sanitize($_POST['home_team']);
        $awayTeam = Helpers::sanitize($_POST['away_team']);
        $matchDate = $_POST['match_date'];
        $league = Helpers::sanitize($_POST['league']);
        $homeOdds = floatval($_POST['home_odds']);
        $awayOdds = floatval($_POST['away_odds']);
        $drawOdds = floatval($_POST['draw_odds']);
        
        // Validation
        $errors = [];
        
        if (empty($homeTeam)) {
            $errors[] = 'Home team is required';
        }
        
        if (empty($awayTeam)) {
            $errors[] = 'Away team is required';
        }
        
        if (empty($matchDate)) {
            $errors[] = 'Match date is required';
        } elseif (strtotime($matchDate) < time()) {
            $errors[] = 'Match date must be in the future';
        }
        
        if (empty($league)) {
            $errors[] = 'League is required';
        }
        
        if ($homeOdds <= 1) {
            $errors[] = 'Home odds must be greater than 1';
        }
        
        if ($awayOdds <= 1) {
            $errors[] = 'Away odds must be greater than 1';
        }
        
        if ($drawOdds <= 1) {
            $errors[] = 'Draw odds must be greater than 1';
        }
        
        if (!empty($errors)) {
            Session::setFlash('error', implode('<br>', $errors));
            Helpers::redirect('/admin/add-match');
        }
        
        // Create match
        $matchData = [
            'home_team' => $homeTeam,
            'away_team' => $awayTeam,
            'match_date' => $matchDate,
            'league' => $league,
            'home_odds' => $homeOdds,
            'away_odds' => $awayOdds,
            'draw_odds' => $drawOdds,
            'status' => 'upcoming'
        ];
        
        if ($this->matchModel->create($matchData)) {
            Session::setFlash('success', 'Match added successfully.');
        } else {
            Session::setFlash('error', 'Failed to add match.');
        }
        
        Helpers::redirect('/admin/matches');
    }
    
    public function showEditMatch() {
        $matchId = $_GET['id'] ?? null;
        if (!$matchId) {
            Helpers::redirect('/admin/matches');
        }
        
        $match = $this->matchModel->findById($matchId);
        if (!$match) {
            Session::setFlash('error', 'Match not found.');
            Helpers::redirect('/admin/matches');
        }
        
        require_once 'views/admin/edit_match.php';
    }
    
    public function editMatch() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Helpers::redirect('/admin/matches');
        }
        
        $matchId = $_POST['match_id'];
        $homeTeam = Helpers::sanitize($_POST['home_team']);
        $awayTeam = Helpers::sanitize($_POST['away_team']);
        $matchDate = $_POST['match_date'];
        $league = Helpers::sanitize($_POST['league']);
        $homeOdds = floatval($_POST['home_odds']);
        $awayOdds = floatval($_POST['away_odds']);
        $drawOdds = floatval($_POST['draw_odds']);
        $status = $_POST['status'];
        
        $matchData = [
            'home_team' => $homeTeam,
            'away_team' => $awayTeam,
            'match_date' => $matchDate,
            'league' => $league,
            'home_odds' => $homeOdds,
            'away_odds' => $awayOdds,
            'draw_odds' => $drawOdds,
            'status' => $status
        ];
        
        if ($this->matchModel->updateMatch($matchId, $matchData)) {
            Session::setFlash('success', 'Match updated successfully.');
        } else {
            Session::setFlash('error', 'Failed to update match.');
        }
        
        Helpers::redirect('/admin/matches');
    }
    
    public function updateMatchResult() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Helpers::redirect('/admin/matches');
        }
        
        $matchId = $_POST['match_id'];
        $homeScore = intval($_POST['home_score']);
        $awayScore = intval($_POST['away_score']);
        
        if ($this->matchModel->updateResult($matchId, $homeScore, $awayScore)) {
            // Settle bets for this match
            $this->betModel->settleBetsForMatch($matchId);
            Session::setFlash('success', 'Match result updated and bets settled.');
        } else {
            Session::setFlash('error', 'Failed to update match result.');
        }
        
        Helpers::redirect('/admin/matches');
    }
    
    public function bets() {
        $bets = $this->betModel->getAllBets();
        require_once 'views/admin/bets.php';
    }
    
    public function settlePendingBets() {
        $pendingBets = $this->betModel->getPendingBets();
        $settledCount = 0;
        
        foreach ($pendingBets as $bet) {
            if ($this->betModel->settleBetsForMatch($bet['match_id'])) {
                $settledCount++;
            }
        }
        
        Session::setFlash('success', "Settled {$settledCount} pending bets.");
        Helpers::redirect('/admin/bets');
    }
    
    public function deleteMatch() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Helpers::redirect('/admin/matches');
        }
        
        $matchId = $_POST['match_id'];
        
        if ($this->matchModel->deleteMatch($matchId)) {
            Session::setFlash('success', 'Match deleted successfully.');
        } else {
            Session::setFlash('error', 'Cannot delete match with existing bets.');
        }
        
        Helpers::redirect('/admin/matches');
    }
    
    public function adjustUserBalance() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Helpers::redirect('/admin/users');
        }
        
        $userId = $_POST['user_id'];
        $amount = floatval($_POST['amount']);
        
        if ($this->userModel->updateBalance($userId, $amount)) {
            Session::setFlash('success', 'User balance adjusted successfully.');
        } else {
            Session::setFlash('error', 'Failed to adjust user balance.');
        }
        
        Helpers::redirect('/admin/users');
    }
}