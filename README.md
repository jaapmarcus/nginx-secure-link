# nginx-secure-link

## Changes to nginx / sites config 

Add the following code to the  server -> location of the domain config of Nginx

   	location ~* ^.+\.(mp4|webm|mp3|ogg|ogv)$ {
	     proxy_cache    off;
             secure_link $arg_md5,$arg_expires;
             secure_link_md5 "$secure_link_expires$uri secret-pass";
             if ($secure_link = "") {
                return 403; 
             }
             if ($secure_link = "0") {
                return 403; 
             }
	     root           %sdocroot%;
             access_log     /var/log/%web_system%/domains/%domain%.log combined;
             access_log     /var/log/%web_system%/domains/%domain%.bytes bytes;
        }