<?php
$title = 'Football Betting Platform - Home';
require_once 'views/layout/header.php';
?>

<!-- Hero Section -->
<div class="hero-section">
    <div class="container text-center">
        <h1><i class="bi bi-trophy-fill me-3"></i>Welcome to BetFootball</h1>
        <p class="lead mb-4">Your premier destination for football betting. Safe, secure, and exciting!</p>
        <div class="row g-4 mb-4">
            <div class="col-md-3 col-6">
                <div class="d-flex align-items-center justify-content-center">
                    <i class="bi bi-shield-check display-6 me-3"></i>
                    <div class="text-start">
                        <h5 class="mb-0">Secure</h5>
                        <small>Licensed Platform</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="d-flex align-items-center justify-content-center">
                    <i class="bi bi-lightning-fill display-6 me-3"></i>
                    <div class="text-start">
                        <h5 class="mb-0">Fast</h5>
                        <small>Instant Payouts</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="d-flex align-items-center justify-content-center">
                    <i class="bi bi-graph-up display-6 me-3"></i>
                    <div class="text-start">
                        <h5 class="mb-0">Best Odds</h5>
                        <small>Competitive Rates</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="d-flex align-items-center justify-content-center">
                    <i class="bi bi-headset display-6 me-3"></i>
                    <div class="text-start">
                        <h5 class="mb-0">Support</h5>
                        <small>24/7 Available</small>
                    </div>
                </div>
            </div>
        </div>
        <?php if (!Session::isLoggedIn()): ?>
            <div class="mt-4">
                <a href="/register" class="btn btn-warning btn-lg me-3">
                    <i class="bi bi-person-plus me-2"></i>Join Now & Get $100 Bonus
                </a>
                <a href="/login" class="btn btn-outline-light btn-lg">
                    <i class="bi bi-box-arrow-in-right me-2"></i>Login
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="container">
    <!-- Live Matches -->
    <?php if (!empty($liveMatches)): ?>
        <div class="row mb-5">
            <div class="col-12">
                <h2 class="mb-4">
                    <i class="bi bi-broadcast text-danger me-2"></i>
                    Live Matches 
                    <span class="badge bg-danger">LIVE</span>
                </h2>
                <div class="row">
                    <?php foreach ($liveMatches as $match): ?>
                        <div class="col-md-6 mb-4">
                            <div class="match-card">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <span class="badge bg-primary"><?= htmlspecialchars($match['league']) ?></span>
                                    <span class="badge bg-danger">
                                        <i class="bi bi-broadcast me-1"></i>LIVE
                                    </span>
                                </div>
                                
                                <div class="row text-center">
                                    <div class="col-5">
                                        <h5 class="mb-0"><?= htmlspecialchars($match['home_team']) ?></h5>
                                        <h3 class="text-primary"><?= $match['home_score'] ?? '0' ?></h3>
                                    </div>
                                    <div class="col-2">
                                        <div class="display-6">VS</div>
                                    </div>
                                    <div class="col-5">
                                        <h5 class="mb-0"><?= htmlspecialchars($match['away_team']) ?></h5>
                                        <h3 class="text-primary"><?= $match['away_score'] ?? '0' ?></h3>
                                    </div>
                                </div>
                                
                                <div class="text-center mt-3">
                                    <a href="/match/<?= $match['id'] ?>" class="btn btn-outline-primary">
                                        <i class="bi bi-eye me-1"></i>View Details
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Upcoming Matches -->
    <div class="row mb-5">
        <div class="col-12">
            <h2 class="mb-4">
                <i class="bi bi-calendar-event text-primary me-2"></i>
                Upcoming Matches
            </h2>
            <?php if (!empty($upcomingMatches)): ?>
                <div class="row">
                    <?php foreach (array_slice($upcomingMatches, 0, 6) as $match): ?>
                        <div class="col-lg-6 mb-4">
                            <div class="match-card">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <span class="badge bg-primary"><?= htmlspecialchars($match['league']) ?></span>
                                    <small class="text-muted">
                                        <i class="bi bi-clock me-1"></i>
                                        <?= Helpers::formatDateTime($match['match_date']) ?>
                                    </small>
                                </div>
                                
                                <div class="row text-center mb-3">
                                    <div class="col-5">
                                        <h5 class="mb-2"><?= htmlspecialchars($match['home_team']) ?></h5>
                                    </div>
                                    <div class="col-2">
                                        <div class="h4">VS</div>
                                    </div>
                                    <div class="col-5">
                                        <h5 class="mb-2"><?= htmlspecialchars($match['away_team']) ?></h5>
                                    </div>
                                </div>
                                
                                <!-- Betting Options -->
                                <?php if (Session::isLoggedIn()): ?>
                                    <div class="row g-2 mb-3">
                                        <div class="col-4">
                                            <button type="button" class="btn btn-outline-success w-100 odds-btn" 
                                                    data-bs-toggle="modal" data-bs-target="#betModal"
                                                    data-match-id="<?= $match['id'] ?>"
                                                    data-bet-type="home"
                                                    data-odds="<?= $match['home_odds'] ?>"
                                                    data-team="<?= htmlspecialchars($match['home_team']) ?>">
                                                <small>Home Win</small><br>
                                                <strong><?= $match['home_odds'] ?></strong>
                                            </button>
                                        </div>
                                        <div class="col-4">
                                            <button type="button" class="btn btn-outline-warning w-100 odds-btn"
                                                    data-bs-toggle="modal" data-bs-target="#betModal"
                                                    data-match-id="<?= $match['id'] ?>"
                                                    data-bet-type="draw"
                                                    data-odds="<?= $match['draw_odds'] ?>"
                                                    data-team="Draw">
                                                <small>Draw</small><br>
                                                <strong><?= $match['draw_odds'] ?></strong>
                                            </button>
                                        </div>
                                        <div class="col-4">
                                            <button type="button" class="btn btn-outline-danger w-100 odds-btn"
                                                    data-bs-toggle="modal" data-bs-target="#betModal"
                                                    data-match-id="<?= $match['id'] ?>"
                                                    data-bet-type="away"
                                                    data-odds="<?= $match['away_odds'] ?>"
                                                    data-team="<?= htmlspecialchars($match['away_team']) ?>">
                                                <small>Away Win</small><br>
                                                <strong><?= $match['away_odds'] ?></strong>
                                            </button>
                                        </div>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="text-center">
                                    <a href="/match/<?= $match['id'] ?>" class="btn btn-primary btn-sm">
                                        <i class="bi bi-info-circle me-1"></i>More Details
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="text-center mt-4">
                    <a href="/matches" class="btn btn-primary btn-lg">
                        <i class="bi bi-calendar-event me-2"></i>View All Matches
                    </a>
                </div>
            <?php else: ?>
                <div class="text-center py-5">
                    <i class="bi bi-calendar-x display-1 text-muted"></i>
                    <h4 class="mt-3 text-muted">No upcoming matches at the moment</h4>
                    <p class="text-muted">Check back later for new betting opportunities!</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Recent Winners -->
    <?php if (!empty($recentWinners)): ?>
        <div class="row mb-5">
            <div class="col-12">
                <h2 class="mb-4">
                    <i class="bi bi-star-fill text-warning me-2"></i>
                    Recent Winners
                </h2>
                <div class="row">
                    <?php foreach ($recentWinners as $winner): ?>
                        <div class="col-md-6 col-lg-4 mb-3">
                            <div class="card border-warning">
                                <div class="card-body">
                                    <div class="d-flex align-items-center mb-2">
                                        <i class="bi bi-trophy-fill text-warning me-2"></i>
                                        <strong><?= htmlspecialchars($winner['username']) ?></strong>
                                    </div>
                                    <div class="small text-muted mb-2">
                                        <?= htmlspecialchars($winner['home_team']) ?> vs <?= htmlspecialchars($winner['away_team']) ?>
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <span class="text-success">
                                            <i class="bi bi-cash me-1"></i>
                                            Won <?= Helpers::formatMoney($winner['potential_win']) ?>
                                        </span>
                                        <small class="text-muted">
                                            <?= Helpers::timeAgo($winner['created_at']) ?>
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="text-center mt-3">
                    <a href="/winners" class="btn btn-warning">
                        <i class="bi bi-star-fill me-2"></i>View All Winners
                    </a>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Features Section -->
    <div class="row mb-5">
        <div class="col-12">
            <h2 class="text-center mb-5">Why Choose BetFootball?</h2>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="card h-100 text-center">
                        <div class="card-body">
                            <i class="bi bi-shield-check display-4 text-primary mb-3"></i>
                            <h5>Secure & Licensed</h5>
                            <p class="text-muted">Your funds and data are protected with industry-leading security measures.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card h-100 text-center">
                        <div class="card-body">
                            <i class="bi bi-graph-up display-4 text-success mb-3"></i>
                            <h5>Best Odds</h5>
                            <p class="text-muted">Competitive odds across all major football leagues and tournaments.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card h-100 text-center">
                        <div class="card-body">
                            <i class="bi bi-lightning-fill display-4 text-warning mb-3"></i>
                            <h5>Instant Payouts</h5>
                            <p class="text-muted">Get your winnings instantly after match results are confirmed.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Betting Modal -->
<?php if (Session::isLoggedIn()): ?>
    <?php 
    require_once 'models/User.php';
    $userModel = new User();
    $userBalance = $userModel->getBalance(Session::getUserId());
    ?>
    <div class="modal fade" id="betModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Place Your Bet</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="/place-bet" method="POST" onsubmit="return validateBetForm(this)" data-max-balance="<?= $userBalance ?>">
                    <div class="modal-body">
                        <input type="hidden" name="match_id" id="bet-match-id">
                        <input type="hidden" name="bet_type" id="bet-type">
                        
                        <div class="text-center mb-3">
                            <h6 id="bet-description"></h6>
                            <div class="badge bg-primary" id="bet-odds"></div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="bet-amount" class="form-label">Bet Amount</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" class="form-control" name="amount" id="bet-amount" 
                                       min="1" max="<?= $userBalance ?>" step="0.01" required>
                            </div>
                            <div class="form-text">
                                Available balance: <?= Helpers::formatMoney($userBalance) ?>
                            </div>
                        </div>
                        
                        <div class="alert alert-info">
                            <strong>Potential Win: </strong>
                            <span id="potential-win">$0.00</span>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Place Bet</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Handle betting modal
        document.getElementById('betModal').addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const matchId = button.getAttribute('data-match-id');
            const betType = button.getAttribute('data-bet-type');
            const odds = parseFloat(button.getAttribute('data-odds'));
            const team = button.getAttribute('data-team');
            
            document.getElementById('bet-match-id').value = matchId;
            document.getElementById('bet-type').value = betType;
            document.getElementById('bet-description').textContent = 'Betting on: ' + team;
            document.getElementById('bet-odds').textContent = 'Odds: ' + odds;
            
            // Calculate potential win on amount change
            const amountInput = document.getElementById('bet-amount');
            const potentialWinSpan = document.getElementById('potential-win');
            
            amountInput.addEventListener('input', function() {
                const amount = parseFloat(this.value) || 0;
                const potentialWin = amount * odds;
                potentialWinSpan.textContent = '$' + potentialWin.toFixed(2);
            });
        });
    </script>
<?php endif; ?>

<?php require_once 'views/layout/footer.php'; ?>