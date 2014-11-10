<?php
	
	namespace Advitum\Cmd\Plugins;
	
	class Gallery extends \Advitum\Cmd\Plugin
	{
		public function frontend() {
			$html = '';
			
			$images = array();
			
			$params = array_merge(array(
				'folder' => false
			), $this->params);
			
			if($params['folder']) {
				$folder = str_replace('/', DS, $params['folder']);
				if(substr($folder, -1) != DS) {
					$folder .= DS;
				}
				
				if(is_dir(CONTENT_PATH . $folder)) {
					foreach(scandir(CONTENT_PATH . $folder) as $file) {
						if(!is_file(CONTENT_PATH . $folder . $file) || substr($file, 0, 1) == '.') {
							continue;
						}
						
						$images[] = array(
							'path' => CONTENT_PATH . $folder . $file,
							'url' => CONTENT_URL . str_replace(DS, '/', $folder) . $file
						);
					}
				}
			}
			
			if(count($images)) {
				\Advitum\Cmd\Layout::addScript('jquery', $this->url() . 'js/jquery-1.11.1.min.js');
				\Advitum\Cmd\Layout::addScript('lightbox', $this->url() . 'js/jquery.lightbox.js', array('jquery'));
				\Advitum\Cmd\Layout::addScript('gallery', $this->url() . 'js/gallery.js', array('jquery', 'lightbox'));
				\Advitum\Cmd\Layout::addStylesheet('gallery', $this->url() . 'css/gallery.css');
				
				$html .= '<div class="gallery">';
				
				$hash = htmlspecialchars(md5(mt_rand() . 'lightbox'));
				
				foreach($images as $image) {
					$html .= '<a href="' . htmlspecialchars($image['url']) . '" rel="lightbox-' . $hash . '"><img src="/content/autoimg/index.php?p=w200-h200-c' . htmlspecialchars($image['url']) . '" alt="" /></a>';
				}
				
				$html .= '</div>';
			}
			
			return $html;
		}
		
		/*public function backend() {
			return 'I am not doing anything yet.';
		}*/
	}
	
?>