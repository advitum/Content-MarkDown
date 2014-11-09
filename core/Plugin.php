<?php
	
	namespace Advitum\Cmd;
	
	abstract class Plugin
	{
		protected $params = array();
		
		public function __construct($params) {
			$this->params = $params;
		}
		
		abstract public function frontend();
	
		protected final function error($message) {
			return '**Error**: ' . $message . ' (Plugin ' . get_called_class() . ')';
		}
	}