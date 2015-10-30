<script src="https://code.jquery.com/jquery-2.1.4.js"></script>
<?php
	$FB_Shortcode = array(
		'id' => 'toyota',
		'access_token' => '1463338250663058|312d53cdb378522f3657ce8f60769e0c',
		'language' => 'en_US',
		'posts' => '10',
		);
	extract($FB_Shortcode);
	$mulit_data = array(
		'feed_data' => 'https://graph.facebook.com/'.$id.'/feed?fields=id,caption,created_time,description,from,icon,link,message,name,object_id,picture,place,shares,source,status_type,story,to,type&limit='.$posts.'&access_token='.$access_token.'&locale='.$language.'',
		);
	// $url = 'https://graph.facebook.com/'.$id.'/feed?fields=id,caption,created_time,description,from,icon,link,message,name,object_id,picture,place,shares,source,status_type,story,to,type&limit='.$posts.'&access_token='.$access_token.$language.'';

	function fts_get_feed_json($mulit_data)
	{
		// var_dump($mulit_data); die();
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
		  // Create array options need ssl cerifypeer
			$options = [
		        CURLOPT_SSL_VERIFYPEER => false,
		    ];
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

	$response = fts_get_feed_json($mulit_data);
	$feed_data = json_decode($response['feed_data']);

	echo '<table style="border: 1px solid #000;">';
	foreach ($feed_data->data as $counter) {
		$array_id[] = $counter->id;
		echo '<tr><td style="border: 1px solid #000;">' . $counter->id . '</td>
		<td><div class="comment-info' . $counter->id . '" style="border: 1px solid #000;">
		<button id="show-comment' . $counter->id . '">Show Comment</button>
		</div></td></tr>';
?>
<script type="text/javascript">
	jQuery(document).ready(function() {
		$(<?php echo '"#show-comment' . $counter->id . '"' ?>).click(function() {
			$.ajax({
				url : "https://graph.facebook.com/<?php echo $counter->id ?>?fields=comments",
				data : {
					format : 'json'
				},
				error: function () {
					var text_error = $("<p>").text('An error current');
					$('<?php echo 'div.comment-info' . $counter->id ?>').append(text_error);
				},
				dataType : 'json',
				success : function (data) {
					if (data.comments) {
						$(data.comments.data).each(function ($key, $value) {
							// console.log($value.from.name, $value.message);
							var text = $value.from.name;
							console.log(text);
							var display_name = '<p>' + text + '</p><br />';
							var display_comment = $("<p>").text($value.message);
							console.log(display_comment);
							$('<?php echo 'div.comment-info' . $counter->id ?>').append(display_name).append(display_comment);
						});
					} else {
						var no_comment = $("<p>").text('No Comment');
						$('<?php echo 'div.comment-info' . $counter->id ?>').append(no_comment);
					}
				},
				type : 'GET'
			});
		});
	});
</script>
<?php
	}
	echo '</table>';
?>