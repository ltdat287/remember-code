<?php

/************************************************************************************
* vBSEO 3.5.0 RC2 for vBulletin v3.x. & v4.x by Crawlability, Inc.                  *
*                                                                                   *
* Copyright � 2010, Crawlability, Inc. All rights reserved.                    *
* You may not redistribute this file or its derivatives without written permission. *
*                                                                                   *
* Sales Email: sales@crawlability.com                                               *
*                                                                                   *
*----------------------------vBSEO IS NOT FREE SOFTWARE-----------------------------*
* http://www.crawlability.com/vbseo/license/                                        *
************************************************************************************/

function vbseo_process_template($type, $pdata = array())
{
if(VBSEO_VB4)
return vbseo_process_template_vb4($type, $pdata);
else
return vbseo_process_template_vb3($type, $pdata);
}
function vbseo_process_template_vb4($type, $pdata = array())
{
global $vbulletin, $vbphrase;
switch($type)
{
case 'forumrules':
$pingrules = 
"\n<li>' . vB_Template_Runtime::parsePhrase('vbseo_trackback_is_x', 'misc.'.VBSEO_VB_EXT.'?do=linkbacks#trackbacks', vB_Template_Runtime::parsePhrase(iif(VBSEO_EXT_TRACKBACK, 'on', 'off'))) .'</li>"
."\n<li>' . vB_Template_Runtime::parsePhrase('vbseo_pingback_is_x', 'misc.'.VBSEO_VB_EXT.'?do=linkbacks#pingbacks', vB_Template_Runtime::parsePhrase(iif(VBSEO_EXT_PINGBACK, 'on', 'off'))) .'</li>"
."\n<li>' . vB_Template_Runtime::parsePhrase('vbseo_refback_is_x', 'misc.'.VBSEO_VB_EXT.'?do=linkbacks#refbacks', vB_Template_Runtime::parsePhrase(iif(VBSEO_EXT_REFBACK, 'on', 'off'))) .'</li>"
;
vbseo_modify_template('forumrules', '#(html_code_is_x.*?</li>)#s', '$1' . $pingrules, 0, '<!--LINKBACK_POSTRULES-->');
break;
case 'postbit_bookmarks':
if(($search_for = $pdata['search_for']) == 'editlink')
$search_for = '\'; if ($post[\'editlink\'])';
$pdata['abm'] = str_replace('$post[postid]', '\'.$post[postid].\'', $pdata['abm']);
vbseo_modify_template( $pdata['tpl'], $search_for,
'<div style="float:\'.vB_Template_Runtime::fetchStylevar("left").\'">' . $pdata['abm'] . "</div>\n" . $search_for
);
break;
case 'postbit_linkback':
$tplpostbit = vbseo_get_postbit_tpl();
$pingtpl = '".($post[\'linkbacksno\']?"<a href=\"' . (($_POST['ajax'] || (THIS_SCRIPT != 'showthread'))?'showthread.' . VBSEO_VB_EXT . '?p=$post[postid]':"") . '#linkbacks\"><img class=\\"inlineimg\\" src=\\"'.vBSEO_Storage::path('fimages').'post_linkback.gif\\" alt=\\"".construct_phrase("$vbphrase[vbseo_no_links_to_this_post]",$post[linkbacksno])."\" border=\\"0\\" /></a> ":"")."';
$pingtpl2 = '<a href=\"' . (($_POST['ajax'] || (THIS_SCRIPT != 'showthread'))?'showthread.' . VBSEO_VB_EXT . '?p=$post[postid]':'#post$post[postid]') . '\" title=\"".$vbphrase[\'vbseo_link_to_this_post\']."\">".$vbphrase[\'vbseo_permalink\']."</a>';
if (VBSEO_POSTBIT_PINGBACK == 1)
$pingtpl .= $pingtpl2;
if (vbseo_tpl_search($tplpostbit, '<!--PERMALINK_INFO-->'))
vbseo_modify_template($tplpostbit, '<!--PERMALINK_INFO-->', $pingtpl);
else
{
if (VBSEO_POSTBIT_PINGBACK == 1)
vbseo_modify_template($tplpostbit, '#(\$show\[\'messageicon\'\] OR \$post\[\'title\'\])(.*?)(<div.*?</div>)#s',
'$1 OR 1$2<table cellspacing=\"0\" cellpadding=\"0\" width=\"100%\" border=\"0\">
<tr><td>$3</td>
<td><div class=\"smallfont\" style=\"float:right\">' . $pingtpl . '</div></td>
</tr></table>'
);
else
vbseo_modify_template($tplpostbit, '#("\.\(\(\$show\[\'postcount\'\])#s',
$pingtpl . '$1'
);
if (VBSEO_POSTBIT_PINGBACK == 3)
vbseo_modify_template($tplpostbit, '#("\.\(\(\$show\[\'postcount\'\].*?</a>)#s',
'$1 (<b>' . $pingtpl2 . '</b>)'
);
}
break;
case 'linkback_menu':
global $vbseo_bookmarks, $show;
$show['vbseo_bookmarks'] = $vbseo_bookmarks;
$_q = vbseo_fetch_tpl('vbseo_linkbackmenu_entry');
vbseo_modify_template('SHOWTHREAD', '#(<li[^>]*?threadtools)#i', $_q.'$1', 0, '<!--LINKBACK_MENU-->');
break;
case 'misc_linkbacks':
$navbits = construct_navbits(array('faq.' . VBSEO_VB_EXT . $vbulletin->session->vars['sessionurl_q'] => $vbphrase['faq'],
'' => $vbphrase['vbseo_linkbacks']
));
$navbar = render_navbar_template($navbits);
$templater = vB_Template::create('vbseo_help_linkback');
$templater->register_page_templates();
$templater->register('navbar', $navbar);
print_output($templater->render());
exit;
break;	
case 'linkbacks_list':
if (!vbseo_tpl_search('SHOWTHREAD', '[vbseolinkbacks]'))
{
$search_for = $pdata['showactusers'] ? '; if ($show[\'activeusers\']' : ' . $similarthreads';
if (!vbseo_tpl_search('SHOWTHREAD', $search_for))
$search_for = '<!-- currently active users -->';
vbseo_modify_template('SHOWTHREAD', $search_for, ".\$show[vbseolinkbacks]$search_for");
}
break;
case 'notices':
global $notices;
if($notices)
{
$_js_snr = '#(\$show\\[\'notices\'\\].*?)(<ol.*?\$notices.*?</ol>)#s';
vbseo_modify_template('navbar', $_js_snr, '$1<!--VBSEO_VIRTUAL_HTML-->$2<!--/VBSEO_VIRTUAL_HTML-->');
}
break;
case 'lastpost_col':                       
vbseo_modify_template('FORUMHOME',
'#<span[^>]*forumlastpost.*?</span>#is', '');
vbseo_modify_template('forumhome_forumbit_level1_nopost',
'#<span[^>]*forumlastpost.*?</span>#is', '');
vbseo_modify_template('FORUMDISPLAY',
'#<span[^>]*forumlastpost.*?</span>#is', '');
vbseo_modify_template('forumhome_forumbit_level2_post',
'#<div[^>]*forumlastpost td.*?</div>.*?</div>#is', '');
vbseo_modify_template('forumhome_forumbit_level1_post',
'#<div[^>]*forumlastpost td.*?</div>.*?</div>#is', '');
break;
case 'lastpost_links':
vbseo_modify_template('forumhome_lastpostby',
'#<a href=[^>]*?lastpostid[^>]*?><img[^<]*?</a>#is', '');
vbseo_modify_template('forumhome_lastpostby',
'$memberaction_dropdown', '$lastpostinfo[\'lastposter\']');
$vbphrase['by_x'] = strip_tags($vbphrase['by_x']);
break;
}
}
function vbseo_process_template_vb3($type, $pdata = array())
{
global $vbulletin, $vbphrase;
switch($type)
{
case 'forumrules':
$pingrules = '<div>" . construct_phrase("$vbphrase[vbseo_trackback_is_x]", "misc.".VBSEO_VB_EXT."?do=linkbacks#trackbacks", "' . iif(VBSEO_EXT_TRACKBACK, $vbphrase['on'], $vbphrase['off']) . '") . "</div>
<div>" . construct_phrase("$vbphrase[vbseo_pingback_is_x]", "misc.".VBSEO_VB_EXT."?do=linkbacks#pingbacks", "' . iif(VBSEO_EXT_PINGBACK, $vbphrase['on'], $vbphrase['off']) . '") . "</div>
<div>" . construct_phrase("$vbphrase[vbseo_refback_is_x]", "misc.".VBSEO_VB_EXT."?do=linkbacks#refbacks", "' . iif(VBSEO_IN_REFBACK, $vbphrase['on'], $vbphrase['off']) . '") . "</div>';
vbseo_modify_template('forumrules', '#(html_code_is_x.*?</div>)#s', '$1' . $pingrules, 0, '<!--LINKBACK_POSTRULES-->');
break;
case 'postbit_bookmarks':
if(($search_for = $pdata['search_for']) == 'editlink')
$search_for = '".(($post[\'editlink\'])';
vbseo_modify_template( $pdata['tpl'], $search_for,
'<div style=\\"float:$stylevar[left]\\">' . str_replace('"', '\\"', $pdata['abm']). "</div>\n" . $search_for
);
break;
case 'postbit_linkback':
$tplpostbit = vbseo_get_postbit_tpl();
$pingtpl = '".($post[\'linkbacksno\']?"<a href=\"' . (($_POST['ajax'] || (THIS_SCRIPT != 'showthread'))?'showthread.' . VBSEO_VB_EXT . '?p=$post[postid]':"") . '#linkbacks\"><img class=\\"inlineimg\\" src=\\"'.vBSEO_Storage::path('fimages').'post_linkback.gif\\" alt=\\"".construct_phrase("$vbphrase[vbseo_no_links_to_this_post]",$post[linkbacksno])."\" border=\\"0\\" /></a> ":"")."';
$pingtpl2 = '<a href=\"' . (($_POST['ajax'] || (THIS_SCRIPT != 'showthread'))?'showthread.' . VBSEO_VB_EXT . '?p=$post[postid]':'#post$post[postid]') . '\" title=\"".$vbphrase[\'vbseo_link_to_this_post\']."\">".$vbphrase[\'vbseo_permalink\']."</a>';
if (VBSEO_POSTBIT_PINGBACK == 1)
$pingtpl .= $pingtpl2;
if (vbseo_tpl_search($tplpostbit, '<!--PERMALINK_INFO-->'))
vbseo_modify_template($tplpostbit, '<!--PERMALINK_INFO-->', $pingtpl);
else
{
if (VBSEO_POSTBIT_PINGBACK == 1)
vbseo_modify_template($tplpostbit, '#(\$show\[\'messageicon\'\] OR \$post\[\'title\'\])(.*?)(<div.*?</div>)#s',
'$1 OR 1$2<table cellspacing=\"0\" cellpadding=\"0\" width=\"100%\" border=\"0\">
<tr><td>$3</td>
<td><div class=\"smallfont\" style=\"float:right\">' . $pingtpl . '</div></td>
</tr></table>'
);
else
vbseo_modify_template($tplpostbit, '#("\.\(\(\$show\[\'postcount\'\])#s',
$pingtpl . '$1'
);
if (VBSEO_POSTBIT_PINGBACK == 3)
vbseo_modify_template($tplpostbit, '#("\.\(\(\$show\[\'postcount\'\].*?</a>)#s',
'$1 (<b>' . $pingtpl2 . '</b>)'
);
}
break;
case 'linkback_menu':
global $vbseo_bookmarks, $vbseo_linkback_menu, $vbseo_linkback_menu_list, $show, $vbseo_linkback_uri, $thread;
eval('$vbseo_linkback_menu = "' . fetch_template('vbseo_linkbackmenu_entry') . '";');
vbseo_modify_template('SHOWTHREAD', '#(<td[^>]*?threadtools)#i', '\$vbseo_linkback_menu$1', 0, '<!--LINKBACK_MENU-->');
eval('$vbseo_linkback_menu_list = "' . fetch_template('vbseo_linkbackmenu') . '";');
if (!vbseo_tpl_search('SHOWTHREAD', '$vbseo_linkback_menu_list'))
vbseo_modify_template('SHOWTHREAD', '#(vbseo_about_linkbacks}</a></li>)#is', '$1' . '\$vbseo_linkback_menu_list');
break;
case 'misc_linkbacks':
global $navbits, $headinclude, $header, $footer;
$navbits = construct_navbits(array('faq.' . VBSEO_VB_EXT . $vbulletin->session->vars['sessionurl_q'] => $vbphrase['faq'],
'' => $vbphrase['vbseo_linkbacks']
));
@extract($GLOBALS);
eval('$navbar = "' . vbseo_fetch_tpl('navbar') . '";');
echo vbseo_fetch_tpl('navbar');
eval('print_output("' . vbseo_fetch_tpl('vbseo_help_linkback') . '");');
exit;
break;	
case 'linkbacks_list':
if (!vbseo_tpl_search('SHOWTHREAD', '$vbseolinkbacks'))
{
$search_for = $pdata['showactusers'] ? '".(($show[\'activeusers\']' : '$similarthreads';
if (!vbseo_tpl_search('SHOWTHREAD', $search_for))
$search_for = '<!-- currently active users -->';
vbseo_modify_template('SHOWTHREAD', $search_for, "\$show[vbseolinkbacks]\n$search_for");
}
break;
case 'notices':
global $notices;
if($notices)
{
$_js_snr = '#(\$show\[\'notices\'\].*?)(<table.*?\$notices.*?</table>)#s';
vbseo_modify_template('navbar', $_js_snr, '$1<!--VBSEO_VIRTUAL_HTML-->$2<!--/VBSEO_VIRTUAL_HTML-->');
}
break;
case 'lastpost_col':
vbseo_modify_template('FORUMHOME',
'#<td[^<]+?vbphrase\[last_post\]</td>#is', '');
vbseo_modify_template('forumhome_forumbit_level1_nopost',
'#<td[^<]+?vbphrase\[last_post\].*?</td>#is', '');  
vbseo_modify_template('FORUMDISPLAY',
'#<td[^<]+?(<span[^<]+?)?(<a[^<]+?)?vbphrase\[last_post\].*?</td>#is', '');
vbseo_modify_template('forumhome_forumbit_level2_post',
'#<td[^<]+?forum\[lastpostinfo\].*?</td>#is', '');
vbseo_modify_template('forumhome_forumbit_level1_post',
'#<td[^<]+?forum\[lastpostinfo\].*?</td>#is', '');
vbseo_modify_template('threadbit',
'#\(\(\$show\[\'threadmoved\'.*?/td>\s*"\)\)\.#is', '');
break;
case 'lastpost_links':
vbseo_modify_template('threadbit',
'#<a href=[^>]*?->[^>]*?->[^>]*?lastpostid.*?</a>#is', '');
vbseo_modify_template('forumhome_lastpostby',
'#<a href=[^>]*?->[^>]*?->[^>]*?lastpostid.*?</a>#is', '');
$vbphrase['by_x'] = strip_tags($vbphrase['by_x']);
break;
}
}
function vbseo_eval_template($tpl, $varname, $append = false)
{
$tplcode = vbseo_fetch_tpl($tpl);
$enclose = VBSEO_VB4 ? "'" : '"';
$tplcode = '$final_rendered = ' . $enclose . $tplcode . $enclose .';';
if($varname)
$tplcode .= $varname . ($append ? '.' : '') . '=$final_rendered;';
return $tplcode;
}
function vbseo_modify_template34($tplname, $searchfor, $replacewith, $show = false, $strsearch = '')
{
if(VBSEO_VB4)
{
$replacewith = preg_replace('#\$stylevar\[(.*?)\]#', "'.vB_Template_Runtime::fetchStylevar(\"$1\").'", $replacewith);
$replacewith = preg_replace('#(\$[\w\_]+?\[.*?\])#m', "'.$1.'", $replacewith);
$searchfor = preg_replace('#(\\\\\$[\w\_]+?\\\\\[)(.*?)(\\\\\])#m', "\' \. $1'$2'$3 \. \'", $searchfor);
}else
{
$replacewith = str_replace('"', '\\"', $replacewith);
}
return vbseo_modify_template($tplname, $searchfor, $replacewith, $show, $strsearch);
}
function vbseo_modify_template($tplname, $searchfor, $replacewith, $show = false, $strsearch = '')
{
global $vbulletin;
$_thistpl = $_thistpl1 = '';    
if (vbseo_tpl_exists($tplname))
{
$_thistpl = & $vbulletin->templatecache[$tplname];
$_thistpl1 = $_thistpl;
if($strsearch && strstr($_thistpl, $strsearch))
{
$_thistpl = str_replace($strsearch, 
preg_replace('#^[\s\-]+#', '', preg_replace('#\$\d+#', '', $replacewith)), $_thistpl);
}else
if($searchfor[0] == '#')
{
$_thistpl = preg_replace($searchfor, $replacewith, $_thistpl);
}else
$_thistpl = str_replace($searchfor, $replacewith, $_thistpl);
}
if ($show)
echo $vbulletin->templatecache[$tplname];
return $_thistpl != $_thistpl1;
}
function vbseo_cache_templates()
{
global $globaltemplates, $bootstrap;
$gtpointer = array();
if($_REQUEST['ajax'])
{
if(THIS_SCRIPT == 'blog_post')
$gtpointer[] = 'blog_comment';
if(THIS_SCRIPT == 'group')
$gtpointer[] = 'socialgroups_message';
if(THIS_SCRIPT == 'album')
$gtpointer[] = 'picturecomment_message';
if(THIS_SCRIPT == 'visitormessage')
$gtpointer[] = 'memberinfo_visitormessage';
}
if(THIS_SCRIPT == 'picturecomment')
$gtpointer[] = 'picturecomment_message';
if (THIS_SCRIPT == 'blog' && $_REQUEST['do'] == 'blog')
{
$gtpointer[] = 'vbseo_blog_bmarkentry';
$gtpointer[] = 'vbseo_blog_bmarksection';
}
if (THIS_SCRIPT == 'moderation')
{
$gtpointer[] = 'vbseo_linkbacks';
$gtpointer[] = 'vbseo_linkbackbit';
}
if ((THIS_SCRIPT == 'showthread') && (VBSEO_IN_PINGBACK || VBSEO_IN_TRACKBACK || VBSEO_IN_REFBACK || VBSEO_BOOKMARK_THREAD))
{
$gtpointer[] = 'vbseo_linkbacks';
$gtpointer[] = 'vbseo_linkbackbit';
$gtpointer[] = 'vbseo_linkbackmenu';
$gtpointer[] = 'vbseo_linkbackmenu_entry';
}
if ((THIS_SCRIPT == 'misc') && ($_REQUEST['do'] == 'pingtrackback'))
vbseo_safe_redirect('misc.' . VBSEO_VB_EXT . '?do=linkbacks', array('do'));
if ((THIS_SCRIPT == 'misc') && ($_REQUEST['do'] == 'linkbacks'))
{
$gtpointer[] = 'vbseo_help_linkback';
}
if(VBSEO_VB4 && $bootstrap)
$bootstrap->cache_templates = array_merge($bootstrap->cache_templates, $gtpointer);
else
if(is_array($globaltemplates))
$globaltemplates = array_merge($globaltemplates, $gtpointer);
}
function vbseo_content_type($cinfo)
{
if($cinfo['albumid'])
return 'album';
else
if($cinfo['groupid'])
return 'group';
else
if(VBSEO_VB4)
{
if(class_exists('vB_Types') && $cinfo['contenttypeid'])
{
$types = vB_Types::instance();
if($cinfo['contenttypeid'] == $types->getContentTypeID('vBForum_Album'))
return 'album';
else
if($cinfo['contenttypeid'] == $types->getContentTypeID('vBForum_SocialGroup'))
return 'group';
else
if($cinfo['contenttypeid'] == $types->getContentTypeID('vBCms_Section'))
return 'cms_section';
else
return 'forum';
}else
return 'forum';
}
}
function vbseo_attachment_contentid($attinfo)
{
if($attinfo['albumid'])
return $attinfo['albumid'];
else
if($attinfo['groupid'])
return $attinfo['groupid'];
else
return $attinfo['contentid'];
}
function vbseo_is_threadedmode()
{
global $bbuserinfo;
$tmode = $bbuserinfo["threadedmode"] ? $bbuserinfo["threadedmode"] : $_COOKIE[vbseo_vb_cprefix() . "threadedmode"];
$mode_nonlinear = ($tmode == 'threaded' || $tmode == '1' || $tmode == '2' || $tmode == 'hybrid');
return $mode_nonlinear;
}
function vbseo_clean_basehref()
{
global $headinclude;
$headinclude = preg_replace('#<base href.*?>#is', '', $headinclude);
}
function vbseo_add_canonic_url($url)
{
global $headinclude;
vBSEO_Storage::set('canonical', $url);
if(VBSEO_CANONIC_LINK_TAG && $headinclude && $url)
$headinclude = '<link rel="canonical" href="'.htmlspecialchars(vbseo_create_full_url($url)).'" />' . "\n" . $headinclude;
}
function vbseo_vbversion()
{
global $vbulletin, $versionnumber, $vboptions;
return is_object($vbulletin) ? $vbulletin->versionnumber : 
($versionnumber ? $versionnumber : $vboptions['templateversion']);
}
function vbseo_code_template($tplname, $tplcode, $append = false)
{
if(VBSEO_VB4)
$tplcode .= ';';
else
$tplcode = '".(('.$tplcode.') ? "":"")."';
if($append)
return vbseo_append_template($tplname, $tplcode);
else
return vbseo_prepend_template($tplname, $tplcode);
}
function vbseo_append_template($tplname, $tplcode, $show = false)
{
global $vbulletin;
if (vbseo_tpl_exists($tplname))
{
$_thistpl = & $vbulletin->templatecache[$tplname];
$_thistpl1 = $_thistpl;
$_thistpl = $_thistpl . $tplcode;
}
if($show)echo $_thistpl;
return ($_thistpl != $_thistpl1);
}
function vbseo_prepend_template($tplname, $tplcode, $show = false)
{
global $vbulletin;
if (vbseo_tpl_exists($tplname))
{
$_thistpl = & $vbulletin->templatecache[$tplname];
$_thistpl1 = $_thistpl;
$_thistpl = $tplcode . $_thistpl;
}
if($show)echo $_thistpl;
return ($_thistpl != $_thistpl1);
}
function vbseo_tpl_exists($tplname)
{
global $vbulletin;
return isset($vbulletin->templatecache[$tplname]);
}
function vbseo_tpl_search($tplname, $searchfor)
{
global $vbulletin;
return strstr($vbulletin->templatecache[$tplname], $searchfor);
}
function vbseo_tpl_match($tplname, $snr)
{
global $vbulletin;
preg_match($snr, $vbulletin->templatecache[$tplname], $pm);
return $pm;
}
function vbseo_get_postbit_tpl()
{
global $vbulletin;     
if (is_object($GLOBALS['postbit_obj']) && $GLOBALS['postbit_obj']->templatename)
$tplpostbit = $GLOBALS['postbit_obj']->templatename;
else
if (isset($vbulletin) && $vbulletin->gars)
$tplpostbit = $vbulletin->gars->process_postbit();
else
$tplpostbit = $vbulletin->options['legacypostbit'] ? 'postbit_legacy' : 'postbit';
return $tplpostbit;
}
function vbseo_vb_gpc($varname)
{
if(is_object($vbulletin) && $vbulletin->GPC)
return $vbulletin->GPC[$varname];
else
return $_REQUEST[$varname];
}
function vbseo_vb_cprefix()
{
global $vbulletin;
if (VBSEO_VB35X)
{
if (!$config && isset($vbulletin))
$config = $vbulletin->config;
$cprefix = $config['Misc']['cookieprefix'];
}
else
$cprefix = $GLOBALS['cookieprefix'];
return $cprefix;
}
function vbseo_bbarray_cookie($cookiename, $id = '')
{
global $_COOKIE, $bbuserinfo;
$cookie_name = vbseo_vb_cprefix() . $cookiename;
$cookie = isset($_COOKIE["$cookie_name"]) ? $_COOKIE["$cookie_name"] : "";
if (get_magic_quotes_gpc())
$cookie = stripslashes($cookie);
if (isset($cookie) && $id)
{
if (VBSEO_VB35X)
$cookie = str_replace(array('.', '-', '_'), array('"', ':', ';'), $cookie);
else
$cookie = str_replace(array('-', '_', 'x', 'y'), array('{', '}', ':', ';'), $cookie);
if (!($uncookie = @unserialize($cookie)))
{
$cookie = substr($cookie, 32);
$uncookie = @unserialize($cookie);
}
$cookie = $uncookie[$id];
}
return $cookie;
}
function vbseo_forum_is_public(&$foruminfo, $foruminfo2 = '', $fullcheck = false, $canread = false)
{
global $vbulletin, $vbseo_bitfields, $bbuserinfo, $forum_is_public;
$f_perms = -1;
$fullcheck_ind = $fullcheck ? 0 : 1;
$check_groups = array(1);
vbseo_cache_start();
if (!$forum_is_public)
{
$forum_is_public = array();
if (!$fullcheck)
$forum_is_public[$fullcheck_ind] = $GLOBALS['vbseo_cache']->cacheget('forum_is_public');
}
if (!$fullcheck && isset($forum_is_public[$fullcheck_ind][$foruminfo['forumid']]))
return $forum_is_public[$fullcheck_ind][$foruminfo['forumid']];
if ($fullcheck && isset($bbuserinfo['usergroupid']))
{
$check_groups[] = $bbuserinfo['usergroupid'];
$check_groups = array_merge($check_groups,
explode(',', $bbuserinfo['membergroupids'])
);
$check_groups[] = - $bbuserinfo['forumpermissions'][$foruminfo['forumid']];
}
$ugp_perm = isset($vbseo_bitfields['ugp']['forumpermissions']) ?
$vbseo_bitfields['ugp']['forumpermissions'] :
$vbulletin->bf_ugp_forumpermissions;
$ispub = false;
foreach($check_groups as $gid)
{
if ($gid < 0)
$f_perms = - $gid;
else
if (@isset($foruminfo['permissions'][$gid]))
$f_perms = $foruminfo['permissions'][$gid];
else
if (@isset($foruminfo2['permissions'][$gid]))
$f_perms = $foruminfo2['permissions'][$gid];
$is_public =
($f_perms < 0) ||
(
($f_perms &(defined('CANVIEW') ? CANVIEW : ($ugp_perm['canview']?$ugp_perm['canview']:1))) && ($f_perms &(defined('CANVIEWOTHERS') ? CANVIEWOTHERS : ($ugp_perm['canviewothers']?$ugp_perm['canviewothers']:2))) && (!$canread || ($ugp_perm['canviewthreads']?$f_perms &$ugp_perm['canviewthreads']:1))
);
if ($is_public)
{
$ispub = true;
break;
}
}
if (!$fullcheck)
{
$forum_is_public[$fullcheck_ind][$foruminfo['forumid']] = $ispub;
$GLOBALS['vbseo_cache']->cacheset('forum_is_public', $forum_is_public[$fullcheck_ind]);
}
return $ispub;
}
function vbseo_vmsg_pagenum($userid, $vmid)
{
global $vboptions;
$db = vbseo_get_db();
$vmsg = $db->vbseodb_query_first("SELECT *
FROM " . vbseo_tbl_prefix('visitormessage')." AS visitormessage
WHERE visitormessage.vmid = ".intval($vmid)."
");
$getpagenum = $db->vbseodb_query_first($q="
SELECT COUNT(*) AS comments
FROM " . vbseo_tbl_prefix('visitormessage')." AS visitormessage
WHERE userid = " . intval($userid) . "
AND state in ('visible')
AND dateline >= $vmsg[dateline]
");
$perpage = intval($vboptions['vm_perpage']);
$pg = $perpage ? ceil($getpagenum['comments'] / $perpage) : 1;
return $pg;
}
function vbseo_gmsg_pagenum(&$disid, $gmid)
{
global $vboptions;
$db = vbseo_get_db();
$commno = 0;
if($GLOBALS['vbseo_gcache']['groupsdis'])
{
foreach($GLOBALS['vbseo_gcache']['groupsdis'] as $gid=>$ginfo)
{
if( ($ginfo['gmid'] == $gmid) || ($ginfo['lastpostid'] == $gmid) )
{
if(!$disid)
$disid = $ginfo['discussionid'];
if(isset($ginfo['replies']))
$commno = $ginfo['replies']+1;
break;
}
}
}
if(!$commno)
{
$gmsg = $db->vbseodb_query_first("SELECT *
FROM " . vbseo_tbl_prefix('groupmessage')."
WHERE gmid = ".intval($gmid)."
");
if(vbseo_vbversion() < '3.8')
return vbseo_grp_pagenum($gmsg['groupid'], $gmid);
if(!$disid)
$disid = $gmsg['discussionid'];
$getpagenum = $db->vbseodb_query_first($q="
SELECT COUNT(*) AS comments
FROM " . vbseo_tbl_prefix('groupmessage')."
WHERE discussionid = " . intval($disid) . "
AND state in ('visible')
AND dateline <= $gmsg[dateline]
");
$commno = $getpagenum['comments'];
}
$perpage = intval($vboptions['gm_perpage']);
$pg = $perpage ? ceil($commno / $perpage) : 1;
return $pg;
}
function vbseo_grp_pagenum($groupid, $gmid)
{
global $vboptions;
$db = vbseo_get_db();
$vmsg = $db->vbseodb_query_first($q1="SELECT *
FROM " . vbseo_tbl_prefix('groupmessage')." AS comments
WHERE gmid = '".intval($gmid)."' AND groupid = '$groupid'
");
$getpagenum = $db->vbseodb_query_first($q="
SELECT COUNT(*) AS comments
FROM " . vbseo_tbl_prefix('groupmessage') . " AS gm
WHERE groupid = '$groupid'
AND state in ('visible')
AND dateline >= $vmsg[dateline]
");
$perpage = intval($vboptions['vm_perpage']);
$pg = $perpage ? ceil($getpagenum['comments'] / $perpage) : 1;
return $pg;
}
function vbseo_pic_pagenum($picid, $commentid)
{
global $vboptions;
$db = vbseo_get_db();
$getpagenum = $db->vbseodb_query_first($q="
SELECT COUNT(*) AS comments
FROM " . vbseo_tbl_prefix('picturecomment')."
WHERE pictureid = " . intval($picid) . "
AND state in ('visible')
AND commentid <= '$commentid'
");
$perpage = intval($vboptions['pc_perpage']);
$pg = $perpage ? ceil($getpagenum['comments'] / $perpage) : 1;
return $pg;
}
function vbseo_get_next_thread($threadid, $older)
{
global $vbseo_gcache;
$thread = $vbseo_gcache['thread'][$threadid];
$db = vbseo_get_db();
$hasthreadprefixes = vbseo_vbversion()>='3.8';
$getnextoldest = $db->vbseodb_query_first("
SELECT t.forumid, t.threadid, " . (VBSEO_GET_THREAD_TITLES ? 't.title, ' : '') . "t.replycount, t.lastposter, t.lastpost".
((VBSEO_URL_THREAD_PREFIX && $hasthreadprefixes) ? ', t.prefixid' : '')."
FROM " . vbseo_tbl_prefix('thread') . " AS t
WHERE forumid = $thread[forumid] AND lastpost " . ($older?'<':'>') . " $thread[lastpost] AND visible = 1 AND open <> 10
ORDER BY lastpost " . ($older?'DESC':'') . "
LIMIT 1
");
vbseo_thread_seotitle($getnextoldest);
$vbseo_gcache['thread'][$getnextoldest['threadid']] = $getnextoldest;
return $getnextoldest;
}
function vbseo_get_last_post($threadid)
{
$db = vbseo_get_db();
$postarr = $db->vbseodb_query_first("
SELECT MAX(postid) AS postid
FROM " . vbseo_tbl_prefix('post') . " AS post
WHERE threadid = " . intval($threadid) . " AND visible = 1
LIMIT 1
");
return $postarr['postid'];
}
function vbseo_get_new_post($threadid)
{
global $bbuserinfo, $vboptions, $vbseo_gcache;
$db = vbseo_get_db();
$lvisit = $bbuserinfo['lastvisit'];
if ($vboptions['threadmarking'] AND $bbuserinfo['userid'])
{
$threadinfo = $db->vbseodb_query_first($q = "
SELECT
threadread.readtime AS threadread, forumread.readtime AS forumread
FROM " . vbseo_tbl_prefix('thread') . " AS thread
LEFT JOIN " . vbseo_tbl_prefix('threadread') . " AS threadread ON (threadread.threadid = thread.threadid AND threadread.userid = " . $bbuserinfo['userid'] . ")
LEFT JOIN " . vbseo_tbl_prefix('forumread') . " AS forumread ON (forumread.forumid = thread.forumid AND forumread.userid = " . $bbuserinfo['userid'] . ")
WHERE thread.threadid = '$threadid'
");
$lvisit = max($threadinfo['threadread'], $threadinfo['forumread'], TIMENOW - ($vboptions['markinglimit'] * 86400));
}
else
if (($tview = vbseo_bbarray_cookie('thread_lastview', $threadid)) > $lvisit)
$lvisit = $tview;
$postarr = $db->vbseodb_query_first($q = "
SELECT MIN(postid) AS postid
FROM " . vbseo_tbl_prefix('post') . "
WHERE threadid = $threadid
AND visible = 1
AND dateline > " . intval($lvisit) . "
LIMIT 1
");
return $postarr['postid'] ? $postarr['postid'] :
vbseo_get_last_post($threadid);
}
function vbseo_get_post_thread_info($ids, $implicit = false)
{
global $vbseo_gcache;
global $found_object_ids, $bbuserinfo;
if (!$ids) return array();
if (!is_array($ids)) $ids = array($ids);
$lookupids = array();
foreach($ids as $id)
if($id)
{
if (isset($vbseo_gcache['post'][$id]) && $vbseo_gcache['post'][$id]['threadid'] && !$implicit) continue;
$vbseo_gcache['post'][$id] = array();
$lookupids[] = $id;
}
if (empty($lookupids))return array();
$db = vbseo_get_db();
$db->vbseodb_select_db();
$rid = $db->vbseodb_query($q = "
select p.postid, t.threadid, t.title, p.dateline
from " . vbseo_tbl_prefix('thread') . " t, " . vbseo_tbl_prefix('post') . " p
where
p.postid in (" . join(',', $lookupids) . ")
AND p.threadid=t.threadid
");
$postids = array();
if ($rid)
{
while ($post = @$db->funcs['fetch_assoc']($rid))
{
if (@in_array($post['postid'], $found_object_ids['prepostthread_ids']))
{
$dbret = $db->vbseodb_query_first("
select count(*) as preposts
from " . vbseo_tbl_prefix('post') . " p
where
p.threadid='" . $post['threadid'] . "'
AND p.visible=1
AND p.dateline " . (($bbuserinfo['postorder'] == 0) ? '<=' : '>=') . $post['dateline'] . "
");
$post['preposts'] = $dbret['preposts'];
$post['prepostsproc'] = isset($bbuserinfo['postorder']);
}
$vbseo_gcache['post'][$post['postid']] = $post;
$found_object_ids['postthreads'][] = $post['threadid'];
}
$db->vbseodb_free_result($rid);
}
$arr = array();
foreach($ids as $id)
{
$arr[$id] = $vbseo_gcache['post'][$id];
}
return $arr;
}
function vbseo_get_forum_announcement($id, $aids = 0)
{
global $vboptions, $vbseo_gcache, $vbseo_precache, $usercache;
$ids = is_array($id) ? $id : array($id);
if (isset($vbseo_precache['announcements']))
{
foreach($vbseo_precache['announcements'] as $ann)
foreach($ids as $fid)
{
$vbseo_gcache['forum'][$fid]['announcement'][$ann['announcementid']] = $ann['title'];
$usercache[$ann['userid']] = array('userid' => $ann['userid'],
'username' => $ann['username']
);
}
return;
}
$db = vbseo_get_db();
$idlist = '';
for($i = 0; $i < count($ids); $i++)
{
$idlist .= ($i?',':'') . $ids[$i];
$pl = $vbseo_gcache['forum'][$ids[$i]]['parentlist'];
if ($pl)
$idlist .= ',' . $pl;
}
$rid = $db->vbseodb_query($q = "
SELECT
forumid,announcementid,title
FROM " . vbseo_tbl_prefix('announcement') . " AS announcement
WHERE " . ($aids?"announcementid='$aids'":"startdate <= " . (time() - $vboptions['hourdiff']) . "
AND enddate >= " . (time() - $vboptions['hourdiff']) . "
AND forumid IN (" . $idlist . ",-1)
ORDER BY startdate DESC")
);
if ($rid)
{
while ($arr = @$db->funcs['fetch_assoc']($rid))
{
$fid = $arr['forumid'];
if ($aids)$ids = array($fid);
for($i = 0; $i < count($ids); $i++)
{
if (isset($vbseo_gcache['forum'][$ids[$i]]))
{
$forum = &$vbseo_gcache['forum'][$ids[$i]];
if (($fid == -1) ||
($ids[$i] == $fid) ||
preg_match('#\b' . $fid . '\b#', $forum['parentlist']))
$forum['announcement'][$arr['announcementid']] = $arr['title'];
}
}
if ($aids)return $arr;
}
$db->vbseodb_free_result($rid);
}
return $forum;
}
function vbseo_get_poll_info($ids)
{
global $vbseo_gcache, $pollinfo;
if (!$ids) return array();
if (!is_array($ids)) $ids = array($ids);
if ($pollinfo)
{
$vbseo_gcache['polls'][$pollinfo['pollid']] = $pollinfo;
$ids = array_diff($ids, array($pollinfo['pollid']));
}
if (isset($vbseo_gcache['polls']) && $vbseo_gcache['polls'])
$ids = array_diff($ids, array_keys($vbseo_gcache['polls']));
if (!empty($ids))
{
$db = vbseo_get_db();
$rid = $db->vbseodb_query($q = "
SELECT
pollid, question
FROM " . vbseo_tbl_prefix('poll') . "
WHERE pollid IN (" . implode(',', $ids) . ")");
if ($rid)
{
while ($arr = @$db->funcs['fetch_assoc']($rid))
$vbseo_gcache['polls'][$arr['pollid']] = $arr;
$db->vbseodb_free_result($rid);
}
}
}
function vbseo_get_forum_info($implicit = false)
{
global $vbseo_gcache, $vbulletin, $vboptions, $usercache, 
$forumcache, $threadcache, $vbseo_cache, $found_object_ids;
vbseo_cache_start();
$f_allow = (!$found_object_ids['forum_last'] ||
($found_object_ids['forum_last'][0] && $forumcache[$found_object_ids['forum_last'][0]] && $forumcache[$found_object_ids['forum_last'][0]]['lastposter'])
);
$fp_cached = $vboptions['vbseo_opt']['forumpaths'] ? true : false;
$vbseo_fp = $fp_cached ? $vboptions['vbseo_opt']['forumpaths'] : array();
$savecache = false;
if (!$vbseo_gcache['forum'])
$vbseo_gcache['forum'] = $vbseo_cache->cacheget('forum');
if (is_object($vbulletin) && $vbulletin->forumcache)
$fc = &$vbulletin->forumcache;
else
$fc = &$forumcache;
if (empty($vbseo_gcache['forum']) || ($implicit && $f_allow))
{
if (is_array($fc) && $f_allow)
{
foreach($fc as $forum_id => $arr)
{
$arr['parentlist'] = substr($arr['parentlist'], 0, -3);
$vbseo_gcache['forum'][$arr['forumid']] = $arr;
}
}
else
{
$db = vbseo_get_db();
$rid = $db->vbseodb_query("select forumid" . (VBSEO_GET_FORUM_TITLES ? ', title' : '') . 
", parentlist, lastpost, lastposter, daysprune, parentid, threadcount, lastthreadid, lastthread, lastpostid from " . vbseo_tbl_prefix('forum') );
if ($rid)
{
while ($arr = @$db->funcs['fetch_assoc']($rid))
{
$arr['parentlist'] = substr($arr['parentlist'], 0, -3);
$vbseo_gcache['forum'][$arr['forumid']] = $arr;
}
$db->vbseodb_free_result($rid);
}
}
$forumids = $vbseo_gcache['forum'] ? array_keys($vbseo_gcache['forum']) : array();
foreach($forumids as $forumid)
{
$forum = &$vbseo_gcache['forum'][$forumid];
if (isset($forum['lastthreadid']) && ($tid = $forum['lastthreadid']) && !in_array(THIS_SCRIPT, array('showthread', 'printthread', 'showpost')))
{
$threadcache[$tid] = array_merge(isset($threadcache[$tid])?$threadcache[$tid]:array(),
array('threadid' => $forum['lastthreadid'],
'title' => $forum['lastthread'],
'forumid' => $forumid,
'lastpost' => $forum['lastpost'],
'lastpostid' => $forum['lastpostid'],
'lastposter' => $forum['lastposter']
));
}
if ($fp_cached)
continue;
$parentlist = array_reverse(explode(',', $forum['parentlist']));
$forum['patharr'] = array();
if (VBSEO_GET_FORUM_PATH)
for($i = 0; isset($parentlist[$i]) && ($id = $parentlist[$i]); $i++)
{
vbseo_forum_seotitle($vbseo_gcache['forum'][$id]);
$replace = array('%forum_id%' => $id,
'%forum_title%' => $vbseo_gcache['forum'][$id]['seotitle'],
);
$forum['patharr'] [] = str_replace(array_keys($replace), $replace, VBSEO_FORUM_TITLE_BIT);
}
$fc[$forumid]['path'] = $forum['path'] = @implode('/', $forum['patharr']);
}
$savecache = true;
}
if (!$fp_cached)
{
$vboptions['vbseo_opt'] = array();
vbseo_check_datastore();
}
if(is_array($vbseo_gcache['forum']))
foreach($vbseo_gcache['forum'] as $forumid => $arr)
{
if (isset($arr['lastposter']) && $found_object_ids['forum_last'] && in_array($arr['forumid'], $found_object_ids['forum_last']))
$found_object_ids['user_names'][] = $arr['lastposter'];
if (!isset($arr['path']) && $fp_cached)
$vbseo_gcache['forum'][$forumid]['path'] = $vbseo_fp[$forumid];
if (isset($arr['lastpostid']))
{
$lpostid = $arr['lastpostid'];
if (!isset($vbseo_gcache['post'][$lpostid]) && isset($arr['lastthreadid']))
$vbseo_gcache['post'][$lpostid] = array('postid' => $lpostid,
'threadid' => $arr['lastthreadid'],
);
}
}
if ($savecache)
{
if(is_array($vbseo_gcache['forum']))
foreach($vbseo_gcache['forum'] as $forumid=>$finfo)
if($finfo['forumread'])
unset($vbseo_gcache['forum'][$forumid]['forumread']);
$vbseo_cache->cacheset('forum', $vbseo_gcache['forum']);
}
vbseo_prepare_cat_anchors();
if (isset($id)) return $vbseo_gcache['forum'][$id];
return $vbseo_gcache['forum'];
}
function vbseo_get_thread_details($postid)
{
$db = vbseo_get_db();
return $db->vbseodb_query_first("SELECT p.pagetext,p.postid FROM " . vbseo_tbl_prefix('post') . " p
WHERE p.postid='$postid'
"
);
}
function vbseo_get_attachments_info($ids)
{
global $vbseo_gcache, $found_object_ids;
if (!$ids) return array();
if (!is_array($ids)) $ids = array($ids);
global $postattach;
if (is_array($postattach))
foreach($postattach as $pid => $attarr)
{
if (is_array($attarr))
foreach($attarr as $id => $arr)
{
$vbseo_gcache['attach'][$id] = $arr;
$found_object_ids['postthread_ids'][] = $arr['postid'];
}
}
$lookupids = array();
foreach($ids as $id)
if($id)
{
if (isset($vbseo_gcache['attach'][$id])) continue;
$vbseo_gcache['attach'][$id] = array();
$lookupids[] = $id;
}
if (empty($lookupids))return array();
$db = vbseo_get_db();
$rid = $db->vbseodb_query($q = "
select at.attachmentid,at.filename,".(VBSEO_VB4 ? 'contenttypeid, contentid, caption' : 'at.postid')."
from " . vbseo_tbl_prefix('attachment') . " at
where
at.attachmentid IN (" . implode(',', $lookupids) . ")
");
if ($rid)
{
while ($att = @$db->funcs['fetch_assoc']($rid))
{
if($att['contenttypeid'] == 1)
$att['postid'] = $att['contentid'];
if($att['postid'])
$found_object_ids['postthread_ids'][] = $att['postid'];
$vbseo_gcache['attach'][$att['attachmentid']] = $att;
}
$db->vbseodb_free_result($rid);
}
return;
}
function vbseo_get_thread_info($ids)
{
global $vbseo_gcache, $found_object_ids;
$haslastpostid = vbseo_vbversion()>='3.6';
$hasthreadprefixes = vbseo_vbversion()>='3.8';
if (!$ids) return array();
if (!is_array($ids)) $ids = array($ids);
$lookupids = array();
foreach($ids as $id)
if ($id)
{
if (isset($vbseo_gcache['thread'][$id])) continue;
$vbseo_gcache['thread'][$id] = array();
$lookupids[] = $id;
}
if (!empty($lookupids))
{
$where = array('');
$db = vbseo_get_db();
$rid = $db->vbseodb_query(
"select t.forumid, t.threadid, " . 
(VBSEO_GET_THREAD_TITLES ? 't.title, ' : '') . 
((VBSEO_URL_THREAD_PREFIX && $hasthreadprefixes) ? 't.prefixid, ' : '') . 
"t.replycount, t.lastposter, ".($haslastpostid?"lastpostid, ":"").
"lastpost, visible
from " . vbseo_tbl_prefix('thread') . " t
where
t.threadid in (" . join(',', $lookupids) . ")
");
if ($rid)
{
while ($thread = @$db->funcs['fetch_assoc']($rid))
{
vbseo_thread_seotitle($thread);
$vbseo_gcache['thread'][$thread['threadid']] = $thread;
if ($found_object_ids['thread_last'] && in_array($thread['threadid'], $found_object_ids['thread_last']))
$found_object_ids['user_names'][] = $thread['lastposter'];
}
$db->vbseodb_free_result($rid);
}
}
$arr = array();
foreach($ids as $id)
$arr[$id] = $vbseo_gcache['thread'][$id];
return $arr;
}
function vbseo_get_posts_info($postids)
{
global $vbseo_gcache;
if (!$postids)return;
$db = vbseo_get_db();
$preq = "select postid, threadid
from " . vbseo_tbl_prefix('post') . "
where postid in ('" . implode("','", array_unique($postids)) . "')";
$rid = $db->vbseodb_query($preq);
if ($rid)
{
while ($arr = @$db->funcs['fetch_assoc']($rid))
{
$vbseo_gcache['post'][$arr['postid']] = $arr;
}
$db->vbseodb_free_result($rid);
}
}
function vbseo_get_user_info($userids, $user_names = array())
{
global $vbseo_gcache;
$whr = $whr2 = '';
if(!is_array($userids))
$userids = array($userids);
if (!empty($userids))
$whr .= "u.userid in ('" . implode("','", $userids) . "')";
if (!empty($user_names) && strstr(VBSEO_URL_MEMBER, '%user_id%'))
{
$unw = '';
foreach($user_names as $uind => $uname)
$unw .= ($unw?',':'') . "'" . str_replace("'", "\\'", str_replace("\\", "\\\\", $uname)) . "'";
$whr2 = "u.username in ($unw)";
}
if ($whr || $whr2)
{
$db = vbseo_get_db();
$preq = "select u.userid, u.username
from " . vbseo_tbl_prefix('user') . " u
where ";
if ($db->mysql_version[0] > '3')
$rid = $db->vbseodb_query(($whr?"( $preq  $whr )":"") . (($whr && $whr2)?"\nUNION ":"") . ($whr2?"( $preq  $whr2 )":""));
else
$rid = $db->vbseodb_query($preq . $whr . (($whr && $whr2) ? ' OR ' : '') . $whr2);
if ($rid)
{
while ($arr = @$db->funcs['fetch_assoc']($rid))
{
$vbseo_gcache['user'][$arr['userid']] =
$vbseo_gcache['usernm'][strtolower($arr['username'])] =
$arr;
}
$db->vbseodb_free_result($rid);
}
}
}
function vbseo_get_blog_info($ids)
{
global $vbseo_gcache;
if (!$ids) return array();
if (!is_array($ids)) $ids = array($ids);
$lookupids = array();
foreach($ids as $id)
if($id)
{
if (isset($vbseo_gcache['blog'][$id])&&isset($vbseo_gcache['blog'][$id]['userid'])) continue;
$lookupids[$id]++;
}
if (empty($lookupids))
return;
$db = vbseo_get_db();
$db->vbseodb_select_db();
$rid = $db->vbseodb_query("
select b.userid, b.username, b.blogid, b.title
from " . vbseo_tbl_prefix('blog') . " b
where
blogid in (" . join(',', array_keys($lookupids)) . ")
");
if ($rid)
{
while ($bl = @$db->funcs['fetch_assoc']($rid))
$vbseo_gcache['blog'][$bl['blogid']] = $bl;
$db->vbseodb_free_result($rid);
}
return;
}
function vbseo_get_blogatt_info($ids)
{
global $vbseo_gcache;
if (!$ids) return array();
if (!is_array($ids)) $ids = array($ids);
$lookupids = array();
foreach($ids as $id)
if($id)
{
if (isset($vbseo_gcache['battach'][$id])) continue;
$lookupids[$id]++;
}
if (empty($lookupids))
return;
$db = vbseo_get_db();
$db->vbseodb_select_db();
$rid = $db->vbseodb_query($q="
select attachmentid, blogid, userid, filename, dateline
from " . vbseo_tbl_prefix('blog_attachment') . "
where
attachmentid in (" . join(',', array_keys($lookupids)) . ")
");
if ($rid)
{
while ($bl = @$db->funcs['fetch_assoc']($rid))
$vbseo_gcache['battach'][$bl['attachmentid']] = $bl;
$db->vbseodb_free_result($rid);
}
return;
}
function vbseo_get_blog_cats($ids)
{
global $vbseo_gcache;
if (!$ids) return array();
if (!is_array($ids)) $ids = array($ids);
$lookupids = array();
foreach($ids as $id)
if($id)
{
if (isset($vbseo_gcache['blogcat'][$id])) continue;
$lookupids[] = $id;
}
if (empty($lookupids))return array();
$db = vbseo_get_db();
$db->vbseodb_select_db();
$rid = $db->vbseodb_query("
select blogcategoryid, title, userid
from " . vbseo_tbl_prefix('blog_category') . "
where
blogcategoryid in (" . join(',', $lookupids) . ")
");
if ($rid)
{
while ($bl = @$db->funcs['fetch_assoc']($rid))
$vbseo_gcache['blogcat'][$bl['blogcategoryid']] = $bl;
$db->vbseodb_free_result($rid);
}
return;
}
function vbseo_get_group_info($ids)
{
global $vbseo_gcache;
if (!$ids) return array();
if (!is_array($ids)) $ids = array($ids);
$lookupids = array();
foreach($ids as $id)
if($id)
{
if (isset($vbseo_gcache['groups'][$id])) continue;
$lookupids[] = $id;
}
if (empty($lookupids))return array();
$db = vbseo_get_db();
$db->vbseodb_select_db();
$rid = $db->vbseodb_query("
select groupid, name, visible, deleted
from " . vbseo_tbl_prefix('socialgroup') . "
where
groupid in (" . join(',', $lookupids) . ")
");
if ($rid)
{
while ($bl = @$db->funcs['fetch_assoc']($rid))
$vbseo_gcache['groups'][$bl['groupid']] = $bl;
$db->vbseodb_free_result($rid);
}
return;
}
function vbseo_get_object_info($otype)
{
global $vbseo_gcache, $found_object_ids;
vbseo_clean_object_ids($otype);
if (!$ids = $found_object_ids[$otype])
return;
if(is_array($vbseo_gcache[$otype]))
$ids = array_diff($ids, array_keys($vbseo_gcache[$otype]));
$lookupids = array();
foreach($ids as $id)
if($id)
$lookupids[] = $id;
if (empty($lookupids))return array();
$db = vbseo_get_db();
$db->vbseodb_select_db();
switch($otype)
{
case 'groupsdis':
$q = "select d.discussionid as tyid, d.discussionid, d.groupid, gm.title, gm.gmid
from " . vbseo_tbl_prefix('discussion') . " d
left join " . vbseo_tbl_prefix('groupmessage') ." gm on gm.gmid = d.firstpostid
where d.discussionid in (" . join(',', $lookupids) . ")";
break;
case 'blogcp_ids':
$q = "select customblockid as tyid, userid, title
from " . vbseo_tbl_prefix('blog_custom_block') . "
where customblockid in (" . join(',', $lookupids) . ")";
break;
case 'album':
$q = "select albumid as tyid, albumid, userid, title
from " . vbseo_tbl_prefix('album') . "
where albumid in (" . join(',', $lookupids) . ")";
break;
case 'cmscont':
$q = "select n.nodeid as tyid, n.url, n.parentnode, n.contenttypeid, ni.title
from " . vbseo_tbl_prefix('cms_node') . " as n
left join " . vbseo_tbl_prefix('cms_nodeinfo') . " as ni on n.nodeid=ni.nodeid
where n.nodeid in (" . join(',', $lookupids) . ")";
break;
case 'cms_cat':
$q = "select categoryid as tyid, categoryid, parentnode, category
from " . vbseo_tbl_prefix('cms_category') . "
where categoryid in (" . join(',', $lookupids) . ")";
break;
case 'pic':
if(VBSEO_VB4)
return vbseo_get_attachments_info($lookupids);
$q = "select p.pictureid as tyid, p.pictureid, ap.albumid, caption, extension
from " . vbseo_tbl_prefix('picture') . " p
join " . vbseo_tbl_prefix('albumpicture') . " ap on ap.pictureid=p.pictureid
where p.pictureid in (" . join(',', $lookupids) . ")";
break;
}
$rid = $db->vbseodb_query($q);
if ($rid)
{
while ($bl = @$db->funcs['fetch_assoc']($rid))
$vbseo_gcache[$otype][$bl['tyid']] = $bl;
$db->vbseodb_free_result($rid);
}
return;
}
function vbseo_extract_msg_postbits()
{
global $vbulletin;
$msg = '';
if($pbits = $GLOBALS['postbits'])
{
preg_match('#<!--\s*message\s*-->(.*?)<!--\s*/\s*message\s*-->#s', $pbits, $post_match);
if(!$post_match || (isset($vbulletin) && $vbulletin->gars) )
preg_match('#post_message_.*?\>(.*?)'.(VBSEO_VB4? '</blockquote>' : '</div>').'#s', $pbits, $post_match);
$msg = preg_replace('#<!--.*?-->#s', '', $msg);
$msg = str_replace('>Quote:<', '', $post_match[1]);
$msg = preg_replace('#<div>Originally Posted by.*?</div>#', '', $msg);
$msg = preg_replace('#<script.*?\>.*?</script>#is', '', $msg);
$msg = preg_replace('#(<.*?\>)+#s', ' ', $msg);
$msg = trim($msg);
}
return $msg;
}
?>