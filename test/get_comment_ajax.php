<?php
	require_once './help_tool.php';

	if (! isset($_GET['fb_post_id'])) {
		echo 'dfgsdfgsdfg';
	} else {
		$id_post = $_GET['fb_post_id'];
		// Content html to output
		$html_output = '';

		$url_post = 'https://graph.facebook.com/' . $id_post . '?fields=comments';
		$obj_post = get_object_feed($url_post);

		if (! empty($obj_post->comments->data)) {
			// $html_output .= '<div class="comment-container">';

			foreach ($obj_post->comments->data as $key => $value) {
				$id_comment = $value->id;
				$message_comment = $value->message;
				$likes_count = $value->like_count;
				$created_time = $value->created_time;
				$user_name = $value->from->name;
				$icon_user = 'http://graph.facebook.com/' . $value->from->id . '/picture';

				// Content html to output
				$html_output .= '<div class="comment-parrent">';
				$html_output .= '<img class="icon-comment" src="' . $icon_user . '"/>';
				$html_output .= '<div class="content-comment"><a href="http://facebook.com/' . $value->from->id . '" target="_blank"><strong>' . $user_name . '</strong></a>';
				$html_output .= '<p>' . $message_comment . '</p>';
				$html_output .= '<div class="info-comment">' . ($likes_count > 0) ? ('Likes ' . $likes_count . ' · ') : '';
				$html_output .= date('F jS, Y \a\t g:ia', strtotime($created_time));
				$html_output .= '</div>'; // End info-comment comment
				$html_output .= '</div>'; // End content comment
				$html_output .= '</div>'; // End parrent comment

				// Check comment has comment child
				// if ($value->comment_count > 0) {
				// 	$url_comment_child = 'https://graph.facebook.com/' . $id_comment . '?fields=comments';
				// 	$obj_comment = get_object_feed($url_comment_child);
				// 	if (! empty($obj_comment)) {
				// 		foreach ($obj_comment as $key_comment => $value_comment) {
				// 			$message_child = $value_comment->message;
				// 			$created_child = $value_comment->created_time;
				// 			$user_child = $value_comment->from->name;
				// 			$icon_child = 'http://graph.facebook.com/' . $value_comment->from->id . '/picture';
				// 			$likes_child = $value_comment->likes_count;

				// 			// Content html output of child comment
				// 			$html_output .= '<div class="comment-child">';
				// 			$html_output .= '<img class="icon-comment" src="' . $icon_child . '"/>';
				// 			$html_output .= '<div class="content-comment"><a href="http://facebook.com/' . $value_comment->from->id . '" target="_blank"><strong>' . $user_child . '</strong></a>';
				// 			$html_output .= '<p>' . $message_child . '</p>';
				// 			$html_output .= '<div class="info-comment">' . ($likes_child > 0) ? ('Likes ' . $likes_child . ' · ') : '';
				// 			$html_output .= date('F jS, Y \a\t g:ia', strtotime($created_child));
				// 			$html_output .= '</div>'; // End info-comment comment
				// 			$html_output .= '</div>'; // End content comment
				// 			$html_output .= '</div>'; // End parrent comment
				// 		}
				// 	}
				// }
			}
			// $html_output .= '</div>'; // End all comment
		}

		echo $html_output;
	}
?>