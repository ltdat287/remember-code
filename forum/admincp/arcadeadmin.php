<?php
// ######################## SET PHP ENVIRONMENT ###########################
error_reporting(E_ALL & ~E_NOTICE);
@set_time_limit(0);

// #################### PRE-CACHE TEMPLATES AND DATA ######################
$phrasegroups = array('cpuser', 'arcade', 'user');
$specialtemplates = array('arcade_bitdef');

// ########################## REQUIRE BACK-END ############################
require_once('./global.php');

// ######################## CHECK ADMIN PERMISSIONS #######################
if (!can_administer('canadminforums'))
{
	print_cp_no_permission();
}

// ####################### ARCADEADMIN.PHP FUNCTIONS ######################
function print_dots_start_arcade($text, $dotschar = ':', $elementid = 'dotsarea')
{
	if (defined('NO_IMPORT_DOTS'))
	{
		return;
	}

	vbflush(); ?>
	<p align="center"><?php echo $text; ?><br /><br />[<span style="color:yellow; font-weight:bold" id="<?php echo $elementid; ?>"><?php echo $dotschar; ?></span>]</p>
	<script type="text/javascript"><!--
	function js_dots()
	{
		document.getElementById('<?php echo $elementid; ?>').innerHTML = document.getElementById('<?php echo $elementid; ?>').innerHTML + "<?php echo $dotschar; ?>";
		jstimer = setTimeout("js_dots();", 75);
	}

	js_dots();

	//-->
	</script>
	<?php vbflush();
}

function print_dots_stop_arcade($elementid = 'dotsarea')
{
	if (defined('NO_IMPORT_DOTS'))
	{
		return;
	}

	vbflush(); ?>
	<script type="text/javascript"><!--

	clearTimeout(jstimer);
	document.getElementById('<?php echo $elementid; ?>').innerHTML = $vbphrase['done'];

	//--></script>
	<?php vbflush();
}

// -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-
// v3 ARCADE LIVE LINK
// Download new content from the v3 Arcade site.
// -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-
if ($_REQUEST['do'] == 'liveinstall')
{
	$vbulletin->input->clean_array_gpc('r', array(
	'licenseid' => TYPE_NOHTML
	));
	exec_header_redirect('index.php?' . $vbulletin->session->vars['sessionurl_js'] . 'loc=' . urlencode('arcadeadmin.php?' . $vbulletin->session->vars['sessionurl_js'] . 'do=checkgames&licenseid=' . $vbulletin->GPC['licenseid']));
}

if ($_REQUEST['do'] == 'checkgames')
{
	print_cp_header($vbphrase['v3_live_link']);

	$vbulletin->input->clean_array_gpc('r', array(
	'licenseid' => TYPE_NOHTML
	));

	require_once(DIR . '/includes/class_xml.php');
	$xmlobj = new XMLparser(false, 'http://www.v3arcade.com/link/init.php?do=checkgames&licenseid=' . $vbulletin->GPC['licenseid']);

	if(!$link = $xmlobj->parse())
	{
		print_stop_message('xml_error_x_at_line_y', $xmlobj->error_string(), $xmlobj->error_line());
	}

	print_form_header('arcadeadmin', 'installgames');

	if ($link['status']['active'] == 1)
	{
		print_table_header($vbphrase['content_server_status']);
		print_cells_row(array($link['status']['message']));
		print_table_break();
	} else {
		print_cp_message($link['status']['message']);
	}

	// Installed game cache.
	$gamecache = array();
	$installedgames = $db->query_read("SELECT shortname FROM " . TABLE_PREFIX . "arcade_games AS arcade_games");
	while ($igame = $db->fetch_array($installedgames))
	{
		$gamecache[] = $igame['shortname'];
	}

	print_table_header($vbphrase['installation_list']);

	// If no games have been chosen.
	if (!$link['game'])
	{
		print_cells_row(array($vbphrase['no_games_chosen']));
		print_table_footer();
	} else {
		print_cells_row(array($vbphrase['game'], $vbphrase['status']), true);
		// There's more than one game.
		if ($link['game'][1])
		{
			foreach ($link['game'] as $key => $val)
			{
				print_cells_row(array("$val[title]<br /><dfn>$val[description]</dfn>", iif(in_array($val['shortname'], $gamecache), $vbphrase['installed'], $vbphrase['not_installed'])));
			}
		} else {
			// There's only one game.
			print_cells_row(array($link['game']['title'] . '<br /><dfn>' . $link['game']['description'] . '</dfn>', iif(in_array($link['game']['shortname'], $gamecache), $vbphrase['installed'], $vbphrase['not_installed'])));
		}
		construct_hidden_code('licenseid', $vbulletin->GPC['licenseid']);
		print_hidden_fields();
		print_submit_row($vbphrase['proceed_with_installation'], '');
	}

	print_cp_footer();
}

if ($_REQUEST['do'] == 'installgames')
{
	print_cp_header($vbphrase['v3_live_link']);
	print_dots_start_arcade($vbphrase['checking_installation_queue'], '|', 'gp1');

	$vbulletin->input->clean_array_gpc('r', array(
	'licenseid' => TYPE_NOHTML
	));

	require_once(DIR . '/includes/class_xml.php');
	$xmlobj = new XMLparser(false, 'http://www.v3arcade.com/link/init.php?do=install&licenseid=' . $vbulletin->GPC['licenseid']);

	if(!$link = $xmlobj->parse())
	{
		print_stop_message('xml_error_x_at_line_y', $xmlobj->error_string(), $xmlobj->error_line());
	}
	vbflush();
	sleep(1);

	print_dots_stop_arcade('gp1');

	if (!is_array($link['game']))
	{
		$gotopage = 'http://www.v3arcade.com/forums/games.php?do=finish';
		// print_cp_redirect($gotopage);
		echo '<p align="center" class="smallfont"><a href="' . $gotopage . '">' . $vbphrase['processing_complete_proceed'] . '</a></p>';
		echo "\n<script type=\"text/javascript\">\n";
		echo "top.location=\"$gotopage\";";
		echo "\n</script>\n";
	} else {
		$thisgame =& $link['game'];
		// Install the current game.

		print_dots_start_arcade(construct_phrase($vbphrase['checking_x_status'], $thisgame[title]), "|", 'gp2');

		if ($db->query_first("SELECT gameid, shortname FROM " . TABLE_PREFIX . "arcade_games AS arcade_games WHERE shortname='" . addslashes($thisgame['shortname']) . "' LIMIT 1"))
		{
			$thisgame['doinstall'] = 0;

			// Call home to remove this license.
			$xmlobj = new XMLparser(false, 'http://www.v3arcade.com/link/init.php?do=invalidate&licenseid=' . $vbulletin->GPC['licenseid'] . '&gameid=' . $thisgame['gameid']);
			if(!$invcheck = $xmlobj->parse())
			{
				print_stop_message('xml_error_x_at_line_y', $xmlobj->error_string(), $xmlobj->error_line());
			}

			vbflush();
			sleep(1);
			print_dots_stop_arcade('gp2');
			// The user already has this game installed.
			print_cp_message(construct_phrase($vbphrase['x_already_installed'], $thisgame['title']), 'arcadeadmin.php?do=installgames&licenseid=' . $vbulletin->GPC['licenseid'], 1);
		} else {
			$thisgame['doinstall'] = 1;

			vbflush();
			sleep(1);
			print_dots_stop_arcade('gp2');
		}

		if ($thisgame['doinstall']==1)
		{
			print_dots_start_arcade($vbphrase['downloading_game_data'], "|", 'gp3');
			vbflush();

			$real_path = realpath('./');

			// Get the next queued game.
			$contents = file_get_contents("http://www.v3arcade.com/link/init.php?do=download&licenseid=" . $vbulletin->GPC['licenseid']);

			// Get the class we're going to use to untar files.
			require_once(DIR . '/includes/class_tar.php');

			$gametar = new tar;
			if ($gametar->openTAR($contents, 1))
			{
				foreach ($gametar->files as $key => $val)
				{
					if (!strpos($val['name'], '/'))
					{
						// Figure out where this file is going.
						switch (substr($val['name'], strlen($val['name'])-4, 4))
						{
							case '.gif':
							$newfile = $real_path . '/' . $vbulletin->options['arcadeimages'] . '/' . $val['name'];
							break;
							case '.swf':
							$newfile = $real_path . '/' . $vbulletin->options['gamedir'] . '/' . $val['name'];
							break;
						}

						if ($handle = fopen($newfile, "wb") )
						{
							fputs($handle, $val['file'], strlen($val['file']));
							fclose($handle);
						}
					}

				}
			}

			print_dots_stop_arcade('gp3');

			print_dots_start_arcade(construct_phrase($vbphrase['installing_x'], $thisgame['title']), '|', 'gp4');
			$db->query_write("INSERT INTO " . TABLE_PREFIX . "arcade_games (shortname, title, description, file, width, height, stdimage, miniimage, dateadded) VALUES
			('" . addslashes($thisgame['shortname']) . "', '" . addslashes($thisgame['title']) . "', '" . addslashes($thisgame['description']) . "', '" . addslashes($thisgame['file']) . "', '$thisgame[width]', '$thisgame[height]', '" . addslashes($thisgame['stdimage']) . "', '" . addslashes($thisgame['miniimage']) . "', '" . TIMENOW . "')");
			$db->query_write("INSERT INTO " . TABLE_PREFIX . "arcade_news (newstext, newstype, datestamp) VALUES ('" . addslashes(construct_phrase($vbphrase['x_has_been_added'], $thisgame['title'])) . "', 'auto', " . TIMENOW . ")");
			print_dots_stop_arcade('gp4');

			print_cp_redirect('arcadeadmin.php?do=installgames&licenseid=' . $vbulletin->GPC['licenseid'], 2);
		}
	}

	print_cp_footer();
}

// -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-
// GAMES
// Find games and edit their permissions.
// -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-
if ($_REQUEST['do'] == 'games')
{
	$vbulletin->input->clean_array_gpc('r', array(
	'title' => TYPE_NOHTML,
	'lastsearch' => TYPE_NOHTML,
	'perpage' => TYPE_UINT,
	'pagenumber' => TYPE_UINT,
	'orderby' => TYPE_NOHTML
	));

	$showprev = false;
	$shownext = false;

	print_cp_header($vbphrase['arcade_games']);

	$gamecount = $db->query_first("
		SELECT COUNT(*) AS total
		FROM " . TABLE_PREFIX . "arcade_games AS arcade_games
		" . iif($vbulletin->GPC['title'], "WHERE title LIKE '%" . addslashes($vbulletin->GPC['title']) . "%'") . "
	");

	if (($vbulletin->GPC['pagenumber'] < 1) || ($vbulletin->GPC['lastsearch'] != $vbulletin->GPC['title']))
	{
		$vbulletin->GPC['pagenumber'] = 1;
	}

	if (!$vbulletin->GPC['perpage'])
	{
		$vbulletin->GPC['perpage'] = $vbulletin->options['gamesperpage'];
	}

	$totalpages = ceil($gamecount['total'] / $vbulletin->GPC['perpage']);
	if ($totalpages < 1)
	{
		$totalpages = 1;
	}

	$startat = ($vbulletin->GPC['pagenumber'] - 1) * $vbulletin->GPC['perpage'];

	switch($vbulletin->GPC['orderby'])
	{
		// Perhaps some more ordering at a later date.
		case 'title':
		default:
		$order = 'arcade_games.title ASC';
	}

	if ($vbulletin->GPC['pagenumber'] > 1)
	{
		$showprev = true;
	}
	if ($vbulletin->GPC['pagenumber'] < $totalpages)
	{
		$shownext = true;
	}

	$pageoptions = array();
	for ($i = 1; $i <= $totalpages; $i++)
	{
		$pageoptions["$i"] = "$vbphrase[page] $i / $totalpages";
	}

	$gameoptions = array(
	'edit' => $vbphrase['edit'],
	'delete' => $vbphrase['delete']
	);

	?>
	<script type="text/javascript">
	<!--
	function js_game_jump(gameid)
	{

		action = eval("document.cpform.g" + gameid + ".options[document.cpform.g" + gameid + ".selectedIndex].value");

		switch (action)
		{
			case 'edit': page = "arcadeadmin.php?do=editgame&gameid="; break;
			case 'delete': page = "arcadeadmin.php?do=deletegame&gameid=";
			confirmdelete = confirm('<?php echo $vbphrase['are_you_sure_delete_game']; ?>');
			if (confirmdelete!=true)
			{
				return;
			}
			break;
		}
		document.cpform.reset();
		jumptopage = page + gameid + "&s=<?php echo $vbulletin->session->vars['sessionhash']; ?>";

		window.location = jumptopage;
	}
	-->
	</script>
	<?php

	print_form_header('arcadeadmin', 'games', false, true, 'navform', '90%', '', true, 'get');
	echo '
	<colgroup span="5">
		<col style="white-space:nowrap"></col>
		<col></col>
		<col width="100%" align="center"></col>
		<col style="white-space:nowrap"></col>
		<col></col>
	</colgroup>
	<tr>
		<td class="thead" nowrap>' . $vbphrase['game_search'] . ':<input type="hidden" name="lastsearch" value="' . $vbulletin->GPC['title'] . '" /></td>
		<td class="thead"><input type="text" name="title" class="bginput" tabindex="1" value="' . $vbulletin->GPC['title'] . '" /></td>
		<td class="thead">' .
	'<input type="button"' . iif(!$showprev, ' disabled="disabled"') . ' class="button" value="&laquo; ' . $vbphrase['prev'] . '" tabindex="1" onclick="this.form.page.selectedIndex -= 1; this.form.submit()" />' .
	'<select name="page" tabindex="1" onchange="this.form.submit()" class="bginput">' . construct_select_options($pageoptions, $vbulletin->GPC['pagenumber']) . '</select>' .
	'<input type="button"' . iif(!$shownext, ' disabled="disabled"') . ' class="button" value="' . $vbphrase['next'] . ' &raquo;" tabindex="1" onclick="this.form.page.selectedIndex += 1; this.form.submit()" />
		</td>
		<td class="thead" nowrap>' . $vbphrase['games_per_page'] . ':</td>
		<td class="thead"><input type="text" class="bginput" name="perpage" value="' . $vbulletin->GPC['perpage'] . '" tabindex="1" size="5" /></td>
		<td class="thead"><input type="submit" class="button" value=" ' . $vbphrase['go'] . ' " tabindex="1" accesskey="s" /></td>
	</tr>';

	print_table_footer();

	print_form_header('arcadeadmin');
	print_table_header($vbphrase['arcade_games'], 5);

	$cell[] = $vbphrase['game'];
	$cell[] = $vbphrase['high_scorer'];
	$cell[] = $vbphrase['high_score'];
	$cell[] = $vbphrase['times_played'];
	$cell[] = $vbphrase['options'];
	print_cells_row($cell, true);

	$games = $db->query_read("SELECT arcade_games.*, user.username FROM " . TABLE_PREFIX . "arcade_games AS arcade_games
	LEFT JOIN " . TABLE_PREFIX . "user AS user ON (arcade_games.highscorerid=user.userid)
	WHERE arcade_games.title LIKE '%" . addslashes($vbulletin->GPC['title']) . "%' ORDER BY $order LIMIT $startat, " . $vbulletin->GPC['perpage']);
	while ($game = $db->fetch_array($games))
	{
		unset($cell);
		$cell[] = '<img src="../' . $vbulletin->options['arcadeimages'] . '/' . $game['miniimage'] . '" align="absmiddle" /> ' . $game['title'];
		$cell[] = $game['username'];
		$cell[] = sprintf((float)$game['highscore']);
		$cell[] = $game['sessioncount'];
		$cell[] = "<select name=\"g$game[gameid]\" onchange=\"js_game_jump($game[gameid]);\" class=\"bginput\">\n" . construct_select_options($gameoptions) . "\t</select><input type=\"button\" class=\"button\" value=\"" . $vbphrase['go'] . "\" onclick=\"js_game_jump($game[gameid]);\" />";
		print_cells_row($cell);
	}

	print_table_footer();

	print_cp_footer();
}

// -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-
// EDIT GAME
// -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-
if ($_REQUEST['do'] == 'editgame')
{
	$vbulletin->input->clean_array_gpc('r', array(
	'gameid' => TYPE_UINT
	));

	print_cp_header($vbphrase['arcade_games']);

	if ($game = $db->query_first("SELECT * FROM " . TABLE_PREFIX . "arcade_games AS arcade_games WHERE gameid=" . $vbulletin->GPC['gameid']))
	{
		$game['permissions'] = convert_bits_to_array($game['gamepermissions'], $vbulletin->bf_misc_gamepermissions);

		print_form_header('arcadeadmin', 'doeditgame');
		print_table_header(construct_phrase($vbphrase['editing_game_x'], $game['title']));

		print_input_row($vbphrase['game_title'], 'title', $game['title']);
		print_textarea_row($vbphrase['game_description'], 'description', $game['description']);

		$catoptions = array();
		$categories = $db->query_read("SELECT * FROM " . TABLE_PREFIX . "arcade_categories AS arcade_categories WHERE categoryid<>2 ORDER BY displayorder ASC");
		while ($category = $db->fetch_array($categories))
		{
			$catoptions[$category['categoryid']] = $category['catname'];
		}

		print_select_row($vbphrase['game_category'], 'categoryid', $catoptions, $game['categoryid']);

		print_input_row($vbphrase['width'] . $vbphrase['width_dfn'], 'width', $game['width']);
		print_input_row($vbphrase['height'] . $vbphrase['height_dfn'], 'height', $game['height']);

		$cell = array(
		$vbphrase['game_icons'],
		'<div align="left"><img src="../' . $vbulletin->options['arcadeimages'] . '/' . $game['stdimage'] . '" />' . ' <img src="../' . $vbulletin->options['arcadeimages'] . '/' . $game['miniimage'] . '" /></div>'
		);
		print_cells_row($cell);

		print_yes_no_row($vbphrase['is_active'] . $vbphrase['is_active_dfn'], 'gamepermissions[isactive]', $game['permissions']['isactive']);
		print_yes_no_row($vbphrase['show_award'], 'gamepermissions[showaward]', $game['permissions']['showaward']);
		print_yes_no_row($vbphrase['enable_challenges'], 'gamepermissions[enablechallenges]', $game['permissions']['enablechallenges']);
		print_yes_no_row($vbphrase['use_reverse'], 'isreverse', $game['isreverse']);
		
		// vbBux Integration
		if ($vbulletin->options['vbbux_pointsfield'])
		{
			print_input_row($vbphrase['game_cost'], 'cost', $game['cost']);
		}

		print_table_break();

		print_table_header($vbphrase['access_restrictions']);
		print_input_row($vbphrase['minpoststotal'], 'minpoststotal', $game['minpoststotal']);
		print_input_row($vbphrase['minpostsperday'], 'minpostsperday', $game['minpostsperday']);
		print_input_row($vbphrase['minpoststhisday'], 'minpoststhisday', $game['minpoststhisday']);
		print_input_row($vbphrase['minreglength'], 'minreglength', $game['minreglength']);
		print_input_row($vbphrase['minrep'], 'minrep', $game['minrep']);

		construct_hidden_code('gameid', $vbulletin->GPC['gameid']);
		print_hidden_fields();

		print_submit_row($vbphrase['save'], '');
	}

	print_cp_footer();
}

// -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-
// DO EDIT GAME
// -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-
if ($_REQUEST['do'] == 'doeditgame')
{
	$vbulletin->input->clean_array_gpc('r', array(
	'gameid' => TYPE_UINT,
	'title' => TYPE_STR,
	'description' => TYPE_STR,
	'width' => TYPE_UINT,
	'height' => TYPE_UINT,
	'gamepermissions' => TYPE_ARRAY_BOOL,
	'isreverse' => TYPE_UINT,
	'minpoststotal' => TYPE_UINT,
	'minpostsperday' => TYPE_UINT,
	'minpoststhisday' => TYPE_UINT,
	'minreglength' => TYPE_UINT,
	'minrep' => TYPE_UINT,
	'categoryid' => TYPE_UINT,
	'cost' => TYPE_NUM
	));

	// Get that bitfield value calculated.
	require_once(DIR . '/includes/functions_misc.php');
	$vbulletin->GPC['gamepermissions'] = convert_array_to_bits($vbulletin->GPC['gamepermissions'], $vbulletin->bf_misc_gamepermissions);

	$db->query_write("UPDATE " . TABLE_PREFIX . "arcade_games SET
	title='" . addslashes($vbulletin->GPC['title']) . "',
	description='" . addslashes($vbulletin->GPC['description']) . "',
	width='" . $vbulletin->GPC['width'] . "',
	height='" . $vbulletin->GPC['height'] . "',
	gamepermissions='" . $vbulletin->GPC['gamepermissions'] . "',
	minpoststotal='" . $vbulletin->GPC['minpoststotal'] . "',
	minpostsperday='" . $vbulletin->GPC['minpostsperday'] . "',
	minpoststhisday='" . $vbulletin->GPC['minpoststhisday'] . "',
	minreglength='" . $vbulletin->GPC['minreglength'] . "',
	minrep='" . $vbulletin->GPC['minrep'] . "',
	categoryid='" . $vbulletin->GPC['categoryid'] . "',
	isreverse='" . $vbulletin->GPC['isreverse'] . "',
	cost='" . $vbulletin->GPC['cost'] . "'
	WHERE gameid=" . $vbulletin->GPC['gameid']);

	print_cp_redirect('arcadeadmin.php?do=games');
}

// -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-
// DELETE GAME
// -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-
if ($_REQUEST['do'] == 'deletegame')
{
	print_cp_header($vbphrase['arcade_games']);

	$vbulletin->input->clean_array_gpc('r', array(
	'gameid' => TYPE_UINT
	));

	if ($thisgame = $db->query_first("SELECT * FROM " . TABLE_PREFIX . "arcade_games AS arcade_games WHERE gameid=" . $vbulletin->GPC['gameid']))
	{
		$game_path = realpath($vbulletin->options['gamedir']);
		$image_path = realpath($vbulletin->options['arcadeimages']);

		$db->query_write("DELETE FROM " . TABLE_PREFIX . "arcade_games WHERE gameid=" . $vbulletin->GPC['gameid']);
		$db->query_write("DELETE FROM " . TABLE_PREFIX . "arcade_sessions WHERE gameid=" . $vbulletin->GPC['gameid']);

		@unlink($image_path . '/' . $thisgame['shortname'] . '1.gif');
		@unlink($image_path . '/' . $thisgame['shortname'] . '2.gif');
		@unlink($game_path . '/' . $thisgame['shortname'] . '.swf');
	}
	print_cp_redirect('arcadeadmin.php?do=games');
}


// -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-
// GAME TOOLS
// Everything to do with adding games.
// -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-
if ($_REQUEST['do'] == 'gametools')
{
	print_cp_header($vbphrase['arcade_games']);

	print_form_header('arcadeadmin', 'v3import');

	print_table_header($vbphrase['v3_import']);
	print_description_row($vbphrase['v3_importnote']);
	print_input_row($vbphrase['file_path'], 'filepath', 'admincp/games');
	print_input_row($vbphrase['games_per_page'] . $vbphrase['games_per_page_process_dfn'], 'gamesperpage', 10);
	print_submit_row($vbphrase['start'], '');

	print_form_header('arcadeadmin', 'ibimport');
	print_table_header($vbphrase['ib_import']);
	print_description_row($vbphrase['ib_importnote']);
	print_input_row($vbphrase['file_path'], 'filepath', 'admincp/games');
	print_input_row($vbphrase['games_per_page'] . $vbphrase['games_per_page_process_dfn'], 'gamesperpage', 10);
	print_submit_row($vbphrase['start'], '');

	print_form_header('arcadeadmin', 'processgames');
	print_table_header($vbphrase['mass_process_game_settings']);
	print_description_row($vbphrase['mass_process_game_settings_note']);
	print_input_row($vbphrase['games_per_page'] . $vbphrase['games_per_page_dfn'], 'perpage', $vbulletin->options['gamesperpage']);

	print_submit_row($vbphrase['start'], '');

	print_cp_footer();
}

// -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-
// v3 IMPORT
// -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-
if ($_REQUEST['do'] == 'v3import')
{
	print_cp_header($vbphrase['arcade_games']);

	$vbulletin->input->clean_array_gpc('r', array(
	'filepath' 	=> TYPE_STR,
	'gamesperpage' => TYPE_UINT
	));

	$games = array();
	$counter = 0;
	$gamecache = array();
	$installlog = '';

	$gamequery = $db->query_read("SELECT shortname, title FROM " . TABLE_PREFIX . "arcade_games AS arcade_games");
	while ($gameresult = $db->fetch_array($gamequery))
	{
		$gamecache[$gameresult['shortname']] = $gameresult['title'];
	}

	$real_path = realpath($vbulletin->GPC['filepath']);
	$d = dir($real_path);
	while (($filename = $d->read()) AND ($counter<$vbulletin->GPC['gamesperpage'])) {
		if (substr($filename, strlen($filename)-9, 9) == '.game.php')
		{
			$thisgame['shortname'] = substr($filename, 0, strlen($filename)-9);

			if ($gamecache[$thisgame['shortname']])
			{
				$installlog .= construct_phrase($vbphrase['already_installed_x_removed'], $gamecache[$thisgame['shortname']]) . '<br />';
				@unlink($real_path . '/' . $filename);
				@unlink($real_path . '/' . $thisgame['shortname'] . '1.gif');
				@unlink($real_path . '/' . $thisgame['shortname'] . '2.gif');
				@unlink($real_path . '/' . $thisgame['shortname'] . '.swf');
			} else {

				// Read file.
				$thisgame['file'] = file_get_contents($real_path . '/' . $filename);

				// Check type.
				if (strpos($thisgame['file'], '$title=\''))
				{
					// Non Standard 1
					preg_match_all("/title='(.*?)';.*description='(.*?)';.*game_width='(.*?)';.*game_height='(.*?)';/s", $thisgame['file'], $thisgame['regex']);
					$thisgame['title'] = stripslashes($thisgame['regex'][1][0]);
					$thisgame['description'] = stripslashes($thisgame['regex'][2][0]);
					$thisgame['width'] = intval($thisgame['regex'][3][0]);
					$thisgame['height'] = intval($thisgame['regex'][4][0]);
					$thisgame['stdimage'] = $thisgame['shortname'] . '1.gif';
					$thisgame['miniimage'] = $thisgame['shortname'] . '2.gif';
					$thisgame['file'] = $thisgame['shortname'] . '.swf';

					@copy($real_path . '/' . $thisgame['file'], $real_path . '/../../' . $vbulletin->options['gamedir'] . '/' . $thisgame['file']);
					@copy($real_path . '/' . $thisgame['stdimage'], $real_path . '/../../' . $vbulletin->options['arcadeimages'] . '/' . $thisgame['stdimage']);
					@copy($real_path . '/' . $thisgame['miniimage'], $real_path . '/../../' . $vbulletin->options['arcadeimages'] . '/' . $thisgame['miniimage']);

					@unlink($real_path . '/' . $filename);
					@unlink($real_path . '/' . $thisgame['shortname'] . '1.gif');
					@unlink($real_path . '/' . $thisgame['shortname'] . '2.gif');
					@unlink($real_path . '/' . $thisgame['shortname'] . '.swf');

					$db->query_write("INSERT INTO " . TABLE_PREFIX . "arcade_games (shortname, title, description, file, width, height, stdimage, miniimage, dateadded) VALUES
					('" . addslashes($thisgame['shortname']) . "', '" . addslashes($thisgame['title']) . "', '" . addslashes($thisgame['description']) . "', '" . addslashes($thisgame['file']) . "', '$thisgame[width]', '$thisgame[height]', '" . addslashes($thisgame['stdimage']) . "', '" . addslashes($thisgame['miniimage']) . "', '" . TIMENOW . "')");

					$installlog .= construct_phrase($vbphrase['installed_x'], $thisgame['title']) . '<br />';

				} else if (strpos($thisgame['file'], '(shortname, gameid, title, descr, file, width, height, miniimage, stdimage, gamesettings, highscorerid, highscore)'))
				{
					// Standard 1
					preg_match_all("/games \(shortname, gameid, title, descr, file, width, height, miniimage, stdimage, gamesettings, highscorerid, highscore\) VALUES \('.*?', NULL, '(.*?)', '(.*?)', '.*?', (.*?), (.*?),/", $thisgame['file'], $thisgame['regex']);
					$thisgame['title'] = stripslashes($thisgame['regex'][1][0]);
					$thisgame['description'] = stripslashes($thisgame['regex'][2][0]);
					$thisgame['width'] = intval($thisgame['regex'][3][0]);
					$thisgame['height'] = intval($thisgame['regex'][4][0]);
					$thisgame['stdimage'] = $thisgame['shortname'] . '1.gif';
					$thisgame['miniimage'] = $thisgame['shortname'] . '2.gif';
					$thisgame['file'] = $thisgame['shortname'] . '.swf';

					@copy($real_path . '/' . $thisgame['file'], $real_path . '/../../' . $vbulletin->options['gamedir'] . '/' . $thisgame['file']);
					@copy($real_path . '/' . $thisgame['stdimage'], $real_path . '/../../' . $vbulletin->options['arcadeimages'] . '/' . $thisgame['stdimage']);
					@copy($real_path . '/' . $thisgame['miniimage'], $real_path . '/../../' . $vbulletin->options['arcadeimages'] . '/' . $thisgame['miniimage']);

					@unlink($real_path . '/' . $filename);
					@unlink($real_path . '/' . $thisgame['shortname'] . '1.gif');
					@unlink($real_path . '/' . $thisgame['shortname'] . '2.gif');
					@unlink($real_path . '/' . $thisgame['shortname'] . '.swf');

					$db->query_write("INSERT INTO " . TABLE_PREFIX . "arcade_games (shortname, title, description, file, width, height, stdimage, miniimage, dateadded) VALUES
					('" . addslashes($thisgame['shortname']) . "', '" . addslashes($thisgame['title']) . "', '" . addslashes($thisgame['description']) . "', '" . addslashes($thisgame['file']) . "', '$thisgame[width]', '$thisgame[height]', '" . addslashes($thisgame['stdimage']) . "', '" . addslashes($thisgame['miniimage']) . "', '" . TIMENOW . "')");

					$installlog .= construct_phrase($vbphrase['installed_x'], $thisgame['title']) . '<br />';

				} else {
					// Unknown
					@unlink($real_path . '/' . $filename);
					@unlink($real_path . '/' . $thisgame['shortname'] . '1.gif');
					@unlink($real_path . '/' . $thisgame['shortname'] . '2.gif');
					@unlink($real_path . '/' . $thisgame['shortname'] . '.swf');
				}
			}


			$counter++;
		}
	}
	$d->close();

	print_form_header('arcadeadmin', 'v3import');

	if (!$installlog)
	{
		print_table_header($vbphrase['importing_games']);
		$installlog = $vbphrase['no_more_games_to_import'];
		print_description_row($installlog);
		print_table_footer();
	} else {
		print_table_header($vbphrase['importing_games']);
		// Handle hidden fields
		construct_hidden_code('gamesperpage', $vbulletin->GPC['gamesperpage']);
		construct_hidden_code('filepath', $vbulletin->GPC['filepath']);
		print_description_row($installlog);
		print_hidden_fields();
		print_submit_row($vbphrase['next'], '');
	}

	print_cp_footer();

}

// -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-
// iB IMPORT
// -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-
if ($_REQUEST['do'] == 'ibimport')
{
	print_cp_header($vbphrase['arcade_games']);

	// Get the class we're going to use to untar files.
	require_once(DIR . '/includes/class_tar.php');

	$vbulletin->input->clean_array_gpc('r', array(
	'filepath' 	=> TYPE_STR,
	'gamesperpage' => TYPE_UINT
	));

	$games = array();
	$counter = 0;
	$gamecache = array();
	$installlog = '';

	$gamequery = $db->query_read("SELECT shortname, title FROM " . TABLE_PREFIX . "arcade_games AS arcade_games");
	while ($gameresult = $db->fetch_array($gamequery))
	{
		$gamecache[$gameresult['shortname']] = $gameresult['title'];
	}

	$real_path = realpath($vbulletin->GPC['filepath']);
	$d = dir($real_path);
	while (($filename = $d->read()) AND ($counter<$vbulletin->GPC['gamesperpage'])) {

		if (substr($filename, strlen($filename)-4, 4) == '.tar')
		{
			$gametar = new tar;
			if ($gametar->openTAR($vbulletin->GPC['filepath'] . '/' . $filename))
			{
				foreach ($gametar->files as $key => $val)
				{
					if (!strpos($val['name'], '/'))
					{
						// Figure out where this file is going.
						switch (substr($val['name'], strlen($val['name'])-4, 4))
						{
							case '.gif':
							$newfile = $real_path . '/../../' . $vbulletin->options['arcadeimages'] . '/' . $val['name'];
							break;
							case '.swf':
							$newfile = $real_path . '/../../' . $vbulletin->options['gamedir'] . '/' . $val['name'];
							break;
							case '.php':
							$newfile = $real_path . '/' . $val['name'];
							$val['isphp'] = true;
							break;
						}

						if ($handle = fopen($newfile, "wb") )
						{
							fputs($handle, $val['file'], strlen($val['file']));
							fclose($handle);
						}

						if ($val['isphp'] == true)
						{
							require_once($real_path . '/' . $val['name']);
							if (is_array($config))
							{
								$thisgame['title'] = stripslashes($config['gtitle']);
								$thisgame['description'] = stripslashes($config['gwords']);
								$thisgame['width'] = intval($config['gwidth']);
								$thisgame['height'] = intval($config['gheight']);
								$thisgame['stdimage'] = $config['gname'] . '1.gif';
								$thisgame['miniimage'] = $config['gname'] . '2.gif';
								$thisgame['file'] = $config['gname'] . '.swf';

								if ($gamecache[$config['gname']])
								{
									@unlink($newfile);

									// Get rid of the tar file for good.
									@unlink($real_path . '/' . $filename);
									$installlog .= construct_phrase($vbphrase['already_installed_x_removed'], $gamecache[$thisgame['shortname']]) . '<br />';
								} else {
									$db->query_write("INSERT INTO " . TABLE_PREFIX . "arcade_games (shortname, title, description, file, width, height, stdimage, miniimage, dateadded, system) VALUES
									('" . addslashes($config['gname']) . "', '" . addslashes($thisgame['title']) . "', '" . addslashes($thisgame['description']) . "', '" . addslashes($thisgame['file']) . "', '$thisgame[width]', '$thisgame[height]', '" . addslashes($thisgame['stdimage']) . "', '" . addslashes($thisgame['miniimage']) . "', '" . TIMENOW . "', 10)");

									$installlog .= construct_phrase($vbphrase['installed_x'], $thisgame['title']) . '<br />';
								}
								@unlink($newfile);
							}
						}
					}

				}
			}

			$counter++;
		}
		@unlink($real_path . '/' . $filename);
	}
	$d->close();

	print_form_header('arcadeadmin', 'ibimport');

	if (!$installlog)
	{
		print_table_header($vbphrase['importing_games']);
		$installlog = $vbphrase['no_more_games_to_import'];
		print_description_row($installlog);
		print_table_footer();
	} else {
		print_table_header($vbphrase['importing_games']);
		// Handle hidden fields
		construct_hidden_code('gamesperpage', $vbulletin->GPC['gamesperpage']);
		construct_hidden_code('filepath', $vbulletin->GPC['filepath']);
		print_description_row($installlog);
		print_hidden_fields();
		print_submit_row($vbphrase['next'], '');
	}

	print_cp_footer();

}

// -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-
// MASS SET SETTINGS
// Easy and relatively easy permission/setting editing.
// -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-
if ($_REQUEST['do'] == 'processgames')
{
	print_cp_header($vbphrase['arcade_games']);

	$vbulletin->input->clean_array_gpc('r', array(
	'title' => TYPE_NOHTML,
	'lastsearch' => TYPE_NOHTML,
	'perpage' => TYPE_UINT,
	'pagenumber' => TYPE_UINT,
	'orderby' => TYPE_NOHTML,
	'finished' => TYPE_UINT,
	'newtitle' => TYPE_ARRAY_STR,
	'description' => TYPE_ARRAY_STR,
	'width' => TYPE_ARRAY_UINT,
	'height' => TYPE_ARRAY_UINT,
	'isreverse' => TYPE_ARRAY_UINT,
	'categoryid' => TYPE_ARRAY_UINT,
	'minpoststotal' => TYPE_ARRAY_UINT,
	'minpostsperday' => TYPE_ARRAY_UINT,
	'minpoststhisday' => TYPE_ARRAY_UINT,
	'minreglength' => TYPE_ARRAY_UINT,
	'minrep' => TYPE_ARRAY_UINT,
	'gamesarray' => TYPE_ARRAY_UINT
	));

	$catoptions = array();
	$categories = $db->query_read("SELECT * FROM " . TABLE_PREFIX . "arcade_categories AS arcade_categories WHERE categoryid<>2 ORDER BY displayorder ASC");
	while ($category = $db->fetch_array($categories))
	{
		$catoptions[$category['categoryid']] = $category['catname'];
	}

	// If we've been passed gamesarray, find out which bitfield arrays we need to clean.
	if ($vbulletin->GPC['gamesarray'])
	{
		require_once(DIR . '/includes/functions_misc.php');

		// GPC temp storage.
		$gpcstore = array();
		foreach ($vbulletin->GPC['gamesarray'] as $id => $val)
		{
			$gpcstore['gp_' . $id] = TYPE_ARRAY_BOOL;
		}

		// Perform this cleaning.
		$vbulletin->input->clean_array_gpc('r', $gpcstore);

		// And now we save.
		foreach ($vbulletin->GPC['gamesarray'] as $id => $val)
		{
			// Get that bitfield value calculated.
			$vbulletin->GPC['gamepermissions'][$id] = convert_array_to_bits($vbulletin->GPC['gp_' . $id], $vbulletin->bf_misc_gamepermissions);

			$db->query_write("UPDATE " . TABLE_PREFIX . "arcade_games SET
			title='" . addslashes($vbulletin->GPC['newtitle'][$id]) . "',
			description='" . addslashes($vbulletin->GPC['description'][$id]) . "',
			width='" . $vbulletin->GPC['width'][$id] . "',
			height='" . $vbulletin->GPC['height'][$id] . "',
			gamepermissions='" . $vbulletin->GPC['gamepermissions'][$id] . "',
			isreverse='" . $vbulletin->GPC['isreverse'][$id] . "',
			minpoststotal='" . $vbulletin->GPC['minpoststotal'][$id] . "',
			minpostsperday='" . $vbulletin->GPC['minpostsperday'][$id] . "',
			minpoststhisday='" . $vbulletin->GPC['minpoststhisday'][$id] . "',
			minreglength='" . $vbulletin->GPC['minreglength'][$id] . "',
			minrep='" . $vbulletin->GPC['minrep'][$id] . "',
			categoryid='" . $vbulletin->GPC['categoryid'][$id] . "'
			WHERE gameid=$id
			");
		}
	}

	if ($vbulletin->GPC['finished']==1)
	{
		print_cp_redirect('arcadeadmin.php?do=gametools');
	}

	$showprev = false;
	$shownext = false;

	$gamecount = $db->query_first("
		SELECT COUNT(*) AS total
		FROM " . TABLE_PREFIX . "arcade_games AS arcade_games
		" . iif($vbulletin->GPC['title'], "WHERE title LIKE '%" . addslashes($vbulletin->GPC['title']) . "%'") . "
	");

	if (($vbulletin->GPC['pagenumber'] < 1) || ($vbulletin->GPC['lastsearch'] != $vbulletin->GPC['title']))
	{
		$vbulletin->GPC['pagenumber'] = 1;
	}

	if (!$vbulletin->GPC['perpage'])
	{
		$vbulletin->GPC['perpage'] = $vbulletin->options['gamesperpage'];
	}

	$totalpages = ceil($gamecount['total'] / $vbulletin->GPC['perpage']);
	if ($totalpages < 1)
	{
		$totalpages = 1;
	}

	$startat = ($vbulletin->GPC['pagenumber'] - 1) * $vbulletin->GPC['perpage'];

	switch($vbulletin->GPC['orderby'])
	{
		// Perhaps some more ordering at a later date.
		case 'title':
		default:
		$order = 'arcade_games.title ASC';
	}

	if ($vbulletin->GPC['pagenumber'] > 1)
	{
		$showprev = true;
	}
	if ($vbulletin->GPC['pagenumber'] < $totalpages)
	{
		$shownext = true;
	}

	$pageoptions = array();
	for ($i = 1; $i <= $totalpages; $i++)
	{
		$pageoptions["$i"] = "$vbphrase[page] $i / $totalpages";
	}

	print_form_header('arcadeadmin', 'processgames', false, true, 'navform', '90%', '', true, 'get');
	echo '
	<colgroup span="5">
		<col style="white-space:nowrap"></col>
		<col></col>
		<col width="100%" align="center"></col>
		<col style="white-space:nowrap"></col>
		<col></col>
	</colgroup>
	<tr>
		<td class="thead" nowrap>' . $vbphrase['game_search'] . ':<input type="hidden" name="lastsearch" value="' . $vbulletin->GPC['title'] . '" /></td>
		<td class="thead"><input type="text" name="title" class="bginput" tabindex="1" value="' . $vbulletin->GPC['title'] . '" /></td>
		<td class="thead">' .
	'<input type="button"' . iif(!$showprev, ' disabled="disabled"') . ' class="button" value="&laquo; ' . $vbphrase['prev'] . '" tabindex="1" onclick="this.form.page.selectedIndex -= 1; this.form.submit()" />' .
	'<select name="page" tabindex="1" onchange="this.form.submit()" class="bginput">' . construct_select_options($pageoptions, $vbulletin->GPC['pagenumber']) . '</select>' .
	'<input type="button"' . iif(!$shownext, ' disabled="disabled"') . ' class="button" value="' . $vbphrase['next'] . ' &raquo;" tabindex="1" onclick="this.form.page.selectedIndex += 1; this.form.submit()" />
		</td>
		<td class="thead" nowrap>' . $vbphrase['games_per_page'] . ':</td>
		<td class="thead"><input type="text" class="bginput" name="perpage" value="' . $vbulletin->GPC['perpage'] . '" tabindex="1" size="5" /></td>
		<td class="thead"><input type="submit" class="button" value=" ' . $vbphrase['go'] . ' " tabindex="1" accesskey="s" /></td>
	</tr>';

	print_table_footer();

	print_form_header('arcadeadmin', 'processgames');
	print_table_header($vbphrase['arcade_games'], 5);

	$games = $db->query_read("SELECT arcade_games.*, user.username FROM " . TABLE_PREFIX . "arcade_games AS arcade_games
	LEFT JOIN " . TABLE_PREFIX . "user AS user ON (arcade_games.highscorerid=user.userid)
	WHERE arcade_games.title LIKE '%" . addslashes($vbulletin->GPC['title']) . "%' ORDER BY $order LIMIT $startat, " . $vbulletin->GPC['perpage']);
	while ($game = $db->fetch_array($games))
	{
		$game['permissions'] = convert_bits_to_array($game['gamepermissions'], $vbulletin->bf_misc_gamepermissions);

		echo '<tr><td class="thead" colspan="2"><img src="../' . $vbulletin->options['arcadeimages'] . '/' . $game['miniimage'] . '" align="absmiddle" /> ' . $game['title'] . '</td></tr>';

		print_input_row($vbphrase['game_title'], "newtitle[$game[gameid]]", $game['title']);
		print_textarea_row($vbphrase['game_description'], "description[$game[gameid]]", $game['description']);
		print_select_row($vbphrase['game_category'], "categoryid[$game[gameid]]", $catoptions, $game['categoryid']);
		print_input_row($vbphrase['width'] . $vbphrase['width_dfn'], "width[$game[gameid]]", $game['width']);
		print_input_row($vbphrase['height'] . $vbphrase['height_dfn'], "height[$game[gameid]]", $game['height']);

		print_yes_no_row($vbphrase['is_active'] . $vbphrase['is_active_dfn'], 'gp_' . $game['gameid'] . '[isactive]', $game['permissions']['isactive']);
		print_yes_no_row($vbphrase['show_award'], 'gp_' . $game['gameid'] . '[showaward]', $game['permissions']['showaward']);
		print_yes_no_row($vbphrase['enable_challenges'], 'gp_' . $game['gameid'] . '[enablechallenges]', $game['permissions']['enablechallenges']);
		print_yes_no_row($vbphrase['use_reverse'], "isreverse[$game[gameid]]", $game['isreverse']);

		print_input_row($vbphrase['minpoststotal'], "minpoststotal[$game[gameid]]", $game['minpoststotal']);
		print_input_row($vbphrase['minpostsperday'], "minpostsperday[$game[gameid]]", $game['minpostsperday']);
		print_input_row($vbphrase['minpoststhisday'], "minpoststhisday[$game[gameid]]", $game['minpoststhisday']);
		print_input_row($vbphrase['minreglength'], "minreglength[$game[gameid]]", $game['minreglength']);
		print_input_row($vbphrase['minrep'], "minrep[$game[gameid]]", $game['minrep']);

		construct_hidden_code("gamesarray[$game[gameid]]", 1);
	}

	construct_hidden_code('pagenumber', $vbulletin->GPC['pagenumber']+1);
	construct_hidden_code('perpage', $vbulletin->GPC['perpage']);
	construct_hidden_code('title', $vbulletin->GPC['title']);
	construct_hidden_code('lastsearch', $vbulletin->GPC['title']);
	if ($shownext == false)
	{
		construct_hidden_code('finished', 1);
	}
	print_hidden_fields();

	print_submit_row($vbphrase['save_and_continue'], '');
	print_table_footer();

	print_cp_footer();
}

// -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-
// DEFAULT USER SETTINGS
// The default settings.... for users.
// -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-
if ($_REQUEST['do'] == 'dus')
{
	print_cp_header($vbphrase['defaultusersettings']);
	
	$vbulletin->input->clean_array_gpc('r', array(
	'arcadeoptions' => TYPE_ARRAY_BOOL,
	'process' => TYPE_UINT
	));
	
	if ($vbulletin->GPC['process'])
	{
		$bittotal = 0;
		foreach ($vbulletin->bf_misc_arcadeoptions AS $key => $val)
	    { 
			if ($vbulletin->GPC['arcadeoptions'][$key])
			{
				$bittotal += $val;
			}
	    }
	    
	    build_datastore('arcade_bitdef', $bittotal);
	    $db->query_write("ALTER TABLE " . TABLE_PREFIX . "user CHANGE arcadeoptions arcadeoptions INT(10) UNSIGNED NOT NULL DEFAULT '" . $bittotal . "'");
	} else {
		$bittotal = $vbulletin->arcade_bitdef;
	}
	
    $arcade_array = convert_bits_to_array($bittotal, $vbulletin->bf_misc_arcadeoptions);
	
	print_form_header('arcadeadmin', 'dus');
	print_table_header($vbphrase['defaultusersettings']);
	
	print_description_row($vbphrase['defbitdescr']);
	print_cells_row(array($vbphrase['fields'], ''), true);
	
	print_checkbox_row($vbphrase['allow_challenges'], 'arcadeoptions[allowchallenges]', $arcade_array['allowchallenges']);
	print_checkbox_row($vbphrase['auto_accept_challenges'], 'arcadeoptions[autoaccept]', $arcade_array['autoaccept']);
	print_checkbox_row($vbphrase['email_notification'], 'arcadeoptions[useemail]', $arcade_array['useemail']);
	print_checkbox_row($vbphrase['pm_notification'], 'arcadeoptions[usepms]', $arcade_array['usepms']);
	print_checkbox_row($vbphrase['high_score_beaten'], 'arcadeoptions[highscorebeaten]', $arcade_array['highscorebeaten']);
	print_checkbox_row($vbphrase['new_challenge_received'], 'arcadeoptions[newchallenge]', $arcade_array['newchallenge']);
	print_checkbox_row($vbphrase['challenge_accepted'], 'arcadeoptions[challengeaccepted]', $arcade_array['challengeaccepted']);
	print_checkbox_row($vbphrase['challenge_declined'], 'arcadeoptions[challengedeclined]', $arcade_array['challengedeclined']);
	print_checkbox_row($vbphrase['finished_challenge'], 'arcadeoptions[finishedchallenge]', $arcade_array['finishedchallenge']);
	
	construct_hidden_code('process', 1);
	print_hidden_fields();
	
	print_submit_row($vbphrase['save'], '');
	print_table_footer();


	
	print_cp_footer();

	
}

// -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-
// CATEGORIES
// Categories contain games.
// -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-
if ($_REQUEST['do'] == 'categories')
{
	print_cp_header($vbphrase['arcade_categories']);

	$vbulletin->input->clean_array_gpc('r', array(
	'category' 	=> TYPE_ARRAY_NOHTML,
	'displayorder' 	=> TYPE_ARRAY_INT,
	'isactive' 	=> TYPE_ARRAY_INT,
	'process' => TYPE_UINT
	));

	$categoryselect = array(0 => $vbphrase['choose_a_category']);

	if ($vbulletin->GPC['process'] == 1)
	{
		$categorycache = array();

		foreach ($vbulletin->GPC['category'] as $catid => $name)
		{
			$categorycache[$catid] = array(
			'catname' => $name,
			'displayorder' => $vbulletin->GPC['displayorder'][$catid],
			'isactive' => $vbulletin->GPC['isactive'][$catid]
			);
		}
	}

	print_form_header('arcadeadmin', 'categories');
	print_table_header($vbphrase['game_categories'], 4);

	$cell[] = $vbphrase['active'];
	$cell[] = $vbphrase['order'];
	$cell[] = '<div align="left">' . $vbphrase['category_title'] . '</div>';
	$cell[] = $vbphrase['options'];
	print_cells_row($cell, true);

	$out = '';

	$categories = $db->query_read("SELECT * FROM " . TABLE_PREFIX . "arcade_categories AS arcade_categories ORDER BY displayorder ASC");
	while ($category = $db->fetch_array($categories))
	{
		if ((($categorycache[$category['categoryid']]['catname']!=$category['catname']) || ($categorycache[$category['categoryid']]['displayorder']!=$category['displayorder']) || ($categorycache[$category['categoryid']]['isactive']!=$category['isactive'])) && ($vbulletin->GPC['process'] == 1))
		{
			// Save the new data.
			$db->query_write("UPDATE " . TABLE_PREFIX . "arcade_categories SET displayorder='" . $categorycache[$category['categoryid']]['displayorder'] . "', catname='" . addslashes($categorycache[$category['categoryid']]['catname']) . "', isactive='" . addslashes($categorycache[$category['categoryid']]['isactive']) . "' WHERE categoryid=$category[categoryid]");
			$category['catname'] = $categorycache[$category['categoryid']]['catname'];
			$category['isactive'] = $categorycache[$category['categoryid']]['isactive'];
			$category['displayorder'] = $categorycache[$category['categoryid']]['displayorder'];
		}

		$categoryselect[$category['categoryid']] = $category['catname'];
		
		$isactive = iif($category['isactive']==1, ' checked ');
		exec_switch_bg();
		$out .= '<tr>';
		$out .= "<td class=\"$bgclass\" width=\"1%\" align=\"center\" nowrap><input type=\"checkbox\" name=\"isactive[$category[categoryid]]\" value=\"1\" $isactive/></td>";
		$out .= "<td class=\"$bgclass\" width=\"1%\" align=\"center\" nowrap><input type=\"text\" name=\"displayorder[$category[categoryid]]\" value=\"$category[displayorder]\" class=\"bginput\" size=\"3\" /></td>";
		$out .= "<td class=\"$bgclass\" align=\"left\"><input type=\"text\" name=\"category[$category[categoryid]]\" value=\"$category[catname]\" class=\"bginput\" /></td>";
		$out .= "<td class=\"$bgclass\" align=\"right\"><input type=\"button\" name=\"delete\" value=\"$vbphrase[delete]\" class=\"button\" onclick=\"deletecategory($category[categoryid])\"" . iif($category['categoryid']>2, '', ' disabled="disabled"') . " /></td>";
		$out .= '</tr>';
	}

	?>

	<script type="text/javascript">
	<!--
	function deletecategory(catid)
	{
		confirmdelete = confirm('<?php echo $vbphrase['are_you_sure_delete_category']; ?>');
		if (confirmdelete!=true)
		{
			return;
		}
		window.location = "arcadeadmin.php?do=deletecategory&categoryid=" + catid;
	}
	-->
	</script>
	
	<?php

	echo $out;

	construct_hidden_code('process', 1);
	print_hidden_fields();
	print_submit_row($vbphrase['save'], '', 4);

	print_form_header('arcadeadmin', 'addcategory');
	print_table_header($vbphrase['add_new_category']);
	print_input_row($vbphrase['category_title'] . $vbphrase['category_title_dfn'], 'catname');
	print_input_row($vbphrase['display_order'] . $vbphrase['display_order_dfn'], 'displayorder');
	print_submit_row($vbphrase['add_new_category'], '');

	print_cp_footer();
}

// -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-
// ADD CATEGORY
// -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-
if ($_REQUEST['do'] == 'addcategory')
{
	print_cp_header($vbphrase['arcade_categories']);

	$vbulletin->input->clean_array_gpc('r', array(
	'catname' 	=> TYPE_NOHTML,
	'displayorder' 	=> TYPE_UINT
	));

	$db->query_write("INSERT INTO " . TABLE_PREFIX . "arcade_categories (catname, displayorder) VALUES ('" . addslashes($vbulletin->GPC['catname']) . "', " . $vbulletin->GPC['displayorder'] . ")");

	print_cp_redirect('arcadeadmin.php?do=categories');
}

// -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-
// DELETE CATEGORY
// -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-
if ($_REQUEST['do'] == 'deletecategory')
{
	print_cp_header($vbphrase['arcade_categories']);

	$vbulletin->input->clean_array_gpc('r', array(
	'categoryid' => TYPE_UINT
	));

	if ($vbulletin->GPC['categoryid']<=2)
	{
		// You shouldn't be here...
		exit;
	}

	$db->query_write("DELETE FROM " . TABLE_PREFIX . "arcade_categories WHERE categoryid=" . $vbulletin->GPC['categoryid']);
	$db->query_write("UPDATE " . TABLE_PREFIX . "arcade_games SET categoryid=1 WHERE categoryid=" . $vbulletin->GPC['categoryid']);

	print_cp_redirect('arcadeadmin.php?do=categories');
}


// -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-
// SCORES
// Games contain scores.
// -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-
if ($_REQUEST['do'] == 'scores')
{
	print_cp_header($vbphrase['arcade_scores']);

	print_form_header('arcadeadmin', 'removezero');
	print_table_header($vbphrase['remove_zero_scores']);
	print_description_row($vbphrase['remove_zero_scores_dfn']);
	print_submit_row($vbphrase['start'], '');

	print_form_header('arcadeadmin', 'removeuserscores');
	print_table_header($vbphrase['remove_user_scores']);
	print_description_row($vbphrase['remove_user_scores_dfn']);
	print_input_row($vbphrase['user_id'], 'userid');
	print_submit_row($vbphrase['start'], '');

	print_cp_footer();
}

// -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-
// REMOVE ZERO SCORES
// -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-
if ($_REQUEST['do'] == 'removezero')
{
	print_cp_header($vbphrase['arcade_scores']);

	$db->query_write("DELETE FROM " . TABLE_PREFIX . "arcade_sessions WHERE score=0 AND valid=1");

	// Getting Arcade functions to rebuild the counts.
	require_once(DIR . '/includes/functions_arcade.php');
	build_games();

	print_cp_redirect('arcadeadmin.php?do=scores');
}

// -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-
// REMOVE USER SCORES
// -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-
if ($_REQUEST['do'] == 'removeuserscores')
{
	$vbulletin->input->clean_array_gpc('r', array(
	'userid' => TYPE_UINT
	));

	if (!verify_id('user', $vbulletin->GPC['userid'], false))
	{
		print_stop_message('invaliduserid', $vbulletin->GPC['userid']);
	}

	print_cp_header($vbphrase['arcade_scores']);

	$db->query_write("DELETE FROM " . TABLE_PREFIX . "arcade_sessions WHERE userid=" . $vbulletin->GPC['userid']);

	// Getting Arcade functions to rebuild the counts.
	require_once(DIR . '/includes/functions_arcade.php');
	build_games();

	print_cp_redirect('arcadeadmin.php?do=scores');
}

// -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-
// NEWS & EVENTS
// Insignificant.
// -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-
if ($_REQUEST['do'] == 'newsandevents')
{
	print_cp_header($vbphrase['arcade_newsandevents']);

	print_table_start();
	print_description_row('Coming in 0.9');
	print_table_footer();

	print_cp_footer();
}
?>