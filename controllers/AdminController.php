<?php

require_once 'models/User.php';
require_once 'models/Match.php';
require_once 'models/Bet.php';
require_once 'models/Announcement.php';
require_once 'models/Settings.php';
require_once 'core/Session.php';
require_once 'core/Helpers.php';

class AdminController {
    private $userModel;
    private $matchModel;
    private $betModel;
    private $announcementModel;
    private $settings;
    
    public function __construct() {
        $this->userModel = new User();
        $this->matchModel = new Match();
        $this->betModel = new Bet();
        $this->announcementModel = new Announcement();
        $this->settings = new Settings();
        
        // Check admin access
        if (!Session::isAdmin()) {
            Session::setFlash('error', 'Access denied. Admin privileges required.');
            header('Location: /');
            exit;
        }
    }
    
    public function dashboard() {
        // Get statistics
        $stats = [
            'total_users' => count($this->userModel->getAllUsers()),
            'total_matches' => count($this->matchModel->getAllMatches()),
            'pending_bets' => count($this->betModel->getPendingBets()),
            'pending_deposits' => count($this->userModel->getDepositRequests('pending')),
            'pending_withdrawals' => count($this->userModel->getWithdrawalRequests('pending')),
            'total_bet_amount' => $this->betModel->getTotalStats()['total_wagered'] ?? 0,
            'total_payouts' => $this->betModel->getTotalStats()['total_payouts'] ?? 0
        ];
        
        // Get recent activity
        $recentBets = $this->betModel->getAllBets(10);
        $recentUsers = array_slice($this->userModel->getAllUsers(), 0, 10);
        $recentMatches = array_slice($this->matchModel->getAllMatches(), 0, 10);
        $pendingDeposits = $this->userModel->getDepositRequests('pending');
        $pendingWithdrawals = $this->userModel->getWithdrawalRequests('pending');
        
        require_once 'views/admin/dashboard.php';
    }
    
    public function matches() {
        $action = $_GET['action'] ?? 'list';
        
        switch ($action) {
            case 'add':
                $this->addMatch();
                break;
            case 'edit':
                $this->editMatch();
                break;
            case 'delete':
                $this->deleteMatch();
                break;
            case 'settle':
                $this->settleMatch();
                break;
            case 'lock':
                $this->lockMatch();
                break;
            case 'unlock':
                $this->unlockMatch();
                break;
            default:
                $this->listMatches();
        }
    }
    
    private function listMatches() {
        $matches = $this->matchModel->getMatchesWithBets();
        require_once 'views/admin/matches.php';
    }
    
    private function addMatch() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $matchData = [
                'home_team' => trim($_POST['home_team']),
                'away_team' => trim($_POST['away_team']),
                'league' => trim($_POST['league']),
                'match_date' => $_POST['match_date'],
                'home_odds' => (float) $_POST['home_odds'],
                'away_odds' => (float) $_POST['away_odds'],
                'draw_odds' => (float) $_POST['draw_odds'],
                'over_25_odds' => (float) $_POST['over_25_odds'],
                'under_25_odds' => (float) $_POST['under_25_odds'],
                'btts_yes_odds' => (float) $_POST['btts_yes_odds'],
                'btts_no_odds' => (float) $_POST['btts_no_odds'],
                'status' => $_POST['status'] ?? 'upcoming'
            ];
            
            if ($this->matchModel->create($matchData)) {
                Session::setFlash('success', 'Match added successfully.');
                header('Location: /admin/matches');
                return;
            } else {
                Session::setFlash('error', 'Failed to add match.');
            }
        }
        
        require_once 'views/admin/add_match.php';
    }
    
    private function editMatch() {
        $id = (int) ($_GET['id'] ?? 0);
        $match = $this->matchModel->findById($id);
        
        if (!$match) {
            Session::setFlash('error', 'Match not found.');
            header('Location: /admin/matches');
            return;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $matchData = [
                'home_team' => trim($_POST['home_team']),
                'away_team' => trim($_POST['away_team']),
                'league' => trim($_POST['league']),
                'match_date' => $_POST['match_date'],
                'home_odds' => (float) $_POST['home_odds'],
                'away_odds' => (float) $_POST['away_odds'],
                'draw_odds' => (float) $_POST['draw_odds'],
                'over_25_odds' => (float) $_POST['over_25_odds'],
                'under_25_odds' => (float) $_POST['under_25_odds'],
                'btts_yes_odds' => (float) $_POST['btts_yes_odds'],
                'btts_no_odds' => (float) $_POST['btts_no_odds'],
                'status' => $_POST['status']
            ];
            
            if ($this->matchModel->updateMatch($id, $matchData)) {
                Session::setFlash('success', 'Match updated successfully.');
                header('Location: /admin/matches');
                return;
            } else {
                Session::setFlash('error', 'Failed to update match.');
            }
        }
        
        require_once 'views/admin/edit_match.php';
    }
    
    private function deleteMatch() {
        $id = (int) ($_GET['id'] ?? 0);
        
        if ($this->matchModel->deleteMatch($id)) {
            Session::setFlash('success', 'Match deleted successfully.');
        } else {
            Session::setFlash('error', 'Cannot delete match with existing bets.');
        }
        
        header('Location: /admin/matches');
    }
    
    private function settleMatch() {
        $id = (int) ($_GET['id'] ?? 0);
        $match = $this->matchModel->findById($id);
        
        if (!$match) {
            Session::setFlash('error', 'Match not found.');
            header('Location: /admin/matches');
            return;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $homeScore = (int) $_POST['home_score'];
            $awayScore = (int) $_POST['away_score'];
            
            if ($this->matchModel->updateResult($id, $homeScore, $awayScore)) {
                // Settle all bets for this match
                $this->betModel->settleBetsForMatch($id);
                Session::setFlash('success', 'Match settled and bets processed.');
            } else {
                Session::setFlash('error', 'Failed to settle match.');
            }
            
            header('Location: /admin/matches');
            return;
        }
        
        require_once 'views/admin/settle_match.php';
    }
    
    private function lockMatch() {
        $id = (int) ($_GET['id'] ?? 0);
        
        if ($this->matchModel->lockBetting($id)) {
            Session::setFlash('success', 'Betting locked for match.');
        } else {
            Session::setFlash('error', 'Failed to lock betting.');
        }
        
        header('Location: /admin/matches');
    }
    
    private function unlockMatch() {
        $id = (int) ($_GET['id'] ?? 0);
        
        if ($this->matchModel->unlockBetting($id)) {
            Session::setFlash('success', 'Betting unlocked for match.');
        } else {
            Session::setFlash('error', 'Failed to unlock betting.');
        }
        
        header('Location: /admin/matches');
    }
    
    public function users() {
        $action = $_GET['action'] ?? 'list';
        
        switch ($action) {
            case 'view':
                $this->viewUser();
                break;
            case 'edit':
                $this->editUser();
                break;
            case 'suspend':
                $this->suspendUser();
                break;
            case 'activate':
                $this->activateUser();
                break;
            case 'adjust_balance':
                $this->adjustUserBalance();
                break;
            default:
                $this->listUsers();
        }
    }
    
    private function listUsers() {
        $users = $this->userModel->getAllUsers();
        require_once 'views/admin/users.php';
    }
    
    private function viewUser() {
        $id = (int) ($_GET['id'] ?? 0);
        $user = $this->userModel->findById($id);
        
        if (!$user) {
            Session::setFlash('error', 'User not found.');
            header('Location: /admin/users');
            return;
        }
        
        $userBets = $this->betModel->getUserBets($id);
        $userStats = $this->betModel->getUserStats($id);
        $transactions = $this->userModel->getUserTransactions($id);
        
        require_once 'views/admin/view_user.php';
    }
    
    private function editUser() {
        $id = (int) ($_GET['id'] ?? 0);
        $user = $this->userModel->findById($id);
        
        if (!$user) {
            Session::setFlash('error', 'User not found.');
            header('Location: /admin/users');
            return;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userData = [
                'name' => trim($_POST['name']),
                'username' => trim($_POST['username']),
                'email' => trim($_POST['email'])
            ];
            
            if ($this->userModel->updateProfile($id, $userData)) {
                Session::setFlash('success', 'User updated successfully.');
                header('Location: /admin/users?action=view&id=' . $id);
                return;
            } else {
                Session::setFlash('error', 'Failed to update user.');
            }
        }
        
        require_once 'views/admin/edit_user.php';
    }
    
    private function suspendUser() {
        $id = (int) ($_GET['id'] ?? 0);
        
        if ($this->userModel->updateStatus($id, 'suspended')) {
            Session::setFlash('success', 'User suspended successfully.');
        } else {
            Session::setFlash('error', 'Failed to suspend user.');
        }
        
        header('Location: /admin/users');
    }
    
    private function activateUser() {
        $id = (int) ($_GET['id'] ?? 0);
        
        if ($this->userModel->updateStatus($id, 'active')) {
            Session::setFlash('success', 'User activated successfully.');
        } else {
            Session::setFlash('error', 'Failed to activate user.');
        }
        
        header('Location: /admin/users');
    }
    
    private function adjustUserBalance() {
        $id = (int) ($_GET['id'] ?? 0);
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $amount = (float) $_POST['amount'];
            $description = trim($_POST['description']);
            $type = $_POST['type']; // 'add' or 'deduct'
            
            if ($type === 'deduct') {
                $amount = -$amount;
            }
            
            if ($this->userModel->updateBalance($id, $amount, $description, 'admin_adjustment')) {
                Session::setFlash('success', 'User balance adjusted successfully.');
            } else {
                Session::setFlash('error', 'Failed to adjust user balance.');
            }
            
            header('Location: /admin/users?action=view&id=' . $id);
            return;
        }
        
        $user = $this->userModel->findById($id);
        require_once 'views/admin/adjust_balance.php';
    }
    
    public function deposits() {
        $action = $_GET['action'] ?? 'list';
        
        switch ($action) {
            case 'approve':
                $this->approveDeposit();
                break;
            case 'reject':
                $this->rejectDeposit();
                break;
            default:
                $this->listDeposits();
        }
    }
    
    private function listDeposits() {
        $status = $_GET['status'] ?? null;
        $deposits = $this->userModel->getDepositRequests($status);
        require_once 'views/admin/deposits.php';
    }
    
    private function approveDeposit() {
        $id = (int) ($_GET['id'] ?? 0);
        $adminId = Session::get('user_id');
        $adminNotes = $_POST['admin_notes'] ?? '';
        
        if ($this->userModel->processDepositRequest($id, 'approved', $adminId, $adminNotes)) {
            Session::setFlash('success', 'Deposit approved successfully.');
        } else {
            Session::setFlash('error', 'Failed to approve deposit.');
        }
        
        header('Location: /admin/deposits');
    }
    
    private function rejectDeposit() {
        $id = (int) ($_GET['id'] ?? 0);
        $adminId = Session::get('user_id');
        $adminNotes = $_POST['admin_notes'] ?? '';
        
        if ($this->userModel->processDepositRequest($id, 'rejected', $adminId, $adminNotes)) {
            Session::setFlash('success', 'Deposit rejected.');
        } else {
            Session::setFlash('error', 'Failed to reject deposit.');
        }
        
        header('Location: /admin/deposits');
    }
    
    public function withdrawals() {
        $action = $_GET['action'] ?? 'list';
        
        switch ($action) {
            case 'approve':
                $this->approveWithdrawal();
                break;
            case 'reject':
                $this->rejectWithdrawal();
                break;
            default:
                $this->listWithdrawals();
        }
    }
    
    private function listWithdrawals() {
        $status = $_GET['status'] ?? null;
        $withdrawals = $this->userModel->getWithdrawalRequests($status);
        require_once 'views/admin/withdrawals.php';
    }
    
    private function approveWithdrawal() {
        $id = (int) ($_GET['id'] ?? 0);
        $adminId = Session::get('user_id');
        $adminNotes = $_POST['admin_notes'] ?? '';
        
        if ($this->userModel->processWithdrawalRequest($id, 'approved', $adminId, $adminNotes)) {
            Session::setFlash('success', 'Withdrawal approved successfully.');
        } else {
            Session::setFlash('error', 'Failed to approve withdrawal.');
        }
        
        header('Location: /admin/withdrawals');
    }
    
    private function rejectWithdrawal() {
        $id = (int) ($_GET['id'] ?? 0);
        $adminId = Session::get('user_id');
        $adminNotes = $_POST['admin_notes'] ?? '';
        
        if ($this->userModel->processWithdrawalRequest($id, 'rejected', $adminId, $adminNotes)) {
            Session::setFlash('success', 'Withdrawal rejected.');
        } else {
            Session::setFlash('error', 'Failed to reject withdrawal.');
        }
        
        header('Location: /admin/withdrawals');
    }
    
    public function bets() {
        $action = $_GET['action'] ?? 'list';
        
        switch ($action) {
            case 'settle':
                $this->settleBet();
                break;
            case 'cancel':
                $this->cancelBet();
                break;
            default:
                $this->listBets();
        }
    }
    
    private function listBets() {
        $status = $_GET['status'] ?? null;
        
        if ($status) {
            $bets = $this->betModel->getUserBetHistory(null, $status, 100);
        } else {
            $bets = $this->betModel->getAllBets(100);
        }
        
        require_once 'views/admin/bets.php';
    }
    
    private function settleBet() {
        $id = (int) ($_GET['id'] ?? 0);
        $status = $_POST['status'] ?? 'won'; // 'won' or 'lost'
        $payout = (float) ($_POST['payout'] ?? 0);
        
        if ($this->betModel->settleBet($id, $status, $payout)) {
            if ($status === 'won' && $payout > 0) {
                $bet = $this->betModel->findById($id);
                $this->userModel->updateBalance($bet['user_id'], $payout, 
                    "Manual bet settlement - Bet #$id", 'bet_won');
            }
            
            Session::setFlash('success', 'Bet settled successfully.');
        } else {
            Session::setFlash('error', 'Failed to settle bet.');
        }
        
        header('Location: /admin/bets');
    }
    
    private function cancelBet() {
        $id = (int) ($_GET['id'] ?? 0);
        
        if ($this->betModel->cancelBet($id, true)) {
            Session::setFlash('success', 'Bet cancelled and refunded.');
        } else {
            Session::setFlash('error', 'Failed to cancel bet.');
        }
        
        header('Location: /admin/bets');
    }
    
    public function announcements() {
        $action = $_GET['action'] ?? 'list';
        
        switch ($action) {
            case 'add':
                $this->addAnnouncement();
                break;
            case 'edit':
                $this->editAnnouncement();
                break;
            case 'delete':
                $this->deleteAnnouncement();
                break;
            case 'toggle':
                $this->toggleAnnouncement();
                break;
            default:
                $this->listAnnouncements();
        }
    }
    
    private function listAnnouncements() {
        $announcements = $this->announcementModel->getAll();
        require_once 'views/admin/announcements.php';
    }
    
    private function addAnnouncement() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'title' => trim($_POST['title']),
                'content' => trim($_POST['content']),
                'type' => $_POST['type'],
                'is_active' => isset($_POST['is_active']),
                'created_by' => Session::get('user_id')
            ];
            
            if ($this->announcementModel->create($data)) {
                Session::setFlash('success', 'Announcement added successfully.');
                header('Location: /admin/announcements');
                return;
            } else {
                Session::setFlash('error', 'Failed to add announcement.');
            }
        }
        
        require_once 'views/admin/add_announcement.php';
    }
    
    private function editAnnouncement() {
        $id = (int) ($_GET['id'] ?? 0);
        $announcement = $this->announcementModel->findById($id);
        
        if (!$announcement) {
            Session::setFlash('error', 'Announcement not found.');
            header('Location: /admin/announcements');
            return;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'title' => trim($_POST['title']),
                'content' => trim($_POST['content']),
                'type' => $_POST['type'],
                'is_active' => isset($_POST['is_active'])
            ];
            
            if ($this->announcementModel->update($id, $data)) {
                Session::setFlash('success', 'Announcement updated successfully.');
                header('Location: /admin/announcements');
                return;
            } else {
                Session::setFlash('error', 'Failed to update announcement.');
            }
        }
        
        require_once 'views/admin/edit_announcement.php';
    }
    
    private function deleteAnnouncement() {
        $id = (int) ($_GET['id'] ?? 0);
        
        if ($this->announcementModel->delete($id)) {
            Session::setFlash('success', 'Announcement deleted successfully.');
        } else {
            Session::setFlash('error', 'Failed to delete announcement.');
        }
        
        header('Location: /admin/announcements');
    }
    
    private function toggleAnnouncement() {
        $id = (int) ($_GET['id'] ?? 0);
        
        if ($this->announcementModel->toggleStatus($id)) {
            Session::setFlash('success', 'Announcement status updated.');
        } else {
            Session::setFlash('error', 'Failed to update announcement status.');
        }
        
        header('Location: /admin/announcements');
    }
    
    public function settings() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $settingsData = [
                'site_name' => $_POST['site_name'],
                'min_deposit' => $_POST['min_deposit'],
                'max_deposit' => $_POST['max_deposit'],
                'min_withdrawal' => $_POST['min_withdrawal'],
                'max_withdrawal' => $_POST['max_withdrawal'],
                'min_bet' => $_POST['min_bet'],
                'max_bet' => $_POST['max_bet'],
                'new_user_bonus' => $_POST['new_user_bonus'],
                'maintenance_mode' => isset($_POST['maintenance_mode']) ? '1' : '0'
            ];
            
            if ($this->settings->bulkUpdate($settingsData)) {
                Session::setFlash('success', 'Settings updated successfully.');
            } else {
                Session::setFlash('error', 'Failed to update settings.');
            }
            
            header('Location: /admin/settings');
            return;
        }
        
        $allSettings = $this->settings->getAll();
        require_once 'views/admin/settings.php';
    }
    
    public function reports() {
        $type = $_GET['type'] ?? 'overview';
        
        switch ($type) {
            case 'users':
                $this->userReports();
                break;
            case 'bets':
                $this->betReports();
                break;
            case 'matches':
                $this->matchReports();
                break;
            case 'financial':
                $this->financialReports();
                break;
            default:
                $this->overviewReports();
        }
    }
    
    private function overviewReports() {
        $totalStats = $this->betModel->getTotalStats();
        $userStats = $this->userModel->getAllUsers();
        $matchStats = $this->matchModel->getAllMatches();
        
        require_once 'views/admin/reports_overview.php';
    }
    
    private function userReports() {
        $users = $this->userModel->getAllUsers();
        require_once 'views/admin/reports_users.php';
    }
    
    private function betReports() {
        $betsByType = $this->betModel->getBetsByType();
        $recentBets = $this->betModel->getAllBets(50);
        
        require_once 'views/admin/reports_bets.php';
    }
    
    private function matchReports() {
        $matches = $this->matchModel->getMatchesWithBets();
        require_once 'views/admin/reports_matches.php';
    }
    
    private function financialReports() {
        $transactions = $this->userModel->getAllTransactions(100);
        $totalStats = $this->betModel->getTotalStats();
        
        require_once 'views/admin/reports_financial.php';
    }
}