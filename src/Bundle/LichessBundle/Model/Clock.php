<?php

namespace Bundle\LichessBundle\Model;

interface Clock {

    function getName();
    
    function reset();

    function step();

    function start();

    function stop();

    function addTime($color, $time);

    function isOutOfTime($color);
    
    function getRemainingTime($color);

    function getElapsedTime($color);

    function getRemainingTimes();

    function getMoveBonus();

    function setMoveBonus($moveBonus);

    function getColor();

    function setColor($color);

    function isEnabled();

    function isRunning();

    function getLimit();

    function getLimitInMinutes();

    function getTimes();

    function renderTime($time);
}