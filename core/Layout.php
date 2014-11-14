<?php
	
	namespace Advitum\Cmd;
	
	class Layout extends PluginContainer
	{
		private static $content = '';
		private static $navigation = array();
		private static $title = false;
		private static $layout = 'default';
		private static $active = false;
		
		private static $scripts = array();
		private static $stylesheets = array();
		
		private static $head = array();
		private static $foot = array();
		
		public static function render() {
			$html = file_get_contents(LAYOUTS_PATH . self::$layout . '.tpl');
			
			if(self::$title === false) {
				self::$title = LONG_TITLE;
			} else {
				self::$title .= ' - ' . SHORT_TITLE;
			}
			
			self::$head[] = '<meta http-equiv="Content-Type" content="text/html; charset=utf-8">';
			self::$head[] = '<title>' . self::$title . '</title>';
			self::$head[] = '<meta name="referrer" content="always" />';
			
			$html = preg_replace('/<cmd:content\s[^>]*?\/>/s', self::$content, $html);
			
			$html = self::replacePlugins($html);
			
			if(preg_match('/<cmd:message\s[^>]*\/>/s', $html)) {
				$html = preg_replace('/<cmd:message\s[^>]*\/>/s', Session::getMessage(), $html);
			}
			
			uasort(self::$stylesheets, function($a, $b) {
				return $a['position'] < $b['position'] ? -1 : 1;
			});
			
			foreach(self::$stylesheets as $id => $stylesheet) {
				self::loadStylesheet($id);
			}
			
			foreach(self::$scripts as $id => $script) {
				self::loadScript($id);
			}
			
			$html = preg_replace('/<cmd:head\s+[^>]*\/>/s', implode("\n", self::$head), $html);
			$html = preg_replace('/<cmd:foot\s+[^>]*\/>/s', implode("\n", self::$foot), $html);
			
			echo $html;
		}
		
		public static function setActive($active) {
			self::$active = $active;
		}
		
		public static function setContent($content) {
			self::$content = $content;
		}
		
		public static function setNavigation($navigation) {
			self::$navigation = $navigation;
		}
		
		public static function setTitle($title) {
			self::$title = $title;
		}
		
		public static function setLayout($layout) {
			self::$layout = $layout;
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
		
		public static function renderNavigation($params, $items = false, $path = '', $depth = 1) {
			$html = '';
			
			if($items === false) {
				$items = self::$navigation;
			}
			
			$defaults = array(
				'start' => 1,
				'end' => false,
				'active' => true,
				'home' => true
			);
			
			$params = array_merge($defaults, $params);
			
			$params = array_map(function($a) {
				if($a === 'true') {
					$a = true;
				} elseif($a === 'false') {
					$a = false;
				}
				return $a;
			}, $params);
			
			$list = true;
			if($params['start'] === $params['end']) {
				$list = false;
			}
			
			if($list) {
				$html .= '<ul>' . "\n";
			}
			
			if($params['home'] && $depth == 1 && $depth >= $params['start'] && ($params['end'] === false || $depth <= $params['end'])) {
				if($list) {
					$html .= '<li>';
				}
				
				$html .= '<a href="/" class="' . (self::$active !== false && self::$active == '' ? 'active' : '') . '">' . (is_string($params['home']) ? $params['home'] : 'Home') . '</a>';
				
				if($list) {
					$html .= '</li>';
				}
			}
			
			foreach($items as $item) {
				$newPath = $path . '/' . $item['name'];
				$active = self::$active !== false && $newPath == substr(self::$active, 0, strlen($newPath));
				
				if($depth >= $params['start'] && ($params['end'] === false || $depth <= $params['end'])) {
					if($list) {
						$html .= '<li>';
					}
					
					$classes = array();
					if($active) {
						$classes[] = 'active';
					}
					if(isset($item['sub']) && $item['sub'] !== false && count($item['sub'])) {
						$classes[] = 'sub';
					}
					
					$html .= '<a href="' . $newPath . '"' . (count($classes) ? ' class="' . implode(' ', $classes) . '"' : '') . '>' . ($item['navtitle'] !== false ? $item['navtitle'] : ucfirst($item['name'])) . '</a>';
				}
				
				if(($active || $params['active'] === false) && isset($item['sub']) && $item['sub'] !== false && count($item['sub'])) {
					$html .= self::renderNavigation($params, $item['sub'], $path . '/' . $item['name'], $depth + 1);
				}
				
				if($depth >= $params['start'] && ($params['end'] === false || $depth <= $params['end'])) {
					if($list) {
						$html .= '</li>';
					}
				}
				
				$html .= "\n";
			}
			
			if($list) {
				$html .= '</ul>' . "\n";
			}
			
			return $html;
		}
		
		private static function loadScript($id, $cyclic = array()) {
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
					self::loadScript($dependency, $cyclic);
				}
				
				self::$foot[] = '<script type="text/javascript" src="' . $script['file'] . '"></script>';
				$script['loaded'] = true;
				self::$scripts[$id] = $script;
			}
		}
		
		private static function loadStylesheet($id) {
			if(!isset(self::$stylesheets[$id])) {
				die('Fatal error: Stylesheet ' . $id . ' not defined'); exit();
			}
			
			$stylesheet = self::$stylesheets[$id];
			
			if($stylesheet['loaded'] === false) {
				self::$head[] = '<link rel="stylesheet" type="text/css" href="' . $stylesheet['file'] . '" />';
				$stylesheet['loaded'] = true;
				self::$stylesheets[$id] = $stylesheet;
			}
		}
	}
	
?>