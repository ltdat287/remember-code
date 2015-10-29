<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>test facebook</title>
	<link rel="stylesheet" href="./css/magnific-popup.css">
	<link rel="stylesheet" href="./css/font-awesome.min.css">
	<link rel="stylesheet" href="./css/styles.css">
</head>
<body>
	<div class="container" style="width:500px">
		<?php
			include('facebook-test.php');
			$display_feed = new Facebook_Feed;
			$content = $display_feed->fts_fb_func();
			echo $content;
		?>
	</div>
</body>
</html>