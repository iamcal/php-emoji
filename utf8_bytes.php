<?
	$cp = hexdec($_GET[cp]);


		if ($cp < 128){
			$bytes = array($cp);

		}elseif ($cp < 2048){
			$bytes = array(192 + (($cp - ($cp % 64)) / 64),
				128 + ($cp % 64));

		}else{
			$bytes = array(224 + (($cp - ($cp % 4096)) / 4096),
				128 + ((($cp % 4096) - ($cp % 64)) / 64),
				128 + ($cp % 64));
		}

	$cpx = dechex($cp);
	$bytesx = array();
	foreach ($bytes as $byte) $bytesx[] = '0x'.dechex($byte);

	#echo "DEC: Codepoint $cp -> ".implode(', ', $bytes)."<br />";

	echo "HEX: Codepoint U+$cpx -> ".implode(', ', $bytesx)."<br />";

?>