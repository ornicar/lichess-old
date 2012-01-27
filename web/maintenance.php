<?php

sleep(2); // Avoid rush

header(sprintf('HTTP/1.0 503 Service Unavailable'));

echo "<h1>lichess.org</h1><h2>Temporarily down for maintenance</h2>We expect to be back in a few seconds. Thanks for your patience.";

exit();
