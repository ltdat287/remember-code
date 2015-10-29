<?php
$output .= '<div class="feed-them-social-admin-input-wrap youtube_name">';
    $output .= '<div class="feed-them-social-admin-input-label">Youtube Name (required)</div>';
    $output .= '<input type="text" name="youtube_name" id="youtube_name" class="feed-them-social-admin-input" value="'.$youtube_name_option.'" />';
$output .= '<div class="clear"></div>';
  $output .= '</div><!--/feed-them-social-admin-input-wrap-->';
  
  $output .= '<div class="feed-them-social-admin-input-wrap youtube_vid_count">';
    $output .= '<div class="feed-them-social-admin-input-label"># of videos (required)</div>';
    $output .= '<input type="text" name="youtube_vid_count" id="youtube_vid_count" class="feed-them-social-admin-input" value="'.$youtube_vid_count_option.'" />';
$output .= '<div class="clear"></div>';
  $output .= '</div><!--/feed-them-social-admin-input-wrap-->';
  
  $output .= '<div class="feed-them-social-admin-input-wrap youtube_columns">';
    $output .= '<div class="feed-them-social-admin-input-label"># of videos in each row?</div>';
    $output .= '<select id="youtube_columns" class="feed-them-social-admin-input" size="1">';
      $output .= '<option '. selected($youtube_columns_option, '1', false ) .' value="1">1 video per row</option>';
      $output .= '<option '. selected($youtube_columns_option, '2', false ) .' value="2">2 videos per row</option>';
      $output .= '<option '. selected($youtube_columns_option, '3', false ) .' value="3">3 videos per row</option>';
      $output .= '<option '. selected($youtube_columns_option, '4', false ) .' value="4">4 videos per row</option>';
    $output .= '</select>';
$output .= '<div class="clear"></div>';
  $output .= '</div><!--/feed-them-social-admin-input-wrap-->';
  
  $output .= '<div class="feed-them-social-admin-input-wrap youtube_first_video">';
    $output .= '<div class="feed-them-social-admin-input-label">Display First video full size?</div>';
    $output .= '<select name="youtube_first_video" id="youtube_first_video" class="feed-them-social-admin-input" size="1">';
      $output .= '<option '. selected($youtube_first_video_option, 'yes', false ) .' value="yes">Yes</option>';
      $output .= '<option '. selected($youtube_first_video_option, 'no', false ) .' value="no">No</option>';
    $output .= '</select>';
$output .= '<div class="clear"></div>';
  $output .= '</div><!--/feed-them-social-admin-input-wrap-->';

if(!$save_options){
	$output .= $this->generate_shortcode('updateTextArea_youtube();','YouTube Feed Shortcode','youtube-final-shortcode');
	$output .= '</form>';
}
else{
	$output .= '<input type="submit" class="feed-them-social-admin-submit-btn" value="Save Changes" />';
} 
?>