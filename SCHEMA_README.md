# Database Schema Setup Guide

## Overview
This guide will help you set up the complete database schema for the AWDP (TechCorp Solutions) project in Supabase.

## Quick Start

### Step 1: Access Supabase SQL Editor
1. Log in to your [Supabase Dashboard](https://app.supabase.com)
2. Select your project
3. Navigate to the **SQL Editor** section in the left sidebar

### Step 2: Upload Schema
1. Open the `schema.sql` file in this repository
2. Copy the **entire contents** of the file
3. Paste it into the Supabase SQL Editor
4. Click the **Run** button (or press Ctrl/Cmd + Enter)

### Step 3: Verify Installation
After running the schema, you should see:
- ✅ 6 tables created
- ✅ 19 indexes created
- ✅ 4 triggers created
- ✅ 1 function created
- ✅ Sample services data inserted (6 services)

## Database Tables

The schema creates the following tables:

### 1. **contacts**
Stores contact form submissions from website visitors.
- Fields: id, name, email, phone, company, subject, message, created_at, status
- Status values: 'new', 'read', 'replied'

### 2. **testimonials**
Stores client testimonials and reviews.
- Fields: id, name, company, position, testimonial, rating, image_url, is_featured, is_active, created_at, updated_at
- Rating: 1-5 stars

### 3. **services**
Stores services offered by the company.
- Fields: id, title, description, icon, features (JSONB), price_range, is_active, sort_order, created_at, updated_at
- Features stored as JSON array

### 4. **projects**
Stores portfolio projects and case studies.
- Fields: id, title, description, technologies (JSONB), image_url, project_url, github_url, client_name, completion_date, is_featured, is_active, created_at, updated_at
- Technologies stored as JSON array

### 5. **blog_posts**
Stores blog articles and news posts.
- Fields: id, title, slug, excerpt, content, author, featured_image, tags (JSONB), is_published, published_at, created_at, updated_at
- Tags stored as JSON array
- Slug must be unique

### 6. **newsletter_subscribers**
Stores email newsletter subscriptions.
- Fields: id, email, name, is_active, subscribed_at, unsubscribed_at
- Email must be unique

## Features

### Automatic Timestamp Updates
Tables with `updated_at` columns automatically update this field when a row is modified:
- testimonials
- services
- projects
- blog_posts

### Indexes
All tables have appropriate indexes for optimal query performance:
- Primary keys on all tables
- Indexes on frequently queried columns
- Indexes on foreign key-like columns
- Indexes on status/active flags

### Data Validation
- Rating constraints (1-5)
- Status enum constraints
- Email uniqueness
- Slug uniqueness

## Sample Data

The schema includes sample data for the **services** table (6 services) which is configuration data needed for the application to work properly.

Optional sample data is available (commented out) for:
- Testimonials (5 sample testimonials)
- Projects (3 sample projects)

To enable sample data, edit the schema.sql file and uncomment the relevant INSERT statements.

## Verification

After running the schema, you can verify it was created successfully:

```sql
-- Check all tables exist
SELECT table_name 
FROM information_schema.tables 
WHERE table_schema = 'public' 
ORDER BY table_name;

-- Check row counts
SELECT 
    'contacts' as table_name, COUNT(*) as row_count FROM contacts
UNION ALL SELECT 'testimonials', COUNT(*) FROM testimonials
UNION ALL SELECT 'services', COUNT(*) FROM services
UNION ALL SELECT 'projects', COUNT(*) FROM projects
UNION ALL SELECT 'blog_posts', COUNT(*) FROM blog_posts
UNION ALL SELECT 'newsletter_subscribers', COUNT(*) FROM newsletter_subscribers;
```

## Environment Configuration

After setting up the database, make sure your application's environment variables are configured:

```env
SUPABASE_HOST=your-project-ref.pooler.supabase.com
SUPABASE_PORT=6543
SUPABASE_DATABASE=postgres
SUPABASE_USERNAME=postgres.your-project-ref
SUPABASE_PASSWORD=your-password
```

Or use the `DATABASE_URL` format:
```env
DATABASE_URL=postgresql://user:password@host:port/database
```

## Troubleshooting

### Error: "relation already exists"
This means the tables are already created. You can either:
1. Drop the existing tables and re-run the schema
2. Skip the schema creation (tables already exist)

### Error: "permission denied"
Make sure you're using the correct Supabase credentials with sufficient permissions.

### Error: "syntax error"
Make sure you copied the **entire** schema.sql file, including all lines.

## Re-running the Schema

The schema uses `CREATE TABLE IF NOT EXISTS` and `CREATE INDEX IF NOT EXISTS`, so it's safe to re-run. However, if you want to start fresh:

```sql
-- WARNING: This will delete all data!
DROP TABLE IF EXISTS contacts CASCADE;
DROP TABLE IF EXISTS testimonials CASCADE;
DROP TABLE IF EXISTS services CASCADE;
DROP TABLE IF EXISTS projects CASCADE;
DROP TABLE IF EXISTS blog_posts CASCADE;
DROP TABLE IF EXISTS newsletter_subscribers CASCADE;
DROP FUNCTION IF EXISTS update_updated_at_column() CASCADE;
```

Then run the schema.sql file again.

## Support

If you encounter any issues:
1. Check the Supabase logs in the dashboard
2. Verify your database credentials
3. Ensure you have the correct permissions
4. Review the error message in the SQL Editor

## Files in this Repository

- `schema.sql` - Complete database schema (USE THIS FILE)
- `docs/supabase-setup.sql` - Previous version (for reference)
- `docs/database.sql` - MySQL version for local development (not for Supabase)
- `SCHEMA_README.md` - This file

---

**Last Updated:** 2025-11-01  
**Version:** 1.0.0  
**Compatible with:** PostgreSQL 14+ (Supabase)
