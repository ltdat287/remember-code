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

function vbseo_startup()
{
vbseo_get_options();
vbseo_prepare_seo_replace();
vbseo_get_forum_info();
}
function vbseo_get_options($getuserinfo = true)
{
global $vboptions, $bbuserinfo, $vbulletin, $vbseo_gcache,
$forumcache, $threadcache, $config, $session,
$GAS_settings, $vbseo_bitfields, $vbseo_cache;
vbseo_cache_start();
if (!isset($vboptions) || !isset($vboptions['bburl']) || !isset($forumcache))
{
if (isset($vbulletin) && isset($vbulletin->options))
{
$vboptions = $vbulletin->options;
$forumcache = $vbulletin->forumcache;
$session = $vbulletin->session->vars;
}
else
{
$options = &$vboptions;
$bitfields = &$vbseo_bitfields;
$optimported = false;
if ($GLOBALS['config']['Datastore']['class'] == 'vB_Datastore_Filecache')
{
$dsfolder = vBSEO_Storage::path('vbinc') . '/datastore';
$include_return = @include_once($dsfolder . '/datastore_cache.' . VBSEO_VB_EXT);
if($options)$optimported = true;
}
if(!$optimported)
{
$optarr2 = array('options', 'bitfields', 'forumcache', 'GAS_settings');
if ($optarr2)
{
$db = vbseo_get_db();
$rid = $db->vbseodb_query("select title,data from " . vbseo_tbl_prefix('datastore') . "
where title in ('" . implode("','", $optarr2) . "')");
if ($rid)
{
while ($dstore = @$db->funcs['fetch_assoc']($rid))
{
$$dstore['title'] = @unserialize($dstore['data']);
if(!$$dstore['title'])
$$dstore['title'] = @unserialize(utf8_decode($dstore['data']));
}
$db->vbseodb_free_result($rid);
}
}
}
}
}
if (defined('VBSEO_CUSTOM_BBURL'))
$vboptions['bburl'] = VBSEO_CUSTOM_BBURL;
$vboptions['bburl2'] = vbseo_http_s_url(preg_replace('#/+$#', '', $vboptions['bburl']));
$vboptions['cutbburl'] = preg_replace('#^https?://[^/]+(.*)$#', '$1', $vboptions['bburl2']);
$vboptions['relbburl'] = VBSEO_USE_HOSTNAME_IN_URL ? $vboptions['bburl2'] : $vboptions['cutbburl'];
define('VBSEO_VB4', (vbseo_vbversion()>='4'));
$vbseo_gcache['post'] = isset($GLOBALS['itemids'])?$GLOBALS['itemids']:(isset($GLOBALS['postcache'])?$GLOBALS['postcache']:array());
if (isset($GLOBALS['getlastpost']) && $GLOBALS['getlastpost']['postid'])
{
$vbseo_gcache['post'][$GLOBALS['getlastpost']['postid']] = $GLOBALS['getlastpost'];
}
$url = isset($vboptions['forumhome'])?($vboptions['forumhome'] . '.' . VBSEO_VB_EXT . ''):'';
if ((($url == 'index.' . VBSEO_VB_EXT) || VBSEO_FORCEHOMEPAGE_ROOT) && VBSEO_HP_FORCEINDEXROOT)
$url = '';
@define('VBSEO_HOMEPAGE', $url);
if (isset($threadcache))
foreach($threadcache as $threadid => $tar)
{
if ($tar['firstpostid'] && !isset($vbseo_gcache['post'][$tar['firstpostid']]))
$vbseo_gcache['post'][$tar['firstpostid']] = array('threadid' => $threadid,
'postid' => $tar['firstpostid']
);
if ($tar['lastpostid'] && !isset($vbseo_gcache['post'][$tar['lastpostid']]))
$vbseo_gcache['post'][$tar['lastpostid']] = array('threadid' => $threadid,
'postid' => $tar['lastpostid']
);
}
if ($getuserinfo && (!VBSEO_VB4 || VBSEO_EQFORUMDIR || defined('VBSEO_PREPROCESSED')))
{
if (isset($vbulletin) && (!$bbuserinfo || !$bbuserinfo['usergroupid']))
$bbuserinfo = $vbulletin->userinfo;
if (!isset($bbuserinfo) || !$bbuserinfo['userid'] ||
(isset($vbulletin) && !$bbuserinfo['usergroupid'])
)
{
$cvisit = @intval($_COOKIE[vbseo_vb_cprefix() . 'lastvisit']);
$bbuserinfo['lastvisit'] = $cvisit ? $cvisit : time();
if ($_COOKIE[vbseo_vb_cprefix() . 'userid'] && !$bbuserinfo['userid'])
$bbuserinfo['userid'] = $_COOKIE[vbseo_vb_cprefix() . 'userid'];
}
$bbuserinfo['isadmin'] = isset($bbuserinfo['usergroupid']) && ($bbuserinfo['usergroupid'] == 6);
}
vbseo_check_datastore();
}
function vbseo_check_datastore($forceupdate = false)
{
global $vboptions, $forumcache, $vbseo_gcache;
$opt = isset($vboptions['vbseo_opt']) ? $vboptions['vbseo_opt'] : array();
if (!$forceupdate && isset($opt['stamp']) && (VBSEO_TIMESTAMP < ($opt['stamp'] + 86400)))
return;
$opt['forumthreads'] = $opt['forumpaths'] = array();
$totalthreads = 0;
if ($forumcache)
foreach($forumcache as $forumid => $finfo)
{
if (isset($finfo['forumid']))
{
$tcount = (isset($finfo['forumid']) && $finfo['threadcount']) ?
$finfo['threadcount'] : (isset($vbseo_gcache['forum'])?$vbseo_gcache['forum'][$finfo['forumid']]['threadcount']:0);
if ($tcount)
$opt['forumthreads'][$finfo['forumid']] = $tcount;
$totalthreads += $tcount;
if (($fpath = $finfo['path']) || ($fpath = $vbseo_gcache['forum'][$finfo['forumid']]['path']))
$opt['forumpaths'][$finfo['forumid']] = $fpath;
}
}
if (!$totalthreads)return;
vbseo_update_datastore($opt);
}
function vbseo_update_datastore($opt)
{
global $vboptions, $vbulletin;
$vbo = vbseo_get_datastore('options');
$opt['stamp'] = VBSEO_TIMESTAMP;
$vboptions['vbseo_opt'] = $vbo['vbseo_opt'] = $opt;
vbseo_set_datastore('options', $vbo);
}
function vbseo_get_datastore($record)
{
global $vbseo_isvb_360;
$db = vbseo_get_db();
$rid = $db->vbseodb_query("select * from " . vbseo_tbl_prefix('datastore') . " where title = '$record'");
if ($rid)
{
$vbseostore = @$db->funcs['fetch_assoc']($rid);
$db->vbseodb_free_result($rid);
if (!$vbseo_isvb_360)
$vbseo_isvb_360 = isset($vbseostore['unserialize']);
return @unserialize($vbseostore['data']);
}
else
return array();
}
function vbseo_set_datastore($record, $arr)
{
global $vbulletin, $vbseo_isvb_360, $vbseo_gcache;
if($vbseo_gcache['var']['vboptchanged']) return;
$db = vbseo_get_db();
$db->vbseodb_query($q = "REPLACE INTO " . vbseo_tbl_prefix('datastore') . " (title, data" . ($vbseo_isvb_360?",unserialize":"") . ")
VALUES ('$record', '" . addslashes(serialize($arr)) . "'" . ($vbseo_isvb_360?",1":"") . ")");
if (($record == 'options') && $vbulletin && @method_exists($vbulletin->datastore, 'build'))
{
$vbulletin->datastore->build($record, serialize($arr));
}
}
?>