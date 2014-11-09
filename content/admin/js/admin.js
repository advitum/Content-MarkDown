$(document).ready(function() {
	$(window).resize(function() {
		resize();
	});
	
	$('.mdEdit').tabby({
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
			
			if(!hidden) {
				items.push({
					title: 'View',
					callback: function(e) {
						var path = $page.children('a').attr('href');
						path = path.replace(/^\/admin\/pages/, '').replace(/\/[0-9_]+/g, '/').replace(/\.md$/, '').replace(/\/(index)?$/, '');
						
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
			$element.text('_unnamed.md');
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
				document.location.href = path + '?newpage=_unnamed.md&_nonce=' + encodeURI(nonces.newPage);
			}
		});
	});
	
	resize();
});

function resize() {
	$('#content').css({
		minHeight: $(window).height() - $('#content').offset().top + 'px'
	});
	$('.mdEdit').each(function() {
		var padding = $('#content').css('paddingBottom');
		padding = padding.substr(0, padding.length - 2) * 1;
		if(isNaN(padding)) {
			padding = 0;
		}
		
		$(this).css({
			height: $(window).height() - $(this).offset().top - padding + 'px'
		});
	});
}