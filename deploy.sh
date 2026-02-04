#!/bin/bash

# Auto-Deployment Script for Radiif
# This script will be called by the webhook

echo "🚀 Starting deployment..."

# Navigate to project directory
cd /var/www/radiif || exit

# Pull latest changes
echo "📥 Pulling latest changes from GitHub..."
git pull origin master

# Create storage symlink if not exists
echo "🔗 Creating storage symlink..."
php artisan storage:link 2>/dev/null || echo "Storage link already exists"

# Clear caches
echo "🧹 Clearing caches..."
php artisan cache:clear
php artisan config:clear
php artisan view:clear
php artisan route:clear

# Optimize for production
echo "⚡ Optimizing for production..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Set permissions
echo "🔐 Setting permissions..."
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache

echo "✅ Deployment completed successfully!"
echo "📅 Deployed at: $(date)"
