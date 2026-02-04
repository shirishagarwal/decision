# 🚀 QUICK START GUIDE - DecisionVault

## ⚡ 5-Minute Setup

### 1. CREATE DATABASE (2 minutes)
- Login to Hostinger → Databases → MySQL
- Create new database → Note credentials
- Open phpMyAdmin → Import `database.sql`

### 2. GET API KEYS (2 minutes)

**Google OAuth:**
1. https://console.cloud.google.com/
2. Create Project → Enable Google+ API
3. Create OAuth Client ID (Web app)
4. Add redirect URI: `https://yourdomain.com/auth/callback.php`
5. Copy Client ID & Secret

**Gemini API:**
1. https://makersuite.google.com/app/apikey
2. Create API Key → Copy it

### 3. CONFIGURE (1 minute)
Edit `config.php`:
```php
define('DB_NAME', 'your_db_name');
define('DB_USER', 'your_db_user');
define('DB_PASS', 'your_db_pass');

define('GOOGLE_CLIENT_ID', 'paste-here');
define('GOOGLE_CLIENT_SECRET', 'paste-here');
define('GOOGLE_REDIRECT_URI', 'https://yourdomain.com/auth/callback.php');

define('GEMINI_API_KEY', 'paste-here');
define('APP_URL', 'https://yourdomain.com');
```

### 4. UPLOAD & TEST
- Upload all files to `public_html`
- Visit your domain
- Click "Continue with Google"
- ✅ Done!

## 🆘 Common Issues

**"Database error"** → Check credentials in config.php  
**"OAuth error"** → Verify redirect URI matches exactly  
**"404 errors"** → Enable mod_rewrite, check .htaccess  
**"AI not working"** → Check Gemini API key  

## 📁 File Structure
```
public_html/
├── index.php           (Login page)
├── config.php          (⚠️ EDIT THIS)
├── database.sql        (Import to MySQL)
├── dashboard.php       (Main app)
├── ai-assistant.php    (AI features)
├── settings.php        (Settings)
├── .htaccess          (Apache config)
├── auth/
│   ├── google.php
│   ├── callback.php
│   └── logout.php
└── api/
    ├── decisions.php
    ├── stats.php
    └── ai-chat.php
```

## ✅ Checklist
- [ ] Database created and SQL imported
- [ ] Google OAuth configured
- [ ] Gemini API key added
- [ ] config.php updated with YOUR values
- [ ] Files uploaded to Hostinger
- [ ] HTTPS/SSL enabled
- [ ] Tested login flow

## 🎯 Next Steps
1. Login with Google
2. Click "AI Assistant"
3. Try: "Help me plan a vacation"
4. Watch AI generate options!

## 💡 Need Help?
1. Read full README.md
2. Check Hostinger docs
3. Verify all credentials
4. Test each API separately

---
You're 5 minutes away from AI-powered decisions! 🚀
