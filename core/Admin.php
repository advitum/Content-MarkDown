<?php
	
	namespace Advitum\Cmd;
	
	class Admin
	{
		private $navigation = array(
			'Content' => array(
				array(
					'url' => '/admin/pages',
					'title' => 'Pages'
				)
			),
			'Users' => array(
				array(
					'url' => '/admin/users/list',
					'title' => 'List'
				),
				array(
					'url' => '/admin/users/add',
					'title' => 'Add'
				)
			),
			'Plugins' => array(),
			array(
				array(
					'url' => '/admin/logout',
					'title' => 'Logout'
				)
			)
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
			
			Admin::addScript('jquery', CONTENT_URL . 'admin/js/jquery-1.11.1.min.js');
			Admin::addScript('textarea', CONTENT_URL . 'admin/js/jquery.textarea.min.js', array('jquery'));
			Admin::addScript('lightbox', CONTENT_URL . 'admin/js/jquery.lightbox.js', array('jquery'));
			Admin::addScript('box', CONTENT_URL . 'admin/js/box.js', array('jquery', 'lightbox'));
			Admin::addScript('contextmenu', CONTENT_URL . 'admin/js/contextmenu.js', array('jquery'));
			Admin::addScript('admin', CONTENT_URL . 'admin/js/admin.js', array('jquery', 'textarea', 'lightbox', 'box', 'contextmenu'));
			
			Admin::addStylesheet('admin', CONTENT_URL . 'admin/css/admin.css');
			
			User::create('admin', 'admin');
			
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
			
			if($this->user && $this->user->username !== 'admin') {
				unset($this->navigation['Users']);
			}
			
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
					if(Nonce::check('login')) {
						if(!Validator::validate('login', array(
							'username' => array(
								'message' => 'Please enter your username.'
							),
							'password' => array(
								'message' => 'Please enter your password.'
							)
						))) {
							Session::setMessage('Please check your input!', 'error');
						} elseif(!User::login($_POST['login']['username'], $_POST['login']['password'])) {
							Session::setMessage('The username or password is wrong.', 'error');
						} else {
							$this->user = User::get();
							Session::setMessage('Hello ' . htmlspecialchars($this->user->username) . ', welcome back!', 'success');
							Router::redirect('/admin');
						}
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
						Router::redirect('/admin');
						
						break;
					case 'plugin':
						if(isset($this->params[1]) && isset($this->plugins[$this->params[1]])) {
							$this->view = 'plugin';
						} else {
							Session::setMessage('The plugin "' . ucfirst($this->params[1]) . '" was not found.', 'error');
							Router::redirect('/admin');
						}
						
						break;
					case 'pages':
						$path = '/' . implode('/', array_slice($this->params, 1));
						
						if(isset($_GET['newfolder']) && !empty($_GET['newfolder'])) {
							if(Nonce::check('newFolder')) {
								if(is_dir(PAGES_PATH . $path) && mkdir(PAGES_PATH . $path . '/' . $_GET['newfolder']) && file_put_contents(PAGES_PATH . $path . '/' . $_GET['newfolder'] . '/index.markdown', '') !== false) {
									Session::setMessage('The new folder was created.', 'success');
									Router::redirect('/admin/pages' . $path . '/' . $_GET['newfolder']);
								} else {
									Session::setMessage('The new folder could not be created.', 'error');
								}
							}
							
							Router::redirect('/admin/pages' . $path);
						}
						
						if(isset($_GET['newpage']) && !empty($_GET['newpage'])) {
							if(Nonce::check('newPage')) {
								if(is_dir(PAGES_PATH . $path) && file_put_contents(PAGES_PATH . $path . '/' . $_GET['newpage'], '') !== false) {
									Session::setMessage('The new page was created.', 'success');
									Router::redirect('/admin/pages' . $path . '/' . $_GET['newpage']);
								} else {
									Session::setMessage('The new page could not be created.', 'error');
								}
							}
							
							Router::redirect('/admin/pages' . $path);
						}
						
						if(is_file(PAGES_PATH . $path) || is_dir(PAGES_PATH . $path)) {
							if(isset($_GET['rename']) && !empty($_GET['rename'])) {
								$newPath = dirname($path) . '/' . $_GET['rename'];
								
								if(Nonce::check('renamePage')) {
									if(rename(PAGES_PATH . $path, PAGES_PATH . $newPath)) {
										Session::setMessage('The page was renamed.', 'success');
										Router::redirect('/admin/pages' . $newPath);
									} else {
										Session::setMessage('The page could not be renamed.', 'error');
									}
								}
								Router::redirect('/admin/pages' . $path);
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
										Router::redirect('/admin/pages' . dirname($path) . '/' . $newName);
									} else {
										Session::setMessage('The page`s visibilty could not be hidden.', 'error');
									}
								}
								Router::redirect('/admin/pages' . $path);
							}
							
							if(isset($_GET['delete'])) {
								if(Nonce::check('deletePage')) {
									if((is_dir(PAGES_PATH . $path) && $this->rrmdir(PAGES_PATH . $path)) || unlink(PAGES_PATH . $path)) {
										Session::setMessage('The page was deleted.', 'success');
										Router::redirect('/admin/pages');
									} else {
										Session::setMessage('The page could not be deleted.', 'error');
									}
								}
								Router::redirect('/admin/pages' . $path);
							}
						}
						
						if(count($this->params) <= 1) {
							Router::redirect('/admin/pages/index.markdown');
						}
						
						if(!is_file(PAGES_PATH . $path)) {
							if(is_dir(PAGES_PATH . $path)) {
								if(is_file(PAGES_PATH . $path . '/index.markdown')) {
									Router::redirect('/admin/pages' . $path . '/index.markdown');
								} elseif(is_file(PAGES_PATH . $path . '/_index.markdown')) {
									Router::redirect('/admin/pages' . $path . '/_index.markdown');
								}
							}
							
							Session::setMessage('The page was not found.', 'error');
							Router::redirect('/admin/pages');
						}
						
						if(Form::sent('editPage')) {
							if(Nonce::check('editPage')) {
								file_put_contents(PAGES_PATH . $path, $_POST['editPage']['content']);
								Session::setMessage('The page was saved.', 'success');
							}
						}
						
						$this->view = 'pages';
						
						break;
					case 'users':
						if($this->user->username != 'admin') {
							Session::setMessage('You do not have the permission to access the user management.', 'error');
							Router::redirect('/admin/pages');
						} else {
							$this->view = 'users';
						}
						
						break;
					default:
						Router::redirect('/admin/pages');
						
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
	<title><?php echo ($this->title === null ? 'Backend - ' . SHORT_TITLE : htmlspecialchars($this->title) . ' - Backend - ' . SHORT_TITLE); ?></title>
	<meta name="referrer" content="never" />
	<!--[if lt IE 9]><script type="text/javascript" src="<?php echo CONTENT_URL; ?>admin/js/html5shiv.js"></script><![endif]-->
	
	<?php
		
		foreach(self::$stylesheets as $id => $stylesheet) {
			$this->loadStylesheet($id);
		}
		
	?>
</head>

<body>
	<?php echo Session::getMessage(); ?>
	
	<div id="sidebar">
		<header>
			<img src="<?php echo CONTENT_URL; ?>admin/img/cmd-logo.png" alt="Content MarkDown" />
		</header>
		<?php if($this->user !== null) { ?><nav>
			<?php $this->navigation(); ?>
		</nav><?php } ?>
		<footer>
			<p><a href="https://github.com/advitum/Content-MarkDown">Content MarkDown</a> is released under the <a href="http://opensource.org/licenses/MIT" title="MIT License">MIT License</a>.</p>
		</footer>
	</div>
	
	<h1><?php echo ($this->title === null ? 'Backend' : htmlspecialchars($this->title)); ?></h1><?php
		}
		
		private function footer() {
?>
	
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
				'label' => 'Username',
				'placeholder' => 'Username',
				'autofocus' => 'autofocus'
			));
			$html .= Form::input('password', array(
				'label' => 'Password',
				'placeholder' => 'Password'
			));
			$html .= Form::submit('Login');
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
		
		private function render_users() {
			$params = array_slice($this->params, 1);
			
			if(count($params) == 0) {
				Router::redirect('/admin/users/list');
			}
			
			$html = '';
			switch($params[0]) {
				case 'add':
					if(Form::sent('addUser') && Nonce::check('addUser')) {
						if(!Validator::validate('addUser', array(
							'username' => array(
								'rules' => array(
									'notEmpty',
									function($value) {
										return DB::count(sprintf("SELECT COUNT(*) FROM `users` WHERE `username` = '%s'", DB::escape($value))) == 0;
									}
								),
								'message' => 'Please enter a valid, unique username.'
							),
							'password' => array(
								'message' => 'Please enter a password.'
							)
						))) {
							Session::setMessage('The user could not be created. Please check your input.', 'error');
						} else {
							User::create(Form::value('addUser', 'username'), Form::value('addUser', 'password'));
							Session::setMessage('The user was created.', 'success');
							Router::redirect('/admin/users/list');
						}
					}
					
					$html .= Form::create('/admin/users/add', 'addUser');
					$html .= Nonce::field('addUser');
					$html .= Form::input('username', array(
						'title' => 'Username',
						'placeholder' => 'Username',
						'autofocus' => 'autofocus'
					));
					$html .= Form::input('password', array(
						'title' => 'Password',
						'placeholder' => 'Password'
					));
					$html .= Form::submit('<i class="fa fa-save"></i>', array(
						'escape' => false
					));
					$html .= Form::end();
					
					$this->title = 'Add User - Users';
					break;
				case 'edit':
					if(!isset($params[1])) {
						Session::setMessage('Please select a user to edit.', 'error');
						Router::redirect('/admin/users/list');
					}
					$user = DB::selectSingle(sprintf("SELECT * FROM `users` WHERE `id` = %d", $params[1]));
					if(!$user) {
						Session::setMessage('The user was not found. Maybe it was deleted?', 'error');
						Router::redirect('/admin/users/list');
					}
					
					
					if(Form::sent('editUser') && Nonce::check('editUser_' . $user->id)) {
						if($user->username == 'admin') {
							if(!Validator::validate('editUser', array(
								'password' => array(
									'message' => 'Please enter a password for this user.'
								)
							))) {
								Session::setMessage('Your changes could not be saved. Please check your input.', 'error');
							} else {
								User::update($user->id, $user->username, Form::value('editUser', 'password'));
								Session::setMessage('The password was changed.', 'success');
								Router::redirect('/admin/users/list');
							}
						} else {
							if(!Validator::validate('editUser', array(
								'username' => array(
									'rules' => array(
										'notEmpty', function($username) use($user) {
											return $username == $user->username || DB::count(sprintf("SELECT COUNT(*) FROM `users` WHERE `username` = '%s'", DB::escape($username))) == 0;
										}
									),
									'message' => 'Please enter a valid, unique username.'
								)
							))) {
								Session::setMessage('Your changes could not be saved. Please check your input.', 'error');
							} else {
								$data = array(
									'username' => Form::value('editUser', 'username')
								);
								if(!empty(Form::value('editUser', 'password'))) {
									$data['password'] = User::generateHash(Form::value('editUser', 'password'));
								}
								
								DB::update('users', $data, sprintf("WHERE `id` = %d", DB::escape($user->id)));
								
								Session::setMessage('Your changes were saved.', 'success');
								Router::redirect('/admin/users/list');
							}
						}
					}
					
					
					$this->title = 'Edit user "' . $user->username . '"';
					
					$html .= Form::create('/admin/users/edit/' . $user->id, 'editUser');
					$html .= Nonce::field('editUser_' . $user->id);
					
					$usernameAttributes = array(
						'label' => 'Username',
						'placeholder' => 'Username',
						'default' => $user->username
					);
					$passwordAttributes = array(
						'label' => 'Password',
						'placeholder' => 'Password'
					);
					if($user->username == 'admin') {
						$usernameAttributes['disabled'] = 'disabled';
						$usernameAttributes['label'] .= ' (The username of the admin cannot be changed)';
						$passwordAttributes['autofocus'] = 'autofocus';
					} else {
						$usernameAttributes['autofocus'] = 'autofocus';
						$passwordAttributes['label'] .= ' (leave empty to keep the current password)';
					}
					
					$html .= Form::input('username', $usernameAttributes);
					$html .= Form::input('password', $passwordAttributes);
					$html .= Form::submit('<i class="fa fa-save"></i>', array(
						'escape' => false
					));
					$html .= Form::end();
					break;
				case 'delete':
					if(!isset($params[1])) {
						Session::setMessage('Please select a user to delete.', 'error');
					} else {
						$user = DB::selectSingle(sprintf("SELECT * FROM `users` WHERE `id` = %d", $params[1]));
						if(!$user) {
							Session::setMessage('The user was not found. Maybe it has already been deleted?', 'error');
						} elseif(Nonce::check('deleteUser_' . $user->id)) {
							User::delete($user->id);
							Session::setMessage('The user was deleted.', 'success');
						}
					}
					
					Router::redirect('/admin/users/list');
					break;
				default:
					$users = DB::selectArray("SELECT `id`, `username`, `lastseen`, `created` FROM `users` ORDER BY `lastseen` DESC");
					
					$html .= '<div class="buttonBar">
						<a href="/admin/users/add" class="button"><i class="fa fa-plus"></i></a>
					</div>';
					
					$html .= Html::arrayTable($users, array(
						'username' => array(
							'title' => 'Username'
						),
						'lastseen' => array(
							'title' => 'Last seen'
						),
						'created' => array(
							'title' => 'Created'
						),
						'actions' => array(
							'title' => '',
							'callback' => function($user) {
								$actions = array('<a href="/admin/users/edit/' . $user->id . '" class="action"><i class="fa fa-pencil"></i></a>');
								
								if($user->username != 'admin') {
									$actions[] = '<a href="/admin/users/delete/' . $user->id . '?_nonce=' . urlencode(Nonce::get('deleteUser_' . $user->id)) . '" class="action confirm" data-confirm="Are you sure you want to delete the user &quot;' . htmlspecialchars($user->username) . '&quot;?"><i class="fa fa-trash"></i></a>';
								}
								
								return implode(' ', $actions);
							}
						)
					));
					
					$this->title = 'Users';
					break;
			}
			
			return $html;
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
	<button id="newFolder"><i class="fa fa-folder"></i><i class="fa fa-plus fa-small fa-invert"></i></button>
	<button id="newPage"><i class="fa fa-file"></i><i class="fa fa-plus fa-small fa-invert"></i></button>';
			
			$html .= $this->render_pages_tree();
			$html .= '</div><div id="pageContent">';
			
			$path = '/' . implode('/', array_slice($this->params, 1));
			
			if(is_file(PAGES_PATH . $path)) {
				$form = new Form();
				
				$html .= $form->create('/admin/pages' . $path, 'editPage');
				$html .= Nonce::field('editPage');
				
				$html .= '<div class="submit">';
				$html .= $form->submit('<i class="fa fa-floppy-o"></i>', array(
					'escape' => false,
					'div' => false
				));
				
				if(!preg_match('/\/[0-9]*_/', $path)) {
					$html .= ' <a href="' . preg_replace('/\/[0-9_]*/', '/', preg_replace('/(index)?\.markdown$/', '', $path)) . '" target="_blank" class="button"><i class="fa fa-eye"></i></a>';
				}
				$html .= '</div>';
				
				$html .= '<div class="input mdEdit">' . $form->input('content', array(
					'type' => 'textarea',
					'default' => str_replace("\t", "    ", file_get_contents(PAGES_PATH . $path)),
					'label' => false,
					'div' => false
				)) . '</div>';
			}
			
			$html .= '</div>';
			
			$this->title = 'Pages';
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