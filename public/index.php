<?php

// Include core files
require_once '../core/Router.php';
require_once '../core/Session.php';
require_once '../core/Helpers.php';
require_once '../config/database.php';

// Start session
Session::start();

// Initialize router
$router = new Router();

// Public routes
$router->addRoute('GET', '/', 'MatchController', 'index');
$router->addRoute('GET', '/home', 'MatchController', 'index');
$router->addRoute('GET', '/matches', 'MatchController', 'matches');
$router->addRoute('GET', '/match/{id}', 'MatchController', 'showMatch');
$router->addRoute('GET', '/results', 'MatchController', 'results');
$router->addRoute('GET', '/live', 'MatchController', 'live');
$router->addRoute('GET', '/leagues', 'MatchController', 'leagues');
$router->addRoute('GET', '/league/{name}', 'MatchController', 'showLeague');
$router->addRoute('GET', '/winners', 'MatchController', 'winners');

// Auth routes
$router->addRoute('GET', '/login', 'AuthController', 'showLogin');
$router->addRoute('POST', '/login', 'AuthController', 'login');
$router->addRoute('GET', '/register', 'AuthController', 'showRegister');
$router->addRoute('POST', '/register', 'AuthController', 'register');
$router->addRoute('GET', '/logout', 'AuthController', 'logout');
$router->addRoute('GET', '/profile', 'AuthController', 'showProfile');
$router->addRoute('POST', '/profile', 'AuthController', 'updateProfile');
$router->addRoute('POST', '/change-password', 'AuthController', 'changePassword');

// User routes
$router->addRoute('GET', '/dashboard', 'MatchController', 'dashboard');
$router->addRoute('GET', '/my-bets', 'MatchController', 'myBets');
$router->addRoute('POST', '/place-bet', 'MatchController', 'placeBet');

// Admin routes
$router->addRoute('GET', '/admin', 'AdminController', 'dashboard');
$router->addRoute('GET', '/admin/dashboard', 'AdminController', 'dashboard');
$router->addRoute('GET', '/admin/users', 'AdminController', 'users');
$router->addRoute('GET', '/admin/matches', 'AdminController', 'matches');
$router->addRoute('GET', '/admin/bets', 'AdminController', 'bets');
$router->addRoute('GET', '/admin/add-match', 'AdminController', 'showAddMatch');
$router->addRoute('POST', '/admin/add-match', 'AdminController', 'addMatch');
$router->addRoute('GET', '/admin/edit-match', 'AdminController', 'showEditMatch');
$router->addRoute('POST', '/admin/edit-match', 'AdminController', 'editMatch');
$router->addRoute('POST', '/admin/update-result', 'AdminController', 'updateMatchResult');
$router->addRoute('POST', '/admin/delete-match', 'AdminController', 'deleteMatch');
$router->addRoute('POST', '/admin/adjust-balance', 'AdminController', 'adjustUserBalance');
$router->addRoute('POST', '/admin/settle-bets', 'AdminController', 'settlePendingBets');

// Dispatch the request
$router->dispatch();