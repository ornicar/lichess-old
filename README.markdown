Very fast Chess game designed to run on a small server and play hundreds of concurrent games.
Allows to play with a friend or an Artificial Intelligence.
Supports castling, en passant, selective promotion, color selection, check and mate detection, and move validation.
Uses only open source languages: PHP 5.3, HTML5, Javascript and CSS.
Powered by Symfony2 and jQuery 1.4.
  
PLAY
----

[http://lichess.org](http://lichess.org)

FEEDBACK
--------

I'm waiting for bug reports and feature requests in [GitHub issue tracker](http://github.com/ornicar/lichess/issues)

Users can give feedback in [Uservoice](http://lichess.uservoice.com/forums/62479-general)

INSTALL
-------

Lichess is built on Symfony2, which is under heavy development and has very few [documentation](http://symfony-reloaded.org/) at the moment.

### Get the code

    git clone git://github.com/ornicar/lichess.git
    cd lichess
    git submodule update --init --recursive

### Create data folders

    mkdir lichess/data
    mkdir lichess/cache/socket
    ln -s ../lichess/cache/socket web/socket

### Run

Open your browser at http://myhostname/index_dev.php

### Configure Artificial Intelligence

The default AI is crafty, a opensource program written in C.

#### Install crafty on Debian based distros:

    sudo apt-get install crafty

If you can't or don't want to install crafty, you can use a `Stupid` AI:

    # lichess/config/lichess.yml
    parameters:
        lichess.ai.class: "Bundle\LichessBundle\Ai\Stupid"

TEST
----

Before doing any modification to the code, you should be able to run the test suite.
You need [PHPUnit 3.5](http://github.com/sebastianbergmann/phpunit) installed.

Run all unit and functional tests

    phpunit -c lichess
