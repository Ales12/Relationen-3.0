<?php

// Disallow direct access to this file for security reasons
if (!defined("IN_MYBB")) {
    die("Direct initialization of this file is not allowed.");
}

// Profil
$plugins->add_hook("member_profile_end", "relations_profile");

// globale Alert anzeige
$plugins->add_hook('global_intermediate', 'relations_global_alert');

// Alerts
if (class_exists('MybbStuff_MyAlerts_AlertTypeManager')) {
    $plugins->add_hook("global_start", "relations_alerts");
}

// user cp
$plugins->add_hook('usercp_menu', 'relations_usercp_nav', 30);
$plugins->add_hook('usercp_start', 'relations_usercp');

//wer ist wo
$plugins->add_hook('fetch_wol_activity_end', 'relations_user_activity');
$plugins->add_hook('build_friendly_wol_location_end', 'relations_location_activity');


function relations_info()
{
    return array(
        "name" => "Relationsverwaltungssytem",
        "description" => "Hier können Charaktere ihre relations in ihren Profilen verwalten",
        "website" => "",
        "author" => "Ales",
        "authorsite" => "https://github.com/Ales12",
        "version" => "2.0",
        "guid" => "",
        "codename" => "",
        "compatibility" => "*"
    );


}

function relations_install()
{
    global $db, $mybb, $templates;

    global $db;
    if ($db->engine == 'mysql' || $db->engine == 'mysqli') {
        $db->query("CREATE TABLE `" . TABLE_PREFIX . "relations` (
          `rid` int(10) NOT NULL auto_increment,
          `r_uid` int(10) NOT NULL,
          `r_reuid` int(10) NOT NULL,
          `r_npcname` varchar(500) NOT NULL,
            `r_cat` varchar(500) NOT NULL,
            `r_kind` varchar(500) NOT NULL,
            `r_age` int(10) NOT NULL,
            `r_job` varchar(500) NOT NULL,
            `r_text` varchar(500) NOT NULL,
      `r_ok` int(10) NOT NULL DEFAULT 0,
          PRIMARY KEY (`rid`)
        ) ENGINE=MyISAM" . $db->build_create_table_collation());

    }
    // Einstellungen
    global $db, $mybb;

    $setting_group = array(
        'name' => 'relations',
        'title' => 'Einstellungen für das Relationssystem',
        'description' => 'Hier kannst du alle wichtigen Einstellungen für die relations einfügen.',
        'disporder' => 5, // The order your setting group will display
        'isdefault' => 0
    );

    $gid = $db->insert_query("settinggroups", $setting_group);

    $setting_array = array(
        // A text setting
        'relations_cat' => array(
            'title' => 'Relationskategorien',
            'description' => 'Welche Kategorien sollen auswählbar sein?:',
            'optionscode' => 'text',
            'value' => 'Familie, Freunde, Bekannte, Feinde, Liebe, Vergangenheit', // Default
            'disporder' => 1
        ),
        // A select box
        'relations_default' => array(
            'title' => 'Defaultbild',
            'description' => 'Gebe hier den Namen des Defaultbildes an, bei NPC oder fehlenden Avatar genutzt werden soll. Es wird auch Gästen angezeigt:',
            'optionscode' => 'text',
            'value' => 'default.png',
            'disporder' => 2
        ),
        // A yes/no boolean box
        'relations_avatar' => array(
            'title' => 'Profilfeld anstatt Avatar?',
            'description' => 'Soll anstatt dem Avatar ein Profilfeld ausgelesen werden?',
            'optionscode' => 'yesno',
            'value' => 0,
            'disporder' => 3
        ),
        // A select box
        'relations_avatar_fid' => array(
            'title' => 'Profilfeld für Avataralternative',
            'description' => 'Gebe hier die fid-ID an, welches anstatt das Avatar ausgelesen werden soll:',
            'optionscode' => 'text',
            'value' => 'fid2',
            'disporder' => 4
        ),
        // A yes/no boolean box
        'relations_age' => array(
            'title' => 'Steht das Alter in einem Profilfeld?',
            'description' => 'Wird das Alter in einem eigenen Profilfeld angegeben? Wenn nein, wird auf das mybb-Standardgeburtstagsfeld zurückgegriffen und der Geburtstag berechnet.',
            'optionscode' => 'yesno',
            'value' => 0,
            'disporder' => 5
        ),
        // A select box
        'relations_age_fid' => array(
            'title' => 'Profilfeld für Alter',
            'description' => 'Gebe hier die fid-ID an, in welchem das Alter eingetragen wird:',
            'optionscode' => 'text',
            'value' => 'fid2',
            'disporder' => 6
        ),
        'relations_date' => array(
            'title' => 'Inplayzeitraum',
            'description' => 'Gebe hier das Datum des <b>letzten</b> Inplaytags ein. <b>Actung!</b> Muss bei jedem Zeitsprung angepasst werden.',
            'optionscode' => 'text',
            'value' => '00.00.0000',
            'disporder' => 7
        ),

        'relations_job' => array(
            'title' => 'Wird der Joblistplugin genutzt?',
            'description' => 'Wenn du den Plugin installiert hast und daraus den Job ausgelesen haben möchtest.',
            'optionscode' => 'yesno',
            'value' => 0,
            'disporder' => 8
        ),

    );

    foreach ($setting_array as $name => $setting) {
        $setting['name'] = $name;
        $setting['gid'] = $gid;

        $db->insert_query('settings', $setting);
    }

    // Don't forget this!
    rebuild_settings();


    $insert_array = array(
        'title' => 'relations_addtoo',
        'template' => $db->escape_string('<a onclick="$(\'#rela_add\').modal({ fadeDuration: 250, keepelement: true, zIndex: (typeof modal_zindex !== \'undefined\' ? modal_zindex : 9999) }); return false;" style="cursor: pointer;">{$lang->relations_popup_add}</a>	<div class="modal" id="rela_add" style="display: none;"><form action="usercp.php?action=relations" method="post" id="submitrelation">			
	<input type="hidden" class="textbox" name="r_uid" id="r_uid" size="10" maxlength="1155" value="{$row[\'r_reuid\']}"> 			<input type="hidden" class="textbox" name="r_reuid" id="r_reuid" size="10" maxlength="1155" value="{$row[\'r_uid\']}"> 
<div class="relations" style="width: 100%;">
  <div class="relationscat">		<div class="tcat"><strong>{$lang->relations_cat}</strong></div>
			<select name="r_cat">
			<option>{$lang->relations_kind}</option>
			{$cat_options}
		</select>
	</div>
  <div class="relationskind">	<div class="tcat"><strong>{$lang->relations_kind}</strong></div>
				<input type="text" class="textbox" name="r_kind" id="r_kind" size="40" maxlength="1155" placeholder="Beziehungsstatus">

	</div>
  <div class="relationstext">		<textarea class="textarea" name="r_text" id="r_text" rows="3" cols="15" style="width: 95%" placeholder="Beschreibe hier kurz die Beziehung deines Charakters zu {$memprofile[\'username\']}."></textarea>
	</div>
  <div class="relationssubmit">		<input type="submit" name="submitrelation" id="submitrelation" value="{$lang->relations_submit}" class="buttom">
		</div>
</div></div>'),
        'sid' => '-1',
        'version' => '',
        'dateline' => TIME_NOW
    );
    $db->insert_query("templates", $insert_array);

    $insert_array = array(
        'title' => 'relations_alert',
        'template' => $db->escape_string('<div class="red_alert">
	<a href="usercp.php?action=relations">{$lang->relations_alert}</a>
</div>'),
        'sid' => '-1',
        'version' => '',
        'dateline' => TIME_NOW
    );
    $db->insert_query("templates", $insert_array);

    $insert_array = array(
        'title' => 'relations_alert_other',
        'template' => $db->escape_string('<div class="red_alert">
<a id="switch_{$alert2[\'uid\']}" href="#switch" class="switchlink">Aktuell hat {$user} {$count} offene {$request}</a>
</div>'),
        'sid' => '-1',
        'version' => '',
        'dateline' => TIME_NOW
    );
    $db->insert_query("templates", $insert_array);

    $insert_array = array(
        'title' => 'relations_edit',
        'template' => $db->escape_string('<a onclick="$(\'#rela_edit\').modal({ fadeDuration: 250, keepelement: true, zIndex: (typeof modal_zindex !== \'undefined\' ? modal_zindex : 9999) }); return false;" style="cursor: pointer;">{$lang->relations_popup_edit}</a>	<div class="modal" id="rela_edit" style="display: none;"><form action="usercp.php?action=relations" method="post" id="editrelation">			
	<input type="hidden" class="textbox" name="r_uid" id="r_uid" size="10" maxlength="1155" value="{$row[\'r_uid\']}"> 			<input type="hidden" class="textbox" name="r_reuid" id="r_reuid" size="10" maxlength="1155" value="{$row[\'r_reuid\']}"> <input type="text" class="textbox" name="rid" id="rid" size="10" maxlength="1155" value="{$row[\'rid\']}"> 
<div class="relations" style="width: 100%;">
  <div class="relationscat">		<div class="tcat"><strong>{$lang->relations_cat}</strong></div>
			<select name="r_cat">
			<option>{$lang->relations_kind}</option>
			{$cat_options}
		</select>
	</div>
  <div class="relationskind">	<div class="tcat"><strong>{$lang->relations_kind}</strong></div>
				<input type="text" class="textbox" name="r_kind" id="r_kind" size="40" maxlength="1155" value="{$row[\'r_kind\']}">

	</div>
  <div class="relationstext">		<textarea class="textarea" name="r_text" id="r_text" rows="3" cols="15" style="width: 95%" >{$row[\'r_text\']}</textarea>
	</div>
  <div class="relationssubmit">		<input type="submit" name="editrelation" id="editrelation" value="{$lang->relations_submit}" class="buttom">
		</div>
</div>
	</form></div>'),
        'sid' => '-1',
        'version' => '',
        'dateline' => TIME_NOW
    );
    $db->insert_query("templates", $insert_array);

    $insert_array = array(
        'title' => 'relations_form',
        'template' => $db->escape_string('<form action="member.php?action=profile&uid={$memprofile[\'uid\']}" method="post" id="submitrelation">			<input type="hidden" class="textbox" name="r_uid" id="r_uid" size="10" maxlength="1155" value="{$mybb->user[\'uid\']}"> 			<input type="hidden" class="textbox" name="r_reuid" id="r_reuid" size="10" maxlength="1155" value="{$memprofile[\'uid\']}"> 
<div class="relations">
  <div class="relationscat">		<div class="tcat"><strong>{$lang->relations_cat}</strong></div>
			<select name="r_cat">
			<option>{$lang->relations_kind}</option>
			{$cat_options}
		</select>
	</div>
  <div class="relationskind">	<div class="tcat"><strong>{$lang->relations_kind}</strong></div>
				<input type="text" class="textbox" name="r_kind" id="r_kind" size="40" maxlength="1155" placeholder="Beziehungsstatus">

	</div>
  <div class="relationstext">		<textarea class="textarea" name="r_text" id="r_text" rows="3" cols="15" style="width: 95%" placeholder="Beschreibe hier kurz die Beziehung deines Charakters zu {$memprofile[\'username\']}."></textarea>
	</div>
  <div class="relationssubmit">		<input type="submit" name="submitrelation" id="submitrelation" value="{$lang->relations_submit}" class="buttom">
		</div>
</div>
</form>'),
        'sid' => '-1',
        'version' => '',
        'dateline' => TIME_NOW
    );
    $db->insert_query("templates", $insert_array);

    $insert_array = array(
        'title' => 'relations_npc_form',
        'template' => $db->escape_string('<form action="member.php?action=profile&uid={$memprofile[\'uid\']}" method="post" id="submitrelation">
			<input type="hidden" class="textbox" name="r_uid" id="r_uid" size="10" maxlength="1155" value="{$mybb->user[\'uid\']}"> 			<input type="hidden" class="textbox" name="r_reuid" id="r_reuid" size="10" maxlength="1155" value="0"> 
<div class="npc_relations">
  <div class="charaname">
<div class="tcat"><strong>{$lang->relations_name}</strong></div>
	  <input type="text" class="textbox" name="r_npcname" id="r_npcname" size="40" maxlength="1155" placeholder="Vorname Nachname"></div>
  <div class="relationstype">
			<div class="tcat"><strong>{$lang->relations_cat}</strong></div>
	  	<select name="r_cat">
			<option>{$lang->relations_kind}</option>
			{$cat_options}
		</select>			
		<div class="tcat"><strong>{$lang->relations_kind}</strong></div>
			<input type="text" class="textbox" name="r_kind" id="r_kind" size="40" maxlength="1155" placeholder="Vater, Mutter, Freunde?">
	
	</div>
  <div class="shortfacts">
				<div class="tcat"><strong>{$lang->relations_age}</strong></div>
			<input type="number" class="textbox" name="r_age" id="r_age" size="40" maxlength="1155" placeholder="00">			<br />
		<div class="tcat"><strong>{$lang->relations_job}</strong></div>
			<input type="text" class="textbox" name="r_job" id="r_job" size="40" maxlength="1155" placeholder="Beruf">
	</div>
  <div class="relationstext">		<textarea class="textarea" name="r_text" id="r_text" rows="3" cols="15" style="width: 95%" placeholder="Beschreibe hier kurz die Beziehung zwischen {$memprofile[\'username\']} und den NPC."></textarea>
	</div>
  <div class="relationsubmit">	<input type="submit" name="submitrelation" id="submitrelation" value="{$lang->relations_submit}" class="buttom">
		</div>
</div>

</form>'),
        'sid' => '-1',
        'version' => '',
        'dateline' => TIME_NOW
    );
    $db->insert_query("templates", $insert_array);

    $insert_array = array(
        'title' => 'relations_profile',
        'template' => $db->escape_string('<table border="0" cellspacing="{$theme[\'borderwidth\']}" cellpadding="{$theme[\'tablespace\']}" class="tborder tfixed">
<colgroup>
<col style="width: 30%;" />
</colgroup>
<tr>
<td class="thead"><strong>Relationen</strong></td>
</tr>
	<tr><td>
{$relations_form}
		<div class="relation_flex">
			{$relations_cat}
		</div>
		</td>
	</tr>
</table>
<br />'),
        'sid' => '-1',
        'version' => '',
        'dateline' => TIME_NOW
    );
    $db->insert_query("templates", $insert_array);

    $insert_array = array(
        'title' => 'relations_profile_bit',
        'template' => $db->escape_string('<div class="profile_box">
	<div class="profile_charaname">{$charaname}</div>
	<div class="profile_short">{$age} // {$job}</div>
	<div class="profile_avatar"><img src="{$avatar}"></div>
	<div class="profile_relatext">
		<b>{$kind}</b> {$relatext}
		</div>
	<div class="profile_options">
		{$options}
	</div>
</div>'),
        'sid' => '-1',
        'version' => '',
        'dateline' => TIME_NOW
    );
    $db->insert_query("templates", $insert_array);

    $insert_array = array(
        'title' => 'relations_profile_cat',
        'template' => $db->escape_string('<div class="relation_box">
	<div class="tcat"><strong>{$rela_cat}</strong></div>
	{$relations_bit}
</div>'),
        'sid' => '-1',
        'version' => '',
        'dateline' => TIME_NOW
    );
    $db->insert_query("templates", $insert_array);

    $insert_array = array(
        'title' => 'relations_profile_edit',
        'template' => $db->escape_string('<a onclick="$(\'#rela_edit_{$rela[\'rid\']}\').modal({ fadeDuration: 250, keepelement: true, zIndex: (typeof modal_zindex !== \'undefined\' ? modal_zindex : 9999) }); return false;" style="cursor: pointer;">{$lang->relations_popup_edit}</a>	<div class="modal" id="rela_edit_{$rela[\'rid\']}" style="display: none;"><form action="member.php?action=profile&uid={$memprofile}" method="post" id="editrelation">			
	<input type="hidden" class="textbox" name="r_uid" id="r_uid" size="10" maxlength="1155" value="{$rela[\'r_uid\']}"> 			<input type="hidden" class="textbox" name="r_reuid" id="r_reuid" size="10" maxlength="1155" value="{$rela[\'r_reuid\']}"> <input type="hidden" class="textbox" name="rid" id="rid" size="10" maxlength="1155" value="{$rela[\'rid\']}"> 
<div class="relations" style="width: 100%;">
  <div class="relationscat">		<div class="tcat"><strong>{$lang->relations_cat}</strong></div>
			<select name="r_cat">
			<option>{$lang->relations_kind}</option>
			{$cat_options}
		</select>
	</div>
  <div class="relationskind">	<div class="tcat"><strong>{$lang->relations_kind}</strong></div>
				<input type="text" class="textbox" name="r_kind" id="r_kind" size="40" maxlength="1155" value="{$rela[\'r_kind\']}">

	</div>
  <div class="relationstext">		<textarea class="textarea" name="r_text" id="r_text" rows="3" cols="15" style="width: 95%" >{$rela[\'r_text\']}</textarea>
	</div>
  <div class="relationssubmit">		<input type="submit" name="editrelation" id="editrelation" value="{$lang->relations_profil_editsubmit}" class="buttom">
		</div>
</div>
	</form></div>'),
        'sid' => '-1',
        'version' => '',
        'dateline' => TIME_NOW
    );
    $db->insert_query("templates", $insert_array);

    $insert_array = array(
        'title' => 'relations_profile_edit_npc',
        'template' => $db->escape_string('<a onclick="$(\'#rela_edit_{$rela[\'rid\']}\').modal({ fadeDuration: 250, keepelement: true, zIndex: (typeof modal_zindex !== \'undefined\' ? modal_zindex : 9999) }); return false;" style="cursor: pointer;">{$lang->relations_popup_edit}</a>	<div class="modal" id="rela_edit_{$rela[\'rid\']}" style="display: none;"><form action="member.php?action=profile&uid={$memprofile}" method="post" id="editrelation">			
	<input type="hidden" class="textbox" name="r_uid" id="r_uid" size="10" maxlength="1155" value="{$rela[\'r_uid\']}"> 			<input type="hidden" class="textbox" name="r_reuid" id="r_reuid" size="10" maxlength="1155" value="{$row[\'r_reuid\']}"> <input type="hidden" class="textbox" name="rid" id="rid" size="10" maxlength="1155" value="{$rela[\'rid\']}"> 
<div class="npc_relations" style="width: 100%;">
  <div class="charaname">
<div class="tcat"><strong>{$lang->relations_name}</strong></div>
	  <input type="text" class="textbox" name="r_npcname" id="r_npcname" size="40" maxlength="1155" value="{$rela[\'r_npcname\']}"></div>
  <div class="relationstype">
			<div class="tcat"><strong>{$lang->relations_cat}</strong></div>
	  	<select name="r_cat">
			<option>{$lang->relations_kind}</option>
			{$cat_options}
		</select>			
		<div class="tcat"><strong>{$lang->relations_kind}</strong></div>
			<input type="text" class="textbox" name="r_kind" id="r_kind" size="40" maxlength="1155" value="{$rela[\'r_kind\']}">
	
	</div>
  <div class="shortfacts">
				<div class="tcat"><strong>{$lang->relations_age}</strong></div>
			<input type="number" class="textbox" name="r_age" id="r_age" size="40" maxlength="1155" value="{$rela[\'r_age\']}">			<br />
		<div class="tcat"><strong>{$lang->relations_job}</strong></div>
			<input type="text" class="textbox" name="r_job" id="r_job" size="40" maxlength="1155" value="{$rela[\'r_job\']}">
	</div>
  <div class="relationstext">		<textarea class="textarea" name="r_text" id="r_text" rows="3" cols="15" style="width: 95%">{$rela[\'r_text\']}</textarea>
	</div>
  <div class="relationsubmit">		<input type="submit" name="editrelation" id="editrelation" value="{$lang->relations_profil_editsubmit}" class="buttom">
		</div>
</div>
	</form></div>'),
        'sid' => '-1',
        'version' => '',
        'dateline' => TIME_NOW
    );
    $db->insert_query("templates", $insert_array);

    $insert_array = array(
        'title' => 'relations_usercp',
        'template' => $db->escape_string('<html>
<head>
<title>{$lang->user_cp} - {$lang->relations_usercp}</title>
{$headerinclude}
</head>
<body>
{$header}
<table width="100%" border="0" align="center">
<tr>
{$usercpnav}
<td valign="top">
<table border="0" cellspacing="{$theme[\'borderwidth\']}" cellpadding="{$theme[\'tablespace\']}" class="tborder">
<tr>
<td class="thead" colspan="{$colspan}"><strong>{$lang->relations_usercp}</strong></td>
</tr>
<tr>
<td class="trow2" valign="top">
	 <!-- Tab links -->
<div class="tab">
  <button class="tablinks" onclick="openRela(event, \'request\')" id="defaultOpen">{$lang->relations_request}</button></button>
  <button class="tablinks" onclick="openRela(event, \'own\')">{$lang->relations_own}</button>
  <button class="tablinks" onclick="openRela(event, \'other\')">{$lang->relations_other}</button>
</div>

<!-- Tab content -->
<div id="request" class="tabcontent">
	<div class="relations_flex">
{$relations_request}

	</div></div>

<div id="own" class="tabcontent">
	<div class="relations_flex">
{$relations_own}
	</div></div>

<div id="other" class="tabcontent">
	<div class="relations_flex">
{$relations_other}
	</div></div>
</td>
</tr>
</table>
</td>
</tr>
</table>
{$footer}
</body>
</html>

<script>
function openRela(evt, relations) {
  var i, tabcontent, tablinks;
  tabcontent = document.getElementsByClassName("tabcontent");
  for (i = 0; i < tabcontent.length; i++) {
    tabcontent[i].style.display = "none";
  }
  tablinks = document.getElementsByClassName("tablinks");
  for (i = 0; i < tablinks.length; i++) {
    tablinks[i].className = tablinks[i].className.replace(" active", "");
  }
  document.getElementById(relations).style.display = "block";
  evt.currentTarget.className += " active";
}

// Get the element with id="defaultOpen" and click on it
document.getElementById("defaultOpen").click();
</script>'),
        'sid' => '-1',
        'version' => '',
        'dateline' => TIME_NOW
    );
    $db->insert_query("templates", $insert_array);

    $insert_array = array(
        'title' => 'relations_usercp_bit',
        'template' => $db->escape_string('<div class="relations_box">
	<div class="relations_username">{$charaname}</div>
	<div class="relations_short">{$cat} || {$kind}</div>
	<div class="relations_text">{$relatext}</div>
	<div class="relations_options">{$options}</div>
</div>'),
        'sid' => '-1',
        'version' => '',
        'dateline' => TIME_NOW
    );
    $db->insert_query("templates", $insert_array);

    $insert_array = array(
        'title' => 'relations_usercp_nav',
        'template' => $db->escape_string('<tr><td class="trow1 smalltext"><a href="usercp.php?action=relations" class="usercp_nav_item usercp_nav_editlists">{$lang->relations_ucp_nav}</a></td></tr>'),
        'sid' => '-1',
        'version' => '',
        'dateline' => TIME_NOW
    );
    $db->insert_query("templates", $insert_array);

    // CSS 
    //CSS einfügen
    $css = array(
        'name' => 'relations.css',
        'tid' => 1,
        'attachedto' => '.relation_flex{
	display: flex;
	flex-wrap: wrap;
	justify-content: center;
}

.relation_box{
	width: 45%;
	box-sizing: border-box;
	margin: 10px 2px;
}

.npc_relations {  display: grid;
  grid-template-columns: 1fr 1fr;
  grid-template-rows: min-content min-content min-content min-content; 
  gap: 5px 5px;
  grid-auto-flow: row;
  grid-template-areas:
    "charaname charaname"
    "relationstype shortfacts"
    "relationstext relationstext"
    "relationsubmit relationsubmit";
	width: 50%;
	text-align: center;
	margin: auto;
}


.charaname { grid-area: charaname; }

.relationstype { grid-area: relationstype; }

.shortfacts { grid-area: shortfacts; }

.relationstext { grid-area: relationstext; }

.relationsubmit { grid-area: relationsubmit; }


.relations  {  display: grid;
  grid-template-columns: 1fr 1fr;
  grid-template-rows: min-content min-content min-content;
  gap: 5px 5px;
  grid-auto-flow: row;
  grid-template-areas:
    "relationscat relationskind"
    "relationstext relationstext"
    "relationssubmit relationssubmit";
		width: 50%;
	text-align: center;
	margin: auto;
}

.relationscat { grid-area: relationscat; }

.relationskind { grid-area: relationskind; }

.relationstext { grid-area: relationstext; }

.relationssubmit { grid-area: relationssubmit; }

.npc_relations > div, .relations > div {
	padding: 5px 10px;	
}
.npc_relations .tcat, .relations .tcat{
	margin: 5px 0;	
}

/*profile*/
.profile_box{
	width: 100%;
	margin: 10px;
	padding: 10px;
	box-sizing: border-box;
}

.profile_charaname{
	font-size 15px;
	text-align: center;
	border-bottom: 1px solid;
}

.profile_short{
	font-size: 10px;
	text-align: center;
}

.profile_relatext{
	height:100px;
	width: justify;
	overflow: auto;
	line-height: 1.2em;
}

.profile_avatar{
	height:100px;
	width: auto;
	float: left;
	margin: 2px 5px 0 1px;
}

.profile_avatar img{
	height: 100px;
	width: auto;
	float: left;
	margin: 2px 5px 0 1px;
}

.profile_options{
	width: 100%;
	text-align: center;
}

/*usercp*/

/* Style the tab */
.tab {
  overflow: hidden;
  border: 1px solid #ccc;
  background-color: #f1f1f1;
}

/* Style the buttons inside the tab */
.tab button {
  background-color: inherit;
  float: left;
  border: none;
  outline: none;
  cursor: pointer;
  padding: 14px 16px;
  transition: 0.3s;
  font-size: 17px;
}

/* Change background color of buttons on hover */
.tab button:hover {
  background-color: #ddd;
}

/* Create an active/current tablink class */
.tab button.active {
  background-color: #ccc;
}

/* Style the tab content */
.tabcontent {
  display: none;
  padding: 6px 12px;
  -webkit-animation: fadeEffect 1s;
  animation: fadeEffect 1s;
}

/* Fade in tabs */
@-webkit-keyframes fadeEffect {
  from {opacity: 0;}
  to {opacity: 1;}
}

@keyframes fadeEffect {
  from {opacity: 0;}
  to {opacity: 1;}
}

.relations_flex{
	display: flex;
	flex-wrap: wrap;
}

.relations_flex .relations_box{
	width: 33%;
	margin: 10px 5px;
	background: #efefef;
	box-sizing: border-box;
	padding: 2px;
}

.relations_username{
	font-size: 15px;
	text-align: center;
	font-weight: bold;
}

.relations_short{
		font-size: 11px;
	text-align: center;
border-bottom: 1px solid #0f0f0f;
	padding: 3px 10px;
	margin-bottom: 2px;
}

.relations_text{
			font-size: 11px;
	text-align: justify;
}

.relations_options{
		font-size: 13px;
	text-align: center;
	padding: 5px;
}',
        "stylesheet" => '',
        'cachefile' => $db->escape_string(str_replace('/', '', 'relations.css')),
        'lastmodified' => time()
    );

    require_once MYBB_ADMIN_DIR . "inc/functions_themes.php";

    $sid = $db->insert_query("themestylesheets", $css);
    $db->update_query("themestylesheets", array("cachefile" => "css.php?stylesheet=" . $sid), "sid = '" . $sid . "'", 1);

    $tids = $db->simple_select("themes", "tid");
    while ($theme = $db->fetch_array($tids)) {
        update_theme_stylesheet_list($theme['tid']);
    }
    rebuild_settings();
}

function relations_is_installed()
{
    global $db;
    if ($db->table_exists("relations")) {
        return true;
    }
    return false;
}

function relations_uninstall()
{
    global $db, $cache;
    if ($db->table_exists("relations")) {
        $db->drop_table("relations");
    }

    // Einstellungen löschen
    $db->query("DELETE FROM " . TABLE_PREFIX . "settinggroups WHERE name='relations'");
    $db->query("DELETE FROM " . TABLE_PREFIX . "settings WHERE name='relations_cat'");
    $db->query("DELETE FROM " . TABLE_PREFIX . "settings WHERE name='relations_default'");
    $db->query("DELETE FROM " . TABLE_PREFIX . "settings WHERE name='relations_avatar'");
    $db->query("DELETE FROM " . TABLE_PREFIX . "settings WHERE name='relations_avatar_fid'");
    $db->query("DELETE FROM " . TABLE_PREFIX . "settings WHERE name='relations_age'");
    $db->query("DELETE FROM " . TABLE_PREFIX . "settings WHERE name='relations_age_fid'");
    $db->query("DELETE FROM " . TABLE_PREFIX . "settings WHERE name='relations_job'");
    $db->query("DELETE FROM " . TABLE_PREFIX . "settings WHERE name='relations_date'");

    $db->delete_query("templates", "title LIKE '%relations%'");

    require_once MYBB_ADMIN_DIR . "inc/functions_themes.php";
    $db->delete_query("themestylesheets", "name = 'relations.css'");
    $query = $db->simple_select("themes", "tid");
    while ($theme = $db->fetch_array($query)) {
        update_theme_stylesheet_list($theme['tid']);
    }

    rebuild_settings();
}

function relations_activate()
{
    global $db, $cache;

    if (class_exists('MybbStuff_MyAlerts_AlertTypeManager')) {
        $alertTypeManager = MybbStuff_MyAlerts_AlertTypeManager::getInstance();

        if (!$alertTypeManager) {
            $alertTypeManager = MybbStuff_MyAlerts_AlertTypeManager::createInstance($db, $cache);
        }

        $alertType = new MybbStuff_MyAlerts_Entity_AlertType();
        $alertType->setCode('relations_accept'); // The codename for your alert type. Can be any unique string.
        $alertType->setEnabled(true);
        $alertType->setCanBeUserDisabled(true);

        $alertTypeManager->add($alertType);

        $alertType = new MybbStuff_MyAlerts_Entity_AlertType();
        $alertType->setCode('relations_refuse'); // The codename for your alert type. Can be any unique string.
        $alertType->setEnabled(true);
        $alertType->setCanBeUserDisabled(true);

        $alertTypeManager->add($alertType);


        $alertType = new MybbStuff_MyAlerts_Entity_AlertType();
        $alertType->setCode('relations_edit'); // The codename for your alert type. Can be any unique string.
        $alertType->setEnabled(true);
        $alertType->setCanBeUserDisabled(true);

        $alertTypeManager->add($alertType);

    }

    require MYBB_ROOT . "/inc/adminfunctions_templates.php";
    find_replace_templatesets("member_profile", "#" . preg_quote('{$bannedbit}') . "#i", '{$bannedbit}{$relations_profile}');
    find_replace_templatesets("header", "#" . preg_quote('{$pm_notice}') . "#i", '{$relations_alerts} {$pm_notice}');
}

function relations_deactivate()
{
    global $db, $cache;

    if (class_exists('MybbStuff_MyAlerts_AlertTypeManager')) {
        $alertTypeManager = MybbStuff_MyAlerts_AlertTypeManager::getInstance();

        if (!$alertTypeManager) {
            $alertTypeManager = MybbStuff_MyAlerts_AlertTypeManager::createInstance($db, $cache);
        }

        $alertTypeManager->deleteByCode('relations_accept');
        $alertTypeManager->deleteByCode('relations_refuse');
        $alertTypeManager->deleteByCode('relations_edit');
    }


    require MYBB_ROOT . "/inc/adminfunctions_templates.php";
    find_replace_templatesets("member_profile", "#" . preg_quote('{$relations_profile}') . "#i", '', 0);
    find_replace_templatesets("header", "#" . preg_quote('{$relations_alerts}') . "#i", '', 0);
}
// ADMIN-CP PEEKER
$plugins->add_hook('admin_config_settings_change', 'relations_settings_change');
$plugins->add_hook('admin_settings_print_peekers', 'relations_settings_peek');
function relations_settings_change()
{
    global $db, $mybb, $relations_settings_peeker;

    $result = $db->simple_select('settinggroups', 'gid', "name='relations'", array("limit" => 1));
    $group = $db->fetch_array($result);
    $relations_settings_peeker = ($mybb->input['gid'] == $group['gid']) && ($mybb->request_method != 'post');
}
function relations_settings_peek(&$peekers)
{
    global $mybb, $relations_settings_peeker;

    if ($relations_settings_peeker) {
        $peekers[] = 'new Peeker($(".setting_relations_avatar"), $("#row_setting_relations_avatar_fid"),/1/,true)';
    }
    if ($relations_settings_peeker) {
        $peekers[] = 'new Peeker($(".setting_relations_age"), $("#row_setting_relations_age_fid"),/1/,true)';
    }
}

function relations_profile()
{
    global $db, $mybb, $templates, $lang, $memprofile, $relations_form, $relations_profile, $cat_options, $theme, $parser, $relations_bit, $relations_edit, $cat_options, $options;
    $lang->load('relations');
    require_once MYBB_ROOT . "inc/class_parser.php";
    $parser = new postParser;

    $options = array(
        "allow_html" => 1,
        "allow_mycode" => 1,
        "allow_smilies" => 1,
        "allow_imgcode" => 1,
        "filter_badwords" => 0,
        "nl2br" => 1,
        "allow_videocode" => 0
    );

    $ownuid = $mybb->user['uid'];
    $memuid = $memprofile['uid'];

    // Einstellungen
    $cat_setting = explode(", ", $mybb->settings['relations_cat']);
    $avatar = $mybb->settings['relations_avatar'];
    $avatar_fid = $mybb->settings['relations_avatar_fid'];
    $job = $mybb->settings['relations_job'];
    $age = $mybb->settings['relations_age'];
    $age_fid = $mybb->settings['relations_age_fid'];
    $default_avatar = $mybb->settings['relations_default'];
    $inplaydate = $mybb->settings['relations_date'];

    foreach ($cat_setting as $cat) {

        $cat_options .= "<option value='{$cat}'>{$cat}</option>";

    }

    if ($ownuid != 0) {
        if ($ownuid == $memuid) {
            // man ist auf seinem eigenen Profil        
            eval ("\$relations_form = \"" . $templates->get("relations_npc_form") . "\";");
        } elseif ($ownuid != $memuid) {
            // man ist auf einem fremden Profil
            eval ("\$relations_form = \"" . $templates->get("relations_form") . "\";");
        }
    }

    // Relation einfügen
    if (isset($mybb->input['submitrelation'])) {
        $r_uid = $_POST['r_uid'];
        $r_reuid = $_POST['r_reuid'];
        $r_npcname = htmlspecialchars($_POST['r_npcname']);
        $r_cat = htmlspecialchars($_POST['r_cat']);
        $r_kind = htmlspecialchars($_POST['r_kind']);
        $r_age = $_POST['r_age'];
        $r_job = htmlspecialchars($_POST['r_job']);
        $r_text = htmlspecialchars($_POST['r_text']);
        if ($ownuid == $memuid) {
            $r_ok = 1;
        }


        $new_relation = array(
            "r_uid" => $r_uid,
            "r_reuid" => $r_reuid,
            "r_npcname" => $r_npcname,
            "r_cat" => $r_cat,
            "r_kind" => $r_kind,
            "r_age" => $r_age,
            "r_job" => $r_job,
            "r_text" => $r_text,
            "r_ok" => $r_ok
        );

        $db->insert_query("relations", $new_relation);
        redirect("member.php?action=profile&uid={$memprofile['uid']}");
    }

    // relations auslesen

    // brauchen wir einmal, um den Geburtstag zu berechnen, wenn über mybb-Geburtstagfeld :D
    // einmal bitte Inplaydatum auseinandernehmen
    $inplay_date = explode(".", $inplaydate);
    $inplayyear = $inplay_date[2];
    $rep_ipyear = array(2 => $inplayyear);
    $inplay_date = array_replace($inplay_date, $rep_ipyear);
    $lastday = implode(".", $inplay_date);
    $inplay = new DateTime($lastday);

    $relations_cat = "";
    foreach ($cat_setting as $rela_cat) {
        $relations_bit = "";


        $get_relas = $db->query("SELECT *
        FROM " . TABLE_PREFIX . "relations r
        LEFT JOIN " . TABLE_PREFIX . "users u
        on (r.r_reuid = u.uid)
        LEFT JOIN " . TABLE_PREFIX . "userfields uf
        on (u.uid = uf.ufid)
        WHERE r.r_uid = '" . $memuid . "'
        and r.r_cat = '" . $rela_cat . "'
        and r.r_ok = 1
        ORDER BY r.r_kind ASC, u.username ASC, r.r_npcname ASC
        ");

        while ($rela = $db->fetch_array($get_relas)) {
            $avatar = "";
            $kind = "";
            $age = 0;
            $job = "";
            $relatext = "";
            $charaname = "";
            $options = "";
            $cat_options = "";
            $relations_edit = "";

            foreach ($cat_setting as $r_cat) {
                $checked = "";
                if ($r_cat == $rela['r_cat']) {
                    $checked = "selected";
                }
                $cat_options .= "<option value='{$r_cat}' {$checked}>{$r_cat}</option>";

            }
            if ($rela['r_reuid'] == 0) {

                $charaname = $rela['r_npcname'];
                $avatar = "{$theme['imgdir']}/{$default_avatar}";
                $kind = $rela['r_kind'];
                $age = $rela['r_age'] . " Jahre";
                $job = $rela['r_job'];
                $relatext = $parser->parse_message($rela['r_text'], $options);
                eval ("\$relations_edit = \"" . $templates->get("relations_profile_edit_npc") . "\";");
            } else {

                $username = format_name($rela['username'], $rela['usergroup'], $rela['displaygroup']);
                $charaname = build_profile_link($username, $rela['uid']);
                if ($avatar == 1) {
                    $avatar = "{$avatar_fid}";
                } else {
                    $avatar = $rela['avatar'];
                }
                $kind = $rela['r_kind'];
                if ($age == 0) {
                    if (!empty($rela['birthday'])) {
                        // einmal den Geburtstag auseinandernehmen
                        $explode_birthday = explode("-", $rela['birthday']);

                        // nun ist jeder Part vom Geburtstag ein eigener Array eingetrag. Jetzt zieh ich sie mir einzeln. Arrays beginnen immer bei 0, weswegen die erste Position die 0 ist.
                        $birth_day = $explode_birthday[0];
                        $birth_month = $explode_birthday[1];
                        $birth_year = $explode_birthday[2];

                        $birth_year_count = strlen($birth_year);

                        // dann gucken wir mal, ob das Geburtsjahr 4 Ziffern hat, sonst müssen wir auffüllen.
                        if ($birth_year_count < 4) {
                            $zero = "";
                            for ($i = $birth_year_count; $i <= 3; $i++) {
                                $zero = "0";
                            }
                            $birthyear = $zero . $birth_year;

                        } else {
                            $birthyear = $birth_year;
                        }

                        // wir formatieren Geburtstag neu :) und berechnen den Geburtstag daraus.

                        $charabirthday = new DateTime($birth_day . "." . $birth_month . "." . $birthyear);
                        $interval = $inplay->diff($charabirthday);
                        $age = $interval->format("%Y Jahre");

                    } else {
                        $age = "0 Jahre";
                    }
                } else {
                    $age = $rela[$age_fid] . " Jahre";
                }

                if ($job == 1) {
                    if (!empty($rela['job'])) {
                        $job = $rela['job'];
                    } else {
                        $job = $lang->relations_profile_job;
                    }
                }

                $relatext = $parser->parse_message($rela['r_text'], $options);

                eval ("\$relations_edit = \"" . $templates->get("relations_profile_edit") . "\";");

            }

            if ($memuid == $ownuid) {
                $options = "{$relations_edit} {$lang->relations_profile_inbetween} <a href='member.php?action=profile&delete_rela={$rela['rid']}'>{$lang->relations_profile_delete}</a>";
            }

            eval ("\$relations_bit .= \"" . $templates->get("relations_profile_bit") . "\";");
        }
        eval ("\$relations_cat .= \"" . $templates->get("relations_profile_cat") . "\";");
    }

    eval ("\$relations_profile = \"" . $templates->get("relations_profile") . "\";");

    // Relation ändern
    if (isset($mybb->input['editrelation'])) {
        $rid = $mybb->input['rid'];
        $r_uid = $mybb->input['r_uid'];
        $r_reuid = $mybb->input['r_reuid'];
        $r_npcname = htmlspecialchars($mybb->input['r_npcname']);
        $r_cat = htmlspecialchars($mybb->input['r_cat']);
        $r_kind = htmlspecialchars($mybb->input['r_kind']);
        $r_age = $mybb->input['r_age'];
        $r_job = htmlspecialchars($mybb->input['r_job']);
        $r_text = htmlspecialchars($mybb->input['r_text']);
        if ($r_reuid == 0) {
            $r_ok = 1;
        }


        $edit_request = array(
            "r_uid" => $r_uid,
            "r_reuid" => $r_reuid,
            "r_npcname" => $r_npcname,
            "r_cat" => $r_cat,
            "r_kind" => $r_kind,
            "r_age" => $r_age,
            "r_job" => $r_job,
            "r_text" => $r_text,
            "r_ok" => $r_ok
        );


        // Alert auslösen, weil wir wollen ja bescheid wissen, ne?!
        if (class_exists('MybbStuff_MyAlerts_AlertTypeManager')) {
            $alertType = MybbStuff_MyAlerts_AlertTypeManager::getInstance()->getByCode('relations_edit');
            if ($alertType != NULL && $alertType->getEnabled() && $r_reuid != 0) {
                $alert = new MybbStuff_MyAlerts_Entity_Alert((int) $r_reuid, $alertType);
                $alert->setExtraDetails([
                    'kind' => $r_kind,
                    'cat' => $r_cat
                ]);
                MybbStuff_MyAlerts_AlertManager::getInstance()->addAlert($alert);
            }
        }


        $db->update_query("relations", $edit_request, "rid = {$rid}");
        redirect("member.php?action=profile&uid={$memprofile['uid']}");
    }


    if (isset($mybb->input['delete_rela'])) {
        $delete = "";
        $delete = $mybb->input['delete_rela'];
        $db->delete_query("relations", "rid = {$delete}");
        redirect("member.php?action=profile&uid={$memprofile['uid']}");
    }

}

function relations_global_alert()
{
    global $db, $mybb, $templates, $lang, $relations_alerts;
    $lang->load('relations');


    //welcher user ist online
    $this_user = intval($mybb->user['uid']);

    //für den fall nicht mit hauptaccount online
    $as_uid = intval($mybb->user['as_uid']);

    // suche alle angehangenen accounts
    if ($as_uid == 0) {
        $select = $db->query("SELECT * FROM " . TABLE_PREFIX . "users 
        WHERE (as_uid = $this_user) OR (uid = $this_user) ORDER BY username ASC");
    } else if ($as_uid != 0) {
        //id des users holen wo alle angehangen sind
        $select = $db->query("SELECT * FROM " . TABLE_PREFIX . "users WHERE (as_uid = $as_uid) OR (uid = $this_user) OR (uid = $as_uid) ORDER BY username ASC");
    }

    while ($alert = $db->fetch_array($select)) {
        $select_alert = $db->query("SELECT *
    FROM " . TABLE_PREFIX . "relations r
    LEFT JOIN " . TABLE_PREFIX . "users u
    on (r.r_reuid = u.uid)
    WHERE r.r_ok = 0
    and  r.r_reuid  != '" . $mybb->user['uid'] . "'
    and r.r_reuid = '" . $alert['uid'] . "'
     ");

        $alert2 = $db->fetch_array($select_alert);
        $count = mysqli_num_rows($select_alert);
        if (isset($alert2['username'])) {
            $user = $alert2['username'];
        }
        if ($mybb->user['uid'] != 0) {
            if ($count == '1') {
                $request = "Relationsanfrage";
            } else {
                $request = "Relationsanfragen";
            }

            if ($count != 0) {
                eval ("\$relations_alerts .= \"" . $templates->get("relations_alert_other") . "\";");
            }
        }

    }

    $get_relations = $db->query("SELECT *
        FROM " . TABLE_PREFIX . "relations
         WHERE r_ok = 0
         and  r_reuid = '" . $mybb->user['uid'] . "'
    ");

    $count_own = mysqli_num_rows($get_relations);
    if ($mybb->user['uid'] != 0) {
        if ($count_own == '1') {
            $request = "Relationsanfrage";
        } else {
            $request = "Relationsanfragen";
        }

        $lang->relations_alert = $lang->sprintf($lang->relations_alert, $count_own, $request);
        if ($count_own != 0) {
            eval ("\$relations_alerts = \"" . $templates->get("relations_alert") . "\";");
        }
    }

}

function relations_usercp_nav()
{
    global $db, $mybb, $templates, $usercpmenu, $lang;
    $lang->load('relations');
    eval ("\$usercpmenu .= \"" . $templates->get("relations_usercp_nav") . "\";");
}


function relations_usercp()
{
    global $mybb, $lang, $templates, $lang, $header, $headerinclude, $footer, $page, $usercpnav, $db, $relations_request, $options, $relations_edit, $theme, $colspan;
    $lang->load('relations');
    require_once MYBB_ROOT . "inc/class_parser.php";
    $parser = new postParser;
    // Do something, for example I'll create a page using the hello_world_template
    $options = array(
        "allow_html" => 1,
        "allow_mycode" => 1,
        "allow_smilies" => 1,
        "allow_imgcode" => 1,
        "filter_badwords" => 0,
        "nl2br" => 1,
        "allow_videocode" => 0
    );
    // Einstellungen
    $cat_setting = explode(", ", $mybb->settings['relations_cat']);
    if ($mybb->get_input('action') == 'relations') {
        //Erstmal die Anzeige generieren
        add_breadcrumb($lang->relations_usercp, "usercp.php?action=relations");
        $uid = $mybb->user['uid'];

        $count_request = 0;
        $count_own = 0;
        $count_other = 0;
        // Deine Anfragen

        $request_query = $db->query("SELECT *
        FROM " . TABLE_PREFIX . "relations r
        JOIN " . TABLE_PREFIX . "users u
        on (r.r_uid = u.uid)
        WHERE r.r_reuid = '" . $uid . "'
        and r.r_ok = 0
        ORDER BY u.username ASC, r.r_cat ASC
        ");

        while ($row = $db->fetch_array($request_query)) {
            //variabel definieren
            $charaname = "";
            $kind = "";
            $cat = "";
            $relatext = "";
            $options = "";

            $count_request++;

            $charaname = $row['username'];
            $kind = $row['r_kind'];
            $cat = $row['r_cat'];
            $relatext = $parser->parse_message($row['r_text'], $options);

            $options = "<a href='usercp.php?action=relations&accepted={$row['rid']}&uid={$row['r_uid']}' title='{$lang->relations_ucp_accept}'>{$lang->relations_ucp_accept_check}</a> {$lang->relations_inbetween}  <a href='usercp.php?action=relations&refuse={$row['rid']}&uid={$row['r_uid']}' title='{$lang->relations_ucp_refuse}'>{$lang->relations_ucp_refuse_x}</a>";

            eval ("\$relations_request .= \"" . $templates->get("relations_usercp_bit") . "\";");
        }

        if ($count_request == 0) {
            $relations_request = $lang->relations_norelations_request;
        }




        // Relationsn in der Übersicht

        $rela_own_query = $db->query("SELECT *
            FROM " . TABLE_PREFIX . "relations r
        JOIN " . TABLE_PREFIX . "users u
        on (r.r_reuid = u.uid)
        WHERE r.r_uid = '" . $uid . "'
        and r.r_ok = 1
        ORDER BY u.username ASC, r.r_cat ASC
    ");

        while ($row = $db->fetch_array($rela_own_query)) {
            //variabel definieren
            $charaname = "";
            $kind = "";
            $cat = "";
            $relatext = "";
            $options = "";

            $count_own++;

            $charaname = $row['username'];
            $kind = $row['r_kind'];
            $cat = $row['r_cat'];
            $relatext = $parser->parse_message($row['r_text'], $options);


            foreach ($cat_setting as $r_cat) {
                if ($r_cat == $row['r_cat']) {
                    $checked = "selected";
                }
                $cat_options .= "<option value='{$r_cat}' {$checked}>{$r_cat}</option>";

            }

            eval ("\$relations_edit = \"" . $templates->get("relations_edit") . "\";");


            $options = "{$relations_edit} {$lang->relations_inbetween} <a href='usercp.php?action=relations&delete={$row['rid']}' title='{$lang->relations_ucp_delete}'>{$lang->relations_ucp_delete_x}</a>";

            eval ("\$relations_own .= \"" . $templates->get("relations_usercp_bit") . "\";");
        }

        if ($count_own == 0) {
            $relations_own = $lang->relations_norelations_own;
        }



        // Fremd Relationen in der Übersicht

        $rela_own_query = $db->query("SELECT *
            FROM " . TABLE_PREFIX . "relations r
        JOIN " . TABLE_PREFIX . "users u
        on (r.r_reuid = u.uid)
        WHERE r.r_reuid = '" . $uid . "'
        and r.r_ok = 1
        ORDER BY u.username ASC, r.r_cat ASC
    ");

        while ($row = $db->fetch_array($rela_own_query)) {
            //variabel definieren
            $charaname = "";
            $kind = "";
            $cat = "";
            $relatext = "";
            $options = "";

            $count_other++;

            $charaname = $row['username'];
            $kind = $row['r_kind'];
            $cat = $row['r_cat'];
            $relatext = $parser->parse_message($row['r_text'], $options);
            foreach ($cat_setting as $r_cat) {
                if ($r_cat == $row['r_cat']) {
                    $checked = "selected";
                }
                $cat_options .= "<option value='{$r_cat}' {$checked}>{$r_cat}</option>";

            }
            $relations_addtoo = "";
            eval ("\$relations_addtoo = \"" . $templates->get("relations_addtoo") . "\";");


            $options = "{$relations_addtoo} {$lang->relations_inbetween} <a href='usercp.php?action=relations&delete={$row['rid']}' title='{$lang->relations_ucp_delete}'>{$lang->relations_ucp_delete_x}</a>";

            eval ("\$relations_other .= \"" . $templates->get("relations_usercp_bit") . "\";");
        }

        if ($count_other == 0) {
            $relations_other = $lang->relations_norelations_other;
        }

        // Relationsanfrage bestätigen
        $accept = "";
        if (isset($mybb->input['accepted'])) {
            $accept = $mybb->input['accepted'];
        }
        $r_uid = 0;
        if (isset($mybb->input['uid'])) {
            $r_uid = $mybb->input['uid'];
        }
        if ($accept) {
            $accept_request = array(
                "r_ok" => 1
            );

            $rela_infos = $db->fetch_array($db->simple_select("relations", "*", "rid = {$accept}"));
            $kind = $rela_infos['r_kind'];
            $cat = $rela_infos['r_cat'];

            // Alert auslösen, weil wir wollen ja bescheid wissen, ne?!
            if (class_exists('MybbStuff_MyAlerts_AlertTypeManager')) {
                $alertType = MybbStuff_MyAlerts_AlertTypeManager::getInstance()->getByCode('relations_accept');
                if ($alertType != NULL && $alertType->getEnabled()) {
                    $alert = new MybbStuff_MyAlerts_Entity_Alert((int) $r_uid, $alertType);
                    $alert->setExtraDetails([
                        'uid' => $r_uid,
                        'kind' => $kind,
                        'cat' => $cat
                    ]);
                    MybbStuff_MyAlerts_AlertManager::getInstance()->addAlert($alert);
                }
            }


            $db->update_query("relations", $accept_request, "rid = {$accept}");
            redirect("usercp.php?action=relations");
        }

        // Relationsanfrage ablehnen
        $refuse = "";
        if (isset($mybb->input['refuse'])) {
            $refuse = $mybb->input['refuse'];
        }
        if ($refuse) {
            $rela_infos = $db->fetch_array($db->simple_select("relations", "*", "rid = {$refuse}"));
            $kind = $rela_infos['r_kind'];
            $cat = $rela_infos['r_cat'];
            $r_reuid = $rela_infos['r_reuid'];
            // Alert auslösen, weil wir wollen ja bescheid wissen, ne?!
            if (class_exists('MybbStuff_MyAlerts_AlertTypeManager')) {
                $alertType = MybbStuff_MyAlerts_AlertTypeManager::getInstance()->getByCode('relations_refuse');
                if ($alertType != NULL && $alertType->getEnabled()) {
                    $alert = new MybbStuff_MyAlerts_Entity_Alert((int) $r_uid, $alertType);
                    $alert->setExtraDetails([
                        'uid' => $r_reuid,
                        'kind' => $kind,
                        'cat' => $cat
                    ]);
                    MybbStuff_MyAlerts_AlertManager::getInstance()->addAlert($alert);
                }
            }


            $db->delete_query("relations", "rid = {$refuse}");
            redirect("usercp.php?action=relations");
        }


        // Relation ändern
        if (isset($mybb->input['editrelation'])) {
            $r_reuid = $mybb->input['r_reuid'];
            $r_uid = $mybb->input['r_uid'];
            $rid = $mybb->input['rid'];
            $r_cat = htmlspecialchars($mybb->input['r_cat']);
            $r_kind = htmlspecialchars($mybb->input['r_kind']);
            $r_text = htmlspecialchars($mybb->input['r_text']);


            $edit_request = array(
                "r_cat" => $r_cat,
                "r_kind" => $r_kind,
                "r_text" => $r_text,
                "r_ok" => 0
            );

            // Alert auslösen, weil wir wollen ja bescheid wissen, ne?!
            if (class_exists('MybbStuff_MyAlerts_AlertTypeManager')) {
                $alertType = MybbStuff_MyAlerts_AlertTypeManager::getInstance()->getByCode('relations_edit');
                if ($alertType != NULL && $alertType->getEnabled()) {
                    $alert = new MybbStuff_MyAlerts_Entity_Alert((int) $r_reuid, $alertType);
                    $alert->setExtraDetails([
                        'kind' => $r_kind,
                        'cat' => $r_cat
                    ]);
                    MybbStuff_MyAlerts_AlertManager::getInstance()->addAlert($alert);
                }
            }


            $db->update_query("relations", $edit_request, "rid = {$rid}");
            redirect("usercp.php?action=relations");
        }

        // Relation auch eintragen
        // Relation einfügen
        if (isset($mybb->input['submitrelation'])) {
            $r_uid = $mybb->input['r_uid'];
            $r_reuid = $mybb->input['r_reuid'];
            $r_cat = htmlspecialchars($mybb->input['r_cat']);
            $r_kind = htmlspecialchars($mybb->input['r_kind']);
            $r_text = htmlspecialchars($mybb->input['r_text']);

            $new_relation = array(
                "r_uid" => $r_uid,
                "r_reuid" => $r_reuid,
                "r_npcname" => $r_npcname,
                "r_cat" => $r_cat,
                "r_kind" => $r_kind,
                "r_age" => $r_age,
                "r_job" => $r_job,
                "r_text" => $r_text,
                "r_ok" => 0
            );

            $db->insert_query("relations", $new_relation);
            redirect("usercp.php?action=relations");
        }


        eval ("\$page = \"" . $templates->get("relations_usercp") . "\";");
        output_page($page);
    }




}

// Benachrichtungen generieren
function relations_alerts()
{
    global $mybb, $lang;
    $lang->load('relations');


    /**
     * Alert, wenn die Relations angenommen wurde
     */
    class MybbStuff_MyAlerts_Formatter_AcceptRelationsFormatter extends MybbStuff_MyAlerts_Formatter_AbstractFormatter
    {
        /**
         * Format an alert into it's output string to be used in both the main alerts listing page and the popup.
         *
         * @param MybbStuff_MyAlerts_Entity_Alert $alert The alert to format.
         *
         * @return string The formatted alert string.
         */
        public function formatAlert(MybbStuff_MyAlerts_Entity_Alert $alert, array $outputAlert)
        {
            $alertContent = $alert->getExtraDetails();
            return $this->lang->sprintf(
                $this->lang->relations_accept,
                $outputAlert['from_user'],
                $alertContent['kind'],
                $alertContent['cat'],
                $outputAlert['dateline']
            );
        }


        /**
         * Init function called before running formatAlert(). Used to load language files and initialize other required
         * resources.
         *
         * @return void
         */
        public function init()
        {
        }

        /**
         * Build a link to an alert's content so that the system can redirect to it.
         *
         * @param MybbStuff_MyAlerts_Entity_Alert $alert The alert to build the link for.
         *
         * @return string The built alert, preferably an absolute link.
         */
        public function buildShowLink(MybbStuff_MyAlerts_Entity_Alert $alert)
        {
            $alertContent = $alert->getExtraDetails();
            return $this->mybb->settings['bburl'] . '/member.php?action=profile&uid=' . $alertContent['uid'];
        }
    }

    if (class_exists('MybbStuff_MyAlerts_AlertFormatterManager')) {
        $formatterManager = MybbStuff_MyAlerts_AlertFormatterManager::getInstance();

        if (!$formatterManager) {
            $formatterManager = MybbStuff_MyAlerts_AlertFormatterManager::createInstance($mybb, $lang);
        }

        $formatterManager->registerFormatter(
            new MybbStuff_MyAlerts_Formatter_AcceptRelationsFormatter($mybb, $lang, 'relations_accept')
        );
    }

    /**
     * Alert, wenn die Relations abgelehnt wurde
     */
    class MybbStuff_MyAlerts_Formatter_RefuseRelationsFormatter extends MybbStuff_MyAlerts_Formatter_AbstractFormatter
    {
        /**
         * Format an alert into it's output string to be used in both the main alerts listing page and the popup.
         *
         * @param MybbStuff_MyAlerts_Entity_Alert $alert The alert to format.
         *
         * @return string The formatted alert string.
         */
        public function formatAlert(MybbStuff_MyAlerts_Entity_Alert $alert, array $outputAlert)
        {
            $alertContent = $alert->getExtraDetails();
            return $this->lang->sprintf(
                $this->lang->relations_refuse,
                $outputAlert['from_user'],
                $alertContent['kind'],
                $alertContent['cat'],
                $outputAlert['dateline']
            );
        }


        /**
         * Init function called before running formatAlert(). Used to load language files and initialize other required
         * resources.
         *
         * @return void
         */
        public function init()
        {
        }

        /**
         * Build a link to an alert's content so that the system can redirect to it.
         *
         * @param MybbStuff_MyAlerts_Entity_Alert $alert The alert to build the link for.
         *
         * @return string The built alert, preferably an absolute link.
         */
        public function buildShowLink(MybbStuff_MyAlerts_Entity_Alert $alert)
        {
            $alertContent = $alert->getExtraDetails();
            return $this->mybb->settings['bburl'] . '/member.php?action=profile&uid=' . $alertContent['uid'];
        }
    }

    if (class_exists('MybbStuff_MyAlerts_AlertFormatterManager')) {
        $formatterManager = MybbStuff_MyAlerts_AlertFormatterManager::getInstance();

        if (!$formatterManager) {
            $formatterManager = MybbStuff_MyAlerts_AlertFormatterManager::createInstance($mybb, $lang);
        }

        $formatterManager->registerFormatter(
            new MybbStuff_MyAlerts_Formatter_RefuseRelationsFormatter($mybb, $lang, 'relations_refuse')
        );
    }

    /**
     * Alert, wenn die Relations editiert wurde
     */
    class MybbStuff_MyAlerts_Formatter_EditRelationsFormatter extends MybbStuff_MyAlerts_Formatter_AbstractFormatter
    {
        /**
         * Format an alert into it's output string to be used in both the main alerts listing page and the popup.
         *
         * @param MybbStuff_MyAlerts_Entity_Alert $alert The alert to format.
         *
         * @return string The formatted alert string.
         */
        public function formatAlert(MybbStuff_MyAlerts_Entity_Alert $alert, array $outputAlert)
        {
            $alertContent = $alert->getExtraDetails();
            return $this->lang->sprintf(
                $this->lang->relations_edit,
                $outputAlert['from_user'],
                $alertContent['kind'],
                $alertContent['cat'],
                $outputAlert['dateline']
            );
        }


        /**
         * Init function called before running formatAlert(). Used to load language files and initialize other required
         * resources.
         *
         * @return void
         */
        public function init()
        {
        }

        /**
         * Build a link to an alert's content so that the system can redirect to it.
         *
         * @param MybbStuff_MyAlerts_Entity_Alert $alert The alert to build the link for.
         *
         * @return string The built alert, preferably an absolute link.
         */
        public function buildShowLink(MybbStuff_MyAlerts_Entity_Alert $alert)
        {
            $alertContent = $alert->getExtraDetails();
            return $this->mybb->settings['bburl'] . '/usercp.php?action=relations';
        }
    }

    if (class_exists('MybbStuff_MyAlerts_AlertFormatterManager')) {
        $formatterManager = MybbStuff_MyAlerts_AlertFormatterManager::getInstance();

        if (!$formatterManager) {
            $formatterManager = MybbStuff_MyAlerts_AlertFormatterManager::createInstance($mybb, $lang);
        }

        $formatterManager->registerFormatter(
            new MybbStuff_MyAlerts_Formatter_EditRelationsFormatter($mybb, $lang, 'relations_edit')
        );
    }
}

function relations_user_activity($user_activity)
{
    global $user;
    if (isset($user['location'])) {
        if (my_strpos($user['location'], "usercp.php?action=relations") !== false) {
            $user_activity['activity'] = "relations";
        }
    }

    return $user_activity;
}

function relations_location_activity($plugin_array)
{
    global $db, $mybb, $lang;
    $lang->load('relations');
    if ($plugin_array['user_activity']['activity'] == "relations") {
        $plugin_array['location_name'] = $lang->relations_wiw;
    }
    return $plugin_array;
}
