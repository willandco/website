/* An attempt at jQuery in order to */

// do stuff when DOM is ready
$(document).ready(function() {
	
	/* check if something exists */	
	
	//$('textarea').load('get_sig.php');
	
	/*
	jquery-textrange object returned: 
	{ 
		position
		start
		end
		length
		text
	} 
	*/
	
	
	/*
	Title : Submit and preview functions
	
	Description : Used to preview and submit text
	
	TODO :
	
	notes :  action='newtopic.php' method='post'
	*/
	
	function NewPost( event ){
		
	/*
	Check if the title exists or not to determine whether to use
	new message or new topic.
	*/	
	// New message 
	var title = ($('input').exists()) ? $('input').val() : 'valid';
	var msg = $('textarea').val();
	/* Check and make sure the title is okay first */
	if(CheckMessage(msg, title)){
		if(event.target.id == 'sub'){	
		
			/* Check to see enough time has passed first */
			$('#resp_area').css('display', 'none');
			$('#resp_area').load('../php/post_timer.php' ,function(){
				/* Case 1 : must wait, bind timing event */
				if(parseInt($('#resp_area').text()) != 0) {
					$('#resp_area').css('display', 'block');
					clearInterval(wait_inter);							
					wait_inter = setInterval(Wait, 1000);
					$('button.submit').off();
				
				} else {
					
				/* Case 2 : no need to wait */
					$('form').attr({
						method:'post',
						action:($('input').exists()) ? 'newtopic_create.php':'message_new.php'
					});
					$('form').submit();
				}
			});
			
		} else if(event.target.id == 'preview') {
			alert('this does nothing currently');
		}
	}
	}
	
	$('button.submit').bind('click',function( event ){ NewPost( event );});

	var wait_inter;
	var Wait = function(){
		var cur_text = $('#resp_area').text();
		var dec = parseInt(cur_text,10);
		if(dec != 0){
			var len = dec.toString().length;
			dec--;
			var str = dec.toString() + cur_text.substring(len);
			
			$('#resp_area').text(str);
		} else {
			clearInterval(wait_inter);
			$('button.submit').on('click', function( event ){ NewPost( event ); });
		}
	}
	
	function CheckMessage(msg, title){
	
		/* check the length of the title to ensure it's under 80 characters and over 5 */
		if(title.length < 5){
			$('#resp_area').text('Topic title must be at least 5 characters');
			return false;
		} else if(title.length > 80) {
			$('#resp_area').text('Topic title must be 80 characters or less<br>');
			return false;
		} else if(!CheckMsg(msg)){
			return false;
		}
		$('#resp_area').text('');
		return true;
	}
	
	/* inherited from old code */
	function CheckMsg(msg){
		if(msg.length < 5){
			$('#resp_area').text('Message body must be at least 5 characters');
			return false;
		} 
		return true;
	}
	
	/* 
	Title : Text replacement
	
	Description : Used for all message boxes to bold, italic, underline, preformat. 
	
	TODO : img and spoiler tags 
	*/
	
	/* When a button is pressed, get it */
	$('button:not(.submit)').bind('click', function( event ){
		cursor_info = $('#msg').textrange('get');
		/* store to reduce repeated call */
		tag = event.target;
		
		/* Special Case : image tags */
		if(tag.id == 'img'){
		
			output = '<img src="">';
			/* Output information */
			$('textarea').textrange('insert',output);
			$('textarea').textrange('setcursor', cursor_info.position + output.length);
			cursor_info = $('textarea').textrange('get');
			
		/* Otherwise proceed as normal */
		} else {
			
			if(cursor_info['start'] == cursor_info['end']){
			
			output = (tag.innerHTML.substr(tag.innerHTML.length-1,1) != '*') ? '<' + tag.id + '>' : '</' + tag.id + '>';
			tag.innerHTML = (tag.innerHTML.substr(tag.innerHTML.length-1,1) != '*') ? tag.innerHTML + '*' : tag.innerHTML.substring(0,tag.innerHTML.length-1);
			
			/* Output information */
			$('textarea').textrange('insert',output);
			$('textarea').textrange('setcursor', cursor_info.position + output.length);
			cursor_info = $('textarea').textrange('get');
			
			} else {
				/* CASE TWO : Something selected */
				output = '<' + tag.id + '>' + cursor_info.text + '</' + tag.id + '>';
				$('textarea').textrange('replace',output);
				cursor_info = $('textarea').textrange('get');
			}
		}
		
	});
 });