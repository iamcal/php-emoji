<?php
$in = file_get_contents('emoji-data/emoji.json');
$catalog = json_decode($in, true);


#
# build the final maps
#

$maps = array();
$maps["unified_to_html"] = make_html_map($catalog);
$maps["unified_rx"] = make_html_rx($maps["unified_to_html"]);

#
# output
# we could just use var_dump, but we get 'better' output this way
#

echo "<" . "?php\n";
echo file_get_contents('inc.php');
exportJson($maps);
##########################################################################################

function exportJson($jsonData)
{
    $jsonData = json_encode($jsonData);
    file_put_contents('../lib/emoji-map.json', $jsonData);
}

function get_all_kaomoji($mapping)
{
    $arr = array();

    foreach ($mapping as $map) {
        if (isset($map['docomo']['kaomoji'])) {
            $arr[$map['docomo']['kaomoji']] = '1';
        }

        if (isset($map['au']['kaomoji'])) {
            $arr[$map['au']['kaomoji']] = '1';
        }

        if (isset($map['softbank']['kaomoji'])) {
            $arr[$map['softbank']['kaomoji']] = '1';
        }
    }

    return array_keys($arr);
}

function make_names_map($map)
{
    $out = array();
    foreach ($map as $row) {

        if (!empty($row['skin_variations'])) {
            foreach ($row['skin_variations'] as $skin_variation) {

                $bytes = unicode_bytes($skin_variation['unified']);

                $key_enc = format_string($bytes);

                $out[$bytes] = !empty($skin_variation['name']) ? $skin_variation['name'] : $row['name'];
//                $out[$key_enc] = AddSlashes(!empty($skin_variation['name']) ? $skin_variation['name'] : $row['name']);
            }
        }

        $bytes = unicode_bytes($row['unified']);

//        $key_enc = format_string($bytes);
//        $name_enc = AddSlashes($row['name']);

//        $out[$key_enc] = $name_enc;
        $out[$bytes] = $row['name'];
    }

    return $out;
}

function make_html_map($map)
{
    $out = array();
    foreach ($map as $row) {
        if (isset($row['skin_variations'])) {
            foreach ($row['skin_variations'] as $variation) {
                $hex = unicode_hex_chars($variation['unified']);
                $bytes = unicode_bytes($variation['unified']);

                $out[$bytes] = $hex;
            }
        }

        $hex = unicode_hex_chars($row['unified']);
        $bytes = unicode_bytes($row['unified']);

        $out[$bytes] = $hex;

        if ($row['non_qualified']) {
            $bytes = unicode_bytes($row['non_qualified']);

            $out[$bytes] = $hex;
        }
    }

    return $out;
}

function make_html_rx($map)
{
    $rx_bits = $rx_bits_ = array();

    foreach ($map as $bytes => $hex) {

        $out = '';
        for ($i = 0, $iMax = strlen($bytes); $i < $iMax; $i++) {
            $c = ord($bytes[$i]);
            $out .= sprintf('\\x%02x', $c);
        }

        $rx_bits[] = $out;
    }

    usort($rx_bits, function ($a, $b) {
        $a = strlen($a);
        $b = strlen($b);

        if ($a == $b) {
            return 0;
        }
        if ($a < $b) {
            return 1;
        }
        if ($a > $b) {
            return -1;
        }

        return false;
    });

    $rx_bits_chunk = array_chunk($rx_bits, 1000);

    foreach ($rx_bits_chunk as $item) {
        $rx_bits_[] = '!(' . implode('|', $item) . ')(\\xEF\\xB8\\x8E|\\xEF\\xB8\\x8F)?!';
    }

    return $rx_bits_;
}

function make_mapping($mapping, $dest)
{

    $result = array();

    foreach ($mapping as $map) {

        if (!empty($map['skin_variations'])) {
            foreach ($map['skin_variations'] as $skin_variation) {

                $src_char = unicode_bytes($skin_variation['unified']);

                if (!empty($skin_variation[$dest])) {

                    $dest_char = unicode_bytes($skin_variation[$dest]);
                } else {
                    $dest_char = '';
                }

                $result[$src_char] = $dest_char;
            }
        }

        $src_char = unicode_bytes($map['unified']);

        if (!empty($map[$dest])) {

            $dest_char = unicode_bytes($map[$dest]);
        } else {
            $dest_char = '';
        }

        $result[$src_char] = $dest_char;
    }

    return $result;
}

function make_mapping_flip($mapping, $src)
{
    $result = make_mapping($mapping, $src);
    $result = array_flip($result);
    unset($result[""]);
    return $result;
}

function unicode_bytes($str)
{

    $out = '';

    $cps = explode('-', $str);
    foreach ($cps as $cp) {
        $out .= emoji_utf8_bytes(hexdec($cp));
    }

    return $out;
}

function unicode_hex_chars($str)
{

    $out = '';

    $cps = explode('-', $str);
    foreach ($cps as $cp) {
        $out .= sprintf('%x', hexdec($cp));
    }

    return $out;
}

function emoji_utf8_bytes($cp)
{

    if ($cp > 0x10000) {
        # 4 bytes
        return chr(0xF0 | (($cp & 0x1C0000) >> 18)) .
            chr(0x80 | (($cp & 0x3F000) >> 12)) .
            chr(0x80 | (($cp & 0xFC0) >> 6)) .
            chr(0x80 | ($cp & 0x3F));
    }

    if ($cp > 0x800) {
        # 3 bytes
        return chr(0xE0 | (($cp & 0xF000) >> 12)) .
            chr(0x80 | (($cp & 0xFC0) >> 6)) .
            chr(0x80 | ($cp & 0x3F));
    }

    if ($cp > 0x80) {
        # 2 bytes
        return chr(0xC0 | (($cp & 0x7C0) >> 6)) .
            chr(0x80 | ($cp & 0x3F));
    }

    # 1 byte
    return chr($cp);
}

function format_string($s)
{
    $out = '';
    for ($i = 0, $iMax = strlen($s); $i < $iMax; $i++) {
        $c = ord(substr($s, $i, 1));
        if ($c >= 0x20 && $c < 0x80 && !in_array($c, array(34, 39, 92))) {
            $out .= chr($c);
        } else {
            $out .= sprintf('\\x%02x', $c);
        }
    }
    return '"' . $out . '"';
}

function fetch_prefixes($map, $length = 2)
{
    $result = array();
    foreach ($map as $symbol => $junk) {
        $result[substr($symbol, 0, $length)] = 1;
    }

    return array_keys($result);
}
