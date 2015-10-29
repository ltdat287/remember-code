<?php 

$output .= '<div class="feed-them-social-admin-input-wrap">';
  $output .= '<div class="feed-them-social-admin-input-label"># of Posts</div>';
  $output .= '<input type="text" name="fb_event_post_count" id="fb_event_post_count" class="feed-them-social-admin-input" value="'.$fb_event_post_count_option.'" placeholder="5 is the default value" />';
  $output .= '<div class="clear"></div>';
$output .= '</div><!--/feed-them-social-admin-input-wrap-->';

$output .= '<div class="feed-them-social-admin-input-wrap">';
  $output .= '<div class="feed-them-social-admin-input-label">Show Event Title</div>';
  $output .= '<select name="fb_event_title_option" id="fb_event_title_option" class="feed-them-social-admin-input">';
    $output .= '<option '. selected($fb_event_title_option, 'yes', false ) .' value="yes">Yes</option>';
    $output .= '<option '. selected($fb_event_title_option, 'no', false ) .' value="no">No</option>';
  $output .= '</select>';
  $output .= '<div class="clear"></div>';
$output .= '</div><!--/feed-them-social-admin-input-wrap-->';

$output .= '<div class="feed-them-social-admin-input-wrap">';
  $output .= '<div class="feed-them-social-admin-input-label">Show Event Description</div>';
  $output .= '<select name="fb_event_description_option" id="fb_event_description_option" class="feed-them-social-admin-input">';
    $output .= '<option '. selected($fb_event_description_option, 'yes', false ) .' value="yes">Yes</option>';
    $output .= '<option '. selected($fb_event_description_option, 'no', false ) .' value="no">No</option>';
  $output .= '</select>';
  $output .= '<div class="clear"></div>';
$output .= '</div><!--/feed-them-social-admin-input-wrap-->';

$output .= '<div class="feed-them-social-admin-input-wrap">';
  $output .= '<div class="feed-them-social-admin-input-label">Amount of words per post</div>';
 	$output .= '<input type="text" name="fb_event_word_count_option" id="fb_event_word_count_option" class="feed-them-social-admin-input" value="'.$fb_event_word_count_option.'" placeholder="45 is the default value" />';
  $output .= '<div class="clear"></div>';
$output .= '</div><!--/feed-them-social-admin-input-wrap-->';
?>