<?php
class Facebook_Feed {

	private $config = array(
			'access_token' => '1463338250663058|312d53cdb378522f3657ce8f60769e0c',
		);

	private $FB_array = array(
								'id' => 'muv0z',
								'type' => 'page',
								'posts_displayed' => '',
								'height' => '',
								'album_id' => '',
								'image_width' => '',
								'image_height' => '',
								'space_between_photos' => '',
								'hide_date_likes_comments' => '',
								'center_container' => '',
								'image_stack_animation' => '',
								'image_position_lr' => '',
								'image_position_top' => '',
								'posts' => '5',
							);

	//**************************************************
	// Create function getoption for php
	//**************************************************
	function get_option( $option, $default = '' )
	{
		$default_options = array(
			'fts-timezone' => 'Asia/Ho_Chi_Minh',
			'fb_show_follow_btn' => 'like-button-faces', //like-button-share-faces, like-button-faces, like-box-faces, like-button-share
			'fb_show_follow_like_box_cover' => 'fb_like_box_cover-yes', //fb_like_box_cover-yes
			'fb_language' => 'en_US',
			'fb_app_ID' => '1463338250663058',
			'fb_like_btn_color' => '',
			);
		if ( !empty($option) ) {

			return $default;
		}
		if ( in_array($option, $default_options) ) {

			return $default_options[$option];
		} else {

			return $default;
		}
	}
	//**************************************************
	// Display Facebook Feed
	//**************************************************
	function fts_fb_func() {
		//Get access token
		$access_token = $this->get_access_token();
		$FB_Shortcode = $this->FB_array;

		// Username?
		if (!$FB_Shortcode['id']) { return 'Please enter a username for this feed.'; }
		ob_start();
		//View Link
		$fts_view_fb_link = $this->get_view_link($FB_Shortcode);
		//Get Cache Name
		$fb_cache_name = $this->get_fb_cache_name($FB_Shortcode);
		//Get language
		$language = $this->get_language($FB_Shortcode);
		//Get Response (AKA Page & Feed Information) ERROR CHECK inside this function
		$response = $this->get_facebook_feed_response($FB_Shortcode, $fb_cache_name, $access_token, $language);
		//Json decode data and build it from cache or response
		$page_data = json_decode($response['page_data']);
		$feed_data = json_decode($response['feed_data']);

		//If No Response or Error then return
		if($response == false){return;}

			// if (is_plugin_active('feed-them-premium/feed-them-premium.php') && is_plugin_active('feed-them-social-facebook-reviews/feed-them-social-facebook-reviews.php')) {
			// //Make sure it's not ajaxing and we will allow the omition of certain album covers from the list by using omit_album_covers=0,1,2,3 in the shortcode
			// 	if (!isset($_GET['load_more_ajaxing'])) {
			// 		if ($FB_Shortcode['type'] == 'albums') {
			// 			// omit_album_covers=0,1,2,3 for example
			// 			$omit_album_covers = $FB_Shortcode['omit_album_covers'];
			// 			$omit_album_covers_new = array();
			// 			$omit_album_covers_new = explode(',', $omit_album_covers);
			// 			foreach ($feed_data->data as $post_data) {
			// 				foreach ($omit_album_covers_new as $omit) {
			// 					unset($feed_data->data[$omit]);
			// 				}
			// 			}
			// 		}
			// 	}
			// 	//Reviews Rating Filter
			// 	if ($FB_Shortcode['type'] == 'reviews') {
			// 		foreach ($feed_data->data as $key => $post_data) {
			// 			if($post_data->rating < $FB_Shortcode['reviews_type_to_show']){
			// 				unset($feed_data->data[$key]);
			// 			}
			// 		}
			// 	}
			//
				// echo'<pre>';
				// 	print_r($feed_data);
				// echo'</pre>';
			//If events array Flip it so it's in proper order
			if ($FB_Shortcode['type'] == 'events') {
				if($feed_data->data){
					usort($feed_data->data, function($a, $b) {
					    $a = strtotime($a->start_time);
					    $b = strtotime($b->start_time);
						    return (($a == $b) ? (0) : (($a > $b) ? (1) : (-1)));
					});
				//	 $feed_data->data = array_reverse($feed_data->data);
				}
			}

			$FTS_FB_OUTPUT = '';
			//Make sure it's not ajaxing
			if (!isset($_GET['load_more_ajaxing'])) {
				//Get Response (AKA Page & Feed Information)
				$_REQUEST['fts_dynamic_name'] = trim($this->rand_string(10).'_'.$FB_Shortcode['type']);
				//Create Dynamic Class Name
				$fts_dynamic_class_name = $this->get_fts_dynamic_class_name();
				//******************
				// SOCIAL BUTTON
				//******************
				$FTS_FB_OUTPUT .= $this->fb_social_btn_placement($FB_Shortcode, $access_token, 'fb-like-top-above-title');
				$page_data->description = isset($page_data->description) ? $page_data->description : "";
				$page_data->name = isset($page_data->name) ? $page_data->name : "";
				// fts-fb-header-wrapper (for grid)
				$FTS_FB_OUTPUT .= isset($FB_Shortcode['grid']) && $FB_Shortcode['grid'] !== 'yes' ? '<div class="fts-fb-header-wrapper">' : '';
					//Header
					$FTS_FB_OUTPUT .= '<div class="fts-jal-fb-header">';
						// $FTS_FB_OUTPUT .= our Facebook Page Title or About Text. Commented out the group description because in the future we will be adding the about description.
						$FTS_FB_OUTPUT .= isset($FB_Shortcode['title']) && $FB_Shortcode['title'] == 'yes' || isset($FB_Shortcode['title']) && $FB_Shortcode['title'] == '' ? '<h1><a href="'.$fts_view_fb_link.'" target="_blank">'.$page_data->name.'</a></h1>' : '';
						//Description
						$FTS_FB_OUTPUT .= isset($FB_Shortcode['description']) && $FB_Shortcode['description'] == 'yes' || isset($FB_Shortcode['description']) && $FB_Shortcode['description'] == '' ? '<div class="fts-jal-fb-group-header-desc">'.$this->fts_facebook_tag_filter($page_data->description).'</div>' : '';
					//END Header
					$FTS_FB_OUTPUT .= '</div>';
				// Close fts-fb-header-wrapper
				$FTS_FB_OUTPUT .= isset($FB_Shortcode['grid']) && $FB_Shortcode['grid'] !== 'yes' && $FB_Shortcode['type'] !== 'album_photos' && $FB_Shortcode['type'] !== 'albums' ? '</div>' : '';
			} //End check
			//******************
			// SOCIAL BUTTON
			//******************
			$FTS_FB_OUTPUT .= $this->fb_social_btn_placement($FB_Shortcode, $access_token, 'fb-like-top-below-title');
			//*********************
			// Feed Header
			//*********************
			//Make sure it's not ajaxing
				if (!isset($_GET['load_more_ajaxing'])) {
						if (!isset($FBtype) && $FB_Shortcode['type'] == 'albums' || !isset($FBtype) && $FB_Shortcode['type'] == 'album_photos' || isset($FB_Shortcode['grid']) && $FB_Shortcode['grid'] == 'yes') {
								if(isset($FB_Shortcode['video_album']) && $FB_Shortcode['video_album'] == 'yes' ){ } else {
								wp_enqueue_script( 'fts-masonry-pkgd', plugins_url( 'feed-them-social/feeds/js/masonry.pkgd.min.js'), array( 'jquery' ) ); 
				$FTS_FB_OUTPUT .='<script>';
						 $FTS_FB_OUTPUT .='jQuery(window).load(function(){';
							 $FTS_FB_OUTPUT .='jQuery(".'.$fts_dynamic_class_name.'").masonry({';
				             $FTS_FB_OUTPUT .='itemSelector: ".fts-jal-single-fb-post"';
				            $FTS_FB_OUTPUT .='});';
						 $FTS_FB_OUTPUT .='});';
				        $FTS_FB_OUTPUT .='</script>';
				       } 
			if (!isset($FBtype) && $FB_Shortcode['type'] == 'albums' || !isset($FBtype) && $FB_Shortcode['type'] == 'album_photos' ) {  
	$FTS_FB_OUTPUT .= '<div class="fts-slicker-facebook-photos fts-slicker-facebook-albums '.(isset($FB_Shortcode['video_album']) && $FB_Shortcode['video_album'] && $FB_Shortcode['video_album'] == 'yes' ? 'popup-video-gallery-fb' : ' popup-gallery-fb masonry js-masonry').' '.(isset($FB_Shortcode['images_align']) && $FB_Shortcode['images_align'] ? ' popup-video-gallery-align-'.$FB_Shortcode['images_align'] : '').' popup-gallery-fb '.$fts_dynamic_class_name.'" style="margin:auto;" data-masonry-options=\'{ "isFitWidth": '.($FB_Shortcode['center_container'] == 'no' ? 'false' : 'true') .' '.($FB_Shortcode['image_stack_animation'] == 'no' ? ', "transitionDuration": 0' : '').'}\'>';
			}
				if (isset($FB_Shortcode['grid']) && $FB_Shortcode['grid'] == 'yes') { 
	$FTS_FB_OUTPUT .='<div class="fts-slicker-facebook-posts masonry js-masonry '.($FB_Shortcode['popup'] == 'yes' ? 'popup-gallery-fb-posts ' : '').($FB_Shortcode['type'] == 'reviews' ? 'fts-reviews-feed ' : '').$fts_dynamic_class_name.' " style="margin:auto;" data-masonry-options=\'{ "isFitWidth": '.($FB_Shortcode['center_container'] == 'no' ? 'false' : 'true').' '.($FB_Shortcode['image_stack_animation'] == 'no' ? ', "transitionDuration": 0' : '').'}\'>';
	            }
				}
				else { 
					$FTS_FB_OUTPUT .= '<div class="fts-jal-fb-group-display fts-simple-fb-wrapper '.(isset($FB_Shortcode['popup']) && $FB_Shortcode['popup'] == 'yes' ? 'popup-gallery-fb-posts ' :'').($FB_Shortcode['type'] == 'reviews' ? 'fts-reviews-feed ' : '').$fts_dynamic_class_name.' '.($FB_Shortcode['height'] !== 'auto' && empty($FB_Shortcode['height']) == NULL ? 'fts-fb-scrollable" style="height:'.$FB_Shortcode['height'].'"' : '"').'>';
				}
			} //End ajaxing Check
	}

	//**************************************************
	// Get Access Token
	//**************************************************
	function get_access_token(){
		//API Access Token
		$custom_access_token = $this->config['access_token'];
		if (!empty($custom_access_token)) {
			$access_token = $this->config['access_token'];
			return $access_token;
		}
		else {
			//Randomizer
			$values = array(
				'817537814961507|HSQjMRcTKHfsqO4CSItHTrnyVBk',
				'1102592936422517|FG1AX3hBb0hzEKrYIFi6z71lYs8',
				'1581354922148318|Ta9P0qtrRTcI4s9_bmnMGbGAfv4',
				'1470900269866726|EYsX18Tk8iw84zr-Err483yUR4c',
				'346055802264380|wSn-ygXzJJkTuPsNuQMksUMRWuc',
				'646491715485385|vhBnr8q-42P49EiXnZnmr4F1AX4',
				'433290036843633|spBHAl5Mw9s2VouZRnlp3GTO2Gw',
				'706407866152249|nUx5ejy-JLxdSDmb0xB9p1ybolA',
				'1425804354387502|wzo-X1Q7V87SqhjhXV7GSB0qb8A',
				'1586514261631082|Uxz9iF9llMEiCWwBMyUO1GK8H_A',
			);
			$access_token = $values[array_rand($values, 1)];
			return $access_token;
		}
	}
	//**************************************************
	// Get View Link
	//**************************************************
	function get_view_link($FB_Shortcode) {
		switch ($FB_Shortcode['type']) {
			case 'group' :
				$fts_view_fb_link ='https://www.facebook.com/groups/'.$FB_Shortcode['id'].'/';
				break;
			case 'page':
				$fts_view_fb_link ='https://www.facebook.com/'.$FB_Shortcode['id'].'/';
				break;
			case 'event' :
				$fts_view_fb_link ='https://www.facebook.com/events/'.$FB_Shortcode['id'].'/';
				break;
			case 'events' :
				$fts_view_fb_link ='https://www.facebook.com/'.$FB_Shortcode['id'].'/events/';
				break;
			case 'albums':
				$fts_view_fb_link ='https://www.facebook.com/'.$FB_Shortcode['id'].'/photos_stream?tab=photos_albums';
				break;
				// album photos and videos album
			case 'album_photos':
				$fts_view_fb_link = isset($FB_Shortcode['video_album']) && $FB_Shortcode['video_album'] == 'yes' ?  'https://www.facebook.com/'.$FB_Shortcode['id'].'/videos/' : 'https://www.facebook.com/'.$FB_Shortcode['id'].'/photos_stream/';
				break;
			case 'hashtag':
				$fts_view_fb_link ='https://www.facebook.com/hashtag/'.$FB_Shortcode['id'].'/';
				break;
		    case 'reviews':
			   $fts_view_fb_link ='https://www.facebook.com/'.$FB_Shortcode['id'].'/reviews/';
			   break;
		}
		return $fts_view_fb_link;
	}
	//**************************************************
	// Get Feed Cache Name
	//**************************************************
	function get_fb_cache_name($FB_Shortcode){
		//URL to get page info
		switch ($FB_Shortcode['type']) {
			case 'album_photos':
				$fb_data_cache_name = 'fb_'.$FB_Shortcode['type'].'_'.$FB_Shortcode['id'].'_'.$FB_Shortcode['album_id'].'_num'.$FB_Shortcode['posts'].'';
				break;
			default:
				$fb_data_cache_name = 'fb_'.$FB_Shortcode['type'].'_'.$FB_Shortcode['id'].'_num'.$FB_Shortcode['posts'].'';
				break;
		}
		return $fb_data_cache_name;
	}
	//**************************************************
	// Get Language
	//**************************************************
	function get_language() {
		//this check is in place because we used this option and it failed for many people because we use wp get contents instead of curl
		// this can be removed in a future update and just keep the $language_option = $this->get_option('fb_language', 'en_US');
		$language_option_check = '';
		if (isset($language_option_check) && $language_option_check !== 'Please Select Option') {
			$language_option = 'en_US';
		}
		else {
			$language_option = 'en_US';
		}
		$language = !empty($language_option) ? '&locale='.$language_option : '';
		return $language;
	}
	//**************************************************
	// Get Facebook Feed Information
	//**************************************************
	function get_facebook_feed_response($FB_Shortcode, $fb_cache_name, $access_token, $language) {
		if (false !== ($transient_exists = $this->fts_check_feed_cache_exists($fb_cache_name)) and !isset($_GET['load_more_ajaxing'])) {
			$response = $this->fts_get_feed_cache($fb_cache_name);
		}
		else {
			//Page
			if ($FB_Shortcode['type'] == 'page' && $FB_Shortcode['posts_displayed'] == 'page_only') {
				$mulit_data = array('page_data' => 'https://graph.facebook.com/'.$FB_Shortcode['id'].'?fields=id,name,description&access_token='.$access_token.$language.'');
				$mulit_data['feed_data'] = isset($_REQUEST['next_url']) ? $_REQUEST['next_url'] : 'https://graph.facebook.com/'.$FB_Shortcode['id'].'/posts?fields=id,caption,created_time,description,from,icon,link,message,name,object_id,picture,place,shares,source,status_type,story,to,type&limit='.$FB_Shortcode['posts'].'&access_token='.$access_token.$language.'';
			}
			//Event
			elseif ($FB_Shortcode['type'] == 'events') {
				date_default_timezone_set($this->get_option('fts-timezone'));
				$date = date('Y-m-d');
				$mulit_data = array('page_data' => 'https://graph.facebook.com/'.$FB_Shortcode['id'].'?fields=id,name&access_token='.$access_token.$language.'');
				//Check If Ajax next URL needs to be used
				$mulit_data['feed_data'] = isset($_REQUEST['next_url']) ? $_REQUEST['next_url'] : 'https://graph.facebook.com/'.$FB_Shortcode['id'].'/events?since='.$date.'&access_token='.$access_token.$language.'';
			}
			//Albums
			elseif ($FB_Shortcode['type'] == 'albums') {
				$mulit_data = array('page_data' => 'https://graph.facebook.com/'.$FB_Shortcode['id'].'?fields=id,name,description,link&access_token='.$access_token.$language.'');
				//Check If Ajax next URL needs to be used
				$mulit_data['feed_data'] = isset($_REQUEST['next_url']) ? $_REQUEST['next_url'] : 'https://graph.facebook.com/'.$FB_Shortcode['id'].'/albums?fields=id,created_time,name,from,link,cover_photo,count,updated_time,type&limit='.$FB_Shortcode['posts'].'&access_token='.$access_token.$language.'';
			}
			//Album Photos
			elseif ($FB_Shortcode['type'] == 'album_photos') {
				$mulit_data = array('page_data' => 'https://graph.facebook.com/'.$FB_Shortcode['id'].'?fields=id,name,description&access_token='.$access_token.$language.'');
					//Check If Ajax next URL needs to be used
					//The reason I did not create a whole new else if for the video album is because I did not want to duplicate all the code required to make the video because the videos gallery comes from the photo albums on facebook.
					if (isset($FB_Shortcode['video_album']) && $FB_Shortcode['video_album'] == 'yes') {
					$mulit_data['feed_data'] = isset($_REQUEST['next_url']) ? $_REQUEST['next_url'] : 'https://graph.facebook.com/'.$FB_Shortcode['album_id'].'/videos?fields=id,created_time,description,from,icon,link,message,name,object_id,picture,place,shares,source,to,type,format,embed_html&limit='.$FB_Shortcode['posts'].'&access_token='.$access_token.$language.'';
					}
					else {
					$mulit_data['feed_data'] = isset($_REQUEST['next_url']) ? $_REQUEST['next_url'] : 'https://graph.facebook.com/'.$FB_Shortcode['album_id'].'/photos?fields=id,caption,created_time,description,from,icon,link,message,name,object_id,picture,place,shares,source,status_type,story,to,type&limit='.$FB_Shortcode['posts'].'&access_token='.$access_token.$language.'';
					}
			}
			//HashTag
			elseif ($FB_Shortcode['type'] == 'hashtag') {
				$mulit_data = array(
					'page_data' => 'https://graph.facebook.com/search?q=%23'.$FB_Shortcode['id'].'&access_token='.$access_token.$language.''
				);
				//Check If Ajax next URL needs to be used
				$mulit_data['feed_data'] = isset($_REQUEST['next_url']) ? $_REQUEST['next_url'] : 'https://graph.facebook.com/search?q=%23'.$FB_Shortcode['id'].'&limit='.$FB_Shortcode['posts'].'&access_token='.$access_token.$language.'';
				//Check If Ajax next URL needs to be used
			}
			//Group
			elseif ($FB_Shortcode['type'] == 'group') {
				$mulit_data = array('page_data' => 'https://graph.facebook.com/'.$FB_Shortcode['id'].'?fields=id,name,description&access_token='.$access_token.$language.'');
				//Check If Ajax next URL needs to be used
				$mulit_data['feed_data'] = isset($_REQUEST['next_url']) ? $_REQUEST['next_url'] : 'https://graph.facebook.com/'.$FB_Shortcode['id'].'/feed?fields=id,caption,created_time,description,from,icon,link,message,name,object_id,picture,place,shares,source,status_type,story,to,type&limit='.$FB_Shortcode['posts'].'&access_token='.$access_token.$language.'';
			}
			//Reviews
			elseif ($FB_Shortcode['type'] == 'reviews') {
				if (is_plugin_active('feed-them-social-facebook-reviews/feed-them-social-facebook-reviews.php')) {
					$FTS_Facebook_Reviews = new FTS_Facebook_Reviews();
					$mulit_data = $FTS_Facebook_Reviews->review_connection($FB_Shortcode, $access_token, $language);
				}
				else{
					return 'Please Purchase and Activate the Feed Them Social Reviews plugin.';
					exit;
				}
			}
			else {
				$mulit_data = array('page_data' => 'https://graph.facebook.com/'.$FB_Shortcode['id'].'?fields=id,name,description&access_token='.$access_token.$language.'');
				//Check If Ajax next URL needs to be used
				$mulit_data['feed_data'] = isset($_REQUEST['next_url']) ? $_REQUEST['next_url'] : 'https://graph.facebook.com/'.$FB_Shortcode['id'].'/feed?fields=id,caption,created_time,description,from,icon,link,message,name,object_id,picture,place,shares,source,status_type,story,to,type&limit='.$FB_Shortcode['posts'].'&access_token='.$access_token.$language.'';
			}

			$response = $this->fts_get_feed_json($mulit_data);

			//Error Check
			// $feed_data = json_decode($response['feed_data']);

			// $fts_error_check = new fts_error_handler();
			// $fts_error_check_complete = $fts_error_check->facebook_error_check($FB_Shortcode, $feed_data);
			// if($fts_error_check_complete == true){return false;}

			//Make sure it's not ajaxing
			if (!isset($_GET['load_more_ajaxing']) && !empty($response['feed_data'])) {
				//Create Cache
				$this->fts_create_feed_cache($fb_cache_name, $response);
			}
		} // end main else
		//RETURN THE RESPONSE!!!
		return $response;
	}

	function fts_get_feed_json($mulit_data)
	{
		// array of curl handles
		$curly = array();
		// data to be returned
		$response = array();

		// multi handle
		$mh = curl_multi_init();

		// loop through $data and create curl handles
		// then add them to the multi-handle
		foreach ($mulit_data as $id => $d) {

		  $curly[$id] = curl_init();

		  $url = (is_array($d) && !empty($d['url'])) ? $d['url'] : $d;
		  curl_setopt($curly[$id], CURLOPT_URL,            $url);
		  curl_setopt($curly[$id], CURLOPT_HEADER,         0);
		  curl_setopt($curly[$id], CURLOPT_RETURNTRANSFER, 1);

		  // post?
		  if (is_array($d)) {
			if (!empty($d['post'])) {
			  curl_setopt($curly[$id], CURLOPT_POST,       1);
			  curl_setopt($curly[$id], CURLOPT_POSTFIELDS, $d['post']);
			}
		  }

		  // extra options?
		  if (!empty($options)) {
			curl_setopt_array($curly[$id], $options);
		  }

		  curl_multi_add_handle($mh, $curly[$id]);
		}

		// execute the handles
		$running = null;
		do {
		  curl_multi_exec($mh, $running);
		} while($running > 0);

		// get content and remove handles
		foreach($curly as $id => $c) {
		  $response[$id] = curl_multi_getcontent($c);
		  curl_multi_remove_handle($mh, $c);
		}

		// all done
		curl_multi_close($mh);

		return $response;
	}

	function fts_create_feed_cache($fb_cache_name, $response)
	{
		//Cache
	  // $des = json_decode($response['page_data']);
	  // $data = json_decode($response['feed_data']);
	  $data = json_encode($response);

	  if (!file_exists($fb_cache_name)) {
		  touch($fb_cache_name);
	  }
	  // file_put_contents($fb_cache_name,json_encode($des));
	  file_put_contents($fb_cache_name, $data);
	}

	function fts_get_feed_cache($fb_cache_name)
	{
		$data_cache = __DIR__ . '/' .$fb_cache_name;
		$response = json_decode(file_get_contents($data_cache), true);

		return $response;
	}

	function fts_check_feed_cache_exists($fb_cache_name)
	{
		$data_cache = __DIR__ . '/' .$fb_cache_name;
		if (file_exists($data_cache) && !filesize($data_cache) == 0 && filemtime($data_cache) > time() - 900) {

			return true;
		} else {

			return false;
		}
	}

	function get_fts_dynamic_class_name(){
		$fts_dynamic_class_name =  '';
		if (isset($_REQUEST['fts_dynamic_name'])) {
			$fts_dynamic_class_name =  'feed_dynamic_class'.$_REQUEST['fts_dynamic_name'];
		}
		return $fts_dynamic_class_name;
	}

	//**************************************************
	// FB Social Button Placement
	//**************************************************
	function fb_social_btn_placement($FB_Shortcode, $access_token, $share_loc) {
		    //Don't do it for these!
			if($FB_Shortcode['type'] == 'group' || $FB_Shortcode['type'] == 'event' || isset($FB_Shortcode['hide_like_option']) && $FB_Shortcode['hide_like_option'] == 'yes') { return;}
			//Facebook Follow Button Options
			$fb_show_follow_btn = 'display';
			$fb_show_follow_btn_where = 'fb-like-top-below-title';
			if (!isset($_GET['load_more_ajaxing'])) {
				$like_option_align_final = isset($FB_Shortcode['like_option_align']) ? 'fts-fb-social-btn-'.$FB_Shortcode['like_option_align'].'' : '';
				$output ='';
				if($share_loc === $fb_show_follow_btn_where)	{
					switch($fb_show_follow_btn_where){
						case 'fb-like-top-above-title':
							// Top Above Title
							if (isset($fb_show_follow_btn) && $fb_show_follow_btn !== 'dont-display') {
								$output .= '<div class="fb-social-btn-top '.$like_option_align_final.'">';
									$output .= $this->social_follow_button('facebook', $FB_Shortcode['id'], $access_token);
								$output .= '</div>';
							}
							break;
						//Top Below Title
						case 'fb-like-top-below-title':
							if (isset($fb_show_follow_btn) && $fb_show_follow_btn !== 'dont-display') {
								$output .= '<div class="fb-social-btn-below-description">';
									$output .= $this->social_follow_button('facebook', $FB_Shortcode['id'], $access_token);
								$output .= '</div>';
							}
							break;
						//Bottom
						case 'fb-like-below':
							if (isset($fb_show_follow_btn) && $fb_show_follow_btn !== 'dont-display') {
								$output .= '<div class="fb-social-btn-bottom '.$like_option_align_final.'">';
									$output .= $this->social_follow_button('facebook', $FB_Shortcode['id'], $access_token);
								$output .= '</div>';
							}
							break;
					}
				}
			return $output ;
			}
	}
	//**************************************************
	// Create a random string
	//**************************************************
	function rand_string( $length = 10) {
			$characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$charactersLength = strlen($characters);
		$randomString = '';
		for ($i = 0; $i < $length; $i++) {
			$randomString .= $characters[rand(0, $charactersLength - 1)];
		}
		return $randomString;
	}
	//**************************************************
	// Social Follow Button.
	//**************************************************
	function social_follow_button($feed, $user_id, $access_token = NULL) {

		global $channel_id, $playlist_id, $username_subscribe_btn, $username;
		$output = '';
		switch ($feed) {
		case 'facebook':
				//Facebook settings options for follow button
				$fb_show_follow_btn = $this->get_option('fb_show_follow_btn');
				$fb_show_follow_like_box_cover = $this->get_option('fb_show_follow_like_box_cover');
				$language_option_check = $this->get_option('fb_language');
				$fb_app_ID = $this->get_option('fb_app_ID');

				if (isset($language_option_check) && $language_option_check !== 'Please Select Option') {
					$language_option = $this->get_option('fb_language', 'en_US');
				}
				else {
							$language_option = 'en_US';
				}
				$fb_like_btn_color = $this->get_option('fb_like_btn_color', 'light');
			//	var_dump( $fb_like_btn_color ); /* outputs 'default_value' */

				$show_faces = $fb_show_follow_btn == 'like-button-share-faces' || $fb_show_follow_btn == 'like-button-faces' || $fb_show_follow_btn == 'like-box-faces' ? 'true' : 'false';
				$share_button = $fb_show_follow_btn == 'like-button-share-faces' || $fb_show_follow_btn == 'like-button-share' ? 'true' : 'false';
				$page_cover = $fb_show_follow_like_box_cover == 'fb_like_box_cover-yes' ? 'true' : 'false';
				if(!isset($_POST['fts_facebook_script_loaded'])){
							$output .='<div id="fb-root"></div>
							<script>(function(d, s, id) {
							  var js, fjs = d.getElementsByTagName(s)[0];
							  if (d.getElementById(id)) return;
							  js = d.createElement(s); js.id = id;
							  js.src = "//connect.facebook.net/'.$language_option.'/sdk.js#xfbml=1&appId='.$fb_app_ID.'&version=v2.3";
							  fjs.parentNode.insertBefore(js, fjs);
							}(document, "script", "facebook-jssd"));</script>';
							$_POST['fts_facebook_script_loaded'] = 'yes';
				}
				//Page Box
				if($fb_show_follow_btn == 'like-box' || $fb_show_follow_btn == 'like-box-faces') {
					$output .='<div class="fb-page" data-href="https://www.facebook.com/'.$user_id.'" data-hide-cover="'.$page_cover.'" data-show-facepile="'.$show_faces.'" data-show-posts="false"></div>';
				}
				//Like Button
				else{
					$output .='<div class="fb-like" data-href="https://www.facebook.com/'.$user_id.'" data-layout="standard" data-action="like" data-colorscheme="'.$fb_like_btn_color.'" data-show-faces="'.$show_faces.'" data-share="'.$share_button.'" data-width:"100%"></div>';
				}
				return $output;
			break;
		case 'instagram':
			$output .='<a href="https://instagram.com/'.$user_id.'/" target="_blank">Follow on Instagram</a>';
			print $output;
			break;
		case 'twitter':
			if(!isset($_POST['fts_twitter_script_loaded'])){
				$output .='<script>window.twttr=(function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],t=window.twttr||{};if(d.getElementById(id))return t;js=d.createElement(s);js.id=id;js.src="https://platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);t._e=[];t.ready=function(f){t._e.push(f);};return t;}(document,"script","twitter-wjs"));</script>';
				$_POST['fts_twitter_script_loaded'] = 'yes';
			}
			$output .='<a class="twitter-follow-button" href="https://twitter.com/'.$user_id.'" data-show-count="false" data-lang="en"> Follow @'.$user_id.'</a>';
			print $output;
			break;
		case 'pinterest':
				if(!isset($_POST['fts_pinterest_script_loaded'])){
					$output .='
					<script>
						jQuery(function () {
						   	//then load the JavaScript file
						    jQuery.getScript("//assets.pinterest.com/js/pinit.js");
						});
					</script>
					';
					$_POST['fts_pinterest_script_loaded'] = 'yes';
				}

				$output .='<a data-pin-do="buttonFollow" href="http://www.pinterest.com/'.$user_id.'/">Follow @'.$user_id.'</a>';

				return $output;
			break;
		case 'youtube':
				if(!isset($_POST['fts_youtube_script_loaded'])){
					$output .='<script src="https://apis.google.com/js/platform.js"></script>';
					$_POST['fts_youtube_script_loaded'] = 'yes';
				}
					if($channel_id == '' && $playlist_id == '' && $username !== '' || $playlist_id !== '' && $username_subscribe_btn !== ''){

								if($username_subscribe_btn !== ''){
										$output .='<div class="g-ytsubscribe" data-channel="'.$username_subscribe_btn.'" data-layout="full" data-count="default"></div>';
								}
								else {
										$output .='<div class="g-ytsubscribe" data-channel="'.$user_id.'" data-layout="full" data-count="default"></div>';
								}

					}
					elseif($channel_id !== '' && $playlist_id !== '' || $channel_id !== '') {
						$output .='<div class="g-ytsubscribe" data-channelid="'.$channel_id.'" data-layout="full" data-count="default"></div>';
					}
				print $output;
			break;
		}
	}
}