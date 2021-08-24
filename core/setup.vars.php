<?php

define('PATH_ROOT', realpath(__DIR__.'/../').'/');
define('PATH_CORE', PATH_ROOT.'core/');
define('PATH_CLASS', PATH_CORE.'class/');
define('PATH_VENDOR', PATH_ROOT.'vendor/');


/***************** CUSTUMS VARS *********************************/

// InfluxDB
define('INFLUX_TOKEN', "xxxxxx");
define('INFLUX_URL', "xxxxxx");
define('INFLUX_ORG', 'xxxxxx');
define('INFLUX_BUCKET', 'xxxxxx');

// Enedis
define('ENEDIS_POINT_ID', 'xxxxxx');
define('ENEDIS_START_DAILY', '2019-03-01');
define('ENEDIS_START_HOURLY', '2019-08-22');
