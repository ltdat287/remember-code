<?php
// ######################## SET PHP ENVIRONMENT ###########################
error_reporting(E_ALL & ~E_NOTICE);

// #################### PRE-CACHE TEMPLATES AND DATA ######################
$phrasegroups = array('style','evbs_sstab');
$specialtemplates = array('products');

// ########################## REQUIRE BACK-END ############################
require_once('./global.php');
require_once(DIR . '/includes/adminfunctions_template.php');

print_cp_header();

/////////////////////// front page
if ( $_REQUEST['do'] == 'removeitemmenu') 
{
$vbulletin->input->clean_array_gpc('r', array(
		'childid' => TYPE_INT
	));

	if (empty($vbulletin->GPC['childid']))
	{
		print_stop_message('evbs_sstab_advanced_tab_error');
	}
	
		
	$childid = $vbulletin->GPC['childid'];
	
	$db->query_write("DELETE FROM " . TABLE_PREFIX . "evbs_sstabs_childs WHERE childid = ". $childid ."");
	$_REQUEST['do'] = 'listitemmenu';
}

if ( $_REQUEST['do'] == 'modifyorderitemmenu' or $_REQUEST['do'] == 'modifyordersubmenu') 
{
	$vbulletin->input->clean_array_gpc('p', array(
		'idcambiomass' 		=> TYPE_ARRAY,));
	$vbulletin->input->clean_array_gpc('p', array(
		'activo' 		=> TYPE_ARRAY,));
	$vbulletin->input->clean_array_gpc('p', array(
		'orden' 		=> TYPE_ARRAY,));
	$activo 		= $vbulletin->GPC['activo'];
	$orden 		= $vbulletin->GPC['orden'];	
	$idcambiomass 		= $vbulletin->GPC['idcambiomass'];	
	
	$se_actualizo=false;	
	foreach($idcambiomass as  $key =>$valor)
	{
		if($activo[$key]=='on') {$activo[$key]=1;}else{$activo[$key]=0;};
				
		$db->query_write("UPDATE " . TABLE_PREFIX . "evbs_sstabs_childs
				SET `enable` = ".$activo[$key].",
				displayorder = ".$orden[$key]."
				 WHERE childid =".$key);
		
		$se_actualizo=true;
	}
	if ($_REQUEST['do']=='modifyorderitemmenu')
	{
		$_REQUEST['do'] = 'listitemmenu';
	}
	else
	{
		$_REQUEST['do'] = 'listsubmenu';
	}
}


if ( $_REQUEST['do'] == 'remove') 
{
$vbulletin->input->clean_array_gpc('r', array(
		'tabid' => TYPE_INT
	));

	if (empty($vbulletin->GPC['tabid']))
	{
		print_stop_message('evbs_sstab_advanced_tab_error');
	}
	
		
	$tabid = $vbulletin->GPC['tabid'];
	
	$db->query_write("DELETE FROM " . TABLE_PREFIX . "evbs_sstabs WHERE tabid = ". $tabid ."");
	$_REQUEST['do'] = '';
}




if ( $_REQUEST['do'] == 'modifyorder' ) 
{

	$vbulletin->input->clean_array_gpc('p', array(
		'idcambiomass' 		=> TYPE_ARRAY,));
	$vbulletin->input->clean_array_gpc('p', array(
		'activo' 		=> TYPE_ARRAY,));
	$vbulletin->input->clean_array_gpc('p', array(
		'orden' 		=> TYPE_ARRAY,));
	$activo 		= $vbulletin->GPC['activo'];
	$orden 		= $vbulletin->GPC['orden'];	
	$idcambiomass 		= $vbulletin->GPC['idcambiomass'];	
	
	$se_actualizo=false;	
	foreach($idcambiomass as  $key =>$valor)
	{
		if($activo[$key]=='on') {$activo[$key]=1;}else{$activo[$key]=0;};
				
		$db->query_write("UPDATE " . TABLE_PREFIX . "evbs_sstabs
				SET `enable` = ".$activo[$key].",
				displayorder = ".$orden[$key]."
				 WHERE tabid =".$key);
		
		$se_actualizo=true;
	}

$_REQUEST['do'] = '';
}

if ( $_REQUEST['do'] == 'updateitemmenu' ) 
{

$vbulletin->input->clean_array_gpc('p', array(
	'childid'  => TYPE_INT, 
	'tabid'  => TYPE_INT, 
	'title' => TYPE_STR, 
	'url' => TYPE_STR, 
	'target' => TYPE_STR,
	'pusergroup' => TYPE_ARRAY,
	'nusergroup' => TYPE_ARRAY,
	'enable' => TYPE_INT,
	'displayorder' => TYPE_INT,
	'tipo' => TYPE_STR,
	));
	
	$childid = intval($vbulletin->GPC['childid']);
	$tabid = intval($vbulletin->GPC['tabid']);
	$title = str_replace ("'", "''", $vbulletin->GPC['title']);
	$url = str_replace ("'", "''", $vbulletin->GPC['url']);
	$target = $vbulletin->GPC['target'];
	$pusergroup = implode(",", $vbulletin->GPC['pusergroup']);
	$nusergroup = implode(",", $vbulletin->GPC['nusergroup']);
	$enable = $vbulletin->GPC['enable'];
	$displayorder = $vbulletin->GPC['displayorder'];
	
	
	if ($vbulletin->GPC['tipo']!='submenu')
	{
		$tipo = 'menuitem';
	}
	else
	{
		$tipo = 'submenu';
	}

	
	
	if (empty($vbulletin->GPC['childid']))
	{
		$db->query_write("INSERT INTO " . TABLE_PREFIX . "evbs_sstabs_childs(tabid,title,url,target,pusergroup,nusergroup,enable,tipo,displayorder)
				VALUES (
				'".$tabid."',
				'".$title."',
				'".$url."',
				'".$target."',
				'".$pusergroup."',
				'".$nusergroup."',
				'".$enable."',
				'".$tipo."',
				'".$displayorder."'
				)");
	}
	else
	{
		$db->query_write("UPDATE " . TABLE_PREFIX . "evbs_sstabs_childs SET 
		tabid = '".$tabid."',
		title = '".$title."',
		url = '".$url."',
		target = '".$target."',
		pusergroup = '".$pusergroup."',
		nusergroup = '".$nusergroup."',
		`enable` = '".$enable."',
		displayorder = '".$displayorder."' 
		WHERE childid =$childid");
	}
	if ($vbulletin->GPC['tipo']!='submenu')
	{
		$_REQUEST['do'] = 'listitemmenu';
	}
	else
	{
		$_REQUEST['do'] = 'listsubmenu';
	}

}		

if ( $_REQUEST['do'] == 'listitemmenu' or $_REQUEST['do'] == 'listsubmenu') 
{
	
	if ($_REQUEST['do'] == 'listsubmenu')
	{
		$tipo = 'submenu' ;
		$formulario= 'modifyordersubmenu';
		$plusurl= "&tipo=submenu";
		$headtitle = $vbphrase['evbs_sstab_advanced_manage_submenu'];
	}
	else
	{
		$tipo = 'menuitem';
		$formulario = 'modifyorderitemmenu';
		$headtitle = $vbphrase['evbs_sstab_advanced_child_title'];
	}
	
	print_form_header('evbs_sstabs', $formulario, 0, 1, 'listitemmenu');
	print_table_header($headtitle,6);
	$sql = "SELECT IF(isnull(tab.title),'".$vbphrase['evbs_sstab_advanced_no_asigned']."',tab.title) as padre, child.childid, child.tabid, child.title, child.url, child.target, child.pusergroup, child.nusergroup, child.enable, child.displayorder  
	FROM " . TABLE_PREFIX . "evbs_sstabs_childs as child 
	LEFT JOIN " . TABLE_PREFIX . "evbs_sstabs as tab on child.tabid=tab.tabid
	WHERE child.tipo='$tipo'
	ORDER BY tab.tabid,displayorder
	";
	$result = $db->query_read($sql);
	
	print_cells_row(array($vbphrase['evbs_sstab_advanced_menu_name'],
			$vbphrase['evbs_sstab_advanced_url'],
			$vbphrase['evbs_sstab_advanced_ug_title'],
			$vbphrase['evbs_sstab_advanced_active'],
			$vbphrase['evbs_sstab_advanced_orden_display'],
			$vbphrase['evbs_sstab_advanced_controls']
			), 1, '', -1);

	if ($db->num_rows($result))
	{
		$nuevotitulo = '';
		while( $fuenteitem = $db->fetch_array( $result ) ) 
		{
			if ($nuevotitulo!=$fuenteitem['padre'])
			{
				print_table_header($fuenteitem['padre'].construct_link_code($vbphrase['evbs_sstab_advanced_edit'], "evbs_sstabs.php?$session[sessionurl]do=edit&amp;tabid=$fuenteitem[tabid]"),6,false,'','left');
				$nuevotitulo=$fuenteitem['padre'];
			}
			$cell = array();
			$cell[] = $fuenteitem['title'] . '<input name="idcambiomass['.$fuenteitem['childid'].']" type="hidden" value="'.$fuenteitem['childid'].'">';
			$cell[] = $fuenteitem['url'] . "<dfn>target = ".$fuenteitem['target']."</dfn>";
			$cell[] = "<div>".construct_phrase($vbphrase['evbs_sstab_advanced_ug_allowed'],$fuenteitem['pusergroup'])."</div> <div>".construct_phrase($vbphrase['evbs_sstab_advanced_ug_not_allowed'],$fuenteitem['nusergroup'])."</div>";
			if ($fuenteitem['enable']==1)
			{
				$check = 'checked="checked"';
			}
			else
			{
				$check = "";
			}
			
			$cell[] = '<input name="activo['.$fuenteitem['childid'].']" '.$check.' type="checkbox">';
			$cell[] = "
						<input style=\"text-align: center\" type=\"text\" class=\"bginput\" name=\"orden[" . $fuenteitem['childid'] . "]\" tabindex=\"1\" value=\"$fuenteitem[displayorder]\" size=\"2\" title=\"".$vbphrase['evbs_sstab_advanced_orden_display']."\" class=\"smallfont\" />
			";
			$cell[] = "" .
					construct_link_code($vbphrase['evbs_sstab_advanced_edit'], "evbs_sstabs.php?$session[sessionurl]do=edititemmenu&amp;childid=$fuenteitem[childid]$plusurl") .
					construct_link_code($vbphrase['evbs_sstab_advanced_delete'], "evbs_sstabs.php?$session[sessionurl]do=removeitemmenu&childid=$fuenteitem[childid]$plusurl");
			print_cells_row($cell, 0, '', 1);
		}
	}
	else
	{
		print_description_row('<center>'.$vbphrase['evbs_sstab_advanced_no_item_menu'].'</center>',0,6);
	}
		print_table_footer(6, "\n\t <input type=\"button\" value=\"".$vbphrase['evbs_sstab_advanced_add_new_item_menu']."\" onclick=\"location.href='evbs_sstabs.php?$session[sessionurl]do=newitemmenu$plusurl'\" />  <input type=\"submit\" class=\"button\" name=\"doorder\" value=\"".$vbphrase['evbs_sstab_advanced_save_changes']."\" tabindex=\"1\" />\n\t",'',false);
}


if ( $_REQUEST['do'] == 'update' ) 
{
$vbulletin->input->clean_array_gpc('p', array(
	'tabid'  => TYPE_INT, 
	'title' => TYPE_STR, 
	'url' => TYPE_STR, 
	'tabmode' =>TYPE_STR,
	'script' => TYPE_STR, 
	'target' => TYPE_STR,
	'pusergroup' => TYPE_ARRAY,
	'nusergroup' => TYPE_ARRAY,
	'enable' => TYPE_INT,
	'position' => TYPE_STR,
	'displayorder' => TYPE_INT,
	'istabmenu' => TYPE_INT,
	));
	
	$tabid = intval($vbulletin->GPC['tabid']);
	$title = str_replace ("'", "''", $vbulletin->GPC['title']);
	
	$script = str_replace ("'", "''", $vbulletin->GPC['script']);
	$target = $vbulletin->GPC['target'];
	$tabmode = $vbulletin->GPC['tabmode'];
	$pusergroup = implode(",", $vbulletin->GPC['pusergroup']);
	$nusergroup = implode(",", $vbulletin->GPC['nusergroup']);

	$enable = $vbulletin->GPC['enable'];
	$position = $vbulletin->GPC['position'];
	$displayorder = $vbulletin->GPC['displayorder'];
	
	if (empty($vbulletin->GPC['istabmenu']))
	{
		if ($vbulletin->GPC['url']=='-[MENU ITEM]-')
		{
			$url='';
		}
		else
		{
		$url = str_replace ("'", "''", $vbulletin->GPC['url']);
		}
	}
	else
	{
		$url = '-[MENU ITEM]-';
	}
	
	
	if (empty($vbulletin->GPC['tabid']))
	{
		$db->query_write("INSERT INTO " . TABLE_PREFIX . "evbs_sstabs(title,url,tabmode,script,target,pusergroup,nusergroup,`enable`,position,displayorder)
				VALUES (
				'".$title."',
				'".$url."',
				'".$tabmode."',
				'".$script."',
				'".$target."',
				'".$pusergroup."',
				'".$nusergroup."',
				'".$enable."',
				'".$position."',
				'".$displayorder."'
				)");
	}
	else
	{
		$db->query_write("UPDATE " . TABLE_PREFIX . "evbs_sstabs SET 
		title = '".$title."',
		url = '".$url."',
		tabmode = '".$tabmode."',
		script = '".$script."',
		target = '".$target."',
		pusergroup = '".$pusergroup."',
		nusergroup = '".$nusergroup."',
		`enable` = '".$enable."',
		position = '".$position."',
		displayorder = '".$displayorder."' 
		WHERE tabid =$tabid");
	}
$_REQUEST['do'] = '';
}		

if ( empty($_REQUEST['do']) ) {
	
	
	print_form_header('evbs_sstabs', 'modifyorder', 0, 1, 'modifytab');
	print_table_header($vbphrase['evbs_sstab_advanced_tab_title'],9);
	$sql = "SELECT t.*,c.title as ctitle, c.url as curl, c.childid
			FROM " . TABLE_PREFIX . "evbs_sstabs as t
			LEFT JOIN " . TABLE_PREFIX . "evbs_sstabs_childs as c on t.tabid=c.tabid
			ORDER BY t.displayorder,c.displayorder
			";
	$result = $db->query_read($sql);
	
	print_cells_row(array('Tabid',
			$vbphrase['evbs_sstab_advanced_tab'],
			$vbphrase['evbs_sstab_advanced_url'],
			'Tab Mode',
			$vbphrase['evbs_sstab_advanced_ug_title'],
			$vbphrase['evbs_sstab_advanced_posicion'],
			$vbphrase['evbs_sstab_advanced_active'],
			$vbphrase['evbs_sstab_advanced_orden_display'],
			$vbphrase['evbs_sstab_advanced_controls']
			), 1, '', -1);

	if ($db->num_rows($result))
	{
	$acumulado = array();

	while( $fuenteitem = $db->fetch_array( $result ) ) 
		{
			$cell = array();
			$cell[0] = $fuenteitem['tabid'];
			
			if ($fuenteitem['url']=='-[MENU ITEM]-')
			{
				$cell[1] = $fuenteitem['title'] . '<input name="idcambiomass['.$fuenteitem['tabid'].']" type="hidden" value="'.$fuenteitem['tabid'].'"> <dfn><strong>MENU TAB</strong></dfn>';
				if ($fuenteitem['childid'])
				{
				$cell[2] = "<div><a href='evbs_sstabs.php?$session[sessionurl]do=edititemmenu&amp;childid=$fuenteitem[childid]'>$fuenteitem[ctitle]</a></div>";
				}
				else
				{
				$cell[2] = "<dfn>".$vbphrase['evbs_sstab_advanced_hide_no_menu']."<br />";
				$cell[2] .= construct_link_code($vbphrase['evbs_sstab_advanced_add_new_item_menu'], "evbs_sstabs.php?do=newitemmenu&tabid=$fuenteitem[tabid]") . "</dfn>";
				}
			}
			else
			{
				$cell[1] = $fuenteitem['title'] . '<input name="idcambiomass['.$fuenteitem['tabid'].']" type="hidden" value="'.$fuenteitem['tabid'].'">';
				$cell[2] = $fuenteitem['url'] . "<dfn>target = ".$fuenteitem['target']."</dfn>";
			}
			
			if ($fuenteitem['tabmode']=='THIS_SCRIPT')
			{
				$masinfo='<br /> ' .$fuenteitem['script'];
			}
			$cell[3] = "<strong>".$fuenteitem['tabmode'] . "</strong>". $masinfo;
			$cell[4] = "<div>".construct_phrase($vbphrase['evbs_sstab_advanced_ug_allowed'],$fuenteitem['pusergroup'])."</div> <div>".construct_phrase($vbphrase['evbs_sstab_advanced_ug_not_allowed'],$fuenteitem['nusergroup'])."</div>";
			$cell[5] = $fuenteitem['position'];
			if ($fuenteitem['enable']==1)
			{
				$check = 'checked="checked"';
			}
			else
			{
				$check = "";
			}
			
			$cell[6] = '<input name="activo['.$fuenteitem['tabid'].']" '.$check.' type="checkbox">';
			$cell[7] = "
						<input style=\"text-align: center\" type=\"text\" class=\"bginput\" name=\"orden[" . $fuenteitem['tabid'] . "]\" tabindex=\"1\" value=\"$fuenteitem[displayorder]\" size=\"2\" title=\"".$vbphrase['evbs_sstab_advanced_orden_display']."\" class=\"smallfont\" />
			";
			$cell[8] = "" .
					construct_link_code($vbphrase['evbs_sstab_advanced_edit'], "evbs_sstabs.php?$session[sessionurl]do=edit&amp;tabid=$fuenteitem[tabid]") .
					construct_link_code($vbphrase['evbs_sstab_advanced_delete'], "evbs_sstabs.php?$session[sessionurl]do=remove&tabid=$fuenteitem[tabid]");
			

				if($cell[0]==$acumulado[count($acumulado)-1][0])
				{
					if ($cell[2]=='-[MENU ITEM]-')
					{
					$acumulado[count($acumulado)-1][2].=$cell[2];
					}
					
				}
				else
				{
					$acumulado[]=$cell;
				}
			
		}

		foreach ($acumulado as $key=>$fila)
		{
			print_cells_row($fila, 0, '', 1);
		}
	}
	else
	{
	print_description_row('<center>'.$vbphrase['evbs_sstab_advanced_no_tabs'].'</center>',0,9);
	}
		print_table_footer(9, "\n\t <input type=\"button\" value=\"".$vbphrase['evbs_sstab_advanced_add_new_tab']."\" onclick=\"location.href='evbs_sstabs.php?$session[sessionurl]do=newtab'\" />  <input type=\"submit\" class=\"button\" name=\"doorder\" value=\"".$vbphrase['evbs_sstab_advanced_save_changes']."\" tabindex=\"1\" />\n\t",'',false);
		

}

if ( $_REQUEST['do'] == 'edit' or $_REQUEST['do'] == 'newtab') 
{
	if ($_REQUEST['do'] == 'edit')
	{
	$vbulletin->input->clean_array_gpc('r', array(
		'tabid' => TYPE_INT
	));

	if (empty($vbulletin->GPC['tabid']))
	{
		print_stop_message('evbs_sstab_advanced_tab_error');
	}
		
	$tab = $db->query_first("SELECT * FROM " . TABLE_PREFIX . "evbs_sstabs WHERE  tabid = ". $vbulletin->GPC['tabid'] ."");
	$head_table = "$tab[title] (id:$tab[tabid])";
		if ($tab['url']=='-[MENU ITEM]-')
		{
			$istabmenu= 1;
		}
		else
		{
			$istabmenu= 0;
		}
	}
	else
	{
		$head_table = $vbphrase['evbs_sstab_advanced_add_new_tab'];
		$tab['enable'] = 1;
		$istabmenu= 0;
	}
	print_form_header('evbs_sstabs', 'update');
	construct_hidden_code('tabid', $vbulletin->GPC['tabid']);
	print_table_header($head_table, 2);
	print_input_row($vbphrase['evbs_sstab_advanced_tab_label'], 'title', $tab['title'],true,20);
	print_yes_no_row($vbphrase['evbs_sstab_advanced_is_menu_tab'], 'istabmenu', $istabmenu);
	print_input_row($vbphrase['evbs_sstab_advanced_url'], 'url', $tab['url']);
	print_select_row($vbphrase['evbs_sstab_advanced_tab_mode'], 'tabmode',array (THIS_SCRIPT=>"THIS_SCRIPT",URL=>"URL",URL_PARAMETER=>"URL_PARAMETER"), $tab['tabmode']);
	print_input_row($vbphrase['evbs_sstab_advanced_this_script'], 'script', $tab['script']);
	print_select_row("Target", "target", array (_self=>"_self",_blank=>"_blank",_parent=>"_parent",_top=>"_top"),$tab['target']);
	
	print_membergroup_row($vbphrase['evbs_sstab_advanced_ug_allowed_full'], "pusergroup",$vbulletin->options['evbs_sstab_advanced_num_col'],array('usergroupid' => 0, 'membergroupids' => $tab['pusergroup']));
	print_membergroup_row($vbphrase['evbs_sstab_advanced_ug_not_allowed_full'], "nusergroup",$vbulletin->options['evbs_sstab_advanced_num_col'],array('usergroupid' => 0, 'membergroupids' => $tab['nusergroup']));
	print_yes_no_row($vbphrase['evbs_sstab_advanced_active_tab'], 'enable', $tab['enable']);
	print_select_row($vbphrase['evbs_sstab_advanced_posicion'], "position", array (right=>$vbphrase['evbs_sstab_advanced_right'],left=>$vbphrase['evbs_sstab_advanced_left'],center=>$vbphrase['evbs_sstab_advanced_center']),$tab['position']);
	print_input_row($vbphrase['evbs_sstab_advanced_orden_display'], 'displayorder', $tab['displayorder']);
	print_submit_row($vbphrase['evbs_sstab_advanced_save']);
}

if ( $_REQUEST['do'] == 'edititemmenu' or $_REQUEST['do'] == 'newitemmenu') 
{
	$vbulletin->input->clean_array_gpc('r', array(
		'childid' => TYPE_INT,
		'tipo'  => TYPE_STR,
		'tabid' => TYPE_INT,
	));
	
	if ($_REQUEST['do'] == 'edititemmenu')
	{
		if (empty($vbulletin->GPC['childid']))
		{
			print_stop_message('evbs_sstab_advanced_tab_error');
		}
			
		$tabchild = $db->query_first("SELECT *
									 FROM " . TABLE_PREFIX . "evbs_sstabs_childs 
									 WHERE  childid = ". $vbulletin->GPC['childid'] ."");
		$head_table = "$tabchild[title] (id:$tabchild[childid])";
	}
	else
	{
		$head_table = $vbphrase['evbs_sstab_advanced_add_new_item_menu'];
		$tabchild['enable'] = 1;
		$tabchild['tabid'] = $vbulletin->GPC['tabid'];
	}
	
	if ($vbulletin->GPC['tipo']=='submenu')
	{
		$result = $db->query_read("SELECT tabid,title FROM " . TABLE_PREFIX . "evbs_sstabs WHERE  url != '-[MENU ITEM]-'");
	}
	else
	{
		$result = $db->query_read("SELECT tabid,title FROM " . TABLE_PREFIX . "evbs_sstabs WHERE  url = '-[MENU ITEM]-'");
	}
	
	/*Inicio Padre*/
	$tabpadre[0]=$vbphrase['evbs_sstab_advanced_no_asigned'];
	if ($db->num_rows($result))
	{
		while( $fuenteitem = $db->fetch_array( $result )) 
		{
			$tabpadre[$fuenteitem['tabid']]=$fuenteitem['title'];
		}
	}
	
	/*Fin Padre*/
	print_form_header('evbs_sstabs', 'updateitemmenu');
	construct_hidden_code('childid', $vbulletin->GPC['childid']);
	construct_hidden_code('tipo', $vbulletin->GPC['tipo']);
	print_table_header($head_table, 2);
	print_select_row($vbphrase['evbs_sstab_advanced_tab_parent'], 'tabid',$tabpadre,$tabchild['tabid']); //corregir
	print_input_row($vbphrase['evbs_sstab_advanced_menu_name'], 'title', $tabchild['title'],true);
	print_input_row($vbphrase['evbs_sstab_advanced_url'], 'url', $tabchild['url']);
	print_select_row("Target", "target", array (_self=>"_self",_blank=>"_blank",_parent=>"_parent",_top=>"_top"),$tab['target']);
	print_membergroup_row($vbphrase['evbs_sstab_advanced_ug_allowed_full'], "pusergroup",$vbulletin->options['evbs_sstab_advanced_num_col'],array('usergroupid' => 0, 'membergroupids' => $tabchild['pusergroup']));
	print_membergroup_row($vbphrase['evbs_sstab_advanced_ug_not_allowed_full'], "nusergroup",$vbulletin->options['evbs_sstab_advanced_num_col'],array('usergroupid' => 0, 'membergroupids' => $tabchild['nusergroup']));	
	print_yes_no_row($vbphrase['evbs_sstab_advanced_im_active'], 'enable', $tabchild['enable']);
	print_input_row($vbphrase['evbs_sstab_advanced_orden_display'], 'displayorder', $tabchild['displayorder']);
	print_submit_row($vbphrase['evbs_sstab_advanced_save']);
}
print_cp_footer();
?>