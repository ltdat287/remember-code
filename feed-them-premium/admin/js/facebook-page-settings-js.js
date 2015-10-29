var fb_page_title_option = ' title=' + jQuery("select#fb_page_title_option").val();
var fb_page_description_option = ' description=' + jQuery("select#fb_page_description_option").val();
var facebook_height = jQuery("input#facebook_page_height").val();
var load_more_option = jQuery("select#fb_load_more_option").val();
var load_more_posts_style = jQuery("select#fb_load_more_style").val();


var facebook_grid = jQuery("select#fb-grid-option").val();
var facebook_grid_colmn_width = jQuery("input#facebook_grid_colmn_width").val();
var facebook_grid_space_between_posts = jQuery("input#facebook_grid_space_between_posts").val();

var facebook_popup = jQuery("select#facebook_popup").val();
	
	
	var fb_page_post_count_final_check = jQuery("input#fb_page_post_count").val();
		if (fb_page_post_count_final_check !== '')	{
			 var fb_page_post_count_final = ' posts=' + jQuery("input#fb_page_post_count").val();
		}
		else	{
			var fb_page_post_count_final = ' posts=5';
	}

	var fb_page_word_count_option_check = jQuery("input#fb_page_word_count_option").val();
		if (fb_page_word_count_option_check !== '')	{
			var fb_page_word_count_option = ' words=' + jQuery("input#fb_page_word_count_option").val();
		}
		else	{
			var fb_page_word_count_option = ' words=45';
	}



	if (facebook_height)	{
		var facebook_height_final = ' height=' + jQuery("input#facebook_page_height").val();
	}
	else {
		var facebook_height_final = '';
	}
	
	
	
	if (facebook_grid == 'yes')	{
		var facebook_grid = ' grid=' + jQuery("select#fb-grid-option").val();
	}
	else {
		var facebook_grid = '';
	}
	
	if (facebook_grid_colmn_width && facebook_grid)	{
		var facebook_grid_colmn_width = ' colmn_width=' + jQuery("input#facebook_grid_colmn_width").val();
	}
	else {
		var facebook_grid_colmn_width = '';
	}
	
	if (facebook_grid_space_between_posts && facebook_grid)	{
		var facebook_grid_space_between_posts = ' space_between_posts=' + jQuery("input#facebook_grid_space_between_posts").val();
	}
	else {
		var facebook_grid_space_between_posts = '';
	}
	if (facebook_popup == 'yes')	{
		var facebook_popup = ' popup=' + jQuery("select#facebook_popup").val();
	}
	else {
		var facebook_popup = '';
	}
	
	
	
	
	
	
	if (load_more_option == 'no')	{
		var load_more_posts_style_final = '';
	}
	else {
		var load_more_posts_style_final = ' loadmore=' + jQuery("select#fb_load_more_style").val();
	}


if (albums_photos_option == "album_photos") {
	  				var final_fb_page_shortcode = '[fts facebook' + fb_page_id + fb_album_id + fb_page_post_count_final + fb_page_title_option + fb_page_description_option + fb_page_word_count_option + fb_feed_type + image_width + image_height + space_between_photos + hide_date_likes_comments + center_container + image_stack_animation + position_lr + position_top + load_more_posts_style_final +' ]';
				}
				else if (albums_photos_option == "albums") {
	  				var final_fb_page_shortcode = '[fts facebook' + fb_page_id  + fb_page_post_count_final + fb_page_title_option + fb_page_description_option + fb_page_word_count_option + fb_feed_type + image_width + image_height + space_between_photos + hide_date_likes_comments + center_container + image_stack_animation + position_lr + position_top + load_more_posts_style_final + ']';
				}
				else if (albums_photos_option == "page") {
					var final_fb_page_shortcode = '[fts facebook' + fb_page_id + fb_page_posts_displayed + fb_page_post_count_final + fb_page_title_option + fb_page_description_option + fb_page_word_count_option + facebook_height_final + fb_feed_type + load_more_posts_style_final + facebook_grid + facebook_grid_space_between_posts + facebook_grid_colmn_width + facebook_popup + ']';
				}
				else {
					var final_fb_page_shortcode = '[fts facebook' + fb_page_id + fb_page_post_count_final + fb_page_title_option + fb_page_description_option + fb_page_word_count_option + facebook_height_final + fb_feed_type  + load_more_posts_style_final + facebook_grid + facebook_grid_space_between_posts + facebook_grid_colmn_width + facebook_popup +  ']';
				} 	 	