#!/bin/bash

echo "--- Clearing Laravel Caches ---"
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear
php artisan optimize:clear

echo ""
echo "--- How to Reset Activation ---"
echo "To force the application to ask for the activation key again:"
echo "1. In the application (Tauri window), press 'Shift + Alt + R' on the login page."
echo "2. Or manually clear the Local Storage in the browser/app."
echo ""
echo "Done."
