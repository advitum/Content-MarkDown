<?php
	
	namespace Advitum\Cmd;
	
	session_start();
	
	define('DS', DIRECTORY_SEPARATOR);
	
	define('ROOT_PATH', __DIR__ . DS);
	define('PLUGINS_PATH', ROOT_PATH . 'plugins' . DS);
	define('CORE_PATH', ROOT_PATH . 'core' . DS);
	define('CORE_PLUGINS_PATH', CORE_PATH . 'plugins' . DS);
	define('VENDOR_PATH', ROOT_PATH . 'vendor' . DS);
	define('CONTENT_PATH', ROOT_PATH . 'content' . DS);
	define('LAYOUTS_PATH', CONTENT_PATH . 'layouts' . DS);
	define('MEDIA_PATH', CONTENT_PATH . 'media' . DS);
	define('PAGES_PATH', CONTENT_PATH . 'pages' . DS);
	
	define('ROOT_URL', '/');
	define('CSS_URL', ROOT_URL . 'content/css/');
	define('JS_URL', ROOT_URL . 'content/js/');
	define('MEDIA_URL', ROOT_URL . 'content/media/');
	define('PLUGINS_URL', ROOT_URL . 'plugins/');
	
	spl_autoload_register(function($class) {
		if(substr($class, 0, strlen(__NAMESPACE__)) == __NAMESPACE__) {
			$file = CORE_PATH . str_replace('\\', DS, substr($class, strlen(__NAMESPACE__) + 1)) . '.php';
			if(is_file($file)) {
				require_once($file);
			}
		}
	});
	
	require_once(CONTENT_PATH . 'config.php');
	Router::init();
	
?>