<?php
	
	namespace Advitum\Cmd;
	
	function debug() {
		$backtrace = debug_backtrace();
		
		echo '<strong>' . $backtrace[0]['file'] . ':' . $backtrace[0]['line'] . '</strong>';
		
		foreach($backtrace[0]['args'] as $arg) {
			echo '<pre>';
			print_r($arg);
			echo '</pre>';
		}
	}
	
?>