<?php

require_once 'models/User.php';
require_once 'models/Match.php';
require_once 'models/Bet.php';
require_once 'core/Session.php';
require_once 'core/Helpers.php';

class MatchController {
    private $userModel;
    private $matchModel;
    private $betModel;
    
    public function __construct() {
        $this->userModel = new User();
        $this->matchModel = new Match();
        $this->betModel = new Bet();
    }
    
    public function index() {
        $upcomingMatches = $this->matchModel->getUpcomingMatches();
        $liveMatches = $this->matchModel->getLiveMatches();
        $finishedMatches = $this->matchModel->getFinishedMatches(5);
        $recentWinners = $this->betModel->getRecentWinners(5);
        
        require_once 'views/home.php';
    }
    
    public function dashboard() {
        if (!Session::isLoggedIn()) {
            Helpers::redirect('/login');
        }
        
        $userId = Session::getUserId();
        $user = $this->userModel->findById($userId);
        $userBets = $this->betModel->getUserBets($userId, 10);
        $userStats = $this->betModel->getUserStats($userId);
        $upcomingMatches = $this->matchModel->getUpcomingMatches();
        
        require_once 'views/dashboard.php';
    }
    
    public function matches() {
        $upcomingMatches = $this->matchModel->getUpcomingMatches();
        $liveMatches = $this->matchModel->getLiveMatches();
        $finishedMatches = $this->matchModel->getFinishedMatches(20);
        
        require_once 'views/matches.php';
    }
    
    public function showMatch() {
        $matchId = $_GET['id'] ?? null;
        if (!$matchId) {
            Helpers::redirect('/matches');
        }
        
        $match = $this->matchModel->findById($matchId);
        if (!$match) {
            Session::setFlash('error', 'Match not found.');
            Helpers::redirect('/matches');
        }
        
        $matchBets = $this->betModel->getMatchBets($matchId);
        $userBalance = 0;
        
        if (Session::isLoggedIn()) {
            $userBalance = $this->userModel->getBalance(Session::getUserId());
        }
        
        require_once 'views/match_detail.php';
    }
    
    public function placeBet() {
        if (!Session::isLoggedIn()) {
            Session::setFlash('error', 'Please login to place bets.');
            Helpers::redirect('/login');
        }
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Helpers::redirect('/matches');
        }
        
        $matchId = $_POST['match_id'];
        $betType = $_POST['bet_type']; // 'home', 'away', 'draw'
        $amount = floatval($_POST['amount']);
        $userId = Session::getUserId();
        
        // Validation
        $errors = [];
        
        // Check if match exists and is upcoming
        $match = $this->matchModel->findById($matchId);
        if (!$match) {
            $errors[] = 'Match not found.';
        } elseif ($match['status'] !== 'upcoming') {
            $errors[] = 'Betting is closed for this match.';
        } elseif (strtotime($match['match_date']) <= time()) {
            $errors[] = 'Betting is closed for this match.';
        }
        
        // Check bet type
        if (!in_array($betType, ['home', 'away', 'draw'])) {
            $errors[] = 'Invalid bet type.';
        }
        
        // Check amount
        if ($amount <= 0) {
            $errors[] = 'Bet amount must be greater than 0.';
        } elseif ($amount < 1) {
            $errors[] = 'Minimum bet amount is $1.';
        }
        
        // Check user balance
        $userBalance = $this->userModel->getBalance($userId);
        if ($amount > $userBalance) {
            $errors[] = 'Insufficient balance.';
        }
        
        if (!empty($errors)) {
            Session::setFlash('error', implode('<br>', $errors));
            Helpers::redirect("/match/{$matchId}");
        }
        
        // Get odds for the bet type
        $odds = 0;
        switch ($betType) {
            case 'home':
                $odds = $match['home_odds'];
                break;
            case 'away':
                $odds = $match['away_odds'];
                break;
            case 'draw':
                $odds = $match['draw_odds'];
                break;
        }
        
        // Deduct amount from user balance
        if ($this->userModel->deductBalance($userId, $amount)) {
            // Create bet
            $betData = [
                'user_id' => $userId,
                'match_id' => $matchId,
                'bet_type' => $betType,
                'amount' => $amount,
                'odds' => $odds
            ];
            
            if ($this->betModel->create($betData)) {
                $potentialWin = $amount * $odds;
                Session::setFlash('success', "Bet placed successfully! Potential win: " . Helpers::formatMoney($potentialWin));
            } else {
                // Refund the amount if bet creation failed
                $this->userModel->updateBalance($userId, $amount);
                Session::setFlash('error', 'Failed to place bet. Please try again.');
            }
        } else {
            Session::setFlash('error', 'Failed to process bet. Insufficient balance.');
        }
        
        Helpers::redirect("/match/{$matchId}");
    }
    
    public function myBets() {
        if (!Session::isLoggedIn()) {
            Helpers::redirect('/login');
        }
        
        $userId = Session::getUserId();
        $userBets = $this->betModel->getUserBets($userId);
        $userStats = $this->betModel->getUserStats($userId);
        
        require_once 'views/my_bets.php';
    }
    
    public function results() {
        $finishedMatches = $this->matchModel->getFinishedMatches(50);
        require_once 'views/results.php';
    }
    
    public function live() {
        $liveMatches = $this->matchModel->getLiveMatches();
        require_once 'views/live.php';
    }
    
    public function leagues() {
        $leagues = $this->matchModel->getLeagues();
        require_once 'views/leagues.php';
    }
    
    public function showLeague() {
        $league = $_GET['name'] ?? null;
        if (!$league) {
            Helpers::redirect('/leagues');
        }
        
        $matches = $this->matchModel->getMatchesByLeague($league);
        require_once 'views/league_matches.php';
    }
    
    public function winners() {
        $recentWinners = $this->betModel->getRecentWinners(50);
        require_once 'views/winners.php';
    }
}