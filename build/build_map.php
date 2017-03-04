<?php
	$in = file_get_contents('emoji-data/emoji.json');
	$catalog = json_decode($in, true);


	#
	# build the final maps
	#

	$maps = array();

	$maps['names']		= make_names_map($catalog);
	$maps['kaomoji']	= get_all_kaomoji($catalog);

	#fprintf(STDERR, "fix Geta Mark ()  '〓' (U+3013)\n");
	#$catalog = fix_geta_mark($catalog);

	$maps["unified_to_docomo"]	= make_mapping($catalog, 'docomo');
	$maps["unified_to_kddi"]	= make_mapping($catalog, 'au');
	$maps["unified_to_softbank"]	= make_mapping($catalog, 'softbank');
	$maps["unified_to_google"]	= make_mapping($catalog, 'google');

	$maps["docomo_to_unified"]	= make_mapping_flip($catalog, 'docomo');
	$maps["kddi_to_unified"]	= make_mapping_flip($catalog, 'au');
	$maps["softbank_to_unified"]	= make_mapping_flip($catalog, 'softbank');
	$maps["google_to_unified"]	= make_mapping_flip($catalog, 'google');

	$maps["unified_to_html"]	= make_html_map($catalog);
	$maps["unified_rx"]		= make_html_rx($maps["unified_to_html"]);

	$maps["prefixes"]		= fetch_prefixes($maps['names']);


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

	foreach ($maps['names'] as $k => $v){

		$key_enc = format_string($k);
		$name_enc = "'".AddSlashes($v)."'";
		echo "\t\t\t$key_enc => $name_enc,\n";
	}

	echo "\t\t),\n";

	foreach ($maps as $k => $v){

		if ($k == 'names') continue;
		if ($k == 'unified_rx') continue;

		echo "\t\t'$k' => array(\n";

		foreach ($v as $k2 => $v2){
			if (strpos($k, 'prefixes') === 0){
				echo "\t\t\t" . format_string($v2) . ",\n";
			}else{
				echo "\t\t\t" . format_string($k2) . '=>' . format_string($v2) . ",\n";
			}
		}

		echo "\t\t),\n";
	}

	echo "\t\t'unified_rx' => " . var_export($maps['unified_rx'], true) . ",\n";

	echo "\t);\n";


	echo file_get_contents('inc.php');



	##########################################################################################

	function get_all_kaomoji($mapping){
		$arr = array();

		foreach ($mapping as $map){
			if (isset($map['docomo']['kaomoji']) ) {
				$arr[ $map['docomo']['kaomoji'] ] = '1';
			}

			if (isset($map['au']['kaomoji']) ) {
				$arr[ $map['au']['kaomoji'] ] = '1';
			}

			if (isset($map['softbank']['kaomoji']) ) {
				$arr[ $map['softbank']['kaomoji'] ] = '1';
			}
		}

		return array_keys($arr);
	}

	function make_names_map($map){

		$out = array();
		foreach ($map as $row){

			$bytes = unicode_bytes($row['unified']);

			$out[$bytes] = $row['name'];
		}

		return $out;
	}

	function make_html_map($map){

		$out = array();

		foreach ($map as $row){

			$hex = unicode_hex_chars($row['unified']);
			$bytes = unicode_bytes($row['unified']);

			$out[$bytes] = $hex;
		}

		return $out;
	}

	function make_html_rx($map){

		$rx_bits = array();

		foreach ($map as $bytes => $hex){

			$out = '';
			for ($i=0; $i<strlen($bytes); $i++){
				$c = ord(substr($bytes,$i,1));
				$out .= sprintf('\\x%02x', $c);
			}

			$rx_bits[] = $out;
		}

		return '!('.implode('|', $rx_bits).')(\\xEF\\xB8\\x8E|\\xEF\\xB8\\x8F)?!';
	}

	function make_mapping($mapping, $dest){

		$result = array();

		foreach ($mapping as $map){

			$src_char = unicode_bytes($map['unified']);

			if (!empty($map[$dest])){

				$dest_char = unicode_bytes($map[$dest]);
			}else{
				$dest_char = '';
			}

			$result[$src_char] = $dest_char;
		}

		return $result;
	}

	function make_mapping_flip($mapping, $src){
		$result = make_mapping($mapping, $src);
		$result = array_flip($result);
		unset($result[""]);
		return $result;
	}

	function unicode_bytes($str){

		$out = '';

		$cps = explode('-', $str);
		foreach ($cps as $cp){
			$out .= emoji_utf8_bytes(hexdec($cp));
		}

		return $out;
	}

	function unicode_hex_chars($str){

		$out = '';

		$cps = explode('-', $str);
		foreach ($cps as $cp){
			$out .= sprintf('%x', hexdec($cp));
		}

		return $out;
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

	function fetch_prefixes($map, $length = 2){
		$result = array();
		foreach ($map as $symbol => $junk){
			$result[substr($symbol, 0, $length)] = 1;
		}
		return array_keys($result);
	}
