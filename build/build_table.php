<?php
	include('../lib/emoji.php');

	function format_bytes($bytes){
		$out = '';
		for ($i=0; $i<strlen($bytes); $i++){
			$out .= '\\x'.sprintf('%02X', ord(substr($bytes, $i, 1)));
		}
		return $out;
	}
?>
<html>
<head>

<title>PHP Emoji Catalog</title>
<link rel="stylesheet" type="text/css" media="all" href="../lib/emoji.css" />
<style type="text/css">

body {
    font-size: 12px;
    font-family: Arial, Helvetica, sans-serif;
}

table {
    -webkit-border-radius: 0.41em;
    -moz-border-radius: 0.41em;
    border: 1px solid #999;
    font-size: 12px;
}

table td {
    padding-left: 0.41em;
    padding-right: 0.41em;
}

table th {
    font-weight: bold;
    text-align: left;
    background: #BBB;
    color: #333;
    font-size: 14px;
    padding: 0.41em;
}

table tbody tr:nth-child(even) {
    background: #dedede;
}

table tbody td {
    padding: 0.41em;
}

</style>
</head>
<body>

<h1>PHP Emoji Catalog</h1>

<table cellspacing="0" cellpadding="0">
	<tr>
		<th colspan="2">Name</th>
		<th>Unified</th>
		<th>Docomo</th>
		<th>KDDI</th>
		<th>Softbank</th>
		<th>Google</th>
	</tr>
	<tbody>

<?php
	foreach ($GLOBALS['emoji_maps']['names'] as $unified => $name){

		echo "\t<tr>\n";
		echo "\t\t<td>{$GLOBALS['emoji_maps']['unified_to_html'][$unified]}</td>\n";
		echo "\t\t<td>".HtmlSpecialChars(StrToLower($name))."</td>\n";
		echo "\t\t<td>".format_bytes($unified)."</td>\n";
		echo "\t\t<td>".format_bytes($GLOBALS['emoji_maps']['unified_to_docomo'][$unified])."</td>\n";
		echo "\t\t<td>".format_bytes($GLOBALS['emoji_maps']['unified_to_kddi'][$unified])."</td>\n";
		echo "\t\t<td>".format_bytes($GLOBALS['emoji_maps']['unified_to_softbank'][$unified])."</td>\n";
		echo "\t\t<td>".format_bytes($GLOBALS['emoji_maps']['unified_to_google'][$unified])."</td>\n";
		echo "\t</tr>\n";
	}
?>
	</tbody>
</table>

</body>
<html>
