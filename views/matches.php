<?php
$title = 'All Matches - Football Betting Platform';
require_once 'views/layout/header.php';
?>

<div class="container py-4">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <h2>
                <i class="bi bi-calendar-event text-primary me-2"></i>
                Football Matches
            </h2>
            <p class="text-muted">Browse all available matches and place your bets</p>
        </div>
    </div>

    <!-- Live Matches -->
    <?php if (!empty($liveMatches)): ?>
        <div class="row mb-5">
            <div class="col-12">
                <h3 class="mb-3">
                    <i class="bi bi-broadcast text-danger me-2"></i>
                    Live Matches 
                    <span class="badge bg-danger ms-2">LIVE</span>
                </h3>
                <div class="row">
                    <?php foreach ($liveMatches as $match): ?>
                        <div class="col-lg-6 mb-4">
                            <div class="match-card border-danger">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <span class="badge bg-primary"><?= htmlspecialchars($match['league']) ?></span>
                                    <span class="badge bg-danger">
                                        <i class="bi bi-broadcast me-1"></i>LIVE
                                    </span>
                                </div>
                                
                                <div class="row text-center mb-3">
                                    <div class="col-5">
                                        <h5 class="mb-1"><?= htmlspecialchars($match['home_team']) ?></h5>
                                        <h2 class="text-primary mb-0"><?= $match['home_score'] ?? '0' ?></h2>
                                    </div>
                                    <div class="col-2">
                                        <div class="h4 text-muted">VS</div>
                                        <small class="text-danger">
                                            <i class="bi bi-circle-fill" style="font-size: 0.5rem;"></i>
                                            LIVE
                                        </small>
                                    </div>
                                    <div class="col-5">
                                        <h5 class="mb-1"><?= htmlspecialchars($match['away_team']) ?></h5>
                                        <h2 class="text-primary mb-0"><?= $match['away_score'] ?? '0' ?></h2>
                                    </div>
                                </div>
                                
                                <div class="text-center">
                                    <a href="/match/<?= $match['id'] ?>" class="btn btn-outline-danger">
                                        <i class="bi bi-eye me-1"></i>Watch Live
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
            <h3 class="mb-3">
                <i class="bi bi-clock text-warning me-2"></i>
                Upcoming Matches
            </h3>
            <?php if (!empty($upcomingMatches)): ?>
                <div class="row">
                    <?php foreach ($upcomingMatches as $match): ?>
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
                                        <div class="h4 text-muted">VS</div>
                                        <small class="text-primary countdown-timer" 
                                               data-match-date="<?= $match['match_date'] ?>">
                                            Loading...
                                        </small>
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
                                                <small>Home</small><br>
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
                                                <small>Away</small><br>
                                                <strong><?= $match['away_odds'] ?></strong>
                                            </button>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <div class="row g-2 mb-3">
                                        <div class="col-4 text-center">
                                            <div class="border rounded p-2">
                                                <small class="text-muted">Home</small><br>
                                                <strong><?= $match['home_odds'] ?></strong>
                                            </div>
                                        </div>
                                        <div class="col-4 text-center">
                                            <div class="border rounded p-2">
                                                <small class="text-muted">Draw</small><br>
                                                <strong><?= $match['draw_odds'] ?></strong>
                                            </div>
                                        </div>
                                        <div class="col-4 text-center">
                                            <div class="border rounded p-2">
                                                <small class="text-muted">Away</small><br>
                                                <strong><?= $match['away_odds'] ?></strong>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="text-center">
                                        <a href="/login" class="btn btn-primary btn-sm">
                                            <i class="bi bi-box-arrow-in-right me-1"></i>Login to Bet
                                        </a>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="text-center mt-2">
                                    <a href="/match/<?= $match['id'] ?>" class="btn btn-outline-primary btn-sm">
                                        <i class="bi bi-info-circle me-1"></i>Match Details
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="text-center py-5">
                    <i class="bi bi-calendar-x display-1 text-muted"></i>
                    <h4 class="mt-3 text-muted">No upcoming matches</h4>
                    <p class="text-muted">Check back later for new betting opportunities!</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Recent Results -->
    <?php if (!empty($finishedMatches)): ?>
        <div class="row mb-5">
            <div class="col-12">
                <h3 class="mb-3">
                    <i class="bi bi-check-circle text-success me-2"></i>
                    Recent Results
                </h3>
                <div class="row">
                    <?php foreach ($finishedMatches as $match): ?>
                        <div class="col-lg-4 col-md-6 mb-3">
                            <div class="card">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <small class="text-primary"><?= htmlspecialchars($match['league']) ?></small>
                                        <span class="badge bg-success">Finished</span>
                                    </div>
                                    
                                    <div class="row text-center">
                                        <div class="col-5">
                                            <div class="fw-bold"><?= htmlspecialchars($match['home_team']) ?></div>
                                            <h4 class="text-primary"><?= $match['home_score'] ?></h4>
                                        </div>
                                        <div class="col-2">
                                            <small class="text-muted">VS</small>
                                        </div>
                                        <div class="col-5">
                                            <div class="fw-bold"><?= htmlspecialchars($match['away_team']) ?></div>
                                            <h4 class="text-primary"><?= $match['away_score'] ?></h4>
                                        </div>
                                    </div>
                                    
                                    <div class="text-center mt-2">
                                        <small class="text-muted">
                                            <?= Helpers::formatDate($match['match_date']) ?>
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="text-center">
                    <a href="/results" class="btn btn-outline-success">
                        <i class="bi bi-trophy me-2"></i>View All Results
                    </a>
                </div>
            </div>
        </div>
    <?php endif; ?>
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