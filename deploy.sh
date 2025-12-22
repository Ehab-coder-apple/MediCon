#!/bin/bash

# MediCon Deployment Script
# This script handles the deployment process for the MediCon application

set -e  # Exit on any error

echo "🚀 Starting MediCon Deployment..."

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Function to print colored output
print_status() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

print_success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Check if we're in the correct directory
if [ ! -f "artisan" ]; then
    print_error "artisan file not found. Please run this script from the Laravel project root."
    exit 1
fi

# Check environment
if [ "$1" = "production" ]; then
    ENV="production"
    print_warning "Deploying to PRODUCTION environment"
    read -p "Are you sure you want to continue? (y/N): " -n 1 -r
    echo
    if [[ ! $REPLY =~ ^[Yy]$ ]]; then
        print_error "Deployment cancelled"
        exit 1
    fi
else
    ENV="staging"
    print_status "Deploying to STAGING environment"
fi

# Step 1: Put application in maintenance mode
print_status "Putting application in maintenance mode..."
php artisan down --render="errors::503" --retry=60

# Step 2: Pull latest code (if using git)
if [ -d ".git" ]; then
    print_status "Pulling latest code from repository..."
    git pull origin main
fi

# Step 3: Install/Update Composer dependencies
print_status "Installing/Updating Composer dependencies..."
if [ "$ENV" = "production" ]; then
    composer install --no-dev --optimize-autoloader --no-interaction
else
    composer install --optimize-autoloader --no-interaction
fi

# Step 4: Install/Update NPM dependencies and build assets
print_status "Installing NPM dependencies and building assets..."
npm ci
npm run build

# Step 5: Clear all caches
print_status "Clearing application caches..."
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan event:clear

# Step 6: Run database migrations
print_status "Running database migrations..."
php artisan migrate --force

# Step 7: Optimize application for production
if [ "$ENV" = "production" ]; then
    print_status "Optimizing application for production..."
    
    # Cache configuration
    php artisan config:cache
    
    # Cache routes
    php artisan route:cache
    
    # Cache views
    php artisan view:cache
    
    # Cache events
    php artisan event:cache
    
    # Optimize autoloader
    composer dump-autoload --optimize --classmap-authoritative
fi

# Step 8: Create storage symlink if it doesn't exist
if [ ! -L "public/storage" ]; then
    print_status "Creating storage symlink..."
    php artisan storage:link
fi

# Step 9: Set proper permissions
print_status "Setting proper file permissions..."
chmod -R 755 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache public/storage 2>/dev/null || true

# Step 10: Queue restart (if using queues)
print_status "Restarting queue workers..."
php artisan queue:restart

# Step 11: Bring application back online
print_status "Bringing application back online..."
php artisan up

# Step 12: Run health checks
print_status "Running health checks..."

# Check if application is responding
if curl -f -s http://localhost > /dev/null; then
    print_success "Application is responding"
else
    print_warning "Application health check failed"
fi

# Check database connection
if php artisan tinker --execute="DB::connection()->getPdo(); echo 'Database connection: OK';" 2>/dev/null; then
    print_success "Database connection is working"
else
    print_error "Database connection failed"
fi

# Step 13: Clear OPcache (if available)
if command -v php-fpm >/dev/null 2>&1; then
    print_status "Clearing OPcache..."
    php artisan opcache:clear 2>/dev/null || true
fi

# Step 14: Send deployment notification (optional)
if [ -n "$SLACK_WEBHOOK_URL" ]; then
    print_status "Sending deployment notification..."
    curl -X POST -H 'Content-type: application/json' \
        --data "{\"text\":\"🚀 MediCon deployed successfully to $ENV environment\"}" \
        "$SLACK_WEBHOOK_URL" 2>/dev/null || true
fi

print_success "Deployment completed successfully!"
print_status "Application is now running in $ENV mode"

# Display deployment summary
echo
echo "📊 Deployment Summary:"
echo "======================"
echo "Environment: $ENV"
echo "Timestamp: $(date)"
echo "Git Commit: $(git rev-parse --short HEAD 2>/dev/null || echo 'N/A')"
echo "PHP Version: $(php -v | head -n1)"
echo "Laravel Version: $(php artisan --version)"
echo

# Show next steps
echo "🎯 Next Steps:"
echo "=============="
echo "1. Monitor application logs: tail -f storage/logs/laravel.log"
echo "2. Check queue status: php artisan queue:work"
echo "3. Monitor performance: php artisan horizon (if using Horizon)"
echo "4. Run tests: php artisan test"
echo

print_success "MediCon deployment completed! 🎉"
