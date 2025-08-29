# TechCorp Solutions - Railway Deployment

## 🚂 Railway Deployment Guide

### Quick Deploy to Railway

[![Deploy on Railway](https://railway.app/button.svg)](https://railway.app/template/new)

### Manual Deployment Steps

1. **Connect Repository**

   ```bash
   # Push your code to GitHub
   git add .
   git commit -m "Railway deployment ready"
   git push origin main
   ```

2. **Create Railway Project**

   - Go to [Railway.app](https://railway.app)
   - Click "Deploy from GitHub repo"
   - Select your repository
   - Railway auto-detects PHP

3. **Add PostgreSQL Database**

   - In Railway dashboard, click "Add Service"
   - Select "PostgreSQL"
   - Railway provides DATABASE_URL automatically

4. **Environment Variables**
   Railway auto-sets:

   - `DATABASE_URL` (PostgreSQL connection)
   - `PORT` (app port)
   - `RAILWAY_ENVIRONMENT` (production)

5. **Custom Domain (Optional)**
   - Go to Settings → Domains
   - Add your custom domain
   - Update DNS records

### Clean Project Structure

```
├── public/              # Entry point for Railway
│   ├── index.php       # Railway router
│   └── assets/         # CSS, JS, images
├── config/
│   └── database.php    # Railway-compatible DB config
├── index.html          # Homepage
├── admin.php           # Admin panel
├── contact.php         # Contact form
├── testimonials.php    # Testimonials page
├── setup.php          # Database initialization
├── composer.json       # PHP dependencies
├── Procfile           # Railway start command
├── railway.toml       # Railway configuration
└── .env.example       # Environment variables template

```

### Database Setup

After deployment:

1. Visit: `https://your-app.railway.app/setup`
2. Initialize database tables
3. Access admin: `https://your-app.railway.app/admin`
4. Login: `admin` / `admin123`

### Features

- ✅ PHP 8.1+ Runtime
- ✅ PostgreSQL Database
- ✅ Auto-deployment from Git
- ✅ Environment variables
- ✅ Custom domains
- ✅ SSL certificates
- ✅ Logs and monitoring

### Troubleshooting

**Database Connection Issues:**

```bash
# Check Railway logs
railway logs
```

**Environment Variables:**

```bash
# View all environment variables
railway variables
```

**Local Development:**

```bash
# Install Railway CLI
npm install -g @railway/cli

# Login to Railway
railway login

# Link to your project
railway link

# Run locally with Railway environment
railway run php -S localhost:8000 -t public/
```

### Comparison: Railway vs Vercel

| Feature     | Railway        | Vercel           |
| ----------- | -------------- | ---------------- |
| PHP Support | ✅ Native      | ⚠️ Limited       |
| PostgreSQL  | ✅ Built-in    | ❌ External only |
| Deployment  | ✅ Git push    | ✅ Git push      |
| Environment | ✅ Full server | ⚠️ Serverless    |
| Cost        | 💰 $5/month    | 💰 Free tier     |

### Next Steps

1. Deploy to Railway
2. Test all functionality
3. Configure custom domain
4. Set up monitoring
5. Backup database regularly

Happy deploying! 🚀
