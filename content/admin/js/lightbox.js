function lightboxOpen(content) {
	lightboxClose();
	
	var $lightbox = $('<div class="lightbox"><div class="lightboxOuter"><div class="lightboxInner"><div class="lightboxContent"></div></div></div></div>');
	$lightbox.find('.lightboxContent').append(content);
	
	$('body').append($lightbox);
}

function lightboxClose() {
	$('.lightbox').remove();
}