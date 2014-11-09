<?php
	
	namespace Advitum\Cmd;
	
	class Admin
	{
		private $navigation = array(
			'Content' => array(
				array(
					'url' => '/admin/pages',
					'title' => 'Pages'
				),
				array(
					'url' => '/admin/logout',
					'title' => 'Logout'
				)
			),
			'Plugins' => array()
		);
		private $params = array();
		private $plugins = array();
		private static $scripts = array();
		private static $stylesheets = array();
		private $title = null;
		private $user = null;
		private $view = null;
		
		public static function init($params) {
			$admin = new Admin($params);
			
			Admin::addScript('jquery', '/content/admin/js/jquery-1.11.1.min.js');
			Admin::addScript('textarea', '/content/admin/js/jquery.textarea.min.js', array('jquery'));
			Admin::addScript('lightbox', '/content/admin/js/lightbox.js', array('jquery'));
			Admin::addScript('box', '/content/admin/js/box.js', array('jquery', 'lightbox'));
			Admin::addScript('contextmenu', '/content/admin/js/contextmenu.js', array('jquery'));
			Admin::addScript('admin', '/content/admin/js/admin.js', array('jquery', 'textarea', 'lightbox', 'box', 'contextmenu'));
			
			Admin::addStylesheet('admin', '/content/admin/css/admin.css');
			
			$admin->render();
		}
		
		public static function addScript($id, $file, $dependencies = array()) {
			if(!isset(self::$scripts[$id])) {
				self::$scripts[$id] = array(
					'file' => $file,
					'dependencies' => $dependencies,
					'loaded' => false
				);
			}
		}
		
		public static function addStylesheet($id, $file, $position = 0) {
			if(!isset(self::$stylesheets[$id])) {
				self::$stylesheets[$id] = array(
					'file' => $file,
					'position' => $position,
					'loaded' => false
				);
			}
		}
		
		private function __construct($params) {
			$this->params = $params;
			$this->user = User::get();
			
			$plugins = scandir(CORE_PLUGINS_PATH);
			foreach($plugins as $plugin) {
				if($plugin == '.' || $plugin == '..') {
					continue;
				}
				
				if(is_dir(CORE_PLUGINS_PATH . $plugin) && is_file(CORE_PLUGINS_PATH . $plugin . DS . $plugin . '.php')) {
					require_once(CORE_PLUGINS_PATH . $plugin . '/' . $plugin . '.php');
					
					if(method_exists(__NAMESPACE__ . '\\Plugins\\' . $plugin, 'backend')) {
						$this->plugins[strtolower($plugin)] = array(
							'title' => $plugin,
							'class' => __NAMESPACE__ . '\\Plugins\\' . $plugin
						);
					}
				}
			}
			
			$plugins = scandir(PLUGINS_PATH);
			foreach($plugins as $plugin) {
				if($plugin == '.' || $plugin == '..') {
					continue;
				}
				
				if(!in_array($plugin, $this->plugins) && is_dir(PLUGINS_PATH . $plugin) && is_file(PLUGINS_PATH . $plugin . DS . $plugin . '.php')) {
					require_once(PLUGINS_PATH . $plugin . '/' . $plugin . '.php');
					
					if(method_exists($plugin, 'backend')) {
						$this->plugins[strtolower($plugin)] = array(
							'title' => $plugin,
							'class' => $plugin
						);
					}
				}
			}
			
			ksort($this->plugins);
			foreach($this->plugins as $key => $plugin) {
				$this->navigation['Plugins'][] = array(
					'url' => '/admin/plugin/' .  $key,
					'title' => $plugin['title']
				);
			}
		}
		
		private function render() {
			if($this->user === null) {
				$this->view = 'login';
				
				if(Form::sent('login')) {
					if(!Validator::validate('login', array(
						'username' => array(
							'message' => 'Please enter your username.'
						),
						'password' => array(
							'message' => 'Please enter your password.'
						)
					))) {
						Session::setMessage('Please check your input!', 'error');
					} elseif(!Nonce::check('login')) {
						Session::setMessage('Bist Du sicher, dass Du das tun willst?', 'error');
					} elseif(!User::login($_POST['login']['username'], $_POST['login']['password'])) {
						Session::setMessage('The username or password is wrong.', 'error');
					} else {
						$this->user = User::get();
						Session::setMessage('Hello ' . htmlspecialchars($this->user->username) . ', welcome back!', 'success');
						header('Location: /admin');
						exit();
					}
				}
			} else {
				if(!isset($this->params[0])) {
					$this->params[0] = '';
				}
				
				switch($this->params[0]) {
					case 'logout':
						User::logout();
						Session::setMessage('You were successfully logged out.', 'success');
						header('Location: /admin');
						exit();
						
						break;
					case 'plugin':
						if(isset($this->params[1]) && isset($this->plugins[$this->params[1]])) {
							$this->view = 'plugin';
						} else {
							Session::setMessage('The plugin "' . ucfirst($this->params[1]) . '" was not found.', 'error');
							header('Location: /admin');
							exit();
						}
						
						break;
					case 'pages':
						$path = '/' . implode('/', array_slice($this->params, 1));
						
						if(isset($_GET['newfolder']) && !empty($_GET['newfolder'])) {
							if(Nonce::check('newFolder')) {
								if(is_dir(PAGES_PATH . $path) && mkdir(PAGES_PATH . $path . '/' . $_GET['newfolder']) && file_put_contents(PAGES_PATH . $path . '/' . $_GET['newfolder'] . '/index.markdown', '') !== false) {
									Session::setMessage('The new folder was created.', 'success');
									header('Location: /admin/pages' . $path . '/' . $_GET['newfolder']);
									exit();
								} else {
									Session::setMessage('The new folder could not be created.', 'error');
								}
							}
							
							header('Location: /admin/pages' . $path);
							exit();
						}
						
						if(isset($_GET['newpage']) && !empty($_GET['newpage'])) {
							if(Nonce::check('newPage')) {
								if(is_dir(PAGES_PATH . $path) && file_put_contents(PAGES_PATH . $path . '/' . $_GET['newpage'], '') !== false) {
									Session::setMessage('The new page was created.', 'success');
									header('Location: /admin/pages' . $path . '/' . $_GET['newpage']);
									exit();
								} else {
									Session::setMessage('The new page could not be created.', 'error');
								}
							}
							
							header('Location: /admin/pages' . $path);
							exit();
						}
						
						if(is_file(PAGES_PATH . $path) || is_dir(PAGES_PATH . $path)) {
							if(isset($_GET['rename']) && !empty($_GET['rename'])) {
								$newPath = dirname($path) . '/' . $_GET['rename'];
								
								if(Nonce::check('renamePage')) {
									if(rename(PAGES_PATH . $path, PAGES_PATH . $newPath)) {
										Session::setMessage('The page was renamed.', 'success');
										header('Location: /admin/pages' . $newPath);
										exit();
									} else {
										Session::setMessage('The page could not be renamed.', 'error');
									}
								}
								header('Location: /admin/pages' . $path);
								exit();
							}
							
							if(isset($_GET['togglevisibility'])) {
								$newName = basename($path);
								$visible = substr($newName, 0, 1) == '_';
								if($visible) {
									$newName = substr($newName, 1);
								} else {
									$newName = '_' . $newName;
								}
								
								if(Nonce::check('toggleVisibiltyPage')) {
									if(rename(PAGES_PATH . $path, PAGES_PATH . dirname($path) . '/' . $newName)) {
										Session::setMessage('The page ' . ($visible ? 'is now visible' : 'is now hidden') . '.', 'success');
										header('Location: /admin/pages' . dirname($path) . '/' . $newName);
										exit();
									} else {
										Session::setMessage('The page`s visibilty could not be hidden.', 'error');
									}
								}
								header('Location: /admin/pages' . $path);
								exit();
							}
							
							if(isset($_GET['delete'])) {
								if(Nonce::check('deletePage')) {
									if((is_dir(PAGES_PATH . $path) && $this->rrmdir(PAGES_PATH . $path)) || unlink(PAGES_PATH . $path)) {
										Session::setMessage('The page was deleted.', 'success');
										header('Location: /admin/pages');
										exit();
									} else {
										Session::setMessage('The page could not be deleted.', 'error');
									}
								}
								header('Location: /admin/pages' . $path);
								exit();
							}
						}
						
						if(count($this->params) <= 1) {
							header('Location: /admin/pages/index.markdown');
							exit();
						}
						
						if(!is_file(PAGES_PATH . $path)) {
							if(is_dir(PAGES_PATH . $path)) {
								if(is_file(PAGES_PATH . $path . '/index.markdown')) {
									header('Location: /admin/pages' . $path . '/index.markdown');
									exit();
								} elseif(is_file(PAGES_PATH . $path . '/_index.markdown')) {
									header('Location: /admin/pages' . $path . '/_index.markdown');
									exit();
								}
							}
							
							Session::setMessage('The page was not found.', 'error');
							header('Location: /admin/pages');
							exit();
						}
						
						if(Form::sent('editPage')) {
							if(Nonce::check('editPage')) {
								file_put_contents(PAGES_PATH . $path, $_POST['editPage']['content']);
								Session::setMessage('The page was saved.', 'success');
							}
						}
						
						$this->view = 'pages';
						
						break;
					default:
						header('Location: /admin/pages');
						exit();
						
						break;
				}
			}
			
			$content = '';
			if($this->view !== null) {
				$view = 'render_' . $this->view;
				$content = $this->$view();
			}
			
			$this->header();
			echo $content;
			$this->footer();
		}
		
		private function header() {
?><!DOCTYPE html>
<html>

<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<title><?php echo ($this->title === null ? 'Backend - ' . SHORT_TITLE : $this->title . ' - Backend - ' . SHORT_TITLE); ?></title>
	<meta name="referrer" content="never" />
	<!--[if lt IE 9]><script type="text/javascript" src="/content/admin/js/html5shiv.js"></script><![endif]-->
	
	<?php
		
		foreach(self::$stylesheets as $id => $stylesheet) {
			$this->loadStylesheet($id);
		}
		
	?>
</head>

<body>
	<?php Session::printMessage(); ?>
	
	<?php if($this->user !== null) { ?><nav id="main">
		<?php $this->navigation(); ?>
	</nav><?php } ?>
	
	<div id="content">
		<header>
			<h1><?php echo ($this->title === null ? 'Backend' : $this->title); ?></h1>
		</header><?php
		}
		
		private function footer() {
?></div>
	
	<?php
		
		foreach(self::$scripts as $id => $script) {
			$this->loadScript($id);
		}
		
	?>
</body>

</html><?php
		}
		
		private function navigation() {
			foreach($this->navigation as $title => $section) {
				if(count($section) == 0) continue;
				
				?><section>
	<?php if(!is_numeric($title)) { ?>
	<h2><?php echo htmlspecialchars($title); ?></h2>
	<?php } ?>
	<?php
				
				foreach($section as $link) {
					$active = isset($link['active']) ? $link['active'] : $link['url'];
					$active = substr('/admin/' . implode('/', $this->params), 0, strlen($active)) == $active;
					
					echo '<a ' . Html::attributes(array(
						'href' => $link['url'],
						'class' => ($active ? 'active' : null)
					)) . '>' . htmlspecialchars($link['title']) . '</a>';
				}
				
	?>
</section><?php
			}
		}
		
		private function loadScript($id, $cyclic = array()) {
			if(!isset(self::$scripts[$id])) {
				die('Fatal error: Script ' . $id . ' not defined'); exit();
			}
			
			$script = self::$scripts[$id];
			
			if($script['loaded'] === false) {
				if(in_array($id, $cyclic)) {
					die('Fatal error: Cyclic script dependency'); exit();
				}
				
				foreach($script['dependencies'] as $dependency) {
					$cyclic[] = $id;
					$this->loadScript($dependency, $cyclic);
				}
				
				echo '<script type="text/javascript" src="' . $script['file'] . '"></script>';
				$script['loaded'] = true;
				self::$scripts[$id] = $script;
			}
		}
		
		private function loadStylesheet($id) {
			if(!isset(self::$stylesheets[$id])) {
				die('Fatal error: Stylesheet ' . $id . ' not defined'); exit();
			}
			
			$stylesheet = self::$stylesheets[$id];
			
			if($stylesheet['loaded'] === false) {
				echo '<link rel="stylesheet" type="text/css" href="' . $stylesheet['file'] . '" />';
				$stylesheet['loaded'] = true;
				self::$stylesheets[$id] = $stylesheet;
			}
		}
		
		private function render_login() {
			$html = Form::create('/admin', 'login');
			$html .= Nonce::field('login');
			$html .= Form::input('username', array(
				'label' => 'Nutzername'
			));
			$html .= Form::input('password', array(
				'label' => 'Passwort'
			));
			$html .= Form::submit('Anmelden');
			$html .= Form::end();
			
			return $html;
		}
		
		private function render_plugin() {
			$plugin = $this->plugins[$this->params[1]];
			
			$this->title = $plugin['title'] . ' - Plugins';
			
			$params = array_slice($this->params, 2);
			$pluginInstance = new $plugin['class']($params);
			
			$content = $pluginInstance->backend();
			
			return $content;
		}
		
		private function render_pages() {
			$html = '<script type="text/javascript">
	var nonces = {
		renamePage: "' . Nonce::get('renamePage') . '",
		toggleVisibiltyPage: "' . Nonce::get('toggleVisibiltyPage') . '",
		deletePage: "' . Nonce::get('deletePage') . '",
		newFolder: "' . Nonce::get('newFolder') . '",
		newPage: "' . Nonce::get('newPage') . '"
	};
</script>
<div id="tree">
	<button id="newFolder"><span class="fa fa-stack"><i class="fa fa-folder fa-stack-2x"></i><i class="fa fa-plus fa-stack-1x"></i></span></button>
	<button id="newPage"><span class="fa fa-stack"><i class="fa fa-file fa-stack-2x"></i><i class="fa fa-plus fa-stack-1x"></i></span></button>';
			
			$html .= $this->render_pages_tree();
			$html .= '</div><div id="pageContent">';
			
			$path = '/' . implode('/', array_slice($this->params, 1));
			
			if(is_file(PAGES_PATH . $path)) {
				$form = new Form();
				
				$html .= $form->create('/admin/pages' . $path, 'editPage');
				$html .= Nonce::field('editPage');
				$html .= $form->submit('<i class="fa fa-floppy-o"></i>', array(
					'escape' => false
				));
				$html .= $form->input('content', array(
					'type' => 'textarea',
					'class' => 'mdEdit',
					'default' => str_replace("\t", "    ", file_get_contents(PAGES_PATH . $path)),
					'label' => false
				));
			}
			
			$html .= '</div>';
			
			return $html;
		}
		
		private function render_pages_tree($path = '/') {
			$current = '/' . implode('/', array_slice($this->params, 1));
			$pages = Router::ls(PAGES_PATH . $path);
			
			$html = '<ul>';
			
			foreach($pages as $page) {
				$subPath = $path . $page['file'];
				$url = $path . $page['file'];
				$classes = array();
				
				if($page['folder']) {
					$classes[] = 'folder';
				}
				if(substr($page['file'], 0, 1) == '_') {
					$classes[] = 'hidden';
				}
				
				if($subPath == substr($current, 0, strlen($subPath))) {
					$classes[] = 'active';
				}
				
				$html .= '<li class="' . implode(' ', $classes) . '">
					<a href="/admin/pages' . $url . '">' . $page['file'] . '</a>';
				
				if($page['folder']) {
					$html .= $this->render_pages_tree($subPath . '/');
				}
				
				$html .= '</li>';
			}
			
			$html .= '</ul>';
			
			return $html;
		}
		
		private function rrmdir($dir) {
			if(is_dir($dir)) {
				$objects = scandir($dir);
				foreach($objects as $object) {
					if ($object != "." && $object != "..") {
						if (filetype($dir . "/" . $object) == "dir") {
							$this->rrmdir($dir . "/" . $object);
						} else {
							unlink($dir . "/" . $object);
						}
					}
				}
				reset($objects);
				return rmdir($dir);
			}
			return false;
		}
	}
	
?>