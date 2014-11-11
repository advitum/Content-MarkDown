<?php
	
	namespace Advitum\Cmd;
	
	class Html
	{
		public static function arrayTable($rows, $columns, $tableOptions = array()) {
			$defaultOptions = array(
				'title' => false,
				'format' => 'text',
				'class' => false,
				'callback' => false
			);
			
			$defaultTableOptions = array(
				'id' => false,
				'class' => 'stdTable'
			);
			
			$tableOptions = array_merge($defaultTableOptions, $tableOptions);
			
			$columns = array_map(function($options) use ($defaultOptions) {
				if(!is_array($options)) {
					$options = array('title' => $options);
				}
				
				return array_merge($defaultOptions, $options);
			}, $columns);
			
			$heading = '<tr>';
			foreach($columns as $column => $options) {
				$heading .= '<th>' . htmlspecialchars($options['title'] !== false ? $options['title'] : ucfirst($column)) . '</th>';
			}
			$heading .= '</tr>';
			
			$tableAttributes = array();
			if($tableOptions['class'] !== false) {
				$tableAttributes['class'] = $tableOptions['class'];
			}
			if($tableOptions['id'] !== false) {
				$tableAttributes['id'] = $tableOptions['id'];
			}
			
			$html = '<table' . self::attributes($tableAttributes) . '><thead>' . $heading . '</thead><tbody>';
			
			foreach($rows as $row) {
				$html .= '<tr>';
				foreach($columns as $column => $options) {
					$value = '';
					
					if($options['callback'] !== false && is_callable($options['callback'])) {
						$value = call_user_func($options['callback'], $row);
					} elseif(isset($row->{$column})) {
						$value = htmlspecialchars($row->{$column});
						
						if($options['format'] == 'datetime') {
							$value = Html::dateTimeFormat($value);
						}
					}
					
					$cellAttributes = array();
					if($options['class'] !== false) {
						$cellAttributes['class'] = $options['class'];
					}
					
					$html .= '<td' . self::attributes($cellAttributes) . '>' . $value . '</td>';
				}
				$html .= '</tr>';
			}
			
			$html .= '</tbody></table>';
			
			return $html;
		}
		
		public static function dateFormat($date) {
			return date('j. F Y', strtotime($date));
		}
		
		public static function dateTimeFormat($date) {
			return self::dateFormat($date) . ' - ' . date('H:i', strtotime($date)) . ' Uhr';
		}
		
		public static function attributes($attributes) {
			$htmlAttributes = array();
			
			foreach($attributes as $attribute => $value) {
				if($value === false || $value === null)
					continue;
				
				if(is_numeric($attribute)) {
					$attribute = $value;
				}
				
				$htmlAttributes[] = htmlspecialchars($attribute) . '="' . htmlspecialchars($value) . '"';
			}
			
			return ' ' . implode(' ', $htmlAttributes);
		}
	}
	
?>