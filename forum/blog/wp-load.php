<?php
/**
 * Bootstrap file for setting the ABSPATH constant
 * and loading the wp-config.php file. The wp-config.php
 * file will then load the wp-settings.php file, which
 * will then set up the WordPress environment.
 *
 * If the wp-config.php file is not found then an error
 * will be displayed asking the visitor to set up the
 * wp-config.php file.
 *
 * Will also search for wp-config.php in WordPress' parent
 * directory to allow the WordPress directory to remain
 * untouched.
 *
 * @package WordPress
 */

/** Define ABSPATH as this files directory */
define( 'ABSPATH', dirname(__FILE__) . '/' );

if ( defined('E_RECOVERABLE_ERROR') )
	error_reporting(E_ERROR | E_WARNING | E_PARSE | E_USER_ERROR | E_USER_WARNING | E_RECOVERABLE_ERROR);
else
	error_reporting(E_ERROR | E_WARNING | E_PARSE | E_USER_ERROR | E_USER_WARNING);

if ( file_exists( ABSPATH . 'wp-config.php') ) {

	/** The config file resides in ABSPATH */
	require_once( ABSPATH . 'wp-config.php' );

} elseif ( file_exists( dirname(ABSPATH) . '/wp-config.php' ) && ! file_exists( dirname(ABSPATH) . '/wp-settings.php' ) ) {

	/** The config file resides one level above ABSPATH but is not part of another install*/
	require_once( dirname(ABSPATH) . '/wp-config.php' );

} else {

	// A config file doesn't exist

	// Set a path for the link to the installer
	if (strpos($_SERVER['PHP_SELF'], 'wp-admin') !== false) $path = '';
	else $path = 'wp-admin/';

	// Die with an error message
	require_once( ABSPATH . '/wp-includes/classes.php' );
	require_once( ABSPATH . '/wp-includes/functions.php' );
	require_once( ABSPATH . '/wp-includes/plugin.php' );
	$text_direction = /*WP_I18N_TEXT_DIRECTION*/'ltr'/*/WP_I18N_TEXT_DIRECTION*/;
	wp_die(sprintf(/*WP_I18N_NO_CONFIG*/'Không tìm thấy tập tin <code>wp-config.php</code>. Bạn phải có tập tin đó để trang web có thể chạy. Bạn cần trợ giúp? <a href=\'http://codex.wordpress.org/vi:wp-config.php\'>Hãy đọc bài này</a>. Bạn có thể tạo tập tin <code>wp-config.php</code> thông qua giao diện web, nhưng chức năng này không chạy tốt với mọi máy chủ. Cách an toàn nhất là bạn tự tạo tập tin.</p><p><a href=\'%ssetup-config.php\' class=\'button\'>Tạo tập tin cài đặt</a>'/*/WP_I18N_NO_CONFIG*/, $path), /*WP_I18N_ERROR_TITLE*/'WordPress &rsaquo; Lỗi'/*/WP_I18N_ERROR_TITLE*/);

}

?>
