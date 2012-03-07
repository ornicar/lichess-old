Very fast Chess game designed to run on a small server and play hundreds of concurrent games.
GUI is only HTML5 and JavaScript.

- Allows to play with a friend, a random player or an artificial intelligence.
- Supports castling, en passant, selective promotion, color selection, check and mate detection, threefold repetition, and move validation.
- Chess clock, integrated chat, real time spectator mode and analysis interface available.
- Chess variants available: Standard and Chess960
- Translated to 57 languages thanks to contributors.
- Uses only open source languages: PHP 5.3, HTML5, Javascript and CSS. Powered by Symfony2, jQuery 1.5 and jQuery UI 1.8.
- [Learn more in the wiki](http://en.lichess.org/wiki)
  
PLAY
----

- [Play Chess with a random player](http://en.lichess.org)
- [Play Chess with a friend](http://en.lichess.org/#friend)
- [Play Chess with the computer](http://en.lichess.org/#ai)
- [Real time list of games beeing played](http://en.lichess.org/games)
- [List of all games](http://en.lichess.org/games/all)
- [List of all players](http://en.lichess.org/people)
- [Game analysis](http://en.lichess.org/analyse/0Zcvl5)
- [Wiki](http://en.lichess.org/wiki)

FEEDBACK
--------

I'm eagerly waiting for bug reports and feature requests in [Lichess Forum](http://en.lichess.org/forum/lichess-feedback)

INSTALL
-------

Lichess is built on Symfony2.

It requires [APC](http://www.php.net/manual/en/book.apc.php). It's a free and open opcode cache for PHP.

It uses [MongoDB](http://mongodb.org) for game storage.

### Get the code

    git clone git://github.com/ornicar/lichess.git
    cd lichess
    ./bin/vendors install

### Check your server requirements

Open your browser at http://myhostname/check.php

You can also run checks from command line, but the results may differ:

    php web/check.php

### Configure lichess host

You have to tell lichess the host it will be accessed through.

Open app/config/config_dev.yml and replace occurences of `l.org` with your own localhost.

### Configure subdomains

lichess uses one subdomain per language. You don't have to configure all of them. Here is how to configure the English and French ones:
Just replace `l.org` with your domain name.

/etc/hosts

    127.0.0.1	l.org
    127.0.0.1	en.l.org
    127.0.0.1	fr.l.org
    
Here is a nginx configuration example:

    server {
        listen 80;
        server_name l.org *.l.org;
        root /home/thib/data/workspace/lichess/web;

        location / {
            root   /home/thib/data/workspace/lichess/web/;
            index  index_dev.php;
            # serve static files directly
            if (-f $request_filename) {
                access_log        off;
                expires           1s;
                break;
            }
            rewrite ^(.*) /index_dev.php last;
        }

        location ~ \.php {
            fastcgi_pass   unix:/var/run/php-fpm/php-fpm.sock;
            fastcgi_index  index_dev.php; 
            fastcgi_param  SCRIPT_FILENAME  /home/thib/data/workspace/lichess/web/$fastcgi_script_name;
            include        fastcgi_params;
        }
    }

### Initialize things

By running this script:

    ./bin/reload

It will build the bootstrap, clear the cache, warm it up,
load fixtures, create MongoDB indexes and symlink assets.

You can run this script as many times as needed.
Note that it reinitializes the dev and test databases.

### Run

Open your browser at http://myhostname/index_dev.php

### Configure Artificial Intelligence

The default AI is crafty, an opensource program written in C.

#### Install crafty on Debian based distros:

    sudo apt-get install crafty

If you can't or don't want to install crafty, you can disable it:

    # lichess/config/config.yml
    lichess:
        ai:
            crafty:
                enabled: false
                priority: 2
                executable_path: /usr/bin/crafty
                book_dir: /usr/share/crafty
            stupid:
                enabled: true
                priority: 1

Lichess will then use the next AI available, called "stupid".
It's dumb as hell but it plays :)

### APC cache slam

If you get cache slam warning in logs, upgrade APC and/or disable the warnings in apc.ini

    apc.slam_defense="Off"

### TEST

Lichess is well tested. You should run the tests if you plan to modify the code.

You need [PHPUnit 3.5](http://github.com/sebastianbergmann/phpunit) installed.

Run all unit and functional tests

    phpunit -c app

To get functional tests passing, you need to enable APC in CLI.

    # /etc/php5/cli/conf.d/apc.ini
    extension=apc.so
    apc.enabled=1  
    apc.enable_cli=1
    apc.shm_segments=1  
    apc.shm_size=64
