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
	# export the catalog
	#

	echo "<"."?php \$catalog = ";
	var_export($items);
	echo "; ?".">";


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
