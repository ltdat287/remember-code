<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" <?php language_attributes(); ?>>
<head profile="http://gmpg.org/xfn/11">
<meta http-equiv="Content-Type" content="<?php bloginfo('html_type'); ?>; charset=<?php bloginfo('charset'); ?>" />
<meta http-equiv="X-UA-Compatible" content="IE=EmulateIE7" />
<title><?php if (is_home () ) { bloginfo('name'); } elseif ( is_category() ) { single_cat_title(); if(get_bloginfo('name') != "") echo ' - ' ; bloginfo('name'); }
 elseif (is_single() ) { single_post_title(); }
 elseif (is_page() ) { bloginfo('name'); if(get_bloginfo('name') != "") echo ': '; single_post_title(); }
 else { wp_title('',true); } ?></title>
<script type="text/javascript" src="<?php bloginfo('template_url'); ?>/script.js"></script>
<link rel="stylesheet" href="<?php bloginfo('stylesheet_url'); ?>" type="text/css" media="screen" />
<!--[if IE 6]><link rel="stylesheet" href="<?php bloginfo('template_url'); ?>/style.ie6.css" type="text/css" media="screen" /><![endif]-->
<!--[if IE 7]><link rel="stylesheet" href="<?php bloginfo('template_url'); ?>/style.ie7.css" type="text/css" media="screen" /><![endif]-->
<link rel="alternate" type="application/rss+xml" title="<?php printf(__('%s RSS Feed', 'kubrick'), get_bloginfo('name')); ?>" href="<?php bloginfo('rss2_url'); ?>" />
<link rel="alternate" type="application/atom+xml" title="<?php printf(__('%s Atom Feed', 'kubrick'), get_bloginfo('name')); ?>" href="<?php bloginfo('atom_url'); ?>" /> 
<link rel="pingback" href="<?php bloginfo('pingback_url'); ?>" />
<?php wp_head(); ?>
</head>
<body>
<div id="page-background-simple-gradient">
</div>
<div id="page-background-glare">
    <div id="page-background-glare-image"></div>
</div>
<div id="main">
<div class="Sheet">
    <div class="Sheet-tl"></div>
    <div class="Sheet-tr"></div>
    <div class="Sheet-bl"></div>
    <div class="Sheet-br"></div>
    <div class="Sheet-tc"></div>
    <div class="Sheet-bc"></div>
    <div class="Sheet-cl"></div>
    <div class="Sheet-cr"></div>
    <div class="Sheet-cc"></div>
    <div class="Sheet-body">
<div class="Header">
    <div class="Header-png"></div>
    <div class="Header-jpeg"></div>
<div class="Logo">
    <h1 id="name-text" class="Logo-name">
        <a href="<?php echo get_option('home'); ?>/"><?php bloginfo('name'); ?></a></h1>
    <div id="slogan-text" class="Logo-text">
        <?php bloginfo('description'); ?></div>
</div>

</div>
<div class="nav">
	<div class="l"></div>
	<div class="r"></div>
	<div class="nav-center">
	<ul class="menu">
		<?php art_menu_items(); ?>
	</ul>
	</div>
</div>
