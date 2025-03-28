#!/bin/bash










if [ -f "$domain_dir/.env" ]; then
    cd /var/www/vhost || exit "Directory not found"
    mv "$domain_dir/.env" "/var/www/laravel/${domain}.env"
    rm -rf "$domain_dir"
    mkdir -p "$domain_dir"
fi

git clone "$repo_link" "$domain_dir"
cd "$domain_dir" || exit

if [ -f "/var/www/laravel/${domain}.env" ]; then
    mv "/var/www/laravel/${domain}.env" "$domain_dir/.env"
fi

if [[ "$use_as_template" == "yes" ]]; then
    rm -rf .git
    git init
    git branch -m main
fi

echo "When asked to confirm running composer as root, type 'yes'."
composer install

if [ ! -f ".env" ]; then
    echo -e "\e[31mYou will need to upload your .env file !!\e[0m"
    echo -e "\e[31mYou will need to manually run 'php artisan key:generate' !!\e[0m"
    echo -e "\e[33mThe script will proceed in 5 sec.\e[0m"
    sleep 5
else
    php artisan key:generate
fi
php artisan storage:link
sudo chown -R www-data:www-data "$domain_dir"/storage "$domain_dir"/bootstrap/cache
