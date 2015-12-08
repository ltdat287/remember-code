<?php

/************************************************************************************
* vBSEO 3.5.0 RC2 for vBulletin v3.x. & v4.x by Crawlability, Inc.                  *
*                                                                                   *
* Copyright © 2010, Crawlability, Inc. All rights reserved.                    *
* You may not redistribute this file or its derivatives without written permission. *
*                                                                                   *
* Sales Email: sales@crawlability.com                                               *
*                                                                                   *
*----------------------------vBSEO IS NOT FREE SOFTWARE-----------------------------*
* http://www.crawlability.com/vbseo/license/                                        *
************************************************************************************/

@define('VBSEO_IS_VBSEOCP', 1);
include dirname(__FILE__).'/vbseo/includes/functions_vbseocp.php';
vBSEO_CP::init();
vBSEO_CP::$script = basename(__FILE__);
if($_GET['logout'])
{
setcookie('vbseocp_supp','');
vBSEO_CP::logout();
vbseo_safe_redirect(vBSEO_CP::$script, array('logout'));
}
if($_GET['getsettings'] && vBSEO_CP::$logged_in)
{
$xcont = vBSEO_CP::get_settings($_GET['get']);
$expnames = array ('all' => 'vbseo_all', 'urw' => 'vbseo_urls');
@header('Content-Type: application/xml');
@header('Content-Disposition: attachment; filename=' . $expnames[$_GET['get']] . '.xml');
echo $xcont;
exit;
}
if (($fl = $_FILES['file']) && $fl['size'])
{
$rdopt = vBSEO_Storage::read_config($fl['tmp_name']);
vBSEO_CP::proc_settings($rdopt, true);
vBSEO_CP::save_settings($rdopt);
}else
if($_POST)
{
ob_start();
$noreload = isset($_POST['setting']['noreload_skip']);
$result = $messages = array();
$litem = $_POST['load'];
$postsend = $_POST['settingset'];
if(isset($postsend['password']))
{
if(!vBSEO_CP::login($postsend['password']))
$messages[] = array('error', vBSEO_CP::lang('login_failed'));
}
if(isset($postsend['pass']))
{
if($error_id = vBSEO_CP::setpass($postsend['pass'], $postsend['pass2']))
$messages[] = array('error', vBSEO_CP::lang($error_id));
}
if(!is_writable(vBSEO_Storage::path('config')))
$messages[] = array('attention', vBSEO_CP::lang('warn_readonly'));
if(!vBSEO_CP::$logged_in)
{
$litem = vBSEO_Storage::setting('VBSEO_ADMIN_PASSWORD') ? 'login' : 'setpass';
}
else
{
if($lpreset = $_POST['loadpreset'])
{
$rdopt = vBSEO_Storage::read_config(vBSEO_CP::preset_name($lpreset));
$rdopt = vBSEO_CP::filter_settings($rdopt, $_POST['type']);
vBSEO_CP::proc_settings($rdopt);
vBSEO_CP::save_settings($rdopt);
}
if($postsettings = $_POST['setting'])
{
vBSEO_CP::proc_settings($postsettings);
if(vBSEO_CP::save_settings($postsettings))
{
if($noreload)
$messages[] = array('save_success');
else
$messages[] = array('success', vBSEO_CP::lang('saved_ok'));
}else
$messages[] = array('error', vBSEO_CP::lang('config_readonly'));
}
if(!$litem)
$litem = 'dashboard';
}
vBSEO_CP::read_lang();
if(!$noreload || vBSEO_CP::$proc_error)
{
$result = vBSEO_CP::proc_page($litem);
}
$result['messages'] = $result['messages'] ? array_merge($result['messages'], $messages) : $messages;
ob_clean();
$thexml = new VBSEOCP_XML;
$thexml->start_xml(vBSEO_CP::charset());
$thexml->add_tag('data', $result);
echo $thexml->send_xml();
exit;
}
echo vBSEO_CP::get_template('vbseocp');
?>