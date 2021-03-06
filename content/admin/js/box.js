function box(content, buttons) {
	var $dialogue = $('<div class="box"></div>').html(content + '<div class="buttonbar"></div>');
	
	if(typeof(buttons) != "undefined") {
		for(label in buttons) {
			var callback = buttons[label];
			
			if(typeof(callback) != "function") {
				callback = function() { return true; };
			}
			
			$dialogue.find('.buttonbar').append('<button type="button">' + label + '</button>');
			
			(function(callback) {
				$dialogue.find('.buttonbar button:last-child').click(function() {
					var result = callback();
					
					if(typeof(result) == 'undefined' || result !== false) {
						$.lightbox.close();
					}
				});
			})(callback);
		}
	} else {
		$dialogue.find('.buttonbar').append('<button type="button">OK</button>');
		$dialogue.find('.buttonbar button').click(function() {
			$.lightbox.close();
		});
	}
	
	$.lightbox.open($dialogue, {
		type: 'html',
		modular: true
	});
	
	$('.box').find('input, textarea').eq(0).focus();
}

function headerBox(header, content, buttons) {
	box('<header>' + header + '</header>' + content, buttons);
}


function confirmBox(question, yesCallback, noCallback) {
	headerBox('Are you sure?', question, {
		'<i class="fa fa-check"></i>': yesCallback,
		'<i class="fa fa-times"></i>': noCallback
	});
}