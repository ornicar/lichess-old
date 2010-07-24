<?php $view->extend('LichessBundle::layout') ?>
<?php $view->stylesheets->add('bundle/lichess/css/translation.css') ?>

<div class="translation_list">
<?php
foreach($days as $date => $times) {
    echo '<h2>'.$date.'</h2>';
    foreach($times as $time => $infos) {
        echo '<h3><strong>'.$infos['locale'].'</strong> - '.$infos['date']->format('H:i:s').'</h3>';
        echo '<pre style="max-height: 200px; overflow: auto; border: 1px solid #CCC; margin-bottom: 2em;">'.file_get_contents($infos['file']).'</pre>';
    }
}
?>
</div>
