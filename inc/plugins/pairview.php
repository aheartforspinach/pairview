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
if(class_exists('MybbStuff_MyAlerts_AlertTypeManager')) {
    $plugins->add_hook("global_start", "pairview_alerts");
}

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
	<tr><td class="trow1">	<div class="profilucp">{$lang->pairview_rela}</div></td><td class="trow2">
	<select name=\'typ\' id=\'typ\'>
		{$cat_select}
		</select></td></tr>
	<tr><td class="trow1">	<div class="profilucp">{$lang->pairview_lover1}</div></td><td class="trow2">
		<input type="text" name="lover1" id="lover1" value="{$lover1}" class="textbox" size="40" maxlength="1155" v style="min-width: 150px; max-width: 100%;"   />
		</td></tr>
	<tr><td class="trow1">	
	<div class="profilucp">{$lang->pairview_lover1_gif}</div>
		<div class="smalltext">{$lang->pairview_gifsize}</div></td><td class="trow2">
<input type="text" name="gif1" id="gif1" value="" class="textbox" /></td></tr>
	<tr><td class="trow1">	
<div class="profilucp">{$lang->pairview_lover2}</div></td><td class="trow2">
		<input type="text" name="lover2" id="lover2" value="{$lover2}" class="textbox" size="40" maxlength="1155" v style="min-width: 150px; max-width: 100%;"   />	</td></tr>
		<tr><td class="trow1">	
	<div class="profilucp">{$lang->pairview_lover2_gif}</div>
				<div class="smalltext">{$lang->pairview_gifsize}</div></td><td class="trow2">
<input type="text" name="gif2" id="gif2" value="" class="textbox" /></td></tr>
	<td align="center" colspan="2" class="trow1"><input type="submit" name="add" value="Pärchen eintragen" id="submit" class="button"></td></tr>
	</table></form>

</td>
</tr>
</table>
{$footer}
</body>
</html>

<link rel="stylesheet" href="{$mybb->asset_url}/jscripts/select2/select2.css?ver=1807">
<script type="text/javascript" src="{$mybb->asset_url}/jscripts/select2/select2.min.js?ver=1806"></script>
<script type="text/javascript">
<!--
if(use_xmlhttprequest == "1")
{
    MyBB.select2();
    $("#lover1").select2({
        placeholder: "{$lang->search_user}",
        minimumInputLength: 2,
        maximumSelectionSize: \'\',
        multiple: true,
        ajax: { // instead of writing the function to execute the request we use Select2\'s convenient helper
            url: "xmlhttp.php?action=get_users",
            dataType: \'json\',
            data: function (term, page) {
                return {
                    query: term, // search term
                };
            },
            results: function (data, page) { // parse the results into the format expected by Select2.
                // since we are using custom formatting functions we do not need to alter remote JSON data
                return {results: data};
            }
        },
        initSelection: function(element, callback) {
            var query = $(element).val();
            if (query !== "") {
                var newqueries = [];
                exp_queries = query.split(",");
                $.each(exp_queries, function(index, value ){
                    if(value.replace(/\s/g, \'\') != "")
                    {
                        var newquery = {
                            id: value.replace(/,\s?/g, ","),
                            text: value.replace(/,\s?/g, ",")
                        };
                        newqueries.push(newquery);
                    }
                });
                callback(newqueries);
            }
        }
    })
}
// -->
</script>

<script type="text/javascript">
<!--
if(use_xmlhttprequest == "1")
{
    MyBB.select2();
    $("#lover2").select2({
        placeholder: "{$lang->search_user}",
        minimumInputLength: 2,
        maximumSelectionSize: \'\',
        multiple: true,
        ajax: { // instead of writing the function to execute the request we use Select2\'s convenient helper
            url: "xmlhttp.php?action=get_users",
            dataType: \'json\',
            data: function (term, page) {
                return {
                    query: term, // search term
                };
            },
            results: function (data, page) { // parse the results into the format expected by Select2.
                // since we are using custom formatting functions we do not need to alter remote JSON data
                return {results: data};
            }
        },
        initSelection: function(element, callback) {
            var query = $(element).val();
            if (query !== "") {
                var newqueries = [];
                exp_queries = query.split(",");
                $.each(exp_queries, function(index, value ){
                    if(value.replace(/\s/g, \'\') != "")
                    {
                        var newquery = {
                            id: value.replace(/,\s?/g, ","),
                            text: value.replace(/,\s?/g, ",")
                        };
                        newqueries.push(newquery);
                    }
                });
                callback(newqueries);
            }
        }
    })
}
// -->
</script>'),
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
<div class="lovefacts"> <span class="lovefact">{$lang->pairview_lovers}</span> {$lover2}</div>
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
        'template' => $db->escape_string('<html>
<head>
<title>{$mybb->settings[\'bbname\']} - {$lang->pairview_change}</title>
{$headerinclude}
</head>
<body>
{$header}
<table border="0" cellspacing="{$theme[\'borderwidth\']}" cellpadding="{$theme[\'tablespace\']}" class="tborder">
<tr>
<td class="thead" colspan="2"><div class="headline">{$lang->pairview_change}</div></td>
</tr>
<tr>
	<td>
  <div class="pop"><form action="misc.php?action=pairview_edit" id="pair_edit"  method="post">
	  <input type="hidden" name="pairId" id="pairId" value="{$row[\'pairId\']}" />
<table border="0" cellspacing="{$theme[\'borderwidth\']}" cellpadding="{$theme[\'tablespace\']}" class="tborder">
	<tr><td class="trow1">	<div class="profilucp">{$lang->pairview_rela}</div></td><td class="trow2">
	<select name=\'typ\' id=\'typ\'>
		<option  selected value="{$row[\'typ\']}">{$row[\'typ\']}</option>
{$cat_select}
		</select></td></tr>
			<tr><td class="trow1">	
	<div class="profilucp">Partner 1</div>
</td><td class="trow2">
<input type="text" name="lover1" id="lover1" value="{$row[\'lover1\']}" class="textbox" size="40" maxlength="1155" v style="min-width: 150px; max-width: 100%;"  /></td></tr>
	<tr><td class="trow1">	
	<div class="profilucp">Gif für Partner 1</div>
		<div class="smalltext">Größe 90x100, Typ ist eine Gif</div></td><td class="trow2">
<input type="text" name="gif1" id="gif1" value="{$row[\'gif1\']}" class="textbox" /></td></tr>
			<tr><td class="trow1">	
	<div class="profilucp">Partner 1</div>
</td><td class="trow2">
<input type="text" name="lover2" id="lover2" value="{$row[\'lover2\']}" class="textbox" size="40" maxlength="1155" v style="min-width: 150px; max-width: 100%;"  /></td></tr>
		<tr><td class="trow1">	
	<div class="profilucp">Gif für Partner 2</div>
			<div class="smalltext">Größe 90x100, Typ ist eine Gif</div></td><td class="trow2">
<input type="text" name="gif2" id="gif2" value="{$row[\'gif2\']}" class="textbox" /></td></tr>
	<td align="center" colspan="2" class="trow1"><input type="submit" name="edit" value="Pärchen editieren" id="submit" class="button"></td></tr>
	</table></form>
</td>
</tr>
</table>
{$footer}
</body>
</html>


<link rel="stylesheet" href="{$mybb->asset_url}/jscripts/select2/select2.css?ver=1807">
<script type="text/javascript" src="{$mybb->asset_url}/jscripts/select2/select2.min.js?ver=1806"></script>
<script type="text/javascript">
<!--
if(use_xmlhttprequest == "1")
{
    MyBB.select2();
    $("#lover1").select2({
        placeholder: "{$lang->search_user}",
        minimumInputLength: 2,
        maximumSelectionSize: \'\',
        multiple: true,
        ajax: { // instead of writing the function to execute the request we use Select2\'s convenient helper
            url: "xmlhttp.php?action=get_users",
            dataType: \'json\',
            data: function (term, page) {
                return {
                    query: term, // search term
                };
            },
            results: function (data, page) { // parse the results into the format expected by Select2.
                // since we are using custom formatting functions we do not need to alter remote JSON data
                return {results: data};
            }
        },
        initSelection: function(element, callback) {
            var query = $(element).val();
            if (query !== "") {
                var newqueries = [];
                exp_queries = query.split(",");
                $.each(exp_queries, function(index, value ){
                    if(value.replace(/\s/g, \'\') != "")
                    {
                        var newquery = {
                            id: value.replace(/,\s?/g, ","),
                            text: value.replace(/,\s?/g, ",")
                        };
                        newqueries.push(newquery);
                    }
                });
                callback(newqueries);
            }
        }
    })
}
// -->
</script>

<script type="text/javascript">
<!--
if(use_xmlhttprequest == "1")
{
    MyBB.select2();
    $("#lover2").select2({
        placeholder: "{$lang->search_user}",
        minimumInputLength: 2,
        maximumSelectionSize: \'\',
        multiple: true,
        ajax: { // instead of writing the function to execute the request we use Select2\'s convenient helper
            url: "xmlhttp.php?action=get_users",
            dataType: \'json\',
            data: function (term, page) {
                return {
                    query: term, // search term
                };
            },
            results: function (data, page) { // parse the results into the format expected by Select2.
                // since we are using custom formatting functions we do not need to alter remote JSON data
                return {results: data};
            }
        },
        initSelection: function(element, callback) {
            var query = $(element).val();
            if (query !== "") {
                var newqueries = [];
                exp_queries = query.split(",");
                $.each(exp_queries, function(index, value ){
                    if(value.replace(/\s/g, \'\') != "")
                    {
                        var newquery = {
                            id: value.replace(/,\s?/g, ","),
                            text: value.replace(/,\s?/g, ",")
                        };
                        newqueries.push(newquery);
                    }
                });
                callback(newqueries);
            }
        }
    })
}
// -->
</script>'),
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
    global $db, $cache;
    //Alertseinstellungen
    if(class_exists('MybbStuff_MyAlerts_AlertTypeManager')) {
        $alertTypeManager = MybbStuff_MyAlerts_AlertTypeManager::getInstance();

        if (!$alertTypeManager) {
            $alertTypeManager = MybbStuff_MyAlerts_AlertTypeManager::createInstance($db, $cache);
        }

        $alertType = new MybbStuff_MyAlerts_Entity_AlertType();
        $alertType->setCode('pairview_addpair'); // The codename for your alert type. Can be any unique string.
        $alertType->setEnabled(true);
        $alertType->setCanBeUserDisabled(true);

        $alertTypeManager->add($alertType);
    }

    require MYBB_ROOT."/inc/adminfunctions_templates.php";

}

function pairview_deactivate()
{
    global $db, $cache;

    //Alertseinstellungen
    if (class_exists('MybbStuff_MyAlerts_AlertTypeManager')) {
        $alertTypeManager = MybbStuff_MyAlerts_AlertTypeManager::getInstance();

        if (!$alertTypeManager) {
            $alertTypeManager = MybbStuff_MyAlerts_AlertTypeManager::createInstance($db, $cache);
        }

        $alertTypeManager->deleteByCode('pairview_addpair');

    }

    require MYBB_ROOT."/inc/adminfunctions_templates.php";

}


/*
 * Hier wird die Seite generiert, in der die Pärchen eingefügt werden und ausgelesen werden können.
 */

function misc_pairview()
{

    global $mybb, $templates, $lang, $header, $headerinclude, $footer, $pairview_menu, $db, $chara_name, $page, $lover1, $lover2, $option, $edit, $chara_lover, $cat_select, $cat_select_edit, $selected, $pair_type;

  

    //Die Sprachdatei
    $lang->load('pairview');


    //Menü
    eval("\$pairview_menu = \"" . $templates->get("pairview_menu") . "\";");

    /*
     * Paare hinzugefügen
     */

    if ($mybb->get_input('action') == 'pairview_add') {

        add_breadcrumb($lang->pairview_add, "misc.php?action=pairview_add");
        //Gäste sollen natürlich keine Pärchen hinzufügen können.
        if ($mybb->user['uid'] == 0) {
            error_no_permission();
        }


        $pair_cat_setting = $mybb->settings['pairview_category'];
        $pair_cat = explode(", ", $pair_cat_setting);
        foreach ($pair_cat as $cat) {
            $cat_select .= "<option value='{$cat}'>{$cat}</option>";
        }


        if ($mybb->user['uid'] == 0) {
            //  error_no_permission();
        } elseif ($_POST['add']) {
            $typ = $db->escape_string($_POST['typ']);
            $lover1 = $db->escape_string($_POST['lover1']);
            $gif1 = $db->escape_string($_POST['gif1']);
            $lover2 = $db->escape_string($_POST['lover2']);
            $gif2 = $db->escape_string($_POST['gif2']);

            $lover_user = get_user_by_username($lover1, array('fields' => '*'));
            $lover1 = $lover_user['uid'];

            $lover_user2 = get_user_by_username($lover2, array('fields' => '*'));
            $lover2 = $lover_user2['uid'];

            $new_pair = array(
                "typ" => $typ,
                "lover1" => $lover1,
                "gif1" => $gif1,
                "lover2" => $lover2,
                "gif2" => $gif2,
            );

            $db->insert_query("pairs", $new_pair);

            $lovers = array(
                "lover1" => $lover1,
                "lover2" => $lover2,
            );

            if(class_exists('MybbStuff_MyAlerts_AlertTypeManager')) {
                $alertType = MybbStuff_MyAlerts_AlertTypeManager::getInstance()->getByCode('pairview_addpair');
                foreach ($lovers as $lover) {
                    if ($alertType != NULL && $alertType->getEnabled()) {
                        $alert = new MybbStuff_MyAlerts_Entity_Alert((int)$lover, $alertType);
                        MybbStuff_MyAlerts_AlertManager::getInstance()->addAlert($alert);
                    }
                }
            }

            redirect("misc.php?action=pairview");
        }
        eval("\$menu = \"" . $templates->get("listen_nav") . "\";");
        // Using the misc_help template for the page wrapper
        eval("\$page = \"" . $templates->get("pairview_add") . "\";");
        output_page($page);
    }

    /*
     * Paare auslesen
     */
    if ($mybb->get_input('action') == 'pairview') {
        add_breadcrumb($lang->pairview, "misc.php?action=pairview");

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
                $lover1_uid = $row['lover1'];
                $lover2_uid = $row['lover2'];
                $pair_type = $row['typ'];
                $option = "";

                /*
                 * Zieh mal alle Informationen für den ersten Charakter aus der Usertabelle
                 */

                $lover1_select = $db->query("select *
              from " . TABLE_PREFIX . "users
              where uid = '$lover1_uid'
              ");
                $lover1_info = $db->fetch_array($lover1_select);

                $username = format_name($lover1_info['username'], $lover1_info['usergroup'], $lover1_info['displaygroup']);
                $lover1 = build_profile_link($username, $lover1_info['uid']);
                $gif1 = $row['gif1'];
 
                /*
                 * Zieh mal alle Informationen für den zweiten Charakter aus der Usertabelle
                 */

                $lover2_select = $db->query("select *
              from " . TABLE_PREFIX . "users
              where uid = '$lover2_uid'
              ");
                $lover2_info = $db->fetch_array($lover2_select);

                $username = format_name($lover2_info['username'], $lover2_info['usergroup'], $lover2_info['displaygroup']);
                $lover2 = build_profile_link($username, $lover2_info['uid']);

                $gif2 = $row['gif2'];


                if ($mybb->usergroup['canmodcp'] == 1 OR $row['lover1'] == $mybb->user['uid'] OR $row['lover2'] == $mybb->user['uid']) {
                    $cat_select_edit = "";
                    $lover_user = get_user($row['lover1'], array('fields' => '*'));
                    $row['lover1'] = $lover_user['username'];

                    $lover_user = get_user($row['lover2'], array('fields' => '*'));
                    $row['lover2'] = $lover_user['username'];

                    foreach ($type as $cat) {


                        if ($cat == $pair_type) {
                            $cat_select_edit .= "<option selected>{$cat}</option>";
                        } else {
                            $cat_select_edit .= "<option>{$cat}</option>";
                        }


                    }

                    $edit = "<a href='misc.php?action=pairview_edit&pair_edit=$row[pairId]'>Editieren</a>";
                    $option = "<div class='loveoption'><a href='misc.php?action=pairview&delete=$row[pairId]'>Löschen</a> # {$edit}</div>";
                }

                eval("\$pairs .= \"" . $templates->get("pairview_bit_charas") . "\";");
            }

            eval("\$pair_bit .= \"" . $templates->get("pairview_chara_bit") . "\";");
        }


        $delete = $mybb->input['delete'];
        if ($delete) {
            $db->delete_query("pairs", "pairId = '$delete'");
            redirect("misc.php?action=pairview");
        }
        eval("\$menu = \"" . $templates->get("listen_nav") . "\";");

        // Using the misc_help template for the page wrapper
        eval("\$page = \"" . $templates->get("pairview") . "\";");
        output_page($page);

    }


    if ($mybb->get_input('action') == 'pairview_edit') {
        $pairid = intval($mybb->input['pair_edit']);
        $pair_cat_setting = $mybb->settings['pairview_category'];
        $type = explode(", ", $pair_cat_setting);

        //Menü
        eval("\$pairview_menu = \"" . $templates->get("pairview_menu") . "\";");
        //Menü
        eval("\$menu = \"" . $templates->get("listen_nav") . "\";");

        $edit_query = $db->query("SELECT *
            FROM " . TABLE_PREFIX . "pairs
            where pairId = '" . $pairid . "'
            ");

        $row = $db->fetch_array($edit_query);

        $lover_user = get_user($row['lover1'], array('fields' => '*'));
        $row['lover1'] = $lover_user['username'];

        $lover_user = get_user($row['lover2'], array('fields' => '*'));
        $row['lover2'] = $lover_user['username'];

        foreach ($type as $cat) {

            if ($cat == $pair_type) {
                $cat_select_edit .= "<option selected>{$cat}</option>";
            } else {
                $cat_select_edit .= "<option>{$cat}</option>";
            }

        }

        if($mybb->input['edit']){
            $pairId = $mybb->input['pairId'];
            $typ = $db->escape_string($_POST['typ']);
            $lover1 = $db->escape_string($_POST['lover1']);
            $gif1 = $db->escape_string($_POST['gif1']);
            $lover2 = $db->escape_string($_POST['lover2']);
            $gif2 = $db->escape_string($_POST['gif2']);

            $lover_user = get_user_by_username($lover1, array('fields' => '*'));
            $lover1 = $lover_user['uid'];

            $lover_user2 = get_user_by_username($lover2, array('fields' => '*'));
            $lover2 = $lover_user2['uid'];


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
        eval("\$page = \"" . $templates->get("pairview_chara_edit") . "\";");
        output_page($page);

    }

}

//wer ist wo
$plugins->add_hook ('fetch_wol_activity_end', 'pairview_user_activity');
$plugins->add_hook ('build_friendly_wol_location_end', 'pairview_location_activity');

function pairview_user_activity($user_activity)
{
    global $user;

    if (my_strpos ($user['location'], "misc.php?action=pairview") !== false) {
        $user_activity['activity'] = "pairview";
    }
    if (my_strpos ($user['location'], "misc.php?action=pairview_add") !== false) {
        $user_activity['activity'] = "pairview_add";
    }

    return $user_activity;
}

function pairview_location_activity($plugin_array)
{
    global $db, $mybb, $lang;

    if ($plugin_array['user_activity']['activity'] == "pairview") {
        $plugin_array['location_name'] = "Schaut sich die <b><a href='misc.php?action=pairview'>Pärchenübersicht</a></b> an.";
    }

    if ($plugin_array['user_activity']['activity'] == "pairview_add") {
        $plugin_array['location_name'] = "Fügt ein weiteres Pärchen der <b><a href='misc.php?action=pairview_add'>Pärchenübersicht</a></b> hinzu.";
    }

    return $plugin_array;
}




function pairview_alerts()
{
    global $mybb, $lang;
    $lang->load('pairview');

    /**
     * Alert formatter for my custom alert type.
     */
    class MybbStuff_MyAlerts_Formatter_AddPairFormatter extends MybbStuff_MyAlerts_Formatter_AbstractFormatter
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
            return $this->lang->sprintf(
                $this->lang->pairview_addpair,
                $outputAlert['from_user'],
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

        }
    }

    if (class_exists('MybbStuff_MyAlerts_AlertFormatterManager')) {
        $formatterManager = MybbStuff_MyAlerts_AlertFormatterManager::getInstance();

        if (!$formatterManager) {
            $formatterManager = MybbStuff_MyAlerts_AlertFormatterManager::createInstance($mybb, $lang);
        }

        $formatterManager->registerFormatter(
            new MybbStuff_MyAlerts_Formatter_AddPairFormatter($mybb, $lang, 'pairview_addpair')
        );
    }
}
