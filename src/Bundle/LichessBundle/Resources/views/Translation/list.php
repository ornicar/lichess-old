<?php $view->extend('LichessBundle::layout') ?>

<?php
foreach($files as $file) {
    echo '<strong>'.basename($file).'</strong><br />';
    echo '<pre style="max-height: 200px; overflow: auto; border: 1px solid #CCC; margin-bottom: 2em;">'.file_get_contents($file).'</pre>';
}
