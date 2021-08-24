<?php

namespace Enedis2InfluxDB;

use Exception;

class Loader {

	public static function instance() {
		static $instance = null;
		if ($instance === null) {
			$instance = new self();
		}
		return $instance;
	}

    /**
	 * Private construct to singleton
	 */
	private function __construct() {
		$this->register_autoload();
	}

	/**
	 * Registers default autoload function and declare default behaviour
	 * for Doc_framework classes.
	 */
	private function register_autoload() {
		spl_autoload_register(array($this, 'autoload'));
		$this->register_autoload_prefix('DOC_', PATH_CLASS.'class.DOC_%s.php');
		
		$this->register_jeedom();
		@include PATH_VENDOR.'autoload.php';
	}

	private function register_jeedom() {
		$levels = 3;
		$path = '';
		$jeedom = $path.'/core/php/core.inc.php';
		while (!file_exists($jeedom)) {
			$path = dirname ( __DIR__ , $levels);
			$jeedom = $path.'/core/php/core.inc.php';
			$levels++;
		}
		var_dump($jeedom);
		require_once $jeedom;
	}

	public function register_autoload_prefix($prefix, $path) {
		if (!$prefix || !$path) {
			throw new Exception('Need to specify the prefix ("'.$prefix.'") and path ("'.$path.'")');
		}

		$this->autoload_prefix[$prefix] = $path;
	}

	public function autoload($class_name) {
		foreach ($this->autoload_prefix as $prefix => $path) {
			if (substr($class_name, 0, strlen($prefix)) == $prefix) {
				// Translate namespace structure into directory structure
				$class = str_replace(array($prefix, '\\'), array('', DIRECTORY_SEPARATOR), $class_name);
				$file  = sprintf($path, $class);
				// TODO : file_exists to delete when CF_api will use namespace. useful also for unit test. Try to fix it with other way
				if (file_exists($file)) {
					require_once $file;
					break;
				}
			}
		}
	}
    
}