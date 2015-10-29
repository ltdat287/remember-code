<?php
//Pic Count Option
$output .= '<div class="feed-them-social-admin-input-wrap">';
$output .= '<div class="feed-them-social-admin-input-label"># of Pics (optional)</div>';
$output .= '<input type="text" name="pics_count" id="pics_count" class="feed-them-social-admin-input" value="'.$pics_count_option.'" />';
$output .= '<div class="clear"></div>';
$output .= '</div><!--/feed-them-social-admin-input-wrap-->';

//Pop Up Option
$output .= '<div class="feed-them-social-admin-input-wrap">';
$output .= '<div class="feed-them-social-admin-input-label">Display Photos in Popup</div>';
$output .= '<select name="instagram_popup_option" id="instagram-popup-option" class="feed-them-social-admin-input">';
$output .= '<option '.selected($instagram_popup_option, 'no', false ) .' value="no">No</option>';
$output .= '<option '.selected($instagram_popup_option, 'yes', false ) .' value="yes">Yes</option>';
$output .= '</select>';
$output .= '<div class="clear"></div>';
$output .= '</div><!--/feed-them-social-admin-input-wrap-->';
?>