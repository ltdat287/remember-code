<?php
###########################################################################
#####  [AJAX]VietVBB - Advanced Forum Statistics for vbulletin 4.x.x  #####
#####  Linhnt - VietVBBTeam - www.vietvbb.vn                          #####
###########################################################################


class vietvbbTopx	{
	public $content = array(); // noi dung trong topx main
	private $vbb; //$vbulletin
	private $db; //$vbulletin->db
	private $exclfids=''; // Forum not include from stats (list id)
	private $exclgroups = ''; //usergroup not include (query)
	private $bold = array("<strong>","</strong>","<b>","</b>", "font-weight: bold", "font-weight:bold"); // use to remove bold style
	private $result=10; // So luong ket qua
	private $trimthreadtitle;
	private	$trimusername;
	private	$trimforumtitles;
	
	
	###############    START         ##############
	public function __construct($vbulletin, $result=0)	{		
		$this->vbb = $vbulletin;
		$this->db = $vbulletin->db;
		$this->trimthreadtitle = $vbulletin->options['vietvbbtopstats_trim_threadtitles'];
		$this->trimusername = $vbulletin->options['vietvbbtopstats_trim_usernames'];
		$this->trimforumtitles = $vbulletin->options['vietvbbtopstats_trim_forumtitles'];
		$this->content['title'] = $vbulletin->options['vietvbbtopstats_name'];
		if ($result >0)  //ajax load with moresult
			$this->result = $result;
		else	{  
			$results = explode(',',$vbulletin->options['vietvbbtopstats_more_result']);
			$result = (int)$results[0];
			if ($vbulletin->session->vars['topxresult'])	{ // session (load top or ajax - moresult disable)
				$this->result = ($results[1])? $vbulletin->session->vars['topxresult'] : $result;
			}
			else	{ // Chua co session (load top)
				if ($result>0) $this->result = $result;
			}
		}
		
		if ($this->result >100) $this->result=100;
		
		if ($vbulletin->options['vietvbbtopstats_excl_groups_listing'])
			$this->exclgroups = "AND user.usergroupid NOT IN (".$vbulletin->options['vietvbbtopstats_excl_groups_listing'].")";
	}
	
	############## Lay thong tin tab ####################
	private function getTabInfo($num)	{
		global $vbphrase;
		$tab = array();		
		switch ($this->vbb->options['vietvbbtopstats_switch_t'.$num])	{
			case 0: $tab['id'] = false; break;
			case 1: $tab['title'] = $vbphrase['latest_news']; $tab['id'] = 'latest_news'; break;
			case 2: $tab['title'] = $vbphrase['newest_members']; $tab['id'] = 'newest_members'; break;
			case 3: $tab['title'] = $vbphrase['top_posters']; $tab['id'] = 'top_posters'; break;
			case 4: $tab['title'] = $vbphrase['top_starters']; $tab['id'] = 'top_starters'; break;
			case 5: $tab['title'] = $vbphrase['top_referrers']; $tab['id'] = 'top_referrers'; break;
			case 6: $tab['title'] = $vbphrase['most_viewed']; $tab['id'] = 'most_viewed'; break;
			case 7: $tab['title'] = $vbphrase['hottest_threads']; $tab['id'] = 'hottest_threads'; break;
			case 8: $tab['title'] = $vbphrase['top_reputation']; $tab['id'] = 'top_reputation'; break;
			case 9: $tab['title'] = $vbphrase['thanked_members']; $tab['id'] = 'thanked_members'; break;
			case 10: $tab['title'] = $vbphrase['top_forums']; $tab['id'] = 'top_forums'; break;
			case 11: $tab['title'] = $vbphrase['top_infractions']; $tab['id'] = 'top_infractions'; break;
			case 12: $tab['title'] = $vbphrase['latest_classads']; $tab['id'] ='latest_classads'; break;			
			case 14: $tab['title'] = $vbphrase['top_credits']; $tab['id'] = 'top_credits'; break;
			case 15: $tab['title'] = $vbphrase['latest_posts']; $tab['id'] = 'latest_posts'; break;
			case 16: $tab['title'] = $this->vbb->options['vietvbbtopstats_latest_posts_custom1_name']; $tab['id'] = 'latest_posts_custom1'; break;
			case 17: $tab['title'] = $this->vbb->options['vietvbbtopstats_latest_posts_custom2_name']; $tab['id'] = 'latest_posts_custom2'; break;
			case 18: $tab['title'] = $this->vbb->options['vietvbbtopstats_latest_posts_custom3_name']; $tab['id'] = 'latest_posts_custom3'; break;
			case 19: $tab['title'] = $this->vbb->options['vietvbbtopstats_latest_posts_custom4_name']; $tab['id'] = 'latest_posts_custom4'; break;
			case 20: $tab['title'] = $this->vbb->options['vietvbbtopstats_latest_posts_custom5_name']; $tab['id'] = 'latest_posts_custom5'; break;
			case 21: $tab['title'] = $this->vbb->options['vietvbbtopstats_latest_posts_custom6_name']; $tab['id'] = 'latest_posts_custom6'; break;
			case 22: $tab['title'] = $this->vbb->options['vietvbbtopstats_latest_posts_custom7_name']; $tab['id'] = 'latest_posts_custom7'; break;
			case 23: $tab['title'] = $this->vbb->options['vietvbbtopstats_latest_posts_custom8_name']; $tab['id'] = 'latest_posts_custom8'; break;
			case 24: $tab['title'] = $this->vbb->options['vietvbbtopstats_latest_posts_custom9_name']; $tab['id'] = 'latest_posts_custom9'; break;
			case 30: $tab['title'] = $vbphrase['latest_blogs']; $tab['id'] = 'latest_blogs'; break;
			case 31: $tab['title'] = $vbphrase['latest_comments']; $tab['id'] = 'latest_comments'; break;
			case 32: $tab['title'] = $vbphrase['most_viewed_entrys']; $tab['id'] = 'most_viewed_entrys'; break;
			case 33: $tab['title'] = $vbphrase['hottest_entrys']; $tab['id'] = 'hottest_entrys'; break;
			case 34: $tab['title'] = $vbphrase['top_bloggers']; $tab['id'] = 'top_bloggers'; break;
		}
		
		if($this->vbb->session->vars['topxtab'])
			$tab['class'] = ($this->vbb->session->vars['topxtab']==$tab['id'])? 'current' : '';
		else
			$tab['class'] = ($num==1)? 'current' : '';
		
		return $tab;
	}
	
	
	########### Lay thong tin menu  ####################
	private function getMenuInfo($num)	{
		global $vbphrase;
		$menu = array();		
		switch ($this->vbb->options['vietvbbtopstats_switch_s'.$num])	{
			case 0: $menu['value'] = false; break;
			case 2: $menu['title'] = $vbphrase['newest_members']; $menu['value'] = 'newest_members'; break;
			case 3: $menu['title'] = $vbphrase['top_posters']; $menu['value'] = 'top_posters'; break;
			case 4: $menu['title'] = $vbphrase['top_starters']; $menu['value'] = 'top_starters'; break;
			case 5: $menu['title'] = $vbphrase['top_referrers']; $menu['value'] = 'top_referrers'; break;
			case 8: $menu['title'] = $vbphrase['top_reputation']; $menu['value'] = 'top_reputation'; break;
			case 9: $menu['title'] = $vbphrase['thanked_members']; $menu['value'] = 'thanked_members'; break;
			case 11: $menu['title'] = $vbphrase['top_infractions']; $menu['value'] = 'top_infractions'; break;
			case 14: $menu['title'] = $vbphrase['top_credits']; $menu['value'] = 'top_credits'; break;
			case 34: $menu['title'] = $vbphrase['top_bloggers']; $menu['value'] = 'top_bloggers'; break;
		}
		
		if($this->vbb->session->vars['topxmenu'])			
			$menu['selected'] = ($this->vbb->session->vars['topxmenu']==$menu['value'])? 'selected="selected"' : '';
		else
			$menu['selected'] = ($num==1)? 'selected="selected"' : '';
		
		return $menu;
	}
	
	
	############ tao danh sach tab ######################
	private function buildTab($tab)	{		
		$temp = vB_Template::create('vietvbb_topstats_tabbit');
		$temp->register('tab', $tab);		
		$this->content['tablist'] .= $temp->render();
	}
	
	
	########### Tao danh sach menu ########################
	private function buildMenu($menu)	{		
		$temp = vB_Template::create('vietvbb_topstats_menubit');
		$temp->register('menu', $menu);		
		$this->content['menulist'] .= $temp->render();
	}
	
	
	###############  Tao danh sach Moresult ######################
	private function buildMoreResult($option)	{
		$option = str_replace(' ','', $option);
		$option = explode(',', $option);
		if (count($option)>1)	{			
			foreach ($option as $val)	{
				$val = (int)$val;
				$select = ($this->vbb->session->vars['topxresult']==$val)? 'selected="selected" ' : '';				
				$this->content['moreresult'] .= "<option {$select}value=\"{$val}\">{$val}</option>";				
			}
			$this->content['moreresult'] = "<select id=\"vietvbb_topstats_result\">{$this->content['moreresult']}</select>";
		}
		else	$this->content['moreresult'] ='';
	}
	
	
	############## Tao Topx #################################
	public function buildTopx()	{
		for ($i=1; $i<=10; $i++)    {
			$tab = $this->getTabInfo($i);
			if ($tab['class'] =='current') $this->content['tab_content'] = $this->getTop($tab['id']);
			if ($tab['id']) $this->buildTab($tab);
		}
    
		for ($i=1; $i<=8; $i++)    {
			$menu = $this->getMenuInfo($i);
			if ($menu['selected'] =='selected="selected"') $this->content['menu_content'] = $this->getTop($menu['value']);
			if ($menu['value']) $this->buildMenu($menu);
		}
    
		$this->buildMoreResult($this->vbb->options['vietvbbtopstats_more_result']);
		
		$temp = vB_Template::create('vietvbb_topstats_main');
		$temp->register('content', $this->content);		
		return $temp->render();
	}
	
	
	################# kiem tra Forum Permission ###############
	private function checkPerm()	{
		$this->exclfids = '';
		$forumperms = array();
		foreach($this->vbb->forumcache AS $forum)	{
			$forumperms[$forum["forumid"]] = fetch_permissions($forum['forumid']);
			if (
						!($forumperms[$forum["forumid"]] & $this->vbb->bf_ugp_forumpermissions['canview'])
					OR (
							!($forum['options'] & $this->vbb->bf_misc_forumoptions['active']) 
							AND !$this->vbb->options['showprivateforums'] 
							AND !in_array($this->vbb->userinfo['usergroupid'], array(5,6,7))
						)
				)	{
				$this->exclfids .= ','.$forum['forumid'];
			}
		}
	
		// Excl
		if ($this->vbb->options['vietvbbtopstats_excl_forums'])	{
			$this->exclfids .= ','.$this->vbb->options['vietvbbtopstats_excl_forums'];
		}
		$this->exclfids = substr($this->exclfids, 1);		
	}

	
	
	
	################ Get Top #######################
	public function getTop($top)	{
		$customids = false;
		
		$need_check_perm = array(
			'latest_posts',
			'top_forums',
			'hottest_threads',
			'most_viewed'
		);
		
		for ($i=1; $i<=9; $i++) {			
			if ($top=='latest_posts_custom'.$i)	{
				$tmp = explode(',',$this->vbb->options['vietvbbtopstats_latest_posts_custom'.$i.'_forumid']);
				$customids = $comma = '';
				foreach ($tmp as $v)	{
					if($this->vbb->forumcache["$v"]['childlist'])	{
						$customids .= $comma.$this->vbb->forumcache["$v"]['childlist'];
						$comma = ',';
					}
				}				
				$top = 'latest_posts';
				break;
			}				
		}
		
		$valids = array_merge($need_check_perm,array(
				'latest_news',
				'newest_members',
				'top_posters',
				'top_starters',
				'top_referrers',				
				'top_reputation',
				'thanked_members',				
				'top_infractions',
				'latest_classads',
				'top_credits',
				'latest_blogs',
				'latest_comments',
				'most_viewed_entrys',
				'hottest_entrys',
				'top_bloggers'
			));
			
		if (in_array($top, $valids))	{
			if (in_array($top, $need_check_perm))	$this->checkPerm();
			return $this->{$top}($customids);
		}
		else
			return false;
	}
	
	
	
	
	
	############### Get lastest News ###########################
	private function latest_news() {
		global $vbphrase;
		$forums = ($this->vbb->options['vietvbbtopstats_latestnews_newsforumid'])? $this->vbb->options['vietvbbtopstats_latestnews_newsforumid'] : 0;
		$prefixid = ($this->vbb->options['vietvbbtopstats_prefix'])? ', prefixid' : '';
		$query = $this->db->query_read("
			SELECT threadid, 
				title, 
				forumid, 
				views, 
				replycount, 
				dateline, 
				lastpost, 
				visible, 
				open, 
				postusername,
				postuserid,				
				lastposter,
				lastposterid
				$prefixid
			FROM " . TABLE_PREFIX . "thread AS thread
			WHERE NOT ISNULL(threadid) AND forumid IN (".$forums.") AND visible = '1' AND open!='10'
			ORDER BY dateline DESC
			LIMIT 0, {$this->result}
		");
		
		while ($get_latest_news = $this->db->fetch_array($query))	{
			$get_latest_news[fullthreadtitle] = strip_tags($get_latest_news[title]);
			if ($this->trimthreadtitle > 0)	{
				$get_latest_news[titletrimmed] = fetch_trimmed_title($get_latest_news[fullthreadtitle], $this->trimthreadtitle);
			}
			else	{
				$get_latest_news[titletrimmed] = $get_latest_news[fullthreadtitle];
			}
			if ($get_latest_news['prefixid'])	{
				$get_latest_news['prefix_plain_html'] = htmlspecialchars_uni($vbphrase["prefix_$get_latest_news[prefixid]_title_plain"]);
				$get_latest_news['prefix_rich'] = $vbphrase["prefix_$get_latest_news[prefixid]_title_rich"];
			}	else	{
				$get_latest_news['prefix_plain_html'] = '';
				$get_latest_news['prefix_rich'] = '';
			}


			$get_latest_news[newpost] = ($get_latest_news[lastpost] > $this->vbb->userinfo['lastvisit'])? 'new' : 'old';			
			
			$get_latest_news[fullforumtitle] = strip_tags($this->vbb->forumcache["$get_latest_news[forumid]"]['title_clean']);		
			
			$get_latest_news[lastpostdate] = vbdate($this->vbb->options['dateformat'], $get_latest_news[lastpost], true);
			
			$get_latest_news[lastposttime] = vbdate($this->vbb->options['timeformat'], $get_latest_news[lastpost]);
			
			$get_latest_news[startdate] = vbdate($this->vbb->options['dateformat'], $get_latest_news[dateline], true);
			
			$get_latest_news[starttime] = vbdate($this->vbb->options['timeformat'], $get_latest_news[dateline]);
			
			$get_latest_news[gotonewpost] = array('goto' => 'newpost');
			
			$temp = vB_Template::create('vietvbb_topstats_thread_info');
			$temp->register('content', $get_latest_news);
			$get_latest_news[threadinfo] = $temp->render();
			
			$temp = vB_Template::create('vietvbb_topstats_latest_news');
			$temp->register('content', $get_latest_news);
			$output .= $temp->render();			
		}
		if (!$output)	$output = 'No Result';
		$this->db->free_result($query);
		return $output;
	}
	
	
	################################ Get Newest Member  ###############################
	private function newest_members()	{
		$query = $this->db->query_read("
			SELECT userid, usergroupid, displaygroupid, username, joindate, posts
			FROM " . TABLE_PREFIX . "user AS user
			WHERE userid > '0' {$this->exclgroups}
			ORDER BY joindate DESC
			LIMIT 0, {$this->result}
			");
		while ($get_new_mem = $this->db->fetch_array($query))	{
			$get_new_mem[fullusername] = strip_tags($get_new_mem[username]);
			
			if ($this->trimusername > 0)	{
				$get_new_mem[username] = fetch_trimmed_title($get_new_mem[fullusername], $this->trimusername);
			}
			else	{
				$get_new_mem[username] = $get_new_mem[fullusername];
			}
			
			$get_new_mem[musername] = fetch_musername($get_new_mem);
			
			if ($this->vbb->options['vietvbbtopstats_bold_remove'])	{
				$get_new_mem[musername] = str_replace($this->bold, "", $get_new_mem[musername]);
			}
			if ($get_new_mem[joindate] > $this->vbb->userinfo['lastvisit'])	{
				$get_new_mem[newuser] = true;
			}
			$get_new_mem[memberjoined] = vbdate($this->vbb->options['dateformat'], $get_new_mem['joindate'], true);
			
			
			$temp = vB_Template::create('vietvbb_topstats_newest_members');
			$temp->register('content', $get_new_mem);
			$output .= $temp->render();	
		}
		$this->db->free_result($query);
		return $output;
	}
	
	
	#################### Top Poster   ##########################
	private function top_posters()	{
		$query = $this->db->query_read("
			SELECT userid, usergroupid, displaygroupid, username, posts
			FROM " . TABLE_PREFIX . "user AS user
			WHERE posts > '0' {$this->exclgroups}
			ORDER BY posts DESC
			LIMIT 0, {$this->result}
			");
		
		while ($getstats_poster = $this->db->fetch_array($query))	{
			$getstats_poster[fullusername] = strip_tags($getstats_poster[username]);
			if ($this->trimusername > 0)	{
				$getstats_poster[username] = fetch_trimmed_title($getstats_poster[fullusername], $this->trimusername);
			}
			else	{
				$getstats_poster[username] = $getstats_poster[fullusername];
			}
			
			$getstats_poster[musername] = fetch_musername($getstats_poster);
			if ($this->vbb->options['vietvbbtopstats_bold_remove'])	{
				$getstats_poster[musername] = str_replace($this->bold, '', $getstats_poster[musername]);
			}
			
			
			$temp = vB_Template::create('vietvbb_topstats_top_posters');
			$temp->register('content', $getstats_poster);
			$output .= $temp->render();			
		}
		if (!$output)	$output = 'No Result';
		$this->db->free_result($query);
		return $output;
	}
	
	
	
	######################### Top Starter   ################################
	private function top_starters ()	{
		$query = $this->db->query_read("
			SELECT COUNT(thread.threadid) AS threads, thread.postuserid, thread.dateline, user.userid, user.usergroupid, user.displaygroupid, user.username
			FROM " . TABLE_PREFIX . "thread AS thread
			LEFT JOIN " . TABLE_PREFIX . "user AS user ON (thread.postuserid = user.userid)
			LEFT JOIN " . TABLE_PREFIX . "forum AS forum ON (forum.forumid = thread.forumid)
			WHERE thread.visible='1' AND (forum.options & 4096) AND user.userid > '0' {$this->exclgroups}
			GROUP BY thread.postuserid
			ORDER BY threads DESC
			LIMIT 0, {$this->result}
			");
			
		while ($getstats_starter = $this->db->fetch_array($query))	{
			$getstats_starter[fullusername] = strip_tags($getstats_starter[username]);
			if ($this->trimusername > 0)	{
				$getstats_starter[username] = fetch_trimmed_title($getstats_starter[fullusername], $this->trimusername);
			}
			else	{
				$getstats_starter[username] = $getstats_starter[fullusername];
			}
			
			$getstats_starter[musername] = fetch_musername($getstats_starter);
			if ($this->vbb->options['vietvbbtopstats_bold_remove'])	{
				$getstats_starter[musername] = str_replace($this->bold, "", $getstats_starter[musername]);
			}
			
			$getstats_starter[gotonewpost] = array('goto' => 'newpost');
			
			$temp = vB_Template::create('vietvbb_topstats_top_starters');
			$temp->register('content', $getstats_starter);
			$output .= $temp->render();			
		}
		
		if (!$output)	$output = 'No Result';
		$this->db->free_result($query);
		return $output;
	}
	
	
	########################### Top Referrers ##################################
	private function top_referrers ()	{
		$query = $this->db->query_read("
			SELECT COUNT(*) AS refnumber, user.username, user.userid, user.usergroupid, user.displaygroupid, user.referrerid, refs.joindate
			FROM " . TABLE_PREFIX . "user AS refs
			LEFT JOIN " . TABLE_PREFIX . "user AS user ON (refs.referrerid = user.userid)
			WHERE refs.referrerid > '0' AND user.userid > '0' {$this->exclgroups}
			GROUP BY refs.referrerid
			ORDER BY refnumber DESC
			LIMIT 0, {$this->result}
			");
		while ($getstats_referrer = $this->db->fetch_array($query))	{
			$getstats_referrer[fullusername] = strip_tags($getstats_referrer[username]);
			if ($this->trimusername > 0)	{
				$getstats_referrer[username] = fetch_trimmed_title($getstats_referrer[fullusername], $this->trimusername);
			}
			else	{
				$getstats_referrer[username] = $getstats_referrer[fullusername];
			}
			$getstats_referrer[musername] = fetch_musername($getstats_referrer);
			if ($this->vb->options['vietvbbtopstats_bold_remove'])	{
				$getstats_referrer[musername] = str_replace($this->bold, "", $getstats_referrer[musername]);
			}
			
			$temp = vB_Template::create('vietvbb_topstats_top_referrers');
			$temp->register('content', $getstats_referrer);
			$output .= $temp->render();
		}
		
		if (!$output)	$output = 'No Result';
		$this->db->free_result($query);
		return $output;
	}
	
	
	
	############### Most view thread  ####################
	private function most_viewed ()	{
		global $vbphrase;
		if($this->exclfids) $exclforum = "AND forumid NOT IN({$this->exclfids})";
		$prefixid = ($this->vbb->options['vietvbbtopstats_prefix'])? ', prefixid' : '';
		$query = $this->db->query_read("
			SELECT threadid,
				title,
				forumid,
				views,
				dateline,
				visible,
				open,
				lastpost,
				replycount,
				postusername,
				postuserid,
				lastposter,
				lastposterid
				$prefixid
			FROM " . TABLE_PREFIX . "thread AS thread
			WHERE NOT ISNULL(threadid) AND visible = '1' AND views > '0' AND open!='10' $exclforum $timecut_mostviewed
			ORDER BY views DESC
			LIMIT 0, {$this->result}
			");		
		
		while ($get_most_viewed = $this->db->fetch_array($query))	{
			$get_most_viewed[fullthreadtitle] = strip_tags($get_most_viewed[title]);
			if ($this->trimthreadtitle > 0)	{
				$get_most_viewed[titletrimmed] = fetch_trimmed_title($get_most_viewed[fullthreadtitle], $this->trimthreadtitle);
			}
			else	{
				$get_most_viewed[titletrimmed] = $get_most_viewed[fullthreadtitle];
			}
			if ($get_most_viewed['prefixid'])	{
				$get_most_viewed['prefix_plain_html'] = htmlspecialchars_uni($vbphrase["prefix_$get_most_viewed[prefixid]_title_plain"]);
				$get_most_viewed['prefix_rich'] = $vbphrase["prefix_$get_most_viewed[prefixid]_title_rich"];
			}	else	{
				$get_most_viewed['prefix_plain_html'] = '';
				$get_most_viewed['prefix_rich'] = '';
			}
			$get_most_viewed[newpost] = ($get_most_viewed[lastpost] > $this->vbb->userinfo['lastvisit'])? 'new' : 'old';			
			$get_most_viewed[fullforumtitle] = strip_tags($this->vbb->forumcache["$get_most_viewed[forumid]"]['title_clean']);		
			$get_most_viewed[lastpostdate] = vbdate($this->vbb->options['dateformat'], $get_most_viewed[lastpost], true);
			$get_most_viewed[lastposttime] = vbdate($this->vbb->options['timeformat'], $get_most_viewed[lastpost]);
			$get_most_viewed[startdate] = vbdate($this->vbb->options['dateformat'], $get_most_viewed[dateline], true);
			$get_most_viewed[starttime] = vbdate($this->vbb->options['timeformat'], $get_most_viewed[dateline]);
			$get_most_viewed[gotonewpost] = array('goto' => 'newpost');
			
			$temp = vB_Template::create('vietvbb_topstats_thread_info');
			$temp->register('content', $get_most_viewed);
			$get_most_viewed[threadinfo] = $temp->render();
			
			$temp = vB_Template::create('vietvbb_topstats_most_viewed');
			$temp->register('content', $get_most_viewed);
			$output .= $temp->render();
		}
		
		if (!$output)	$output = 'No Result';
		$this->db->free_result($query);
		return $output;
	}
	
	
	
	############################# Hostest Thread ############################
	private function hottest_threads ()	{
		global $vbphrase;
		if($this->exclfids) $exclforum = "AND forumid NOT IN({$this->exclfids})";
		$prefixid = ($this->vbb->options['vietvbbtopstats_prefix'])? ', prefixid' : '';
		$query = $this->db->query_read("
			SELECT threadid, title, lastpost, forumid, views, replycount, dateline, visible, open, postusername, lastposter, lastposterid, postuserid $prefixid
			FROM " . TABLE_PREFIX . "thread AS thread
			WHERE NOT ISNULL(threadid) AND visible = '1' AND replycount > '0' AND open!='10' $exclforum
			ORDER BY replycount DESC
			LIMIT 0, {$this->result}
			");
			
		while ($get_hottest_threads = $this->db->fetch_array($query))	{
			$get_hottest_threads[fullthreadtitle] = strip_tags($get_hottest_threads[title]);
			if ($this->trimthreadtitle > 0)	{
				$get_hottest_threads[titletrimmed] = fetch_trimmed_title($get_hottest_threads[fullthreadtitle], $this->trimthreadtitle);
			}
			else	{
				$get_hottest_threads[titletrimmed] = $get_hottest_threads[fullthreadtitle];
			}
			if ($get_hottest_threads['prefixid'])	{
				$get_hottest_threads['prefix_plain_html'] = htmlspecialchars_uni($vbphrase["prefix_$get_hottest_threads[prefixid]_title_plain"]);
				$get_hottest_threads['prefix_rich'] = $vbphrase["prefix_$get_hottest_threads[prefixid]_title_rich"];
			}	else	{
				$get_hottest_threads['prefix_plain_html'] = '';
				$get_hottest_threads['prefix_rich'] = '';
			}
			$get_hottest_threads[newpost] = ($get_hottest_threads[lastpost] > $this->vbb->userinfo['lastvisit'])? 'new' : 'old';
			$get_hottest_threads[fullforumtitle] = strip_tags($this->vbb->forumcache["$get_hottest_threads[forumid]"]['title_clean']);		
			$get_hottest_threads[lastpostdate] = vbdate($this->vbb->options['dateformat'], $get_hottest_threads[lastpost], true);
			$get_hottest_threads[lastposttime] = vbdate($this->vbb->options['timeformat'], $get_hottest_threads[lastpost]);
			$get_hottest_threads[startdate] = vbdate($this->vbb->options['dateformat'], $get_hottest_threads[dateline], true);
			$get_hottest_threads[starttime] = vbdate($this->vbb->options['timeformat'], $get_hottest_threads[dateline]);
			$get_hottest_threads[gotonewpost] = array('goto' => 'newpost');
			
			$temp = vB_Template::create('vietvbb_topstats_thread_info');
			$temp->register('content', $get_hottest_threads);
			$get_hottest_threads[threadinfo] = $temp->render();
			
			$temp = vB_Template::create('vietvbb_topstats_hottest_threads');
			$temp->register('content', $get_hottest_threads);
			$output .= $temp->render();			
		}
		
		if (!$output)	$output = 'No Result';
		$this->db->free_result($query);
		return $output;
	}
	
	
	
	################### Top Reputation  ########################
	private function top_reputation ()	{
		$query = $this->db->query_read("
			SELECT userid, usergroupid, displaygroupid, username, posts, reputation
			FROM " . TABLE_PREFIX . "user AS user
			WHERE reputation > '0' {$this->exclgroups}
			ORDER BY reputation DESC
			LIMIT 0, {$this->result}
			");
		while ($getstats_rep = $this->db->fetch_array($query))	{
			$getstats_rep[fullusername] = strip_tags($getstats_rep[username]);
			if ($this->trimusername > 0)	{
				$getstats_rep[username] = fetch_trimmed_title($getstats_rep[fullusername], $this->trimusername);
			}
			else	{
				$getstats_rep[username] = $getstats_rep[fullusername];
			}
			$getstats_rep[musername] = fetch_musername($getstats_rep);
			if ($this->vbb->options['vietvbbtopstats_bold_remove'])	{
				$getstats_rep[musername] = str_replace($this->bold, '', $getstats_rep[musername]);
			}
			
			$temp = vB_Template::create('vietvbb_topstats_top_reputation');
			$temp->register('content', $getstats_rep);
			$output .= $temp->render();
		}
		
		if (!$output)	$output = 'No Result';
		$this->db->free_result($query);
		return $output;
	}
	
	
	
	##################### Top Thanked ##############################
	private function thanked_members()	{
		$query = $this->db->query_read("
			SELECT userid, usergroupid, displaygroupid, username, posts, post_thanks_thanked_times
			FROM " . TABLE_PREFIX . "user AS user
			WHERE post_thanks_thanked_times > '0' {$this->exclgroups}
			ORDER BY post_thanks_thanked_times DESC
			LIMIT 0, {$this->result}
			");
		
		while ($getstats_thx = $this->db->fetch_array($query))	{
			$getstats_thx[fullusername] = strip_tags($getstats_thx[username]);
			if ($this->trimusername > 0)	{
				$getstats_thx[username] = fetch_trimmed_title($getstats_thx[fullusername], $this->trimusername);
			}
			else	{
				$getstats_thx[username] = $getstats_thx[fullusername];
			}
			$getstats_thx[musername] = fetch_musername($getstats_thx);
			if ($this->vbb->options['vietvbbtopstats_bold_remove'])	{
				$getstats_thx[musername] = str_replace($this->bold, '', $getstats_thx[musername]);
			}
			
			$temp = vB_Template::create('vietvbb_topstats_thanked_members');
			$temp->register('content', $getstats_thx);
			$output .= $temp->render();
		}
		
		if (!$output)	$output = 'No Result';
		$this->db->free_result($query);
		return $output;
	}
	
	
	
	###################### Top Forums ###########################
	private function top_forums()	{
		if($this->exclfids) $exclforum = "AND forumid NOT IN({$this->exclfids})";
		$query = $this->db->query_read("
			SELECT forumid, title_clean, replycount, lastpost
			FROM " . TABLE_PREFIX . "forum AS forum
			WHERE replycount > '0' $exclforum
			ORDER BY replycount DESC
			LIMIT 0, {$this->result}
			");
		while ($get_topforums = $this->db->fetch_array($query))	{
			$get_topforums[fullforumtitle] = strip_tags($get_topforums[title_clean]);
			if ($this->trimforumtitles > 0)	{
				$get_topforums[titletrimmed] = fetch_trimmed_title($get_topforums[fullforumtitle], $this->trimforumtitles);
			}
			else	{
				$get_topforums[titletrimmed] = $get_topforums[fullforumtitle];
			}
			$get_topforums[newpost] = ($get_topforums[lastpost] > $this->vbb->userinfo['lastvisit'])? 'new' : 'old';
			
			$temp = vB_Template::create('vietvbb_topstats_top_forums');
			$temp->register('content', $get_topforums);
			$output .= $temp->render();
		}
		
		if (!$output)	$output = 'No Result';
		$this->db->free_result($query);
		return $output;
	}
	
	
	
	###################### Top Infractions  #########################
	private function top_infractions()	{
		$query = $this->db->query_read("
			SELECT COUNT(infraction.infractionid) AS infs, SUM(infraction.points) AS infpoints, infraction.userid, user.usergroupid, user.displaygroupid, user.username
			FROM " . TABLE_PREFIX . "infraction AS infraction
			LEFT JOIN " . TABLE_PREFIX . "user AS user ON (infraction.userid = user.userid)
			WHERE infraction.userid > '0' AND infraction.points > '0' {$this->exclgroups}
			GROUP BY infraction.userid
			ORDER BY infpoints DESC
			LIMIT 0, {$this->result}
			");
		
		while ($getstats_infraction = $this->db->fetch_array($query))	{
			$getstats_infraction[fullusername] = strip_tags($getstats_infraction[username]);
			if ($this->trimusername > 0)	{
				$getstats_infraction[username] = fetch_trimmed_title($getstats_infraction[fullusername], $this->trimusername);
			}
			else	{
				$getstats_infraction[username] = $getstats_infraction[fullusername];
			}
			$getstats_infraction[musername] = fetch_musername($getstats_infraction);
			if ($this->vbb->options['vietvbbtopstats_bold_remove'])	{
				$getstats_infraction[musername] = str_replace($this->bold, '', $getstats_infraction[musername]);
			}
			
			$temp = vB_Template::create('vietvbb_topstats_top_infractions');
			$temp->register('content', $getstats_infraction);
			$output .= $temp->render();			
		}
		
		if (!$output)	$output = 'No Result';
		$this->db->free_result($query);
		return $output;
	}
	
	
	##################### Class ads  #########################
	private function latest_classads()	{
		global $vbphrase;
		$forums = ($this->vbb->options['vietvbbtopstats_classads_forums'])? $this->vbb->options['vietvbbtopstats_classads_forums'] : 0;
		$prefixid = ($this->vbb->options['vietvbbtopstats_prefix'])? ', prefixid' : '';
		$query = $this->db->query_read("
			SELECT threadid, title, lastpost, forumid, dateline, visible, open, views, replycount, postusername, postuserid, lastposter, lastposterid $prefixid
			FROM " . TABLE_PREFIX . "thread AS thread
			WHERE NOT ISNULL(threadid) AND forumid IN (".$forums.") AND visible = '1' AND open!='10'
			ORDER BY dateline DESC
			LIMIT 0, {$this->result}
			");
		
		while ($get_latest_classads = $this->db->fetch_array($query))	{
			$get_latest_classads[fullthreadtitle] = strip_tags($get_latest_classads[title]);
			if ($this->trimthreadtitle > 0)	{
				$get_latest_classads[titletrimmed] = fetch_trimmed_title($get_latest_classads[fullthreadtitle], $this->trimthreadtitle);
			}
			else	{
				$get_latest_classads[titletrimmed] = $get_latest_classads[fullthreadtitle];
			}
			if ($get_latest_classads['prefixid'])	{
				$get_latest_classads['prefix_plain_html'] = htmlspecialchars_uni($vbphrase["prefix_$get_latest_classads[prefixid]_title_plain"]);
				$get_latest_classads['prefix_rich'] = $vbphrase["prefix_$get_latest_classads[prefixid]_title_rich"];
			}	else	{
				$get_latest_classads['prefix_plain_html'] = '';
				$get_latest_classads['prefix_rich'] = '';
			}
			
			$get_latest_classads[newpost] = ($get_latest_classads[lastpost] > $this->vbb->userinfo['lastvisit'])? 'new' : 'old';			
			$get_latest_classads[fullforumtitle] = strip_tags($this->vbb->forumcache["$get_latest_classads[forumid]"]['title_clean']);
			if ($this->trimforumtitles > 0)	{
				$get_latest_classads[forumtitle] = fetch_trimmed_title($get_latest_classads[fullforumtitle], $this->trimforumtitles);
			}
			else	{
				$get_latest_classads[forumtitle] = $get_latest_classads[fullforumtitle];
			}			
			
			$get_latest_classads[lastpostdate] = vbdate($this->vbb->options['dateformat'], $get_latest_classads[lastpost], true);
			$get_latest_classads[lastposttime] = vbdate($this->vbb->options['timeformat'], $get_latest_classads[lastpost]);
			$get_latest_classads[startdate] = vbdate($this->vbb->options['dateformat'], $get_latest_classads[dateline], true);
			$get_latest_classads[starttime] = vbdate($this->vbb->options['timeformat'], $get_latest_classads[dateline]);
			$get_latest_classads[gotonewpost] = array('goto' => 'newpost');
			
			$temp = vB_Template::create('vietvbb_topstats_thread_info');
			$temp->register('content', $get_latest_classads);
			$get_latest_classads[threadinfo] = $temp->render();
			
			$temp = vB_Template::create('vietvbb_topstats_latest_classads');
			$temp->register('content', $get_latest_classads);
			$output .= $temp->render();	
		}
		
		if (!$output)	$output = 'No Result';
		$this->db->free_result($query);
		return $output;
	}
	
	
		
	################### Top credit ########################
	private function top_credits()	{
		$moneyfield = $this->vbb->options['vietvbbtopstats_money_field'];
		if ($this->vbb->options['vietvbbtopstats_excl_groups_listing'])	{
			$exclgroup = $this->vbb->options['vietvbbtopstats_excl_groups_listing'];
			$exclgroup .= ',';
		}
		if ($this->vbb->options['vietvbbtopstats_excl_richer_groups_listing'])	{
			$exclgroup .= $this->vbb->options['vietvbbtopstats_excl_richer_groups_listing'];
		}
		if ($exclgroup)	{
			$giau_tien = "AND usergroupid NOT IN($exclgroup)";
		}
		
		$query = $this->db->query_read("
			SELECT userid, usergroupid, displaygroupid, username, $moneyfield
			FROM " . TABLE_PREFIX . "user AS user
			WHERE $moneyfield > 0 $giau_tien
			ORDER BY $moneyfield DESC
			LIMIT 0, {$this->result}
			");
		
		while ($getstats_credit = $this->db->fetch_array($query))	{
			$getstats_credit[fullusername] = strip_tags($getstats_credit[username]);
			if ($this->trimusername > 0)	{
				$getstats_credit[username] = fetch_trimmed_title($getstats_credit[fullusername], $this->trimusername);
			}
			else	{
				$getstats_credit[username] = $getstats_credit[fullusername];
			}
			$getstats_credit[musername] = fetch_musername($getstats_credit);
			$getstats_credit[credits] = round($getstats_credit[$moneyfield],0);
			if ($this->vbb->options['vietvbbtopstats_bold_remove'])	{
				$getstats_credit[musername] = str_replace($this->bold, '', $getstats_credit[musername]);
			}
			
			$temp = vB_Template::create('vietvbb_topstats_top_credits');
			$temp->register('content', $getstats_credit);
			$output .= $temp->render();	
		}
		
		if (!$output)	$output = 'No Result';
		$this->db->free_result($query);
		return $output;
	}

	
	
	################## Latest Post  ###########################
	private function latest_posts($custom=false)	{
		global $vbphrase;
		if($this->exclfids) $exclforum = "AND thread.forumid NOT IN({$this->exclfids})";
		if ($custom) $custom = "AND thread.forumid IN ($custom)";
		else $custom = '';
		$prefixid = ($this->vbb->options['vietvbbtopstats_prefix'])? ', thread.prefixid' : '';
		
		$query = $this->db->query_read("
			SELECT thread.threadid, thread.title, thread.lastpost, thread.forumid, thread.replycount, thread.postusername, thread.postuserid, thread.lastposter, thread.lastposterid, thread.dateline, thread.views, thread.visible, thread.open, user.usergroupid, user.displaygroupid $prefixid
			FROM " . TABLE_PREFIX . "thread AS thread
			LEFT JOIN " . TABLE_PREFIX . "user AS user ON (user.userid = thread.lastposterid)
			WHERE NOT ISNULL(thread.threadid) AND thread.visible = '1' AND thread.open!='10' $custom $exclforum
			ORDER BY lastpost DESC
			LIMIT 0, {$this->result}
			");
		
		while ($get_new_posts = $this->db->fetch_array($query))	{
			$get_new_posts[username] = $get_new_posts[lastposter]; $get_new_posts[userid] = $get_new_posts[lastposterid];
			$get_new_posts[fullthreadtitle] = strip_tags($get_new_posts[title]);
			if ($this->trimthreadtitle > 0)	{
				$get_new_posts[titletrimmed] = fetch_trimmed_title($get_new_posts[fullthreadtitle], $this->trimthreadtitle);
			}
			else	{
				$get_new_posts[titletrimmed] = $get_new_posts[fullthreadtitle];
			}
			if ($get_new_posts['prefixid'])	{
				$get_new_posts['prefix_plain_html'] = htmlspecialchars_uni($vbphrase["prefix_$get_new_posts[prefixid]_title_plain"]);
				$get_new_posts['prefix_rich'] = $vbphrase["prefix_$get_new_posts[prefixid]_title_rich"];
			}	else	{
				$get_new_posts['prefix_plain_html'] = '';
				$get_new_posts['prefix_rich'] = '';
			}
			$get_new_posts[newpost] = ($get_new_posts[lastpost] > $this->vbb->userinfo['lastvisit'])? 'new' : 'old';			
			$get_new_posts[fullusername] = strip_tags($get_new_posts[username]);
			if ($this->trimusername > 0)	{
				$get_new_posts[username] = fetch_trimmed_title($get_new_posts[fullusername], $this->trimusername);
			}
			else	{
				$get_new_posts[username] = $get_new_posts[fullusername];
			}
			$get_new_posts[musername] = fetch_musername($get_new_posts);			
			$get_new_posts[fullforumtitle] = strip_tags($this->vbb->forumcache["$get_new_posts[forumid]"]['title_clean']);		
			if ($this->vbb->options['vietvbbtopstats_bold_remove'])	{
				$get_new_posts[musername] = str_replace($this->bold, '', $get_new_posts[musername]); 
			}
			$get_new_posts[lastpostdate] = vbdate($this->vbb->options['dateformat'], $get_new_posts[lastpost], true);
			$get_new_posts[lastposttime] = vbdate($this->vbb->options['timeformat'], $get_new_posts[lastpost]);
			$get_new_posts[startdate] = vbdate($this->vbb->options['dateformat'], $get_new_posts[dateline], true);
			$get_new_posts[starttime] = vbdate($this->vbb->options['timeformat'], $get_new_posts[dateline]);
			
			$get_new_posts[gotonewpost] = array('goto' => 'newpost');
			
			$temp = vB_Template::create('vietvbb_topstats_thread_info');
			$temp->register('content', $get_new_posts);
			$get_new_posts[threadinfo] = $temp->render();
			
			$temp = vB_Template::create('vietvbb_topstats_latest_posts');
			$temp->register('content', $get_new_posts);
			$output .= $temp->render();
		}
		
		if (!$output)	$output = 'No Result';
		$this->db->free_result($query);
		return $output;
	}
				
			
			
			
	######################## Newest Blog #############################
	private function latest_blogs()	{
		$query = $this->db->query_read("
			SELECT blog.blogid, blog.title, blog.userid, blog.username, blog.postedby_userid, blog.postedby_username, blog.dateline, blog.lastcommenter, blog.lastcomment, blog.views, blog.comments_visible, user.displaygroupid, user.usergroupid
			FROM " . TABLE_PREFIX . "blog AS blog
			LEFT JOIN " . TABLE_PREFIX . "user AS user ON (blog.userid = user.userid)
			WHERE state = 'visible'
			ORDER BY dateline DESC
			LIMIT 0, {$this->result}
			");
		while ($get_latest_blogs = $this->db->fetch_array($query))	{
			$get_latest_blogs[fullblogtitle] = strip_tags($get_latest_blogs[title]);
			if ($this->trimthreadtitle > 0)	{
				$get_latest_blogs[titletrimmed] = fetch_trimmed_title($get_latest_blogs[fullblogtitle], $this->trimthreadtitle);
			}
			else	{
				$get_latest_blogs[titletrimmed] = $get_latest_blogs[fullblogtitle];
			}
			
			$get_latest_blogs[fullusername] = strip_tags($get_latest_blogs[username]);
			if ($this->trimusername > 0)	{
				$get_latest_blogs[username] = fetch_trimmed_title($get_latest_blogs[fullusername], $this->trimusername);
			}
			else	{
				$get_latest_blogs[username] = $get_latest_blogs[fullusername];
			}
			$get_latest_blogs[musername] = fetch_musername($get_latest_blogs);
			
			if ($this->vbb->options['vietvbbtopstats_bold_remove'])	{
				$get_latest_blogs[musername] = str_replace($this->bold, '', $get_latest_blogs[musername]);
			}
			$get_latest_blogs[newpost] = ($get_latest_blogs[dateline] > $this->vbb->userinfo['lastvisit'])? 'new' : 'old';
			
			$get_latest_blogs[lastpostdate] = vbdate($this->vbb->options['dateformat'], $get_latest_blogs[lastcomment], true);
			$get_latest_blogs[lastposttime] = vbdate($this->vbb->options['timeformat'], $get_latest_blogs[lastcomment]);
			$get_latest_blogs[startdate] = vbdate($this->vbb->options['dateformat'], $get_latest_blogs[dateline], true);
			$get_latest_blogs[starttime] = vbdate($this->vbb->options['timeformat'], $get_latest_blogs[dateline]);
			
			$get_latest_blogs[username] = $get_latest_blogs[fullusername];
			
			$temp = vB_Template::create('vietvbb_topstats_blog_info');
			$temp->register('content', $get_latest_blogs);
			$get_latest_blogs[bloginfo] = $temp->render();
			
			$temp = vB_Template::create('vietvbb_topstats_latest_blogs');
			$temp->register('content', $get_latest_blogs);
			$output .= $temp->render();	
		}		
		
		if (!$output)	$output = 'No Result';
		$this->db->free_result($query);
		return $output;
	}
	
	
	
	
	###################   Latest Comments #####################	
	private function latest_comments()	{
		$query = $this->db->query_read("
			SELECT blog.blogid, blog.title, blog.userid, blog.username, blog.postedby_userid, blog.postedby_username, blog.dateline, blog.lastcommenter, blog.lastcomment, blog.views, blog.comments_visible, blog_text.userid as lastcommenterid, IF(user.displaygroupid=0, user.usergroupid, user.displaygroupid) AS displaygroupid
			FROM " . TABLE_PREFIX . "blog AS blog
			LEFT JOIN " . TABLE_PREFIX . "blog_text as blog_text ON (blog.lastblogtextid = blog_text.blogtextid)
			LEFT JOIN " . TABLE_PREFIX . "user AS user ON (blog_text.userid = user.userid)
			WHERE blog.state = 'visible' and blog.comments_visible > 0
			ORDER BY blog.lastcomment DESC
			LIMIT 0, {$this->result}
			");
		while ($get_latest_conmments = $this->db->fetch_array($query))	{
			$get_latest_conmments[fullblogtitle] = strip_tags($get_latest_conmments[title]);
			if ($this->trimthreadtitle > 0)	{
				$get_latest_conmments[titletrimmed] = fetch_trimmed_title($get_latest_conmments[fullblogtitle], $this->trimthreadtitle);
			}
			else	{
				$get_latest_conmments[titletrimmed] = $get_latest_conmments[fullblogtitle];
			}
			
			$get_latest_conmments[fullusername] = strip_tags($get_latest_conmments[lastcommenter]);
			if ($this->trimusername > 0)	{
				$get_latest_conmments[lastcommenter] = fetch_trimmed_title($get_latest_conmments[fullusername], $this->trimusername);
			}
			else	{
				$get_latest_conmments[lastcommenter] = $get_latest_conmments[fullusername];
			}
			$get_latest_conmments[musername] = fetch_musername($get_latest_conmments, 'displaygroupid' ,'lastcommenter');
			
			if ($this->vbb->options['vietvbbtopstats_bold_remove'])	{
				$get_latest_conmments[musername] = str_replace($this->bold, '', $get_latest_conmments[musername]);
			}
			$get_latest_conmments[newpost] = ($get_latest_conmments[lastcomment] > $this->vbb->userinfo['lastvisit'])? 'new' : 'old';
			
			$get_latest_conmments[lastpostdate] = vbdate($this->vbb->options['dateformat'], $get_latest_conmments[lastcomment], true);
			$get_latest_conmments[lastposttime] = vbdate($this->vbb->options['timeformat'], $get_latest_conmments[lastcomment]);
			$get_latest_conmments[startdate] = vbdate($this->vbb->options['dateformat'], $get_latest_conmments[dateline], true);
			$get_latest_conmments[starttime] = vbdate($this->vbb->options['timeformat'], $get_latest_conmments[dateline]);
			
			$get_latest_conmments[lastcommenter] = $get_latest_conmments[fullusername];
			
			$temp = vB_Template::create('vietvbb_topstats_blog_info');
			$temp->register('content', $get_latest_conmments);
			$get_latest_conmments[bloginfo] = $temp->render();
			
			$temp = vB_Template::create('vietvbb_topstats_latest_comments');
			$temp->register('content', $get_latest_conmments);
			$output .= $temp->render();	
		}		
		
		if (!$output)	$output = 'No Result';
		$this->db->free_result($query);
		return $output;
	}
	
	######################## Most Viewed Entrys #############################
	private function most_viewed_entrys()	{
		$query = $this->db->query_read("
			SELECT blog.blogid, blog.title, blog.userid, blog.username, blog.postedby_userid, blog.postedby_username, blog.dateline, blog.lastcommenter, blog.lastcomment, blog.views, blog.comments_visible
			FROM " . TABLE_PREFIX . "blog AS blog			
			WHERE state = 'visible' AND views > 0
			ORDER BY views DESC
			LIMIT 0, {$this->result}
			");
		while ($get_most_viewed_entrys = $this->db->fetch_array($query))	{
			$get_most_viewed_entrys[fullblogtitle] = strip_tags($get_most_viewed_entrys[title]);
			if ($this->trimthreadtitle > 0)	{
				$get_most_viewed_entrys[titletrimmed] = fetch_trimmed_title($get_most_viewed_entrys[fullblogtitle], $this->trimthreadtitle);
			}
			else	{
				$get_most_viewed_entrys[titletrimmed] = $get_most_viewed_entrys[fullblogtitle];
			}
			
			$get_most_viewed_entrys[newpost] = ($get_most_viewed_entrys[dateline] > $this->vbb->userinfo['lastvisit'])? 'new' : 'old';
			
			$get_most_viewed_entrys[lastpostdate] = vbdate($this->vbb->options['dateformat'], $get_most_viewed_entrys[lastcomment], true);
			$get_most_viewed_entrys[lastposttime] = vbdate($this->vbb->options['timeformat'], $get_most_viewed_entrys[lastcomment]);
			$get_most_viewed_entrys[startdate] = vbdate($this->vbb->options['dateformat'], $get_most_viewed_entrys[dateline], true);
			$get_most_viewed_entrys[starttime] = vbdate($this->vbb->options['timeformat'], $get_most_viewed_entrys[dateline]);		
			
			$temp = vB_Template::create('vietvbb_topstats_blog_info');
			$temp->register('content', $get_most_viewed_entrys);
			$get_most_viewed_entrys[bloginfo] = $temp->render();
			
			$temp = vB_Template::create('vietvbb_topstats_most_viewed_entrys');
			$temp->register('content', $get_most_viewed_entrys);
			$output .= $temp->render();	
		}		
		
		if (!$output)	$output = 'No Result';
		$this->db->free_result($query);
		return $output;
	}
	
	
	
	######################## Hottest Entrys #############################
	private function hottest_entrys()	{
		$query = $this->db->query_read("
			SELECT blog.blogid, blog.title, blog.userid, blog.username, blog.postedby_userid, blog.postedby_username, blog.dateline, blog.lastcommenter, blog.lastcomment, blog.views, blog.comments_visible
			FROM " . TABLE_PREFIX . "blog AS blog			
			WHERE state = 'visible' AND comments_visible > 0
			ORDER BY comments_visible DESC
			LIMIT 0, {$this->result}
			");
		while ($get_hottest_entrys = $this->db->fetch_array($query))	{
			$get_hottest_entrys[fullblogtitle] = strip_tags($get_hottest_entrys[title]);
			if ($this->trimthreadtitle > 0)	{
				$get_hottest_entrys[titletrimmed] = fetch_trimmed_title($get_hottest_entrys[fullblogtitle], $this->trimthreadtitle);
			}
			else	{
				$get_hottest_entrys[titletrimmed] = $get_hottest_entrys[fullblogtitle];
			}
			
			$get_hottest_entrys[newpost] = ($get_hottest_entrys[dateline] > $this->vbb->userinfo['lastvisit'])? 'new' : 'old';
			
			$get_hottest_entrys[lastpostdate] = vbdate($this->vbb->options['dateformat'], $get_hottest_entrys[lastcomment], true);
			$get_hottest_entrys[lastposttime] = vbdate($this->vbb->options['timeformat'], $get_hottest_entrys[lastcomment]);
			$get_hottest_entrys[startdate] = vbdate($this->vbb->options['dateformat'], $get_hottest_entrys[dateline], true);
			$get_hottest_entrys[starttime] = vbdate($this->vbb->options['timeformat'], $get_hottest_entrys[dateline]);		
			
			$temp = vB_Template::create('vietvbb_topstats_blog_info');
			$temp->register('content', $get_hottest_entrys);
			$get_hottest_entrys[bloginfo] = $temp->render();
			
			$temp = vB_Template::create('vietvbb_topstats_hottest_entrys');
			$temp->register('content', $get_hottest_entrys);
			$output .= $temp->render();	
		}		
		
		if (!$output)	$output = 'No Result';
		$this->db->free_result($query);
		return $output;
	}
	
	
	###################### Top Bloggers ##############################
	private function top_bloggers()	{
		$query = $this->db->query_read("
			SELECT blog_user.bloguserid as userid, blog_user.entries, IF(user.displaygroupid=0, user.usergroupid, user.displaygroupid) AS displaygroupid, user.username
			FROM " . TABLE_PREFIX . "blog_user AS blog_user
			INNER JOIN " . TABLE_PREFIX . "user as user ON (blog_user.bloguserid=user.userid)
			WHERE blog_user.entries>0 {$this->exclgroups}
			ORDER BY blog_user.entries DESC
			LIMIT 0, {$this->result}
			");
		while ($top_bloggers = $this->db->fetch_array($query))	{
			$top_bloggers[fullusername] = strip_tags($top_bloggers[username]);
			if ($this->trimusername > 0)	{
				$top_bloggers[username] = fetch_trimmed_title($top_bloggers[fullusername], $this->trimusername);
			}
			else	{
				$top_bloggers[username] = $top_bloggers[fullusername];
			}
			
			$top_bloggers[musername] = fetch_musername($top_bloggers);
			if ($this->vbb->options['vietvbbtopstats_bold_remove'])	{
				$top_bloggers[musername] = str_replace($this->bold, '', $top_bloggers[musername]);
			}		
			
			$temp = vB_Template::create('vietvbb_topstats_top_bloggers');
			$temp->register('content', $top_bloggers);
			$output .= $temp->render();	
		}		
		
		if (!$output)	$output = 'No Result';
		$this->db->free_result($query);
		return $output;
	}

	########################################################
}

###########################################################################
#####  [AJAX]VietVBB - Advanced Forum Statistics for vbulletin 4.x.x  #####
#####  Linhnt - VietVBBTeam - www.vietvbb.vn                          #####
###########################################*###############################
?>