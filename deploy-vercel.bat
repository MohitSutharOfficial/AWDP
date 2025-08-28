@echo off
REM Vercel Deployment Script for Windows - TechCorp Solutions

echo 🚀 Starting Vercel Deployment Process...
echo ======================================

REM Check if Node.js is installed
node --version >nul 2>&1
if %ERRORLEVEL% neq 0 (
    echo ❌ Node.js not found. Please install Node.js first.
    echo Download from: https://nodejs.org/
    pause
    exit /b 1
)

REM Check if Vercel CLI is installed
vercel --version >nul 2>&1
if %ERRORLEVEL% neq 0 (
    echo ❌ Vercel CLI not found. Installing...
    npm install -g vercel
)

REM Check if logged in to Vercel
echo 🔐 Checking Vercel authentication...
vercel whoami >nul 2>&1
if %ERRORLEVEL% neq 0 (
    echo 🔑 Please log in to Vercel:
    vercel login
)

echo 📦 Installing dependencies...
npm install

echo 🔧 Checking environment variables...
if not exist .env.local (
    echo ⚠️  .env.local not found. Creating template...
    (
    echo # Database Configuration
    echo DB_HOST=your_database_host
    echo DB_USER=your_database_username
    echo DB_PASSWORD=your_database_password
    echo DB_NAME=your_database_name
    echo.
    echo # Update these values with your actual database credentials
    ) > .env.local
    echo 📝 Please update .env.local with your database credentials
    echo    Then run this script again
    pause
    exit /b 1
)

echo 🌐 Deploying to Vercel...
vercel --prod

echo ✅ Deployment complete!
echo.
echo 📋 Next Steps:
echo 1. Add environment variables in Vercel dashboard:
echo    - Go to your project settings
echo    - Add DB_HOST, DB_USER, DB_PASSWORD, DB_NAME
echo 2. Set up your database tables
echo 3. Test your contact form
echo.
echo 🎉 Your website is now live on Vercel!
pause
