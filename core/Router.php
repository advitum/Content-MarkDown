<?php
	
	namespace Advitum\Cmd;
	
	class Router
	{
		private static $url = null;
		
		public static function init() {
			if(isset($_SERVER["REDIRECT_URL"])) {
				$url = $_SERVER["REDIRECT_URL"];
			} else {
				$url = '';
			}
			
			$dirty = $url;
			$url = self::sanitize($dirty);
			
			if($url !== $dirty) {
				Router::redirect($url);
			}
			
			DB::connect(DATABASE_HOST, DATABASE_USERNAME, DATABASE_PASSWORD, DATABASE_DATABASE);
			
			if($url == '/admin' || substr($url, 0, 7) == '/admin/') {
				$params = array();
				if(strlen($url) > 7) {
					$params = explode('/', substr($url, 7));
				}
				
				Admin::init($params);
			} else {
				self::render($url);
			}
		}
		
		public static function setup() {
			if(is_file(CONTENT_PATH . 'config.php')) {
				return;
			}
			
			if(Form::sent('setup')) {
				if(DB::connect(Form::value('setup', 'host'), Form::value('setup', 'username'), Form::value('setup', 'password'), Form::value('setup', 'database'), false)) {
					$configuration = file_get_contents(CONTENT_PATH . 'config.default.php');
					$configuration = str_replace('%HOST%', Form::value('setup', 'host'), $configuration);
					$configuration = str_replace('%USERNAME%', Form::value('setup', 'username'), $configuration);
					$configuration = str_replace('%PASSWORD%', Form::value('setup', 'password'), $configuration);
					$configuration = str_replace('%DATABASE%', Form::value('setup', 'database'), $configuration);
					
					if(file_put_contents(CONTENT_PATH . 'config.php', $configuration)) {
						Session::setMessage('Your configuration was saved. Have fun!', 'success');
						Router::redirect(ROOT_URL);
					} else {
						Session::setMessage('Your configuration could not be written. Please check the file permissions of the content folder.', 'error');
					}
				} else {
					Session::setMessage('The database connection could not be established. Please check your information.', 'error');
				}
			}
			
			?><h1>Content MarkDown Setup</h1>
<p>Please enter the information needed to connect to your database.</p>
<?php echo Session::getMessage(); ?>
<?php echo Form::create(ROOT_URL, 'setup'); ?>
	<?php echo Form::input('host', array(
		'label' => 'Host',
		'placeholder' => 'Host'
	)) ?>
	<?php echo Form::input('username', array(
		'label' => 'Username',
		'placeholder' => 'Username'
	)) ?>
	<?php echo Form::input('password', array(
		'label' => 'Password',
		'placeholder' => 'Password'
	)) ?>
	<?php echo Form::input('database', array(
		'label' => 'Database',
		'placeholder' => 'Database'
	)) ?>
<?php echo Form::end('Connect'); ?>
<style type="text/css">
	.input {
		margin-bottom: 20px;
	}

	label {
		display: block;
	}
</style>
<?php
		}
		
		public static function redirect($url) {
			header('Location: ' . $url);
			exit();
		}
		
		public static function here() {
			return self::$url;
		}
		
		public static function ls($folder) {
			if(substr($folder, -1) == '/') {
				$folder = substr($folder, 0, -1);
			}
			
			$dir = scandir($folder);
			$files = array();
			
			foreach($dir as $file) {
				if($file == '.' || $file == '..') {
					continue;
				}
				
				$name = preg_replace('/^[0-9_]*/', '', preg_replace('/\.markdown$/', '', $file));
				$number = false;
				$hidden = (substr($file, 0, 1) == '_');
				
				$matches = array();
				preg_match('/^_?([0-9]*)/', $file, $matches);
				if(isset($matches[1])) {
					$number = $matches[1];
				}
				
				$file = array(
					'name' => $name,
					'file' => $file,
					'folder' => is_dir($folder . '/' . $file),
					'number' => $number,
					'hidden' => $hidden
				);
				
				if(!isset($files[$name]) || $files[$name]['number'] == false || $files[$name]['number'] > $number || ($files[$name]['folder'] == true && $folder == false)) {
					$files[$name] = $file;
				}
			}
			
			return $files;
		}
		
		private static function sanitize($url) {
			$path = explode('/', $url);
			
			$sanPath = array();
			
			foreach($path as $folder) {
				$folder = trim($folder);
				if($folder != '') {
					$sanPath[] = $folder;
				}
			}
			
			if(count($sanPath) > 0) {
				$sanUrl = '/' . implode('/', $sanPath);
			} else {
				$sanUrl = '';
			}
			
			return $sanUrl;
		}
		
		private static function render($url) {
			self::$url = $url;
			
			$file = self::urlToFile($url);
			if($file === false) {
				self::error404();
				return;
			}
			
			$markdown = new Markdown(file_get_contents($file));
			
			Layout::setActive($url);
			Layout::setContent($markdown->getHtml());
			Layout::setNavigation(self::buildNavigation());
			
			$title = false;
			
			if($title === false) {
				$pageTitle = $markdown->getHeader('title');
				if($pageTitle !== false) {
					$title = $pageTitle;
				}
			}
			
			if($title === false) {
				$navTitle = $markdown->getHeader('navtitle');
				if($navTitle !== false) {
					$title = $navTitle;
				}
			}
			
			if($title === false) {
				$title = explode('/', $url);
				$title = array_pop($title);
				$title = ucfirst($title);
				if($title === '') {
					$title = false;
				}
			}
			
			Layout::setTitle($title);
			
			$layout = $markdown->getHeader('layout');
			if($layout !== false) {
				Layout::setLayout($layout);
			}
			
			Layout::render();
		}
		
		private static function error404() {
			header("HTTP/1.0 404 Not Found");
			
			Layout::setContent(<<<CONTENT
<h1>Error 404: Page not found</h1>
<p>The page you have been looking for could not be found.</p>
CONTENT
);
			Layout::setActive(false);
			Layout::setNavigation(self::buildNavigation());
			Layout::setTitle('Page not found');
			Layout::render();
			
			exit;
		}
		
		private static function urlToFile($url) {
			$file = PAGES_PATH;
			
			if(strlen($url) > 0 && substr($url, 0, 1) == '/') {
				$url = substr($url, 1);
			}
			if(strlen($url) > 0 && substr($url, -1) == '/') {
				$url = substr($url, 0, -1);
			}
			
			if($url == '') {
				$file .= 'index.markdown';
				return $file;
			}
			
			$path = explode('/', $url);
			$count = count($path);
			
			for($i = 0; $i < $count; ++$i) {
				$dir = self::ls($file);
				if(!isset($dir[$path[$i]])) {
					return false;
				}
				
				$element = $dir[$path[$i]];
				if($element['hidden'] == true) {
					return false;
				}
				
				$file .= $element['file'];
				if($element['folder']) {
					$file .= DS;
				}
			}
			
			if($element['folder']) {
				$file .= 'index.markdown';
				if(!is_file($file)) {
					return false;
				}
			}
			
			return $file;
		}
		
		private static function buildNavigation($path = PAGES_PATH) {
			$navigation = array();
			
			$ls = self::ls($path);
			foreach($ls as $item) {
				if($item['number'] != '' && $item['hidden'] == false) {
					$item['sub'] = array();
					
					if($item['folder'] == true) {
						$item['sub'] = self::buildNavigation($path . '/' . $item['file']);
					}
					
					$header = Header::fromFile($path . '/' . $item['file'] . ($item['folder'] ? '/index.markdown' : ''));
					
					$item['navtitle'] = $header->getHeader('NavTitle');
					
					$navigation[$item['name']] = $item;
				}
			}
			
			return $navigation;
		}
	}
	
?>