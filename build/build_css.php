<?php
	header('Content-type: text/plain; charset=UTF-8');

	$in = file_get_contents('emoji-data/emoji.json');
	$catalog = json_decode($in, true);

	echo file_get_contents('inc.css');

	$max = 0;
	foreach ($catalog as $item){
		$max = max($item['sheet_x'], $max);
		$max = max($item['sheet_y'], $max);
	}
	$fact = 100 / $max;

	$sheet_size = $max + 1;
	echo "span.emoji-inner { background-size: {$sheet_size}00%; }\n";

	foreach ($catalog as $item){

		$unilow = unicode_hex_chars($item['unified']);

		$pos_x = $item['sheet_x'] * $fact;
		$pos_y = $item['sheet_y'] * $fact;

		echo ".emoji$unilow { background-position: {$pos_x}% {$pos_y}% !important; }\n";
	}

	function unicode_hex_chars($str){

		$out = '';

		$cps = explode('-', $str);
		foreach ($cps as $cp){
			$out .= sprintf('%x', hexdec($cp));
		}

		return $out;
	}

