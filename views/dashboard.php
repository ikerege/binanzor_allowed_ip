<?php
$title = 'Dashboard - Football Betting Platform';
require_once 'views/layout/header.php';
?>

<div class="container py-4">
    <!-- Welcome Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card bg-gradient-primary text-white">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h2 class="mb-1">
                                <i class="bi bi-speedometer2 me-2"></i>
                                Welcome back, <?= htmlspecialchars($user['username']) ?>!
                            </h2>
                            <p class="mb-0 opacity-75">
                                Ready to place some winning bets? Check out the latest matches below.
                            </p>
                        </div>
                        <div class="col-md-4 text-md-end">
                            <div class="balance-display fs-4">
                                <i class="bi bi-wallet2 me-2"></i>
                                <?= Helpers::formatMoney($user['balance']) ?>
                            </div>
                            <small class="opacity-75">Available Balance</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card text-center">
                <div class="card-body">
                    <i class="bi bi-receipt-cutoff display-4 text-primary mb-2"></i>
                    <h5 class="card-title"><?= $userStats['total_bets'] ?? 0 ?></h5>
                    <p class="card-text text-muted">Total Bets</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card text-center">
                <div class="card-body">
                    <i class="bi bi-cash-stack display-4 text-success mb-2"></i>
                    <h5 class="card-title"><?= Helpers::formatMoney($userStats['total_wagered'] ?? 0) ?></h5>
                    <p class="card-text text-muted">Total Wagered</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card text-center">
                <div class="card-body">
                    <i class="bi bi-trophy-fill display-4 text-warning mb-2"></i>
                    <h5 class="card-title"><?= $userStats['bets_won'] ?? 0 ?></h5>
                    <p class="card-text text-muted">Bets Won</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card text-center">
                <div class="card-body">
                    <i class="bi bi-clock-fill display-4 text-info mb-2"></i>
                    <h5 class="card-title"><?= $userStats['bets_pending'] ?? 0 ?></h5>
                    <p class="card-text text-muted">Pending</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Recent Bets -->
        <div class="col-lg-8 mb-4">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bi bi-clock-history me-2"></i>Recent Bets
                    </h5>
                    <a href="/my-bets" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
                <div class="card-body">
                    <?php if (!empty($userBets)): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Match</th>
                                        <th>Bet</th>
                                        <th>Amount</th>
                                        <th>Odds</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($userBets as $bet): ?>
                                        <tr>
                                            <td>
                                                <small class="text-muted d-block"><?= htmlspecialchars($bet['league']) ?></small>
                                                <strong><?= htmlspecialchars($bet['home_team']) ?></strong>
                                                <small>vs</small>
                                                <strong><?= htmlspecialchars($bet['away_team']) ?></strong>
                                            </td>
                                            <td>
                                                <span class="badge bg-secondary">
                                                    <?php
                                                    switch($bet['bet_type']) {
                                                        case 'home': echo htmlspecialchars($bet['home_team']); break;
                                                        case 'away': echo htmlspecialchars($bet['away_team']); break;
                                                        case 'draw': echo 'Draw'; break;
                                                    }
                                                    ?>
                                                </span>
                                            </td>
                                            <td><?= Helpers::formatMoney($bet['amount']) ?></td>
                                            <td><?= $bet['odds'] ?></td>
                                            <td>
                                                <?php
                                                $statusClass = [
                                                    'pending' => 'warning',
                                                    'won' => 'success',
                                                    'lost' => 'danger'
                                                ];
                                                ?>
                                                <span class="badge bg-<?= $statusClass[$bet['status']] ?>">
                                                    <?= ucfirst($bet['status']) ?>
                                                </span>
                                                <?php if ($bet['status'] === 'won'): ?>
                                                    <small class="text-success d-block">
                                                        Won <?= Helpers::formatMoney($bet['potential_win']) ?>
                                                    </small>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <small><?= Helpers::formatDate($bet['created_at']) ?></small>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="bi bi-receipt display-1 text-muted"></i>
                            <h5 class="mt-3 text-muted">No bets yet</h5>
                            <p class="text-muted">Start betting on upcoming matches!</p>
                            <a href="/matches" class="btn btn-primary">
                                <i class="bi bi-calendar-event me-2"></i>Browse Matches
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Upcoming Matches -->
        <div class="col-lg-4 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-calendar-event me-2"></i>Upcoming Matches
                    </h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($upcomingMatches)): ?>
                        <?php foreach (array_slice($upcomingMatches, 0, 5) as $match): ?>
                            <div class="mb-3 pb-3 border-bottom">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <small class="text-primary"><?= htmlspecialchars($match['league']) ?></small>
                                    <small class="text-muted">
                                        <?= Helpers::formatDateTime($match['match_date']) ?>
                                    </small>
                                </div>
                                <div class="fw-bold mb-2">
                                    <?= htmlspecialchars($match['home_team']) ?> 
                                    <small class="text-muted">vs</small> 
                                    <?= htmlspecialchars($match['away_team']) ?>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span class="badge bg-outline-success"><?= $match['home_odds'] ?></span>
                                    <span class="badge bg-outline-warning"><?= $match['draw_odds'] ?></span>
                                    <span class="badge bg-outline-danger"><?= $match['away_odds'] ?></span>
                                </div>
                                <div class="mt-2">
                                    <a href="/match/<?= $match['id'] ?>" class="btn btn-sm btn-outline-primary w-100">
                                        <i class="bi bi-eye me-1"></i>View & Bet
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        
                        <div class="text-center">
                            <a href="/matches" class="btn btn-primary">
                                <i class="bi bi-calendar-event me-2"></i>View All Matches
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-3">
                            <i class="bi bi-calendar-x display-4 text-muted"></i>
                            <p class="text-muted mt-2">No upcoming matches</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-lightning-fill me-2"></i>Quick Actions
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3 col-sm-6">
                            <a href="/matches" class="btn btn-outline-primary w-100 h-100 d-flex flex-column align-items-center justify-content-center py-3">
                                <i class="bi bi-calendar-event display-5 mb-2"></i>
                                <span>Browse Matches</span>
                            </a>
                        </div>
                        <div class="col-md-3 col-sm-6">
                            <a href="/live" class="btn btn-outline-danger w-100 h-100 d-flex flex-column align-items-center justify-content-center py-3">
                                <i class="bi bi-broadcast display-5 mb-2"></i>
                                <span>Live Matches</span>
                            </a>
                        </div>
                        <div class="col-md-3 col-sm-6">
                            <a href="/my-bets" class="btn btn-outline-info w-100 h-100 d-flex flex-column align-items-center justify-content-center py-3">
                                <i class="bi bi-receipt display-5 mb-2"></i>
                                <span>My Bets</span>
                            </a>
                        </div>
                        <div class="col-md-3 col-sm-6">
                            <a href="/profile" class="btn btn-outline-secondary w-100 h-100 d-flex flex-column align-items-center justify-content-center py-3">
                                <i class="bi bi-person-gear display-5 mb-2"></i>
                                <span>Profile</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'views/layout/footer.php'; ?>