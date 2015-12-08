<?php
/*======================================================================*\
|| #################################################################### ||
|| # vBulletin 4.0.2
|| # ---------------------------------------------------------------- # ||
|| # Copyright ©2000-2010 vBulletin Solutions Inc. All Rights Reserved. ||
|| # This file may not be redistributed in whole or significant part. # ||
|| # ---------------- VBULLETIN IS NOT FREE SOFTWARE ---------------- # ||
|| # http://www.vbulletin.com | http://www.vbulletin.com/license.html # ||
|| #################################################################### ||
\*======================================================================*/

function parse_video_bbcode($pagetext)
{
	global $vbulletin;

	$parseurl = false;
	$providers = $search = $replace = array();
	($hook = vBulletinHook::fetch_hook('data_preparse_bbcode_video_start')) ? eval($hook) : false;

	// Convert video bbcode
	if (stripos($pagetext, '[video]') !== false OR $parseurl)
	{
		if (!$providers)
		{
			$bbcodes = $vbulletin->db->query_read_slave("
				SELECT
					provider, url, regex_url, regex_scrape, tagoption
				FROM " . TABLE_PREFIX . "bbcode_video
				ORDER BY priority
			");
			while ($bbcode = $vbulletin->db->fetch_array($bbcodes))
			{
				$providers["$bbcode[tagoption]"] = $bbcode;
			}
		}

		$scraped = 0;
		if (!empty($providers) AND preg_match_all('#\[video\](.*?)\[/video\]#si', $pagetext, $matches))
		{
			foreach ($matches[1] AS $key => $url)
			{
				$match = false;
				foreach ($providers AS $provider)
				{
					$addcaret = ($provider['regex_url'][0] != '^') ? '^' : '';
					if (preg_match('#' . $addcaret . $provider['regex_url'] . '#si', $url, $match))
					{
						break;
					}
				}
				if ($match)
				{
					if (!$provider['regex_scrape'] AND $match[1])
					{
						$search[] = '#' . preg_quote($matches[0][$key], '#') . '#si';
						$replace[] = '[video=' . $provider['tagoption'] . ';' . $match[1] . ']' . $url . '[/video]';
					}
					else if ($provider['regex_scrape'] AND $vbulletin->options['bbcode_video_scrape'] > 0 AND $scraped < $vbulletin->options['bbcode_video_scrape'])
					{
						require_once(DIR . '/includes/functions_file.php');
						$result = fetch_body_request($url);
						if (preg_match('#' . $provider['regex_scrape'] . '#si', $result, $scrapematch))
						{
							$search[] = '#' . preg_quote($matches[0][$key], '#') . '#si';
							$replace[] = '[video=' . $provider['tagoption'] . ';' . $scrapematch[1] . ']' . $url . '[/video]';
						}
						$scraped++;
					}
				}
			}

			if (!empty($search))
			{
				$pagetext = preg_replace($search, $replace, $pagetext);
			}
		}
	}

	return $pagetext;
}

/*======================================================================*\
|| ####################################################################
|| # CVS: $RCSfile$ - $Revision: 27207 $
|| ####################################################################
\*======================================================================*/
?>
