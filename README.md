# T2M URL Shortener 🔗

A minimalist, plug-and-play URL shortener built with PHP that uses JSON files for storage - no database required!

## Features ✨

- 🎯 **Simple & Clean Dashboard** - Modern UI matching professional designs
- 📊 **Click Analytics** - Track every click with detailed logs
- 🎨 **QR Code Generation** - Auto-generate QR codes for short URLs
- 🔐 **Enhanced Security** - Input validation, XSS protection, and secure code generation
- 📱 **Responsive Design** - Works on all devices
- 💾 **JSON Storage** - No database setup needed
- ✏️ **Custom Short Codes** - Choose your own codes or auto-generate
- ⏰ **Expiration Dates** - Optional URL expiry
- 📈 **Share Integration** - Facebook, Twitter sharing buttons
- 🗑️ **URL Management** - Edit and delete URLs with confirmation
- 📥 **CSV Export** - Export all URLs and statistics
- 🌐 **Dynamic Base URLs** - Auto-detects installation path (root, subfolder, subdomain)

## Screenshots 📸

### Dashboard
![Dashboard](screenshots/dashboard.png)
*Main dashboard showing URL list with click statistics and management options*

### Login Page
![Login](screenshots/login.png)
*Simple and secure authentication interface*

### Create Short URL
![Create URL](screenshots/create-url.png)
*Easy URL creation with optional custom codes*

### Analytics
![Analytics](screenshots/analytics.png)
*Detailed click analytics and visitor tracking*

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
RewriteBase /

# Change RewriteBase if installed in subfolder:
# RewriteBase /url-shortener/

RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^([a-zA-Z0-9_-]+)$ r.php?code=$1 [L,QSA]
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

6. **Run configuration helper (optional)**
- Visit: `http://localhost:8000/config-helper.php`
- This tool auto-detects your installation path and shows correct configuration

## File Structure 📁

```
url-shortener/
├── index.php             # Main dashboard with inline documentation
├── login.php             # Authentication page
├── logout.php            # Logout handler
├── create.php            # URL creation with auto-detection and security
├── r.php                 # Redirect handler
├── analytics.php         # Analytics page
├── qr.php                # QR code generator
├── edit.php              # Edit existing URLs
├── delete.php            # Delete URLs with confirmation
├── export.php            # Export URLs to CSV
├── config-helper.php     # Configuration diagnostic tool
├── data/
│   ├── urls.json         # URL storage (auto-created)
│   └── deleted_urls.json # Backup of deleted URLs
├── assets/
│   ├── style.css         # Optimized styling
│   └── script.js         # JavaScript interactions
├── screenshots/          # Application screenshots
│   ├── dashboard.png
│   ├── login.png
│   ├── create-url.png
│   └── analytics.png
└── README.md             # This file
```

## Core Features 🔧

### URL Management

**Create URLs:**
1. Log in to the dashboard
2. Click "+ Create Short URL"
3. Enter your long URL
4. (Optional) Enter a custom code
5. Click "Create Short URL"

**Edit URLs:**
1. Click "Edit" next to any URL
2. Modify the long URL or custom code
3. Click "Save Changes"
4. Statistics are preserved

**Delete URLs:**
1. Click "Delete" next to any URL
2. Review details on confirmation page
3. Click "Yes, Delete URL"
4. Deleted URLs are backed up to `deleted_urls.json`

**Export Data:**
1. Click "Export to CSV" button
2. Download includes all URLs and statistics
3. Summary section with totals and averages

### URL Redirection

When someone visits `http://yourdomain.com/abc123`:
1. The web server routes to `r.php?code=abc123`
2. `r.php` looks up the code in `urls.json`
3. Increments click counter
4. Logs click data (timestamp, IP, user agent, referer)
5. Redirects to the long URL

### Analytics

Access detailed analytics for each URL:
- Total clicks
- Last clicked timestamp
- Recent visitor logs (last 50 clicks)
- User agent information
- Referrer sources

### Data Storage

All URLs are stored in `data/urls.json`:

```json
[
  {
    "id": "unique-id-123",
    "code": "abc123",
    "long_url": "https://example.com/very/long/url",
    "short_url": "http://yourdomain.com/abc123",
    "base_url": "http://yourdomain.com",
    "clicks": 42,
    "created_at": "2024-01-15 10:30:00",
    "updated_at": "2024-01-20 15:30:00",
    "expires_at": null,
    "last_clicked": "2024-01-20 14:22:10",
    "created_by": "admin",
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

## Security Features 🔒

### Enhanced Input Validation
- URL scheme validation (HTTP/HTTPS only)
- Comprehensive input sanitization using `filter_var`
- Custom code format validation (3-50 characters)
- Reserved keyword protection (admin, api, etc.)
- Prevention of internal URL shortening

### XSS Protection
- All output is properly escaped
- HTML special characters sanitized
- Safe handling of user input

### Secure Code Generation
- Uses cryptographically secure `random_int()`
- Ensures uniqueness with retry mechanism
- 6-character codes from alphanumeric set

### CSRF Protection (Prepared)
- Framework ready for CSRF token implementation
- Uncomment code in `create.php` to enable

## Customization 🎨

### Change Login Credentials

Edit `login.php` line 8-9:
```php
if ($username === 'your_username' && $password === 'your_password') {
```

**For production**, implement proper password hashing:
```php
// Hash password
$hashed = password_hash('your_password', PASSWORD_DEFAULT);

// Verify
if (password_verify($password, $hashed)) {
    // Login successful
}
```

### Configure Base URL

The system automatically detects your installation path! Works in:
- **Root directory**: `https://domain.com/code`
- **Subfolder**: `https://domain.com/folder/code`
- **Subdomain**: `https://short.domain.com/code`

To verify configuration, visit: `config-helper.php`

### Styling

Edit `assets/style.css` to customize:
- Color scheme (default: teal/green #10b981)
- Fonts and typography
- Layout and spacing
- Responsive breakpoints

The CSS is optimized with:
- Logical section organization
- Utility classes for reusability
- Reduced specificity conflicts
- Performance optimizations

## Advanced Usage 📚

### CSV Export Format

Exported CSV includes:
- URL ID and short code
- Full short URL and target URL
- Total clicks and creation date
- Last clicked timestamp
- Expiration date and status
- Summary statistics section

### QR Code Generation

Generate QR codes in multiple formats:
- **SVG**: Vector format for scaling
- **PNG**: Raster format via Google Charts API

Access: Click "SVG" or "PNG" next to any URL

### Click Analytics

Track comprehensive visitor data:
- Timestamp of each click
- IP address (for analytics)
- User agent (browser/device info)
- Referrer (traffic source)

View: Click "View" in Analytics column

## Deployment 🚀

### cPanel Hosting

1. Upload files to `public_html` or subdirectory
2. Create `data` folder with permissions 755
3. Update `.htaccess` RewriteBase if in subfolder
4. Visit `config-helper.php` to verify configuration
5. Change default login credentials

### VPS/Dedicated Server

1. Configure Apache or Nginx
2. Set proper file permissions
3. Enable mod_rewrite (Apache)
4. Configure SSL certificate
5. Set up automated backups for `data/` folder

### Subdomain Installation

1. Point subdomain to installation directory
2. Set RewriteBase to `/` in `.htaccess`
3. System auto-detects subdomain setup
4. No additional configuration needed

## Troubleshooting 🔍

### URLs not redirecting
- Check `.htaccess` is present and loaded (Apache)
- Verify mod_rewrite is enabled
- Ensure RewriteBase matches your installation path
- Use `config-helper.php` to diagnose

### Permission errors
```bash
chmod 755 data/
chmod 644 data/urls.json
```

### Wrong base URL in generated links
1. Visit `config-helper.php`
2. Check detected base URL
3. Update `.htaccess` RewriteBase if needed
4. Verify `create.php` is the updated version with auto-detection

### 404 errors on short URLs
- Verify `.htaccess` exists in root directory
- Check RewriteBase directive matches folder
- Test: `http://yourdomain.com/r.php?code=test`
- If r.php works, issue is with .htaccess

### JSON file corruption
Backup and recreate:
```bash
mv data/urls.json data/urls.json.backup
echo "[]" > data/urls.json
```

## Performance Optimization ⚡

### CSS Optimization
- Organized into logical sections
- Removed duplicate selectors
- Minimized specificity conflicts
- Optimized animations for GPU acceleration
- Utility classes for common patterns

### PHP Optimization
- Efficient JSON parsing
- Minimal file I/O operations
- Proper error handling
- Prepared statements for future database migration

### Caching (Optional)
Add to `.htaccess` for static assets:
```apache
<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType text/css "access plus 1 month"
    ExpiresByType application/javascript "access plus 1 month"
    ExpiresByType image/png "access plus 1 year"
</IfModule>
```

## Future Enhancements 🚀

Ideas for extending this project:

### Database Integration
- MySQL/PostgreSQL support
- Better scalability for high traffic
- Advanced querying capabilities

### API Endpoints
- RESTful API for programmatic access
- API key authentication
- Rate limiting per API key

### Advanced Features
- Password-protected URLs
- URL categories and tags
- Bulk URL import from CSV
- Custom domains support
- User management with roles
- Advanced analytics with charts
- Email notifications
- Webhook integrations

## Security Best Practices 🛡️

For production deployment:

1. **Authentication**
   - Use password hashing (bcrypt/argon2)
   - Implement session timeout
   - Add CSRF protection
   - Enable two-factor authentication

2. **Input Validation**
   - Validate all user inputs
   - Sanitize output
   - Use prepared statements if migrating to database

3. **File Permissions**
   - Restrict `data/` directory (755)
   - Protect JSON files (644)
   - Keep `.htaccess` readable (644)

4. **HTTPS**
   - Always use SSL certificate
   - Force HTTPS redirect
   - Enable HSTS header

5. **Rate Limiting**
   - Limit URL creation per user
   - Prevent brute force attacks
   - Implement CAPTCHA for public instances

## License 📄

MIT License - Feel free to use and modify!

## Contributing 🤝

Pull requests welcome! Areas for contribution:
- New features
- Bug fixes
- Documentation improvements
- Security enhancements
- Performance optimizations
- Translation/localization

## Credits 💫

Built as a minimalist URL shortener for easy deployment and learning purposes.

**Key Features:**
- Auto-detecting base URL system
- Enhanced security validation
- Comprehensive inline documentation
- Performance-optimized CSS
- Full CRUD operations
- CSV export functionality

---

**Happy URL Shortening!** 🚀

## Quick Links

- [Installation Guide](#installation)
- [Configuration Helper](config-helper.php)
- [Troubleshooting](#troubleshooting-🔍)
- [Security Best Practices](#security-best-practices-🛡️)
- [Contributing](#contributing-🤝)
