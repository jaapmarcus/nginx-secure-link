#=======================================================================#
# Default Web Domain Template                                           #
# DO NOT MODIFY THIS FILE! CHANGES WILL BE LOST WHEN REBUILDING DOMAINS #
#=======================================================================#

server {
    listen      %ip%:%web_port%;
    server_name %domain_idn% %alias_idn%;
    root        %docroot%;
    index       index.php index.html index.htm;
    access_log  /var/log/nginx/domains/%domain%.log combined;
    access_log  /var/log/nginx/domains/%domain%.bytes bytes;
    error_log   /var/log/nginx/domains/%domain%.error.log error;
        
    include %home%/%user%/conf/web/%domain%/nginx.forcessl.conf*;
    
    location = /favicon.ico {
        log_not_found off;
        access_log off;
    }

    location = /robots.txt {
        allow all;
        log_not_found off;
        access_log off;
    }

    location / {
        try_files $uri $uri/ /index.php?$args;
        
        if (!-e $request_filename)
        {
            rewrite ^(.+)$ /index.php?q=$1 last;
        }

    location ~* ^.+\.(mp4|webm|mp3|ogg|ogv)$ {
            secure_link $arg_md5,$arg_expires;
            secure_link_md5 "$secure_link_expires$uri %user%";
            if ($secure_link = "") {
               return 403; 
            }
            if ($secure_link = "0") {
               return 403; 
            }
            expires     max;
            fastcgi_hide_header "Set-Cookie";
        }


        location ~* ^.+\.(jpeg|jpg|png|gif|bmp|ico|svg|css|js)$ {
            expires     max;
            fastcgi_hide_header "Set-Cookie";
        }

        location ~ [^/]\.php(/|$) {
            fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
            if (!-f $document_root$fastcgi_script_name) {
                return  404;
            }

            fastcgi_pass    %backend_lsnr%;
            fastcgi_index   index.php;
            include     %home%/%user%/conf/web/%domain%/nginx.fastcgi_cache.conf*;
            include        /etc/nginx/fastcgi_params;
        }
    }

    location /error/ {
        alias   %home%/%user%/web/%domain%/document_errors/;
    }

    # Banned locations (only reached if the earlier PHP entry point regexes don't match)
    location ~ /\.(?!well-known\/) { 
       deny all; 
       return 404;
    }

    location /vstats/ {
        alias   %home%/%user%/web/%domain%/stats/;
        include %home%/%user%/web/%domain%/stats/auth.conf*;
    }

    include     /etc/nginx/conf.d/phpmyadmin.inc*;
    include     /etc/nginx/conf.d/phppgadmin.inc*;
    include     /etc/nginx/conf.d/webmail.inc*;

    include     %home%/%user%/conf/web/%domain%/nginx.conf_*;
}