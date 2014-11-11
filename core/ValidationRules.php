<?php
	
	namespace Advitum\Cmd;
	
	class ValidationRules
	{
		public static function notEmpty($value) {
			return !(empty($value) && $value !== 0);
		}
	}
	
?>