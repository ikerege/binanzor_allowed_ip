<?php
$title = 'Add Match - Admin Panel';
require_once 'views/layout/header.php';
?>

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">
                            <i class="bi bi-plus-circle me-2"></i>Add New Match
                        </h4>
                        <a href="/admin/matches" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left me-2"></i>Back to Matches
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <form action="/admin/add-match" method="POST" id="addMatchForm">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="home_team" class="form-label">
                                    <i class="bi bi-house me-2"></i>Home Team
                                </label>
                                <input type="text" class="form-control" name="home_team" id="home_team" required 
                                       placeholder="Enter home team name">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="away_team" class="form-label">
                                    <i class="bi bi-airplane me-2"></i>Away Team
                                </label>
                                <input type="text" class="form-control" name="away_team" id="away_team" required 
                                       placeholder="Enter away team name">
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="league" class="form-label">
                                    <i class="bi bi-trophy me-2"></i>League/Competition
                                </label>
                                <select class="form-select" name="league" id="league" required>
                                    <option value="">Select League</option>
                                    <option value="Premier League">Premier League</option>
                                    <option value="La Liga">La Liga</option>
                                    <option value="Bundesliga">Bundesliga</option>
                                    <option value="Serie A">Serie A</option>
                                    <option value="Ligue 1">Ligue 1</option>
                                    <option value="Champions League">Champions League</option>
                                    <option value="Europa League">Europa League</option>
                                    <option value="World Cup">World Cup</option>
                                    <option value="Copa America">Copa America</option>
                                    <option value="Euro Championship">Euro Championship</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="match_date" class="form-label">
                                    <i class="bi bi-calendar me-2"></i>Match Date & Time
                                </label>
                                <input type="datetime-local" class="form-control" name="match_date" id="match_date" required>
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <h5 class="mb-3">
                                <i class="bi bi-calculator me-2"></i>Betting Odds
                            </h5>
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label for="home_odds" class="form-label">Home Win Odds</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" name="home_odds" id="home_odds" 
                                               min="1.01" max="50" step="0.01" required placeholder="2.50">
                                        <span class="input-group-text">:1</span>
                                    </div>
                                    <div class="form-text">Odds for home team victory</div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="draw_odds" class="form-label">Draw Odds</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" name="draw_odds" id="draw_odds" 
                                               min="1.01" max="50" step="0.01" required placeholder="3.20">
                                        <span class="input-group-text">:1</span>
                                    </div>
                                    <div class="form-text">Odds for draw result</div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="away_odds" class="form-label">Away Win Odds</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" name="away_odds" id="away_odds" 
                                               min="1.01" max="50" step="0.01" required placeholder="2.80">
                                        <span class="input-group-text">:1</span>
                                    </div>
                                    <div class="form-text">Odds for away team victory</div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Odds Calculator -->
                        <div class="alert alert-info">
                            <h6><i class="bi bi-info-circle me-2"></i>Odds Calculator</h6>
                            <p class="mb-2">Test your odds with a sample bet:</p>
                            <div class="row align-items-end">
                                <div class="col-md-3">
                                    <label class="form-label">Bet Amount</label>
                                    <input type="number" class="form-control" id="sample_bet" value="10" min="1" step="0.01">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Bet On</label>
                                    <select class="form-select" id="sample_bet_type">
                                        <option value="home">Home Win</option>
                                        <option value="draw">Draw</option>
                                        <option value="away">Away Win</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Potential Win</label>
                                    <div class="form-control" id="potential_win">$0.00</div>
                                </div>
                                <div class="col-md-3">
                                    <button type="button" class="btn btn-info" onclick="calculatePotentialWin()">
                                        Calculate
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-between">
                            <a href="/admin/matches" class="btn btn-secondary">
                                <i class="bi bi-x-circle me-2"></i>Cancel
                            </a>
                            <button type="submit" class="btn btn-success">
                                <i class="bi bi-check-circle me-2"></i>Add Match
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Set minimum date to current time
document.getElementById('match_date').min = new Date().toISOString().slice(0, 16);

// Form validation
document.getElementById('addMatchForm').addEventListener('submit', function(e) {
    const homeTeam = document.getElementById('home_team').value.trim();
    const awayTeam = document.getElementById('away_team').value.trim();
    
    if (homeTeam.toLowerCase() === awayTeam.toLowerCase()) {
        e.preventDefault();
        alert('Home team and away team cannot be the same!');
        return false;
    }
    
    const matchDate = new Date(document.getElementById('match_date').value);
    const now = new Date();
    
    if (matchDate <= now) {
        e.preventDefault();
        alert('Match date must be in the future!');
        return false;
    }
    
    const homeOdds = parseFloat(document.getElementById('home_odds').value);
    const drawOdds = parseFloat(document.getElementById('draw_odds').value);
    const awayOdds = parseFloat(document.getElementById('away_odds').value);
    
    if (homeOdds < 1.01 || drawOdds < 1.01 || awayOdds < 1.01) {
        e.preventDefault();
        alert('All odds must be at least 1.01!');
        return false;
    }
    
    return true;
});

// Calculate potential win
function calculatePotentialWin() {
    const betAmount = parseFloat(document.getElementById('sample_bet').value) || 0;
    const betType = document.getElementById('sample_bet_type').value;
    
    let odds = 0;
    switch(betType) {
        case 'home':
            odds = parseFloat(document.getElementById('home_odds').value) || 0;
            break;
        case 'draw':
            odds = parseFloat(document.getElementById('draw_odds').value) || 0;
            break;
        case 'away':
            odds = parseFloat(document.getElementById('away_odds').value) || 0;
            break;
    }
    
    const potentialWin = betAmount * odds;
    document.getElementById('potential_win').textContent = '$' + potentialWin.toFixed(2);
}

// Auto-calculate when odds change
document.getElementById('home_odds').addEventListener('input', calculatePotentialWin);
document.getElementById('draw_odds').addEventListener('input', calculatePotentialWin);
document.getElementById('away_odds').addEventListener('input', calculatePotentialWin);
document.getElementById('sample_bet').addEventListener('input', calculatePotentialWin);
document.getElementById('sample_bet_type').addEventListener('change', calculatePotentialWin);

// Custom league input
document.getElementById('league').addEventListener('change', function() {
    if (this.value === 'Other') {
        const customLeague = prompt('Enter custom league name:');
        if (customLeague) {
            const option = new Option(customLeague, customLeague, true, true);
            this.add(option);
        } else {
            this.value = '';
        }
    }
});
</script>

<?php require_once 'views/layout/footer.php'; ?>