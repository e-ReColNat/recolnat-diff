DWC-DIFF - eRecolnat
============

Outil d'aide décisionnel des données exportés depuis eRecolnat


##Architecture serveur
* PHP 5.6
    * libraries : 
        * opcache
        * pdo
        * apcu
        * intl
        * json
        * oracle
        * zip
    * paramétrages : 
        * memory_limit : 1024M
        * Default timezone : Europe/Paris
        * opcache.max_accelerated_files : 30000
        * opcache.memory_consumption : 512
* composer
* unzip