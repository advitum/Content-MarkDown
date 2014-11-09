<?php
	
	namespace Advitum\Cmd;
	
	abstract class PluginContainer
	{
		protected static function replacePlugins($content) {
			$content = preg_replace_callback('/{(?P<plugin>[a-zA-Z]+(|.*)?)}/', function($matches) {
				$params = explode('|', $matches['plugin'], 2);
				$plugin = ucfirst(array_shift($params));
				
				if(count($params) > 0) {
					$params = $params[0];
				} else {
					$params = null;
				}
				
				if(is_file(CORE_PLUGINS_PATH . $plugin . DS . $plugin . '.php')) {
					require_once(CORE_PLUGINS_PATH . $plugin . DS . $plugin . '.php');
					
					$plugin = __NAMESPACE__ . '\\Plugins\\' . $plugin;
					$plugin = new $plugin($params);
					return $plugin->frontend();
				} elseif(is_file(PLUGINS_PATH . $plugin . DS . $plugin . '.php')) {
					require_once(PLUGINS_PATH . $plugin . DS . $plugin . '.php');
					
					$plugin = new $plugin($params);
					return $plugin->frontend();
				}
				
				return $matches[0];
			}, $content);
			
			return $content;
		}
	}
	
?>