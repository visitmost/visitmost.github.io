(function( $ ) {
	'use strict';

	$(function() {

		$('.alti-watermark input[type="checkbox"][name="size"]').on('click blur active focus', function() {
			$('input[type="number"][name="size"]').attr('value', '');
		});

		$('.alti-watermark input[type="number"][name="size"]').on('click blur active focus', function() {
			$('input[type="checkbox"][name="size"]').attr('checked', false);
		});

	});

})( jQuery );