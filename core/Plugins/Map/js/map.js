$('document').ready(function() {
	$('.map').each(function() {
		var map;
		var markers = $(this).data('markers');
		var center = [0, 0];
		
		$.each(markers, function(index, marker) {
			center[0] += marker.position[0];
			center[1] += marker.position[1];
		});
		
		center[0] /= markers.length;
		center[1] /= markers.length;
		
		var mapOptions = {
			center: new google.maps.LatLng(center[0], center[1]),
			mapTypeId: google.maps.MapTypeId.ROADMAP,
			zoom: 11
		};
		
		var options = $(this).data('options');
		if(typeof options != 'undefined') {
			$.extend(mapOptions, options);
		}
	 	
		map = new google.maps.Map($(this).get(0), mapOptions);
	 	
	 	var before = 500;
	 	var between = 200;
		$.each(markers, function(index, marker) {
			marker = {
				map: map,
				draggable: false,
				animation: google.maps.Animation.DROP,
				position: new google.maps.LatLng(marker.position[0], marker.position[1]),
				title: marker.title
			};
			
			setTimeout(function() {
				new google.maps.Marker(marker);
			}, before + index * between);
		});
	});
});