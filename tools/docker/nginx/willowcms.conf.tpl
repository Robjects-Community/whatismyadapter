server {
    listen ${APP_PORT:-8080};
    server_name _;
    root /var/www/willowcms/webroot;
    index index.php index.html;

    # Security headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header Referrer-Policy "strict-origin-when-cross-origin" always;
    
    # Hide Nginx version
    server_tokens off;

    # Access and error logs to stdout/stderr for container logging
    access_log /dev/stdout main;
    error_log /dev/stderr warn;

    # Gzip compression (configurable via environment)
    gzip ${NGINX_GZIP:-on};
    gzip_vary on;
    gzip_min_length 1024;
    gzip_types
        application/atom+xml
        application/javascript
        application/json
        application/ld+json
        application/manifest+json
        application/rss+xml
        application/vnd.geo+json
        application/vnd.ms-fontobject
        application/x-font-ttf
        application/x-web-app-manifest+json
        application/xhtml+xml
        application/xml
        font/opentype
        image/bmp
        image/svg+xml
        image/x-icon
        text/cache-manifest
        text/css
        text/javascript
        text/plain
        text/vcard
        text/vnd.rim.location.xloc
        text/vtt
        text/x-component
        text/x-cross-domain-policy;

    # Health check endpoint - returns 200 OK
    location = /health {
        access_log off;
        return 200 "healthy\n";
        add_header Content-Type text/plain;
    }

    # PHP-FPM ping endpoint via proxy
    location = /ping {
        access_log off;
        fastcgi_pass unix:/run/php/php-fpm.sock;
        fastcgi_param SCRIPT_FILENAME \$document_root\$fastcgi_script_name;
        include fastcgi_params;
    }

    # Static assets with long cache headers
    location ~* \.(js|css|png|jpg|jpeg|gif|ico|svg|woff|woff2|ttf|eot)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
        access_log off;
    }

    # Deny access to sensitive files
    location ~ /\. {
        deny all;
        access_log off;
        log_not_found off;
    }

    location ~ ^/(config|tmp|logs|tests)/ {
        deny all;
        access_log off;
        log_not_found off;
    }

    # CakePHP URL rewriting
    location / {
        try_files \$uri \$uri/ /index.php?\$args;
    }

    # PHP processing
    location ~ \.php$ {
        # Security: Don't execute PHP files in upload directories
        location ~ ^/files/.*\.php$ {
            deny all;
            access_log off;
            log_not_found off;
        }

        fastcgi_pass unix:/run/php/php-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME \$document_root\$fastcgi_script_name;
        fastcgi_param PATH_INFO \$fastcgi_path_info;
        fastcgi_param PATH_TRANSLATED \$document_root\$fastcgi_path_info;
        
        # Standard FastCGI parameters
        include fastcgi_params;
        
        # Additional security parameters
        fastcgi_param SERVER_NAME \$server_name;
        fastcgi_param HTTPS \$https if_not_empty;
        
        # Buffer settings for performance
        fastcgi_buffers 16 16k;
        fastcgi_buffer_size 32k;
        
        # Timeout settings
        fastcgi_connect_timeout 5s;
        fastcgi_send_timeout 10s;
        fastcgi_read_timeout 10s;
    }

    # Deny access to .htaccess files (if Apache is also running)
    location ~ /\.htaccess {
        deny all;
        access_log off;
        log_not_found off;
    }
}