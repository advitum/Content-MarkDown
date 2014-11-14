<?php
	
	namespace Advitum\Cmd;
	
	abstract class PluginContainer
	{
		protected static function replacePlugins($content) {
			$content = preg_replace_callback('/<cmd:navigation\s+(?P<params>([a-z-]+=("[^"]*"|\'[^\']*\')\s+)+)?\/>/s', function($matches) {
				$params = array();
				if(isset($matches['params'])) {
					$params = self::parseParams($matches['params']);
				}
				
				return Layout::renderNavigation($params);
			}, $content);
			
			$content = preg_replace_callback('/<cmd:plugin\s+(?P<params>([a-z-]+=("[^"]*"|\'[^\']*\')\s+)+)?\/>/s', function($matches) {
				$html = $matches[0];
				
				$params = array();
				if(isset($matches['params'])) {
					$params = self::parseParams($matches['params']);
					if(isset($params['plugin'])) {
						$plugin = $params['plugin'];
						unset($params['plugin']);
						
						if(is_file(CORE_PLUGINS_PATH . $plugin . DS . $plugin . '.php')) {
							require_once(CORE_PLUGINS_PATH . $plugin . DS . $plugin . '.php');
							
							$plugin = __NAMESPACE__ . '\\Plugins\\' . $plugin;
							$plugin = new $plugin($params);
							$html = $plugin->frontend();
						} elseif(is_file(PLUGINS_PATH . $plugin . DS . $plugin . '.php')) {
							require_once(PLUGINS_PATH . $plugin . DS . $plugin . '.php');
							
							$plugin = new $plugin($params);
							$html = $plugin->frontend();
						}
					}
				}
				
				return $html;
			}, $content);
			
			return $content;
		}
		
		protected static function parseParams($attributes) {
			$params = array();
			
			$matches = array();
			if(preg_match_all('/([a-z-]+)=("([^"]*)"|\'([^\']*)\')/s', $attributes, $matches, PREG_SET_ORDER)) {
				foreach($matches as $match) {
					$attribute = $match[1];
					if(isset($match[4])) {
						$value = $match[4];
					} else {
						$value = $match[3];
					}
					
					if(is_numeric($value)) {
						$value = $value * 1;
					} elseif(strtolower($value) === 'true') {
						$value = true;
					} elseif(strtolower($value) === 'false') {
						$value = false;
					} else {
						$json = json_decode(htmlspecialchars_decode($value));
						if($json !== null) {
							$value = $json;
						}
					}
					
					$params[$attribute] = $value;
				}
			}
			
			return $params;
		}
	}
	
?>