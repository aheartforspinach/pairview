pairview

<html>
<head>
<title>{$mybb->settings['bbname']} - Pärchenübersicht</title>
{$headerinclude}
</head>
<body>
{$header}
<table border="0" cellspacing="{$theme['borderwidth']}" cellpadding="{$theme['tablespace']}" class="tborder">
<tr>
<td class="thead" colspan="2"><div class="headline">Pärchenübersicht</div></td>
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
</html>

__________________________________
pairview_add

<html>
<head>
<title>{$mybb->settings['bbname']} - Pärchen hinzufügen</title>
{$headerinclude}
</head>
<body>
{$header}
<table border="0" cellspacing="{$theme['borderwidth']}" cellpadding="{$theme['tablespace']}" class="tborder">
<tr>
<td class="thead" colspan="2"><div class="headline">Pärchen hinzufügen</div></td>
</tr>
<tr>
	<td valign="top" width="20%">
		{$pairview_menu}
	</td>
<td class="trow1" align="center" width="80%">
<form action="misc.php?action=pairview_add" id="pair_add"  method="post">
<table width="100%">
	<tr><td class="thead" colspan="2"><div class='headline'>Pärchen hinzufügen</div></td></tr>
	<tr><td class="trow1">	<div class="profilucp">Beziehungstyp</div></td><td class="trow2">
	<select name='typ' id='typ'>
		<option value="Ehepaar">Ehepaar</option>
		<option value="Reinblüter Verlobung">Reinblüter Verlobung</option>
		<option value='Paare'>Paar</option>
		<option value='Zukünftiges'>Zukünftiges</option>
		</select></td></tr>
	<tr><td class="trow1">	<div class="profilucp">erster Partner</div></td><td class="trow2">
	<select name='lover1' id='lover1'>
{$chara_name}
</select></td></tr>
	<tr><td class="trow1">	
	<div class="profilucp">Gif für Partner 1</div>
		<div class="smalltext">Größe 90x100, Typ ist eine Gif</div></td><td class="trow2">
<input type="text" name="gif1" id="gif1" value="" class="textbox" /></td></tr>
	<tr><td class="trow1">	
<div class="profilucp">zweiter Partner</div></td><td class="trow2">
	<select name='lover2' id='lover2'>
{$chara_name}
</select>	</td></tr>
		<tr><td class="trow1">	
	<div class="profilucp">Gif für Partner 2</div>
				<div class="smalltext">Größe 90x100, Typ ist eine Gif</div></td><td class="trow2">
<input type="text" name="gif2" id="gif2" value="" class="textbox" /></td></tr>
	<td align="center" colspan="2" class="trow1"><input type="submit" name="add" value="Pärchen eintragen" id="submit" class="button"></td></tr>
	</table></form>

</td>
</tr>
</table>
{$footer}
</body>
</html>

_____________________________________________
pairview_bit_charas
<tr><td class="trow1">
<div class="lovebox"><table width="100%">
	<tr><td width="100px;"><img src="{$gif1}" class="lovepic"></td>
	<td><div class="lovefacts"><span class="lovefact">Name</span> {$lover1}</div>
<div class="lovefacts"><span class="lovefact">Alter</span> {$age1} Jahre</div>
<div class="lovefacts"> <span class="lovefact">mit wem</span> {$lover2}</div>
<div class="lovefacts"><span class="lovefact">Alter</span> {$age2} Jahre</div></td>
		<td width="100px;"><img src="{$gif2}" class="lovepic"></td></tr>
	{$option}</table>
</div>
	</td></tr>
_____________________________________________
pairview_chara_bit
<table width="100%" style="margin: auto;">
	<tr><td class="thead"><h1>{$typ}</h1></td></tr>
	{$pairs}
</table>
_____________________________________________
pairview_chara_edit
<div id="popinfo$row[pairId]" class="infopop">
  <div class="pop"><form action="misc.php?action=pairview" id="pair_edit"  method="post">
	  <input type="hidden" name="pairId" id="pairId" value="{$row['pairId']}" />
<table border="0" cellspacing="{$theme['borderwidth']}" cellpadding="{$theme['tablespace']}" class="tborder">
	<tr><td class="tcat" colspan="2"><div class='headline'>Pärchen ändern</div></td></tr>
	<tr><td class="trow1">	<div class="profilucp">Beziehungstyp</div></td><td class="trow2">
	<select name='typ' id='typ'>
		<option  selected value="{$row['typ']}">{$row['typ']}</option>
		<option value="Ehepaar">Ehepaar</option>
		<option value="Reinblüter Verlobung">Reinblüter Verlobung</option>
		<option value='Paare'>Paar</option>
		<option value='Zuküntiges'>Zukünftiges</option>
		</select></td></tr>
	<tr><td class="trow1">	
	<div class="profilucp">Gif für Partner 1</div>
		<div class="smalltext">Größe 90x100, Typ ist eine Gif</div></td><td class="trow2">
<input type="text" name="gif1" id="gif1" value="{$row['gif1']}" class="textbox" /></td></tr>
		<tr><td class="trow1">	
	<div class="profilucp">Gif für Partner 2</div>
			<div class="smalltext">Größe 90x100, Typ ist eine Gif</div></td><td class="trow2">
<input type="text" name="gif2" id="gif2" value="{$row['gif2']}" class="textbox" /></td></tr>
	<td align="center" colspan="2" class="trow1"><input type="submit" name="edit" value="Pärchen editieren" id="submit" class="button"></td></tr>
	</table></form>
		</div><a href="#closepop" class="closepop"></a>
</div>

<a href="#popinfo$row[pairId]">Editieren</a>
_____________________________________________
pairview_menu
<table width="100%">
	<tr>
		<td class="tcat">
			<h1>
				Menü
			</h1>
		</td></tr>
	<tr><td class="trow1">
	<div class="love_menu">	<i class="fas fa-th-list"></i> <a href="misc.php?action=pairview">Pärchenübersicht</a></div>
		</td></tr>
	<tr><td class="trow2">
	<div class="love_menu"><i class="fas fa-folder-plus"></i>	<a href="misc.php?action=pairview_add">Pärchen hinzufügen</a></div>
		</td></tr>
</table>
---------------------------------------------
css (ich hab sie pairview.css genannt)
.lovebox{
width: 500px;
margin: 5px auto;	
	padding: 4px;
}

.lovepic{
border:3px solid #6C967F;
border-radius:20%;
	height: 100px;
	width: 90px;
}

.lovefact{
font-family: 'bitter', serif;
color:
#6C967F;
font-size: 12px;
text-decoration: none;
display: inline;
text-transform: uppercase;
font-weight: bold;
}


.lovefacts{
font-family: 'bitter', serif;
font-size: 12px;
font-weight: bold;
border-bottom: 1px solid #6C967F;
text-transform: uppercase;
text-align: right;
	letter-spacing: 3px;
}

.love_menu{
font-family: 'bitter', serif;
color:
#6C967F;
font-size: 12px;
text-decoration: none;
display: inline;
text-transform: uppercase;
font-weight: bold;
}

.love_menu a{
font-family: 'bitter', serif;
color:
#6C967F;
font-size: 12px;
text-decoration: none;
display: inline;
text-transform: uppercase;
font-weight: bold;
}

.infopop { position: fixed; top: 0; right: 0; bottom: 0; left: 0; background: hsla(0, 0%, 0%, 0.5); z-index: 1; opacity:0; -webkit-transition: .5s ease-in-out; -moz-transition: .5s ease-in-out; transition: .5s ease-in-out; pointer-events: none; } 

.infopop:target { opacity:1; pointer-events: auto; } 

.infopop > .pop {width: 500px; position: relative; margin: 10% auto; padding: 25px; z-index: 3; } 

.closepop { position: absolute; right: -5px; top:-5px; width: 100%; height: 100%; z-index: 2; }
