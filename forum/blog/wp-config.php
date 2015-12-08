<?php
/**
 * Cấu hình cơ bản của WordPress.
 *
 * Bạn có thể tham khảo hướng dẫn cài đặt WordPress tại đây:
 * http://wordpressvn.net/huong-dan/cai-dat/
 *
 * Tập tin này có các cấu hình sau: cơ sở dữ liệu MySQL, Tiền tố của bảng,
 * Khóa mật, Ngôn ngữ, và đường dẫn tuyệt đối ABSPATH. Bạn có thể tìm thêm thông
 * tin tại trang {@link http://codex.wordpress.org/Editing_wp-config.php Editing
 * wp-config.php} Codex. Bạn có thể tìm thông tin về MySQL của bạn từ nhà cung
 * cấp dịch vụ web.
 *
 * Tập tin này được sử dụng để tạo ra tập tin cấu hình wp-config.php trong quá
 * trình cài đặt. Bạn không bắt buộc phải sử dụng trang web để tạo ra tập tin
 * cấu hình, bạn có thể sao chép tập tin này sang tập tin "wp-config.php" và
 * điền các giá trị vào.
 *
 * @package WordPress
 */

// ** Thông tin về cớ sở dữ liệu MySQL ** //
/** Tên cơ sỡ dữ liệu */
define('DB_NAME', 'namnh_svnd');

/** Tài khoản để kết nối với cơ sở dữ liệu MySQL */
define('DB_USER', 'namnh');

/** Mật khẩu của tài khoản kết nối với cơ sở dữ liệu MySQL */
define('DB_PASSWORD', 'cuckydongian');

/** Máy chủ cơ sở dữ liệu MySQL */
define('DB_HOST', 'localhost');

/** Charset sử dụng khi tạo bảng trong cơ sở dữ liệu. */
define('DB_CHARSET', 'utf8');

/** Kiểu Collate của cơ sở dữ liệu. Nếu bạn không chắc chắn, đừng thay đổi. */
define('DB_COLLATE', '');

/**#@+
 * Khóa xác thực.
 *
 * Thay cụm từ 'put your unique phrase here' bằng các chuỗi kí tự thật dài và khó đoán,
 * các khóa này được sử dụng để tăng cường độ bảo mật cho WordPress
 * Bạn có thể tự tạo ra khóa tại trang {@link https://api.wordpress.org/secret-key/1.1/ Dịch vụ tạo khóa bí mật của WordPress.org}
 * Thay đổi các khóa này sẽ dẫn đến việc các thành viên phải đăng nhập lại.
 *
 * @since 2.6.0
 */
define('AUTH_KEY', 'put your unique phrase here');
define('SECURE_AUTH_KEY', 'put your unique phrase here');
define('LOGGED_IN_KEY', 'put your unique phrase here');
define('NONCE_KEY', 'put your unique phrase here');
/**#@-*/

/**
 * Tiền tố của bảng của WordPress này.
 *
 * Bạn có thể có nhiều WordPress trong cùng một cơ sở dữ liệu nếu mỗi bản
 * WordPress có một tiền tố riêng. Chỉ sử dụng số, chữ, và gạch chân!
 */
$table_prefix  = 'WPRESS';

/**
 * Ngôn ngữ của WordPress, mặc định là tiếng Anh.
 *
 * Thay đổi cài đặt này để sử dụng WordPress với ngôn ngữ bạn mong muốn.
 * Giá trị này là tên của tập tin ngôn ngữ MO nằm trong wp-content/languages.
 * Ví dụ, để sử dụng tiếng Việt, bạn sao chép tập tin vi.mo vào wp-content/languages
 * và điền vào dưới đây 'vi'.
 */
define ('WPLANG', 'vi');

/* Đó là tất cả những gì bạn cần điền! Chúc bạn blog vui vẻ. */

/** Đường dẫn tuyệt đối tới thư mục WordPress. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Cài đặt biến và tập tin cần thiết cho WordPress. */
require_once(ABSPATH . 'wp-settings.php');
