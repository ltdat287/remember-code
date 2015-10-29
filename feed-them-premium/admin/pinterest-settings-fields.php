<?php 
$output .= '<div class="feed-them-social-admin-input-wrap pinterest_name">';
  $output .= '<div class="feed-them-social-admin-input-label">Pinterest Username (required)</div>';
  $output .= '<input type="text" name="pinterest_name" id="pinterest_name" class="feed-them-social-admin-input" value="'.$pinterest_name_option.'" />';
  $output .= '<div class="clear"></div>';
$output .= '</div><!--/feed-them-social-admin-input-wrap-->';

$output .= '<div class="feed-them-social-admin-input-wrap">';
  $output .= '<div class="feed-them-social-admin-input-label"># of Boards</div>';
  $output .= '<input type="text" name="boards_count" id="boards_count" class="feed-them-social-admin-input" placeholder="6 is the default value" value="'.$boards_count_option.'" />';
  $output .= '<div class="clear"></div>';
$output .= '</div><!--/feed-them-social-admin-input-wrap-->';


if(!$save_options){
	$output .= $this->generate_shortcode('updateTextArea_pinterest();','Pinterest Feed Shortcode','pinterest-final-shortcode');
	$output .= '</form>';
}
else{
	$output .= '<input type="submit" class="feed-them-social-admin-submit-btn" value="Save Changes" />';
} 

?>