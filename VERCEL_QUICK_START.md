# 🚀 Quick Vercel Setup - TechCorp Solutions

## ⚡ One-Click Deployment

[![Deploy with Vercel](https://vercel.com/button)](https://vercel.com/new/clone?repository-url=https://github.com/yourusername/techcorp-solutions)

## 📋 Prerequisites

- [Vercel Account](https://vercel.com/signup) (free)
- Database hosting (choose one):
  - [PlanetScale](https://planetscale.com/) (recommended, free tier)
  - [Railway](https://railway.app/) (free tier)
  - [Supabase](https://supabase.com/) (free tier)
  - Any MySQL hosting provider

## 🎯 5-Minute Setup

### 1. Clone & Deploy

```bash
# Clone the repository
git clone https://github.com/yourusername/techcorp-solutions
cd techcorp-solutions

# Install Vercel CLI
npm i -g vercel

# Login and deploy
vercel login
vercel --prod
```

### 2. Database Setup (PlanetScale - Recommended)

1. **Create PlanetScale Account**: Go to [planetscale.com](https://planetscale.com)
2. **Create Database**: Click "New database" → Name it `techcorp`
3. **Get Connection String**: Go to Settings → Copy connection details
4. **Create Tables**: Use the provided SQL schema

#### Quick SQL Setup:

```sql
-- Copy and paste this into your PlanetScale console

CREATE TABLE contacts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    company VARCHAR(255),
    subject VARCHAR(255),
    message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('new', 'read', 'replied') DEFAULT 'new'
);

CREATE TABLE testimonials (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    company VARCHAR(255),
    position VARCHAR(255),
    testimonial TEXT NOT NULL,
    rating INT DEFAULT 5,
    is_featured BOOLEAN DEFAULT FALSE,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Sample testimonials
INSERT INTO testimonials (name, company, position, testimonial, rating, is_featured) VALUES
('Sarah Johnson', 'Digital Marketing Pro', 'CEO', 'TechCorp Solutions transformed our business with their innovative web platform. Exceptional work!', 5, true),
('Michael Chen', 'StartupVenture Inc.', 'CTO', 'Outstanding mobile app development. They delivered on time and exceeded expectations.', 5, true),
('Emily Rodriguez', 'HealthTech Solutions', 'Product Manager', 'The cloud migration was seamless. Highly recommended for enterprise solutions.', 5, false);
```

### 3. Configure Environment Variables

In your **Vercel Dashboard**:

1. Go to your project → Settings → Environment Variables
2. Add these variables:

```
DB_HOST=aws.connect.psdb.cloud
DB_USER=your_planetscale_username
DB_PASSWORD=your_planetscale_password
DB_NAME=techcorp
```

### 4. Redeploy

```bash
vercel --prod
```

## ✅ Verification

Test these features:

- [ ] Homepage loads: `https://your-project.vercel.app`
- [ ] Contact form works (check database)
- [ ] Testimonials display from database
- [ ] Mobile responsiveness
- [ ] All animations work

## 🔧 Alternative Database Options

### Railway Setup:

1. Go to [railway.app](https://railway.app)
2. Create MySQL database
3. Use provided connection details
4. Import the SQL schema

### Supabase Setup:

1. Go to [supabase.com](https://supabase.com)
2. Create new project
3. Use SQL editor to run schema
4. Get connection details from settings

## 📱 Features Included

✅ **Responsive Design** - Works on all devices  
✅ **Contact Form** - Stores in database  
✅ **Testimonials** - Dynamic from database  
✅ **Admin Panel** - Content management (PHP version)  
✅ **Modern UI** - Bootstrap + Tailwind CSS  
✅ **Animations** - Smooth transitions  
✅ **SEO Optimized** - Meta tags and structure  
✅ **Performance** - Optimized for speed

## 🌐 Custom Domain

1. **In Vercel Dashboard:**

   - Go to Domains
   - Add your domain
   - Follow DNS instructions

2. **DNS Configuration:**
   ```
   Type: CNAME
   Name: @ (or www)
   Value: cname.vercel-dns.com
   ```

## 🚨 Troubleshooting

### Contact Form Not Working:

1. Check environment variables in Vercel
2. Verify database connection
3. Check browser console for errors

### Database Connection Issues:

1. Verify credentials in Vercel dashboard
2. Check database is running
3. Ensure SSL is configured correctly

### Deployment Failures:

1. Check Vercel build logs
2. Verify package.json syntax
3. Ensure all dependencies are listed

## 📞 Support

Need help? Check:

- [Vercel Documentation](https://vercel.com/docs)
- [PlanetScale Docs](https://docs.planetscale.com/)
- Project issues on GitHub

## 🎉 Success!

Your TechCorp Solutions website is now live with:

- ⚡ Lightning-fast Vercel hosting
- 🗄️ Serverless database integration
- 🌍 Global CDN distribution
- 🔒 Automatic SSL certificates
- 📊 Built-in analytics

**Live URL**: `https://your-project.vercel.app`

---

**Perfect for assignments, portfolios, and client projects!** 🚀
