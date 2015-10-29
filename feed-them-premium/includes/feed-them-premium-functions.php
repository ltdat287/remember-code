<?php
/************************************************
 	Function file for Feed Them Social plugin
************************************************/
if (isset($_GET['page']) && $_GET['page'] == 'feed-them-settings-page') {
  add_action('admin_enqueue_scripts', 'feed_them_premium_settings');
  
  function feed_them_premium_settings() {
	  wp_register_style( 'feed_them__premium_settings_css', plugins_url( 'admin/css/premium-settings-page.css',  dirname(__FILE__) ) );
	  wp_enqueue_style('feed_them__premium_settings_css'); 
  }
}

/******* Add function for All Wordpress Text Widgets ***********/
  
//YouTube FTS Widget
//CONSTRUCT Class
class fts_youtube_widget extends WP_Widget {
function fts_youtube_widget() {
    $widget_ops = array('classname' => 'YouTubeFTS', 'description' => 'Feed Them Social, YouTube Feed' );
    //WIDGET Name
    $this->WP_Widget('youtube_fts', 'FTS YouTube', $widget_ops);
}

//WIDGET Args
function widget($args, $instance) {
    extract($args, EXTR_SKIP);
    echo $before_widget;
    //WIDGET Database Checks
    $title = empty($instance['title']) ? ' ' : apply_filters('widget_youtube_fts_title', $instance['title']);
    $youtube_name = empty($instance['youtube_name']) ? ' ' : apply_filters('widget_youtube_fts_youtube_name', $instance['youtube_name']);
 	$number_of_vids = empty($instance['number_of_vids']) ? ' ' : apply_filters('widget_youtube_fts_number_of_vids', $instance['number_of_vids']);
 	$youtube_columns = empty($instance['youtube_columns']) ? ' ' : apply_filters('widget_youtube_fts_youtube_columns', $instance['youtube_columns']);
 	$youtube_first_video = empty($instance['youtube_first_video']) ? ' ' : apply_filters('widget_youtube_fts_youtube_first_video', $instance['youtube_first_video']);
    $youtube_fts_shortcode = empty($instance['youtube_fts_shortcode']) ? ' ' : apply_filters('widget_youtube_fts_youtube_fts_shortcode', $instance['youtube_fts_shortcode']);
//HERE IS WHERE WE OUTPUT OUR TITLE AND SHORTCODE
if ( !empty( $title ) && (strlen($title) > 1 )) { echo $before_title . $title . $after_title; };
echo do_shortcode('[fts youtube username='.$youtube_name.' vid_count='.$number_of_vids.' vids_in_row='.$youtube_columns.' large_vid='.$youtube_first_video.']');
    echo $after_widget;
}
//WIDGET Save
function update($new_instance, $old_instance) {
    $instance = $old_instance;
    $instance['title'] = strip_tags($new_instance['title']);
    $instance['youtube_name'] = strip_tags($new_instance['youtube_name']);
	$instance['number_of_vids'] = strip_tags($new_instance['number_of_vids']);
	$instance['youtube_columns'] = strip_tags($new_instance['youtube_columns']);
	$instance['youtube_first_video'] = strip_tags($new_instance['youtube_first_video']);
    $instance['youtube_fts_shortcode'] = strip_tags($new_instance['youtube_fts_shortcode']);
    return $instance;
}
//WIDGET Admin Form
function form($instance) {
    //set your params, $instance and then any additional variables needed below
    $instance = wp_parse_args( (array) $instance, array( 'title' => '', 'youtube_fts_shortcode' => '', 'youtube_name' => '' ) );
    $title = strip_tags($instance['title']);
    $youtube_name = strip_tags($instance['youtube_name']);
    $number_of_vids = strip_tags($instance['number_of_vids']);
    $youtube_columns = strip_tags($instance['youtube_columns']);
    $youtube_first_video = strip_tags($instance['youtube_first_video']);
    $youtube_fts_shortcode = strip_tags($instance['youtube_fts_shortcode']);
	
//Add CSS stylesheet for Widgets, I used the premium settings page to extend the widget CSS and save weight
 wp_enqueue_style( 'fts_youtube_css', plugins_url( 'admin/css/premium-settings-page.css',  dirname(__FILE__) ) );
?>
<div class="slickremix-widget-header-wrap">
<h2 class="fts-widget-header-h2"><?php _e('Feed Them Social', 'feed-them-premium'); ?></h2>
<h2 class="fts-widget-header-h3"><?php _e('YouTube Feed', 'feed-them-premium'); ?></h2>
</div>
<div class="fts-label-input-wrap">
  <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Your Title:', 'feed-them-premium'); ?></label>
  <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr__($title); ?>" />
</div>
<div class="fts-label-input-wrap">
  <label for="<?php echo $this->get_field_id('youtube_name'); ?>"><?php _e('Youtube Name (required)', 'feed-them-premium'); ?></label>
  <input required="required" class="widefat" id="<?php echo $this->get_field_id('youtube_name'); ?>" name="<?php echo $this->get_field_name('youtube_name'); ?>" type="text" value="<?php echo esc_attr__($youtube_name); ?>" />
</div>
<div class="fts-label-input-wrap">
  <label for="<?php echo $this->get_field_id('number_of_vids'); ?>"><?php _e('Number of Videos (required)', 'feed-them-premium'); ?></label>
  <input required="required" class="widefat" id="<?php echo $this->get_field_id('number_of_vids'); ?>" name="<?php echo $this->get_field_name('number_of_vids'); ?>" type="text" value="<?php echo esc_attr__($number_of_vids); ?>" />
</div>
<div class="fts-label-input-wrap">
  <label for="<?php echo $this->get_field_id('youtube_columns'); ?>"><?php _e('Number of Videos in Each Row:', 'feed-them-premium'); ?></label>
  <select id="<?php echo $this->get_field_id('youtube_columns'); ?>" name="<?php echo $this->get_field_name('youtube_columns'); ?>" class="widefat" size="1">
    <option value="1" <?php selected('1', $instance['youtube_columns']); ?>><?php _e('1 video per row', 'feed-them-premium'); ?></option>
    <option value="2" <?php selected('2', $instance['youtube_columns']); ?>><?php _e('2 videos per row', 'feed-them-premium'); ?></option>
    <option value="3" <?php selected('3', $instance['youtube_columns']); ?>><?php _e('3 videos per row', 'feed-them-premium'); ?></option>
    <option value="4" <?php selected('4', $instance['youtube_columns']); ?>><?php _e('4 videos per row', 'feed-them-premium'); ?></option>
  </select>
</div>
<div class="fts-label-input-wrap last">
  <label for="<?php echo $this->get_field_id('youtube_first_video'); ?>"><?php _e('Show First video full size:', 'feed-them-premium'); ?></label>
  <select id="<?php echo $this->get_field_id('youtube_first_video'); ?>" name="<?php echo $this->get_field_name('youtube_first_video'); ?>" class="widefat" size="1">
    <option value="yes" <?php selected('yes', $instance['youtube_first_video']); ?>><?php _e('yes', 'feed-them-premium'); ?></option>
    <option value="no" <?php selected('no', $instance['youtube_first_video']); ?>><?php _e('no', 'feed-them-premium'); ?></option>
  </select>
</div>
<?php
    }
}
 //CREATE Widget
 add_action( 'widgets_init', create_function('', 'register_widget("fts_youtube_widget");') );
 //YouTube FTS Widget End











//Facebook PAGE FTS Widget
//CONSTRUCT Class
class fts_facebook_page_widget extends WP_Widget {
function fts_facebook_page_widget() {
    $widget_ops = array('classname' => 'FacebookpageFTS', 'description' => 'Feed Them Social, Facebook page Feed' );
    //WIDGET Name
    $this->WP_Widget('facebook_page_fts', 'FTS Facebook page', $widget_ops);
}
//WIDGET Args
function widget($args, $instance) {
    extract($args, EXTR_SKIP);
    echo $before_widget;
    //WIDGET Database Checks
    $title = empty($instance['title']) ? ' ' : apply_filters('widget_facebook_page_fts_title', $instance['title']);
    $facebook_page_name = empty($instance['facebook_page_name']) ? ' ' : apply_filters('widget_facebook_page_fts_facebook_page_name', $instance['facebook_page_name']);
 	$number_of_posts = empty($instance['number_of_posts']) ? ' ' : apply_filters('widget_facebook_page_fts_number_of_posts', $instance['number_of_posts']);
 	$fb_page_posts_displayed = empty($instance['fb_page_posts_displayed']) ? ' ' : apply_filters('widget_facebook_page_fts_fb_page_posts_displayed', $instance['fb_page_posts_displayed']);
 	$fb_page_title = empty($instance['fb_page_title']) ? ' ' : apply_filters('widget_facebook_page_fts_fb_page_title', $instance['fb_page_title']);
 	$fb_page_description = empty($instance['fb_page_description']) ? ' ' : apply_filters('widget_facebook_page_fts_fb_page_description', $instance['fb_page_description']);
 	$fb_page_word_count = empty($instance['fb_page_word_count']) ? ' ' : apply_filters('widget_facebook_page_fts_fb_page_word_count', $instance['fb_page_word_count']);
//HERE IS WHERE WE OUTPUT OUR TITLE AND SHORTCODE
if ( !empty( $title ) && (strlen($title) > 1 )) { echo $before_title . $title . $after_title; };
	echo do_shortcode('[fts facebook page id='.$facebook_page_name.' posts='.$number_of_posts.' posts_displayed='.$fb_page_posts_displayed.' title='.$fb_page_title.' description='.$fb_page_description.' words='.$fb_page_word_count.' type=page]');
    echo $after_widget;
}
//WIDGET Save
function update($new_instance, $old_instance) {
    $instance = $old_instance;
    $instance['title'] = strip_tags($new_instance['title']);
    $instance['facebook_page_name'] = strip_tags($new_instance['facebook_page_name']);
	$instance['number_of_posts'] = strip_tags($new_instance['number_of_posts']);
	$instance['fb_page_posts_displayed'] = strip_tags($new_instance['fb_page_posts_displayed']);
    $instance['fb_page_title'] = strip_tags($new_instance['fb_page_title']);
    $instance['fb_page_description'] = strip_tags($new_instance['fb_page_description']);
    $instance['fb_page_word_count'] = strip_tags($new_instance['fb_page_word_count']);
	
    return $instance;
}
//WIDGET Admin Form
function form($instance) {
    //set your params, $instance and then any additional variables needed below
    $instance = wp_parse_args( (array) $instance, array( 'title' => '', 'facebook_page_name' => '', 'number_of_posts' => '', 'fb_page_posts_displayed' => '', 'fb_page_title' => '', 'fb_page_description' => '', 'fb_page_word_count' => '' ) );
    $title = strip_tags($instance['title']);
    $facebook_page_name = strip_tags($instance['facebook_page_name']);
    $number_of_posts = strip_tags($instance['number_of_posts']);
    $fb_page_posts_displayed = strip_tags($instance['fb_page_posts_displayed']);
    $fb_page_title = strip_tags($instance['fb_page_title']);
    $fb_page_description = strip_tags($instance['fb_page_description']);
    $fb_page_word_count = strip_tags($instance['fb_page_word_count']);
?>
<div class="slickremix-widget-header-wrap">
<h2 class="fts-widget-header-h2"><?php _e('Feed Them Social', 'feed-them-premium'); ?></h2>
<h2 class="fts-widget-header-h3"><?php _e('FB Page Feed', 'feed-them-premium'); ?></h2>
</div>
<div class="fts-label-input-wrap">
  <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Your Title:', 'feed-them-premium'); ?></label>
  <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr__($title); ?>" />
</div>
<div class="fts-label-input-wrap">
  <label for="<?php echo $this->get_field_id('facebook_page_name'); ?>"><?php _e('Facebook Page Name (required)', 'feed-them-premium'); ?></label>
  <input required class="widefat" id="<?php echo $this->get_field_id('facebook_page_name'); ?>" name="<?php echo $this->get_field_name('facebook_page_name'); ?>" type="text" value="<?php echo esc_attr__($facebook_page_name); ?>" />
</div>
<div class="fts-label-input-wrap">
  <label for="<?php echo $this->get_field_id('fb_page_posts_displayed'); ?>"><?php _e('Post Type Visible', 'feed-them-premium'); ?></label>
  <select id="<?php echo $this->get_field_id('fb_page_posts_displayed'); ?>" name="<?php echo $this->get_field_name('fb_page_posts_displayed'); ?>"  class="widefat" size="1">
    <option value="page_only" <?php selected('page_only', $instance['fb_page_posts_displayed']); ?>><?php _e('Display Posts made by Page only', 'feed-them-premium'); ?></option>
    <option value="everyone" <?php selected('everyone', $instance['fb_page_posts_displayed']); ?>><?php _e('Display Posts made by Everyone', 'feed-them-premium'); ?></option>
  </select>
</div>
<!--/feed-them-social-admin-input-wrap-->
<div class="fts-label-input-wrap">
  <label for="<?php echo $this->get_field_id('number_of_posts'); ?>"><?php _e('Number of posts (required)', 'feed-them-premium'); ?></label>
  <input required class="widefat" id="<?php echo $this->get_field_id('number_of_posts'); ?>" name="<?php echo $this->get_field_name('number_of_posts'); ?>" type="text" value="<?php echo esc_attr__($number_of_posts); ?>" />
</div>
<div class="fts-label-input-wrap">
  <label for="<?php echo $this->get_field_id('fb_page_title'); ?>"><?php _e('Show page title', 'feed-them-premium'); ?></label>
  <select id="<?php echo $this->get_field_id('fb_page_title'); ?>" name="<?php echo $this->get_field_name('fb_page_title'); ?>" class="widefat" size="1">
    <option value="yes" <?php selected('yes', $instance['fb_page_title']); ?>><?php _e('yes', 'feed-them-premium'); ?></option>
    <option value="no" <?php selected('no', $instance['fb_page_title']); ?>><?php _e('no', 'feed-them-premium'); ?></option>
  </select>
</div>
<div class="fts-label-input-wrap">
  <label for="<?php echo $this->get_field_id('fb_page_description'); ?>"><?php _e('Show page description', 'feed-them-premium'); ?></label>
  <select id="<?php echo $this->get_field_id('fb_page_description'); ?>" name="<?php echo $this->get_field_name('fb_page_description'); ?>" class="widefat" size="1">
    <option value="yes" <?php selected('yes', $instance['fb_page_description']); ?>><?php _e('yes', 'feed-them-premium'); ?></option>
    <option value="no" <?php selected('no', $instance['fb_page_description']); ?>><?php _e('no', 'feed-them-premium'); ?></option>
  </select>
</div>
<div class="fts-label-input-wrap last">
  <label for="<?php echo $this->get_field_id('fb_page_word_count'); ?>"><?php _e('Number of words per post (required)', 'feed-them-premium'); ?></label>
  <input required class="widefat" id="<?php echo $this->get_field_id('fb_page_word_count'); ?>" name="<?php echo $this->get_field_name('fb_page_word_count'); ?>" type="text" value="<?php echo esc_attr__($fb_page_word_count); ?>" />
</div>
<?php
    }
}
 //CREATE Widget
 add_action( 'widgets_init', create_function('', 'register_widget("fts_facebook_page_widget");') );
 //Facebook page FTS Widget End
 
 
 
 
 
 
 
 
 //Facebook Group FTS Widget
//CONSTRUCT Class
class fts_facebook_group_widget extends WP_Widget {
function fts_facebook_group_widget() {
    $widget_ops = array('classname' => 'FacebookGroupFTS', 'description' => 'Feed Them Social, Facebook Group Feed' );
    //WIDGET Name
    $this->WP_Widget('facebook_group_fts', 'FTS Facebook Group', $widget_ops);
}
//WIDGET Args
function widget($args, $instance) {
    extract($args, EXTR_SKIP);
    echo $before_widget;
    //WIDGET Database Checks
    $title = empty($instance['title']) ? ' ' : apply_filters('widget_facebook_group_fts_title', $instance['title']);
    $facebook_group_name = empty($instance['facebook_group_name']) ? ' ' : apply_filters('widget_facebook_group_fts_facebook_group_name', $instance['facebook_group_name']);
 	$number_of_posts = empty($instance['number_of_posts']) ? ' ' : apply_filters('widget_facebook_group_fts_number_of_posts', $instance['number_of_posts']);
 	$fb_group_title = empty($instance['fb_group_title']) ? ' ' : apply_filters('widget_facebook_group_fts_fb_group_title', $instance['fb_group_title']);
 	$fb_group_description = empty($instance['fb_group_description']) ? ' ' : apply_filters('widget_facebook_group_fts_fb_group_description', $instance['fb_group_description']);
 	$fb_group_word_count = empty($instance['fb_group_word_count']) ? ' ' : apply_filters('widget_facebook_group_fts_fb_group_word_count', $instance['fb_group_word_count']);
//HERE IS WHERE WE OUTPUT OUR TITLE AND SHORTCODE
if ( !empty( $title ) && (strlen($title) > 1 )) { echo $before_title . $title . $after_title; };
echo do_shortcode('[fts facebook group id='.$facebook_group_name.' posts='.$number_of_posts.' title='.$fb_group_title.' description='.$fb_group_description.' words='.$fb_group_word_count.' type=group]');
    echo $after_widget;
}
//WIDGET Save
function update($new_instance, $old_instance) {
    $instance = $old_instance;
    $instance['title'] = strip_tags($new_instance['title']);
    $instance['facebook_group_name'] = strip_tags($new_instance['facebook_group_name']);
	$instance['number_of_posts'] = strip_tags($new_instance['number_of_posts']);
    $instance['fb_group_title'] = strip_tags($new_instance['fb_group_title']);
    $instance['fb_group_description'] = strip_tags($new_instance['fb_group_description']);
    $instance['fb_group_word_count'] = strip_tags($new_instance['fb_group_word_count']);
	
    return $instance;
}
//WIDGET Admin Form
function form($instance) {
    //set your params, $instance and then any additional variables needed below
    $instance = wp_parse_args( (array) $instance, array( 'title' => '', 'facebook_group_fts_shortcode' => '', 'facebook_group_name' => '', 'fb_group_title' => '', 'fb_group_description' => '', 'fb_group_word_count' => '' ) );
    $title = strip_tags($instance['title']);
    $facebook_group_name = strip_tags($instance['facebook_group_name']);
    $number_of_posts = strip_tags($instance['number_of_posts']);
    $fb_group_title = strip_tags($instance['fb_group_title']);
    $fb_group_description = strip_tags($instance['fb_group_description']);
    $fb_group_word_count = strip_tags($instance['fb_group_word_count']);
?>
<div class="slickremix-widget-header-wrap">
<h2 class="fts-widget-header-h2"><?php _e('Feed Them Social', 'feed-them-premium'); ?></h2>
<h2 class="fts-widget-header-h3"><?php _e('FB Group Feed', 'feed-them-premium'); ?></h2>
</div>
<div class="fts-label-input-wrap">
  <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Your Title:', 'feed-them-premium'); ?></label>
  <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr__($title); ?>" />
</div>
<div class="fts-label-input-wrap">
  <label for="<?php echo $this->get_field_id('facebook_group_name'); ?>"><?php _e('Facebook Group ID (required)', 'feed-them-premium'); ?></label>
  <input required class="widefat" id="<?php echo $this->get_field_id('facebook_group_name'); ?>" name="<?php echo $this->get_field_name('facebook_group_name'); ?>" type="text" value="<?php echo esc_attr__($facebook_group_name); ?>" />
</div>
<div class="fts-label-input-wrap">
  <label for="<?php echo $this->get_field_id('number_of_posts'); ?>"><?php _e('Number of posts (required)', 'feed-them-premium'); ?></label>
  <input required class="widefat" id="<?php echo $this->get_field_id('number_of_posts'); ?>" name="<?php echo $this->get_field_name('number_of_posts'); ?>" type="text" value="<?php echo esc_attr__($number_of_posts); ?>" />
</div>
<div class="fts-label-input-wrap">
  <label for="<?php echo $this->get_field_id('fb_group_title'); ?>"><?php _e('Show group title', 'feed-them-premium'); ?></label>
  <select id="<?php echo $this->get_field_id('fb_group_title'); ?>" name="<?php echo $this->get_field_name('fb_group_title'); ?>" class="widefat" size="1">
    <option value="yes" <?php selected('yes', $instance['fb_group_title']); ?>><?php _e('yes', 'feed-them-premium'); ?></option>
    <option value="no" <?php selected('no', $instance['fb_group_title']); ?>><?php _e('no', 'feed-them-premium'); ?></option>
  </select>
</div>
<div class="fts-label-input-wrap">
  <label for="<?php echo $this->get_field_id('fb_group_description'); ?>"><?php _e('Show group description', 'feed-them-premium'); ?></label>
  <select id="<?php echo $this->get_field_id('fb_group_description'); ?>" name="<?php echo $this->get_field_name('fb_group_description'); ?>" class="widefat" size="1">
    <option value="yes" <?php selected('yes', $instance['fb_group_description']); ?>><?php _e('yes', 'feed-them-premium'); ?></option>
    <option value="no" <?php selected('no', $instance['fb_group_description']); ?>><?php _e('no', 'feed-them-premium'); ?></option>
  </select>
</div>
<div class="fts-label-input-wrap last">
  <label for="<?php echo $this->get_field_id('fb_group_word_count'); ?>"><?php _e('Number of words per post (required)', 'feed-them-premium'); ?></label>
  <input required class="widefat" id="<?php echo $this->get_field_id('fb_group_word_count'); ?>" name="<?php echo $this->get_field_name('fb_group_word_count'); ?>" type="text" value="<?php echo esc_attr__($fb_group_word_count); ?>" />
</div>
<?php
    }
}
 //CREATE Widget
 add_action( 'widgets_init', create_function('', 'register_widget("fts_facebook_group_widget");') );
 //Facebook Group FTS Widget End
 
 
 
 
 
 
 
 
 
 
 
 //Facebook Event FTS Widget
//CONSTRUCT Class
class fts_facebook_event_widget extends WP_Widget {
function fts_facebook_event_widget() {
    $widget_ops = array('classname' => 'FacebookEventFTS', 'description' => 'Feed Them Social, Facebook Event Feed' );
    //WIDGET Name
    $this->WP_Widget('facebook_event_fts', 'FTS Facebook Event', $widget_ops);
}
//WIDGET Args
function widget($args, $instance) {
    extract($args, EXTR_SKIP);
    echo $before_widget;
    //WIDGET Database Checks
    $title = empty($instance['title']) ? ' ' : apply_filters('widget_facebook_event_fts_title', $instance['title']);
    $facebook_event_name = empty($instance['facebook_event_name']) ? ' ' : apply_filters('widget_facebook_event_fts_facebook_event_name', $instance['facebook_event_name']);
 	$number_of_posts = empty($instance['number_of_posts']) ? ' ' : apply_filters('widget_facebook_event_fts_number_of_posts', $instance['number_of_posts']);
 	$fb_event_title = empty($instance['fb_event_title']) ? ' ' : apply_filters('widget_facebook_event_fts_fb_event_title', $instance['fb_event_title']);
 	$fb_event_description = empty($instance['fb_event_description']) ? ' ' : apply_filters('widget_facebook_event_fts_fb_event_description', $instance['fb_event_description']);
 	$fb_event_word_count = empty($instance['fb_event_word_count']) ? ' ' : apply_filters('widget_facebook_event_fts_fb_event_word_count', $instance['fb_event_word_count']);
//HERE IS WHERE WE OUTPUT OUR TITLE AND SHORTCODE
if ( !empty( $title ) && (strlen($title) > 1 )) { echo $before_title . $title . $after_title; };
echo do_shortcode('[fts facebook event id='.$facebook_event_name.' posts='.$number_of_posts.' title='.$fb_event_title.' description='.$fb_event_description.' words='.$fb_event_word_count.' type=event]');
    echo $after_widget;
}
//WIDGET Save
function update($new_instance, $old_instance) {
    $instance = $old_instance;
    $instance['title'] = strip_tags($new_instance['title']);
    $instance['facebook_event_name'] = strip_tags($new_instance['facebook_event_name']);
	$instance['number_of_posts'] = strip_tags($new_instance['number_of_posts']);
    $instance['fb_event_title'] = strip_tags($new_instance['fb_event_title']);
    $instance['fb_event_description'] = strip_tags($new_instance['fb_event_description']);
    $instance['fb_event_word_count'] = strip_tags($new_instance['fb_event_word_count']);
	
    return $instance;
}
//WIDGET Admin Form
function form($instance) {
    //set your params, $instance and then any additional variables needed below
    $instance = wp_parse_args( (array) $instance, array( 'title' => '', 'facebook_event_name' => '', 'number_of_posts' => '', 'fb_event_title' => '', 'fb_event_description' => '', 'fb_event_word_count' => '' ) );
    $title = strip_tags($instance['title']);
    $facebook_event_name = strip_tags($instance['facebook_event_name']);
    $number_of_posts = strip_tags($instance['number_of_posts']);
    $fb_event_title = strip_tags($instance['fb_event_title']);
    $fb_event_description = strip_tags($instance['fb_event_description']);
    $fb_event_word_count = strip_tags($instance['fb_event_word_count']);
?>
<div class="slickremix-widget-header-wrap">
<h2 class="fts-widget-header-h2"><?php _e('Feed Them Social', 'feed-them-premium'); ?></h2>
<h2 class="fts-widget-header-h3"><?php _e('FB Event Feed', 'feed-them-premium'); ?></h2>
</div>
<div class="fts-label-input-wrap">
  <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Your Title:', 'feed-them-premium'); ?></label>
  <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr__($title); ?>" />
</div>
<div class="fts-label-input-wrap">
  <label for="<?php echo $this->get_field_id('facebook_event_name'); ?>"><?php _e('Facebook Event ID (required)', 'feed-them-premium'); ?></label>
  <input required class="widefat" id="<?php echo $this->get_field_id('facebook_event_name'); ?>" name="<?php echo $this->get_field_name('facebook_event_name'); ?>" type="text" value="<?php echo esc_attr__($facebook_event_name); ?>" />
</div>
<div class="fts-label-input-wrap">
  <label for="<?php echo $this->get_field_id('number_of_posts'); ?>"><?php _e('Number of posts (required)', 'feed-them-premium'); ?></label>
  <input required class="widefat" id="<?php echo $this->get_field_id('number_of_posts'); ?>" name="<?php echo $this->get_field_name('number_of_posts'); ?>" type="text" value="<?php echo esc_attr__($number_of_posts); ?>" />
</div>
<div class="fts-label-input-wrap">
  <label for="<?php echo $this->get_field_id('fb_event_title'); ?>"><?php _e('Show event title', 'feed-them-premium'); ?></label>
  <select id="<?php echo $this->get_field_id('fb_event_title'); ?>" name="<?php echo $this->get_field_name('fb_event_title'); ?>" class="widefat" size="1">
    <option value="yes" <?php selected('yes', $instance['fb_event_title']); ?>><?php _e('yes', 'feed-them-premium'); ?></option>
    <option value="no" <?php selected('no', $instance['fb_event_title']); ?>><?php _e('no', 'feed-them-premium'); ?></option>
  </select>
</div>
<div class="fts-label-input-wrap">
  <label for="<?php echo $this->get_field_id('fb_event_description'); ?>"><?php _e('Show event description', 'feed-them-premium'); ?></label>
  <select id="<?php echo $this->get_field_id('fb_event_description'); ?>" name="<?php echo $this->get_field_name('fb_event_description'); ?>" class="widefat" size="1">
    <option value="yes" <?php selected('yes', $instance['fb_event_description']); ?>><?php _e('yes', 'feed-them-premium'); ?></option>
    <option value="no" <?php selected('no', $instance['fb_event_description']); ?>><?php _e('no', 'feed-them-premium'); ?></option>
  </select>
</div>
<div class="fts-label-input-wrap last">
  <label for="<?php echo $this->get_field_id('fb_event_word_count'); ?>"><?php _e('Number of words per post (required)', 'feed-them-premium'); ?></label>
  <input required class="widefat" id="<?php echo $this->get_field_id('fb_event_word_count'); ?>" name="<?php echo $this->get_field_name('fb_event_word_count'); ?>" type="text" value="<?php echo esc_attr__($fb_event_word_count); ?>" />
</div>
<?php
    }
}
 //CREATE Widget
 add_action( 'widgets_init', create_function('', 'register_widget("fts_facebook_event_widget");') );
 //Facebook Event FTS Widget End
 
 
 
 
 
 
 
 
 
 
 
//Twitter FTS Widget
//CONSTRUCT Class
class fts_twitter_widget extends WP_Widget {
function fts_twitter_widget() {
    $widget_ops = array('classname' => 'TwitterFTS', 'description' => 'Feed Them Social, Twitter Feed' );
    //WIDGET Name
    $this->WP_Widget('twitter_fts', 'FTS Twitter', $widget_ops);
}
//WIDGET Args
function widget($args, $instance) {
    extract($args, EXTR_SKIP);
    echo $before_widget;
    //WIDGET Database Checks
    $title = empty($instance['title']) ? ' ' : apply_filters('widget_twitter_fts_title', $instance['title']);
    $twitter_name = empty($instance['twitter_name']) ? ' ' : apply_filters('widget_twitter_fts_twitter_name', $instance['twitter_name']);
 	$number_of_tweets = empty($instance['number_of_tweets']) ? ' ' : apply_filters('widget_twitter_fts_number_of_tweets', $instance['number_of_tweets']);
//HERE IS WHERE WE OUTPUT OUR TITLE AND SHORTCODE
if ( !empty( $title ) && (strlen($title) > 1 )) { echo $before_title . $title . $after_title; };
echo do_shortcode('[fts twitter twitter_name='.$twitter_name.' tweets_count='.$number_of_tweets.']');
    echo $after_widget;
}
//WIDGET Save
function update($new_instance, $old_instance) {
    $instance = $old_instance;
    $instance['title'] = strip_tags($new_instance['title']);
    $instance['twitter_name'] = strip_tags($new_instance['twitter_name']);
	$instance['number_of_tweets'] = strip_tags($new_instance['number_of_tweets']);
    return $instance;
}
//WIDGET Admin Form
function form($instance) {
    //set your params, $instance and then any additional variables needed below
    $instance = wp_parse_args( (array) $instance, array( 'title' => '', 'twitter_fts_shortcode' => '', 'twitter_name' => '' ) );
    $title = strip_tags($instance['title']);
    $twitter_name = strip_tags($instance['twitter_name']);
    $number_of_tweets = strip_tags($instance['number_of_tweets']);
?>
<div class="slickremix-widget-header-wrap">
<h2 class="fts-widget-header-h2"><?php _e('Feed Them Social', 'feed-them-premium'); ?></h2>
<h2 class="fts-widget-header-h3"><?php _e('Twitter Feed', 'feed-them-premium'); ?></h2>
</div>
<div class="fts-label-input-wrap">
  <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Your Title:', 'feed-them-premium'); ?></label>
  <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr__($title); ?>" />
</div>
<div class="fts-label-input-wrap">
  <label for="<?php echo $this->get_field_id('twitter_name'); ?>"><?php _e('Twitter Name (required)', 'feed-them-premium'); ?></label>
  <input required="required" class="widefat" id="<?php echo $this->get_field_id('twitter_name'); ?>" name="<?php echo $this->get_field_name('twitter_name'); ?>" type="text" value="<?php echo esc_attr__($twitter_name); ?>" />
</div>
<div class="fts-label-input-wrap">
  <label for="<?php echo $this->get_field_id('number_of_tweets'); ?>"><?php _e('Number Tweets (required)', 'feed-them-premium'); ?></label>
  <input required="required" class="widefat" id="<?php echo $this->get_field_id('number_of_tweets'); ?>" name="<?php echo $this->get_field_name('number_of_tweets'); ?>" type="text" value="<?php echo esc_attr__($number_of_tweets); ?>" />
</div>
<?php
    }
}
 //CREATE Widget
 add_action( 'widgets_init', create_function('', 'register_widget("fts_twitter_widget");') );
 //Twitter FTS Widget End
 
 
 
 
 
 
 
 
 
 
 
 
 
//Instagram FTS Widget
//CONSTRUCT Class
class fts_instagram_widget extends WP_Widget {
function fts_instagram_widget() {
    $widget_ops = array('classname' => 'InstagramFTS', 'description' => 'Feed Them Social, Instagram Feed' );
    //WIDGET Name
    $this->WP_Widget('instagram_fts', ' FTS Instagram', $widget_ops);
}

//WIDGET Args
function widget($args, $instance) {
    extract($args, EXTR_SKIP);
    echo $before_widget;
    //WIDGET Database Checks
    $title = empty($instance['title']) ? ' ' : apply_filters('widget_instagram_fts_title', $instance['title']);
    $instagram_name = empty($instance['instagram_name']) ? ' ' : apply_filters('widget_instagram_fts_instagram_name', $instance['instagram_name']);
 	$instagram_id = empty($instance['instagram_id']) ? ' ' : apply_filters('widget_instagram_fts_instagram_id', $instance['instagram_id']);
 	$number_of_pics = empty($instance['number_of_pics']) ? ' ' : apply_filters('widget_instagram_fts_number_of_pics', $instance['number_of_pics']);
//HERE IS WHERE WE OUTPUT OUR TITLE AND SHORTCODE
if ( !empty( $title ) && (strlen($title) > 1 )) { echo $before_title . $title . $after_title; };
echo do_shortcode('[fts instagram instagram_id='.$instagram_id.' pics_count='.$number_of_pics.']');
    echo $after_widget;
}
//WIDGET Save
function update($new_instance, $old_instance) {
    $instance = $old_instance;
    $instance['title'] = strip_tags($new_instance['title']);
    $instance['instagram_name'] = strip_tags($new_instance['instagram_name']);
	$instance['instagram_id'] = strip_tags($new_instance['instagram_id']);
	$instance['number_of_pics'] = strip_tags($new_instance['number_of_pics']);
    return $instance;
}
//WIDGET Admin Form
function form($instance) {
    //set your params, $instance and then any additional variables needed below
    $instance = wp_parse_args( (array) $instance, array( 'title' => '', 'instagram_fts_shortcode' => '', 'instagram_name' => '' ) );
    $title = strip_tags($instance['title']);
    $instagram_name = strip_tags($instance['instagram_name']);
    $instagram_id = strip_tags($instance['instagram_id']);
    $number_of_pics = strip_tags($instance['number_of_pics']);
?>
<script type="text/javascript">
//START convert Instagram name to id. This widget method could use some improvements.
jQuery("input.feed-them-social-admin-submit-btn").click(function() {	
			jQuery.getJSON("https://api.instagram.com/v1/users/search?q=<?php echo $instagram_name; ?>&access_token=267791236.f78cc02.bea846f3144a40acbf0e56b002c112f8&callback=?",
			  {
				format: "json"
			  },
			  function(data) {
					console.log(data);
					var final_instagram_us_id = data.data[0].id;
					jQuery('input.instagram-id-class').val(final_instagram_us_id);
   			 })
})
</script>
<div class="slickremix-widget-header-wrap">
<h2 class="fts-widget-header-h2"><?php _e('Feed Them Social', 'feed-them-premium'); ?></h2>
<h2 class="fts-widget-header-h3"><?php _e('Instagram Feed', 'feed-them-premium'); ?></h2>
</div>
<div class="fts-label-input-wrap">
  <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Your Title:', 'feed-them-premium'); ?></label>
  <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr__($title); ?>" />
</div>
<div class="fts-label-input-wrap">
  <label for="<?php echo $this->get_field_id('instagram_name'); ?>"><?php _e('Instagram Name (required)', 'feed-them-premium'); ?></label>
  <input required="required" class="widefat instagram-username" id="<?php echo $this->get_field_id('instagram_name'); ?>" name="<?php echo $this->get_field_name('instagram_name'); ?>" type="text" value="<?php echo esc_attr__($instagram_name); ?>" />
</div>
<div class="fts-label-input-wrap">
  <label for="<?php echo $this->get_field_id('number_of_pics'); ?>"><?php _e('Number of Photos (required)', 'feed-them-premium'); ?></label>
  <input required="required" class="widefat number-of-pics" id="<?php echo $this->get_field_id('number_of_pics'); ?>" name="<?php echo $this->get_field_name('number_of_pics'); ?>" type="text" value="<?php echo esc_attr__($number_of_pics); ?>" />

</div>
<div class="fts-label-input-wrap">
<label><?php _e('Click button Twice to Convert', 'feed-them-premium'); ?></label>
<input type="submit" name="savewidget" class="feed-them-social-admin-submit-btn button button-primary widget-control-save left" value="<?php _e('Convert Instagram Username', 'feed-them-premium'); ?>">
</div>
<div class="fts-label-input-wrap last" style="display:noneee;">
  <label for="<?php echo $this->get_field_id('instagram_id'); ?>"><?php _e('Instagram ID (required)', 'feed-them-premium'); ?></label>
   <input class="widefat instagram-id-class" id="<?php echo $this->get_field_id('instagram_id'); ?>" name="<?php echo $this->get_field_name('instagram_id'); ?>" type="text" value="<?php echo esc_attr__($instagram_id); ?>" />
</div>


<?php
    }
}
 //CREATE Widget
 add_action( 'widgets_init', create_function('', 'register_widget("fts_instagram_widget");') );
 //Instagram FTS Widget End
 
 
 
 
 
 
 
//Pinterest FTS Widget
//CONSTRUCT Class
class fts_pinterest_widget extends WP_Widget {
function fts_pinterest_widget() {
    $widget_ops = array('classname' => 'PinterestFTS', 'description' => 'Feed Them Social, Pinterest Feed' );
    //WIDGET Name
    $this->WP_Widget('pinterest_fts', 'FTS Pinterest', $widget_ops);
}
//WIDGET Args
function widget($args, $instance) {
    extract($args, EXTR_SKIP);
    echo $before_widget;
    //WIDGET Database Checks
    $title = empty($instance['title']) ? ' ' : apply_filters('widget_pinterest_fts_title', $instance['title']);
    $pinterest_name = empty($instance['pinterest_name']) ? ' ' : apply_filters('widget_pinterest_fts_pinterest_name', $instance['pinterest_name']);
 	$boards_count = empty($instance['boards_count']) ? ' ' : apply_filters('widget_pinterest_fts_boards_count', $instance['boards_count']);
//HERE IS WHERE WE OUTPUT OUR TITLE AND SHORTCODE
if ( !empty( $title ) && (strlen($title) > 1 )) { echo $before_title . $title . $after_title; };
echo do_shortcode('[fts pinterest pinterest_name='.$pinterest_name.' boards_count='.$boards_count.']');
    echo $after_widget;
}
//WIDGET Save
function update($new_instance, $old_instance) {
    $instance = $old_instance;
    $instance['title'] = strip_tags($new_instance['title']);
    $instance['pinterest_name'] = strip_tags($new_instance['pinterest_name']);
	$instance['boards_count'] = strip_tags($new_instance['boards_count']);
    return $instance;
}
//WIDGET Admin Form
function form($instance) {
    //set your params, $instance and then any additional variables needed below
    $instance = wp_parse_args( (array) $instance, array( 'title' => '', 'pinterest_fts_shortcode' => '', 'pinterest_name' => '' ) );
    $title = strip_tags($instance['title']);
    $pinterest_name = strip_tags($instance['pinterest_name']);
    $boards_count = strip_tags($instance['boards_count']);
?>
<div class="slickremix-widget-header-wrap">
<h2 class="fts-widget-header-h2"><?php _e('Feed Them Social', 'feed-them-premium'); ?></h2>
<h2 class="fts-widget-header-h3"><?php _e('Pinterest Feed', 'feed-them-premium'); ?></h2>
</div>
<div class="fts-label-input-wrap">
  <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Your Title:', 'feed-them-premium'); ?></label>
  <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr__($title); ?>" />
</div>
<div class="fts-label-input-wrap">
  <label for="<?php echo $this->get_field_id('pinterest_name'); ?>"><?php _e('Pinterest Name (required)', 'feed-them-premium'); ?></label>
  <input required="required" class="widefat" id="<?php echo $this->get_field_id('pinterest_name'); ?>" name="<?php echo $this->get_field_name('pinterest_name'); ?>" type="text" value="<?php echo esc_attr__($pinterest_name); ?>" />
</div>
<div class="fts-label-input-wrap">
  <label for="<?php echo $this->get_field_id('boards_count'); ?>"><?php _e('Number Boards (required)', 'feed-them-premium'); ?></label>
  <input required="required" class="widefat" id="<?php echo $this->get_field_id('boards_count'); ?>" name="<?php echo $this->get_field_name('boards_count'); ?>" type="text" value="<?php echo esc_attr__($boards_count); ?>" />
</div>
<?php
    }
}
 //CREATE Widget
 add_action( 'widgets_init', create_function('', 'register_widget("fts_pinterest_widget");') );
 //Pinterest FTS Widget End