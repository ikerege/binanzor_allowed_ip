    <!-- Footer -->
    <footer class="footer mt-5 py-4">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5><i class="bi bi-trophy-fill me-2"></i>BetFootball</h5>
                    <p class="mb-0">Your premier destination for football betting. Safe, secure, and exciting!</p>
                </div>
                <div class="col-md-3">
                    <h6>Quick Links</h6>
                    <ul class="list-unstyled">
                        <li><a href="/matches" class="text-light text-decoration-none">Matches</a></li>
                        <li><a href="/live" class="text-light text-decoration-none">Live Games</a></li>
                        <li><a href="/results" class="text-light text-decoration-none">Results</a></li>
                        <li><a href="/winners" class="text-light text-decoration-none">Winners</a></li>
                    </ul>
                </div>
                <div class="col-md-3">
                    <h6>Account</h6>
                    <ul class="list-unstyled">
                        <?php if (Session::isLoggedIn()): ?>
                            <li><a href="/dashboard" class="text-light text-decoration-none">Dashboard</a></li>
                            <li><a href="/my-bets" class="text-light text-decoration-none">My Bets</a></li>
                            <li><a href="/profile" class="text-light text-decoration-none">Profile</a></li>
                        <?php else: ?>
                            <li><a href="/login" class="text-light text-decoration-none">Login</a></li>
                            <li><a href="/register" class="text-light text-decoration-none">Register</a></li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
            <hr class="my-4">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <p class="mb-0">&copy; <?= date('Y') ?> BetFootball. All rights reserved.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <small class="text-muted">
                        <i class="bi bi-shield-check me-1"></i>
                        Secure & Licensed Platform
                    </small>
                </div>
            </div>
        </div>
    </footer>
    
    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JavaScript -->
    <script>
        // Auto-hide alerts after 5 seconds
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                if (alert.querySelector('.btn-close')) {
                    alert.style.transition = 'opacity 0.5s';
                    alert.style.opacity = '0';
                    setTimeout(() => alert.remove(), 500);
                }
            });
        }, 5000);
        
        // Betting form validation
        function validateBetForm(form) {
            const amount = parseFloat(form.amount.value);
            const maxBalance = parseFloat(form.getAttribute('data-max-balance') || 0);
            
            if (isNaN(amount) || amount <= 0) {
                alert('Please enter a valid bet amount.');
                return false;
            }
            
            if (amount < 1) {
                alert('Minimum bet amount is $1.');
                return false;
            }
            
            if (amount > maxBalance) {
                alert('Insufficient balance. Your current balance is $' + maxBalance.toFixed(2));
                return false;
            }
            
            return confirm('Are you sure you want to place this bet for $' + amount.toFixed(2) + '?');
        }
        
        // Live clock
        function updateClock() {
            const now = new Date();
            const timeString = now.toLocaleTimeString();
            const clockElement = document.getElementById('live-clock');
            if (clockElement) {
                clockElement.textContent = timeString;
            }
        }
        
        // Update clock every second
        setInterval(updateClock, 1000);
        updateClock(); // Initial call
        
        // Countdown timer for upcoming matches
        function updateCountdowns() {
            const countdowns = document.querySelectorAll('.countdown-timer');
            countdowns.forEach(countdown => {
                const matchDate = new Date(countdown.getAttribute('data-match-date'));
                const now = new Date();
                const diff = matchDate - now;
                
                if (diff > 0) {
                    const days = Math.floor(diff / (1000 * 60 * 60 * 24));
                    const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                    const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
                    const seconds = Math.floor((diff % (1000 * 60)) / 1000);
                    
                    let timeString = '';
                    if (days > 0) timeString += days + 'd ';
                    if (hours > 0) timeString += hours + 'h ';
                    if (minutes > 0) timeString += minutes + 'm ';
                    timeString += seconds + 's';
                    
                    countdown.textContent = timeString;
                } else {
                    countdown.textContent = 'Starting soon';
                    countdown.classList.remove('text-primary');
                    countdown.classList.add('text-danger');
                }
            });
        }
        
        // Update countdowns every second
        setInterval(updateCountdowns, 1000);
        updateCountdowns(); // Initial call
        
        // Auto-refresh live matches every 30 seconds
        if (window.location.pathname === '/live') {
            setInterval(() => {
                window.location.reload();
            }, 30000);
        }
    </script>
</body>
</html>