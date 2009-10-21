<?
	header('Content-type: text/plain; charset=UTF-8');

	$data = file_get_contents('source.htm');

	$items = array();

	preg_match_all('!<tr(.*?)</tr>!', $data, $mtr);

	foreach ($mtr[1] as $tr){


		#
		# is the a category row?
		#

		if (preg_match('!class=\'category\'!', $tr)){
			if (preg_match('!.>(.*?)<!', $tr, $m)){

				$cat = $m[1];
				continue;
			}
		}


		#
		# is the a subcategory row?
		#

		if (preg_match('!class=\'subcategory\'!', $tr)){
			if (preg_match('!.>(.*?) \(.*\)<!', $tr, $m)){

				$subcat = $m[1];
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
				echo "# weird - found $num cells instead of 7...\n";
				continue;
			}


			#
			# get unified code point
			#

			$unified = 0;
			if (preg_match('!U\+([0-9A-F]{4,6})!', $cells[1], $m)) $unified = hexdec($m[1]);


			#
			# get name
			#

			$name = 'unknown';
			if (preg_match('!>(.*?)(<|$)!', $cells[2], $m)) $name = $m[1];


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
			if (preg_match('!U\+([0-9A-F]{4,6})!', $cells[6], $m)) $google = hexdec($m[1]);

			if ($unified > 0x80){

				$items[] = array(
					'code'		=> $code,
					'unified'	=> $unified,
					'name'		=> $name,
					'docomo'	=> $docomo,
					'kddi'		=> $kddi,
					'softbank'	=> $softbank,
					'google'	=> $google,
				);
			}

			continue;
		}


		#
		# something unexpected
		#

		echo "# unknown row: ".HtmlSpecialChars($tr)."\n";
	}


	#
	# build the mapping arrays
	#

	$maps = array();

	foreach ($items as $row){

		$maps[names][$row[unified]] = $row[name];
		$unified_bytes = emoji_utf8_bytes($row[unified]);

		foreach (array('docomo', 'kddi', 'softbank') as $type){

			if ($row[$type][uni]){

				$type_bytes = emoji_utf8_bytes($row[$type][uni]);

				$maps["unified_to_{$type}"][$unified_bytes] = $type_bytes;

				if ($row[$type][uni] > 0x80 && !$row[$type][oneway]){
					$maps["{$type}_to_unified"][$type_bytes] = $unified_bytes;
				}

			}else if ($row[$type][fallback]){

				$maps["unified_to_{$type}"][$unified_bytes] = $row[$type][fallback];
			}else{
				$maps["unified_to_{$type}"][$unified_bytes] = '?';
			}
		}

		$google_bytes = emoji_utf8_bytes($row[google]);

		$maps["unified_to_google"][$unified_bytes] = $google_bytes;

		if ($row[google] > 0x80){
			$maps["google_to_unified"][$google_bytes] = $unified_bytes;
		}


		$hex = sprintf('%x', $row[unified]);

		$maps["unified_to_html"][$unified_bytes] = "<span class=\"emoji emoji$hex\"></span>";
	}


	#
	# output
	# we could just use var_dump, but we get 'better' output this way
	#

	echo "<"."?php\n";

	echo "\n";
	echo "\t#\n";
	echo "\t# WARNING:\n";
	echo "\t# This code is auto-generated. Do not modify it manually.\n";
	echo "\t#\n";
	echo "\n";

	echo "\t\$GLOBALS['emoji_maps'] = array(\n";

	echo "\t\t'names' => array(\n";

	foreach ($maps[names] as $k => $v){

		$name_enc = escape_string($v);
		echo "\t\t\t$k => $name_enc,\n";
	}

	echo "\t\t),\n";

	foreach ($maps as $k => $v){

		if ($k == 'names') continue;

		echo "\t\t'$k' => array(\n";

		$count = 0;
		echo "\t\t\t";
		foreach ($v as $k2 => $v2){
			$count++;
			if ($count % 10 == 0) echo "\n\t\t\t";
			echo format_string($k2).'=>'.format_string($v2).', ';
		}
		echo "\n";

		echo "\t\t),\n";
	}

	echo "\t);\n";



	function parse_cell($cell){

		$out = array(
			'uni'		=> 0,
			'sjis'		=> 0,
			'jis'		=> 0,
			'fallback'	=> '',
			'oneway'	=> 0,
		);

		if (preg_match('!U\+([0-9A-F]{4,6})!', $cell, $m)) $out[uni] = hexdec($m[1]);
		if (preg_match('!SJIS\-([0-9A-F]{4,6})!', $cell, $m)) $out[sjis] = hexdec($m[1]);
		if (preg_match('!JIS\-([0-9A-F]{4,6})!', $cell, $m)) $out[jis] = hexdec($m[1]);
		if (preg_match('!class=\'text_fallback\'>(.*?)(<|$)!', $cell, $m)) $out[fallback] = $m[1];
		if (preg_match('!class=\'fallback\'>(.*?)(<|$)!', $cell, $m)) $out[oneway] = 1;

		return $out;
	}


	function escape_bytes($cp){
		return escape_string(emoji_utf8_bytes($cp));
	}

	function emoji_utf8_bytes($cp){

		if ($cp > 0x10000){
			# 4 bytes
			return	chr(0xF0 | (($cp & 0x1C0000) >> 18)).
				chr(0x80 | (($cp & 0x3F000) >> 12)).
				chr(0x80 | (($cp & 0xFC0) >> 6)).
				chr(0x80 | ($cp & 0x3F));
		}else if ($cp > 0x800){
			# 3 bytes
			return	chr(0xE0 | (($cp & 0xF000) >> 12)).
				chr(0x80 | (($cp & 0xFC0) >> 6)).
				chr(0x80 | ($cp & 0x3F));
		}else if ($cp > 0x80){
			# 2 bytes
			return	chr(0xC0 | (($cp & 0x7C0) >> 6)).
				chr(0x80 | ($cp & 0x3F));
		}else{
			# 1 byte
			return chr($cp);
		}
	}


	function escape_string($s){
		return "'".AddSlashes($s)."'";
	}

	function format_string($s){
		$out = '';
		for ($i=0; $i<strlen($s); $i++){
			$c = ord(substr($s,$i,1));
			if ($c >= 0x20 && $c < 0x80 && !in_array($c, array(34, 39, 92))){
				$out .= chr($c);
			}else{
				$out .= sprintf('\\x%02x', $c);
			}
		}
		return '"'.$out.'"';
	}
?>