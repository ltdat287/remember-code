var tweets_count = jQuery("input#tweets_count").val();
var twitter_height = jQuery("input#twitter_height").val();
var twitter_popup_option =  ' popup=' + jQuery("select#twitter-popup-option").val();

if (tweets_count !== '')	{
		 var tweets_count_final = ' tweets_count=' + jQuery("input#tweets_count").val();
	}
	else	{
		var tweets_count_final = ' tweets_count=5';
}

if (twitter_height)	{
	var twitter_height_final = ' twitter_height=' + jQuery("input#twitter_height").val();
	}
	else {
		var twitter_height_final = ' twitter_height=auto';
	}
	
var final_twitter_shorcode = '[fts twitter' + twitter_name  + twitter_height_final  + tweets_count_final + twitter_popup_option + ']';