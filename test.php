<?
	include('emoji.php');

	header('Content-type: text/plain; charset=UTF-8');


	#
	# one test will fail on docomo since i picked a dumb non-roundtrip codepoint
	# to test with. oops
	#

	$test_iphone	= "Hello \xEE\x90\x94";	# U+E414
	$test_docomo	= "Hello \xEE\x9B\xB0"; # U+E6F0
	$test_kddi	= "Hello \xEE\x93\xBB"; # U+E4FB
	$test_unified	= "Hello \xE2\x98\xBA"; # U+263A

	$test_html	= "Hello <span class=\"emoji emoji263a\"></span>";


	is(emoji_docomo_to_unified($test_docomo),	$test_unified, "DoCoMo -> Unified (bad test!)");
	is(emoji_kddi_to_unified($test_kddi),		$test_unified, "KDDI -> Unified");
	is(emoji_softbank_to_unified($test_iphone),	$test_unified, "Softbank -> Unified");

	is(emoji_unified_to_docomo($test_unified),	$test_docomo,	"Unified -> DoCoMo");
	is(emoji_unified_to_kddi($test_unified),	$test_kddi,	"Unified -> KDDI");
	is(emoji_unified_to_softbank($test_unified),	$test_iphone,	"Unified -> Softbank");

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
				$out .= sprintf('0x%02x', $c);
			}
		}
		return $out;
	}

?>