<?php
    # handle CLI param(s)
    $is_vertical = (in_array('--grid',$argv) || in_array('-g',$argv)) ? false : true;

	# find all input images
	$path = dirname(__FILE__).'/gemoji/images/emoji/unicode';
	$files = glob("$path/*");

    $icon_size = 20;

	# create small versions of each image
	$temp = dirname(__FILE__).'/temp';
	@mkdir($temp);

	echo "Resizing images ";
	$images = array();
	foreach ($files as $file){

		if (!preg_match('!\.png$!', $file)) continue;

		$last = basename($file);
		$target = $temp.'/'.$last;

		exec("convert {$file} -define filter:blur=0.4 -filter Gaussian -resize {$icon_size}x{$icon_size} {$target}", $out, $code);
		if ($code){
			echo 'x';
		}else{
			echo '.';
		}

		$images[] = array($last);
		#echo "$file -> $target\n";
	}
	echo " DONE\n";


	# quick step - decide on images dimensions and icon
	# positions
	$map = array();
	$y = 0;
	$x = 0;
	$num = ceil(sqrt(count($images)));
	foreach ($images as $k => $v){
		$images[$k][1] = $x * $icon_size;
		$images[$k][2] = $y * $icon_size;
		$map[pathinfo($v[0], PATHINFO_FILENAME)] = array($x*$icon_size, $y*$icon_size);

		$y++;
        if (!$is_vertical && $y == $num) {
            $x++;
            $y = 0;
        }
	}


	echo "Writing image map ... ";
	$fh = fopen('css_catalog.php', 'w');
	fwrite($fh, '<'.'?php $css_data = ');
	fwrite($fh, var_export($map, true));
	fwrite($fh, ";\n");
	fclose($fh);
	echo "DONE\n";
	#exit;


	echo "Compositing images ";
	$pw = ($is_vertical) ? $icon_size : $num*$icon_size;
	$ph = ($is_vertical) ? $y*$icon_size : $num*$icon_size;

	echo shell_exec("convert -size {$pw}x{$ph} xc:none {$temp}/sheet.png"); // easier, didn't throw errors with default IM install
	//echo shell_exec("convert -size {$pw}x{$ph} null: -matte -compose Clear -composite -compose Over {$temp}/sheet.png");

	foreach ($images as $image){

		$px = $image[1];
		$py = $image[2];

		echo shell_exec("composite -geometry +{$px}+{$py} {$temp}/{$image[0]} {$temp}/sheet.png {$temp}/sheet.png");
		echo '.';
	}
	echo " DONE\n";

	echo shell_exec("convert -quality 90 {$temp}/sheet.png png32:{$temp}/sheet2.png");

	echo "Moving final images ";
	rename("{$temp}/sheet2.png", dirname(__FILE__).'/../emoji.png');
	shell_exec("rm -rf {$temp}/");
	echo " DONE\n";
