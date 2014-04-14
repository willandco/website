
$(document).ready(function() {

	$('a#like').bind('click', function( event ) {
		// Increment like SQL by 1
		event.target.text('liking...');
	});

});