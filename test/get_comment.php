<script type="text/javascript" src="https://code.jquery.com/jquery-2.1.4.js"></script>
<?php
	$FB_Shortcode = array(
		'id' => 'muv0z',
		'access_token' => '1463338250663058|312d53cdb378522f3657ce8f60769e0c',
		'language' => 'en_US',
		'posts' => '10',
		);
	extract($FB_Shortcode);
	$mulit_data = array(
		'feed_data' => 'https://graph.facebook.com/'.$id.'/feed?fields=id,caption,created_time,description,from,icon,link,message,name,object_id,picture,place,shares,source,status_type,story,to,type&limit='.$posts.'&access_token='.$access_token.'&locale='.$language.'',
		);
	// $url = 'https://graph.facebook.com/'.$id.'/feed?fields=id,caption,created_time,description,from,icon,link,message,name,object_id,picture,place,shares,source,status_type,story,to,type&limit='.$posts.'&access_token='.$access_token.$language.'';

	// Include tool help use curl get content
	include ('./help_tool.php');

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
				url : "get_comment_ajax.php",
				data : {
					fb_post_id : '<?php echo $counter->id; ?>'
				},
				error: function () {
					var text_error = $("<p>").text('An error current');
					$('<?php echo 'div.comment-info' . $counter->id ?>').append(text_error);
				},
				dataType: 'text/html',
				success : function (data) {
					$('div.comment-info<?php echo $counter->id; ?>').append(data);
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