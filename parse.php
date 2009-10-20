<?
	header('Content-type: text/plain; charset=UTF-8');

	$data = file_get_contents('source.htm');

	preg_match_all('!<tr(.*?)</tr>!', $data, $m);

	foreach ($m[1] as $tr){


		#
		# is the a category row?
		#

		if (preg_match('!class=\'category\'!', $tr)){
			if (preg_match('!.>(.*?)<!', $tr, $m)){

				echo "\n";
				echo "\t\t#####################################################################\n";
				echo "\t\t#\n";
				echo "\t\t# Category $m[1]\n";
				echo "\t\t#\n";
				continue;
			}
		}


		#
		# is the a subcategory row?
		#

		if (preg_match('!class=\'subcategory\'!', $tr)){
			if (preg_match('!.>(.*?) \(.*\)<!', $tr, $m)){

				echo "\n";
				echo "\t\t# Sub-category: $m[1]\n";
				continue;
			}
		}


		#
		# is this a character row?
		#

		if (preg_match('!id=(e-[0-9A-F]+)!', $tr, $m)){

			$code = $m[1];

			preg_match_all('!<td(.*?)</td>!', $tr, $cells);
			$cells = $cells[1];
			$num = count($cells);

			if ($num != 7){
				echo "<h1>Found $num cells...</h1>\n";
				continue;
			}


			#
			# get unified code point
			#

			$unified = 0;
			if (preg_match('!U\+([0-9A-F]{4,6})!', $cells[1], $m)) $unified = '0x'.$m[1];


			#
			# get name
			#

			$name = 'unknown';
			if (preg_match('!>(.*?)(<|$)!', $cells[2], $m)) $name = $m[1];
			$name_enc = escape_string($name);


			#
			# get different formats
			#

			$docomo		= parse_cell($cells[3]);
			$kddi		= parse_cell($cells[4]);
			$softbank	= parse_cell($cells[5]);


			#
			# get google
			#

			$google = 0;
			if (preg_match('!U\+([0-9A-F]{4,6})!', $cells[6], $m)) $google = '0x'.$m[1];


			echo "\t\tarray('$code', $unified, $name_enc, ";
			echo "$docomo[uni], $docomo[sjis], $docomo[jis], ".escape_string($docomo[fallback]).", ";
			echo "$kddi[uni], $kddi[sjis], $kddi[jis], ".escape_string($kddi[fallback]).", ";
			echo "$softbank[uni], $softbank[sjis], $softbank[jis], ".escape_string($softbank[fallback]).", ";
			echo "$google),\n";

			#echo "symbol $code - $unified $name $docomo[uni]/$docomo[sjis]/$docomo[jis]/$docomo[fallback] $kddi[uni]/$kddi[sjis]/$kddi[jis]/$kddi[fallback] $google<br />\n";

			continue;
		}


		#
		# something unexpected
		#

		echo "\t\t# unknown row: ".HtmlSpecialChars($tr)."\n";

	}

	function parse_cell($cell){

		$out = array(
			'uni'		=> 0,
			'sjis'		=> 0,
			'jis'		=> 0,
			'fallback'	=> '',
		);

		if (preg_match('!U\+([0-9A-F]{4,6})!', $cell, $m)) $out[uni] = '0x'.$m[1];
		if (preg_match('!SJIS\-([0-9A-F]{4,6})!', $cell, $m)) $out[sjis] = '0x'.$m[1];
		if (preg_match('!JIS\-([0-9A-F]{4,6})!', $cell, $m)) $out[jis] = '0x'.$m[1];
		if (preg_match('!class=\'text_fallback\'>(.*?)(<|$)!', $cell, $m)) $out[fallback] = $m[1];

		return $out;
	}


	function escape_string($s){

		return "'".AddSlashes($s)."'";
	}

?>