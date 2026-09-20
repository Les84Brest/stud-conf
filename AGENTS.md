# AGENTS.md - Conference Management Platform

## 📋 Project Overview

**Platform:** Digital platform for student scientific and practical conference management  
**Stack:** Laravel 13 + React 19 + Filament 5 + MySQL  
**Status:** In Development  
**Repository:** [Ваш репозиторий]

### Quick Links
- **Admin Panel:** `/admin` (Filament 5)
- **API:** `/api/*` (Laravel Sanctum)
- **Frontend:** React SPA (development)

---

## 🏗️ Architecture
```text
┌─────────────────────────────────────────────────────┐
│ React 19 SPA │
│ (Frontend) │
└────────────────────┬────────────────────────────────┘
│ REST API
┌────────────────────▼────────────────────────────────┐
│ Laravel 13 Backend │
├─────────────────────────────────────────────────────┤
│ Filament 5 Admin │ API Controllers │ Services │
│ Panel │ │ │
└────────────────────┬────────────────────────────────┘
│
┌────────────────────▼────────────────────────────────┐
│ MySQL Database │
└─────────────────────────────────────────────────────┘
```


### Technology Stack

| Layer | Technology | Version |
| --- | --- | --- |
| **Backend** | Laravel | 13.x |
| **Admin Panel** | Filament | 5.x |
| **Frontend** | React | 19.x |
| **Database** | MySQL | 8.0 |
| **Auth** | Laravel Sanctum | 4.x |
| **Styling** | Chakra UI | Latest |
| **Form Management** | Formik | Latest |
| **Routing** | React Router | 6.x |
| **PHP** | PHP | 8.3+ |
| **Docker** | Docker Compose | Latest |

---

## Docker access
### PHP
cli command `docker exec -it universal-php bash` and in contaidner run `cd /stud-conf.loc` to work with project code

### mySQL database
cli command  docker exec -it universal-db bash -c "mysql -uroot -p$$DB_ROOT_PASSWORD"
database name is studconf

### nodejs
cli command  `docker exec -it universal-node sh` and in contaidner run `cd /stud-conf.loc` to work with project code


## 📁 Project Structure
```text
stud-conf.loc/
├── app/
│ ├── Filament/
│ │ ├── Resources/ # Admin panel resources
│ │ │ ├── ConferenceResource.php
│ │ │ ├── EventResource.php
│ │ │ ├── UserResource.php
│ │ │ ├── AuthorResource.php
│ │ │ ├── PresentationResource.php
│ │ │ ├── CriteriaResource.php
│ │ │ ├── CriteriaGroupResource.php
│ │ │ └── AssessmentResource.php
│ │ ├── Widgets/ # Dashboard widgets
│ │ └── Pages/ # Custom admin pages
│ ├── Http/
│ │ ├── Controllers/
│ │ │ └── Api/ # API controllers for React
│ │ └── Middleware/ # Custom middleware
│ ├── Models/ # All Eloquent models
│ ├── Services/ # Business logic layer
│ └── Traits/ # Reusable traits
├── database/
│ ├── migrations/ # Database migrations
│ ├── seeders/ # Seeders for test data
│ └── factories/ # Model factories
├── routes/
│ ├── api.php # API routes (Laravel)
│ ├── web.php # Web routes
│ └── console.php # CLI commands
├── resources/
│ ├── js/ # React frontend source
│ │ ├── pages/
│ │ ├── components/
│ │ ├── services/
│ │ └── App.jsx
│ └── views/ # Blade templates
├── docker/ # Docker configuration
├── tests/ # PHPUnit tests
├── .env # Environment configuration
├── composer.json # PHP dependencies
├── package.json # Node dependencies
└── docker-compose.yml # Docker compose configuration
```


---

## 🎯 Core Features

### 1. User Roles
| Role | Access | Description |
|------|--------|-------------|
| **Admin** | Full access | Manage conferences, users, reports |
| **Expert** | Assigned events | Evaluate presentations |
| **Observer** | Read-only | View results and statistics |

### 2. Key Modules

#### Conference Management
- CRUD operations for conferences
- Event management (sections, olympiads, round tables)
- Schedule and location management

#### Expert Management
- Assign experts to events
- Role-based access control
- User activity tracking

#### Evaluation System
- Criteria-based scoring (6 criteria, max 19 points)
- Star rating or numeric input
- Auto-save functionality
- Assessment history logging

#### Reporting
- Score aggregation (average/sum)
- Expert performance comparison
- Export to Excel (.xlsx)
- PDF reports (planned)

---

## 🚀 Development Setup

### Prerequisites
- PHP 8.3+
- Composer 2.x
- Node.js 20+ & NPM
- Docker & Docker Compose (optional)
- MySQL 8.0+



# Frontend URL for CORS
FRONTEND_URL=http://stud-conf.loc/

# Filament admin path
FILAMENT_PATH=admin
🧪 Testing
bash
# Run all tests
php artisan test

# Run specific test suite
php artisan test --testsuite=Feature
php artisan test --testsuite=Unit

# Run tests with coverage
php artisan test --coverage
## Common Commands
Laravel Commands
bash
php artisan serve                # Start development server
php artisan tinker               # Interactive shell
php artisan make:model ModelName # Create model
php artisan make:controller      # Create controller
php artisan migrate:fresh --seed # Reset and seed database
php artisan optimize:clear       # Clear all cache
php artisan route:list           # List all routes
php artisan filament:make-resource # Create Filament resource
Filament Commands
bash
php artisan make:filament-user            # Create admin user
php artisan make:filament-resource Model  # Create resource
php artisan filament:clear-cached-components # Clear Filament cache
php artisan make:filament-widget          # Create dashboard widget
php artisan make:filament-relation-manager # Create relation manager
Frontend Commands
bash
npm run dev          # Start development server
npm run build        # Build for production
npm run test         # Run frontend tests
npm run format       # Run prettier
npm run lint         # Run ESLint

## Database Schema
Core Tables
users - System users (admins, experts, observers)

conferences - Conference information

events - Conference events (sections, olympiads, round tables)

expert_event - Many-to-many relation (experts → events)

authors - Presentation authors

presentations - Presentations/documents

presentation_author - Many-to-many (presentations → authors)

criteria_groups - Groups of evaluation criteria

criterias - Evaluation criteria

event_criteria - Criteria assigned to events

assessments - Expert evaluations

assessment_logs - History of assessment changes

activity_logs - User activity logs

settings - System settings

## Key Relationships
```text
Conference → hasMany → Event
Event → belongsTo → Conference
Event → belongsToMany → User (experts) via expert_event
Event → belongsToMany → Criteria via event_criteria
Event → hasMany → Presentation
Presentation → belongsTo → Event
Presentation → belongsToMany → Author via presentation_author
Presentation → hasMany → Assessment
Assessment → belongsTo → Presentation
Assessment → belongsTo → User (expert)
Assessment → belongsTo → Event
```

## Filament Admin Panel
Navigation Groups
Панель управления - Dashboard, widgets

Управление конференциями - Conferences, Events, Criteria

Управление пользователями - Users, Experts

Управление контентом - Authors, Presentations

Оценки и отчеты - Assessments, Reports

Настройки - System settings

Creating Resources

# Create new resource
`php artisan make:filament-resource ModelName`


