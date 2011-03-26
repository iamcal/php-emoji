<?php

	include_once(dirname(__FILE__)."/emoji_map.php");


	#
	# functions to convert incoming data into the unified format
	#

	function emoji_docomo_to_unified(	$text){ return emoji_convert($text, 'docomo_to_unified'); }
	function emoji_kddi_to_unified(		$text){ return emoji_convert($text, 'kddi_to_unified'); }
	function emoji_softbank_to_unified(	$text){ return emoji_convert($text, 'softbank_to_unified'); }
	function emoji_google_to_unified(	$text){ return emoji_convert($text, 'google_to_unified'); }


	#
	# functions to convert unified data into an outgoing format
	#

	function emoji_unified_to_docomo(	$text){ return emoji_convert($text, 'unified_to_docomo'); }
	function emoji_unified_to_kddi(		$text){ return emoji_convert($text, 'unified_to_kddi'); }
	function emoji_unified_to_softbank(	$text){ return emoji_convert($text, 'unified_to_softbank'); }
	function emoji_unified_to_google(	$text){ return emoji_convert($text, 'unified_to_google'); }
	function emoji_unified_to_html(		$text){ return emoji_convert($text, 'unified_to_html'); }





	function emoji_convert($text, $map){

		return str_replace(array_keys($GLOBALS['emoji_maps'][$map]), $GLOBALS['emoji_maps'][$map], $text);
	}

	function emoji_get_name($unified_cp){

		return $GLOBALS['emoji_maps']['names'][$unified_cp] ? $GLOBALS['emoji_maps']['names'][$unified_cp] : '?';
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
?>
