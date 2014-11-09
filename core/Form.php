<?php
	
	namespace Advitum\Cmd;
	
	class Form
	{
		private static $form = null;
		
		public static function create($action, $form) {
			self::$form = $form;
			
			$html = '<form ' . self::attrs(array(
				'action' => $action,
				'method' => 'POST',
				'id' => self::$form
			)) . '>';
			$html .= self::hidden('_form', self::$form);
			
			return $html;
		}
		
		public static function end($label = false) {
			if(self::$form === null) {
				return '';
			}
			
			$html = '';
			
			if($label !== false) {
				$html .= self::submit($label);
			}
			
			$html .= '</form>';
			
			self::$form = null;
			
			return $html;
		}
		
		public static function input($name, $options = array()) {
			if(self::$form === null) {
				return '';
			}
			
			$defaultOptions = array(
				'type' => ($name == 'password' ? 'password' : 'text'),
				'label' => ucfirst($name),
				'id' => self::$form . '_' . $name,
				'default' => '',
				'class' => '',
				'div' => true
			);
			
			$options = array_merge($defaultOptions, $options);
			$error = Validator::error(self::$form, $name);
			if($error !== false) {
				$options['class'] .= ' error';
			}
			
			$value = ($options['type'] == 'password' ? false : (isset($_POST[self::$form][$name]) ? $_POST[self::$form][$name] : $options['default']));
			
			$html = '';
			
			if($options['div']) {
				$html .= '<div class="input">';
			}
			
			switch($options['type']) {
				case 'textarea':
					if($options['label'] !== false) { 
						$html .= '<label for="' . htmlspecialchars($options['id']) . '">' . htmlspecialchars($options['label']) . '</label>';
					}
					
					$html .= '<textarea ' . self::attrs(array(
						'id' => $options['id'],
						'name' => self::$form . '[' . $name . ']',
						'class' => $options['class']
					)) . '>' . htmlspecialchars($value) . '</textarea>';
					
					break;
				default:
					if($options['label'] !== false) { 
						$html .= '<label for="' . htmlspecialchars($options['id']) . '">' . htmlspecialchars($options['label']) . '</label>';
					}
					
					$html .= '<input ' . self::attrs(array(
						'type' => $options['type'],
						'id' => $options['id'],
						'name' => self::$form . '[' . $name . ']',
						'class' => $options['class'],
						'value' => $value
					)) . ' />';
					
					if($error !== false) {
						$html .= '<div class="message error">' . htmlspecialchars($error) . '</div>';
					}
					break;
			}
			
			if($options['div']) {
				$html .= '</div>';
			}
			
			return $html;
		}
		
		public static function hidden($name, $value) {
			if(self::$form === null) {
				return '';
			}
			
			$html = '';
			
			$html .= '<input type="hidden" name="' . htmlspecialchars($name) . '" value="' . htmlspecialchars($value) . '" />';
			
			return $html;
		}
		
		public static function submit($label, $options = array()) {
			if(self::$form === null) {
				return '';
			}
			
			$defaultOptions = array(
				'escape' => true
			);
			
			$options = array_merge($defaultOptions, $options);
			
			return '<div class="submit">
					<button type="submit">' . ($options['escape'] ? htmlspecialchars($label) : $label) . '</button>
				</div>';
		}
		
		public static function sent($name) {
			return isset($_POST['_form']) && $_POST['_form'] == $name;
		}
		
		private static function attrs($attrs) {
			return Html::attributes($attrs);
		}
	}
	
?>