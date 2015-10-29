<?php
$output .= '<div class="feed-them-social-admin-input-wrap">';
  $output .= '<div class="feed-them-social-admin-input-label"># of Tweets (optional)</div>';
  $output .= '<input type="text" name="tweets_count" id="tweets_count" class="feed-them-social-admin-input" value="'.$tweets_count_option.'" />';
$output .= '<div class="clear"></div>';
$output .= '</div><!--/feed-them-social-admin-input-wrap-->';

//Pop Up Option
$output .= '<div class="feed-them-social-admin-input-wrap">';
$output .= '<div class="feed-them-social-admin-input-label">Display Photos in Popup</div>';
$output .= '<select name="twitter_popup_option" id="twitter-popup-option" class="feed-them-social-admin-input">';
$output .= '<option '.selected($twitter_popup_option, 'no', false ) .' value="no">No</option>';
$output .= '<option '.selected($twitter_popup_option, 'yes', false ) .' value="yes">Yes</option>';
$output .= '</select>';
$output .= '<div class="clear"></div>';
$output .= '</div><!--/feed-them-social-admin-input-wrap-->';
?>