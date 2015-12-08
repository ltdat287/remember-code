<div class="span-10 last">
    <?php if(get_theme_option('ads_125') != '') {
		?>
		<div class="sidebaradbox125">
			<?php sidebar_ads_125(); ?>
		</div>
	<?php } ?>
	
	<div class="span-5">
        
		<div class="sidebar">
          
		<?php get_search_form(); ?> 
			  <?php
    		if(get_theme_option('rssbox') == 'true') {
    			?>
    			<div class="rssbox">
    				<a href="<?php bloginfo('rss2_url'); ?>"><img src="<?php bloginfo('template_url'); ?>/images/rss.png"  alt="RSS Feed" title="RSS Feed" style="vertical-align:middle; margin-right: 5px;"  /></a><a href="<?php bloginfo('rss2_url'); ?>"><?php echo get_theme_option('rssboxtext'); ?></a>
    			</div>
    			<?php
    		}
    	?>
    	
    	<?php
    		if(get_theme_option('twitter') != '') {
    			?>
    			<div class="twitterbox">
    				<a href="<?php echo get_theme_option('twitter'); ?>"><img src="<?php bloginfo('template_url'); ?>/images/twitter.png"  alt="<?php echo get_theme_option('twittertext'); ?>" title="<?php echo get_theme_option('twittertext'); ?>" style="vertical-align:middle; margin-right: 5px;"  /></a><a href="<?php echo get_theme_option('twitter'); ?>"><?php echo get_theme_option('twittertext'); ?></a>
    			</div>
    			<?php
    		}
    	?>
    	
			<ul>
				<?php 
					if ( !function_exists('dynamic_sidebar') || !dynamic_sidebar('Sidebar 1') ) : ?>
					<li><h2><center>Lưu Trữ</center></h2>
						<ul>
						<?php wp_get_archives('type=monthly'); ?>
						</ul>
					</li>
					<?php wp_list_bookmarks(); ?>				
				<?php endif; ?>
			</ul>
            
		<?php if(get_theme_option('ad_sidebar1_bottom') != '') {
		?>
		<div class="sidebaradbox">
			<?php echo get_theme_option('ad_sidebar1_bottom'); ?>
		</div>
		<?php
		}
		?>
          
		</div>
	</div>

	<div class="span-5 last">
	
		<div class="sidebar">
        
        	<ul>
				<?php 
						if ( !function_exists('dynamic_sidebar') || !dynamic_sidebar('Sidebar 2') ) : ?>
				<li><h2><?php _e('Recent Posts'); ?></h2>
				               <ul>
						<?php wp_get_archives('type=postbypost&limit=5'); ?>  
				               </ul>
				
				
				
				<?php include (TEMPLATEPATH . '/recent-comments.php'); ?>
				<?php if (function_exists('get_recent_comments')) { get_recent_comments(); } ?>
				
				<li><h2><center>Tùy Chọn</center></h2>
					<ul>
						<?php wp_register(); ?>
						<li><?php wp_loginout(); ?></li>
						<li><a href="http://gmpg.org/xfn/"><abbr title="XHTML Friends Network">XFN</abbr></a></li>
						<li><a href="http://wordpress.org/" title="Powered by WordPress, state-of-the-art semantic personal publishing platform.">WordPress</a></li>
						<?php wp_meta(); ?>
					</ul>
					</li>
	
				<?php endif; ?>
			</ul>
            
		<?php if(get_theme_option('ad_sidebar2_bottom') != '') {
		?>
		<div class="sidebaradbox">
			<?php echo get_theme_option('ad_sidebar2_bottom'); ?>
		</div>
		<?php
		}
		?>
	
		</div>
		
	</div>
</div>
