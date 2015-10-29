<?php 
		// FACEBOOK LOAD MORE OPTION
		$output .= '<div class="feed-them-social-admin-input-wrap">';
        $output .= '<div class="feed-them-social-admin-input-label">Load more posts<br/><small>If yes, more options will appear</small></div>';
        $output .= '<select name="fb_load_more_option" id="fb_load_more_option" class="feed-them-social-admin-input">';
        $output .= '<option '.selected($fb_page_posts_displayed_option, 'no', false ) .' value="no">No</option>';
        $output .= '<option '.selected($fb_page_posts_displayed_option, 'yes', false ) .' value="yes">Yes</option>';
        $output .= '</select>';
        $output .= '<div class="clear"></div>';
        $output .= '</div><!--/feed-them-social-admin-input-wrap-->';
		
		
		// FACEBOOK LOAD MORE TYPE OPTION
		$output .= '<div class="fts-facebook-load-more-options-wrap" style="display:none">';
		$output .= '<div class="instructional-text"><strong>NOTE:</strong> The Button option will show a "Load More Posts" button under your feed. The AutoScroll option will load more posts when you reach the bottom of the feed. AutoScroll ONLY works if you\'ve filled in a Fixed Height for your feed.</div>';
		$output .= '<div class="feed-them-social-admin-input-wrap">';
        $output .= '<div class="feed-them-social-admin-input-label">Load more style</div>';
        $output .= '<select name="fb_load_more_style" id="fb_load_more_style" class="feed-them-social-admin-input">';
        $output .= '<option '.selected($fb_page_posts_displayed_option, 'button', false ) .' value="button">Button</option>';
		$output .= '<option '.selected($fb_page_posts_displayed_option, 'autoScroll', false ) .' value="autoscroll">AutoScroll</option>';
        
		$output .= '</select>';
        $output .= '<div class="clear"></div>';
        $output .= '</div><!--/feed-them-social-admin-input-wrap-->';
        $output .= '</div><!--/fts-facebook-load-more-options-wrap-->';
?>