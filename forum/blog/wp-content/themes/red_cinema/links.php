<?php get_header(); ?>
<div class="contentLayout">
<?php include (TEMPLATEPATH . '/sidebar1.php'); ?><div class="content">

<div class="Block">
    <div class="Block-body">

<div class="BlockHeader">
    <div class="l"></div>
    <div class="r"></div>
    <div class="header-tag-icon">
        <div class="t"><?php _e('Links:', 'kubrick'); ?></div>
    </div>
</div>
<div class="BlockContent">
    <div class="BlockContent-tl"></div>
    <div class="BlockContent-tr"></div>
    <div class="BlockContent-bl"></div>
    <div class="BlockContent-br"></div>
    <div class="BlockContent-tc"></div>
    <div class="BlockContent-bc"></div>
    <div class="BlockContent-cl"></div>
    <div class="BlockContent-cr"></div>
    <div class="BlockContent-cc"></div>
    <div class="BlockContent-body">

<ul>
<?php get_links_list(); ?>
</ul>

		<div class="cleared"></div>
    </div>
</div>


		<div class="cleared"></div>
    </div>
</div>


</div>
<?php include (TEMPLATEPATH . '/sidebar2.php'); ?>
</div>
<div class="cleared"></div>

<?php get_footer(); ?>