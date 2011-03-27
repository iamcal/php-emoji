<?
	include('catalog.php');


	$out = array();

	foreach ($catalog as $row){

		$hex = sprintf('%x', $row['unicode']);
		$html = "<span class=\"emoji emoji$hex\"></span>";


		$out[] = array(
			'name'		=> $row['char_name']['title'],

			'unified'	=> $row['unicode'],
			'docomo'	=> $row['docomo']['unicode'],
			'kddi'		=> $row['au']['unicode'],
			'softbank'	=> $row['softbank']['unicode'],
			'google'	=> $row['google']['unicode'],

			'html'		=> $html,
		);
	}

?>

<link rel="stylesheet" type="text/css" media="all" href="../emoji.css" />

<table border="1">
	<tr>
		<th colspan="2">Name</th>
		<th>Unified</th>
		<th>DoCoMo</th>
		<th>KDDI</th>
		<th>Softbank</th>
		<th>Google</th>
	</tr>

<?
	foreach ($out as $row){

		echo "\t<tr>\n";
		echo "\t\t<td>$row[html]</td>\n";
		echo "\t\t<td>".HtmlSpecialChars(StrToLower($row['name']))."</td>\n";
		echo "\t\t<td>".format_codepoint($row['unified'])."</td>\n";
		echo "\t\t<td>".format_codepoint($row['docomo'])."</td>\n";
		echo "\t\t<td>".format_codepoint($row['kddi'])."</td>\n";
		echo "\t\t<td>".format_codepoint($row['softbank'])."</td>\n";
		echo "\t\t<td>".format_codepoint($row['google'])."</td>\n";
		echo "\t</tr>\n";
	}
	echo "</table>\n";
	exit;	



	###############################################################

	function format_codepoint($u){

		if ($u) return 'U+'.sprintf('%04X', $u);

		return '-';
	}
?>