<?php 
		
  
		// FACEBOOK LOAD MORE OPTION
		$output .= '<div class="feed-them-social-admin-input-wrap">';
        $output .= '<div class="feed-them-social-admin-input-label">Load more posts</div>';
        $output .= '<select name="'.$fts_bar_fb_prefix.'fb_load_more_option" id="fb_load_more_option" class="feed-them-social-admin-input">';
        $output .= '<option '.selected($fb_load_more_option, 'no', false ) .' value="no">No</option>';
        $output .= '<option '.selected($fb_load_more_option, 'yes', false ) .' value="yes">Yes</option>';
        $output .= '</select>';
        $output .= '<div class="clear"></div>';
        $output .= '</div><!--/feed-them-social-admin-input-wrap-->';
		
		
		// FACEBOOK LOAD MORE TYPE OPTION
		$output .= '<div class="fts-facebook-load-more-options-wrap" style="display:none">';
		$output .= '<div class="instructional-text"><strong>NOTE:</strong> The Button option will show a "Load More Posts" button under your feed. The AutoScroll option will load more posts when you reach the bottom of the feed. AutoScroll ONLY works if you\'ve filled in a Fixed Height for your feed.</div>';
		$output .= '<div class="feed-them-social-admin-input-wrap">';
        $output .= '<div class="feed-them-social-admin-input-label">Load more style</div>';
        $output .= '<select name="'.$fts_bar_fb_prefix.'fb_load_more_style" id="fb_load_more_style" class="feed-them-social-admin-input">';
        $output .= '<option '.selected($fb_load_more_style, 'button', false ) .' value="button">Button</option>';
		$output .= '<option '.selected($fb_load_more_style, 'autoscroll', false ) .' value="autoscroll">AutoScroll</option>';
        
		$output .= '</select>';
        $output .= '<div class="clear"></div>';
        $output .= '</div><!--/feed-them-social-admin-input-wrap-->';
        $output .= '</div><!--/fts-facebook-load-more-options-wrap-->';
		
		
	// FACEBOOK POP UP FOR PHOTOS
	$output .= '<div class="feed-them-social-admin-input-wrap">';
	  $output .= '<div class="feed-them-social-admin-input-label">Display Photos in Popup</div>';
	  $output .= '<select name="'.$fts_bar_fb_prefix.'facebook_popup" id="facebook_popup" class="feed-them-social-admin-input">';
		$output .= '<option '.selected($facebook_popup, 'no', false ) .' value="no">No</option>';
		$output .= '<option '.selected($facebook_popup, 'yes', false ) .' value="yes">Yes</option>';
	  $output .= '</select>';
	  $output .= '<div class="clear"></div>';
	$output .= '</div><!--/feed-them-social-admin-input-wrap-->';
	
		
//IF NOT FTS BAR
if (isset($_GET['page']) && $_GET['page'] !== 'fts-bar-settings-page'){
	// FACEBOOK GRID OPTIONS
	$output .= '<div class="feed-them-social-admin-input-wrap fb-posts-in-grid-option-wrap">';
	  $output .= '<div class="feed-them-social-admin-input-label">Display Posts in Grid</div>';
	  $output .= '<select name="fb-grid-option" id="fb-grid-option" class="feed-them-social-admin-input">';
        $output .= '<option '.selected($fb_page_posts_displayed_option, 'no', false ) .' value="no">No</option>';
		$output .= '<option '.selected($fb_page_posts_displayed_option, 'yes', false ) .' value="yes">Yes</option>';
	  $output .= '</select>';
	  $output .= '<div class="clear"></div>';
	$output .= '</div><!--/feed-them-social-admin-input-wrap-->';
	
	// FACEBOOK GRID WIDTH
$output .= '<div class="fts-facebook-grid-options-wrap" style="display:none">';
		$output .= '<div class="instructional-text"><strong>NOTE:</strong> Define the Width of each post and the Space between each post below.</div>';
	$output .= '<div class="feed-them-social-admin-input-wrap">';
	  $output .= '<div class="feed-them-social-admin-input-label">Grid Column Width</div>';
		$output .= '<input type="text" name="facebook_grid_colmn_width" id="facebook_grid_colmn_width" class="feed-them-social-admin-input" value="'.$facebook_grid_colmn_width.'" placeholder="310px for example"/>';
	  $output .= '<div class="clear"></div>';
	$output .= '</div><!--/feed-them-social-admin-input-wrap-->';
	
	// FACEBOOK GRID SPACE BETWEEN PHOTOS
	$output .= '<div class="feed-them-social-admin-input-wrap">';
	  $output .= '<div class="feed-them-social-admin-input-label">Grid Spaces Between Posts</div>';
		$output .= '<input type="text" name="facebook_grid_space_between_posts" id="facebook_grid_space_between_posts" class="feed-them-social-admin-input" value="'.$facebook_grid_space_between_posts.'" placeholder="10px for example"/>';
	  $output .= '<div class="clear"></div>';
	$output .= '</div><!--/feed-them-social-admin-input-wrap-->';
$output .= '</div><!--/fts-facebook-grid-options-wrap-->';	

}
	
?>