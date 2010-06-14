Very fast Chess game designed to run on a small server and play hundreds of concurrent games.
Allows to play with a friend or an Artificial Intelligence.
Supports castling, en passant, selective promotion, color selection, check and mate detection, and move validation.
This open source software uses only open source languages: PHP 5.3, HTML5, Javascript and CSS.
Powered by Symfony 2 and jQuery 1.4.
  
PLAY
----

[http://lichess.org](http://lichess.org)

FEEDBACK
--------

I'm waiting for bug reports and feature requests in [GitHub issue tracker](http://github.com/ornicar/lichess/issues)

INSTALL
-------

As it uses no database, lichess is very easy to install.
~~~
git clone git://github.com/ornicar/lichess.git
cd lichess
git submodule update --init --recursive
~~~

Install crafty on Debian based distros:
~~~
sudo apt-get install crafty
~~~

TEST
----

Run unit tests

    phpunit src/Bundle/LichessBundle/Tests/AllTests.php

Run performance tests

    php src/Bundle/LichessBundle/Tests/Performance/AllTests.php
