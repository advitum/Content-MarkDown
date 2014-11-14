<?php
	
	namespace Advitum\Cmd\Plugins;
	use \Advitum\Cmd\Html;
	
	class Map extends \Advitum\Cmd\Plugin
	{
		public function frontend() {
			if(!$this->params['markers']) {
				return;
			}
			
			\Advitum\Cmd\Layout::addScript('jquery', $this->url() . 'js/jquery-1.11.1.min.js');
			\Advitum\Cmd\Layout::addScript('googlemaps', 'http://maps.google.com/maps/api/js?sensor=false');
			\Advitum\Cmd\Layout::addScript('map', $this->url() . 'js/map.js', array('googlemaps', 'jquery'));
			
			$attributes = array(
				'style' => 'width: 100%; height: 400px;',
				'class' => 'map',
				'data-markers' => json_encode($this->params['markers'])
			);
			
			if(isset($this->params['options'])) {
				$attributes['data-options'] = json_encode($this->params['options']);
			}
			
			return '<div ' . Html::attributes($attributes) . '></div>';
		}
	}
	
?>