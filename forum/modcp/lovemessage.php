<?php
/*======================================================================*\
|| #################################################################### ||
|| # Love Message
|| # Author: pdtan - VietVBB.vn Administartor
|| # http://vietvbb.vn
|| #################################################################### ||
\*======================================================================*/

// ####################### SET PHP ENVIRONMENT ###########################
error_reporting(E_ALL & ~E_NOTICE);

// #################### DEFINE IMPORTANT CONSTANTS #######################
define('THIS_SCRIPT', 'love_message');
define('CSRF_PROTECTION', true);
define('CSRF_SKIP_LIST', '');

// ################### PRE-CACHE TEMPLATES AND DATA ######################
// get special phrase groups
$phrasegroups = array();

// get special data templates from the datastore
$specialtemplates = array();

// ######################### REQUIRE BACK-END ############################
require_once('./global.php');

// #######################################################################
// ######################## START MAIN SCRIPT ############################
// #######################################################################

if (!can_moderate(0, 'canbanusers'))
{
	print_stop_message('no_permission');
}
global $vbulletin;
print_cp_header('Love Message - Author: pdtan - vietvbb.vn');
if(in_array($_REQUEST['do'], array('edit','manage','update')))
{
	if(!can_administer('canadminsettings')) print_stop_message('no_permission');	
}
if($_REQUEST['do'] == 'manage')
{
	print_form_header('lovemessage', 'delete', false, true, 'delete', '90%', '', true, 'post', 0);
	print_table_header("Love Message Moderated",6);
	echo '<tr class="thead">
			<td align="center" width="8%">LM ID</td>
			<td align="center" width="15%">From</td>
			<td align="center" width="15%">To</td>
			<td align="center" width="44%">Message</td>
			<td align="center" width="10%">Time</td>
			<td align="center" width="8%"><input type="checkbox" id="allbox" onclick="return js_check_all(this.form)"></td>
		</tr>';
	$items = $db->query_read("
			SELECT id,userid,fname,tname,message,dateline 
			FROM " . TABLE_PREFIX . "love_message
			WHERE checked = 1
			ORDER BY dateline 
	");	
	$i	= 1;
	if($db->num_rows($items))
	while($item = $db->fetch_array($items))
	{
		if($i%2==0) $class = 'alt2'; else $class = 'alt1';
		$day = vbdate($vbulletin->options['dateformat'], $item['dateline'], true);
		$time = vbdate($vbulletin->options['timeformat'], $item['dateline']);
		echo '<tr class="' . $class . '">
			<td align="center" class="smallfont">' . $item['id'] . '</td>
			<td align="left" class="smallfont">' . $item['fname'] . '</td>
			<td align="left" class="smallfont">' . htmlspecialchars($item['tname']) . '</td>
			<td align="center" class="smallfont">'. htmlspecialchars($item['message']) .' <a href="lovemessage.php?do=edit&lmid=' . $item['id'] . '&type=1" title="Edit"><img title="Edit LM" alt="Edit LM" src="../images/misc/userfield_edit.gif" border="0" hspace="6"></a></td>
			<td align="center" class="smallfont">'. $day . ' ' . $time .'</td>
			<td align="center" class="smallfont"><input name="lmids[]" type="checkbox" id="lmid'.$item['id'].'" value="'.$item['id'].'"></td>
		</tr>';	
		$i++;
	}
	else echo '<tr class="alt1"><td align="center" colspan="6">No Love Message Found!</td></tr>';
	$db->free_result($items);
	print_submit_row('Delete" onclick="return confirm(\'Are you sure you want to delete?\')', 'Reset', 6, '', '', false); 
	print_cp_footer();
}


if($_REQUEST['do'] == 'delete')
{
	$lmids = $vbulletin->input->clean_gpc('p','lmids',TYPE_ARRAY_INT);
	if($lmids)
	{
		$db->query_write("DELETE FROM " . TABLE_PREFIX . "love_message WHERE id IN (" . implode(',',$lmids) . ")");
		print_cp_message  ($db->affected_rows().' LM has been deleted', 'lovemessage.php?do=manage');
	}
	else print_cp_message("You haven't selected any LM yet");
	print_cp_footer();
}
if($_REQUEST['do'] == 'domoderate')
{
	print_form_header('lovemessage', 'moderate', false, true, 'moderate', '90%', '', true, 'post', 0);
	print_table_header("Love Message Waiting For Valid Moderation",6);
	echo '<tr class="thead">
			<td align="center" width="8%">LM ID</td>
			<td align="center" width="15%">From</td>
			<td align="center" width="15%">To</td>
			<td align="center" width="44%">Message</td>
			<td align="center" width="10%">Time</td>
			<td align="center" width="8%"><input type="checkbox" id="allbox" onclick="return js_check_all(this.form)"></td>
		</tr>';
	$items = $db->query_read("
			SELECT id,userid,fname,tname,message,dateline 
			FROM " . TABLE_PREFIX . "love_message
			WHERE checked = 0
			ORDER BY dateline 
	");	
	$i	= 1;
	if($db->num_rows($items))
	while($item = $db->fetch_array($items))
	{
		if($i%2==0) $class = 'alt2'; else $class = 'alt1';
		$day = vbdate($vbulletin->options['dateformat'], $item['dateline'], true);
		$time = vbdate($vbulletin->options['timeformat'], $item['dateline']);
		echo '<tr class="' . $class . '">
			<td align="center" class="smallfont">' . $item['id'] . '</td>
			<td align="left" class="smallfont">' . $item['fname'] . '</td>
			<td align="left" class="smallfont">' . htmlspecialchars($item['tname']) . '</td>
			<td align="center" class="smallfont">'. htmlspecialchars($item['message']) .' <a href="lovemessage.php?do=edit&lmid=' . $item['id'] . '&type=2" title="Edit"><img title="Edit LM" alt="Edit LM" src="../images/misc/userfield_edit.gif" border="0" hspace="6"></a></td>
			<td align="center" class="smallfont">'. $day . ' ' . $time .'</td>
			<td align="center" class="smallfont"><input name="lmids[]" type="checkbox" id="lmid'.$item['id'].'" value="'.$item['id'].'"></td>
		</tr>';	
		$i++;
	}
	else echo '<tr class="alt1"><td align="center" colspan="6">No Love Message Found!</td></tr>';
	$db->free_result($items);
	echo '<tr><td class="tfoot" colspan="6" align="center">	
	<input type="submit" id="submit0" class="button" tabindex="1" value="Moderate" accesskey="s" name="submit">
	<input type="submit" value="Delete" onclick="return confirm(\'Are you sure you want to delete?\')" class="button" name="submit">
	<input type="reset" id="reset0" class="button" tabindex="1" value=" Reset  " accesskey="r"></td></tr></table></form>';
	print_cp_footer();
}
if($_REQUEST['do']=='moderate')
{
	$lmids = $vbulletin->input->clean_gpc('p','lmids',TYPE_ARRAY_INT);
	$submit = $vbulletin->input->clean_gpc('p','submit',TYPE_STR);
	if($submit == 'Delete')
	{
		if($lmids)
		{
			$db->query_write("DELETE FROM " . TABLE_PREFIX . "love_message WHERE id IN (" . implode(',',$lmids) . ")");
			print_cp_message  ($db->affected_rows().' LM has been deleted', 'lovemessage.php?do=domoderate');
		}
		else print_cp_message("You haven't selected any LM yet");
	}
	else 
	{
		if($lmids)
		{
			$db->query_write("UPDATE " . TABLE_PREFIX . "love_message SET checked = 1 WHERE id IN (" . implode(',',$lmids) . ")");
			print_cp_message  ($db->affected_rows().' LM has been moderated', 'lovemessage.php?do=domoderate');
		}
		else print_cp_message("You haven't selected any LM yet");
	
	}
	print_cp_footer();
}
if($_REQUEST['do']=='edit')
{
	$lmid = $vbulletin->input->clean_gpc('r','lmid',TYPE_INT);
	$type = $vbulletin->input->clean_gpc('r','type',TYPE_INT);
	$item = $db->query_first("
			SELECT fname,tname,message
			FROM " . TABLE_PREFIX . "love_message
			WHERE id = $lmid
	");		
	print_form_header('lovemessage', 'update');
	construct_hidden_code('lmid', $lmid);
	construct_hidden_code('type', $type);
	print_hidden_fields();		
	print_table_header('Edit Message From: "' . $item['fname'] . '" To: "' . htmlspecialchars($item['tname']) . '"');
	print_textarea_row('Message', 'message', $item['message']);
	if($type==2) print_yes_no_row('Moderate this LM?', 'checked', 1);
	print_submit_row('Save');
	print_cp_footer();		
}
if($_REQUEST['do']=='update')
{
	$lmid = $vbulletin->input->clean_gpc('p','lmid',TYPE_INT);
	$message = $vbulletin->input->clean_gpc('p','message',TYPE_STR);
	$checked = $vbulletin->input->clean_gpc('p','checked',TYPE_INT);
	$type = $vbulletin->input->clean_gpc('p','type',TYPE_INT);
	if($type==2) $extra = ", checked = $checked";
	else $extra = "";
	$item = $db->query_write("
			UPDATE " . TABLE_PREFIX . "love_message
			SET message = '" . $db->escape_string($message) . "'$extra
			WHERE id = $lmid
	");			
	print_cp_message('LM has been updated', 'lovemessage.php?do=' . iif($type==1,'manage','domoderate'));
}
?>