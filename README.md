

 Players market
========================


Requirements
------------

  * PHP 8.1.0 or higher;
  * PDO-SQLite PHP extension enabled;
  * and the [usual Symfony application requirements].

Installation
------------


[Download Composer] and use the `composer` binary installed
on your computer to run these commands:


# ...or you can clone the code repository and install its dependencies
$ git https://github.com/stolak/player-market.git
$ cd player-markett/
$ composer install
```
Setup the .env and edit the database information to something as show below:
# DATABASE_URL="mysql://root@127.0.0.1:3306/foot-ball-team?serverVersion=8&charset=utf8mb4"
Run "php bin/console doctrine:database:create" to create the database
Run "php bin/console make:migration" 
Run "php bin/console doctrine:migrations:migrate"
Run "symfony serve"



