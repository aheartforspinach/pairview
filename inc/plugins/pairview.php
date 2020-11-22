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

}

function pairview_activate()
{
    require MYBB_ROOT."/inc/adminfunctions_templates.php";
}

function pairview_deactivate()
{
    require MYBB_ROOT."/inc/adminfunctions_templates.php";
}


/*
 * Hier wird die Seite generiert, in der die Pärchen eingefügt werden und ausgelesen werden können.
 */

function misc_pairview()
{

    global $mybb, $templates, $lang, $header, $headerinclude, $footer, $pairview_menu, $db, $chara_name, $page, $lover1, $lover2, $option, $edit, $chara_lover, $cat_select;

    //Übernehme die gespeicherte Einstellung, welche Gruppen NICHT mit ausgelesen werden soll
    $excluded_groups = $mybb->settings['excluded_groups'];
    require_once MYBB_ROOT . "inc/datahandlers/pm.php";
    $pmhandler = new PMDataHandler();
    //Menü
    eval("\$pairview_menu = \"" . $templates->get("pairview_menu") . "\";");

    /*
     * Paare hinzugefügen
     */


    if ($mybb->get_input('action') == 'pairview_add') {
        if ($mybb->user['uid'] == 0) {
            echo("Fehler");
            error_no_permission();
        }

        add_breadcrumb('Pärchenübersicht', "misc.php?action=pairview_add");


        $pair_cat_setting = $mybb->settings['pairview_category'];
        $pair_cat = explode(", ", $pair_cat_setting);
        foreach ($pair_cat as $cat){
            $cat_select .= "<option value='{$cat}'>{$cat}</option>";
        }


        $charaktere = $db->query("SELECT uid, username
    FROM " . TABLE_PREFIX . "users
    WHERE usergroup != '2'
    and not usergroup = '4'
    and not usergroup = '26'
    and not usergroup = '28'
    and additionalgroups not like '54'
    ORDER BY username
    ");

        while ($pair = $db->fetch_array($charaktere)) {

            $chara_name .= "<option value='{$pair['uid']}'>{$pair['username']}</option>";
        }


        if ($mybb->user['uid'] == 0) {
            //  error_no_permission();
        } elseif ($_POST['add']) {
            $typ = $db->escape_string($_POST['typ']);
            $lover1 = $db->escape_string($_POST['lover1']);
            $gif1 = $db->escape_string($_POST['gif1']);
            $lover2 = $db->escape_string($_POST['lover2']);
            $gif2 = $db->escape_string($_POST['gif2']);

            // Eine PN Versenden, um den Gegenpart zu informieren
            $query1 = $db->query("SELECT username
                    from ".TABLE_PREFIX."users
                    where uid = ".$lover1."
                    ");

            $love_name1 = $db->fetch_array($query1);
            $lover_name1 = $love_name1['username'];

            $query2 = $db->query("SELECT username
                    from ".TABLE_PREFIX."users
                    where uid = ".$lover2."
                    ");
            $love_name2 = $db->fetch_array($query2);
            $lover_name2 = $love_name2['username'];

            if($lover1 == $mybb->user['uid']) {
                $pm_change = array(
                    "subject" => "Unser (geplantes) Pairing wurde eingetagen",
                    "message" => "Ich habe unser Pairing in die Übersicht eingetragen. <br /> <b>{$lover_name1}</b> und <b>{$lover_name2}</b> in der Kategorie {$typ}. Ich hoffe, es ist für dich in Ordnung.",
                    //From: Wer schreibt die PN
                    "fromid" => $lover1,
                    //to: an wen geht die pn
                    "toid" => $lover2
                );
                // $pmhandler->admin_override = true;
                $pmhandler->set_data($pm_change);
                if (!$pmhandler->validate_pm())
                    return false;
                else {
                    $pmhandler->insert_pm();
                }
            }elseif($lover2 == $mybb->user['uid']){
                $pm_change = array(
                    "subject" => "Unser (geplantes) Pairing wurde eingetagen",
                    "message" => "Ich habe unser Pairing in die Übersicht eingetragen. <br /> <b>{$lover_name1}</b> und <b>{$lover_name2}</b> in der Kategorie {$typ}. Ich hoffe, es ist für dich in Ordnung.",
                    //From: Wer schreibt die PN
                    "fromid" => $lover2,
                    //to: an wen geht die pn
                    "toid" => $lover1
                );
                // $pmhandler->admin_override = true;
                $pmhandler->set_data($pm_change);
                if (!$pmhandler->validate_pm())
                    return false;
                else {
                    $pmhandler->insert_pm();
                }
            } else{

                $lover_array = array(
                    "lover1" => $lover1,
                    "lover2" => $lover2
                );

                foreach ($lover_array as $lover => $lover_uid){

                    $pm_change = array(
                        "subject" => "Das (geplante) Pairing wurde eingetagen",
                        "message" => "Ich habe ein Pairing in die Übersicht für dich und deinem Pairingpartner eingetragen. <br /> Es handelt sich um die Charaktere <b>{$lover_name1}</b> und <b>{$lover_name2}</b> in der Kategorie <i>{$typ}</i>. Ich hoffe, es ist für dich in Ordnung. <br /> Du kannst es dir <a href='misc.php?action=pairview'>hier</a> ansehen.",
                        //From: Wer schreibt die PN
                        "fromid" => $mybb->user['uid'],
                        //to: an wen geht die pn
                        "toid" => $lover_uid
                    );
                    // $pmhandler->admin_override = true;
                    $pmhandler->set_data($pm_change);
                    if (!$pmhandler->validate_pm())
                        return false;
                    else {
                        $pmhandler->insert_pm();
                    }
                }
                $new_pair = array(
                    "typ" => $typ,
                    "lover1" => $lover1,
                    "gif1" => $gif1,
                    "lover2" => $lover2,
                    "gif2" => $gif2,
                );

                $db->insert_query("pairs", $new_pair);
                redirect("misc.php?action=pairview_add");


            }


            $new_pair = array(
                "typ" => $typ,
                "lover1" => $lover1,
                "gif1" => $gif1,
                "lover2" => $lover2,
                "gif2" => $gif2,
            );

            $db->insert_query("pairs", $new_pair);
            redirect("misc.php?action=pairview_add");
        }
        // Using the misc_help template for the page wrapper
        eval("\$page = \"" . $templates->get("pairview_add") . "\";");
        output_page($page);
    }

    /*
     * Paare auslesen
     */
    if ($mybb->get_input('action') == 'pairview') {

        add_breadcrumb('Pärchenübersicht', "misc.php?action=pairview");

       /* $type = array("Ehepaar" => "Ehepaar",
            "Verlobung" => "Verlobung",
            "Paare" => "Paare",
            "Affäre" => "Affäre",
            "Zukünftiges" => "Zukünftiges");*/

        $pair_cat_setting = $mybb->settings['pairview_category'];
        $type = explode(", ", $pair_cat_setting);

        foreach ($type as $typ) {
            $pairs = '';
            $select = $db->query("SELECT *
            FROM " . TABLE_PREFIX . "pairs
            where typ LIKE '%$typ%'
            ");
            while ($row = $db->fetch_array($select)) {

                if ($mybb->usergroup['canmodcp'] == 1) {

                    eval("\$edit = \"" . $templates->get("pairview_chara_edit") . "\";");
                    $option = "<tr><td colspan='3' align='center'><a href='misc.php?action=pairview&delete=$row[pairId]'>Löschen</a> # {$edit}</td></tr>";
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

            if ($typ == 'Verlobung') {
                $typ = "Reinblüter Verlobung";
            }

            if ($typ == 'Zukünftiges') {
                $typ = "Zukünftige Paare";
            }


            if ($mybb->usergroup['canmodcp'] == 1) {

                eval("\$edit = \"" . $templates->get("pairview_chara_edit") . "\";");
                $option = "<tr><td colspan='3' align='center'><a href='misc.php?action=pairview&delete=$row[pairId]'>Löschen</a> # {$edit}</td></tr>";
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
            $gif1 = $mybb->input['gif1'];
            $gif2 = $mybb->input['gif2'];

            $edit_pair = array(
                "typ" => $typ,
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
