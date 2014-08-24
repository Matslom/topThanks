<?php

/*
 * topThanks plugin for ThankYou/LikeSystem
 * required installed ThankYou/LikeSystem plugin
 * author Matslom from forum.mybboard.pl
 * github https://github.com/Matslom/topThanks.git
 * license GNU GENERAL PUBLIC LICENSE
*/

if(!defined('IN_MYBB'))
	die('This file cannot be accessed directly.');

$plugins->add_hook("index_end", "topthanks_show");

function topThanks_info()
{

	return array(
		"name"		=> "Top thanks",
		"description"		=> "Plugin do panelu bocznego wyświetlający najwięcej podziękowań z pluginu Thank You/Like System.",
		"website"		=> "http://www.mybboard.pl",
		"author"		=> "Matslom",
		"authorsite"		=> "https://github.com/Matslom",
		"version"		=> "1.1",
		"guid" 			=> "*",
		"compatibility"	=> "16*"
		);
}


function topThanks_install()
{
	global $mybb, $db;

	$settinggroups = array(
		"name" 			=> "topThanks", 
		"title" 		=> $db->escape_string('Najwięcej podziękowań'),
		"description" 	=> $db->escape_string('Ustawienia pluginu Top thanks'),
		"disporder" 	=> 100, 
		"isdefault" 	=> 0
	);
	$gid = $db->insert_query("settinggroups", $settinggroups);
	$disporder = 0;

	$setting = array(
		"sid"			=> NULL,
		"name"			=> "topThanks_limit",
		"title"			=> $db->escape_string('Ilość użytkowników'),
		"description"	=> $db->escape_string('Ilość wyświetlanych top użytkowników'),
		"optionscode"	=> "text", 
		"value"			=> "5",
		"disporder"		=> $disporder++,
		"gid"			=> $gid
	);
	$db->insert_query("settings", $setting);

	$setting = array(
		"sid"			=> NULL,
		"name"			=> "topThanks_defavatar",
		"title"			=> $db->escape_string('Domyślny awatar'),
		"description"	=> $db->escape_string('Ścieżka do awatara który wyświetli się w wypadku jego braku u użytkownika'),
		"optionscode"	=> "text", 
		"value"			=> "./images/default_avatar.gif",
		"disporder"		=> $disporder++,
		"gid"			=> $gid
	);
	$db->insert_query("settings", $setting);
	
	rebuild_settings();

	$template = array(
		"tid" 			=> "NULL",
		"title" 		=> "topThanks",
		"template"		=> $db->escape_string('<table border="0" cellspacing="{$theme[\'borderwidth\']}" cellpadding="{$theme[\'tablespace\']}" class="tborder" style="margin-top:5px;" id="topthanks">
<thead>
<tr>
<td class="thead" colspan="2">
<div><strong>Najwięcej podziękowań</strong></div>
</td>
</tr>
</thead>
{$topThanks_row}
</table>
<br />'),
		"sid" 			=> "-1", 
		);
	$db->insert_query("templates", $template);
		
	$template = array(
		"tid" 			=> "NULL",
		"title" 		=> "topThanks_row",
		"template"		=> $db->escape_string('<tr> 
<td class="trow1 smalltext">
<a href="{$topThanks[\'profileLink\']}"><img src="{$data[\'avatar\']}" alt="avatar" class="favimg avatar"/></a>
<div class="box"> {$topThanks[\'username\']} 
<div class="thanks"> Otrzymanych podziękowań: <a href="{$mybb->settings[\'bburl\']}/tylsearch.php?action=usertylforthreads&uid={$data[\'uid\']}">{$data[\'tyl_unumrcvtyls\']}</a></div></div>
</td>
</tr>'),
		"sid" 			=> "-1", 
		);
	$db->insert_query("templates", $template);

}

function topThanks_is_installed()
{
	global $mybb, $db;

	$q = $db->simple_select('settinggroups', '*', 'name=\'topThanks\'');
	$group = $db->fetch_array($q);
	if($group === null || empty($group))
	return false;
	return true;
}


function topThanks_uninstall()
{
	global $mybb, $db;

	$db->delete_query('settings', 'name LIKE \'%topThanks_%\'');
	$db->delete_query("settinggroups", "name = 'topThanks'");
	$db->delete_query('templates', 'title LIKE (\'%topThanks%\')');
}


function topThanks_activate(){}

function topThanks_deactivate(){}

function topThanks_show()
{
	global $db, $mybb, $page, $topThanks, $theme, $permissioncache, $templates, $theme;

	$limit = $mybb->settings['topThanks_limit'];
	$query = $db->query("SELECT u.tyl_unumrcvtyls, u.username, u.usergroup, u.displaygroup, u.avatar, u.uid FROM ".TABLE_PREFIX."users as u ORDER BY u.tyl_unumrcvtyls DESC LIMIT ".$limit);

	while($data = $db->fetch_array($query)){
		if($data['avatar'] == null){
			$data['avatar'] = $mybb->settings['topThanks_defavatar']; 
		}
		
		$topThanks['profileLink'] = $mybb->settings['bburl']."/".get_profile_link($data['uid']);
		$usernameFormatted = format_name($data['username'], $data['usergroup'], $data['displaygroup']);
    	$topThanks['username'] = '<a href="member.php?action=profile&uid='.intval($data['uid']).'"> '.$usernameFormatted.'</a>';

		eval('$topThanks_row .= "'.$templates->get('topThanks_row').'";');

	}

	eval('$topThanks = "'.$templates->get('topThanks').'";');



}


?>
