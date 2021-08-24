<?php

require __DIR__.'/../core/setup.php';

$instance = new \Enedis2InfluxDB\enedisfromplugin;
$instance->dailyCron();
