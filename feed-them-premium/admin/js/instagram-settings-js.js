var pics_count = jQuery("input#pics_count").val();

	if (pics_count !== '')	{
		 var pics_count_final = ' pics_count=' + jQuery("input#pics_count").val();
	}
	else	{
		var pics_count_final = ' pics_count=5';
}

if (jQuery("select#instagram-custom-gallery").val() == "no") {
	  			var final_instagram_shorcode = '[fts instagram' + instagram_id + pics_count_final + instagram_feed_type + instagram_popup_option +']';
			}
else {
	var final_instagram_shorcode = '[fts instagram' + instagram_id + pics_count_final + super_gallery + image_size + icon_size + space_between_photos + hide_date_likes_comments + center_container + image_stack_animation + instagram_feed_type + instagram_popup_option +']';
} 