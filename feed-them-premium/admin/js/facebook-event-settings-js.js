var fb_event_title_option = ' title=' + jQuery("select#fb_event_title_option").val();
var fb_event_description_option = ' description=' + jQuery("select#fb_event_description_option").val();
var facebook_height = jQuery("input#facebook_event_height").val();

var fb_event_post_count_final_check = jQuery("input#fb_event_post_count").val();
	if (fb_event_post_count_final_check !== '')	{
		 var fb_event_post_count_final = ' posts=' + jQuery("input#fb_event_post_count").val();
	}
	else	{
		var fb_event_post_count_final = ' posts=5';
}

var fb_event_word_count_option_check = jQuery("input#fb_event_word_count_option").val();
	if (fb_event_word_count_option_check !== '')	{
		var fb_event_word_count_option = ' words=' + jQuery("input#fb_event_word_count_option").val();
	}
	else	{
		var fb_event_word_count_option = ' words=45';
}

if (facebook_height)	{
		var facebook_height_final = ' height=' + jQuery("input#facebook_event_height").val();
	}
	else {
		var facebook_height_final = '';
	}
//Rotate Script
//if (final_fts_rotate_shortcode && jQuery("#"+ rotate_form_id + " input.fts_rotate_feed").is(':checked')){
//	var final_fb_event_shorcode = '[fts facebook event' + fb_event_id  + fb_event_post_count_final + fb_event_title_option + fb_event_description_option + fb_event_word_count_option + ' type=event' + final_fts_rotate_shortcode + ']';			
//}
//else	{
	var final_fb_event_shorcode = '[fts facebook event' + fb_event_id + fb_event_post_count_final + fb_event_title_option + fb_event_description_option + facebook_height_final + fb_event_word_count_option + ' type=event]'
//}	