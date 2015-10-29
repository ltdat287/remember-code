var fb_group_title_option = ' title=' + jQuery("select#fb_group_title_option").val();
var fb_group_description_option = ' description=' + jQuery("select#fb_group_description_option").val();
var facebook_height = jQuery("input#facebook_group_height").val();

var fb_group_post_count_final_check = jQuery("input#fb_group_post_count").val();
	if (fb_group_post_count_final_check !== '')	{
		 var fb_group_post_count_final = ' posts=' + jQuery("input#fb_group_post_count").val();
	}
	else	{
		var fb_group_post_count_final = ' posts=5';
}

var fb_group_word_count_option_check = jQuery("input#fb_group_word_count_option").val();
	if (fb_group_word_count_option_check !== '')	{
		var fb_group_word_count_option = ' words=' + jQuery("input#fb_group_word_count_option").val();
	}
	else	{
		var fb_group_word_count_option = ' words=45';
}

if (facebook_height)	{
		var facebook_height_final = ' height=' + jQuery("input#facebook_group_height").val();
	}
	else {
		var facebook_height_final = '';
	}

//Rotate Script
//if (final_fts_rotate_shortcode && jQuery("#"+ rotate_form_id + " input.fts_rotate_feed").is(':checked') && fts_rotate_active == true){
//	var final_fb_group_shorcode = '[fts facebook group' + fb_group_id  + fb_group_post_count_final + fb_group_title_option + fb_group_description_option + fb_group_word_count_option + ' type=group' + final_fts_rotate_shortcode + ']';			
//}
//else	{
	var final_fb_group_shorcode = '[fts facebook group' + fb_group_id + fb_group_post_count_final + fb_group_title_option + fb_group_description_option + facebook_height_final + fb_group_word_count_option + ' type=group]'
//}	