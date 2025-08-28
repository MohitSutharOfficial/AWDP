# 🚀 Vercel Deployment Guide

## Quick Deploy Steps

### 1. GitHub Setup

1. Create new repository: `techcorp-solutions`
2. Upload all project files
3. Commit changes

### 2. Vercel Deployment

1. Go to [vercel.com](https://vercel.com)
2. Import GitHub repository
3. Deploy with default settings

### 3. Environment Variables

Add in Vercel Settings:

```
SUPABASE_PASSWORD=rsMwRvhAs3qxIWQ8
USE_SUPABASE=true
```

### 4. Database Setup

1. Run SQL in Supabase from `supabase-setup.sql`
2. Visit `/setup.php` on your live site

### 5. Test Live Site

- Homepage: `/`
- Contact: `/contact.php`
- Admin: `/admin.php` (admin/admin123)

## Your Supabase Details

- Host: `db.brdavdukxvilpdzgbsqd.supabase.co`
- Database: `postgres`
- User: `postgres`
- Password: `rsMwRvhAs3qxIWQ8`

## Expected Live URLs

- Site: `https://techcorp-solutions.vercel.app`
- Admin: `https://techcorp-solutions.vercel.app/admin.php`
