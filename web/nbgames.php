<?php

$db = 'lichess';
$collection = 'game2';

$mongo = new Mongo();
print $mongo->selectDB($db)->selectCollection($collection)->count();
