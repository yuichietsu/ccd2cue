<?php

require_once(__DIR__ . '/../vendor/autoload.php');

$ccd2cue = new \Menrui\CCD2CUE();
$ccd2cue->convert($argv[1]);
