Very fast Chess game designed to run on a small server and play hundreds of concurrent games.
Allows to play with a friend or an Artificial Intelligence.
Supports castling, en passant, selective promotion, color selection, check and mate detection, and move validation.
Uses only open source languages: PHP 5.3, HTML5, Javascript and CSS.
Powered by Symfony 2 and jQuery 1.4.
  
PLAY
----

[http://lichess.org](http://lichess.org)

FEEDBACK
--------

I'm waiting for bug reports and feature requests in [GitHub issue tracker](http://github.com/ornicar/lichess/issues)

Users can give feedback in [Uservoice](http://lichess.uservoice.com/forums/62479-general)
INSTALL
-------

As it uses no database, lichess is very easy to install.

    git clone git://github.com/ornicar/lichess.git
    cd lichess
    git submodule update --init --recursive

You also need to create some folders

    mkdir lichess/data
    mkdir lichess/cache/socket
    ln -s web/socket ../lichess/cache/socket

Install crafty on Debian based distros:

    sudo apt-get install crafty

TEST
----

Run all unit and functional tests

    phpunit -c lichess
