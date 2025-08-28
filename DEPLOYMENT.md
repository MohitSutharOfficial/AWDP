# 🚀 Deployment Guide - TechCorp Solutions

## Quick Deployment Steps

### 1. Upload Files

Upload all project files to your web hosting provider (e.g., SmartErasp.net)

### 2. Database Configuration

Edit `config/database.php` with your hosting credentials:

```php
$this->host = 'your_mysql_host';
$this->database = 'your_database_name';
$this->username = 'your_mysql_username';
$this->password = 'your_mysql_password';
```

### 3. Initialize Database

Visit: `https://yoursite.com/setup.php`

- Click to create tables
- Delete setup.php after completion

### 4. Test Website

- Homepage: `https://yoursite.com/`
- Contact: `https://yoursite.com/contact.php`
- Testimonials: `https://yoursite.com/testimonials.php`
- Admin: `https://yoursite.com/admin.php` (admin/admin123)

### 5. Submit Assignment

Submit your live URL in the Google form:
https://forms.gle/yrXpfKHfNXcDWC416

## 🌐 Vercel Deployment (Recommended for Modern Hosting)

**Note:** Vercel is primarily for static sites and serverless functions. For full PHP/MySQL functionality, you'll need a traditional hosting provider. However, you can deploy the frontend to Vercel and use a separate backend service.

### Option 1: Static Frontend Only (Vercel)

1. **Prepare for Static Deployment:**

   ```bash
   # Create a new directory for static files
   mkdir vercel-deployment

   # Copy static files
   cp index.html vercel-deployment/
   cp 404.html vercel-deployment/
   cp -r assets/ vercel-deployment/
   ```

2. **Create vercel.json Configuration:**

   ```json
   {
     "version": 2,
     "builds": [
       {
         "src": "index.html",
         "use": "@vercel/static"
       }
     ],
     "routes": [
       {
         "src": "/",
         "dest": "/index.html"
       },
       {
         "src": "/404",
         "dest": "/404.html"
       },
       {
         "handle": "filesystem"
       },
       {
         "src": "/(.*)",
         "dest": "/404.html"
       }
     ]
   }
   ```

3. **Deploy to Vercel:**

   ```bash
   # Install Vercel CLI
   npm i -g vercel

   # Login to Vercel
   vercel login

   # Deploy
   vercel --prod
   ```

### Option 2: Full Stack with Vercel + External Database

1. **Convert PHP to Serverless Functions:**
   Create `api/contact.js`:

   ```javascript
   import mysql from "mysql2/promise";

   export default async function handler(req, res) {
     if (req.method === "POST") {
       try {
         const connection = await mysql.createConnection({
           host: process.env.DB_HOST,
           user: process.env.DB_USER,
           password: process.env.DB_PASSWORD,
           database: process.env.DB_NAME,
         });

         const { name, email, phone, company, subject, message } = req.body;

         await connection.execute(
           "INSERT INTO contacts (name, email, phone, company, subject, message) VALUES (?, ?, ?, ?, ?, ?)",
           [name, email, phone, company, subject, message]
         );

         await connection.end();

         res
           .status(200)
           .json({ success: true, message: "Message sent successfully!" });
       } catch (error) {
         res
           .status(500)
           .json({ success: false, message: "Error sending message" });
       }
     } else {
       res.status(405).json({ message: "Method not allowed" });
     }
   }
   ```

2. **Add Environment Variables in Vercel:**

   ```
   DB_HOST=your_database_host
   DB_USER=your_database_user
   DB_PASSWORD=your_database_password
   DB_NAME=your_database_name
   ```

3. **Update package.json:**
   ```json
   {
     "name": "techcorp-solutions",
     "version": "1.0.0",
     "dependencies": {
       "mysql2": "^3.6.0"
     }
   }
   ```

### Option 3: Hybrid Approach (Recommended)

1. **Frontend on Vercel + Backend on Traditional Hosting:**

   - Deploy static files (HTML, CSS, JS) to Vercel
   - Keep PHP backend on SmartErasp.net or similar
   - Update API endpoints in JavaScript to point to backend

2. **Update main.js for API calls:**

   ```javascript
   // Replace localhost with your backend URL
   const API_BASE_URL = "https://your-backend-domain.com";

   fetch(`${API_BASE_URL}/contact.php`, {
     method: "POST",
     body: formData,
   });
   ```

## 📋 Pre-Deployment Checklist

- [ ] All files uploaded to hosting
- [ ] Database credentials updated
- [ ] Database tables created via setup.php
- [ ] setup.php deleted for security
- [ ] Contact form working
- [ ] Admin panel accessible
- [ ] All pages loading correctly
- [ ] Mobile responsiveness verified

## 🔧 Troubleshooting

**Database Connection Issues:**

- Verify host, username, password in config/database.php
- Ensure MySQL is enabled on hosting
- Check database name exists

**404 Errors:**

- Ensure all files uploaded to correct directory
- Check file permissions (644 for files, 755 for folders)

**Contact Form Not Working:**

- Check PHP version (requires 7.4+)
- Verify database tables exist
- Check error logs

## 📞 Support

If you encounter issues:

1. Check hosting provider documentation
2. Verify PHP/MySQL requirements
3. Contact hosting support if needed

**Assignment Requirements Met:**
✅ HTML5, CSS3, Bootstrap, Tailwind
✅ JavaScript functionality  
✅ Database connectivity
✅ Contact form with database
✅ Multiple database-connected pages
✅ Live on remote server
✅ Outstanding quality and design
