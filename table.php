<?
	include('emoji.php');


	$out = array();

	foreach ($emoji_maps['names'] as $k => $v){

		$u_c = $k;
		$u_b = utf8_code_to_bytes($k);

		$out[] = array(
			'name'		=> $v,

			'unified'	=>$u_c,
			'docomo'	=> utf8_bytes_to_code(emoji_unified_to_docomo($u_b)),
			'kddi'		=> utf8_bytes_to_code(emoji_unified_to_kddi($u_b)),
			'softbank'	=> utf8_bytes_to_code(emoji_unified_to_softbank($u_b)),
			'google'	=> utf8_bytes_to_code(emoji_unified_to_google($u_b)),

			'html'		=> emoji_unified_to_html($u_b),
		);
	}


	echo "<table>\n";

	foreach ($out as $row){

		echo "\t<tr>\n";
		echo "\t\t<td></td>\n";

		echo "\t</tr>\n";
	}
	echo "</table>\n";
	exit;	

	print_r($out);





	function utf8_code_to_bytes($cp){

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

	function utf8_bytes_to_code($bytes){

		$c = array();
		for ($i=0; $i<strlen($bytes); $i++) $c[] = ord($bytes{$i});

		if (count($c) == 4) return (($c[0] & 0x7) << 18) | (($c[1] & 0x3f) << 12) | (($c[2] & 0x3f) << 6) | ($c[3] & 0x3f);
		if (count($c) == 3) return (($c[0] & 0xf) << 12) | (($c[1] & 0x3f) << 6) | ($c[2] & 0x3f);
		if (count($c) == 2) return (($c[0] & 0x1f) << 6) | ($c[1] & 0x3f);
		if (count($c) == 1) return $c[0] & 0x7f;

		return 0;
	}
?>
