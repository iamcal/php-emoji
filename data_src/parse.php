<?php
	#
	# requires php 5.2+
	#


	#
	# open and parse html
	#

	$html = file_get_contents($argv[1]);
	$html = str_ireplace(array('<br>', '<br/>', '<br />'), "\n", $html);

	$doc = new DOMDocument();
	@$doc->loadHTML($html);


	#
	# get an array of <tr>'s we care about
	#

	$all_trs = get_elts_by_tag($doc, 'tr');

	$trs = array();

	foreach ($all_trs as $item){

		if ($item->getAttribute('class') == 'not_in_proposal'){
			continue;
		}

		if (!preg_match('/^e-\w{3}$/', $item->getAttribute('id'))){
			continue;
		}

		if (!(7 === count(get_elts_by_tag($item, 'td')))){
			continue;
		}

		$trs[] = $item;
	}

	fprintf(STDERR, "trs count:" . count($trs)."\n");


	#
	# iterate over the <tr>'s, extracting the data we need
	#

	$items = array();

	foreach ($trs as $tr){

		$tds = get_elts_by_tag($tr, 'td');

		$items[] = array(
			'mapid'		=> parse_mapid($tds[0]),
			'unicode'	=> parse_unicode($tds[1]),
			'char_name'	=> parse_char_name($tds[2]),
			'docomo'	=> parse_mobile($tds[3]),
			'au'		=> parse_mobile($tds[4]),
			'softbank'	=> parse_mobile($tds[5]),
			'google'	=> parse_google($tds[6]),
		);
	}

	fprintf(STDERR, "codepoint count:".count($items)."\n");


	#
	# filter invalid codepoints
	#

	fprintf(STDERR, "filter only_kaomoji ; like e-554 -> [A] -> [A] -> [A] -> [A]\n");
	$items = filter_only_kaomoji($items);
	fprintf(STDERR, "codepoint count:".count($items)."\n");

	fprintf(STDERR, "filter chars-group ; like #44+#139\n");
	$items = filter_chars_group($items);
	fprintf(STDERR, "codepoint count:".count($items)."\n");


	#
	# build the final maps
	#

	$maps = array();

	$maps['names']		= make_names_map($items);
	$maps['kaomoji']	= get_all_kaomoji($items);

	#fprintf(STDERR, "fix Geta Mark ()  '〓' (U+3013)\n");
	#$items = fix_geta_mark($items);

	$maps["unified_to_docomo"]	= make_mapping($items, 'docomo');
	$maps["unified_to_kddi"]	= make_mapping($items, 'au');
	$maps["unified_to_softbank"]	= make_mapping($items, 'softbank');
	$maps["unified_to_google"]	= make_mapping($items, 'google');

	$maps["docomo_to_unified"]	= make_mapping_flip($items, 'docomo');
	$maps["kddi_to_unified"]	= make_mapping_flip($items, 'au');
	$maps["softbank_to_unified"]	= make_mapping_flip($items, 'softbank');
	$maps["google_to_unified"]	= make_mapping_flip($items, 'google');

	$maps["unified_to_html"]	= make_html_map($items);


	#
	# output
	# we could just use var_dump, but we get 'better' output this way
	#

	echo "<"."?php\n";

	echo "\n";
	echo "\t#\n";
	echo "\t# WARNING:\n";
	echo "\t# This code is auto-generated. Do not modify it manually.\n";
	echo "\t#\n";
	echo "\n";

	echo "\t\$GLOBALS['emoji_maps'] = array(\n";

	echo "\t\t'names' => array(\n";

	foreach ($maps[names] as $k => $v){

		$name_enc = "'".AddSlashes($v)."'";
		echo "\t\t\t$k => $name_enc,\n";
	}

	echo "\t\t),\n";

	foreach ($maps as $k => $v){

		if ($k == 'names') continue;

		echo "\t\t'$k' => array(\n";

		$count = 0;
		echo "\t\t\t";
		foreach ($v as $k2 => $v2){
			$count++;
			if ($count % 10 == 0) echo "\n\t\t\t";
			echo format_string($k2).'=>'.format_string($v2).', ';
		}
		echo "\n";

		echo "\t\t),\n";
	}

	echo "\t);\n";


//-----  functions ------------------

function get_elts_by_tag($root, $tagname){

	$nodelist = $root->getElementsByTagName($tagname);
	$result = array();

	$len = $nodelist->length;
	for($i=0; $i<$len; $i++) {
		$result[] = $nodelist->item($i);
	}

	return $result;
}

function parse_mapid($elt) {
	// like <a href="#e-19E">e-19E</a>
	$links = get_elts_by_tag($elt, 'a');
	if(empty($links)) return null;
	return $links[0]->getAttribute("href");
}

function parse_unicode($elt) {
	//like U+1F469
	return get_unicode_char($elt);
}

function parse_char_name($elt) {
	$lines = array_filter(array_map('trim', explode("\n", $elt->textContent)));

	if(count($lines[0])>0) {
		$result['title'] = $lines[0];
		unset($lines[0]);
		$result['desc'] = join("\n", $lines);

		return $result;
	}
	return null;
}

function parse_mobile($elt) {
	if(!has_image($elt)) {
		return array(
			'kaomoji' => trim($elt->textContent)
		);
	}
	else {
		return array(
			'number' => get_number_char($elt) ,
			'unicode'=> get_unicode_char($elt),
			'sjis' => get_sjis_char($elt),
			'jis' => get_jis_char($elt),
			);
	}
}

function parse_google($elt) {
	return array(
		'unicode' => get_unicode_char($elt)
	);
}

function has_image($elt) {
	return count(get_elts_by_tag($elt, 'img')) > 0;
}

function get_unicode_char($elt) {
	$r = preg_match('/(?:.*)U\+(\w{4,5})/u', $elt->textContent, $match);
	return $r ? intval($match[1], 16) : null;
}

function get_sjis_char($elt) {
	$r = preg_match('/\bSJIS-(\w{4})/u', $elt->textContent, $match);
	return $r ? $match[1] : null;
}

function get_jis_char($elt) {
	$r = preg_match('/\bJIS-(\w{4})/u', $elt->textContent, $match);
	return $r ? $match[1] : null;
}

function get_number_char($elt) {
	$r = preg_match('/\#([a-zA-Z0-9\.+-]{1,})/', $elt->textContent, $match);
	return $r ? $match[1] : null;
}



function filter_only_kaomoji($mapping) {
	$result = array();
	foreach($mapping as $map) {

		if(isset($map['docomo']['kaomoji'])
			&& isset($map['au']['kaomoji'])
			&& isset($map['softbank']['kaomoji'])) 
		{
			continue;
		}
		else {
			$result[] = $map;
		}
	}

	return $result;
}


function filter_chars_group($mapping) {
	$result = array();
	foreach($mapping as $map) {

		if( @preg_match('/\+$/', $map['docomo']['number']) 
			|| @preg_match('/\+$/', $map['au']['number']) 
			|| @preg_match('/\+$/', $map['softbank']['number']) 
		){
			continue;
		}
		else {
			$result[] = $map;
		}
	}

	return $result;
}

function fix_geta_mark($mapping) {
	$result = array();

	foreach($mapping as $map) {

		if(isset($map['docomo']['kaomoji']) 
			&& $map['docomo']['kaomoji'] == '〓') {
			$map['docomo']['kaomoji'] = '';
		}

		if(isset($map['au']['kaomoji']) 
			&& $map['au']['kaomoji'] == '〓') {
			$map['au']['kaomoji'] = '';
		}

		if(isset($map['softbank']['kaomoji']) 
			&& $map['softbank']['kaomoji'] == '〓') {
			$map['softbank']['kaomoji'] = '';
		}

		$result[] = $map;
	}

	return $result;
}

function get_all_kaomoji($mapping) {
	$arr = array();

	foreach($mapping as $map) {
		if(isset($map['docomo']['kaomoji']) ) {
			$arr[ $map['docomo']['kaomoji'] ] = '1';
		}

		if(isset($map['au']['kaomoji']) ) {
			$arr[ $map['au']['kaomoji'] ] = '1';
		}

		if(isset($map['softbank']['kaomoji']) ) {
			$arr[ $map['softbank']['kaomoji'] ] = '1';
		}
	}

	return array_keys($arr);
}

	function make_names_map($map){

		$out = array();
		foreach ($map as $row){
			$out[$row['unicode']] = $row['char_name']['title'];
		}

		return $out;
	}

	function make_html_map($map){

		$out = array();
		foreach ($map as $row){

			$hex = sprintf('%x', $row['unicode']);
			$bytes = emoji_utf8_bytes($row['unicode']);

			$out[$bytes] = "<span class=\"emoji emoji$hex\"></span>";
		}

		return $out;
	}

	function make_mapping($mapping, $dest){

		$result = array();

		foreach ($mapping as $map){

			$src_char = emoji_utf8_bytes($map['unicode']);

			if (!empty($map[$dest]['unicode'])){

				$dest_char = emoji_utf8_bytes($map[$dest]['unicode']);
			}else{
				$dest_char = $map[$dest]['kaomoji'];
			}

			$result[$src_char] = $dest_char;
		}

		return $result;
	}

	function make_mapping_flip($mapping, $src){
		$result = make_mapping($mapping, $src);
		$result = array_flip($result);
		unset($result[""]);
		return $result;
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

	function format_string($s){
		$out = ''; 
		for ($i=0; $i<strlen($s); $i++){
			$c = ord(substr($s,$i,1));
			if ($c >= 0x20 && $c < 0x80 && !in_array($c, array(34, 39, 92))){
				$out .= chr($c);
			}else{
				$out .= sprintf('\\x%02x', $c);
			}   
		}   
		return '"'.$out.'"';
	}   

