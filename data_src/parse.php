<?php

//require php 5.3.0+

$doc = new DOMDocument();
@$doc->loadHTML(str_ireplace(
					array('<br>', '<br/>', '<br />'),
					"\n",
					file_get_contents($argv[1])));

$trs = get_elts_by_tag($doc, 'tr');

$trs = filter_els($trs, array(
		function ($item){
			return cond_attr_not($item, 'class', 'not_in_proposal');
		} ,
		function ($item){
			return cond_attr_match($item, 'id', '/^e-\w{3}$/');
		} ,
		function ($item){
			return 7 === count(get_elts_by_tag($item, 'td')) ;
		} ,
	));

fprintf(STDERR, "trs count:" . count($trs)."\n");

$mapping = array();

foreach($trs as $tr) {
	$map = array();

	$tds = get_elts_by_tag($tr, 'td');

	$map['mapid'] = parse_mapid($tds[0]);
	$map['unicode'] = parse_unicode($tds[1]);
	$map['char_name'] = parse_char_name($tds[2]);
	$map['docomo'] = parse_mobile($tds[3]);
	$map['au'] = parse_mobile($tds[4]);
	$map['softbank'] = parse_mobile($tds[5]);
	$map['google'] = parse_google($tds[6]);

	$mapping[] = $map;
}

fprintf(STDERR, "mapping count:" . count($mapping)."\n");

//filter invalid mapping
fprintf(STDERR, "filter only_kaomoji ; like e-554 -> [A] -> [A] -> [A] -> [A]\n");
$mapping = filter_only_kaomoji($mapping);
fprintf(STDERR, "mapping count:" . count($mapping)."\n");

fprintf(STDERR, "filter chars-group ; like #44+#139\n");
$mapping = filter_chars_group($mapping);
fprintf(STDERR, "mapping count:" . count($mapping)."\n");

$emoji_maps['kaomoji'] = get_all_kaomoji($mapping);

#fprintf(STDERR, "fix Geta Mark ()  '〓' (U+3013)\n");
#$mapping = fix_geta_mark($mapping);

//export mapping array
$emoji_maps["unified_to_docomo"] = make_mapping($mapping, 'unicode', 'docomo');
$emoji_maps["unified_to_kddi"] = make_mapping($mapping, 'unicode', 'au');
$emoji_maps["unified_to_softbank"] = make_mapping($mapping, 'unicode', 'softbank');
$emoji_maps["unified_to_google"] = make_mapping($mapping, 'unicode', 'google');
$emoji_maps["docomo_to_unified"] = make_mapping_flip($mapping, 'unicode', 'docomo');
$emoji_maps["kddi_to_unified"] = make_mapping_flip($mapping, 'unicode', 'au');
$emoji_maps["softbank_to_unified"] = make_mapping_flip($mapping, 'unicode', 'softbank');
$emoji_maps["google_to_unified"] = make_mapping_flip($mapping, 'unicode', 'google');

echo '<'.'?'.'php'."\n";
echo '$GLOBALS["emoji_maps"] = ';
echo	my_var_export($emoji_maps);
echo ";\n";


//-----  functions ------------------

function filter_els($elts, $conds) {
	$result = array();
	foreach($elts as $elt) {
		$bl = true;

		foreach($conds as $func) 
				if(! $func($elt) ) {
					$bl = false;
					break;
				}

		if($bl) {
			$result[] = $elt;
		}
	}
	return $result;
}

function cond_attr_not($item, $name, $value) {
	$attr = $item->getAttribute($name);
	return $attr != $value;
}

function cond_attr_match($item, $name, $regex) {
	$attr = $item->getAttribute($name);
	return preg_match($regex, $attr) ;
}

function node_list_to_arr($nodelist) {
	$result = array();
	$len = $nodelist->length;
	for($i=0; $i<$len; $i++) {
		$result[] = $nodelist->item($i);
	}
	return $result;
}

function get_elts_by_tag($root, $tagname) {
	return node_list_to_arr(
		$root->getElementsByTagName($tagname));
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
	$r = preg_match('/U\+(\w{4,5})/u', $elt->textContent, $match);
	return $r ? $match[1] : null;
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

function make_mapping($mapping, $unicode_key, $dest) {
	
	$result = array();

	foreach($mapping as $map) {
		$src_code = intval($map[$unicode_key] , 16);
		$src_char = int2utf8($src_code);
		//debug end

		//debug
//		$src_char = $map[$unicode_key];

		if(!empty( $map[$dest]['unicode'] )) {
			$dest_code = intval($map[$dest]['unicode'], 16);
			$dest_char = int2utf8($dest_code);

			//debug
//			$dest_char = $map[$dest]['unicode'];
		}
		else {
			$dest_char = $map[$dest]['kaomoji'];
		}

		$result[$src_char] = $dest_char ;
	}

	return $result;
}

function make_mapping_flip($mapping, $unicode_key, $src) {
	$result = make_mapping($mapping, $unicode_key, $src);
	$result = array_flip($result);
	unset($result[""]);
	return $result;
}

function int2utf8($code) {
	$iByte = 0;
	$i = 0;
	$result = "";

	while($code > 0x7f)	{
		$iByte = $code % 0x40;
		$code = ($code - $iByte)/0x40;
		$result =  chr($iByte|0x80) . $result;
		$i++;
	}

	$prefix_arr = array(0x0, 0xc0, 0xe0, 0xf0, 0xf8, 0xfc);

	if($i > count($prefix_arr))	{
		$i = 5;
	}

	$result= chr($code|$prefix_arr[$i]) . $result;
	return $result;
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

function my_var_export($arr) {
	$tab = "\t\t";
	$str = "array(\n";
	foreach($arr as $name => $value) {
		$str .= $tab ;
		$str .= format_string($name). "\t=>\t" ;
		if(is_array($value)) {
			$exp = my_var_export($value) ;
			$str .= preg_replace('/^/', $tab, $exp) ."\t,\n";
		}
		elseif(is_object($value)) {
			$exp = var_export($value, true) . "\t,\n";
			$str .= preg_replace('/^/', $tab, $exp);
		}
		else {
			$str .= format_string($value). "\t,\n" ;
		}
	}
	$str .= "\t)\n";
	return $str;
}
