# 🚀 Quick Start Guide - T2M URL Shortener

## Instant Setup (60 seconds!)

### Option 1: PHP Built-in Server (Easiest)

1. **Open terminal in the project folder**
2. **Run:**
   ```bash
   ./setup.sh
   php -S localhost:8000
   ```
3. **Open browser:** `http://localhost:8000/login.php`
4. **Login:**
   - Username: `admin`
   - Password: `admin123`

### Option 2: XAMPP/WAMP/MAMP

1. **Copy folder to:**
   - XAMPP: `C:\xampp\htdocs\url-shortener\`
   - WAMP: `C:\wamp64\www\url-shortener\`
   - MAMP: `/Applications/MAMP/htdocs/url-shortener/`

2. **Visit:** `http://localhost/url-shortener/login.php`

3. **Login with:** `admin` / `admin123`

## Your First Short URL

1. Click **"+ Create Short URL"**
2. Paste any long URL
3. (Optional) Add custom code
4. Click **"Create Short URL"**
5. ✅ Done! Share your short link

## Features You Can Use Right Now

- ✅ Create unlimited short URLs
- ✅ Custom short codes
- ✅ Click tracking & analytics
- ✅ QR code generation
- ✅ Social media sharing
- ✅ URL expiration dates

## Important Files

- `login.php` - Change password here (line 8-9)
- `data/urls.json` - All your URLs stored here
- `assets/style.css` - Customize colors/design
- `README.md` - Full documentation

## Troubleshooting

**Can't login?**
- Check credentials: admin/admin123
- Make sure PHP session is working

**URLs not redirecting?**
- For Apache: mod_rewrite must be enabled
- For Nginx: Configure location block (see README)

**Permission errors?**
```bash
chmod 755 data/
chmod 644 data/urls.json
```

## Next Steps

1. ✏️ Change login credentials in `login.php`
2. 🎨 Customize colors in `assets/style.css`
3. 🌐 Deploy to your server
4. 🔒 Set up HTTPS
5. 📊 Monitor your analytics

---

**Need help?** Check the full README.md for detailed instructions!
