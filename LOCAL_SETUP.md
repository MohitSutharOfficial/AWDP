# TechCorp Local Development Setup

## Quick Start

1. Install PHP 8.2+ on your system
2. Navigate to the project directory
3. Run the development server:

```bash
php -S localhost:8000
```

4. Open your browser and go to:
   - Homepage: http://localhost:8000
   - Contact: http://localhost:8000/contact
   - Admin: http://localhost:8000/admin
   - Health Check: http://localhost:8000/health.php

## Database Configuration

The application uses SQLite by default (no additional setup required).
The database file will be created automatically at: `database/app.db`

## Admin Access

- Username: admin
- Password: admin123

## Railway Deployment

The application is configured for Railway deployment with:
- PHP 8.2
- Automatic database creation
- Health check endpoint
- Mobile-responsive design

## Features

✅ Contact form with validation
✅ Admin panel with dashboard
✅ Testimonials management
✅ Mobile-responsive design
✅ Real-time data updates
✅ Enhanced navigation
✅ Smart caching system
