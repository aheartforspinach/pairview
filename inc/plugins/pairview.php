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
        'excluded_groups' => array(
            'title' => 'Ausgeschlossene Gruppen',
            'description' => 'Welche Gruppen sollen nicht mit ausgelesen werden?',
            'optionscode' => 'text',
            'value' => '2,4,26,28,54', // Default
            'disporder' => 1
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

function misc_pairview(){

    global $mybb, $templates, $lang, $header, $headerinclude, $footer,$pairview_menu, $db, $chara_name, $page, $lover1, $lover2, $option, $edit, $chara_lover;

    //Übernehme die gespeicherte Einstellung, welche Gruppen NICHT mit ausgelesen werden soll
    $excluded_groups = $mybb->settings['excluded_groups'];

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

            $charaktere = $db->query("SELECT uid, username
    FROM " . TABLE_PREFIX . "users
    WHERE usergroup NOT IN ('$excluded_groups')
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

            $type = array("Ehepaar" => "Ehepaar",
                "Verlobung" => "Verlobung",
                "Paare" => "Paare",
                "Zukünftiges" => "Zukünftiges");
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