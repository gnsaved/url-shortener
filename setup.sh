#!/bin/bash

echo "🚀 T2M URL Shortener - Setup Script"
echo "===================================="
echo ""

# Create data directory if it doesn't exist
if [ ! -d "data" ]; then
    echo "📁 Creating data directory..."
    mkdir -p data
    chmod 755 data
fi

# Create empty urls.json if it doesn't exist
if [ ! -f "data/urls.json" ]; then
    echo "📝 Initializing urls.json..."
    echo "[]" > data/urls.json
    chmod 644 data/urls.json
fi

# Create assets directory if needed
if [ ! -d "assets" ]; then
    echo "📁 Creating assets directory..."
    mkdir -p assets
fi

echo ""
echo "✅ Setup complete!"
echo ""
echo "Next steps:"
echo "1. Configure your web server (Apache/Nginx)"
echo "2. Start your server: php -S localhost:8000"
echo "3. Visit: http://localhost:8000/login.php"
echo "4. Default login - Username: admin, Password: admin123"
echo ""
echo "📖 Check README.md for more details!"
