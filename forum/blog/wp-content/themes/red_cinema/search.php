<?php get_header(); ?>
<div class="contentLayout">
<?php include (TEMPLATEPATH . '/sidebar1.php'); ?><div class="content">


	<?php if (have_posts()) : ?>

<div class="Post">
		    <div class="Post-body">
		<div class="Post-inner article">
		
<div class="PostContent">
        
        
		<h2><?php _e('Search Results', 'kubrick'); ?></h2>

		<?php
		$prev_link = get_previous_posts_link(__('Newer Entries &raquo;', 'kubrick'));
		$next_link = get_next_posts_link(__('&laquo; Older Entries', 'kubrick'));
		?>

		<?php if ($prev_link || $next_link): ?>
<div class="navigation">
	<div class="alignleft"><?php echo $next_link; ?></div>
	<div class="alignright"><?php echo $prev_link; ?></div>
</div>
		<?php endif; ?>


		</div>
		<div class="cleared"></div>
		

		</div>
		
				<div class="cleared"></div>
		    </div>
		</div>
		


		<?php while (have_posts()) : the_post(); ?>
<div class="Post">
			    <div class="Post-body">
			<div class="Post-inner article">
			<h2 class="PostHeader">
			<a href="<?php the_permalink() ?>" rel="bookmark" title="<?php printf(__('Permanent Link to %s', 'kubrick'), the_title_attribute('echo=0')); ?>">
			<?php the_title(); ?>
			</a>
			</h2>
			<?php $icons = array(); ?>
			<?php if (!is_page()): ?><?php ob_start(); ?><?php the_time(__('F jS, Y', 'kubrick')) ?>
			<?php $icons[] = ob_get_clean(); ?><?php endif; ?><?php if (!is_page()): ?><?php ob_start(); ?><?php _e('Author', 'kubrick'); ?>: <?php the_author_posts_link() ?>
			<?php $icons[] = ob_get_clean(); ?><?php endif; ?><?php if (current_user_can('edit_post', $post->ID)): ?><?php ob_start(); ?><?php edit_post_link(__('Edit', 'kubrick'), ''); ?>
			<?php $icons[] = ob_get_clean(); ?><?php endif; ?><?php if (0 != count($icons)): ?>
			<div class="PostHeaderIcons metadata-icons">
			<?php echo implode(' | ', $icons); ?>
			
			</div>
			<?php endif; ?>
			<div class="PostContent">
			
			          <?php if (is_search()) the_excerpt(); else the_content(__('Read the rest of this entry &raquo;', 'kubrick')); ?>
			          <?php if (is_page() or is_single()) wp_link_pages(array('before' => '<p><strong>Pages:</strong> ', 'after' => '</p>', 'next_or_number' => 'number')); ?>
			        
			</div>
			<div class="cleared"></div>
			<?php ob_start(); ?>
			<?php $icons = array(); ?>
			<?php if (!is_page()): ?><?php ob_start(); ?><?php printf(__('Posted in %s', 'kubrick'), get_the_category_list(', ')); ?>
			<?php $icons[] = ob_get_clean(); ?><?php endif; ?><?php if (!is_page() && get_the_tags()): ?><?php ob_start(); ?><?php the_tags(__('Tags:', 'kubrick') . ' ', ', ', ' '); ?>
			<?php $icons[] = ob_get_clean(); ?><?php endif; ?><?php if (!is_page() && !is_single()): ?><?php ob_start(); ?><?php comments_popup_link(__('No Comments &#187;', 'kubrick'), __('1 Comment &#187;', 'kubrick'), __('% Comments &#187;', 'kubrick'), '', __('Comments Closed', 'kubrick') ); ?>
			<?php $icons[] = ob_get_clean(); ?><?php endif; ?><?php if (0 != count($icons)): ?>
			<div class="PostFooterIcons metadata-icons">
			<?php echo implode(' | ', $icons); ?>
			
			</div>
			<?php endif; ?>
			<?php $metadataContent = ob_get_clean(); ?>
			<?php if (trim($metadataContent) != ''): ?>
			<div class="PostMetadataFooter">
			<?php echo $metadataContent; ?>
			
			</div>
			<?php endif; ?>
			
			</div>
			
					<div class="cleared"></div>
			    </div>
			</div>
			
		<?php endwhile; ?>

		<?php if ($prev_link || $next_link): ?>
		
<div class="Post">
		    <div class="Post-body">
		<div class="Post-inner article">
		
<div class="PostContent">
        
        
<div class="navigation">
	<div class="alignleft"><?php echo $next_link; ?></div>
	<div class="alignright"><?php echo $prev_link; ?></div>
</div>
		

		</div>
		<div class="cleared"></div>
		

		</div>
		
				<div class="cleared"></div>
		    </div>
		</div>
		
		
		<?php endif; ?>

	<?php else : ?>
<div class="Post">
		    <div class="Post-body">
		<div class="Post-inner article">
		
<div class="PostContent">
        
        
        <h2><?php _e('Search Results', 'kubrick'); ?></h2>
		<h2 class="center"><?php _e('No posts found. Try a different search?', 'kubrick'); ?></h2>
		<?php if(function_exists('get_search_form')) get_search_form(); ?>
		

		</div>
		<div class="cleared"></div>
		

		</div>
		
				<div class="cleared"></div>
		    </div>
		</div>
		

	<?php endif; ?>

</div>
<?php include (TEMPLATEPATH . '/sidebar2.php'); ?>
</div>
<div class="cleared"></div>

<?php get_footer(); ?>