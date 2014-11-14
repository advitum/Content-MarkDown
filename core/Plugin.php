<?php
	
	namespace Advitum\Cmd;
	
	abstract class Plugin
	{
		protected $params = array();
		
		public function __construct($params) {
			if(is_array($params)) {
				$this->params = $params;
			} else {
				$params = explode(';', $params);
				foreach($params as $param) {
					$param = explode(':', $param, 2);
					if(count($param) == 2) {
						$this->params[$param[0]] = $param[1];
					}
				}
			}
		}
		
		public function path() {
			$reflector = new \ReflectionClass(get_called_class());
			
			$path = dirname($reflector->getFilename()) . DS;
			
			return $path;
		}
		
		public function url() {
			$path = $this->path();
			
			$url = str_replace(DS, '/', mb_substr($path, mb_strlen(ROOT_PATH) - 1));
			
			return $url;
		}
		
		abstract public function frontend();
	
		protected final function error($message) {
			return '**Error**: ' . $message . ' (Plugin ' . get_called_class() . ')';
		}
	}