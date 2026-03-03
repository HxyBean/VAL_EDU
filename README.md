# VAL_EDU - Educational Platform

A comprehensive web-based educational management system designed for managing tutoring relationships, courses, payments, and student progress. Built with PHP, MySQL, and vanilla JavaScript for a robust and scalable learning platform.

## 📋 Table of Contents

- [Features](#features)
- [Architecture](#architecture)
- [Tech Stack](#tech-stack)
- [Installation](#installation)
- [Configuration](#configuration)
- [Project Structure](#project-structure)
- [Usage](#usage)
- [API Documentation](#api-documentation)
- [Database Schema](#database-schema)
- [User Roles](#user-roles)
- [Testing](#testing)
- [Documentation](#documentation)
- [Troubleshooting](#troubleshooting)
- [Contributing](#contributing)

## ✨ Features

### Core Functionality

- **Multi-Role User System** - Support for Admin, Tutor, Student, and Parent roles
- **Course Management** - Create, update, and manage courses with detailed scheduling
- **Payment System** - Track payments, generate invoices, and manage financial records
- **Calendar Integration** - Visual calendar for scheduling and event management
- **Student Dashboard** - Personalized student interface with course information
- **Parent Portal** - Parent access to student progress and course information
- **Tutor Management** - Tutor profiles, scheduling, and course assignments
- **Authentication** - Secure login and session management
- **Time Tracking** - Precise time tracking for sessions and courses
- **Data Validation** - Comprehensive input validation and error handling

### Advanced Features

- **Role-Based Access Control** - Fine-grained permissions for each user role
- **API Endpoints** - RESTful API for programmatic access
- **Responsive Design** - Works on desktop and mobile devices
- **UTF-8 Support** - Full support for international characters (including Vietnamese)
- **Error Logging** - Comprehensive error tracking and debugging

## 🏗️ Architecture

### Design Pattern: MVC (Model-View-Controller)

```
App Layer:
  ├── Controllers (handle requests and business logic)
  ├── Models (handle data access and queries)
  └── Views (handle presentation and UI)

Supporting Layer:
  ├── Base Classes (BaseController, BaseModel, Database)
  ├── Routing (URL to Controller mapping)
  └── API Routes (REST endpoints)
```

### Request Flow

```
1. User Request
   ↓
2. index.php (routing)
   ↓
3. Controller (authentication & validation)
   ↓
4. Model (database queries)
   ↓
5. View (HTML rendering) OR JSON Response
```

## 🛠️ Tech Stack

### Backend
- **Language:** PHP 7.4+
- **Database:** MySQL 5.7+
- **Architecture:** Custom MVC Framework
- **Session Management:** PHP Sessions
- **Encryption:** bcrypt for passwords

### Frontend
- **Markup:** HTML5
- **Styling:** CSS3
- **Scripts:** Vanilla JavaScript (no dependencies)
- **Calendar:** Custom JavaScript calendar implementation
- **Forms:** Dynamic form handling with AJAX

### Development Tools
- **Version Control:** Git
- **Server:** Apache (XAMPP recommended)
- **Database Management:** MySQL

## 📦 Installation

### Prerequisites

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache with mod_rewrite enabled
- XAMPP (recommended for local development)
- Git (for cloning the repository)

### Step 1: Clone the Repository

```bash
git clone https://github.com/anomalyco/VAL_EDU.git
cd VAL_EDU
```

### Step 2: Set Up Database

1. **Using XAMPP:**
   - Start Apache and MySQL from XAMPP Control Panel
   - Open phpMyAdmin: http://localhost/phpmyadmin

2. **Import Database Schema:**
   ```bash
   # Option 1: Via phpMyAdmin
   # 1. Create new database: ValEduDatabase
   # 2. Import webapp/ValEduDatabase.sql
   
   # Option 2: Via Command Line
   mysql -u root < webapp/ValEduDatabase.sql
   ```

### Step 3: Configure Application

1. **Database Configuration:**
   - Open `webapp/Base/Database.php`
   - Default XAMPP configuration is already set:
     ```php
     private $host = '127.0.0.1';
     private $database = 'ValEduDatabase';
     private $username = 'root';
     private $password = ''; // Empty for XAMPP
     ```

2. **Web Server Configuration:**
   - Place project in XAMPP htdocs: `C:\xampp\htdocs\VAL_EDU`
   - OR configure your Apache DocumentRoot to point to project root

### Step 4: Verify Installation

Access the application:
```
http://localhost/VAL_EDU/webapp/
```

### Step 5: Create Admin User (if needed)

```bash
# Generate password hash
php webapp/generate_password.php

# Then manually insert into users table in phpMyAdmin
# Or use the admin registration interface
```

## ⚙️ Configuration

### Database Configuration

Edit `webapp/Base/Database.php`:

```php
private $host = '127.0.0.1';      // Database host
private $port = 3306;              // MySQL port
private $database = 'ValEduDatabase'; // Database name
private $username = 'root';        // MySQL username
private $password = '';            // MySQL password
```

### Session Configuration

Default session configuration in `webapp/index.php`:
```php
session_start();
```

To modify session settings, update PHP configuration or add to index.php:
```php
ini_set('session.gc_maxlifetime', 3600); // 1 hour
```


## 🎓 Configuration Management - Learning Notes

### Current Approach: Hard-coded Configuration

This project uses hard-coded configuration values in `Base/Database.php` for **learning purposes only**. This demonstrates how basic configuration works but is **not recommended for production use**.

**Current Method:**
```php
private $host = '127.0.0.1';
private $database = 'ValEduDatabase';
private $username = 'root';
private $password = ''; // Empty for XAMPP default
```

### Why This Approach Works for Learning

✅ **Simple to understand** - Easy to see how configuration values are used  
✅ **No dependencies** - No external libraries needed  
✅ **Clear flow** - Configuration → Connection → Usage  
✅ **Low barrier to entry** - Beginners can quickly modify settings

### Production Best Practice: Environment Variables (.env Files)

For production applications, you would use a `.env` file approach instead:

**Why `.env` files are better:**
- **Security** - Sensitive data never committed to Git
- **Environment separation** - Different settings for dev/test/production
- **Deployment flexibility** - Same code runs anywhere
- **Team collaboration** - Easy to share `.env.example` template

**How it would look:**
```bash
# .env file (never committed to Git)
DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=ValEduDatabase
DB_USER=root
DB_PASSWORD=
DB_CHARSET=utf8mb4
```

**Code would use:**
```php
private $host = getenv('DB_HOST');
private $port = getenv('DB_PORT');
private $database = getenv('DB_NAME');
private $username = getenv('DB_USER');
private $password = getenv('DB_PASSWORD');
```

**Or with a library like `phpdotenv`:**
```php
require_once 'vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

private $host = $_ENV['DB_HOST'];
private $database = $_ENV['DB_NAME'];
// ... etc
```

### Key Differences: Learning vs. Production

| Aspect | Learning Project (Current) | Production Project |
|--------|---------------------------|-------------------|
| Configuration | Hard-coded in PHP files | Environment variables (.env) |
| Credentials | Visible in code | Hidden from version control |
| Flexibility | Requires code changes | Change .env to switch environments |
| Security | Not secure | Secure with proper setup |
| Dependencies | None needed | May use library like `phpdotenv` |
| Team Setup | Copy code, modify Database.php | Copy code, create .env file |
| Deployment | Requires code modification | No code changes needed |
| Containerization | Difficult | Easy with Docker |

### Educational Path Forward

As you progress with this project:

1. **Now (Learning)**: Understand configuration in `Base/Database.php`
2. **Intermediate**: Learn about environment variables and `.env` files
3. **Advanced**: Implement `.env` file support using `phpdotenv` or similar
4. **Professional**: Implement full configuration management with multiple environments

### Example: Converting to `.env` (Future Enhancement)

If you want to implement `.env` support later, here's a basic example:

**1. Install dependency:**
```bash
composer require vlucas/phpdotenv
```

**2. Create `.env` file:**
```
DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=ValEduDatabase
DB_USER=root
DB_PASSWORD=
DB_CHARSET=utf8mb4
```

**3. Create `.env.example` for Git:**
```
DB_HOST=localhost
DB_PORT=3306
DB_NAME=ValEduDatabase
DB_USER=root
DB_PASSWORD=
DB_CHARSET=utf8mb4
```

**4. Add `.env` to `.gitignore`:**
```
.env
.env.local
.env.*.php
```

**5. Update `Base/Database.php`:**
```php
<?php
require_once dirname(__DIR__) . '/../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__) . '/..');
$dotenv->load();

class Database {
    private static $instance = null;
    private $connection;
    
    private $host = '';
    private $port = 3306;
    private $database = '';
    private $username = '';
    private $password = '';
    
    private function __construct() {
        // Load from environment
        $this->host = $_ENV['DB_HOST'] ?? '127.0.0.1';
        $this->port = $_ENV['DB_PORT'] ?? 3306;
        $this->database = $_ENV['DB_NAME'] ?? 'ValEduDatabase';
        $this->username = $_ENV['DB_USER'] ?? 'root';
        $this->password = $_ENV['DB_PASSWORD'] ?? '';
        
        // ... rest of configuration
    }
}
```

### Summary

This project uses **hard-coded configuration for learning purposes**. Understanding how this works is valuable, but remember that **production applications should always use environment variables** to keep sensitive data secure and enable flexible deployments.

### Charset Configuration

UTF-8 is configured in `Base/Database.php`:
```php
$this->connection->set_charset("utf8mb4");
```

## 📁 Project Structure

```
VAL_EDU/
├── README.md                          # This file
├── TESTING_REFACTOR_SUMMARY.md        # Test organization guide
│
├── webapp/                            # Application root
│   ├── index.php                      # Main routing file
│   ├── .htaccess                      # Apache rewrite rules
│   │
│   ├── Base/                          # Base framework classes
│   │   ├── Database.php               # Database connection (Singleton)
│   │   ├── BaseController.php         # Base controller class
│   │   └── BaseModel.php              # Base model class
│   │
│   ├── Controller/                    # Application controllers
│   │   ├── HomeController.php
│   │   ├── AuthController.php         # Authentication
│   │   ├── AdminController.php        # Admin functions
│   │   ├── StudentController.php      # Student functions
│   │   ├── TutorController.php        # Tutor functions
│   │   └── ParentController.php       # Parent functions
│   │
│   ├── Model/                         # Data models
│   │   ├── UserModel.php              # User operations
│   │   ├── AdminModel.php             # Admin data
│   │   ├── StudentModel.php           # Student data
│   │   ├── TutorModel.php             # Tutor data
│   │   ├── ParentModel.php            # Parent data
│   │   └── HomeModel.php              # Home page data
│   │
│   ├── View/                          # HTML templates
│   │   ├── Auth/                      # Login/Register views
│   │   ├── Admin/                     # Admin panel views
│   │   ├── Student/                   # Student views
│   │   ├── Tutor/                     # Tutor views
│   │   ├── Parent/                    # Parent views
│   │   ├── Home/                      # Home page views
│   │   ├── Partial/                   # Reusable components
│   │   └── images/                    # Static images
│   │
│   ├── api/                           # API endpoints
│   │   └── student/                   # Student API routes
│   │
│   ├── routes/                        # URL routing
│   │   └── student.php                # Student routes
│   │
│   ├── tests/                         # Test suite (organized)
│   │   ├── unit/                      # Unit tests
│   │   ├── integration/               # Integration tests
│   │   ├── e2e/                       # End-to-end tests
│   │   ├── fixtures/                  # Test data
│   │   ├── scripts/                   # Utility scripts
│   │   └── archived/                  # Temporary debug files
│   │
│   ├── ValEduDatabase.sql             # Database schema
│   ├── PAYMENT_SYSTEM_DOCUMENTATION.md # Payment details
│   ├── USERNAME_ROUTING.md            # Routing reference
│   │
│   └── Utility Scripts                # Helper scripts
│       ├── generate_password.php      # Password generation
│       ├── create_sample_data.php     # Sample data generator
│       └── create_sample_payments.php # Sample payment data
│
└── .gitignore                         # Git ignore rules
```

## 🚀 Usage

### Access the Application

1. **Start XAMPP:**
   - Open XAMPP Control Panel
   - Start Apache and MySQL services

2. **Open in Browser:**
   ```
   http://localhost/VAL_EDU/webapp/
   ```

3. **Login:**
   - Use credentials from the database
   - Default admin can be created using `generate_password.php`

### Main Application URLs

| URL | Purpose |
|-----|---------|
| `/` | Home page |
| `/admin` | Admin dashboard |
| `/student` | Student portal |
| `/tutor` | Tutor dashboard |
| `/parent` | Parent portal |
| `/auth/login` | Login page |
| `/auth/register` | Registration page |
| `/auth/logout` | Logout |

## 📡 API Documentation

### Authentication API

#### Login
```
POST /api/student/auth/login
Content-Type: application/json

{
  "username": "user@example.com",
  "password": "password123"
}

Response: { "success": true, "message": "Login successful", "user": {...} }
```

#### Logout
```
POST /api/student/auth/logout
Response: { "success": true, "message": "Logout successful" }
```

### Student API Endpoints

#### Get Student Profile
```
GET /api/student/profile
Authorization: Bearer {token}
Response: { "id": 1, "name": "John Doe", "email": "john@example.com", ... }
```

#### Get Student Courses
```
GET /api/student/courses
Response: [ { "id": 1, "name": "Math 101", "tutor": "Jane Smith" }, ... ]
```

#### Get Student Schedule
```
GET /api/student/schedule
Response: [ { "date": "2024-01-15", "time": "10:00", "course": "Math 101" }, ... ]
```

#### Get Payments
```
GET /api/student/payments
Response: [ { "id": 1, "amount": 100, "date": "2024-01-15", "status": "Paid" }, ... ]
```

### Course API

#### Get All Courses
```
GET /api/courses
Response: [ { "id": 1, "name": "Math 101", "tutor": "Jane Smith", "price": 100 }, ... ]
```

#### Enroll in Course
```
POST /api/student/courses/enroll
Content-Type: application/json

{ "course_id": 1 }

Response: { "success": true, "message": "Enrolled successfully" }
```

## 🗄️ Database Schema

### Tables Overview

| Table | Purpose |
|-------|---------|
| `users` | All user accounts (admin, tutor, student, parent) |
| `students` | Student profiles and information |
| `tutors` | Tutor profiles and rates |
| `courses` | Course definitions |
| `enrollments` | Student course enrollments |
| `payments` | Payment records |
| `sessions` | Student-tutor sessions |

### Users Table
```sql
CREATE TABLE users (
  id INT PRIMARY KEY AUTO_INCREMENT,
  username VARCHAR(255) UNIQUE NOT NULL,
  email VARCHAR(255) UNIQUE NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('admin', 'tutor', 'student', 'parent') NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

### Students Table
```sql
CREATE TABLE students (
  id INT PRIMARY KEY AUTO_INCREMENT,
  user_id INT NOT NULL,
  first_name VARCHAR(255),
  last_name VARCHAR(255),
  phone VARCHAR(20),
  parent_id INT,
  enrollment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id),
  FOREIGN KEY (parent_id) REFERENCES users(id)
);
```

### Tutors Table
```sql
CREATE TABLE tutors (
  id INT PRIMARY KEY AUTO_INCREMENT,
  user_id INT NOT NULL,
  first_name VARCHAR(255),
  last_name VARCHAR(255),
  specialization VARCHAR(255),
  hourly_rate DECIMAL(10, 2),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id)
);
```

### Courses Table
```sql
CREATE TABLE courses (
  id INT PRIMARY KEY AUTO_INCREMENT,
  name VARCHAR(255) NOT NULL,
  description TEXT,
  tutor_id INT NOT NULL,
  price DECIMAL(10, 2),
  duration_hours INT,
  max_students INT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (tutor_id) REFERENCES tutors(id)
);
```

### Payments Table
```sql
CREATE TABLE payments (
  id INT PRIMARY KEY AUTO_INCREMENT,
  student_id INT NOT NULL,
  amount DECIMAL(10, 2),
  payment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  status ENUM('pending', 'completed', 'failed', 'refunded') DEFAULT 'pending',
  course_id INT,
  FOREIGN KEY (student_id) REFERENCES students(id),
  FOREIGN KEY (course_id) REFERENCES courses(id)
);
```

## 👥 User Roles

### Admin Role
- Manage all users and permissions
- View financial reports
- Manage courses and enrollments
- System configuration and monitoring

**Permissions:**
- Create/Edit/Delete users
- View all transactions
- Generate reports
- Manage system settings

### Tutor Role
- Create and manage courses
- View enrolled students
- Track sessions and payments
- Manage schedule

**Permissions:**
- Create courses
- View student information
- Track hours and earnings
- Manage availability

### Student Role
- Enroll in courses
- View course materials
- Track progress
- Make payments

**Permissions:**
- View assigned courses
- Submit assignments
- View grades
- Make course payments

### Parent Role
- Monitor student progress
- View enrolled courses
- View payment history
- Receive notifications

**Permissions:**
- View student's courses
- View student's progress
- View payment records
- Receive update notifications

## 🧪 Testing

### Running Tests

The project has organized test suite located in `webapp/tests/`:

```bash
# Unit tests (component-level testing)
php webapp/tests/unit/run_tests.php

# Integration tests (multi-component workflows)
php webapp/tests/integration/run_tests.php

# End-to-end tests (full browser testing)
php webapp/tests/e2e/run_tests.php

# Run all tests
bash webapp/tests/run_all_tests.sh
```

### Test Organization

```
tests/
├── unit/              # Isolated component tests (12 files)
├── integration/       # Multi-component tests (8 files)
├── e2e/              # Browser-based tests (12 files)
├── fixtures/         # Test data and factories (1 file)
├── scripts/          # Test utilities (1 file)
├── archived/         # Debug files (7 files)
├── README.md         # Detailed testing guide
├── QUICK_REFERENCE.md # Command quick reference
├── CONSOLIDATION_GUIDE.md # Test redundancy report
└── index.html        # Interactive test browser
```

For detailed testing information, see `webapp/tests/README.md`

## 📚 Documentation

Additional documentation files:

- **TESTING_REFACTOR_SUMMARY.md** - Test organization and refactoring details
- **PAYMENT_SYSTEM_DOCUMENTATION.md** - Payment system architecture
- **USERNAME_ROUTING.md** - URL routing reference
- **webapp/tests/README.md** - Comprehensive testing guide
- **webapp/tests/QUICK_REFERENCE.md** - Testing commands reference
- **webapp/tests/CONSOLIDATION_GUIDE.md** - Test consolidation roadmap

## 🔧 Troubleshooting

### Common Issues

#### Issue: Database Connection Error
**Problem:** `Connection refused` or `No database selected`

**Solution:**
1. Verify MySQL is running (check XAMPP Control Panel)
2. Check database credentials in `Base/Database.php`
3. Verify `ValEduDatabase` exists in MySQL
4. Run: `mysql -u root < webapp/ValEduDatabase.sql`

#### Issue: 404 Not Found Errors
**Problem:** `The requested resource was not found`

**Solution:**
1. Verify `.htaccess` is in `webapp/` directory
2. Check that Apache `mod_rewrite` is enabled
3. Restart Apache after enabling modules
4. Access via `http://localhost/VAL_EDU/webapp/`

#### Issue: Session Not Working
**Problem:** User keeps getting logged out

**Solution:**
1. Check session folder has write permissions
2. Verify `session.save_path` in php.ini
3. Check browser allows cookies
4. Clear browser cache and cookies

#### Issue: Character Encoding Issues
**Problem:** Vietnamese characters display as `?` or garbled

**Solution:**
1. Verify database charset is `utf8mb4`
2. Check HTML meta charset: `<meta charset="UTF-8">`
3. Verify MySQL connection charset in `Base/Database.php`:
   ```php
   $this->connection->set_charset("utf8mb4");
   ```

#### Issue: Permission Denied Errors
**Problem:** `Permission denied` when accessing files

**Solution:**
1. Check file permissions: `chmod 755 webapp/`
2. Verify Apache user has read/write access
3. Check logs in `apache/logs/error.log`

#### Issue: White Blank Page
**Problem:** Page loads but shows nothing

**Solution:**
1. Check PHP error logs: `php -r "phpinfo();" | grep "error_log"`
2. Enable display_errors temporarily in `php.ini`
3. Check for syntax errors in controller/view files
4. Review Apache error log

## 🤝 Contributing

### Code Style Guidelines

- **PHP:** Follow PSR-2 coding standards
- **JavaScript:** Use consistent indentation (2 spaces)
- **HTML/CSS:** Semantic markup, mobile-first design
- **Comments:** Add meaningful comments for complex logic

### How to Contribute

1. Fork the repository
2. Create feature branch: `git checkout -b feature/your-feature`
3. Make your changes
4. Write/update tests as needed
5. Commit with clear messages: `git commit -m "Add your feature description"`
6. Push to branch: `git push origin feature/your-feature`
7. Open Pull Request with description

### Testing Before Submission

- Run all tests: `bash webapp/tests/run_all_tests.sh`
- Verify no PHP errors: `php -l yourfile.php`
- Check database migrations work
- Test in multiple browsers if UI changes

### Code Review Process

- At least one approval required before merge
- CI/CD checks must pass
- No merge conflicts with main branch
- Updated documentation for new features

## 📄 License

Educational Use License - This project is provided for learning and educational purposes.

## 👤 Author & Support

**Project:** VAL_EDU - Educational Platform
**Purpose:** Learning and educational management
**Support:** For issues and feedback, visit https://github.com/anomalyco/VAL_EDU

## 🗺️ Roadmap

### Planned Features

#### Phase 1: Core Enhancements
- [ ] Implement `.env` file configuration support
- [ ] Add comprehensive error logging
- [ ] Implement request/response caching
- [ ] Add email notifications

#### Phase 2: Advanced Features
- [ ] Real-time chat between students and tutors
- [ ] Video conferencing integration
- [ ] Mobile app development
- [ ] API rate limiting

#### Phase 3: Analytics & Reporting
- [ ] Student performance analytics
- [ ] Financial reporting dashboard
- [ ] Usage metrics and insights
- [ ] Export to various formats (PDF, Excel)

#### Phase 4: Production Ready
- [ ] Implement multi-environment configuration
- [ ] Add comprehensive test coverage (>80%)
- [ ] Security audit and penetration testing
- [ ] Performance optimization and caching
- [ ] Deployment pipeline (CI/CD)

## ⚠️ Known Limitations

- **Single Server:** Not designed for distributed/multi-server deployment
- **Hard-coded Configuration:** Uses hard-coded values (see Configuration section)
- **No API Authentication:** API endpoints use session-based auth only
- **Limited Error Logging:** Basic error handling without comprehensive logging
- **No Background Jobs:** Synchronous processing only
- **Basic Input Validation:** Client-side and server-side validation present but could be enhanced
- **No HTTPS/SSL:** Designed for local development without encryption

## 📝 Changelog

### Version 1.0.0 - Initial Release (Feb 2026)
- ✅ Multi-role user system (Admin, Tutor, Student, Parent)
- ✅ Course management and enrollment
- ✅ Payment tracking system
- ✅ Calendar and scheduling
- ✅ Student and parent portals
- ✅ Comprehensive test suite
- ✅ Complete documentation
- ✅ API endpoints for core functionality

### Upcoming
- Environment variable configuration (.env)
- Enhanced error logging
- Real-time notifications
- Video integration
- Analytics dashboard

---

**Last Updated:** February 2026
**Repository:** https://github.com/anomalyco/VAL_EDU
**Questions or Issues?** Visit the GitHub issues page

