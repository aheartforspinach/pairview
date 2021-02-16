<?php

// Disallow direct access to this file for security reasons
if(!defined("IN_MYBB"))
{
    die("Direct initialization of this file is not allowed.");
}
/*
 * Hier befinden sich alle Hooks für alle Funktionen.
 */

$plugins->add_hook("misc_start", "misc_pairview");

function pairview_info()
{
    return array(
        "name"			=> "Pärchenübersicht",
        "description"	=> "Hier können alle Pärchenübersicht erstellt werden, so dass alle User eine schnelle Übersicht haben, wer mit wem Angebandelt hat oder dies in Zukunft tun wird.",
        "website"		=> "",
        "author"		=> "Ales",
        "authorsite"	=> "",
        "version"		=> "1.0",
        "guid" 			=> "",
        "codename"		=> "",
        "compatibility" => "*"
    );
}

function pairview_install()
{
    global $db, $mybb;

    //Datenbankerstellung

    if($db->engine=='mysql'||$db->engine=='mysqli')
    {
        $db->query("CREATE TABLE `".TABLE_PREFIX."pairs` (
          `pairId` int(10) NOT NULL auto_increment,
          `typ` varchar(255) NOT NULL,
           `lover1` int(10) NOT NULL,
          `lover2` int(10) NOT NULL,
          `gif1` varchar(255) NOT NULL,
          `gif2` varchar(255) NOT NULL,
          PRIMARY KEY (`pairId`)
        ) ENGINE=MyISAM".$db->build_create_table_collation());

    }

    //Wann wurde die Meldung für einen bestimmten Charakter ausgeblendet? (0 Meldung wird angezeigt, 1 Meldung nicht anzeigen.)
    $db->add_column("users", "pairview_pn", "INT(10) DEFAULT NULL");
    //einstellung

    $setting_group = array(
        'name' => 'pairview',
        'title' => 'Pärchenübersicht',
        'description' => 'Einstellung für die Pärchenübersicht',
        'disporder' => 5, // The order your setting group will display
        'isdefault' => 0
    );

    $gid = $db->insert_query("settinggroups", $setting_group);

    $setting_array = array(
        // A text setting
        'pairview_excluded_groups' => array(
            'title' => 'Ausgeschlossene Gruppen',
            'description' => 'Welche Gruppen sollen nicht mit ausgelesen werden?',
            'optionscode' => 'groupselect',
            'value' => '2,4', // Default
            'disporder' => 1
        ),
        // A text setting
        'pairview_category' => array(
            'title' => 'Kategorien',
            'description' => 'Welche Kategorien soll es geben?',
            'optionscode' => 'text',
            'value' => 'Verheiratet, Verlobt, Beziehung, Affäre, Zukünftig', // Default
            'disporder' => 2
        ),
    );


    foreach($setting_array as $name => $setting)
    {
        $setting['name'] = $name;
        $setting['gid'] = $gid;

        $db->insert_query('settings', $setting);
    }

// Don't forget this!
    rebuild_settings();


    //Templates
    $insert_array = array(
        'title' => 'pairview',
        'template' => $db->escape_string('<html>
<head>
<title>{$mybb->settings[\'bbname\']} - {$lang->pairview}</title>
{$headerinclude}
</head>
<body>
{$header}
<table border="0" cellspacing="{$theme[\'borderwidth\']}" cellpadding="{$theme[\'tablespace\']}" class="tborder">
<tr>
<td class="thead" colspan="2"><div class="headline">{$lang->pairview}</div></td>
</tr>
<tr>
	<td valign="top" width="20%">
		{$pairview_menu}
	</td>
<td class="trow1" align="center" width="80%">
	{$pair_bit}
</td>
</tr>
</table>
{$footer}
</body>
</html>'),
        'sid' => '-1',
        'version' => '',
        'dateline' => TIME_NOW
    );
    $db->insert_query("templates", $insert_array);

    $insert_array = array(
        'title' => 'pairview_add',
        'template' => $db->escape_string('<html>
<head>
<title>{$mybb->settings[\'bbname\']} - {$lang->pairview_add}</title>
{$headerinclude}
</head>
<body>
{$header}
<table border="0" cellspacing="{$theme[\'borderwidth\']}" cellpadding="{$theme[\'tablespace\']}" class="tborder">
<tr>
<td class="thead" colspan="2"><div class="headline">{$lang->pairview_add}</div></td>
</tr>
<tr>
	<td valign="top" width="20%">
		{$pairview_menu}
	</td>
<td class="trow1" align="center" width="80%">
<form action="misc.php?action=pairview_add" id="pair_add"  method="post">
<table width="100%">
	<tr><td class="trow1">	<strong>{$lang->pairview_rela}</strong></td><td class="trow2">
	<select name=\'typ\' id=\'typ\'>
		{$cat_select}
		</select></td></tr>
	<tr><td class="trow1">	<strong>{$lang->pairview_lover1}</strong></td><td class="trow2">
	<select name=\'lover1\' id=\'lover1\'>
{$chara_name}
</select></td></tr>
	<tr><td class="trow1">	
	<strong>{$lang->pairview_lover1_gif}</strong>
		<div class="smalltext">{$lang->pairview_gifsize}</div></td><td class="trow2">
<input type="text" name="gif1" id="gif1" value="" class="textbox" /></td></tr>
	<tr><td class="trow1">	
<strong>{$lang->pairview_lover2}</div></td><td class="trow2">
	<select name=\'lover2\' id=\'lover2\'>
{$chara_name}
</select>	</td></tr>
		<tr><td class="trow1">	
	<strong>{$lang->pairview_lover2_gif}</strong>
				<div class="smalltext">{$lang->pairview_gifsize}</div></td><td class="trow2">
<input type="text" name="gif2" id="gif2" value="" class="textbox" /></td></tr>
	<td align="center" colspan="2" class="trow1"><input type="submit" name="add" value="Pärchen eintragen" id="submit" class="button"></td></tr>
	</table></form>

</td>
</tr>
</table>
{$footer}
</body>
</html>'),
        'sid' => '-1',
        'version' => '',
        'dateline' => TIME_NOW
    );
    $db->insert_query("templates", $insert_array);

    $insert_array = array(
        'title' => 'pairview_bit_charas',
        'template' => $db->escape_string('<tr><td class="trow1">
<div class="lovebox"><table width="600px">
	<tr><td width="100px;"><img src="{$gif1}" class="lovepic" style="margin-right: 30px;"></td>
	<td width="400px;"><div class="lovefacts"><span class="lovefact">{$lang->pairview_lovername}</span> {$lover1}</div>
<div class="lovefacts"><span class="lovefact">{$lang->pairview_loverage}</span> {$age1} Jahre</div>
<div class="lovefacts"> <span class="lovefact">{$lang->pairview_lovers}</span> {$lover2}</div>
<div class="lovefacts"><span class="lovefact">{$lang->pairview_loverage}</span> {$age2} Jahre</div></td>
		<td width="100px;"><img src="{$gif2}" class="lovepic"></td></tr>
	{$option}</table>
</div>
	</td></tr>'),
        'sid' => '-1',
        'version' => '',
        'dateline' => TIME_NOW
    );
    $db->insert_query("templates", $insert_array);

    $insert_array = array(
        'title' => 'pairview_chara_edit',
        'template' => $db->escape_string('<style>.infopop { position: fixed; top: 0; right: 0; bottom: 0; left: 0; background: hsla(0, 0%, 0%, 0.5); z-index: 1; opacity:0; -webkit-transition: .5s ease-in-out; -moz-transition: .5s ease-in-out; transition: .5s ease-in-out; pointer-events: none; } .infopop:target { opacity:1; pointer-events: auto; } .infopop > .pop { width: 300px; position: relative; margin: 10% auto; padding: 25px; z-index: 3; } .closepop { position: absolute; right: -5px; top:-5px; width: 100%; height: 100%; z-index: 2; }</style>
<div id="popinfo$row[pairId]" class="infopop">
  <div class="pop"><form action="misc.php?action=pairview" id="pair_edit"  method="post">
	  <input type="hidden" name="pairId" id="pairId" value="{$row[\'pairId\']}" />
<table border="0" cellspacing="{$theme[\'borderwidth\']}" cellpadding="{$theme[\'tablespace\']}" class="tborder">
	<tr><td class="tcat" colspan="2"><div class=\'headline\'>{$lang->pairview_change}</div></td></tr>
	<tr><td class="trow1">	<strong>{$lang->pairview_rela}</strong></td><td class="trow2">
	<select name=\'typ\' id=\'typ\'>
{$cat_select_edit}
		</select></td></tr>
		<tr><td class="trow1">	
	<strong>{$lang->pairview_lover1}</strong>
			<div class="smalltext">{$lang->pairview_change_lover_desc}</div>
</td><td class="trow2">
<input type="number" name="lover1" id="lover1" value="{$row[\'lover1\']}" class="textbox" /></td></tr>
	<tr><td class="trow1">	
	<strong>{$lang->pairview_lover1_gif}</strong>
		<div class="smalltext">{$lang->pairview_gifsize}</div></td><td class="trow2">
<input type="text" name="gif1" id="gif1" value="{$row[\'gif1\']}" class="textbox" /></td></tr>
			<tr><td class="trow1">	
	<strong>{$lang->pairview_lover2}</strong>
			<div class="smalltext">{$lang->pairview_change_lover_desc}</div>
</td><td class="trow2">
<input type="number" name="lover2" id="lover2" value="{$row[\'lover2\']}" class="textbox" /></td></tr>
		<tr><td class="trow1">	
	<strong>{$lang->pairview_lover2_gif}</strong>
			<div class="smalltext">{$lang->pairview_gifsize}</div></td><td class="trow2">
<input type="text" name="gif2" id="gif2" value="{$row[\'gif2\']}" class="textbox" /></td></tr>
	<td align="center" colspan="2" class="trow1"><input type="submit" name="edit" value="Pärchen editieren" id="submit" class="button"></td></tr>
	</table></form>
		</div><a href="#closepop" class="closepop"></a>
</div>

<a href="#popinfo$row[pairId]">{$lang->pairview_edit}</a>'),
        'sid' => '-1',
        'version' => '',
        'dateline' => TIME_NOW
    );
    $db->insert_query("templates", $insert_array);

    $insert_array = array(
        'title' => 'pairview_chara_bit',
        'template' => $db->escape_string('<table width="100%" style="margin: auto;">
	<tr><td class="thead"><h1>{$typ}</h1></td></tr>
	{$pairs}
</table>'),
        'sid' => '-1',
        'version' => '',
        'dateline' => TIME_NOW
    );
    $db->insert_query("templates", $insert_array);

    $insert_array = array(
        'title' => 'pairview_menu',
        'template' => $db->escape_string('<table width="100%">
	<tr>
		<td class="tcat">
			<strong>
			{$lang->pairview_menu}
			</strong>
		</td></tr>
	<tr><td class="trow1">
	<div class="love_menu">	<i class="fas fa-th-list"></i> <a href="misc.php?action=pairview">{$lang->pairview}</a></div>
		</td></tr>
	<tr><td class="trow2">
	<div class="love_menu"><i class="fas fa-folder-plus"></i>	<a href="misc.php?action=pairview_add">{$lang->pairview_add}</a></div>
		</td></tr>
</table>'),
        'sid' => '-1',
        'version' => '',
        'dateline' => TIME_NOW
    );
    $db->insert_query("templates", $insert_array);


    $insert_array = array(
        'title' => 'pairview_pn_usercp',
        'template' => $db->escape_string('<tr>
<td valign="top"><input type="checkbox" class="checkbox" name="pairview_pn" id="pairview_pn" value="1" {$pn_check} /></td>
<td><span class="smalltext"><label for="pairview_pn">{$lang->pairviewpn}</label></span></td>
</tr>
<tr>
<td valign="top"><input type="checkbox" class="checkbox" name="pairview_pn_all" id="pairview_pn_all" value="1" /></td>
<td><span class="smalltext"><label for="pairview_pn_all">{$lang->pairviewpn_all}</label></span></td>
</tr>'),
        'sid' => '-1',
        'version' => '',
        'dateline' => TIME_NOW
    );
    $db->insert_query("templates", $insert_array);

    //CSS einfügen
    $css = array(
        'name' => 'pairview.css',
        'tid' => 1,
        'attachedto' => '',
        "stylesheet" =>    '.lovebox{
width: 500px;
margin: 5px auto;	
	padding: 4px;
}

.lovepic{
border:3px solid #0066a2;
border-radius:20%;
	height: 100px;
	width: 90px;
}

.lovefact{
color:#0f0f0f;
font-size: 12px;
text-decoration: none;
text-transform: uppercase;
font-weight: bold;
}


.lovefacts{
font-size: 12px;
font-weight: bold;
border-bottom: 1px solid #0f0f0f;
text-transform: uppercase;
}

.love_menu{
color:#0f0f0f;
font-size: 12px;
text-decoration: none;
text-transform: uppercase;
font-weight: bold;
}

.love_menu a{
color:#0f0f0f;
font-size: 12px;
text-decoration: none;
text-transform: uppercase;
font-weight: bold;
}

        ',
        'cachefile' => $db->escape_string(str_replace('/', '', 'pairview.css')),
        'lastmodified' => time()
    );

    require_once MYBB_ADMIN_DIR . "inc/functions_themes.php";

    $sid = $db->insert_query("themestylesheets", $css);
    $db->update_query("themestylesheets", array("cachefile" => "css.php?stylesheet=" . $sid), "sid = '" . $sid . "'", 1);

    $tids = $db->simple_select("themes", "tid");
    while ($theme = $db->fetch_array($tids)) {
        update_theme_stylesheet_list($theme['tid']);
    }
}

function pairview_is_installed()
{
    global $db;
    if($db->table_exists("pairs"))
    {
        return true;
    }
    return false;
}

function pairview_uninstall()
{
    global $db;

    $db->delete_query('settings', "name IN ('excluded_groups')");
    $db->delete_query('settinggroups', "name = 'pairview'");
// Don't forget this
    rebuild_settings();

    if($db->table_exists("pairs"))
    {
        $db->drop_table("pairs");
    }
    rebuild_settings();

    $db->delete_query("templates", "title LIKE '%pairview%'");

    require_once MYBB_ADMIN_DIR."inc/functions_themes.php";
    $db->delete_query("themestylesheets", "name = 'pairview.css'");
    $query = $db->simple_select("themes", "tid");
    while($theme = $db->fetch_array($query)) {
        update_theme_stylesheet_list($theme['tid']);
    }

    rebuild_settings();
}

function pairview_activate()
{
    require MYBB_ROOT."/inc/adminfunctions_templates.php";
    find_replace_templatesets("usercp_options", "#".preg_quote('{$calendaroptions}')."#i", '{$pairview_pn}
{$calendaroptions}');
}

function pairview_deactivate()
{
    require MYBB_ROOT."/inc/adminfunctions_templates.php";
    find_replace_templatesets("usercp_options", "#".preg_quote('{$pairview_pn}')."#i", '', 0);
}


/*
 * Hier wird die Seite generiert, in der die Pärchen eingefügt werden und ausgelesen werden können.
 */

function misc_pairview()
{

    global $mybb, $templates, $lang, $header, $headerinclude, $footer, $pairview_menu, $db, $chara_name, $page, $lover1, $lover2, $option, $edit, $chara_lover, $cat_select, $cat_select_edit, $selected,   $pair_type;

    //PM Handler, dass auch die Privaten Nachrichten rausgehen.
    require_once MYBB_ROOT . "inc/datahandlers/pm.php";
    $pmhandler = new PMDataHandler();

    //Die Sprachdatei
    $lang->load('pairview');

    //Navigation bauen :D

    switch($mybb->input['action'])
    {
        case "pairview_add":
            add_breadcrumb($lang->pairview_add);
            break;
        case "pairview":
            add_breadcrumb($lang->pairview);
            break;
    }


    //Menü
    eval("\$pairview_menu = \"" . $templates->get("pairview_menu") . "\";");

    /*
     * Paare hinzugefügen
     */

    if ($mybb->get_input('action') == 'pairview_add') {
        //Gäste sollen natürlich keine Pärchen hinzufügen können.
        if ($mybb->user['uid'] == 0) {
            error_no_permission ();
        }


        $pair_cat_setting = $mybb->settings['pairview_category'];
        $pair_cat = explode (", ", $pair_cat_setting);
        foreach ($pair_cat as $cat) {
            $cat_select .= "<option value='{$cat}'>{$cat}</option>";
        }


        $charaktere = $db->query ("SELECT uid, username
    FROM " . TABLE_PREFIX . "users
    WHERE usergroup NOT IN ('" . str_replace (',', '\',\'', $mybb->settings['pairview_excluded_groups']) . "')
    ORDER BY username
    ");

        while ($pair = $db->fetch_array ($charaktere)) {

            $chara_name .= "<option value='{$pair['uid']}'>{$pair['username']}</option>";
        }


        if ($mybb->user['uid'] == 0) {
            //  error_no_permission();
        } elseif ($_POST['add']) {
            $typ = $db->escape_string ($_POST['typ']);
            $lover1 = $db->escape_string ($_POST['lover1']);
            $gif1 = $db->escape_string ($_POST['gif1']);
            $lover2 = $db->escape_string ($_POST['lover2']);
            $gif2 = $db->escape_string ($_POST['gif2']);

            // Eine PN Versenden, um den Gegenpart zu informieren
            $query1 = $db->query ("SELECT username
                    from " . TABLE_PREFIX . "users
                    where uid = " . $lover1 . "
                    ");

            $love_name1 = $db->fetch_array ($query1);
            $lover_name1 = $love_name1['username'];

            $query2 = $db->query ("SELECT username
                    from " . TABLE_PREFIX . "users
                    where uid = " . $lover2 . "
                    ");
            $love_name2 = $db->fetch_array ($query2);
            $lover_name2 = $love_name2['username'];

            if ($mybb->user['pairview_pn'] == 0) {
                if ($lover1 == $mybb->user['uid']) {
                    $pm_change = array(
                        "subject" => "Unser (geplantes) Pairing wurde eingetagen",
                        "message" => "Ich habe unser Pairing in die Übersicht eingetragen. <br /> <b>{$lover_name1}</b> und <b>{$lover_name2}</b> in der Kategorie {$typ}. Ich hoffe, es ist für dich in Ordnung.",
                        //From: Wer schreibt die PN
                        "fromid" => $lover1,
                        //to: an wen geht die pn
                        "toid" => $lover2
                    );
                    // $pmhandler->admin_override = true;
                    $pmhandler->set_data ($pm_change);
                    if (!$pmhandler->validate_pm ())
                        return false;
                    else {
                        $pmhandler->insert_pm ();
                    }
                } elseif ($lover2 == $mybb->user['uid']) {
                    $pm_change = array(
                        "subject" => "Unser (geplantes) Pairing wurde eingetagen",
                        "message" => "Ich habe unser Pairing in die Übersicht eingetragen. <br /> <b>{$lover_name1}</b> und <b>{$lover_name2}</b> in der Kategorie {$typ}. Ich hoffe, es ist für dich in Ordnung.",
                        //From: Wer schreibt die PN
                        "fromid" => $lover2,
                        //to: an wen geht die pn
                        "toid" => $lover1
                    );
                    // $pmhandler->admin_override = true;
                    $pmhandler->set_data ($pm_change);
                    if (!$pmhandler->validate_pm ())
                        return false;
                    else {
                        $pmhandler->insert_pm ();
                    }

                } else {

                    $lover_array = array(
                        "lover1" => $lover1,
                        "lover2" => $lover2
                    );

                    foreach ($lover_array as $lover => $lover_uid) {

                        $pm_change = array(
                            "subject" => "Das (geplante) Pairing wurde eingetagen",
                            "message" => "Ich habe ein Pairing in die Übersicht für dich und deinem Pairingpartner eingetragen. <br /> Es handelt sich um die Charaktere <b>{$lover_name1}</b> und <b>{$lover_name2}</b> in der Kategorie <i>{$typ}</i>. Ich hoffe, es ist für dich in Ordnung. <br /> Du kannst es dir <a href='misc.php?action=pairview'>hier</a> ansehen.",
                            //From: Wer schreibt die PN
                            "fromid" => $mybb->user['uid'],
                            //to: an wen geht die pn
                            "toid" => $lover_uid
                        );
                        // $pmhandler->admin_override = true;
                        $pmhandler->set_data ($pm_change);
                        if (!$pmhandler->validate_pm ())
                            return false;
                        else {
                            $pmhandler->insert_pm ();
                        }
                    }

                }

                $new_pair = array(
                    "typ" => $typ,
                    "lover1" => $lover1,
                    "gif1" => $gif1,
                    "lover2" => $lover2,
                    "gif2" => $gif2,
                );

                $db->insert_query ("pairs", $new_pair);
                redirect ("misc.php?action=pairview_add");


            }


            $new_pair = array(
                "typ" => $typ,
                "lover1" => $lover1,
                "gif1" => $gif1,
                "lover2" => $lover2,
                "gif2" => $gif2,
            );

            $db->insert_query ("pairs", $new_pair);
            redirect ("misc.php?action=pairview_add");
        }
        // Using the misc_help template for the page wrapper
        eval("\$page = \"" . $templates->get ("pairview_add") . "\";");
        output_page ($page);
    }

/*
 * Paare auslesen
 */
if ($mybb->get_input('action') == 'pairview') {

    $pair_cat_setting = $mybb->settings['pairview_category'];
    $type = explode(", ", $pair_cat_setting);

    foreach ($type as $typ) {
        $pairs = '';
        $select = $db->query("SELECT *
            FROM " . TABLE_PREFIX . "pairs p
            left join " . TABLE_PREFIX . "users u
            on (u.uid = p.lover1)
            where typ LIKE '%$typ%'
            order by username ASC
            ");
        while ($row = $db->fetch_array($select)) {

            $pair_type = $row['typ'];
            if ($mybb->usergroup['canmodcp'] == 1 OR $row['lover1'] == $mybb->user['uid'] OR  $row['lover2'] == $mybb->user['uid']) {
                $cat_select_edit = "";

                foreach ($type as $cat){


                    if($cat == $pair_type){
                        $cat_select_edit .= "<option selected>{$cat}</option>";
                    } else{
                        $cat_select_edit .= "<option>{$cat}</option>";
                    }


                }


                eval("\$edit = \"" . $templates->get("pairview_chara_edit") . "\";");
                $option = "<tr><td colspan='3' align='center'><a href='misc.php?action=pairview&delete=$row[pairId]'>{$lang->pairview_delete}</a> # {$edit}</td></tr>";
            }
            /*
             * Zieh mal alle Informationen für den ersten Charakter aus der Usertabelle
             */
            $lover1_uid = $row['lover1'];
            $lover1_select = $db->query("select *
              from " . TABLE_PREFIX . "users
              where uid = '$lover1_uid'
              ");
            $lover1_info = $db->fetch_array($lover1_select);

            $username = format_name($lover1_info['username'], $lover1_info['usergroup'], $lover1_info['displaygroup']);
            $lover1 = build_profile_link($username, $lover1_info['uid']);
            $gif1 = $row['gif1'];
            if ($lover1_info['birthday']) {
                $age1 = intval(date('Y', strtotime("1." . $mybb->settings['minica_month'] . "." . $mybb->settings['minica_year'] . "") - strtotime($lover1_info['birthday']))) - 1970;
            } else {
                $age1 = "k/A";
            }
            /*
             * Zieh mal alle Informationen für den zweiten Charakter aus der Usertabelle
             */
            $lover2_uid = $row['lover2'];
            $lover2_select = $db->query("select *
              from " . TABLE_PREFIX . "users
              where uid = '$lover2_uid'
              ");
            $lover2_info = $db->fetch_array($lover2_select);

            $username = format_name($lover2_info['username'], $lover2_info['usergroup'], $lover2_info['displaygroup']);
            $lover2 = build_profile_link($username, $lover2_info['uid']);
            if ($lover2_info['birthday']) {
                $age2 = intval(date('Y', strtotime("1." . $mybb->settings['minica_month'] . "." . $mybb->settings['minica_year'] . "") - strtotime($lover2_info['birthday']))) - 1970;
            } else {
                $age2 = "k/A";
            }
            $gif2 = $row['gif2'];

            eval("\$pairs .= \"" . $templates->get("pairview_bit_charas") . "\";");


        }

        eval("\$pair_bit .= \"" . $templates->get("pairview_chara_bit") . "\";");
    }


    $delete = $mybb->input['delete'];
    if ($delete) {
        $db->delete_query("pairs", "pairId = '$delete'");
        redirect("misc.php?action=pairview");
    }

    if (isset($mybb->input['edit'])) {
        $pairId = $mybb->input['pairId'];
        $typ = $mybb->input['typ'];
        $lover1 = (int)$mybb->input['lover1'];
        $lover2 = (int)$mybb->input['lover2'];
        $gif1 = $mybb->input['gif1'];
        $gif2 = $mybb->input['gif2'];

        $edit_pair = array(
            "typ" => $typ,
            "lover1" => $lover1,
            "lover2" => $lover2,
            "gif1" => $gif1,
            "gif2" => $gif2
        );


        $db->update_query("pairs", $edit_pair, "pairId='{$pairId}'");
        redirect("misc.php?action=pairview");
    }

    // Using the misc_help template for the page wrapper
    eval("\$page = \"" . $templates->get("pairview") . "\";");
    output_page($page);

}

//wer ist wo
$plugins->add_hook('fetch_wol_activity_end', 'pairview_user_activity');
$plugins->add_hook('build_friendly_wol_location_end', 'pairview_location_activity');

function pairview_user_activity($user_activity){
    global $user;

    if(my_strpos($user['location'], "misc.php?action=pairview") !== false) {
        $user_activity['activity'] = "pairview";
    }
    if(my_strpos($user['location'], "misc.php?action=pairview_add") !== false) {
        $user_activity['activity'] = "pairview_add";
    }

    return $user_activity;
}

function pairview_location_activity($plugin_array) {
    global $db, $mybb, $lang;

    if($plugin_array['user_activity']['activity'] == "pairview")
    {
        $plugin_array['location_name'] = "Schaut sich die <b><a href='misc.php?action=pairview'>Pärchenübersicht</a></b> an.";
    }

    if($plugin_array['user_activity']['activity'] == "pairview_add")
    {
        $plugin_array['location_name'] = "Fügt ein weiteres Pärchen der <b><a href='misc.php?action=pairview_add'>Pärchenübersicht</a></b> hinzu.";
    }

    return $plugin_array;
}

/**
 * Was passiert wenn ein User gelöscht wird
 * Relas bei anderen zu npc umtragen
 * die relas des users löschen
 */
$plugins->add_hook("admin_user_users_delete_commit_end", "pairview_user_delete");
function pairview_user_delete()
{
    global $db, $cache, $mybb, $user, $profile_fields;
    $db->delete_query('pairs', "lover1 = " . (int)$user['uid'] . " OR lover2 = " . (int)$user['uid'] . " ");
}

$plugins->add_hook('usercp_options_start', 'pv_edit_options');
function pv_edit_options() {
    global $db, $mybb, $templates, $pn_check, $pairview_pn, $pn_check_all, $lang ;
    //Die Sprachdatei
    $lang->load('pairview');

    $pv_pn = $mybb->user['pairview_pn'];
    $pv_pn_all = $mybb->user['pairview_pn_all'];
    $pn_check = '';
    if($pv_pn == 1){
        $pn_check = 'checked="checked"';
    }
    if($pv_pn_all == 1){
        $pn_check_all = 'checked="checked"';
    }

    eval("\$pairview_pn .=\"".$templates->get("pairview_pn_usercp")."\";");
}

//User CP: änderungen im ucp speichern
//bei Wunsch des Users, Einstellung für alle Charaktere übernehmen
$plugins->add_hook('usercp_do_options_start', 'pv_edit_options_do');
function pv_edit_options_do() {
    global $mybb, $db, $templates;
    //Was hat der User eingestellt?
    $pv_pn = $mybb->get_input('pairview_pn', MyBB::INPUT_INT);
    $pv_pn_all = $mybb->input['pairview_pn_all'];

    //Wer ist online, Wer ist Hauptaccount.
    $this_user = intval($mybb->user['uid']);
    $as_uid = intval($mybb->user['as_uid']);
//Soll für alle Charaktere übernommen werden oder nicht?
    if($pv_pn_all == 1) {
        //Ja, alle raussuchen
        if($as_uid == 0) {
            $id = intval($mybb->user['uid']);
        } else {
            $id = intval($mybb->user['as_uid']);
        }
        //speichern
        $db->query("UPDATE ".TABLE_PREFIX."users SET pairview_pn=".$pv_pn." WHERE uid=".$id." OR as_uid=".$id."");

    } else {
        //nur für aktuellen Charakter speichern
        $db->query("UPDATE ".TABLE_PREFIX."users SET pairview_pn=".$pv_pn." WHERE uid=".$this_user."");
    }
}
