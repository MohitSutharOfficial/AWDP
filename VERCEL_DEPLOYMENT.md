# 🚀 Vercel Deployment Guide - TechCorp Solutions

## 📋 Prerequisites

- [Node.js](https://nodejs.org/) installed (v18 or higher)
- [Git](https://git-scm.com/) installed
- [Vercel CLI](https://vercel.com/cli) installed globally
- Database hosting (PlanetScale, Railway, or traditional MySQL hosting)

## 🎯 Deployment Options

### Option 1: Frontend Only (Static Site)

**Best for:** Assignment demonstration, portfolio showcase
**Limitations:** No backend functionality (contact form, admin panel)

### Option 2: Full Stack (Recommended)

**Best for:** Complete functionality
**Includes:** Contact form, testimonials, admin features

---

## 🌐 Option 1: Static Site Deployment

### Step 1: Install Vercel CLI

```bash
npm install -g vercel
```

### Step 2: Prepare Static Files

```bash
# Create a clean directory
mkdir vercel-static
cd vercel-static

# Copy only static files
cp ../index.html .
cp ../404.html .
cp -r ../assets .
```

### Step 3: Deploy to Vercel

```bash
# Login to Vercel
vercel login

# Deploy
vercel

# For production deployment
vercel --prod
```

### Step 4: Custom Domain (Optional)

1. Go to your Vercel dashboard
2. Select your project
3. Go to Settings > Domains
4. Add your custom domain

---

## 🔧 Option 2: Full Stack Deployment

### Step 1: Setup Database

#### Option A: PlanetScale (Recommended)

1. Go to [PlanetScale](https://planetscale.com/)
2. Create a free account
3. Create a new database
4. Get connection details

#### Option B: Railway

1. Go to [Railway](https://railway.app/)
2. Create a MySQL database
3. Get connection string

#### Option C: Traditional Hosting

1. Use your existing MySQL hosting
2. Note connection details

### Step 2: Environment Variables

Create `.env.local` file:

```env
DB_HOST=your_database_host
DB_USER=your_database_username
DB_PASSWORD=your_database_password
DB_NAME=your_database_name
```

### Step 3: Install Dependencies

```bash
npm install
```

### Step 4: Setup Database Tables

```bash
# Run this SQL in your database:
CREATE TABLE IF NOT EXISTS contacts (
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

CREATE TABLE IF NOT EXISTS testimonials (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    company VARCHAR(255),
    position VARCHAR(255),
    testimonial TEXT NOT NULL,
    rating INT DEFAULT 5,
    image_url VARCHAR(500),
    is_featured BOOLEAN DEFAULT FALSE,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

# Insert sample testimonials
INSERT INTO testimonials (name, company, position, testimonial, rating, is_featured) VALUES
('Sarah Johnson', 'Digital Marketing Pro', 'CEO', 'TechCorp Solutions transformed our business with their innovative web platform.', 5, true),
('Michael Chen', 'StartupVenture Inc.', 'CTO', 'Outstanding mobile app development. They delivered on time and within budget.', 5, true),
('Emily Rodriguez', 'HealthTech Solutions', 'Product Manager', 'The cloud migration services were seamless and professional.', 5, false);
```

### Step 5: Update Frontend for API Calls

Update `assets/js/main.js`:

```javascript
// Replace the contact form submission in main.js
function initContactForm() {
  const contactForm = document.getElementById("contactForm");

  if (!contactForm) return;

  contactForm.addEventListener("submit", function (e) {
    e.preventDefault();

    const formData = new FormData(this);
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;

    // Show loading state
    submitBtn.innerHTML = '<span class="loading"></span> Sending...';
    submitBtn.disabled = true;

    // Convert FormData to JSON
    const data = {};
    formData.forEach((value, key) => {
      data[key] = value;
    });

    // Submit to Vercel API
    fetch("/api/contact", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify(data),
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          showAlert("success", data.message);
          contactForm.reset();
        } else {
          showAlert("danger", data.message);
        }
      })
      .catch((error) => {
        console.error("Error:", error);
        showAlert(
          "danger",
          "Sorry, there was an error sending your message. Please try again."
        );
      })
      .finally(() => {
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
      });
  });
}
```

### Step 6: Deploy to Vercel

```bash
# Login to Vercel
vercel login

# Deploy with environment variables
vercel

# Add environment variables in Vercel dashboard:
# Go to Settings > Environment Variables
# Add: DB_HOST, DB_USER, DB_PASSWORD, DB_NAME

# Deploy to production
vercel --prod
```

### Step 7: Configure Environment Variables in Vercel

1. Go to your Vercel dashboard
2. Select your project
3. Go to Settings > Environment Variables
4. Add each variable:
   - `DB_HOST`: Your database host
   - `DB_USER`: Your database username
   - `DB_PASSWORD`: Your database password
   - `DB_NAME`: Your database name

### Step 8: Test Deployment

1. Visit your Vercel URL
2. Test the contact form
3. Check if testimonials load
4. Verify all functionality

---

## 🔍 Testing Your Deployment

### Frontend Tests:

- [ ] Homepage loads correctly
- [ ] All sections display properly
- [ ] Mobile responsiveness works
- [ ] Navigation functions correctly
- [ ] Contact form submits successfully
- [ ] Testimonials display from database

### Backend Tests:

- [ ] Contact form stores data in database
- [ ] API endpoints respond correctly
- [ ] Database connection works
- [ ] Error handling functions properly

---

## 🚨 Troubleshooting

### Common Issues:

**1. Database Connection Errors**

```
Solution: Check environment variables in Vercel dashboard
Verify database host, username, password, and name
```

**2. API Routes Not Working**

```
Solution: Ensure files are in /api/ directory
Check vercel.json configuration
Verify serverless function syntax
```

**3. CORS Errors**

```
Solution: Check CORS headers in API functions
Ensure proper Access-Control-Allow-Origin settings
```

**4. Build Failures**

```
Solution: Check package.json dependencies
Verify Node.js version compatibility
Review Vercel build logs
```

---

## 📱 Custom Domain Setup

1. **Add Domain in Vercel:**

   - Go to Project Settings > Domains
   - Add your custom domain
   - Follow DNS configuration instructions

2. **DNS Configuration:**

   ```
   Type: CNAME
   Name: www (or @)
   Value: cname.vercel-dns.com
   ```

3. **SSL Certificate:**
   - Automatically provided by Vercel
   - Usually takes 24-48 hours to activate

---

## 🎯 Performance Optimization

### Vercel Optimizations:

- Automatic CDN distribution
- Image optimization (if using Vercel's Image component)
- Automatic compression
- Edge caching

### Manual Optimizations:

- Minimize CSS/JS files
- Optimize images before upload
- Use efficient database queries
- Implement proper caching headers

---

## 📊 Monitoring & Analytics

### Vercel Analytics:

1. Go to your project dashboard
2. Enable Analytics in Settings
3. View performance metrics
4. Monitor function execution

### Database Monitoring:

- Check database performance
- Monitor connection limits
- Review query execution times
- Set up alerts for issues

---

## 🔒 Security Best Practices

1. **Environment Variables:**

   - Never commit `.env` files
   - Use Vercel's environment variable system
   - Rotate database credentials regularly

2. **Database Security:**

   - Use SSL connections
   - Implement proper user permissions
   - Regular security updates

3. **Frontend Security:**
   - Validate all user inputs
   - Sanitize data before database insertion
   - Implement rate limiting

---

## 📝 Final Checklist

- [ ] Project deployed to Vercel
- [ ] Database connected and accessible
- [ ] Environment variables configured
- [ ] Contact form working
- [ ] Testimonials loading from database
- [ ] Custom domain configured (optional)
- [ ] SSL certificate active
- [ ] Performance optimized
- [ ] Security measures implemented
- [ ] Testing completed
- [ ] Documentation updated

---

## 🎉 Success!

Your TechCorp Solutions website is now live on Vercel with full database functionality!

**Live URL:** `https://your-project.vercel.app`

Don't forget to submit your live URL in the assignment Google form! 🚀
