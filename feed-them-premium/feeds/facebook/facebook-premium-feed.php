<?php 
	extract( shortcode_atts( array(
		'id' => '',
		'posts' => '5',
		'posts_displayed' => '',
		'title' => '',
		'description' => '',
		'words' => '45',
		'height' => '',
		'album_id' => '',
		'type' => '',
		'image_width' => '',
		'image_height' => '',
		'space_between_photos' => '',
		'hide_date_likes_comments' => '',
		'center_container' => '',
		'image_stack_animation' => '',
		'image_position_lr' => '',
		'image_position_top' => '',
		'loadmore' => '',
		'grid' => '',
		'colmn_width' => '',
		'space_between_posts' => '',
		'popup' => '',
		
		
	), $atts ) );
	
	$custom_name = $posts_displayed;
	$fts_limiter = $posts;
	$fts_fb_id = $id;
	$fts_fb_type = $type;
	$scrollMore = $loadmore;
	$fts_grid = $grid;
	$fts_colmn_width = $colmn_width;
	$fts_fb_popup = $popup;
?>