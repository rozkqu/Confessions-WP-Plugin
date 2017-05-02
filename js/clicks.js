jQuery(document).ready(function($){
	$(function(){
		//register a handler
		$( ".vote-button" ).on( "click", function() {
			
			//alert('click');
			
			if(!(this).hasAttribute("clicked")) {
			
				//inc button value
				var span = (this).childNodes;
				
				//alert((this).childNodes[1].innerHTML);
				var button_value = span[1].innerHTML;
				span[1].innerHTML = parseInt(button_value) + 1;
				
				(this).setAttribute("clicked", "true");
			}
			
			var data = {
				action: 'update_button_clicks',
				post_id: $(this).attr('post_id'),
				button_type: $(this).attr('button_type')
			}
			
			
			$.post(ajax_options.admin_ajax_url, data, function(response){
				//kod javascript który wykona się po ptrzymaniu odpoweidzi od serwera
				//$(this).innerHTML = response;
				//alert(response); //wyświetlamy odpowiedź
				//location.reload(); 
			});
		});
	});
	
});