<?php
	header('Content-type: text/plain; charset=UTF-8');

	include('catalog.php');
	include('css_catalog.php');

	echo ".emoji { background: url(\"emoji.png\") top left no-repeat; width: 20px; height: 20px; display: -moz-inline-stack; display: inline-block; vertical-align: top; zoom: 1; *display: inline; }\n";

	foreach ($catalog as $item){

        if (!empty($item['softbank']) && !empty($item['softbank']['unicode']) && !empty($item['softbank']['unicode'][0]) && !empty($map[$item['softbank']['unicode'][0]])) {
		    $pos = $map[$item['softbank']['unicode'][0]];
        }

		$unilow = '';
		foreach ($item['unicode'] as $cp) $unilow .= sprintf('%x', $cp);

		$parts = array();
		foreach ($item['unicode'] as $cp) $parts[] = sprintf('%04x', $cp);
		$key = implode('-', $parts);

		# for some reason, gemoji names 0023-20e3 as just 0023
		if (preg_match('!^(\S{4})-20e3$!', $key, $m)) $key = $m[1];

		$pos = (!empty($css_data[$key])) ? $css_data[$key] : false;
		if (!isset($pos)) $pos = $css_data['2754'];

		echo ".emoji$unilow { background-position: -{$pos[0]}px -{$pos[1]}px; }\n";
	}
?>
