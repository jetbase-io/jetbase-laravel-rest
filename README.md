# Jetbase Laravel REST API

[![Build Status](https://travis-ci.org/lukas-pierce/jetbase-laravel-rest.svg?branch=master)](https://travis-ci.org/lukas-pierce/jetbase-laravel-rest)
[![Made in MVP engine](https://img.shields.io/badge/made%20in-MVP%20engine-brightgreen.svg)](https://mvpngn.com/)

## Where are API requests described?
All API requests described in our official [swagger.yml](https://github.com/jetbase-io/jetbase-swagger/blob/master/swagger.yml) file.<br>
For visualization use [Swagger UI](http://petstore.swagger.io/#/) paste there link to our swagger.yml

## Installation

Clone repository
```
cd <project-dir>
git clone https://github.com/jetbase-io/jetbase-laravel-rest
```

install php dependencies:
```
composer install
```

Prepare PostgreSQL database and write its config in `.env` file
```
sudo nano .env
```
in `.env` file replace your database config values:
```
DB_DATABASE=<your-db-name>
DB_USERNAME=<your-db-username>
DB_PASSWORD=<your-db-password>
```

Crete database tables:
```
php artisan migrate
```

Create admin
```
php artisan users:create \
-F"<admin_first_name>" \
-L"<admin_last_name>" \
-E"<admin_email>" \
-P"<admin_password>" \
-A 
```
Flag `-A` means add admin role.

## Testing
> :warning: **Important**: Before run tests create testing database!
```
cd <project-dir>
cp .env .env.testing
sudo nano .env.testing
```

replace there:
```
DB_DATABASE=<your-testing-db-name>
DB_USERNAME=<your-testing-db-username>
DB_PASSWORD=<your-testing-db-password>
```

After prepare testing database you can run tests
```
cd <project-dir>
phpunit
# or
npm run test
```

## Run server
You can run REST App using included server script
```
cd <project-dir>
php artisan serve
```

Then go to [http://localhost:8000](http://localhost:8000) in your browser

## Nginx config
If your want use nginx. There is a basic example of nginx config.<br>
*In all bellow examples instead `jetbase-rest.app` use your real domain.*
```
cd /etc/nginx/sites-available
sudo nano jetbase-rest.app
```

Paste there, 
```nginx
server {
    listen 80;
    listen [::]:80;

    server_name jetbase-rest.app; # insert here your domain

    root /var/www/jetbase-rest.app/public; # path to app dir
    index index.php;
    charset utf-8;
    error_log  /usr/local/etc/nginx/logs/jetbase-rest.app-error.log error;
    access_log  /usr/local/etc/nginx/logs/jetbase-rest.app-access.log combined;
    client_max_body_size 2m;

    # disable loggin on favicon.ico and robots.txt
    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    # all urls direct to index.php
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    # handle php-files by php-fpm
    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        # With php-fpm (or other unix sockets):
        fastcgi_pass unix:/var/run/php/php7.2-fpm.sock;
    }

    location ~ /\.ht {
        deny all;
    }

}
```
Save and exist. Then create link
```
sudo ln -s /etc/nginx/sites-available/jetbase-rest.app /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

Then go to [http://jetbase-rest.app](http://jetbase-rest.app) in your browser


## Running Laravel REST server in docker

```
# build images
docker-compose build

# up containers in daemon mode
docker-compose up -d

# run migration in 'app' container
docker-compose exec app php artisan migrate
```