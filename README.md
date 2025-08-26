# VPN Password Management Portal

This is a simple web portal for users to manage their VPN passwords, which are stored in a FreeRADIUS `radcheck` table. This project is a Laravel-based migration of an older, single-file PHP script.

## Installation Instructions (Ubuntu)

These instructions assume you have a working Ubuntu server with `sudo` access.

### 1. Install System Dependencies (PHP & Composer)

First, update your package list and install PHP, required PHP extensions, and Composer.

```bash
sudo apt-get update
sudo apt-get install -y php-cli php-mbstring php-xml php-curl unzip git
# Download and install Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
```

### 2. Clone the Repository

Clone this project from its Git repository.

```bash
git clone <your-repository-url>
cd <project-directory>
```

### 3. Install Project Dependencies

Install the PHP dependencies using Composer.

```bash
composer install --no-dev --optimize-autoloader
```

### 4. Configure Environment

Copy the example environment file and generate an application key.

```bash
cp .env.example .env
php artisan key:generate
```

Next, you must edit the `.env` file to set up your database and mail server credentials.

```ini
# .env

# --- Database Configuration ---
# Replace placeholders with your actual MySQL/MariaDB credentials
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_db_name_here
DB_USERNAME=your_db_user_here
DB_PASSWORD=your_db_pass_here

# --- Mailer Configuration ---
# Replace placeholders with your SMTP server credentials
MAIL_MAILER=smtp
MAIL_HOST=your_smtp_host_here
MAIL_PORT=your_smtp_port_here
MAIL_USERNAME=your_smtp_user_here
MAIL_PASSWORD=your_smtp_pass_here
MAIL_ENCRYPTION=tls # or ssl, etc.
MAIL_FROM_ADDRESS="noreply@example.com"
MAIL_FROM_NAME="${APP_NAME}"
```

### 5. Configure Web Server (Nginx Example)

Your web server must be configured to serve the application from the `public` directory. Here is an example Nginx server block:

```nginx
server {
    listen 80;
    server_name yourdomain.com;
    root /path/to/your/project/public;

    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock; # Adjust PHP version if needed
    }

    location ~ /\.ht {
        deny all;
    }
}
```
Remember to enable the site and restart Nginx.

### 6. Set Directory Permissions

The web server user (e.g., `www-data`) needs write permissions on the `storage` and `bootstrap/cache` directories.

```bash
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

### 7. Important: FreeRADIUS Service Restart

This application restarts the FreeRADIUS service after a password is changed or reset by executing `sudo systemctl restart freeradius`. For this to work, the web server user (`www-data`) must have passwordless `sudo` access specifically for this command.

To set this up, run `sudo visudo` and add the following line at the end of the file:

```
www-data ALL=(ALL) NOPASSWD: /usr/bin/systemctl restart freeradius
```

This is a critical step for the application to function correctly.

---

After completing these steps, the application should be accessible at the domain you configured.
