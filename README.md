# ⚽ Football Betting Platform

A full-featured football betting platform built with **pure PHP** (no frameworks) and **MySQL**, designed for easy deployment on **shared hosting** environments with **cPanel**.

![Platform Preview](https://img.shields.io/badge/PHP-7.4%2B-blue) ![MySQL](https://img.shields.io/badge/MySQL-5.7%2B-orange) ![Bootstrap](https://img.shields.io/badge/Bootstrap-5.3-purple) ![License](https://img.shields.io/badge/License-MIT-green)

## ✨ Features

### 🏆 Core Functionality
- **User Registration & Authentication** with secure password hashing
- **Live Match Display** with real-time updates
- **Betting System** with three bet types (Home Win, Draw, Away Win)
- **Automatic Bet Settlement** based on match results
- **User Dashboard** with betting statistics and history
- **Admin Panel** for complete platform management
- **Balance Management** with automatic win calculations
- **Responsive Design** with Bootstrap 5

### 👤 User Features
- User registration with $100 starting bonus
- Secure login/logout system
- Personal dashboard with betting stats
- Real-time balance tracking
- Betting history and pending bets
- Profile management
- Mobile-responsive interface

### 🛡️ Admin Features
- Complete match management (add, edit, delete)
- User account management
- Bet monitoring and settlement
- Platform statistics and analytics
- Balance adjustments
- Real-time betting activity monitoring

### 🎨 Design & UX
- Modern, clean interface with Bootstrap 5
- Responsive design for all devices
- Interactive betting modals
- Real-time countdown timers
- Live match indicators
- Intuitive navigation

## 🏗️ Architecture

### MVC Structure
```
football-betting-platform/
├── public/              # Entry point & assets
│   ├── index.php       # Router & bootstrap
│   └── .htaccess       # URL rewriting
├── config/             # Database configuration
│   └── database.php    # DB connection class
├── models/             # Data layer
│   ├── User.php        # User operations
│   ├── Match.php       # Match operations
│   └── Bet.php         # Betting operations
├── controllers/        # Business logic
│   ├── AuthController.php      # Authentication
│   ├── AdminController.php     # Admin operations
│   └── MatchController.php     # Match & betting
├── views/              # Presentation layer
│   ├── layout/         # Header & footer
│   ├── auth/           # Login & registration
│   ├── admin/          # Admin panel
│   └── *.php           # Page templates
├── core/               # Core system
│   ├── Router.php      # URL routing
│   ├── Session.php     # Session management
│   └── Helpers.php     # Utility functions
└── database_schema.sql # Database structure
```

## 🚀 Quick Start

### Requirements
- **PHP 7.4+** (8.0+ recommended)
- **MySQL 5.7+** (8.0+ recommended)
- **Apache** with mod_rewrite
- **cPanel** hosting (or similar)

### Installation

1. **Download the project**
   ```bash
   git clone https://github.com/your-repo/football-betting-platform.git
   cd football-betting-platform
   ```

2. **Upload to your hosting**
   - Upload all files except `public/` contents to your hosting root
   - Upload `public/` contents to your `public_html` folder

3. **Create database**
   - Create MySQL database via cPanel
   - Import `database_schema.sql`

4. **Configure database**
   ```php
   // Edit config/database.php
   private $host = 'localhost';
   private $dbname = 'your_database_name';
   private $username = 'your_username';
   private $password = 'your_password';
   ```

5. **Test installation**
   - Visit your domain
   - Create user account or login as admin

### Default Admin Account
- **Email**: `admin@betfootball.com`
- **Password**: `admin123` ⚠️ **Change immediately!**

## 📱 Screenshots

### Homepage
- Hero section with feature highlights
- Live matches with real-time scores
- Upcoming matches with betting odds
- Recent winners showcase

### User Dashboard
- Personal betting statistics
- Recent bets history
- Quick actions panel
- Upcoming matches preview

### Admin Panel
- Platform overview with key metrics
- User management interface
- Match management system
- Betting activity monitoring

## 🔧 Technology Stack

### Backend
- **Pure PHP** (no frameworks for simplicity)
- **MySQL** with PDO for security
- **MVC Architecture** for organization
- **Session Management** for authentication
- **Password Hashing** with PHP's built-in functions

### Frontend
- **Bootstrap 5** for responsive design
- **Bootstrap Icons** for iconography
- **Vanilla JavaScript** for interactivity
- **CSS3** with custom styling
- **HTML5** semantic markup

### Security
- **SQL Injection** protection via prepared statements
- **XSS Protection** with input sanitization
- **CSRF Protection** with session tokens
- **Password Security** with bcrypt hashing
- **Session Security** with regeneration and timeouts

## 🔒 Security Features

- **Input Sanitization**: All user inputs are cleaned and validated
- **Prepared Statements**: SQL injection protection
- **Password Hashing**: Secure bcrypt password storage
- **Session Management**: Secure session handling with regeneration
- **Access Control**: Role-based permissions (user/admin)
- **Error Handling**: Secure error reporting without information disclosure

## 📊 Database Schema

### Users Table
- User authentication and profile data
- Balance tracking
- Role management (user/admin)

### Matches Table
- Match information (teams, league, date)
- Betting odds for all outcomes
- Match status (upcoming/live/finished)
- Score tracking

### Bets Table
- User betting records
- Bet amounts and odds
- Automatic win calculations
- Bet settlement status

## 🎯 Admin Functions

### Match Management
- Add new matches with custom odds
- Edit existing match details
- Update match results and scores
- Delete matches (with bet protection)

### User Management
- View all user accounts
- Monitor user balances
- Adjust account balances
- Track user activity

### Betting Operations
- View all platform bets
- Settle pending bets
- Monitor betting patterns
- Generate platform statistics

## 🌐 Deployment

### Shared Hosting (cPanel)
Perfect for shared hosting environments:
- Simple PHP deployment
- No server configuration required
- Compatible with most hosting providers
- Easy database setup via phpMyAdmin

### VPS/Dedicated Servers
Also works on VPS/dedicated servers:
- Full control over environment
- Can optimize for performance
- Custom PHP/MySQL configurations
- SSL certificate installation

See [DEPLOYMENT_GUIDE.md](DEPLOYMENT_GUIDE.md) for detailed instructions.

## 🔄 Future Enhancements

### Planned Features
- **Multi-language Support**: International platform support
- **Payment Integration**: Real money transactions
- **Live Streaming**: Embedded match streams
- **Mobile App**: Native iOS/Android apps
- **Advanced Statistics**: Detailed analytics and reporting
- **Bonus System**: Promotional codes and rewards

### API Development
- RESTful API for mobile apps
- Third-party integrations
- Webhook support for real-time updates

## 🤝 Contributing

We welcome contributions! Please read our contributing guidelines:

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add tests if applicable
5. Submit a pull request

### Development Setup
```bash
# Clone the repository
git clone https://github.com/your-repo/football-betting-platform.git

# Set up local development environment
# (Instructions for local setup)
```

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## ⚠️ Legal Disclaimer

This platform is provided for **educational and demonstration purposes only**. 

**Important Legal Notes**:
- Online gambling regulations vary by jurisdiction
- Ensure compliance with local laws before operating
- This software does not include payment processing
- Users are responsible for legal compliance
- Not intended for commercial gambling operations

## 📞 Support

### Getting Help
- **Documentation**: Comprehensive guides included
- **Issue Tracker**: GitHub issues for bug reports
- **Community**: Discussions and feature requests

### Common Issues
- Check [DEPLOYMENT_GUIDE.md](DEPLOYMENT_GUIDE.md) for troubleshooting
- Verify PHP/MySQL versions and extensions
- Ensure proper file permissions
- Check error logs for detailed information

## 🏆 Acknowledgments

- **Bootstrap Team** for the excellent CSS framework
- **PHP Community** for robust documentation
- **MySQL Team** for reliable database engine
- **Open Source Community** for inspiration and tools

---

## 📈 Stats

- **Lines of Code**: ~2,500+ lines
- **Files**: 25+ files organized in MVC structure
- **Features**: 15+ major features implemented
- **Security**: Multiple security layers implemented
- **Responsive**: Works on all device sizes

---

**Made with ❤️ for the football betting community**

⭐ **Star this repository if you found it helpful!**