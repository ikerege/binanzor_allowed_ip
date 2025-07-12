<?php
$title = 'Page Not Found - Football Betting Platform';
require_once 'views/layout/header.php';
?>

<div class="container">
    <div class="row justify-content-center" style="min-height: 70vh; align-items: center;">
        <div class="col-md-8 col-lg-6 text-center">
            <div class="card">
                <div class="card-body p-5">
                    <i class="bi bi-exclamation-triangle display-1 text-warning mb-4"></i>
                    <h1 class="display-4 text-primary mb-3">404</h1>
                    <h2 class="h4 mb-3">Page Not Found</h2>
                    <p class="text-muted mb-4">
                        Oops! The page you're looking for seems to have gone offside. 
                        It might have been moved, deleted, or you entered the wrong URL.
                    </p>
                    
                    <div class="d-grid gap-2 d-md-flex justify-content-md-center mb-4">
                        <a href="/" class="btn btn-primary btn-lg me-md-2">
                            <i class="bi bi-house-fill me-2"></i>Go Home
                        </a>
                        <a href="/matches" class="btn btn-outline-primary btn-lg">
                            <i class="bi bi-calendar-event me-2"></i>View Matches
                        </a>
                    </div>
                    
                    <div class="row g-3 mt-4">
                        <div class="col-md-4">
                            <div class="d-flex align-items-center justify-content-center">
                                <i class="bi bi-trophy-fill text-warning h3 me-2"></i>
                                <div class="text-start">
                                    <div class="fw-bold">Live Betting</div>
                                    <small class="text-muted">Place bets now</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="d-flex align-items-center justify-content-center">
                                <i class="bi bi-graph-up text-success h3 me-2"></i>
                                <div class="text-start">
                                    <div class="fw-bold">Best Odds</div>
                                    <small class="text-muted">Competitive rates</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="d-flex align-items-center justify-content-center">
                                <i class="bi bi-lightning-fill text-info h3 me-2"></i>
                                <div class="text-start">
                                    <div class="fw-bold">Fast Payouts</div>
                                    <small class="text-muted">Instant wins</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'views/layout/footer.php'; ?>