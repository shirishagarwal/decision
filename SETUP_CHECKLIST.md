# DecisionVault - Complete Setup Checklist

## 📋 PRE-DEPLOYMENT CHECKLIST

### ✅ Before You Start
- [ ] Hostinger account ready
- [ ] Domain configured (SSL/HTTPS required!)
- [ ] Google account for OAuth setup
- [ ] Credit card ready for Google Cloud (free tier available)

---

## 🗄️ STEP 1: DATABASE SETUP

### Create Database in Hostinger
1. [ ] Login to Hostinger hPanel
2. [ ] Navigate to: **Databases → MySQL Databases**
3. [ ] Click "Create new database"
4. [ ] Choose a name: e.g., `u123456789_decisionvault`
5. [ ] Click "Create"

### Note Your Credentials
```
Database Host: ________________ (usually: localhost)
Database Name: ________________
Database User: ________________
Database Pass: ________________
```

### Import SQL Schema
6. [ ] Go to **Databases → phpMyAdmin**
7. [ ] Select your database from left sidebar
8. [ ] Click **Import** tab
9. [ ] Click "Choose File" → select `database.sql`
10. [ ] Click **Go** button at bottom
11. [ ] Wait for "Import has been successfully finished" message

**VERIFY:** You should see 13 tables created (users, workspaces, decisions, etc.)

---

## 🔑 STEP 2: GOOGLE OAUTH SETUP

### Create Google Cloud Project
1. [ ] Go to: https://console.cloud.google.com/
2. [ ] Click project dropdown → "New Project"
3. [ ] Name: "DecisionVault" → Click "Create"
4. [ ] Wait for project creation (30 seconds)
5. [ ] Select your new project from dropdown

### Enable Google+ API
6. [ ] Go to: **APIs & Services → Library**
7. [ ] Search: "Google+ API"
8. [ ] Click on it → Click "Enable"
9. [ ] Wait for activation

### Configure OAuth Consent Screen
10. [ ] Go to: **APIs & Services → OAuth consent screen**
11. [ ] Choose "External" → Click "Create"
12. [ ] Fill in:
    - App name: `DecisionVault`
    - User support email: `your@email.com`
    - Developer contact: `your@email.com`
13. [ ] Click "Save and Continue"
14. [ ] Skip "Scopes" → Click "Save and Continue"
15. [ ] Add test users (your email) → Click "Save and Continue"
16. [ ] Review → Click "Back to Dashboard"

### Create OAuth Credentials
17. [ ] Go to: **APIs & Services → Credentials**
18. [ ] Click: "Create Credentials" → "OAuth 2.0 Client ID"
19. [ ] Application type: **Web application**
20. [ ] Name: `DecisionVault Web Client`
21. [ ] Authorized redirect URIs → Click "Add URI"
22. [ ] Enter EXACTLY: `https://yourdomain.com/auth/callback.php`
    - ⚠️ Replace "yourdomain.com" with YOUR domain
    - ⚠️ Must be HTTPS (not http)
    - ⚠️ Include /auth/callback.php path
23. [ ] Click "Create"

### Save Your Credentials
```
Client ID: ________________________________.apps.googleusercontent.com
Client Secret: ________________________________
```

**IMPORTANT:** Copy these NOW - you need them in config.php!

---

## 🤖 STEP 3: GEMINI API SETUP

### Get Gemini API Key
1. [ ] Go to: https://makersuite.google.com/app/apikey
2. [ ] Sign in with Google
3. [ ] Click "Create API Key"
4. [ ] Select your Cloud project (or create new)
5. [ ] Click "Create API key in existing project"
6. [ ] Copy the API key immediately

### Save Your Key
```
Gemini API Key: ________________________________
```

**NOTE:** Free tier includes 15 requests/minute, 1M requests/day

---

## ⚙️ STEP 4: CONFIGURE APPLICATION

### Edit config.php
1. [ ] Extract `decisionvault-saas.zip`
2. [ ] Open `config.php` in text editor
3. [ ] Update DATABASE section:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'your_database_name');    // From Step 1
define('DB_USER', 'your_database_user');    // From Step 1
define('DB_PASS', 'your_database_pass');    // From Step 1
```

4. [ ] Update GOOGLE OAUTH section:
```php
define('GOOGLE_CLIENT_ID', 'paste-your-client-id-here');
define('GOOGLE_CLIENT_SECRET', 'paste-your-client-secret-here');
define('GOOGLE_REDIRECT_URI', 'https://yourdomain.com/auth/callback.php');
```

5. [ ] Update GEMINI API section:
```php
define('GEMINI_API_KEY', 'paste-your-gemini-key-here');
```

6. [ ] Update APPLICATION section:
```php
define('APP_URL', 'https://yourdomain.com');  // No trailing slash!
define('APP_ENV', 'production');
```

7. [ ] Save file

---

## 📤 STEP 5: UPLOAD TO HOSTINGER

### Using File Manager
1. [ ] Login to Hostinger hPanel
2. [ ] Go to **Files → File Manager**
3. [ ] Navigate to `public_html` (or your domain folder)
4. [ ] Click "Upload" button
5. [ ] Select ALL files from extracted folder:
   - index.php
   - config.php (the edited one!)
   - dashboard.php
   - ai-assistant.php
   - settings.php
   - database.sql
   - .htaccess
   - auth/ folder
   - api/ folder
6. [ ] Wait for upload to complete
7. [ ] Verify all files are uploaded

**OR Using FTP:**
1. [ ] Get FTP credentials from Hostinger
2. [ ] Use FileZilla or similar
3. [ ] Connect to your server
4. [ ] Upload all files to `public_html`

### Verify Upload
8. [ ] In File Manager, enable "Show Hidden Files"
9. [ ] Confirm `.htaccess` is present
10. [ ] Check folder structure matches:
```
public_html/
├── index.php
├── config.php
├── dashboard.php
├── ai-assistant.php
├── auth/
└── api/
```

---

## 🧪 STEP 6: TEST YOUR INSTALLATION

### Test 1: Main Page Loads
1. [ ] Visit: `https://yourdomain.com`
2. [ ] Should see: Login page with Google button
3. [ ] If error: Check file upload completed

### Test 2: Database Connection
1. [ ] Click "Continue with Google"
2. [ ] If "Database connection failed":
   - [ ] Re-check config.php credentials
   - [ ] Verify database exists in phpMyAdmin
   - [ ] Check DB_HOST is correct (usually 'localhost')

### Test 3: Google OAuth
1. [ ] Click "Continue with Google"
2. [ ] Should redirect to Google login
3. [ ] If error "redirect_uri_mismatch":
   - [ ] Check GOOGLE_REDIRECT_URI in config.php
   - [ ] Verify it EXACTLY matches Google Console
   - [ ] Ensure using HTTPS (not HTTP)

### Test 4: Complete Login Flow
1. [ ] Login with Google account
2. [ ] Authorize DecisionVault
3. [ ] Should redirect to Dashboard
4. [ ] Should see welcome screen with 0 decisions

### Test 5: AI Assistant
1. [ ] Click "AI Assistant" button
2. [ ] Click example: "Plan vacation"
3. [ ] Should see AI response
4. [ ] If "AI service unavailable":
   - [ ] Check GEMINI_API_KEY in config.php
   - [ ] Verify API key is active in Google AI Studio
   - [ ] Check you haven't exceeded quota

---

## ✅ POST-DEPLOYMENT CHECKLIST

### Security
- [ ] SSL/HTTPS is enabled (green lock in browser)
- [ ] config.php is NOT publicly downloadable
- [ ] Test in private/incognito window
- [ ] Change default demo user password (if kept)

### Functionality
- [ ] Can login with Google
- [ ] Can access dashboard
- [ ] AI Assistant responds
- [ ] Can create decision manually
- [ ] Can view created decisions

### Performance
- [ ] Page loads in < 3 seconds
- [ ] No console errors (F12)
- [ ] Mobile responsive works

---

## 🐛 TROUBLESHOOTING GUIDE

### Problem: "Database connection failed"
**Solution:**
1. Open phpMyAdmin → Can you see your database?
2. Test connection manually in config.php
3. Check DB_USER has permissions
4. Try 'localhost' vs '127.0.0.1' for DB_HOST

### Problem: "redirect_uri_mismatch"
**Solution:**
1. Google Console → Your project → Credentials
2. Edit OAuth client
3. Authorized redirect URIs must match EXACTLY:
   - `https://yourdomain.com/auth/callback.php`
   - NOT http:// (must be https://)
   - NOT missing /auth/callback.php
   - NOT extra slashes or spaces

### Problem: "AI not responding"
**Solution:**
1. Check browser console (F12) for errors
2. Verify GEMINI_API_KEY is correct
3. Visit: https://makersuite.google.com/app/apikey
4. Check quota hasn't been exceeded
5. Try regenerating API key

### Problem: "Page not found" / ".php showing"
**Solution:**
1. Check .htaccess file uploaded
2. Verify mod_rewrite enabled on server
3. Hostinger: Should be enabled by default
4. Try renaming .htaccess temporarily

### Problem: "Session errors"
**Solution:**
1. Check server has write permissions
2. PHP sessions enabled
3. Clear browser cookies
4. Try different browser

---

## 📞 GETTING HELP

If stuck after following this guide:

1. **Check Logs:**
   - Hostinger: Error logs in hPanel
   - Browser: Console (F12)
   - PHP: Check error_log file

2. **Verify Credentials:**
   - Double-check all API keys
   - Ensure no extra spaces
   - Try regenerating keys

3. **Test Components:**
   - Database: Can you login to phpMyAdmin?
   - OAuth: Can you reach Google login?
   - API: Try test request to Gemini

4. **Documentation:**
   - Google OAuth: https://developers.google.com/identity/protocols/oauth2
   - Gemini API: https://ai.google.dev/docs
   - Hostinger: https://support.hostinger.com

---

## 🎉 SUCCESS!

If all checkboxes are ticked and tests pass:

✅ **You're live!** DecisionVault is running!

**Next Steps:**
1. Make your first decision with AI
2. Invite team members
3. Customize workspace settings
4. Set up regular backups

**Remember:**
- Backup database weekly
- Monitor API usage/costs
- Keep config.php secure
- Update SSL certificate before expiry

---

**Installation Date:** _______________
**Your Domain:** _______________
**Database Name:** _______________

**Status:** [ ] Ready to Launch! 🚀
