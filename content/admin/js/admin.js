$(document).ready(function() {
	$(window).resize(function() {
		resize();
	});
	
	$('.mdEdit textarea').tabby({
		tabString: '    '
	});
	
	$('#tree li').on('contextmenu', function(e) {
		e.preventDefault();
		e.stopPropagation();
		
		(function($page) {
			var title = $page.children('a').text();
			var items = [{
				title: 'Edit',
				href: $page.children('a').attr('href'),
				icon: 'fa-pencil'
			}];
			
			var hidden = $page.hasClass('hidden');
			var folder = $page.hasClass('folder');
			
			var path = $page.children('a').attr('href').replace(/^\/admin\/pages/, '');
			if(!hidden && path.match(/\/[0-9]*_/) == null) {
				items.push({
					title: 'View',
					callback: function(e) {
						path = path.replace(/\/[0-9_]+/g, '/').replace(/(index)?\.markdown$/, '');
						
						if(path === '') {
							path = '/';
						}
						
						window.open(path);
					},
					icon: 'fa-binoculars'
				});
			}
			
			if(title != 'index.markdown') {
				items.push({
					title: 'Rename',
					callback: function(e) {
						headerBox('Rename ' + title, '<div class="input"><input type="text" id="renameNewName" value="' + title + '" /></div>', {
							'<i class="fa fa-check">': function() {
								var newName = $('#renameNewName').val();
								if(newName == '') {
									$('.box header').after('<div class="message error">Please enter a new name!</div>');
									return false;
								} else {
									window.location.href = $page.children('a').attr('href') + '?rename=' + encodeURI(newName) + '&_nonce=' + encodeURI(nonces.renamePage);
								}
								return true;
							},
							'<i class="fa fa-times">': function() { return true; }
						});
					},
					icon: 'fa-pencil-square-o'
				});
				
				if(hidden) {
					items.push({
						title: 'Show',
						callback: function(e) {
							window.location.href = $page.children('a').attr('href') + '?togglevisibility&_nonce=' + encodeURI(nonces.toggleVisibiltyPage);
						},
						icon: (folder ? 'fa-folder' : 'fa-file')
					});
				} else {
					items.push({
						title: 'Hide',
						callback: function(e) {
							window.location.href = $page.children('a').attr('href') + '?togglevisibility&_nonce=' + encodeURI(nonces.toggleVisibiltyPage);
						},
						icon: (folder ? 'fa-folder-o' : 'fa-file-o')
					});
				}
				
				items.push({
					title: 'Delete',
					callback: function(e) {
						confirmBox('Are you sure you want to delete the ' + ($page.hasClass('folder') ? 'folder' : 'file') + ' "' + title + '"?', function() {
							window.location.href = $page.children('a').attr('href') + '?delete&_nonce=' + encodeURI(nonces.deletePage);
						});
					},
					icon: 'fa-trash'
				});
			}
			
			contextMenu(e, items, title);
		})($(this));
	});
	
	$('#newFolder, #newPage').mousedown(function(e) {
		var $element = $('<div id="newDragElement"></div>');
		if($(this).attr('id') == 'newFolder') {
			$element.text('_unnamed').addClass('folder');
		} else {
			$element.text('_unnamed.markdown');
		}
		
		$element.css({
			left: e.pageX + 'px',
			top: e.pageY + 'px'
		});
		
		$('body').append($element);
		
		$('#tree li.folder').on('mouseover.drag', function(e) {
			e.stopPropagation();
			
			$('#tree li.highlight').removeClass('highlight');
			$(this).addClass('highlight');
		}).on('mouseout.drag', function() {
			$(this).removeClass('highlight');
		});
		
		$(document).on('mousemove.drag', function(e) {
			$('#newDragElement').css({
				left: e.pageX + 'px',
				top: e.pageY + 'px'
			});
		}).one('mouseup', function(e) {
			$(document).off('.drag');
			$('#tree li.folder').off('.drag');
			
			var isFolder = $('#newDragElement').hasClass('folder');
			$('#newDragElement').remove();
			
			var $folder = $('#tree li.highlight');
			var path = '/admin/pages';
			if($folder.size() > 0) {
				path = $folder.children('a').attr('href').replace(/\/index\.markdown$/, '');
			}
				
			$('#tree li.highlight').removeClass('highlight');
			
			if(isFolder) {
				document.location.href = path + '?newfolder=_unnamed&_nonce=' + encodeURI(nonces.newFolder);
			} else {
				document.location.href = path + '?newpage=_unnamed.markdown&_nonce=' + encodeURI(nonces.newPage);
			}
		});
	});
	
	$('a.confirm').click(function(e) {
		e.preventDefault();
		
		var message = $(this).data('confirm');
		if(typeof message == 'undefined') {
			message = 'Are you sure you want to do this?';
		}
		
		var href = $(this).attr('href');
		confirmBox(message, function() {
			window.location.href = href;
		});
	});
	
	resize();
});

function resize() {
	$('.mdEdit textarea').each(function() {
		var padding = $('body').css('paddingBottom');
		padding = padding.substr(0, padding.length - 2) * 1;
		if(isNaN(padding)) {
			padding = 0;
		}
		
		$(this).css({
			height: Math.max(500, $(window).height() - $(this).offset().top - padding) + 'px'
		});
	});
	
	$('#sidebar').each(function() {
		var sidebarHeight = 0;
		var $nav = $(this).find('nav');
		var $footer = $(this).find('footer');
		
		sidebarHeight += $nav.offset().top;
		sidebarHeight += $nav.height();
		
		var margin = $nav.css('marginBottom');
		margin = margin.substr(0, margin.length - 2) * 1;
		if(isNaN(margin)) {
			margin = 0;
		}
		sidebarHeight += margin;
		
		sidebarHeight += $footer.height();
		
		var padding = $(this).css('paddingTop');
		padding = padding.substr(0, padding.length - 2) * 1;
		if(isNaN(padding)) {
			padding = 0;
		}
		
		sidebarHeight += padding;
		
		if(sidebarHeight > $(window).height()) {
			$(this).css('paddingBottom', padding + 'px');
			$(this).css('overflowY', 'scroll');
			$footer.css('position', 'static');
		} else {
			$(this).css('paddingBottom', padding + $footer.height() + 'px');
			$(this).css('overflowY', 'visible');
			$footer.css('position', 'absolute');
		}
	});
}