<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Football Betting Platform' ?></title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Custom CSS -->
    <style>
        :root {
            --primary-color: #1a5d1a;
            --secondary-color: #2d8f2d;
            --accent-color: #ffc107;
            --success-color: #28a745;
            --danger-color: #dc3545;
        }
        
        body {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            min-height: 100vh;
        }
        
        .navbar-brand {
            font-weight: bold;
            font-size: 1.5rem;
        }
        
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }
        
        .card:hover {
            transform: translateY(-5px);
        }
        
        .btn-primary {
            background: linear-gradient(45deg, var(--primary-color), var(--secondary-color));
            border: none;
            border-radius: 25px;
            padding: 8px 20px;
        }
        
        .btn-primary:hover {
            background: linear-gradient(45deg, var(--secondary-color), var(--primary-color));
        }
        
        .match-card {
            background: white;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .odds-btn {
            border-radius: 10px;
            font-weight: bold;
            transition: all 0.3s ease;
        }
        
        .odds-btn:hover {
            transform: scale(1.05);
        }
        
        .balance-display {
            background: linear-gradient(45deg, var(--accent-color), #ffeb3b);
            color: #000;
            border-radius: 20px;
            padding: 5px 15px;
            font-weight: bold;
        }
        
        .status-badge {
            border-radius: 15px;
            padding: 5px 12px;
            font-size: 0.85rem;
            font-weight: bold;
        }
        
        .footer {
            background: var(--primary-color);
            color: white;
            margin-top: 50px;
        }
        
        .hero-section {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 60px 0;
            margin-bottom: 40px;
        }
        
        .hero-section h1 {
            font-size: 3rem;
            font-weight: bold;
            margin-bottom: 20px;
        }
        
        @media (max-width: 768px) {
            .hero-section h1 {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark" style="background: var(--primary-color);">
        <div class="container">
            <a class="navbar-brand" href="/">
                <i class="bi bi-trophy-fill me-2"></i>
                BetFootball
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="/"><i class="bi bi-house-fill me-1"></i>Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/matches"><i class="bi bi-calendar-event me-1"></i>Matches</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/live"><i class="bi bi-broadcast me-1"></i>Live</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/results"><i class="bi bi-trophy me-1"></i>Results</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/leagues"><i class="bi bi-grid-3x3-gap me-1"></i>Leagues</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/winners"><i class="bi bi-star-fill me-1"></i>Winners</a>
                    </li>
                </ul>
                
                <ul class="navbar-nav">
                    <?php if (Session::isLoggedIn()): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                                <i class="bi bi-person-circle me-1"></i>
                                <?= Session::get('username') ?>
                                <span class="balance-display ms-2">
                                    <?php
                                    require_once 'models/User.php';
                                    $userModel = new User();
                                    $balance = $userModel->getBalance(Session::getUserId());
                                    echo Helpers::formatMoney($balance);
                                    ?>
                                </span>
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="/dashboard"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a></li>
                                <li><a class="dropdown-item" href="/my-bets"><i class="bi bi-receipt me-2"></i>My Bets</a></li>
                                <li><a class="dropdown-item" href="/profile"><i class="bi bi-gear me-2"></i>Profile</a></li>
                                <?php if (Session::isAdmin()): ?>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="/admin"><i class="bi bi-shield-lock me-2"></i>Admin Panel</a></li>
                                <?php endif; ?>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="/logout"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="/login"><i class="bi bi-box-arrow-in-right me-1"></i>Login</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/register"><i class="bi bi-person-plus me-1"></i>Register</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
    
    <!-- Flash Messages -->
    <?php if (Session::hasFlash('success')): ?>
        <div class="alert alert-success alert-dismissible fade show m-3" role="alert">
            <i class="bi bi-check-circle me-2"></i>
            <?= Session::getFlash('success') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    
    <?php if (Session::hasFlash('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show m-3" role="alert">
            <i class="bi bi-exclamation-triangle me-2"></i>
            <?= Session::getFlash('error') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    
    <?php if (Session::hasFlash('info')): ?>
        <div class="alert alert-info alert-dismissible fade show m-3" role="alert">
            <i class="bi bi-info-circle me-2"></i>
            <?= Session::getFlash('info') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>