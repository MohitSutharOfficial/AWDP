# TechCorp Solutions - Development Guide

## Local Development with XAMPP

### Setup Instructions:

1. **Install XAMPP**

   - Download and install XAMPP with PHP 8.1+
   - Start Apache and MySQL services

2. **Database Setup**

   - Create database `techcorp_db` in phpMyAdmin
   - Import the SQL schema from `/docs/database.sql`

3. **Local Testing**
   - Copy project to `C:\xampp\htdocs\techcorp\`
   - Access via: `http://localhost/techcorp/`
   - Or use PHP built-in server: `php -S localhost:8000 local-server.php`

### Production Deployment (Vercel)

The application is deployed using Node.js serverless functions:

- Contact: `/api/contact-node.js`
- Admin: `/api/admin-node.js`
- Testimonials: `/api/testimonials-node.js`
- Setup: `/api/setup-node.js`

### Database Configuration

**Local (MySQL):**

```php
$host = 'localhost';
$database = 'techcorp_db';
$username = 'root';
$password = '';
```

**Production (Supabase PostgreSQL):**

```javascript
host: "db.brdavdukxvilpdzgbsqd.supabase.co";
database: "postgres";
user: "postgres";
password: "rsMwRvhAs3qxIWQ8";
```

### Testing Endpoints

**Local URLs:**

- Homepage: `http://localhost/techcorp/`
- Contact: `http://localhost/techcorp/contact`
- Admin: `http://localhost/techcorp/admin`
- Testimonials: `http://localhost/techcorp/testimonials`
- Setup: `http://localhost/techcorp/setup`

**Production URLs:**

- All endpoints use the same paths but on Vercel domain
