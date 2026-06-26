#!/bin/bash
set -e

# Clear configuration cache first to pick up new env values
if [ -f artisan ]; then
    echo "Clearing Laravel config cache..."
    # We use php8.5/default php to clear config since it's just a CLI utility command
    php artisan config:clear || true
fi

echo "==========================================="
echo "Step 1: Adding Ondřej Surý PHP PPA..."
echo "==========================================="
sudo add-apt-repository ppa:ondrej/php -y
sudo apt-get update

echo "==========================================="
echo "Step 2: Installing PHP 8.4 and core extensions..."
echo "==========================================="
sudo apt-get install -y php8.4 php8.4-cli php8.4-dev php8.4-xml php8.4-common php8.4-sqlite3 php8.4-mysql php8.4-mbstring php8.4-curl php8.4-zip

echo "==========================================="
echo "Step 3: Installing Microsoft SQL Server driver..."
echo "==========================================="
sudo apt-get install -y unixodbc-dev php-pear libpcre3-dev curl

# Since the php8.4-sqlsrv package from the PPA only installs sqlsrv.so (and misses pdo_sqlsrv.so),
# we compile pdo_sqlsrv using PECL for PHP 8.4 by downloading the package directly.
echo "Downloading pdo_sqlsrv PECL package..."
curl -L -o /tmp/pdo_sqlsrv-5.12.0.tgz https://pecl.php.net/get/pdo_sqlsrv-5.12.0.tgz

echo "Compiling pdo_sqlsrv via PECL..."
sudo pecl install -f /tmp/pdo_sqlsrv-5.12.0.tgz

# Ensure both sqlsrv.so and pdo_sqlsrv.so are enabled
echo "extension=sqlsrv.so" | sudo tee /etc/php/8.4/mods-available/sqlsrv.ini
echo "extension=pdo_sqlsrv.so" | sudo tee /etc/php/8.4/mods-available/pdo_sqlsrv.ini
sudo phpenmod -v 8.4 sqlsrv pdo_sqlsrv

echo "==========================================="
echo "Step 4: Verifying SQL Server driver for PHP 8.4..."
echo "==========================================="
php8.4 -m | grep sqlsrv

echo "==========================================="
echo "Step 5: Testing database connection..."
echo "==========================================="
# We run config:clear using php8.4 to ensure it uses PHP 8.4 to parse any config files
php8.4 artisan config:clear
php8.4 artisan db:show


