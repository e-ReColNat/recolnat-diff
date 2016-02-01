# DWC-DIFF - eRecolnat
Outil d'aide décisionnel des données exportés depuis eRecolnat

## Architecture serveur
### Résumé
* PHP 5.6
    * libraries : 
        * opcache
        * pdo
        * apcu
        * intl
        * json
        * oci8 (oracle)
        * zip
    * paramétrages dans php.ini (serveur à redémarrer ensuite) : 
        * memory_limit : 1024M
        * Default timezone : Europe/Paris
        * opcache.max_accelerated_files : 30000
        * opcache.memory_consumption : 512
* composer
* unzip
* phpize

## Installation des librairies php 
### oci8 (oracle)
voir http://php.net/manual/en/oci8.installation.php section Installing OCI8 from PECL
* download oci8 http://pecl.php.net/package/oci8
* tar -zxf oci8-x.tgz
* cd oci8-x
* phpize
* ./configure -with-oci8=shared,$ORACLE_HOME
* make install
* Dans php.ini extension=oci8.so

## Déploiement
### Virtual host (Nginx)

    server {
        listen 80;
        root /path/to/recolnat-diff/web;
        server_name recolnat-diff.tld; # à modifier
        index index.php index.html;
        access_log /var/log/nginx/recolnat-diff-access.log;
        error_log /var/log/nginx/recolnat-diff-error.log;
        
        location / {
            # try to serve file directly, fallback to app.php
            try_files $uri /app.php$is_args$args;
        }

        # DEV
        # This rule should only be placed on your development environment
        # In production, don't include this and don't deploy app_dev.php or config.php
        location ~ ^/(app_dev|config)\.php(/|$) {
            fastcgi_pass php5-fpm-sock;
            fastcgi_split_path_info ^(.+\.php)(/.*)$;
            include /etc/nginx/fastcgi_params;
            fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
            fastcgi_param HTTPS off;
            fastcgi_param PHP_IDE_CONFIG serverName=$server_name;
            
            fastcgi_buffer_size 128k;
            fastcgi_buffers 4 256k;
            fastcgi_busy_buffers_size 256k;
            fastcgi_read_timeout 600;
        }
        # PROD
            location ~ ^/app\.php(/|$) {
            fastcgi_pass php5-fpm-sock;
            fastcgi_split_path_info ^(.+\.php)(/.*)$;
            include /etc/nginx/fastcgi_params;
            fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
            fastcgi_buffer_size 128k;
            fastcgi_buffers 4 256k;
            fastcgi_busy_buffers_size 256k;
            fastcgi_read_timeout 600;
            # Prevents URIs that include the front controller. This will 404:
            # http://domain.tld/app.php/some-path
            # Remove the internal directive to allow URIs like this
            internal;
        }
    }    
    
### Droits sur les répertoires
Le serveur doit avoir des droits de lecture et écriture dans le répertoire data/

### Installation
à la racine du site
composer install
Renseigner les paramètres demandés lors de la configuration par composer (connexion oracle notamment) ou
Les renseigner plus tard en copiant le fichier app/config/parameters.dist.yml vers app/config/parameters.yml et en changeant les valeurs

## Base de données
### Généralités
* Il y a deux connections à la bdd :
    * recolnat et diff (base buffer)
    * recolnat accède à la base centrale de recolnat
    * diff accède uniquement à la base temporaire des institutions nommée recolnat_diff
    * les deux bases ont la même structure
    * recolnat a des droits de sélection sur la base recolnat_diff

### Droits nécessaires
grant SELECT on "RECOLNAT_DIFF"."BIBLIOGRAPHIES" to "RECOLNAT" ;
grant SELECT on "RECOLNAT_DIFF"."DETERMINATIONS" to "RECOLNAT" ;
grant SELECT on "RECOLNAT_DIFF"."LOCALISATIONS" to "RECOLNAT" ;
grant SELECT on "RECOLNAT_DIFF"."MULTIMEDIA" to "RECOLNAT" ;
grant SELECT on "RECOLNAT_DIFF"."MULTIMEDIA_HAS_OCCURRENCES" to "RECOLNAT" ;
grant SELECT on "RECOLNAT_DIFF"."RECOLTES" to "RECOLNAT" ;
grant SELECT on "RECOLNAT_DIFF"."SPECIMENS" to "RECOLNAT" ;
grant SELECT on "RECOLNAT_DIFF"."STRATIGRAPHIES" to "RECOLNAT" ;
grant SELECT on "RECOLNAT_DIFF"."TAXONS" to "RECOLNAT" ;