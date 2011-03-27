<?
	include('emoji.php');

	header('Content-type: text/plain; charset=UTF-8');


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

	$test_iphone	= "Hello \xEE\x89\x80"; # U+E240
	$test_docomo	= "Hello \xEE\x99\x87"; # U+E647
	$test_kddi	= "Hello \xEE\x92\x90"; # U+E490
	$test_google	= "Hello \xF3\xBE\x80\xAC"; # U+FE02C
	$test_unified	= "Hello \xE2\x99\x89"; # U+2649

	$test_html	= "Hello <span class=\"emoji emoji2649\"></span>";


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

	echo "#------------------\n";

	is(emoji_get_name(9728),	'BLACK SUN WITH RAYS',		"name U+2600");
	is(emoji_get_name(9962),	'CHURCH',			"name U+26EA");
	is(emoji_get_name(128128),	'SKULL',			"name U+1F480");
	is(emoji_get_name(128080),	'OPEN HANDS SIGN',		"name U+1F450");
	is(emoji_get_name(128299),	'PISTOL',			"name U+1F52B");


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

?>
