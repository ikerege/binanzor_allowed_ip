-- Enhanced Football Betting Platform Database Schema
-- Run this script to create the database and tables

CREATE DATABASE IF NOT EXISTS football_betting CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE football_betting;

-- Users table (enhanced)
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('user', 'admin') DEFAULT 'user',
    balance DECIMAL(10,2) DEFAULT 0.00,
    status ENUM('active', 'suspended', 'pending') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_username (username),
    INDEX idx_role (role),
    INDEX idx_status (status)
);

-- Enhanced Matches table with all betting options
CREATE TABLE matches (
    id INT AUTO_INCREMENT PRIMARY KEY,
    home_team VARCHAR(100) NOT NULL,
    away_team VARCHAR(100) NOT NULL,
    league VARCHAR(100) NOT NULL,
    match_date DATETIME NOT NULL,
    
    -- 1X2 Odds
    home_odds DECIMAL(5,2) NOT NULL,
    away_odds DECIMAL(5,2) NOT NULL,
    draw_odds DECIMAL(5,2) NOT NULL,
    
    -- Over/Under 2.5 Goals Odds
    over_25_odds DECIMAL(5,2) NOT NULL DEFAULT 1.90,
    under_25_odds DECIMAL(5,2) NOT NULL DEFAULT 1.90,
    
    -- Both Teams to Score Odds
    btts_yes_odds DECIMAL(5,2) NOT NULL DEFAULT 1.80,
    btts_no_odds DECIMAL(5,2) NOT NULL DEFAULT 2.00,
    
    -- Match Results
    home_score INT NULL,
    away_score INT NULL,
    total_goals INT NULL,
    both_teams_scored BOOLEAN NULL,
    
    status ENUM('upcoming', 'live', 'finished', 'cancelled') DEFAULT 'upcoming',
    betting_locked BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_match_date (match_date),
    INDEX idx_status (status),
    INDEX idx_league (league),
    INDEX idx_betting_locked (betting_locked)
);

-- Enhanced Bets table with multiple bet types
CREATE TABLE bets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    match_id INT NOT NULL,
    bet_type ENUM('1x2', 'over_under', 'btts') NOT NULL,
    selected_option VARCHAR(20) NOT NULL, -- 'home', 'away', 'draw', 'over', 'under', 'yes', 'no'
    stake DECIMAL(10,2) NOT NULL,
    odds DECIMAL(5,2) NOT NULL,
    potential_payout DECIMAL(10,2) NOT NULL,
    actual_payout DECIMAL(10,2) DEFAULT 0.00,
    status ENUM('pending', 'won', 'lost', 'cancelled') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (match_id) REFERENCES matches(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_match_id (match_id),
    INDEX idx_status (status),
    INDEX idx_bet_type (bet_type),
    INDEX idx_created_at (created_at)
);

-- Transactions table for wallet management
CREATE TABLE transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    type ENUM('deposit', 'withdrawal', 'bet_placed', 'bet_won', 'bet_refund', 'admin_adjustment') NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    balance_before DECIMAL(10,2) NOT NULL,
    balance_after DECIMAL(10,2) NOT NULL,
    description TEXT,
    reference_id INT NULL, -- bet_id for bet transactions, deposit_id for deposits, etc.
    status ENUM('pending', 'completed', 'rejected', 'cancelled') DEFAULT 'completed',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_type (type),
    INDEX idx_status (status),
    INDEX idx_created_at (created_at)
);

-- Deposit requests table
CREATE TABLE deposit_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    payment_method VARCHAR(50) NOT NULL,
    transaction_ref VARCHAR(100),
    receipt_image VARCHAR(255),
    notes TEXT,
    admin_notes TEXT,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    processed_by INT NULL,
    processed_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (processed_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_user_id (user_id),
    INDEX idx_status (status),
    INDEX idx_created_at (created_at)
);

-- Withdrawal requests table
CREATE TABLE withdrawal_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    payment_method VARCHAR(50) NOT NULL,
    payment_details TEXT NOT NULL,
    notes TEXT,
    admin_notes TEXT,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    processed_by INT NULL,
    processed_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (processed_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_user_id (user_id),
    INDEX idx_status (status),
    INDEX idx_created_at (created_at)
);

-- Site announcements table
CREATE TABLE announcements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    content TEXT NOT NULL,
    type ENUM('info', 'warning', 'success', 'danger') DEFAULT 'info',
    is_active BOOLEAN DEFAULT TRUE,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_is_active (is_active),
    INDEX idx_created_at (created_at)
);

-- Site settings table
CREATE TABLE settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT,
    description TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_setting_key (setting_key)
);

-- Insert default admin user (password: admin123)
INSERT INTO users (name, username, email, password, role, balance) VALUES 
('Administrator', 'admin', 'admin@betfootball.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 10000.00);

-- Insert sample matches with all betting options
INSERT INTO matches (home_team, away_team, league, match_date, home_odds, away_odds, draw_odds, over_25_odds, under_25_odds, btts_yes_odds, btts_no_odds, status) VALUES
('Manchester United', 'Liverpool', 'Premier League', '2024-02-01 15:00:00', 2.50, 2.80, 3.20, 1.85, 1.95, 1.75, 2.05, 'upcoming'),
('Barcelona', 'Real Madrid', 'La Liga', '2024-02-02 16:00:00', 2.10, 3.50, 3.00, 1.90, 1.90, 1.80, 2.00, 'upcoming'),
('Bayern Munich', 'Borussia Dortmund', 'Bundesliga', '2024-02-03 14:30:00', 1.90, 4.20, 3.80, 1.75, 2.05, 1.65, 2.20, 'upcoming'),
('Paris Saint-Germain', 'Marseille', 'Ligue 1', '2024-02-04 17:00:00', 1.70, 4.50, 4.00, 1.80, 2.00, 1.70, 2.10, 'upcoming'),
('Chelsea', 'Arsenal', 'Premier League', '2024-02-05 12:30:00', 2.80, 2.60, 3.10, 1.95, 1.85, 1.85, 1.95, 'upcoming'),
('Juventus', 'AC Milan', 'Serie A', '2024-02-06 15:45:00', 2.30, 3.20, 3.40, 1.90, 1.90, 1.75, 2.05, 'upcoming'),

-- Sample finished matches with results
('Manchester City', 'Tottenham', 'Premier League', '2024-01-25 16:00:00', 1.80, 4.00, 3.50, 1.85, 1.95, 1.80, 2.00, 2, 1, 3, TRUE, 'finished'),
('Atletico Madrid', 'Valencia', 'La Liga', '2024-01-24 18:00:00', 2.20, 3.10, 3.20, 1.90, 1.90, 1.75, 2.05, 1, 1, 2, TRUE, 'finished'),
('Inter Milan', 'Napoli', 'Serie A', '2024-01-23 15:00:00', 2.40, 2.90, 3.00, 1.85, 1.95, 1.70, 2.10, 0, 2, 2, FALSE, 'finished'),

-- Sample live match
('Leeds United', 'Brighton', 'Premier League', '2024-01-30 20:00:00', 2.60, 2.50, 3.30, 1.90, 1.90, 1.80, 2.00, 1, 0, 1, FALSE, 'live');

-- Insert default site settings
INSERT INTO settings (setting_key, setting_value, description) VALUES
('site_name', 'BetFootball Pro', 'Website name'),
('min_deposit', '10.00', 'Minimum deposit amount'),
('max_deposit', '10000.00', 'Maximum deposit amount'),
('min_withdrawal', '20.00', 'Minimum withdrawal amount'),
('max_withdrawal', '5000.00', 'Maximum withdrawal amount'),
('min_bet', '1.00', 'Minimum bet amount'),
('max_bet', '1000.00', 'Maximum bet amount'),
('new_user_bonus', '100.00', 'New user registration bonus'),
('maintenance_mode', '0', 'Maintenance mode (0=off, 1=on)');

-- Insert sample announcement
INSERT INTO announcements (title, content, type, created_by) VALUES
('Welcome to BetFootball Pro!', 'Join our exciting football betting platform and get $100 bonus on registration. Bet on your favorite teams with the best odds!', 'success', 1);

-- Create indexes for better performance
CREATE INDEX idx_users_created_at ON users(created_at);
CREATE INDEX idx_matches_home_away ON matches(home_team, away_team);
CREATE INDEX idx_bets_stake ON bets(stake);
CREATE INDEX idx_transactions_amount ON transactions(amount);

-- Create views for common queries
CREATE VIEW active_matches AS
SELECT * FROM matches 
WHERE status IN ('upcoming', 'live') AND betting_locked = FALSE
ORDER BY match_date ASC;

CREATE VIEW user_statistics AS
SELECT 
    u.id,
    u.name,
    u.username,
    u.email,
    u.balance,
    COUNT(b.id) as total_bets,
    SUM(b.stake) as total_wagered,
    SUM(CASE WHEN b.status = 'won' THEN b.actual_payout ELSE 0 END) as total_winnings,
    COUNT(CASE WHEN b.status = 'won' THEN 1 END) as bets_won,
    COUNT(CASE WHEN b.status = 'lost' THEN 1 END) as bets_lost,
    COUNT(CASE WHEN b.status = 'pending' THEN 1 END) as bets_pending
FROM users u
LEFT JOIN bets b ON u.id = b.user_id
WHERE u.role = 'user'
GROUP BY u.id;

-- Stored procedure to settle bets for a match
DELIMITER //
CREATE PROCEDURE SettleMatchBets(IN match_id INT)
BEGIN
    DECLARE match_home_score INT;
    DECLARE match_away_score INT;
    DECLARE match_total_goals INT;
    DECLARE match_btts BOOLEAN;
    DECLARE done INT DEFAULT FALSE;
    DECLARE bet_cursor CURSOR FOR 
        SELECT id, user_id, bet_type, selected_option, stake, odds, potential_payout 
        FROM bets 
        WHERE match_id = match_id AND status = 'pending';
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;
    
    -- Get match results
    SELECT home_score, away_score, total_goals, both_teams_scored
    INTO match_home_score, match_away_score, match_total_goals, match_btts
    FROM matches 
    WHERE id = match_id AND status = 'finished';
    
    -- Open cursor and process bets
    OPEN bet_cursor;
    
    bet_loop: LOOP
        DECLARE bet_id INT;
        DECLARE bet_user_id INT;
        DECLARE bet_type VARCHAR(20);
        DECLARE selected_option VARCHAR(20);
        DECLARE bet_stake DECIMAL(10,2);
        DECLARE bet_odds DECIMAL(5,2);
        DECLARE potential_payout DECIMAL(10,2);
        DECLARE is_winning_bet BOOLEAN DEFAULT FALSE;
        
        FETCH bet_cursor INTO bet_id, bet_user_id, bet_type, selected_option, bet_stake, bet_odds, potential_payout;
        
        IF done THEN
            LEAVE bet_loop;
        END IF;
        
        -- Determine if bet is winning
        CASE bet_type
            WHEN '1x2' THEN
                IF (selected_option = 'home' AND match_home_score > match_away_score) OR
                   (selected_option = 'away' AND match_away_score > match_home_score) OR
                   (selected_option = 'draw' AND match_home_score = match_away_score) THEN
                    SET is_winning_bet = TRUE;
                END IF;
            WHEN 'over_under' THEN
                IF (selected_option = 'over' AND match_total_goals > 2) OR
                   (selected_option = 'under' AND match_total_goals < 3) THEN
                    SET is_winning_bet = TRUE;
                END IF;
            WHEN 'btts' THEN
                IF (selected_option = 'yes' AND match_btts = TRUE) OR
                   (selected_option = 'no' AND match_btts = FALSE) THEN
                    SET is_winning_bet = TRUE;
                END IF;
        END CASE;
        
        -- Update bet status and user balance
        IF is_winning_bet THEN
            UPDATE bets SET status = 'won', actual_payout = potential_payout WHERE id = bet_id;
            UPDATE users SET balance = balance + potential_payout WHERE id = bet_user_id;
            
            -- Log transaction
            INSERT INTO transactions (user_id, type, amount, balance_before, balance_after, description, reference_id)
            SELECT bet_user_id, 'bet_won', potential_payout, 
                   balance - potential_payout, balance, 
                   CONCAT('Bet won: ', bet_type, ' - ', selected_option), bet_id
            FROM users WHERE id = bet_user_id;
        ELSE
            UPDATE bets SET status = 'lost' WHERE id = bet_id;
        END IF;
        
    END LOOP;
    
    CLOSE bet_cursor;
    
    -- Update match betting status
    UPDATE matches SET betting_locked = TRUE WHERE id = match_id;
    
END //
DELIMITER ;

-- Trigger to automatically calculate potential payout and log bet transaction
DELIMITER //
CREATE TRIGGER after_bet_insert 
AFTER INSERT ON bets
FOR EACH ROW 
BEGIN
    DECLARE user_balance_before DECIMAL(10,2);
    DECLARE user_balance_after DECIMAL(10,2);
    
    -- Get user balance before bet
    SELECT balance INTO user_balance_before FROM users WHERE id = NEW.user_id;
    
    -- Deduct stake from user balance
    UPDATE users SET balance = balance - NEW.stake WHERE id = NEW.user_id;
    
    -- Get balance after deduction
    SELECT balance INTO user_balance_after FROM users WHERE id = NEW.user_id;
    
    -- Log transaction
    INSERT INTO transactions (user_id, type, amount, balance_before, balance_after, description, reference_id)
    VALUES (NEW.user_id, 'bet_placed', NEW.stake, user_balance_before, user_balance_after, 
            CONCAT('Bet placed: ', NEW.bet_type, ' - ', NEW.selected_option), NEW.id);
END //
DELIMITER ;

-- Trigger to calculate potential payout before insert
DELIMITER //
CREATE TRIGGER before_bet_insert 
BEFORE INSERT ON bets
FOR EACH ROW 
BEGIN
    SET NEW.potential_payout = NEW.stake * NEW.odds;
END //
DELIMITER ;