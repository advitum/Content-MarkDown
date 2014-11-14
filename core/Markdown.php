<?php
	
	namespace Advitum\Cmd;
	
	require_once(VENDOR_PATH . '/markdown/Markdown.php');
	
	class Markdown extends PluginContainer {
		private $header = null;
		private $html = '';
		
		public function __construct($markdown) {
			$this->header = Header::fromMarkdown($markdown);
			
			$markdown = $this->header->getMarkdown();
			
			$this->html = $this->replacePlugins(\Michelf\Markdown::defaultTransform($markdown));
		}
		
		public function getHtml() {
			return $this->html;
		}
		
		public function getHeader($name) {
			return $this->header->getHeader($name);
		}
	}
	
?>