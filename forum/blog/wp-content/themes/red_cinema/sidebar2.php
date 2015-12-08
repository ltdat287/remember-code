<div class="sidebar2">      
<?php if (!art_sidebar(2)): ?>
<div class="Block">
    <div class="Block-body">
<div class="BlockHeader">
    <div class="l"></div>
    <div class="r"></div>
    <div class="header-tag-icon">
        <div class="t"><?php _e('Categories', 'kubrick'); ?></div>
    </div>
</div><div class="BlockContent">
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
  <?php wp_list_categories('show_count=1&title_li='); ?>
</ul>
		<div class="cleared"></div>
    </div>
</div>

		<div class="cleared"></div>
    </div>
</div>
<div class="Block">
    <div class="Block-body">
<div class="BlockHeader">
    <div class="l"></div>
    <div class="r"></div>
    <div class="header-tag-icon">
        <div class="t"><?php _e('Bookmarks', 'kubrick'); ?></div>
    </div>
</div><div class="BlockContent">
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
      <?php wp_list_bookmarks('title_li=&categorize=0'); ?>
      </ul>
		<div class="cleared"></div>
    </div>
</div>

		<div class="cleared"></div>
    </div>
</div>

<?php endif ?>
</div>
