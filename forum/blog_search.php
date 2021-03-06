<?php
/*======================================================================*\
|| #################################################################### ||
|| # vBulletin Blog 4.0.2
|| # ---------------------------------------------------------------- # ||
|| # Copyright �2000-2010 vBulletin Solutions Inc. All Rights Reserved. ||
|| # This file may not be redistributed in whole or significant part. # ||
|| # ---------------- VBULLETIN IS NOT FREE SOFTWARE ---------------- # ||
|| # http://www.vbulletin.com | http://www.vbulletin.com/license.html # ||
|| #################################################################### ||
\*======================================================================*/

// ####################### SET PHP ENVIRONMENT ###########################
error_reporting(E_ALL & ~E_NOTICE);

// #################### DEFINE IMPORTANT CONSTANTS #######################
define('VB_PRODUCT', 'vbblog');
define('THIS_SCRIPT', 'blog_search');
define('CSRF_PROTECTION', true);
define('VBBLOG_PERMS', true);
define('VBBLOG_STYLE', true);
define('VBBLOG_SCRIPT', true);

// ################### PRE-CACHE TEMPLATES AND DATA ######################
// get special phrase groups
$phrasegroups = array(
	'vbblogglobal',
	'vbblogcat',
	'posting',
	'search'
);

// get special data templates from the datastore
$specialtemplates = array(
	'smiliecache',
	'bbcodecache',
	'blogstats',
	'blogfeatured',
	'blogcategorycache',
	'blogtagcloud',
	'blogsearchcloud',
);

// pre-cache templates used by all actions
$globaltemplates = array(
	'BLOG',
	'blog_css',
	'blog_usercss',
	'ad_blog_sidebar_start',
	'ad_blog_sidebar_middle',
	'ad_blog_sidebar_end',
);

// pre-cache templates used by specific actions
$actiontemplates = array(
	'search'					=> array(
		'blog_sidebar_calendar',
		'blog_sidebar_calendar_day',
		'blog_search_advanced',
		'blog_sidebar_generic',
		'blog_sidebar_user',
		'blog_sidebar_comment_link',
		'blog_sidebar_custompage_link',
		'blog_sidebar_entry_link',
		'blog_sidebar_category_link',
		'blog_tag_cloud_box',
		'blog_tag_cloud_link',
		'humanverify',
	),
	'searchresults'		=>	array(
		'blog_sidebar_calendar',
		'blog_sidebar_calendar_day',
		'blog_sidebar_generic',
		'blog_search_results_result',
		'blog_search_results',
		'blog_sidebar_user',
		'blog_sidebar_comment_link',
		'blog_sidebar_custompage_link',
		'blog_sidebar_entry_link',
		'blog_sidebar_category_link',
		'blog_sidebar_user_block_archive',
		'blog_sidebar_user_block_category',
		'blog_sidebar_user_block_comments',
		'blog_sidebar_user_block_entries',
		'blog_sidebar_user_block_search',
		'blog_sidebar_user_block_tagcloud',
		'blog_sidebar_user_block_visitors',
		'blog_sidebar_user_block_custom',
		'memberinfo_visitorbit',
		'blog_tag_cloud_link',
	),
);

$actiontemplates['dosearch'] =& $actiontemplates['search'];

if (empty($_REQUEST['do']))
{
	$_REQUEST['do'] = 'search';
}

// ######################### REQUIRE BACK-END ############################
require_once('./global.php');


// Temporarily disabling this entry point. This will be revisited. Bug #33021
exec_header_redirect('search.php?' . $vbulletin->session->vars['sessionurl'] . 'search_type=1#ads=15', 301);


require_once(DIR . '/includes/functions_bigthree.php');
require_once(DIR . '/includes/blog_init.php');
require_once(DIR . '/includes/blog_functions_search.php');

// ### STANDARD INITIALIZATIONS ###
$navbits = array();

/* Check they can view a blog, any blog */
if (!($vbulletin->userinfo['permissions']['vbblog_general_permissions'] & $vbulletin->bf_ugp_vbblog_general_permissions['blog_canviewothers']))
{
	if (!$vbulletin->userinfo['userid'] OR !($vbulletin->userinfo['permissions']['vbblog_general_permissions'] & $vbulletin->bf_ugp_vbblog_general_permissions['blog_canviewown']))
	{
		print_no_permission();
	}
}

// #######################################################################
// ######################## START MAIN SCRIPT ############################
// #######################################################################


$searcherrors = array();
$search_fields = array(
	/* Primary search things */
	'text'                  => TYPE_STR,
	'title'                 => TYPE_STR,
	'comments_title'        => TYPE_STR,
	'textandtitle'          => TYPE_STR,
	'comments_textandtitle' => TYPE_STR,
	'searchuserid'          => TYPE_UINT,
	'username'              => TYPE_STR,
	'tag'                   => TYPE_STR,
);

$optional_fields = array(
	/* Optional extras */
	'sort'           => TYPE_NOHTML,
	'sortorder'      => TYPE_NOHTML,
	'ignorecomments' => TYPE_BOOL,
	'quicksearch'    => TYPE_BOOL,
	'titleonly'      => TYPE_BOOL,
	'boolean'        => TYPE_BOOL,
	'imagehash'      => TYPE_STR,
	'imagestamp'     => TYPE_STR,
	'humanverify'    => TYPE_ARRAY,
);

($hook = vBulletinHook::fetch_hook('blog_search_start')) ? eval($hook) : false;

// #######################################################################
if ($_POST['do'] == 'dosearch')
{
	if (!($vbulletin->userinfo['permissions']['vbblog_general_permissions'] & $vbulletin->bf_ugp_vbblog_general_permissions['blog_cansearch']))
	{
		print_no_permission();
	}

	$vbulletin->input->clean_array_gpc('p', $search_fields + $optional_fields);

	($hook = vBulletinHook::fetch_hook('blog_search_dosearch_start')) ? eval($hook) : false;

	if ($prevsearch = $db->query_first("
		SELECT blogsearchid, dateline
		FROM " . TABLE_PREFIX . "blog_search
		WHERE " . (!$vbulletin->userinfo['userid'] ?
			"ipaddress = '" . $db->escape_string(IPADDRESS) . "'" :
			"userid = " . $vbulletin->userinfo['userid']) . "
		ORDER BY dateline DESC LIMIT 1
	"))
	{
		if ($vbulletin->options['searchfloodtime'] > 0)
		{
			$timepassed = TIMENOW - $prevsearch['dateline'];
			$is_special_user = (($vbulletin->userinfo['permissions']['adminpermissions'] & $vbulletin->bf_ugp_adminpermissions['cancontrolpanel']) OR can_moderate());

			if ($timepassed < $vbulletin->options['searchfloodtime'] AND !$is_special_user)
			{
				$searcherrors[] = fetch_error('searchfloodcheck', $vbulletin->options['searchfloodtime'], ($vbulletin->options['searchfloodtime'] - $timepassed));
			}
		}
	}

	$criteria = array();
	foreach (array_keys($search_fields + $optional_fields) AS $varname)
	{
		$criteria["$varname"] = $vbulletin->GPC["$varname"];
	}

	if (fetch_require_hvcheck('search'))
	{
		require_once(DIR . '/includes/class_humanverify.php');
		$verify =& vB_HumanVerify::fetch_library($vbulletin);
		if (!$verify->verify_token($vbulletin->GPC['humanverify']))
		{
			if ($criteria['quicksearch'])
			{
				$searcherrors[] = fetch_error('please_complete_humanverification');
			}
			else
			{
				$searcherrors[] = fetch_error($verify->fetch_error());
			}
		}
	}

	if (empty($searcherrors))
	{
		if ($criteria['quicksearch'])
		{
			if ($criteria['titleonly'])
			{
				$criteria['boolean'] = 2;
			}
			else
			{
				$criteria['boolean'] = 1;
			}
		}

		$comments = $criteria['ignorecomments'] ? '' : 'comments_';
		if ($criteria['boolean'] == 1)
		{
			$criteria[$comments . 'textandtitle'] = $criteria['text'];
		}
		else
		{
			$criteria[$comments . 'title'] = $criteria['text'];
		}
		$criteria['text'] = '';

		require_once(DIR . '/includes/class_blog_search.php');
		$search = new vB_Blog_Search($vbulletin);

		$has_criteria = false;
		foreach ($search_fields AS $fieldname => $clean_type)
		{
			if (!empty($criteria["$fieldname"]))
			{
				if ($search->add($fieldname, $criteria["$fieldname"]))
				{
					$has_criteria = true;
				}
			}
		}

		$search->set_sort($criteria['sort'], $criteria['sortorder']);

		if ($search->has_errors())
		{
			$searcherrors = $search->generator->errors;
		}

		if (!$search->has_criteria())
		{
			$searcherrors[] = fetch_error('blog_need_search_criteria');
		}

		if (empty($searcherrors))
		{
			$search_perms = build_blog_permissions_query($vbulletin->userinfo);
			$searchid = $search->execute($search_perms);
			($hook = vBulletinHook::fetch_hook('blog_search_dosearch_complete')) ? eval($hook) : false;

			if ($search->has_errors())
			{
				$searcherrors = $search->generator->errors;
			}
			else
			{
				$vbulletin->url = 'blog_search.php?' . $vbulletin->session->vars['sessionurl'] . "do=searchresults&searchid=$searchid";
				eval(print_standard_redirect('blog_search_executed'));
			}
		}
	}

	$_REQUEST['do'] = 'search';
}

// #######################################################################
if ($_REQUEST['do'] == 'searchresults')
{
	$vbulletin->input->clean_array_gpc('r', array(
		'searchid'   => TYPE_UINT,
		'start'      => TYPE_UINT,
		'pagenumber' => TYPE_UINT,
		'perpage'    => TYPE_UINT
	));

	if (!($vbulletin->userinfo['permissions']['vbblog_general_permissions'] & $vbulletin->bf_ugp_vbblog_general_permissions['blog_cansearch']))
	{
		print_no_permission();
	}

	$search = $db->query_first("
		SELECT *
		FROM " . TABLE_PREFIX . "blog_search
		WHERE blogsearchid = " . $vbulletin->GPC['searchid']
	);
	if (!$search OR ($search['userid'] AND $search['userid'] != $vbulletin->userinfo['userid']))
	{
		standard_error(fetch_error('invalidid', $vbphrase['search'], $vbulletin->options['contactuslink']));
	}

	($hook = vBulletinHook::fetch_hook('blog_search_results_start')) ? eval($hook) : false;

	if ($search['searchuserid'])
	{
		$userinfo = fetch_userinfo($search['searchuserid'], 1);
		$sidebar =& build_user_sidebar($userinfo);
	}
	else
	{
		$sidebar =& build_overview_sidebar();
	}

	// Set Perpage .. this limits it to 10. Any reason for more?
	if ($vbulletin->GPC['perpage'] == 0)
	{
		$perpage = 15;
	}
	else if ($vbulletin->GPC['perpage'] > 10)
	{
		$perpage = 30;
	}
	else
	{
		$perpage = $vbulletin->GPC['perpage'];
	}

	$pagenum = ($vbulletin->GPC['pagenumber'] > 0 ? $vbulletin->GPC['pagenumber'] : 1);
	$maxpages = ceil($search['resultcount'] / $perpage);
	if ($pagenum > $maxpages)
	{
		$pagenum = $maxpages;
	}

	if (!$vbulletin->GPC['start'])
	{
		$vbulletin->GPC['start'] = ($pagenum - 1) * $perpage;
		$previous_results = $vbulletin->GPC['start'];
	}
	else
	{
		$previous_results = ($pagenum - 1) * $perpage;
	}
	$previouspage = $pagenum - 1;
	$nextpage = $pagenum + 1;

	$hook_query_fields = $hook_query_joins = $hook_query_where = '';
	($hook = vBulletinHook::fetch_hook('blog_search_results_query')) ? eval($hook) : false;

	$results = $db->query_read("
		SELECT blog.*, blog_searchresult.offset, IF(blog_user.title <> '', blog_user.title, blog.username) AS blogtitle
		" . (($vbulletin->userinfo['userid'] AND in_coventry($vbulletin->userinfo['userid'], true)) ? "
		,IF(blog_tachyentry.userid IS NULL, blog.lastcomment, blog_tachyentry.lastcomment) AS lastcomment
		,IF(blog_tachyentry.userid IS NULL, blog.lastcommenter, blog_tachyentry.lastcommenter) AS lastcommenter
		,IF(blog_tachyentry.userid IS NULL, blog.lastblogtextid, blog_tachyentry.lastblogtextid) AS lastblogtextid
		" : "") . "
			$hook_query_fields
		FROM " . TABLE_PREFIX . "blog_searchresult AS blog_searchresult
		INNER JOIN " . TABLE_PREFIX . "blog AS blog ON (blog_searchresult.id = blog.blogid)
		LEFT JOIN " . TABLE_PREFIX . "blog_user AS blog_user ON (blog_user.bloguserid = blog.userid)
		" . (($vbulletin->userinfo['userid'] AND in_coventry($vbulletin->userinfo['userid'], true)) ? "
		LEFT JOIN " . TABLE_PREFIX . "blog_tachyentry AS blog_tachyentry ON (blog_tachyentry.blogid = blog.blogid AND blog_tachyentry.userid = " . $vbulletin->userinfo['userid'] . ")
		" : "") . "
		$hook_query_joins
		WHERE blog_searchresult.blogsearchid = $search[blogsearchid]
			AND blog_searchresult.offset >= " . $vbulletin->GPC['start'] . "
		$hook_query_where
		ORDER BY offset
		LIMIT $perpage
	");

	$resultbits = '';
	while ($blog = $db->fetch_array($results))
	{
		$canmoderation = (can_moderate_blog('canmoderatecomments') OR $vbulletin->userinfo['userid'] == $blog['userid']);
		$blog['trackbacks_total'] = $blog['trackback_visible'] + ($canmoderation ? $blog['trackback_moderation'] : 0);
		$blog['comments_total'] = $blog['comments_visible'] + ($canmoderation ? $blog['comments_moderation'] : 0);
		$blog['lastcommenter_encoded'] = urlencode($blog['lastcommenter']);

		$blog['lastposttime'] = vbdate($vbulletin->options['timeformat'], $blog['lastcomment']);
		$blog['lastpostdate'] = vbdate($vbulletin->options['dateformat'], $blog['lastcomment'], true);
		$show['blogtitle'] = ($blog['blogtitle'] != $blog['username']);
		$templater = vB_Template::create('blog_search_results_result');
			$templater->register('blog', $blog);
		$resultbits .= $templater->render();
	}

	$next_result = $previous_results + $db->num_rows($results) + 1;
	$show['next_page'] = ($next_result <= $search['resultcount']);
	$show['previous_page'] = ($pagenum > 1);
	$show['pagenav'] = ($show['next_page'] OR $show['previous_page']);
	$first = ($pagenum - 1) * $perpage + 1;
	$last = ($last = $perpage * $pagenum) > $search['resultcount'] ? $search['resultcount'] : $last;

	$pagenav = construct_page_nav(
		$pagenum,
		$perpage,
		$search['resultcount'],
		'blog_search.php?' . $vbulletin->session->vars['sessionurl'] . "do=searchresults&amp;searchid=$search[blogsearchid]",
		''
	);

	// navbar and output
	$navbits['blog_search.php?' . $vbulletin->session->var['sessionurl'] . 'do=search'] = $vbphrase['search'];
	$navbits[] = $vbphrase['search_results'];

	($hook = vBulletinHook::fetch_hook('blog_search_results_complete')) ? eval($hook) : false;

	$templater = vB_Template::create('blog_search_results');
		$templater->register('first', $first);
		$templater->register('last', $last);
		$templater->register('pagenav', $pagenav);
		$templater->register('resultbits', $resultbits);
		$templater->register('search', $search);
	$content = $templater->render();
}

// #######################################################################
if ($_REQUEST['do'] == 'search')
{
	if (!($vbulletin->userinfo['permissions']['vbblog_general_permissions'] & $vbulletin->bf_ugp_vbblog_general_permissions['blog_cansearch']))
	{
		print_no_permission();
	}

	$vbulletin->input->clean_array_gpc('r', $search_fields + $optional_fields);

	($hook = vBulletinHook::fetch_hook('blog_search_form_start')) ? eval($hook) : false;

	if (!empty($searcherrors))
	{
		$errorlist = '';
		foreach($searcherrors AS $error)
		{
			$errorlist .= "<li>$error</li>";
		}
		$show['errors'] = true;
	}

	if ($vbulletin->GPC['quicksearch'])
	{
		if ($vbulletin->GPC['titleonly'])
		{
			$vbulletin->GPC['boolean'] = 2;
		}
		else
		{
			$vbulletin->GPC['boolean'] = 1;
		}
		$vbulletin->GPC_exists['boolean'] = true;
	}
	else if (!$vbulletin->GPC['boolean'])
	{
		$vbulletin->GPC['boolean'] = 1;
		$vbulletin->GPC_exists['boolean'] = true;
	}

	// if search conditions are specified in the URI, use them
	foreach (array_keys($search_fields + $optional_fields) AS $varname)
	{
		if ($vbulletin->GPC_exists["$varname"] AND !is_array($vbulletin->GPC["$varname"]))
		{
			$$varname = htmlspecialchars_uni($vbulletin->GPC["$varname"]);
			$checkedvar = $varname . 'checked';
			$selectedvar = $varname . 'selected';
			$$checkedvar = array($vbulletin->GPC["$varname"] => 'checked="checked"');
			$$selectedvar = array($vbulletin->GPC["$varname"] => 'selected="selected"');
		}
	}

	// image verification
	$human_verify = '';
	if (fetch_require_hvcheck('search'))
	{
		require_once(DIR . '/includes/class_humanverify.php');
		$verification =& vB_HumanVerify::fetch_library($vbulletin);
		$human_verify = $verification->output_token();
	}

	$tag_cloud = '';
	if ($vbulletin->options['vbblog_tagging'] AND $vbulletin->options['vbblog_tagcloud_searchcloud'])
	{
		$show['searchcloud'] = true;
		if ($tag_cloud = fetch_blog_tagcloud('search'))
		{
			$show['tagcloud_css'] = true;
		}
	}

	// navbar and output
	$navbits[] = $vbphrase['search'];

	$sidebar =& build_overview_sidebar();

	($hook = vBulletinHook::fetch_hook('blog_search_form_complete')) ? eval($hook) : false;

	$templater = vB_Template::create('blog_search_advanced');
		$templater->register('booleanchecked', $booleanchecked);
		$templater->register('errorlist', $errorlist);
		$templater->register('human_verify', $human_verify);
		$templater->register('ignorecommentschecked', $ignorecommentschecked);
		$templater->register('imagereg', $imagereg);
		$templater->register('sortorderselected', $sortorderselected);
		$templater->register('sortselected', $sortselected);
		$templater->register('tag', $tag);
		$templater->register('tag_cloud', $tag_cloud);
		$templater->register('text', $text);
		$templater->register('username', $username);
	$content = $templater->render();
}

// build navbar
if (empty($navbits))
{
	$navbits[] = $vbphrase['blogs'];
}
else
{
	$navbits = array_merge(array('blog.php' . $vbulletin->session->vars['sessionurl_q'] => $vbphrase['blogs']), $navbits);
}
$navbits = construct_navbits($navbits);

($hook = vBulletinHook::fetch_hook('blog_search_complete')) ? eval($hook) : false;

$navbar = render_navbar_template($navbits);
$headinclude .= vB_Template::create('blog_css')->render();
$templater = vB_Template::create('BLOG');
	$templater->register_page_templates();
	$templater->register('abouturl', $abouturl);
	$templater->register('blogheader', $blogheader);
	$templater->register('bloginfo', $bloginfo);
	$templater->register('blogrssinfo', $blogrssinfo);
	$templater->register('bloguserid', $bloguserid);
	$templater->register('content', $content);
	$templater->register('navbar', $navbar);
	$templater->register('onload', $onload);
	$templater->register('pagetitle', $pagetitle);
	$templater->register('pingbackurl', $pingbackurl);
	$templater->register('sidebar', $sidebar);
	$templater->register('trackbackurl', $trackbackurl);
	$templater->register('usercss_profile_preview', $usercss_profile_preview);
print_output($templater->render());

/*======================================================================*\
|| ####################################################################
|| # SVN: $Revision: 34206 $
|| ####################################################################
\*======================================================================*/
?>