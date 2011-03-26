<?
	$cp = hexdec($_GET[cp]);


	if ($cp >= 0x10000){
		# 4 bytes
		$bytes = array(
			0xF0 | (($cp & 0x1C0000) >> 18),
			0x80 | (($cp & 0x3F000) >> 12),
			0x80 | (($cp & 0xFC0) >> 6),
			0x80 | ($cp & 0x3F),
		);
	}else if ($cp >= 0x800){
		# 3 bytes
		$bytes = array(
			0xE0 | (($cp & 0xF000) >> 12),
			0x80 | (($cp & 0xFC0) >> 6),
			0x80 | ($cp & 0x3F),
		);
	}else if ($cp >= 0x80){
		# 2 bytes
		$bytes = array(
			0xC0 | (($cp & 0x7C0) >> 6),
			0x80 | ($cp & 0x3F),
		);
	}else{
		# 1 byte
		$bytes = array(
			$cp,
		);
	}


	$cpx = dechex($cp);
	$bytesx = array();
	foreach ($bytes as $byte) $bytesx[] = '0x'.dechex($byte);

	#echo "DEC: Codepoint $cp -> ".implode(', ', $bytes)."<br />";

	echo "HEX: Codepoint U+$cpx -> ".implode(', ', $bytesx)."<br />";

?>