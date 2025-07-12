<?php

require_once 'models/Match.php';
require_once 'models/Bet.php';
require_once 'models/User.php';
require_once 'models/Settings.php';
require_once 'core/Session.php';
require_once 'core/Helpers.php';

class MatchController {
    private $matchModel;
    private $betModel;
    private $userModel;
    private $settings;
    
    public function __construct() {
        $this->matchModel = new Match();
        $this->betModel = new Bet();
        $this->userModel = new User();
        $this->settings = new Settings();
    }
    
    public function index() {
        $upcomingMatches = $this->matchModel->getUpcomingMatches();
        $liveMatches = $this->matchModel->getLiveMatches();
        $finishedMatches = $this->matchModel->getFinishedMatches(10);
        $recentWinners = $this->betModel->getRecentWinners(5);
        
        require_once 'views/home.php';
    }
    
    public function matches() {
        $filter = $_GET['filter'] ?? 'all';
        $league = $_GET['league'] ?? '';
        $date = $_GET['date'] ?? '';
        
        switch ($filter) {
            case 'upcoming':
                $matches = $this->matchModel->getUpcomingMatches();
                break;
            case 'live':
                $matches = $this->matchModel->getLiveMatches();
                break;
            case 'finished':
                $matches = $this->matchModel->getFinishedMatches(50);
                break;
            case 'today':
                $matches = $this->matchModel->getTodaysMatches();
                break;
            default:
                $matches = $this->matchModel->getAllMatches();
        }
        
        // Filter by league if specified
        if ($league) {
            $matches = array_filter($matches, function($match) use ($league) {
                return $match['league'] === $league;
            });
        }
        
        // Filter by date if specified
        if ($date) {
            $matches = array_filter($matches, function($match) use ($date) {
                return date('Y-m-d', strtotime($match['match_date'])) === $date;
            });
        }
        
        $leagues = $this->matchModel->getLeagues();
        
        require_once 'views/matches.php';
    }
    
    public function match() {
        $matchId = (int) ($_GET['id'] ?? 0);
        $match = $this->matchModel->findById($matchId);
        
        if (!$match) {
            Session::setFlash('error', 'Match not found.');
            header('Location: /matches');
            return;
        }
        
        // Get match statistics
        $matchStats = $this->matchModel->getMatchStats($matchId);
        $betsSummary = $this->betModel->getMatchBetsSummary($matchId);
        
        // Get user's previous bets on this match (if logged in)
        $userBets = [];
        if (Session::isLoggedIn()) {
            $userId = Session::get('user_id');
            $allUserBets = $this->betModel->getUserBets($userId);
            $userBets = array_filter($allUserBets, function($bet) use ($matchId) {
                return $bet['match_id'] == $matchId;
            });
        }
        
        require_once 'views/match_detail.php';
    }
    
    public function placeBet() {
        if (!Session::isLoggedIn()) {
            header('Location: /login');
            return;
        }
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /matches');
            return;
        }
        
        $matchId = (int) ($_POST['match_id'] ?? 0);
        $betType = $_POST['bet_type'] ?? ''; // '1x2', 'over_under', 'btts'
        $selectedOption = $_POST['selected_option'] ?? ''; // 'home', 'away', 'draw', 'over', 'under', 'yes', 'no'
        $stake = (float) ($_POST['stake'] ?? 0);
        
        $userId = Session::get('user_id');
        $user = $this->userModel->findById($userId);
        $match = $this->matchModel->findById($matchId);
        
        // Validation
        $errors = [];
        
        if (!$match) {
            $errors[] = 'Invalid match selected.';
        } elseif ($match['status'] !== 'upcoming' || $match['betting_locked']) {
            $errors[] = 'Betting is closed for this match.';
        } elseif (strtotime($match['match_date']) <= time()) {
            $errors[] = 'Match has already started.';
        }
        
        $minBet = $this->settings->getMinBet();
        $maxBet = $this->settings->getMaxBet();
        
        if ($stake < $minBet) {
            $errors[] = "Minimum bet amount is $" . number_format($minBet, 2);
        }
        
        if ($stake > $maxBet) {
            $errors[] = "Maximum bet amount is $" . number_format($maxBet, 2);
        }
        
        if ($stake > $user['balance']) {
            $errors[] = 'Insufficient balance to place this bet.';
        }
        
        // Validate bet type and get odds
        $odds = $this->getOddsForBet($match, $betType, $selectedOption);
        if (!$odds) {
            $errors[] = 'Invalid bet selection.';
        }
        
        if (!empty($errors)) {
            foreach ($errors as $error) {
                Session::setFlash('error', $error);
            }
            header('Location: /match?id=' . $matchId);
            return;
        }
        
        // Create bet
        $betData = [
            'user_id' => $userId,
            'match_id' => $matchId,
            'bet_type' => $betType,
            'selected_option' => $selectedOption,
            'stake' => $stake,
            'odds' => $odds
        ];
        
        if ($this->betModel->create($betData)) {
            // Update user balance in session
            Session::set('user_balance', $user['balance'] - $stake);
            
            $potentialWin = $stake * $odds;
            Session::setFlash('success', "Bet placed successfully! Potential win: $" . number_format($potentialWin, 2));
        } else {
            Session::setFlash('error', 'Failed to place bet. Please try again.');
        }
        
        header('Location: /match?id=' . $matchId);
    }
    
    private function getOddsForBet($match, $betType, $selectedOption) {
        switch ($betType) {
            case '1x2':
                switch ($selectedOption) {
                    case 'home':
                        return $match['home_odds'];
                    case 'away':
                        return $match['away_odds'];
                    case 'draw':
                        return $match['draw_odds'];
                }
                break;
            case 'over_under':
                switch ($selectedOption) {
                    case 'over':
                        return $match['over_25_odds'];
                    case 'under':
                        return $match['under_25_odds'];
                }
                break;
            case 'btts':
                switch ($selectedOption) {
                    case 'yes':
                        return $match['btts_yes_odds'];
                    case 'no':
                        return $match['btts_no_odds'];
                }
                break;
        }
        
        return false;
    }
    
    public function dashboard() {
        if (!Session::isLoggedIn()) {
            header('Location: /login');
            return;
        }
        
        $userId = Session::get('user_id');
        $user = $this->userModel->findById($userId);
        
        // Get user betting statistics
        $userStats = $this->betModel->getUserStats($userId);
        
        // Get recent bets
        $recentBets = $this->betModel->getUserBets($userId, 10);
        
        // Get upcoming matches
        $upcomingMatches = $this->matchModel->getUpcomingMatches();
        $upcomingMatches = array_slice($upcomingMatches, 0, 5); // Limit to 5
        
        // Get recent transactions
        $transactions = $this->userModel->getUserTransactions($userId, 10);
        
        // Get deposit and withdrawal requests
        $depositRequests = $this->userModel->getUserDepositRequests($userId);
        $withdrawalRequests = $this->userModel->getUserWithdrawalRequests($userId);
        
        require_once 'views/dashboard.php';
    }
    
    public function betHistory() {
        if (!Session::isLoggedIn()) {
            header('Location: /login');
            return;
        }
        
        $userId = Session::get('user_id');
        $status = $_GET['status'] ?? null;
        $page = (int) ($_GET['page'] ?? 1);
        $perPage = 20;
        
        $bets = $this->betModel->getUserBetHistory($userId, $status, $perPage);
        $userStats = $this->betModel->getUserStats($userId);
        
        require_once 'views/bet_history.php';
    }
    
    public function liveScores() {
        $liveMatches = $this->matchModel->getLiveMatches();
        $finishedMatches = $this->matchModel->getFinishedMatches(20);
        
        // For AJAX requests, return JSON
        if (isset($_GET['ajax']) && $_GET['ajax'] === '1') {
            header('Content-Type: application/json');
            echo json_encode([
                'live' => $liveMatches,
                'finished' => $finishedMatches
            ]);
            return;
        }
        
        require_once 'views/live_scores.php';
    }
    
    public function search() {
        $query = trim($_GET['q'] ?? '');
        $matches = [];
        
        if (strlen($query) >= 2) {
            $matches = $this->matchModel->searchMatches($query);
        }
        
        // For AJAX requests, return JSON
        if (isset($_GET['ajax']) && $_GET['ajax'] === '1') {
            header('Content-Type: application/json');
            echo json_encode($matches);
            return;
        }
        
        require_once 'views/search_results.php';
    }
    
    public function odds() {
        $matchId = (int) ($_GET['match_id'] ?? 0);
        
        if ($matchId) {
            $odds = $this->matchModel->getMatchOdds($matchId);
            header('Content-Type: application/json');
            echo json_encode($odds);
        } else {
            header('HTTP/1.1 400 Bad Request');
            echo json_encode(['error' => 'Invalid match ID']);
        }
    }
    
    public function calculatePayout() {
        $stake = (float) ($_GET['stake'] ?? 0);
        $odds = (float) ($_GET['odds'] ?? 0);
        
        if ($stake > 0 && $odds > 1) {
            $payout = $stake * $odds;
            $profit = $payout - $stake;
            
            header('Content-Type: application/json');
            echo json_encode([
                'stake' => number_format($stake, 2),
                'odds' => number_format($odds, 2),
                'payout' => number_format($payout, 2),
                'profit' => number_format($profit, 2)
            ]);
        } else {
            header('HTTP/1.1 400 Bad Request');
            echo json_encode(['error' => 'Invalid stake or odds']);
        }
    }
    
    public function validateBet() {
        if (!Session::isLoggedIn()) {
            header('HTTP/1.1 401 Unauthorized');
            echo json_encode(['error' => 'Not logged in']);
            return;
        }
        
        $matchId = (int) ($_POST['match_id'] ?? 0);
        $stake = (float) ($_POST['stake'] ?? 0);
        $betType = $_POST['bet_type'] ?? '';
        $selectedOption = $_POST['selected_option'] ?? '';
        
        $userId = Session::get('user_id');
        $user = $this->userModel->findById($userId);
        $match = $this->matchModel->findById($matchId);
        
        $errors = [];
        $warnings = [];
        
        // Validate match
        if (!$match) {
            $errors[] = 'Invalid match selected.';
        } elseif ($match['status'] !== 'upcoming' || $match['betting_locked']) {
            $errors[] = 'Betting is closed for this match.';
        } elseif (strtotime($match['match_date']) <= time()) {
            $errors[] = 'Match has already started.';
        }
        
        // Validate stake
        $minBet = $this->settings->getMinBet();
        $maxBet = $this->settings->getMaxBet();
        
        if ($stake < $minBet) {
            $errors[] = "Minimum bet amount is $" . number_format($minBet, 2);
        }
        
        if ($stake > $maxBet) {
            $errors[] = "Maximum bet amount is $" . number_format($maxBet, 2);
        }
        
        if ($stake > $user['balance']) {
            $errors[] = 'Insufficient balance to place this bet.';
        }
        
        // Check if user already has bets on this match
        $userBets = $this->betModel->getUserBets($userId);
        $existingBets = array_filter($userBets, function($bet) use ($matchId) {
            return $bet['match_id'] == $matchId && $bet['status'] === 'pending';
        });
        
        if (count($existingBets) >= 3) {
            $warnings[] = 'You already have multiple bets on this match.';
        }
        
        // Validate bet type and get odds
        $odds = $this->getOddsForBet($match, $betType, $selectedOption);
        if (!$odds) {
            $errors[] = 'Invalid bet selection.';
        }
        
        header('Content-Type: application/json');
        echo json_encode([
            'valid' => empty($errors),
            'errors' => $errors,
            'warnings' => $warnings,
            'odds' => $odds,
            'potential_payout' => $odds ? $stake * $odds : 0
        ]);
    }
    
    public function popularMatches() {
        $limit = (int) ($_GET['limit'] ?? 10);
        $matches = $this->matchModel->getPopularMatches($limit);
        
        header('Content-Type: application/json');
        echo json_encode($matches);
    }
    
    public function matchCountdown() {
        $matchId = (int) ($_GET['match_id'] ?? 0);
        $match = $this->matchModel->findById($matchId);
        
        if ($match) {
            $matchTime = strtotime($match['match_date']);
            $currentTime = time();
            $timeLeft = $matchTime - $currentTime;
            
            header('Content-Type: application/json');
            echo json_encode([
                'match_time' => $matchTime,
                'current_time' => $currentTime,
                'time_left' => max(0, $timeLeft),
                'status' => $match['status'],
                'betting_locked' => $match['betting_locked']
            ]);
        } else {
            header('HTTP/1.1 404 Not Found');
            echo json_encode(['error' => 'Match not found']);
        }
    }
}