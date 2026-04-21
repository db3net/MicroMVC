# Server Setup

MicroMVC supports clean URLs (`/controller/method/arg1/arg2`) on all major servers. The `public/` directory is the document root.

## PHP Built-in Server

For local development:

```bash
php -S localhost:8080 -t public
```

Clean URLs work automatically.

## Apache

Point your virtual host document root to `public/`. The included `.htaccess` handles rewriting.

```apache
<VirtualHost *:80>
    ServerName mysite.com
    DocumentRoot /var/www/mysite/public

    <Directory /var/www/mysite/public>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

Enable `mod_rewrite`:

```bash
sudo a2enmod rewrite
sudo systemctl restart apache2
```

## Nginx

```nginx
server {
    listen 80;
    server_name mysite.com;
    root /var/www/mysite/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php$uri?$query_string;
    }

    location ~ \.php(/|$) {
        fastcgi_pass unix:/run/php/php-fpm.sock;
        fastcgi_split_path_info ^(.+\.php)(/.*)$;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        fastcgi_param PATH_INFO $fastcgi_path_info;
    }

    location ~ /\. {
        deny all;
    }
}
```
