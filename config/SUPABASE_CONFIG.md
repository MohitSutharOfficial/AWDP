# Supabase Configuration Instructions

## 🔧 How to Switch Between Local MySQL and Supabase

Your project now supports both local MySQL (XAMPP) and Supabase PostgreSQL automatically!

### 🏠 For Local Testing (MySQL/XAMPP):

- **Default behavior** - No setup needed
- Uses `localhost` MySQL database
- Perfect for local development

### ☁️ For Supabase Testing:

1. **Set your Supabase password** in one of these ways:

   **Option A: Environment Variable (Recommended)**

   ```bash
   # Set environment variable
   set SUPABASE_PASSWORD=your_actual_password_here
   ```

   **Option B: Create config file**

   - Create file: `config/.use-supabase`
   - Update password in `config/database.php` line with your actual password

   **Option C: Direct edit**

   - Edit `config/database.php`
   - Replace `your_supabase_password_here` with your actual password

### 🚀 For Vercel Deployment:

- Automatically uses Supabase when deployed to Vercel
- Set `SUPABASE_PASSWORD` in Vercel environment variables

## 📋 Your Supabase Details:

- **Host:** db.brdavdukxvilpdzgbsqd.supabase.co
- **Port:** 5432
- **Database:** postgres
- **User:** postgres
- **Password:** [You need to provide this]

## 🧪 Testing Steps:

### 1. Test Locally with MySQL:

```bash
# Default - no changes needed
http://localhost/techcorp-solutions/
```

### 2. Test Locally with Supabase:

```bash
# Set password and create switch file
echo. > config/.use-supabase
# Then visit: http://localhost/techcorp-solutions/
```

### 3. Deploy to Vercel:

```bash
# Set environment variable in Vercel dashboard
SUPABASE_PASSWORD=your_password
```

## ⚡ Quick Switch Commands:

**Enable Supabase locally:**

```bash
echo. > config/.use-supabase
```

**Disable Supabase (back to MySQL):**

```bash
del config\.use-supabase
```
