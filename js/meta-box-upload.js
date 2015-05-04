jQuery.noConflict();
(function($) {
	$(function() {
		var formfield;
		$(document).on('click', '.rational-upload-button', function() {
			formfield = $(this).prev('.rational-upload-field');
			tb_show( '', 'media-upload.php?type=image&amp;TB_iframe=true' );
			return false;
		});
		window.send_to_editor = function(html) {
			imgurl = $('img',html).attr('src');
			formfield.val(imgurl);
			tb_remove();
		}
	});
})(jQuery);