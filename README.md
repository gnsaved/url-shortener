# T2M URL Shortener 🔗

A minimalist, plug-and-play URL shortener built with PHP that uses JSON files for storage - no database required!

## Features ✨

- 🎯 **Simple & Clean Dashboard** - Modern UI matching professional designs
- 📊 **Click Analytics** - Track every click with detailed logs
- 🎨 **QR Code Generation** - Auto-generate QR codes for short URLs
- 🔐 **Basic Authentication** - Simple login system
- 📱 **Responsive Design** - Works on all devices
- 💾 **JSON Storage** - No database setup needed
- ✏️ **Custom Short Codes** - Choose your own codes or auto-generate
- ⏰ **Expiration Dates** - Optional URL expiry
- 📈 **Share Integration** - Facebook, Twitter sharing buttons

## Quick Start 🚀

### Requirements
- PHP 7.4 or higher
- Web server (Apache/Nginx) with mod_rewrite

### Installation

1. **Clone or download this repository**
```bash
git clone https://github.com/yourusername/url-shortener.git
cd url-shortener
```

2. **Set up directory permissions**
```bash
mkdir -p data
chmod 755 data
chmod 644 data/urls.json 2>/dev/null || true
```

3. **Configure your web server**

**For Apache (.htaccess included):**
```apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^([a-zA-Z0-9_-]+)$ r.php?code=$1 [L]
```

**For Nginx:**
```nginx
location / {
    try_files $uri $uri/ /r.php?code=$uri&$args;
}
```

4. **Start your web server**
```bash
# Using PHP built-in server (for testing)
php -S localhost:8000
```

5. **Access the application**
- Open: `http://localhost:8000/login.php`
- Default credentials:
  - Username: `admin`
  - Password: `admin123`

## File Structure 📁

```
url-shortener/
├── index.php          # Main dashboard
├── login.php          # Authentication page
├── logout.php         # Logout handler
├── create.php         # URL creation handler
├── r.php              # Redirect handler
├── analytics.php      # Analytics page
├── qr.php             # QR code generator
├── data/
│   └── urls.json      # URL storage (auto-created)
├── assets/
│   ├── style.css      # Styling
│   └── script.js      # JavaScript
└── README.md          # This file
```

## How It Works 🔧

### Creating Short URLs

1. Log in to the dashboard
2. Click "+ Create Short URL"
3. Enter your long URL
4. (Optional) Enter a custom code
5. Click "Create Short URL"

Your short URL will be: `http://yourdomain.com/CODE`

### URL Redirection

When someone visits `http://yourdomain.com/abc123`:
1. The web server routes to `r.php?code=abc123`
2. `r.php` looks up the code in `urls.json`
3. Increments click counter
4. Logs click data (timestamp, IP, user agent, referer)
5. Redirects to the long URL

### Data Storage

All URLs are stored in `data/urls.json`:

```json
[
  {
    "id": "unique-id-123",
    "code": "abc123",
    "long_url": "https://example.com/very/long/url",
    "short_url": "http://yourdomain.com/abc123",
    "clicks": 42,
    "created_at": "2024-01-15 10:30:00",
    "expires_at": null,
    "last_clicked": "2024-01-20 14:22:10",
    "click_log": [
      {
        "timestamp": "2024-01-20 14:22:10",
        "user_agent": "Mozilla/5.0...",
        "ip": "192.168.1.1",
        "referer": "https://google.com"
      }
    ]
  }
]
```

## Customization 🎨

### Change Login Credentials

Edit `login.php` line 8-9:
```php
if ($username === 'your_username' && $password === 'your_password') {
```

### Change Domain

The system auto-detects your domain. For custom domains, edit `create.php` around line 48.

### Styling

Edit `assets/style.css` to customize colors, fonts, and layout.

### Features to Add

Some ideas for extending this project:
- **Database support** - MySQL/PostgreSQL integration
- **Bulk URL import** - CSV upload
- **API endpoints** - RESTful API
- **Advanced analytics** - Charts and graphs
- **Password-protected URLs** - Add access control
- **User management** - Multiple users with different roles
- **URL categories** - Organize with tags
- **Custom domains** - Multiple domain support

## Security Notes 🔒

This is a basic implementation. For production use, consider:

1. **Better Authentication** - Use password hashing (bcrypt)
2. **CSRF Protection** - Add tokens to forms
3. **Input Validation** - Sanitize all inputs
4. **Rate Limiting** - Prevent abuse
5. **HTTPS** - Always use SSL
6. **File Permissions** - Restrict `data/` directory

## Troubleshooting 🔍

### URLs not redirecting
- Check `.htaccess` is loaded (Apache)
- Verify mod_rewrite is enabled
- Check web server configuration

### Permission errors
```bash
chmod 755 data/
chmod 644 data/urls.json
```

### JSON file corruption
Backup and recreate:
```bash
mv data/urls.json data/urls.json.backup
echo "[]" > data/urls.json
```

## License 📄

MIT License - Feel free to use and modify!

## Contributing 🤝

Pull requests welcome! Feel free to:
- Add features
- Fix bugs
- Improve documentation
- Enhance security

## Credits 💫

Built as a minimalist URL shortener for easy deployment and learning purposes.

---

**Happy URL Shortening!** 🚀
