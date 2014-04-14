/* To be used exclusively with message body */

$(document).ready(function() {
	
	$(document).bind('keyup', function(event){ToggleMessage( event )});
	$('a.like').bind('click', function( event ) {Like( event )});
	$('#display').bind('click', function(event) {ToggleMessage( event )});
	$('img.plus').css('display', ($(document).height() > $(window).height()) ? 'block' : 'none');
	
});

function Like( event ) {
	str = event.target.id;
	cur_like = (event.target.text == 'Like') ? true : false;
	if(!(event.target.text == 'Liking' || event.target.text == 'Unliking')){
		like = (event.target.text == 'Like') ? "1" : "-1";
		$("#"+str).text((cur_like) ? 'Liking..' : 'Unliking..');
		$.post('like.php', {like_val: like, msg_id: str}, function(){
			$("#"+str).text((cur_like) ? 'Unlike' : 'Like');
		});
	}
}

function ToggleMessage ( event ) {
	
	// Tilda (`) key event code
	toggle_key = 192;
	if ($(document).height() > $(window).height() || $('#fixed').css('display') == 'block') {
		if((!event.keyCode || (event.keyCode == toggle_key && event.target.tagName != 'TEXTAREA'))){
			
			if($('#absolute').css('display') == 'none'){
				// move stuff over to fixed div
				$('#absolute').empty();
				$('#absolute').css('display', 'block');
				$('#fixed').css('display', 'none');
				var temp_text = $('textarea').val();
				$('#fixed > *').clone(true).prependTo('#absolute');
				$('textarea').val(temp_text);
				$('#fixed').empty();
			} else if ($('#absolute').css('display') == 'block') {
				$('#fixed').empty();
				$('#absolute').css('display', 'none');
				$('#fixed').css('display', 'block');
				var temp_text = $('textarea').val();
				$('#absolute > *').clone(true).prependTo('#fixed');
				$('textarea').val(temp_text);
				$('#absolute').empty();
				$('textarea').focus();		
			}
		}
	}
}