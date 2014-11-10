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
			$bom = pack('H*','EFBBBF');
			$this->markdown = preg_replace("/^" . $bom . "/", '', $markdown);
			
			$matches = array();
			if(preg_match('/^(.*?\/\*\*\s+(?P<header>(\*.*?\s+)+?)\*\/\s+)?(?P<markdown>.+)$/us', $this->markdown, $matches)) {
				$this->markdown = $matches['markdown'];
				
				$header = explode("\n", $matches['header']);
				foreach($header as $line) {
					if($line != '') {
						$line = preg_replace('/^\s*\*\s*/', '', $line);
						$line = explode(': ', trim($line), 2);
						if(count($line) == 2) {
							$this->header[$line[0]] = $line[1];
						}
					}
				}
			}
		}
	}
	
?>