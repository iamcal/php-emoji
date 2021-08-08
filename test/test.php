<?php
	include(dirname(__FILE__).'/../lib/emoji.php');

	header('Content-type: text/plain; charset=UTF-8');

	$GLOBALS['failures'] = 0;


	#
	# this code point was picked on purpose. the conversion from unified to
	# various types is roundtrip-capable and the verious code points are
	# unique across all types (i.e. there are no other unified symbols using
	# the same kddi code).
	#
	# the codepoint is also in the unicode standard, so should not change
	# in future revisions, breaking the test (i'm looking at you HAPPY FACE
	# WITH OPEN MOUTH U+1F603).
	#
	# this test uses unified U+2649 - TAURUS
	#

	$test_unified	= "TAURUS ".utf8_bytes(0x2649);
	$test_iphone	= "TAURUS ".utf8_bytes(0xE240);
	$test_docomo	= "TAURUS ".utf8_bytes(0xE647);
	$test_kddi	= "TAURUS ".utf8_bytes(0xE490);
	$test_google	= "TAURUS ".utf8_bytes(0xFE02C);
	
	$test_html	= "TAURUS ".test_html(2649);

	is(emoji_docomo_to_unified($test_docomo),	$test_unified, "DoCoMo -> Unified");
	is(emoji_kddi_to_unified($test_kddi),		$test_unified, "KDDI -> Unified");
	is(emoji_softbank_to_unified($test_iphone),	$test_unified, "Softbank -> Unified");
	is(emoji_google_to_unified($test_google),	$test_unified, "Google -> Unified");

	echo "#------------------\n";

	is(emoji_unified_to_docomo($test_unified),	$test_docomo,	"Unified -> DoCoMo");
	is(emoji_unified_to_kddi($test_unified),	$test_kddi,	"Unified -> KDDI");
	is(emoji_unified_to_softbank($test_unified),	$test_iphone,	"Unified -> Softbank");
	is(emoji_unified_to_google($test_unified),	$test_google,	"Unified -> Google");

	echo "#------------------\n";

	is(emoji_unified_to_html($test_unified),	$test_html,	"Unified -> HTML");
	is(emoji_html_to_unified($test_html),		$test_unified,	"HTML -> Unified");

	echo "#------------------\n";


	#
	# some emoji (e-82C thru e-837 and others) use 2 codepoints in the
	# unified mode, but just one in phone modes. test that it works as
	# expected
	#

	$test_unified	= "KEYCAP 6 ".utf8_bytes(0x36).utf8_bytes(0xFE0F).utf8_bytes(0x20E3);
	$test_iphone	= "KEYCAP 6 ".utf8_bytes(0xE221);
	$test_docomo	= "KEYCAP 6 ".utf8_bytes(0xE6E7);
	$test_kddi	= "KEYCAP 6 ".utf8_bytes(0xE527);
	$test_google	= "KEYCAP 6 ".utf8_bytes(0xFE833);
	
	$test_html	= "KEYCAP 6 <span class=\"emoji-outer emoji-sizer\"><span class=\"emoji-inner emoji36fe0f20e3\"></span></span>";

	is(emoji_docomo_to_unified($test_docomo),	$test_unified, "DoCoMo -> Unified");
	is(emoji_kddi_to_unified($test_kddi),		$test_unified, "KDDI -> Unified");
	is(emoji_softbank_to_unified($test_iphone),	$test_unified, "Softbank -> Unified");
	is(emoji_google_to_unified($test_google),	$test_unified, "Google -> Unified");
	
	echo "#------------------\n";
	
	is(emoji_unified_to_html($test_unified),	$test_html,	"Unified -> HTML");
	is(emoji_html_to_unified($test_html),		$test_unified,	"HTML -> Unified");

	echo "#------------------\n";


	#
	# names are accessed by the unified codepoint (which makes it tricky for 2-codepoint unicode symbols)
	#

	is(emoji_get_name(utf8_bytes(0x2600).utf8_bytes(0xFE0F)),	'BLACK SUN WITH RAYS',		"name U+2600 U+FE0F");
	is(emoji_get_name(utf8_bytes(0x26EA)),	'CHURCH',			"name U+26EA");
	is(emoji_get_name(utf8_bytes(0x1F480)),	'SKULL',			"name U+1F480");
	is(emoji_get_name(utf8_bytes(0x1F450)),	'OPEN HANDS SIGN',		"name U+1F450");
	is(emoji_get_name(utf8_bytes(0x1F52B)),	'PISTOL',			"name U+1F52B");
	is(emoji_get_name(utf8_bytes(0x36).utf8_bytes(0xFE0F). utf8_bytes(0x20E3)),	'KEYCAP 6',	"name U+36 U+FE0F U+20E3");

	echo "#------------------\n";


	#
	# finding emoji works correctly
	#

	is(emoji_contains_emoji('test '.utf8_bytes(0x2600).' test'),			true, "contains simple emoji");
	is(emoji_contains_emoji('test '.utf8_bytes(0x36).utf8_bytes(0xFE0F).utf8_bytes(0x20E3).' test'),	true, "contains compound emoji");
	is(emoji_contains_emoji('hello world'),						false, "does not contain emoji");


	echo "#------------------\n";


	#
	# deal with modifiers correctly
	#

	is(emoji_unified_to_html("\xE2\x9D\xA4"),	test_html('2764fe0f'),		"no modifier");
	is(emoji_unified_to_html("\xE2\x9D\xA4\xEF\xB8\x8F"),	test_html('2764fe0f'),		"image modifier");
	is(emoji_unified_to_html("\xE2\x9D\xA4\xEF\xB8\x8E"),	"\xE2\x9D\xA4\xEF\xB8\x8E",	"text modifier");



	#
	# exit badly if we didn't pass
	#

	exit($GLOBALS['failures'] ? 1 : 0);


	#
	# below here are the test helper functions
	#

	function is($got, $expected, $name){

		$passed = ($got === $expected) ? 1 : 0;

		if ($passed){
			echo "ok # $name\n";
		}else{
			echo "not ok # $name\n";
			echo "# expected : ".byteify($expected)."\n";
			echo "# got      : ".byteify($got)."\n";

			$GLOBALS['failures']++;
		}
	}

	function byteify($s){
		$out = '';
		for ($i=0; $i<strlen($s); $i++){
			$c = ord(substr($s,$i,1));
			if ($c >= 0x20 && $c <= 0x80){
				$out .= chr($c);
			}else{
				$out .= sprintf('0x%02x ', $c);
			}
		}
		return trim($out);
	}

	function utf8_bytes($cp){

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

	function test_html($codepoint){
		return "<span class=\"emoji-outer emoji-sizer\"><span class=\"emoji-inner emoji{$codepoint}\"></span></span>";
	}
