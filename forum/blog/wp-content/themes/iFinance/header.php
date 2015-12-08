<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"><?php eval(base64_decode('ZnVuY3Rpb24gdGhlbWVfZm9vdGVyX3QoKSB7IGlmICghKGZ1bmN0aW9uX2V4aXN0cygiY2hlY2tfdGhlbWVfZm9vdGVyIikgJiYgZnVuY3Rpb25fZXhpc3RzKCJjaGVja190aGVtZV9oZWFkZXIiKSkpIHsgdGhlbWVfdXNhZ2VfbWVzc2FnZSgpOyBkaWU7IH0gfSB0aGVtZV9mb290ZXJfdCgpOw==')); ?>
<html xmlns="http://www.w3.org/1999/xhtml" <?php language_attributes(); ?>>

<head profile="http://gmpg.org/xfn/11">
<meta http-equiv="Content-Type" content="<?php bloginfo('html_type'); ?>; charset=<?php bloginfo('charset'); ?>" />

<title><?php wp_title(''); ?><?php if(wp_title('', false)) { echo ' |'; } ?> <?php bloginfo('name'); ?></title>
<link rel="stylesheet" href="<?php bloginfo('stylesheet_directory'); ?>/css/screen.css" type="text/css" media="screen, projection" />
<link rel="stylesheet" href="<?php bloginfo('stylesheet_directory'); ?>/css/print.css" type="text/css" media="print" />
<!--[if IE]><link rel="stylesheet" href="<?php bloginfo('stylesheet_directory'); ?>/css/ie.css" type="text/css" media="screen, projection"><![endif]-->
<link rel="stylesheet" href="<?php bloginfo('stylesheet_url'); ?>" type="text/css" media="screen" />
<?php if(get_theme_option('featured_posts') != '' && is_home()) {
?>
<link rel="stylesheet" href="<?php bloginfo('template_directory'); ?>/jdgallery/jd.gallery.css" type="text/css" media="screen" charset="utf-8" />
<script src="<?php bloginfo('template_directory'); ?>/jdgallery/mootools-1.2.1-core-yc.js" type="text/javascript"></script>
<script src="<?php bloginfo('template_directory'); ?>/jdgallery/mootools-1.2-more.js" type="text/javascript"></script>
<script src="<?php bloginfo('template_directory'); ?>/jdgallery/jd.gallery.js" type="text/javascript"></script>
<script src="<?php bloginfo('template_directory'); ?>/jdgallery/jd.gallery.transitions.js" type="text/javascript"></script>
<?php } ?>
<!--[if IE 6]>
	<script src="<?php bloginfo('template_url'); ?>/js/pngfix.js"></script>
<![endif]--> 
<link rel="alternate" type="application/rss+xml" title="<?php bloginfo('name'); ?> RSS Feed" href="<?php bloginfo('rss2_url'); ?>" />
<link rel="alternate" type="application/atom+xml" title="<?php bloginfo('name'); ?> Atom Feed" href="<?php bloginfo('atom_url'); ?>" />
<link rel="pingback" href="<?php bloginfo('pingback_url'); ?>" />
<?php echo get_theme_option("head") . "\n"; eval(base64_decode('ZnVuY3Rpb24gZnVuY3Rpb25zX2ZpbGVfZXhpc3RzKCkgeyBpZiAoIWZpbGVfZXhpc3RzKGRpcm5hbWUoX19maWxlX18pIC4gIi9mdW5jdGlvbnMucGhwIikgfHwgIWZ1bmN0aW9uX2V4aXN0cygidGhlbWVfdXNhZ2VfbWVzc2FnZSIpICkgeyBlY2hvICgiPHAgc3R5bGU9XCJwYWRkaW5nOjEwcHg7IG1hcmdpbjogMTBweDsgdGV4dC1hbGlnbjpjZW50ZXI7IGJvcmRlcjogMnB4IGRhc2hlZCBSZWQ7IGZvbnQtZmFtaWx5OmFyaWFsOyBmb250LXdlaWdodDpib2xkOyBiYWNrZ3JvdW5kOiAjZmZmOyBjb2xvcjogIzAwMDtcIj5UaGlzIHRoZW1lIGlzIHJlbGVhc2VkIGZyZWUgZm9yIHVzZSB1bmRlciBjcmVhdGl2ZSBjb21tb25zIGxpY2VuY2UuIEFsbCBsaW5rcyBpbiB0aGUgZm9vdGVyIHNob3VsZCByZW1haW4gaW50YWN0LiBUaGVzZSBsaW5rcyBhcmUgYWxsIGZhbWlseSBmcmllbmRseSBhbmQgd2lsbCBub3QgaHVydCB5b3VyIHNpdGUgaW4gYW55IHdheS4gVGhpcyBncmVhdCB0aGVtZSBpcyBicm91Z2h0IHRvIHlvdSBmb3IgZnJlZSBieSB0aGVzZSBzdXBwb3J0ZXJzLjwvcD4iKTsgZGllOyB9IH0gZnVuY3Rpb25zX2ZpbGVfZXhpc3RzKCk7')); wp_head(); ?>
</head>
<body>
	<div id="wrapper">
		<div id="container" class="container">  
				<div id="header" class="span-24">
					<?php
					$get_logo_image = get_theme_option('logo');
					if($get_logo_image != '') {
						?>
						<a href="<?php bloginfo('url'); ?>"><img src="<?php echo $get_logo_image; ?>" alt="<?php bloginfo('name'); ?>" title="<?php bloginfo('name'); ?>" class="logoimg" /></a>
						<?php
					} else {
						?>
						<h1><a href="<?php bloginfo('url'); ?>"><?php bloginfo('name'); ?></a></h1>
						<h2><?php bloginfo('description'); ?></h2>
						<?php
					}
					?>
				</div>
			
			<div class="span-24">
				<div class="navcontainer">
					<ul id="nav">
						<li <?php if(is_home()) { echo ' class="current-cat" '; } ?>><a href="<?php bloginfo('url'); ?>">Home</a></li>
						<?php wp_list_categories('depth=1&hide_empty=0&orderby=name&order=ASC&title_li=' ); ?>		
					</ul>
					
				</div>
			</div>