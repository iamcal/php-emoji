<?
	include('emoji.php');

	header('Content-type: text/plain; charset=UTF-8');


	#
	# this code point was picked on purpose. the conversion from unified to
	# various types is roundtrip-capable, but the same codepoint in e.g.
	# DoCoMo is also used for other unified characters.
	#

	$test_iphone	= "Hello \xee\x81\x97"; # U+E057
	$test_docomo	= "Hello \xee\x9b\xb0"; # U+E6F0
	$test_kddi	= "Hello \xee\x91\xb1"; # U+E471
	$test_google	= "Hello \xf3\xbe\x8c\xb0"; # U+FE330
	$test_unified	= "Hello \xf0\x9f\x98\x90"; # U+1F610

	$test_html	= "Hello <span class=\"emoji emoji1f610\"></span>";


	is(emoji_docomo_to_unified($test_docomo),	$test_unified, "DoCoMo -> Unified (bad test!)");
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