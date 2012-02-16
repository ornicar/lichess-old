<?php

sleep(2); // Avoid rush

header(sprintf('HTTP/1.0 503 Service Unavailable'));

?>
<!DOCTYPE html>
  <html lang="en">
    <head>
      <meta charset="utf-8">
      <title>lichess - maintenance</title>
      <style type="text/css">
html, body {
  font:16px monospace;
  background: #000;
  color: #ebd488;
  text-align: center;
}
      </style>
    </head>
    <body>
      <h1>lichess.org is down for maintenance</h2>We expect to be back in a few seconds. Thanks for your patience.
<br />
<br />
      <img src="/images/maintenance.jpg" alt="Lichess maintenance" />
    </body>
  </html>
<?php
exit();
