<?php


$setup_already_exec = class_exists('\Enedis2InfluxDB\Loader', false);

if (!$setup_already_exec) {
	$setup_file = __DIR__.'/setup.vars.php';

    if (file_exists($setup_file)) {
        require_once $setup_file;
    }

    require_once PATH_CLASS.'Loader.php';
}

$Loader = \Enedis2InfluxDB\Loader::instance();

if (!$setup_already_exec) {
    $Loader->register_autoload_prefix('Enedis2InfluxDB\\', PATH_CLASS.'/%s.php');
    $Loader->register_autoload_prefix('\\Enedis2InfluxDB\\', PATH_CLASS.'/%s.php');

}