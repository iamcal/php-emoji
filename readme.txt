

USAGE
-----

<?php

	include('emoji.php');


	# when you recieve text from a mobile device, convert it
	# to the unified format.

	$data = emoji_docomo_to_unified($data); # DoCoMo devices
	$data = emoji_kddi_to_unified($data); # KDDI & Au devices
	$data = emoji_softbank_to_unified($data); # Softbank & (iPhone) Apple devices
	$data = emoji_google_to_unified($data); # Google Android devices


	# when sending data back to mobile devices, you can
	# convert back to their native format.

	$data = emoji_unified_docomo($data); # DoCoMo devices
	$data = emoji_unified_kddi($data); # KDDI & Au devices
	$data = emoji_unified_softbank($data); # Softbank & (iPhone) Apple devices
	$data = emoji_unified_google($data); # Google Android devices


	# when displaying data to anyone else, you can use HTML
	# to format the emoji.

	$data = emoji_unified_to_html($data);

?>

When using the HTML format, you'll also need to include the emoji.css file, which points to