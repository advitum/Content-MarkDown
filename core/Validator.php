<?php
	
	namespace Advitum\Cmd;
	
	class Validator
	{
		public static $errors = array();
		
		public static function validate($form, $fields) {
			self::$errors[$form] = array();
			
			$defaultOptions = array(
				'file' => false,
				'required' => true,
				'message' => 'Please input something in field %s.'
			);
			
			foreach($fields as $field => $options) {
				if(is_numeric($field)) {
					$field = $options;
					$options = array();
				}
				
				$options = array_merge($defaultOptions, $options);
				$error = false;
				
				if($options['file'] == false) {
					$value = false;
					
					if(isset($_GET[$form][$field])) {
						$value = $_GET[$form][$field];
					}
					if(isset($_POST[$form][$field])) {
						$value = $_POST[$form][$field];
					}
					
					if($options['required'] && ($value === false || (empty($value) && $value !== 0))) {
						$error = true;
					}
				} else {
					
				}
				
				if($error) {
					self::$errors[$form][$field] = sprintf($options['message'], ucfirst($field));
				}
			}
			
			return count(self::$errors[$form]) == 0;
		}
		
		public static function errors($form) {
			if(!isset(self::$errors[$form])) {
				self::$errors[$form] = array();
			}
			
			return self::$errors[$form];
		}
		
		public static function error($form, $field) {
			if(!isset(self::$errors[$form]) || !isset(self::$errors[$form][$field])) {
				return false;
			} else {
				return self::$errors[$form][$field];
			}
		}
	}
	
?>