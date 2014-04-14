function fun(event)
{
	tilda = 192;
	if(event.keyCode == tilda){
		// Create login box if 'l' is pressed
		document.getElementById('unused').style.display="block";
		if(!document.getElementById('header_div')){
			// If the div hasn't been created yet, create it.
			Create_Element("header_div","div","unused");
			Create_Element("form_div","div","unused");
			Create_Element("login_form","form","form_div");
			
			Print("Login", "pop_txt", "header_div");
			
			form = document.getElementById('login_form');
			form.action = 'login_main.php';
			form.method = 'post';
			
			Create_Element("usrn_input","INPUT",'login_form');
			usr_input = document.getElementById('usrn_input');
			usr_input.setAttribute("type","text");
			usr_input.name = "username";
			usr_input.placeholder = "Username";
			usr_input.onkeyup = function(event){	
				Enter = 13;
				if(event.keyCode == Enter){
					document.getElementById('login_form').submit();
				}
			};
			usr_input.select();
			
			Create_Element("pwd_input","INPUT",'login_form');
			pwd_input = document.getElementById('pwd_input');
			pwd_input.setAttribute("type","password");
			pwd_input.name = "password";
			pwd_input.placeholder = "Password";
			pwd_input.onkeyup = function(event){
				Enter = 13;
				if(event.keyCode == Enter){
					document.getElementById('login_form').submit();
				}
			};
		} else {
			document.getElementById('usrn_input').select();
		}
	} 
	
	esc = 27;
	if(event.keyCode == esc){
		// Hide login box if 'esc' is pressed
		document.getElementById('unused').style.display="none";
	}
};

// Some generic JS functions I wrote an eon ago
function Print(text, new_id, div){
	var new_p=document.createElement("a");
	var node=document.createTextNode(text);
	new_p.appendChild(node);
	/* could also return a generated id for later use */
	new_p.id = new_id;
	var element=document.getElementById(div);
	element.appendChild(new_p);
};
function Update(old_id, text){
	if(p_up = document.getElementById(old_id)) {
		p_up.innerHTML = text;
		return true;
	} else { return false;}
};
function Remove(old_id){
	var del_p = document.getElementById(old_id);
	del_p.parentNode.removeChild(del_p);
};
function Create_Element(new_id, c_ele, p_id){
	var new_ele=document.createElement(c_ele);
	new_ele.id = new_id;
	
	var element=document.getElementById(p_id);
	element.appendChild(new_ele);
};