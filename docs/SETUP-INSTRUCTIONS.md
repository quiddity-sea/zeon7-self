# Zeon7 Development Setup Instructions


## Apache Virtual Host Setup

### 1. Add Configuration to Apache

**Option A: Append to existing zeon7-ssl.conf**

Add the virtual host block from `docs/apache-config-self.conf` to your existing `/etc/apache2/sites-available/zeon7-ssl.conf` file in WSL.

**Option B: Create separate config file**

```bash
# In WSL
sudo cp /mnt/e/Dev/Projects/self/docs/apache-config-self.conf /etc/apache2/sites-available/self-zeon7.conf
sudo a2ensite self-zeon7.conf
```

### 2. Add to Windows Hosts File

Edit `C:\Windows\System32\drivers\etc\hosts` (as Administrator):

```
127.0.0.1    self.zeon7.com
```

### 3. Reload Apache

```bash
# In WSL
sudo systemctl reload apache2
```

### 4. Verify Symlink

Ensure this project is symlinked correctly:

```bash
# In WSL
ls -la /var/www/zeon7.com/self
# Should point to: /mnt/e/Dev/Projects/self
```

**If symlink doesn't exist, create it:**

```bash
# In WSL
sudo ln -s /mnt/e/Dev/Projects/self /var/www/zeon7.com/self
```

---

## Database Setup

### 1. Create Database

```bash
# In WSL
wsl mysql -u root -p
```

Then run:

```sql
CREATE DATABASE IF NOT EXISTS zeon7_self_dev CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
GRANT ALL PRIVILEGES ON zeon7_self_dev.* TO 'root'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### 2. Run Migration

```bash
# In WSL
wsl mysql -u root -p zeon7_self_dev < /mnt/e/Dev/Projects/self/docs/database/migration.sql
```

### 3. Verify Tables

```bash
# In WSL
wsl mysql -u root -p -e "USE zeon7_self_dev; SHOW TABLES;"
```

**Expected output:**
```
+-------------------------+
| Tables_in_zeon7_self_dev|
+-------------------------+
| api_usage               |
| gemini_log              |
| image_prompt            |
| instruction_set         |
| knowledge_chunk         |
| knowledge_doc           |
| lore                    |
| posts                   |
+-------------------------+
```

---

## Environment Variables

The `.env` file is already configured with:
- Database: `zeon7_self_dev`
- User: `root`
- Password: `F0reverb0x#2o25md`

---

## Testing Access

### Test Apache
- Visit: `https://self.zeon7.com`
- Should see files from `/public` directory

### Test PHP
Create a simple test file:

```bash
# In WSL
echo "<?php phpinfo(); ?>" > /var/www/zeon7.com/self/public/test.php
```

Visit: `https://self.zeon7.com/test.php`

---

## API Endpoint Structure

Once backend is built:
- **Admin APIs**: `https://self.zeon7.com/api/*`
- **Public APIs**: `https://self.zeon7.com/api/chat`, etc.
- **Admin Pages**: `https://self.zeon7.com/admin/zeon7/`
- **Public Pages**: `https://self.zeon7.com/public/noise/`

---

## Troubleshooting

### Apache won't reload
```bash
# Check config syntax
sudo apache2ctl configtest

# Check Apache error logs
sudo tail -f /var/log/apache2/self-error.log
```

### Database connection fails
- Verify MySQL is running: `wsl systemctl status mysql`
- Test credentials: `wsl mysql -u root -p`
- Check `.env` file matches database credentials

### Symlink issues
```bash
# Remove broken symlink
sudo rm /var/www/zeon7.com/self

# Recreate
sudo ln -s /mnt/e/Dev/Projects/self /var/www/zeon7.com/self

# Verify ownership
sudo chown -h www-data:www-data /var/www/zeon7.com/self
```
