<?php

    $in = file_get_contents('emoji-map.json');
    $catalog = json_decode($in, true);

    $GLOBALS['emoji_maps'] = [
        'unified_rx'      => $catalog['unified_rx'],
        'unified_to_html' => $catalog['unified_to_html']
    ];

	$GLOBALS['emoji_maps']['html_to_unified'] = array_flip($GLOBALS['emoji_maps']['unified_to_html']);

    /**
    * HTML transformation
    */
	function emoji_unified_to_html($text){
        $unifiedRegexes = $GLOBALS['emoji_maps']['unified_rx'];

        if (!is_array($unifiedRegexes)) {
            return $text;
        }

        foreach ($unifiedRegexes as $unifiedRx) {
            $text = preg_replace_callback($unifiedRx, function ($m) {
                if (isset($m[2]) && $m[2] == "\xEF\xB8\x8E") return $m[0];
                $cp = $GLOBALS['emoji_maps']['unified_to_html'][$m[1]];
                return "<span class=\"emoji-outer emoji-sizer\"><span class=\"emoji-inner emoji{$cp}\"></span></span>";
            }, $text);
        }

        return $text;
	}

	function emoji_html_to_unified($text){
		return preg_replace_callback("!<span class=\"emoji-outer emoji-sizer\"><span class=\"emoji-inner emoji([0-9a-f]+)\" data-code=\".+?\"></span></span>!", function($m){
			if (isset($GLOBALS['emoji_maps']['html_to_unified'][$m[1]])){
				return $GLOBALS['emoji_maps']['html_to_unified'][$m[1]];
			}
			return $m[0];
		}, $text);
	}
