# DecisionVault - AI-Powered Decision Making SaaS

A complete SaaS application that helps individuals and teams make better decisions using AI assistance.

## 🚀 Features

- **AI-Powered Decision Assistant** - Google Gemini AI helps discover problems, generate options, and analyze pros/cons
- **Google OAuth Authentication** - Secure login with Google accounts
- **Workspace Management** - Create personal, family, or team workspaces
- **Decision Documentation** - Full context capture with options, pros, cons, and rationale
- **Analytics Dashboard** - Track decision-making patterns and success rates
- **Collaboration** - Team members can contribute to decisions
- **Tag System** - Organize decisions with custom tags

## 📋 Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache with mod_rewrite enabled
- cURL extension enabled
- SSL certificate (for production - required by Google OAuth)

## 🔧 Installation Instructions

### Step 1: Upload Files to Hostinger

1. Download the entire `decisionvault-saas` folder
2. Using Hostinger's File Manager or FTP:
   - Upload all files to your `public_html` directory (or subdomain folder)
   - Make sure `.htaccess` is uploaded (it may be hidden)

### Step 2: Create MySQL Database

1. Log into Hostinger's control panel (hPanel)
2. Go to **Databases → MySQL Databases**
3. Click "Create new database"
4. Note down:
   - Database name
   - Database username
   - Database password
   - Database host (usually `localhost`)

### Step 3: Import Database Schema

1. Go to **Databases → phpMyAdmin**
2. Select your newly created database
3. Click the **Import** tab
4. Choose `database.sql` file
5. Click **Go** to execute

The database tables will be created automatically.

### Step 4: Configure Google OAuth

1. Go to [Google Cloud Console](https://console.cloud.google.com/)
2. Create a new project or select existing one
3. Enable **Google+ API**:
   - Go to "APIs & Services → Library"
   - Search for "Google+ API"
   - Click Enable

4. Create OAuth 2.0 Credentials:
   - Go to "APIs & Services → Credentials"
   - Click "Create Credentials → OAuth 2.0 Client ID"
   - Application type: **Web application**
   - Name: DecisionVault
   - Authorized redirect URIs: `https://yourdomain.com/auth/callback.php`
   - Click Create

5. Copy your:
   - Client ID
   - Client Secret

### Step 5: Get Google Gemini API Key

1. Go to [Google AI Studio](https://makersuite.google.com/app/apikey)
2. Click "Create API Key"
3. Copy your API key

### Step 6: Update config.php

Open `config.php` and update the following:

```php
// Database Configuration
define('DB_HOST', 'localhost');           // Your database host
define('DB_NAME', 'your_database_name');  // Your database name
define('DB_USER', 'your_database_user');  // Your database username
define('DB_PASS', 'your_database_pass');  // Your database password

// Google OAuth Configuration
define('GOOGLE_CLIENT_ID', 'your-google-client-id.apps.googleusercontent.com');
define('GOOGLE_CLIENT_SECRET', 'your-google-client-secret');
define('GOOGLE_REDIRECT_URI', 'https://yourdomain.com/auth/callback.php');

// Google Gemini API Configuration
define('GEMINI_API_KEY', 'your-gemini-api-key-here');

// Application Configuration
define('APP_URL', 'https://yourdomain.com'); // Your domain (no trailing slash)
define('APP_ENV', 'production'); // Keep as 'production' for live site
```

### Step 7: Set Permissions (Important!)

Make sure these folders are writable:
```
chmod 755 /path/to/public_html
chmod 644 config.php (after editing)
```

If you need to create a logs folder:
```
mkdir logs
chmod 755 logs
```

### Step 8: Test Your Installation

1. Visit your domain: `https://yourdomain.com`
2. You should see the login page
3. Click "Continue with Google"
4. Authorize the application
5. You should be redirected to the dashboard

## 🎯 Using the Application

### First Time Setup

1. **Sign in with Google** - Use the login page
2. **Dashboard** - You'll see your personal workspace created automatically
3. **AI Assistant** - Click the "AI Assistant" button to start your first decision

### Making a Decision with AI

1. Click **AI Assistant** button
2. Describe your situation (e.g., "We need to choose a CRM for our sales team")
3. AI will ask clarifying questions to identify the core problem
4. Once problem is defined, AI generates 4-5 comprehensive options
5. Each option includes:
   - Pros and cons
   - Cost estimates
   - Feasibility assessment
   - AI recommendations
6. Select your preferred option
7. Click "Save Decision" to document it

### Dashboard Features

- **Stats Overview** - See total decisions, implemented count, average decision time
- **Recent Decisions** - Quick access to your documented decisions
- **Search & Filter** - Find decisions by category, status, or tags

## 🔐 Security Best Practices

1. **Always use HTTPS** - Google OAuth requires SSL
2. **Keep config.php secure** - Never commit to public repositories
3. **Regular backups** - Backup your database regularly via phpMyAdmin
4. **Update passwords** - Change default MySQL passwords
5. **Monitor logs** - Check error logs in Hostinger control panel

## 🐛 Troubleshooting

### "Database connection failed"
- Check database credentials in `config.php`
- Verify database exists in phpMyAdmin
- Ensure MySQL is running

### "Google OAuth error"
- Verify redirect URI matches exactly in Google Console
- Check HTTPS is enabled
- Confirm Client ID and Secret are correct

### "AI service unavailable"
- Verify Gemini API key is correct
- Check API quota limits in Google AI Studio
- Ensure cURL extension is enabled

### "Page not found" or ".php showing"
- Enable mod_rewrite in Apache
- Check `.htaccess` file is uploaded
- Verify file permissions

### Can't upload .htaccess file
- In File Manager, enable "Show hidden files"
- Or use FTP client which shows hidden files by default

## 📊 Database Tables Overview

- `users` - User accounts and profiles
- `workspaces` - Team/family workspaces
- `workspace_members` - Workspace access control
- `decisions` - Main decision records
- `options` - Decision options with pros/cons
- `tags` - Custom tags for organization
- `ai_conversations` - AI chat history
- `activity_log` - Audit trail

## 🔄 Updating the Application

1. Backup your database first!
2. Backup `config.php` (contains your credentials)
3. Upload new files (except config.php)
4. Run any new database migrations if provided

## 💡 Pro Tips

1. **Use descriptive titles** - Makes searching easier
2. **Tag consistently** - Create a tagging system for your team
3. **Review decisions** - Come back and update outcomes
4. **Export important decisions** - Use the export feature regularly

## 📞 Support

For issues or questions:
1. Check troubleshooting section above
2. Review Hostinger documentation
3. Check Google Cloud Console for OAuth issues
4. Verify Gemini API quota and usage

## 📄 License

Proprietary - All rights reserved

## 🎉 You're All Set!

Your DecisionVault installation is complete. Start making better decisions with AI assistance!

---

**Version:** 1.0.0  
**Last Updated:** January 2026
