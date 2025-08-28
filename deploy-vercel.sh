#!/bin/bash
# Vercel Deployment Script for TechCorp Solutions

echo "🚀 Starting Vercel Deployment Process..."
echo "======================================"

# Check if Vercel CLI is installed
if ! command -v vercel &> /dev/null; then
    echo "❌ Vercel CLI not found. Installing..."
    npm install -g vercel
fi

# Check if logged in to Vercel
echo "🔐 Checking Vercel authentication..."
vercel whoami > /dev/null 2>&1
if [ $? -ne 0 ]; then
    echo "🔑 Please log in to Vercel:"
    vercel login
fi

echo "📦 Installing dependencies..."
npm install

echo "🔧 Checking environment variables..."
if [ ! -f .env.local ]; then
    echo "⚠️  .env.local not found. Creating template..."
    cat > .env.local << EOF
# Database Configuration
DB_HOST=your_database_host
DB_USER=your_database_username  
DB_PASSWORD=your_database_password
DB_NAME=your_database_name

# Update these values with your actual database credentials
EOF
    echo "📝 Please update .env.local with your database credentials"
    echo "   Then run this script again"
    exit 1
fi

echo "🌐 Deploying to Vercel..."
vercel --prod

echo "✅ Deployment complete!"
echo ""
echo "📋 Next Steps:"
echo "1. Add environment variables in Vercel dashboard:"
echo "   - Go to your project settings"
echo "   - Add DB_HOST, DB_USER, DB_PASSWORD, DB_NAME"
echo "2. Set up your database tables"
echo "3. Test your contact form"
echo ""
echo "🎉 Your website is now live on Vercel!"
