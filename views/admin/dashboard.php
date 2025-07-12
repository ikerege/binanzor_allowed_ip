<?php
$title = 'Admin Dashboard - Football Betting Platform';
require_once 'views/layout/header.php';
?>

<div class="container py-4">
    <!-- Admin Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card bg-dark text-white">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h2 class="mb-1">
                                <i class="bi bi-shield-lock me-2"></i>
                                Admin Dashboard
                            </h2>
                            <p class="mb-0 opacity-75">
                                Manage your betting platform with full control and insights.
                            </p>
                        </div>
                        <div class="col-md-4 text-md-end">
                            <div class="fs-5">
                                <i class="bi bi-clock me-2"></i>
                                <span id="live-clock"></span>
                            </div>
                            <small class="opacity-75">Current Time</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card text-center bg-primary text-white">
                <div class="card-body">
                    <i class="bi bi-people-fill display-4 mb-2"></i>
                    <h5 class="card-title"><?= $stats['total_users'] ?></h5>
                    <p class="card-text">Total Users</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card text-center bg-success text-white">
                <div class="card-body">
                    <i class="bi bi-calendar-event-fill display-4 mb-2"></i>
                    <h5 class="card-title"><?= $stats['total_matches'] ?></h5>
                    <p class="card-text">Total Matches</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card text-center bg-warning text-dark">
                <div class="card-body">
                    <i class="bi bi-receipt-cutoff display-4 mb-2"></i>
                    <h5 class="card-title"><?= $stats['total_bets'] ?></h5>
                    <p class="card-text">Total Bets</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card text-center bg-info text-white">
                <div class="card-body">
                    <i class="bi bi-cash-stack display-4 mb-2"></i>
                    <h5 class="card-title"><?= Helpers::formatMoney($stats['total_wagered']) ?></h5>
                    <p class="card-text">Total Wagered</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Management Cards -->
    <div class="row mb-4">
        <div class="col-12">
            <h3 class="mb-3">
                <i class="bi bi-gear-fill me-2"></i>Management
            </h3>
            <div class="row g-3">
                <div class="col-lg-3 col-md-6">
                    <a href="/admin/users" class="btn btn-outline-primary w-100 h-100 d-flex flex-column align-items-center justify-content-center py-4 text-decoration-none">
                        <i class="bi bi-people display-4 mb-3"></i>
                        <h5>Manage Users</h5>
                        <p class="mb-0 text-muted">View and manage user accounts</p>
                    </a>
                </div>
                <div class="col-lg-3 col-md-6">
                    <a href="/admin/matches" class="btn btn-outline-success w-100 h-100 d-flex flex-column align-items-center justify-content-center py-4 text-decoration-none">
                        <i class="bi bi-calendar-event display-4 mb-3"></i>
                        <h5>Manage Matches</h5>
                        <p class="mb-0 text-muted">Add, edit and update matches</p>
                    </a>
                </div>
                <div class="col-lg-3 col-md-6">
                    <a href="/admin/bets" class="btn btn-outline-warning w-100 h-100 d-flex flex-column align-items-center justify-content-center py-4 text-decoration-none">
                        <i class="bi bi-receipt display-4 mb-3"></i>
                        <h5>Manage Bets</h5>
                        <p class="mb-0 text-muted">View and settle bets</p>
                    </a>
                </div>
                <div class="col-lg-3 col-md-6">
                    <a href="/admin/add-match" class="btn btn-outline-info w-100 h-100 d-flex flex-column align-items-center justify-content-center py-4 text-decoration-none">
                        <i class="bi bi-plus-circle display-4 mb-3"></i>
                        <h5>Add Match</h5>
                        <p class="mb-0 text-muted">Create new betting matches</p>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bi bi-clock-history me-2"></i>Recent Betting Activity
                    </h5>
                    <a href="/admin/bets" class="btn btn-sm btn-outline-primary">View All Bets</a>
                </div>
                <div class="card-body">
                    <?php if (!empty($stats['recent_bets'])): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>User</th>
                                        <th>Match</th>
                                        <th>Bet Type</th>
                                        <th>Amount</th>
                                        <th>Odds</th>
                                        <th>Potential Win</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($stats['recent_bets'] as $bet): ?>
                                        <tr>
                                            <td>
                                                <i class="bi bi-person-circle me-1"></i>
                                                <?= htmlspecialchars($bet['username']) ?>
                                            </td>
                                            <td>
                                                <small class="text-muted d-block"><?= htmlspecialchars($bet['league'] ?? 'N/A') ?></small>
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
                                            <td><?= Helpers::formatMoney($bet['potential_win']) ?></td>
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
                                            </td>
                                            <td>
                                                <small><?= Helpers::timeAgo($bet['created_at']) ?></small>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="bi bi-receipt display-1 text-muted"></i>
                            <h5 class="mt-3 text-muted">No recent bets</h5>
                            <p class="text-muted">Betting activity will appear here</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-lightning-fill me-2"></i>Quick Actions
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-flex flex-wrap gap-2">
                        <form action="/admin/settle-bets" method="POST" class="d-inline">
                            <button type="submit" class="btn btn-warning" onclick="return confirm('Settle all pending bets for finished matches?')">
                                <i class="bi bi-check-circle me-2"></i>Settle Pending Bets
                            </button>
                        </form>
                        <a href="/admin/add-match" class="btn btn-success">
                            <i class="bi bi-plus-circle me-2"></i>Add New Match
                        </a>
                        <a href="/admin/users" class="btn btn-info">
                            <i class="bi bi-people me-2"></i>View All Users
                        </a>
                        <a href="/" class="btn btn-outline-secondary">
                            <i class="bi bi-house me-2"></i>View Site
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'views/layout/footer.php'; ?>