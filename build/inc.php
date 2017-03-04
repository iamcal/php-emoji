
	$GLOBALS['emoji_maps']['html_to_unified'] = array_flip($GLOBALS['emoji_maps']['unified_to_html']);


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


	#
	# HTML transformation
	#

	function emoji_unified_to_html($text){
		return preg_replace_callback($GLOBALS['emoji_maps']['unified_rx'], function($m){
			if (isset($m[2]) && $m[2] == "\xEF\xB8\x8E") return $m[0];
			$cp = $GLOBALS['emoji_maps']['unified_to_html'][$m[1]];
			return "<span class=\"emoji-outer emoji-sizer\"><span class=\"emoji-inner emoji{$cp}\"></span></span>";
		}, $text);
	}

	function emoji_html_to_unified($text){
		return preg_replace_callback("!<span class=\"emoji-outer emoji-sizer\"><span class=\"emoji-inner emoji([0-9a-f]+)\"></span></span>!", function($m){
			if (isset($GLOBALS['emoji_maps']['html_to_unified'][$m[1]])){
				return $GLOBALS['emoji_maps']['html_to_unified'][$m[1]];
			}
			return $m[0];
		}, $text);
	}


	function emoji_convert($text, $map){

		return str_replace(array_keys($GLOBALS['emoji_maps'][$map]), $GLOBALS['emoji_maps'][$map], $text);
	}

	function emoji_get_name($unified_cp){

		return $GLOBALS['emoji_maps']['names'][$unified_cp] ? $GLOBALS['emoji_maps']['names'][$unified_cp] : '?';
	}

	function emoji_contains_emoji($text){

		$count = 0;
		str_replace($GLOBALS['emoji_maps']['prefixes'], '00', $text, $count);
		return $count > 0;
	}
