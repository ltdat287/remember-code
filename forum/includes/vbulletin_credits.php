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

if (!isset($GLOBALS['vbulletin']->db))
{
	exit;
}

// display the credits table for use in admin/mod control panels

print_form_header('index', 'home');
print_table_header($vbphrase['vbulletin_developers_and_contributors']);
print_column_style_code(array('white-space: nowrap', ''));
print_label_row('<b>' . $vbphrase['software_developed_by'] . '</b>', '
	vBulletin Solutions, Inc.,
	Internet Brands, Inc.
', '', 'top', NULL, false);
print_label_row('<b>' . $vbphrase['business_development'] . '</b>', '
	James Limm,
	Ashley Busby,
	Ray Morgan,
	Joe Rosenblum,
	Jen Rundell
', '', 'top', NULL, false);
print_label_row('<b>' . $vbphrase['engineering'] . '</b>', '
	Kevin Sours,
	Freddie Bingham,
	Darren Gordon,
	Edwin Brown,
	Andrew Elkins,
	Michael Henretty,
	Xiaoyu Huang,
	Mert Gokceimam,
	Andy Huang,
	Chris Holland,
	Joan Gauna,
	David Grove,
	David Bonilla,
	Pritesh Shah,
	Prince Shah,
	Tully Rankin,
	Kier Darby,
	Scott MacVicar,
	Mike Sullivan,
	Jerry Hutchings
', '', 'top', NULL, false);
print_label_row('<b>' . $vbphrase['product'] . '</b>', '
	Don Kuramura
', '', 'top', NULL, false);
print_label_row('<b>' . $vbphrase['graphics_development'] . '</b>', '
	Sophie Xie,
	Kier Darby
', '', 'top', NULL, false);
print_label_row('<b>' . $vbphrase['qa'] . '</b>', '
	Allen Lin,
	Meghan Sensenbach,
	Ryan Burch
', '', 'top', NULL, false);
print_label_row('<b>' . $vbphrase['support'] . '</b>', '
	Steve Machol,
	Wayne Luke,
	Colin Frei,
	Carrie Anderson,
	George Liu,
	Jake Bunce,
	Zachery Woods,
	Marco van Herwaarden,
	Marlena Machol
', '', 'top', NULL, false);
print_label_row('<b>' . $vbphrase['special_thanks_to'] . '</b>', '
	Adrian Sacchi, Andreas Kirbach, Aston Jay, Bob Pankala, Brian Swearingen, Brian Gunter, Chen Avinadav, Chevy Revata, Christopher Riley, 
	Daniel Clements, David Webb, David Yancy, Dominic Schlatter, Don T. Romrell, Doron Rosenberg, Elmer Hernandez, Fernando Munoz,
	Giovanni Martinez, Hanafi Jamil, Hanson Wong, Hartmut Voss, Jacquii Cooke, Jan Allan Zischke, Jeremy Dentel, 
	Joe Velez, Joel Young, John Jakubowski, John Simpson, Jonathan Javier Coletta, Joseph DeTomaso, Kevin Schumacher, Kevin Wilkinson, 
	Kira Lerner, Kolby Bothe, Lisa Swift, Lynne D Sands, Mark James, Martin Meredith, Matthew Gordon, Michael Biddle, Michael Kellogg, Michael \'Mystics\' K&ouml;nig,
	Michael Pierce, Milad Kawas Cale, Nathan Wingate, Nawar Al-Mubaraki, Ole Vik, Overgrow, Paul Marsden, 
	Peggy Lynn Gurney, Robert Beavan White, Ryan Royal, Sal Colascione III, Scott Molinari, Scott William, 
	Shawn Vowell, Stephan \'pogo\' Pogodalla, Sven Keller, Tom Murphy, Tony Phoenix, Torstein H&oslash;nsi, Trevor Hannant, Vinayak Gupta
', '', 'top', NULL, false);

print_label_row('<b>' . $vbphrase['other_contributions_from'] . '</b>', '
	Torstein H&oslash;nsi,
	Mark James
', '', 'top', NULL, false);
print_label_row('<b>' . $vbphrase['copyright_enforcement_by'] . '</b>', '
	Pirate Reports
', '', 'top', NULL, false);
print_table_footer();

/*======================================================================*\
|| ####################################################################
|| # CVS: $RCSfile$ - $Revision: 35229 $
|| ####################################################################
\*======================================================================*/
?>
