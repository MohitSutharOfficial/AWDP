# 🗄️ Database Setup Guide

## Supabase Configuration

### Connection Details:

- **Host:** `db.brdavdukxvilpdzgbsqd.supabase.co`
- **Port:** `5432`
- **Database:** `postgres`
- **User:** `postgres`
- **Password:** `rsMwRvhAs3qxIWQ8`

### Setup Steps:

1. **Run SQL Setup:**

   - Go to Supabase Dashboard → SQL Editor
   - Copy content from `supabase-setup.sql`
   - Execute the SQL script

2. **Verify Tables Created:**

   - `contacts` - Contact form submissions
   - `testimonials` - Client testimonials
   - `services` - Service listings
   - `projects` - Portfolio projects
   - `blog_posts` - Blog articles
   - `newsletter_subscribers` - Email subscribers

3. **Test Connection:**
   - Visit `/setup.php` on your live site
   - Should see success messages

## Tables Created:

- ✅ **contacts** - Form submissions
- ✅ **testimonials** - Client reviews (3 sample records)
- ✅ **services** - Service offerings (3 sample records)
- ✅ **projects** - Portfolio items
- ✅ **blog_posts** - Blog content
- ✅ **newsletter_subscribers** - Email list
