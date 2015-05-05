jQuery.noConflict();
(function($) {
	$(function() {
		var charCountInput = $('textarea[data-char-count], input[data-char-count]');
		charCountInput.each(function() {
		    var charLength = $(this).val().length,
				charLimit = $(this).data('char-count'),
				charRemaining = charLimit - charLength;
			$(this).after('<div class="rational-counter-container"><input class="small-text rational-char-counter" value="' + charRemaining + '" readonly> characters remaining.</div>');
			var charCounter = $(this).next('.rational-counter-container').children('.rational-char-counter');
			$(this).keyup(function() {
				charLength = $(this).val().length;
				charRemaining = charLimit - charLength;
		
				charCounter.val(charRemaining);
			});
		});
	});
})(jQuery);
