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
				echo'<pre>';
					print_r($feed_data);
				echo'</pre>';
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
		// this can be removed in a future update and just keep the $language_option = get_option('fb_language', 'en_US');
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
				date_default_timezone_set(get_option('fts-timezone'));
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
var_dump($data); die();
	  if (!file_exists($fb_cache_name)) {
		  touch($fb_cache_name);
	  }
	  // file_put_contents($fb_cache_name,json_encode($des));
	  file_put_contents($fb_cache_name, $data);
	}

	function fts_get_feed_cache($fb_cache_name)
	{
		$data_cache = __DIR__ . '/' .$fb_cache_name;
		$response = json_decode(file_get_contents($data_cache));
// var_dump($response); die();
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
			$fb_show_follow_btn = get_option('fb_show_follow_btn');
			$fb_show_follow_btn_where = get_option('fb_show_follow_btn_where');
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
}