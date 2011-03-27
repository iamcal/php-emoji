<?
	header('Content-type: text/html; charset=UTF-8');

	include('emoji.php');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en">
<head>
	<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
	<title>EMoji HTML Test</title>
	<link href="emoji.css" rel="stylesheet" type="text/css" />
</head>
<body>

<table border="1">
	<tr>
		<th>Unified</th>
		<th>Name</th>
		<th>Unified Text</th>
		<th>HTML</th>
	</tr>
<?
	$src = array(
		0x2600,		# BLACK SUN WITH RAYS
		0x1F494,	# BROKEN HEART (was U+1F493)
		0x1F197,	# OK SIGN (was U+1F502)
	);

	foreach ($src as $unified){

		$bytes = "Hello ".emoji_utf8_bytes($unified)." World";

		echo "<tr>\n";
		echo "<td>".sprintf('U+%04X', $unified)."</td>\n";
		echo "<td>".HtmlSpecialChars(emoji_get_name($unified))."</td>\n";
		echo "<td>$bytes</td>\n";
		echo "<td>".emoji_unified_to_html($bytes)."</td>\n";
		echo "</tr>\n";
	}
?>
</table>

</body>
</html>
