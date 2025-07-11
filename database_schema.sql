-- Football Betting Platform Database Schema
-- Run this script to create the database and tables

CREATE DATABASE IF NOT EXISTS football_betting CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE football_betting;

-- Users table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('user', 'admin') DEFAULT 'user',
    balance DECIMAL(10,2) DEFAULT 0.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_username (username),
    INDEX idx_role (role)
);

-- Matches table
CREATE TABLE matches (
    id INT AUTO_INCREMENT PRIMARY KEY,
    home_team VARCHAR(100) NOT NULL,
    away_team VARCHAR(100) NOT NULL,
    league VARCHAR(100) NOT NULL,
    match_date DATETIME NOT NULL,
    home_odds DECIMAL(5,2) NOT NULL,
    away_odds DECIMAL(5,2) NOT NULL,
    draw_odds DECIMAL(5,2) NOT NULL,
    home_score INT NULL,
    away_score INT NULL,
    status ENUM('upcoming', 'live', 'finished', 'cancelled') DEFAULT 'upcoming',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_match_date (match_date),
    INDEX idx_status (status),
    INDEX idx_league (league)
);

-- Bets table
CREATE TABLE bets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    match_id INT NOT NULL,
    bet_type ENUM('home', 'away', 'draw') NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    odds DECIMAL(5,2) NOT NULL,
    potential_win DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'won', 'lost') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (match_id) REFERENCES matches(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_match_id (match_id),
    INDEX idx_status (status),
    INDEX idx_created_at (created_at)
);

-- Insert default admin user (password: admin123)
INSERT INTO users (username, email, password, role, balance) VALUES 
('admin', 'admin@betfootball.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 1000.00);

-- Insert sample matches for demonstration
INSERT INTO matches (home_team, away_team, league, match_date, home_odds, away_odds, draw_odds, status) VALUES
('Manchester United', 'Liverpool', 'Premier League', '2024-02-01 15:00:00', 2.50, 2.80, 3.20, 'upcoming'),
('Barcelona', 'Real Madrid', 'La Liga', '2024-02-02 16:00:00', 2.10, 3.50, 3.00, 'upcoming'),
('Bayern Munich', 'Borussia Dortmund', 'Bundesliga', '2024-02-03 14:30:00', 1.90, 4.20, 3.80, 'upcoming'),
('Paris Saint-Germain', 'Marseille', 'Ligue 1', '2024-02-04 17:00:00', 1.70, 4.50, 4.00, 'upcoming'),
('Chelsea', 'Arsenal', 'Premier League', '2024-02-05 12:30:00', 2.80, 2.60, 3.10, 'upcoming'),
('Juventus', 'AC Milan', 'Serie A', '2024-02-06 15:45:00', 2.30, 3.20, 3.40, 'upcoming'),

-- Sample finished matches
('Manchester City', 'Tottenham', 'Premier League', '2024-01-25 16:00:00', 1.80, 4.00, 3.50, 2, 1, 'finished'),
('Atletico Madrid', 'Valencia', 'La Liga', '2024-01-24 18:00:00', 2.20, 3.10, 3.20, 1, 1, 'finished'),
('Inter Milan', 'Napoli', 'Serie A', '2024-01-23 15:00:00', 2.40, 2.90, 3.00, 0, 2, 'finished'),

-- Sample live match
('Leeds United', 'Brighton', 'Premier League', '2024-01-30 20:00:00', 2.60, 2.50, 3.30, 1, 0, 'live');

-- Create indexes for better performance
CREATE INDEX idx_users_created_at ON users(created_at);
CREATE INDEX idx_matches_home_away ON matches(home_team, away_team);
CREATE INDEX idx_bets_amount ON bets(amount);
CREATE INDEX idx_bets_user_match ON bets(user_id, match_id);

-- Create views for common queries
CREATE VIEW active_matches AS
SELECT * FROM matches 
WHERE status IN ('upcoming', 'live') 
ORDER BY match_date ASC;

CREATE VIEW match_stats AS
SELECT 
    m.*,
    COUNT(b.id) as total_bets,
    SUM(b.amount) as total_wagered,
    COUNT(CASE WHEN b.bet_type = 'home' THEN 1 END) as home_bets,
    COUNT(CASE WHEN b.bet_type = 'away' THEN 1 END) as away_bets,
    COUNT(CASE WHEN b.bet_type = 'draw' THEN 1 END) as draw_bets
FROM matches m
LEFT JOIN bets b ON m.id = b.match_id
GROUP BY m.id;

CREATE VIEW user_statistics AS
SELECT 
    u.id,
    u.username,
    u.email,
    u.balance,
    COUNT(b.id) as total_bets,
    SUM(b.amount) as total_wagered,
    SUM(CASE WHEN b.status = 'won' THEN b.potential_win ELSE 0 END) as total_winnings,
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
    DECLARE match_result VARCHAR(10);
    DECLARE home_score INT;
    DECLARE away_score INT;
    
    -- Get match result
    SELECT home_score, away_score INTO home_score, away_score
    FROM matches WHERE id = match_id AND status = 'finished';
    
    -- Determine result
    IF home_score > away_score THEN
        SET match_result = 'home';
    ELSEIF away_score > home_score THEN
        SET match_result = 'away';
    ELSE
        SET match_result = 'draw';
    END IF;
    
    -- Update winning bets
    UPDATE bets SET status = 'won' 
    WHERE match_id = match_id AND bet_type = match_result AND status = 'pending';
    
    -- Update losing bets
    UPDATE bets SET status = 'lost' 
    WHERE match_id = match_id AND bet_type != match_result AND status = 'pending';
    
    -- Update user balances for winning bets
    UPDATE users u 
    JOIN bets b ON u.id = b.user_id 
    SET u.balance = u.balance + b.potential_win
    WHERE b.match_id = match_id AND b.status = 'won';
    
END //
DELIMITER ;

-- Trigger to automatically calculate potential win
DELIMITER //
CREATE TRIGGER calculate_potential_win 
BEFORE INSERT ON bets
FOR EACH ROW 
BEGIN
    SET NEW.potential_win = NEW.amount * NEW.odds;
END //
DELIMITER ;

-- Create admin notification system (optional)
CREATE TABLE notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    type VARCHAR(50) NOT NULL,
    title VARCHAR(200) NOT NULL,
    message TEXT NOT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_read (user_id, is_read)
);