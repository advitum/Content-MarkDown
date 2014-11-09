<?php
	
	namespace Advitum\Cmd;
	
	class Header {
		private $markdown = '';
		private $header = array();
		
		public static function fromFile($filename) {
			return new self(file_get_contents($filename));
		}
		
		public static function fromMarkdown($markdown) {
			return new self($markdown);
		}
		
		public function getMarkdown() {
			return $this->markdown;
		}
		
		public function getHeader($name) {
			if(isset($this->header[$name])) {
				return $this->header[$name];
			} else {
				return false;
			}
		}
		
		private function __construct($markdown) {
			$this->markdown = $markdown;
			
			$matches = array();
			if(preg_match('/^(.*?\/\*\*\s*\R(?P<header>(\s+\*\s+[^\R]+\s+\R)+)\s+\*\/\s*\R)?(?P<markdown>.+)$/s', $this->markdown, $matches)) {
				$this->markdown = $matches['markdown'];
				
				$header = explode("\n", $matches['header']);
				foreach($header as $line) {
					if($line != '') {
						$line = explode(': ', substr($line, 3), 2);
						if(count($line) == 2) {
							$this->header[$line[0]] = $line[1];
						}
					}
				}
			}
		}
	}
	
?>