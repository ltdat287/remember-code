<?php 
	//This File ONLY includes js for feeds strictly Premium (not in free version also. Those must be sperate)
?>

<script>
function updateTextArea_pinterest() {

	var pinterest_name = ' pinterest_name=' + jQuery("input#pinterest_name").val(); 
	
	if (pinterest_name == " pinterest_name=") {
	  	 jQuery(".pinterest_name").addClass('fts-empty-error');  
      	 jQuery("input#pinterest_name").focus();
		 return false;
		 
	}
	if (pinterest_name != " pinterest_name=") {
	  	 jQuery(".pinterest_name").removeClass('fts-empty-error');  
	}
	
	var boards_count = ' boards_count=' + jQuery("input#boards_count").val();

	if (boards_count == " boards_count=") {
	  var boards_count_final=' boards_count=6';
	}
	if (boards_count != " boards_count="){
	 var boards_count_final = boards_count;
	}

<?php 
	//Rotate Plugin
	if(is_plugin_active('fts-rotate/fts-rotate.php')) {?>
		
		var rotate_form_id = "fts-pinterest-form";
		
		<?php include('../wp-content/plugins/fts-rotate/admin/js/fts-rotate-settings-options.js');
	}
?>

//if (final_fts_rotate_shortcode){
//	var final_pinterest_shorcode = '[fts pinterest' + pinterest_name  + boards_count_final + final_fts_rotate_shortcode + ']';			
//}
//else	{
	var final_pinterest_shorcode = '[fts pinterest' + pinterest_name  + boards_count_final + ']';
//}	

jQuery('.pinterest-final-shortcode').val(final_pinterest_shorcode);
	
	jQuery('.pinterest-shortcode-form .final-shortcode-textarea').slideDown();
}
//End Pinterest


//START youtube//
jQuery('.youtube_first_video').hide();

jQuery('select#youtube_columns').change(function(){
	var youtube_columns_count = jQuery(this).val();
	
	if (youtube_columns_count == '1') {
		jQuery('.youtube_first_video').hide();
	}
	else	{
		jQuery('.youtube_first_video').show();
	}
});

function updateTextArea_youtube() {
	
	var youtube_name = ' username=' + jQuery("input#youtube_name").val();
	var youtube_vid_count = ' vid_count=' + jQuery("input#youtube_vid_count").val();
	var youtube_columns = ' vids_in_row=' + jQuery("select#youtube_columns").val();
	
	//check # of vids in row//
	var youtube_columns_count = jQuery('select#youtube_columns').val();
	
	if (youtube_columns_count == '1') {
		var youtube_first_video = '';
	}
	else	{
		var youtube_first_video = ' large_vid=' + jQuery("select#youtube_first_video").val();
	}
	
	if (youtube_name == " username=") {
	  	 jQuery(".youtube_name").addClass('fts-empty-error');  
      	 jQuery("input#youtube_name").focus();
		 return false;
	}
	if (youtube_name != " youtube_name=") {
	  	 jQuery(".youtube_name").removeClass('fts-empty-error');  
	}

<?php 
	//Rotate Plugin
	if(is_plugin_active('fts-rotate/fts-rotate.php')) {?>
		
		var rotate_form_id = "fts-youtube-form";
		
		<?php include('../wp-content/plugins/fts-rotate/admin/js/fts-rotate-settings-options.js');
	}
?>
	
//if (final_fts_rotate_shortcode && jQuery("#"+ rotate_form_id + " input.fts_rotate_feed").is(':checked')){
//	var final_youtube_shorcode = '[fts youtube' + youtube_name + youtube_vid_count + youtube_columns + youtube_first_video + final_fts_rotate_shortcode + ']';			
//}
//else	{
	var final_youtube_shorcode = '[fts youtube' + youtube_name + youtube_vid_count + youtube_columns + youtube_first_video + ']';
//}	
		


jQuery('.youtube-final-shortcode').val(final_youtube_shorcode);
	
	jQuery('.youtube-shortcode-form .final-shortcode-textarea').slideDown();
}
//END youtube//
</script>