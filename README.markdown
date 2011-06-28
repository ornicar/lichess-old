Very fast Chess game designed to run on a small server and play hundreds of concurrent games.
GUI is only HTML5 and JavaScript.

- Allows to play with a friend, a random player or an artificial intelligence.
- Supports castling, en passant, selective promotion, color selection, check and mate detection, threefold repetition, and move validation.
- Chess clock, integrated chat, real time spectator mode and analysis interface available.
- Chess variants available: Standard and Chess960
- Translated to more than 48 languages thanks to contributors.
- Uses only open source languages: PHP 5.3, HTML5, Javascript and CSS. Powered by Symfony2, jQuery 1.5 and jQuery UI 1.8.
  
PLAY
----

- [Homepage](http://lichess.org)
- [Play Chess with a friend](http://lichess.org/friend)
- [Play Chess with a random player](http://lichess.org/anybody)
- [Play Chess with the computer](http://lichess.org/ai)
- [Real time list of games beeing played](http://lichess.org/games)
- [List of all games](http://lichess.org/games/all)
- [List of all players](http://lichess.org/people)
- [Game analysis](http://lichess.org/analyse/0Zcvl5)

FEEDBACK
--------

I'm eagerly waiting for bug reports and feature requests in [Lichess Forum](http://lichess.org/forum/lichess-feedback)

INSTALL
-------

Lichess is built on Symfony2, which is under heavy development at the moment.

It requires [APC](http://www.php.net/manual/en/book.apc.php). It's a free and open opcode cache for PHP.

It uses [MongoDB](http://mongodb.org) for game storage.

### Get the code

    git clone git://github.com/ornicar/lichess.git
    cd lichess
    git submodule init
    git submodule update --init --recursive

### Check your server requirements

Open your browser at http://myhostname/check.php

You can also run checks from command line, but the results may differ:

    php web/check.php

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

The default AI is crafty, a opensource program written in C.

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

    phpunit -c lichess

To get functional tests passing, you need to enable APC in CLI.

    # /etc/php5/cli/conf.d/apc.ini
    extension=apc.so
    apc.enabled=1  
    apc.enable_cli=1
    apc.shm_segments=1  
    apc.shm_size=64
