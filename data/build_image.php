<?php
	# find all input images
	$path = dirname(__FILE__).'/gemoji/images/emoji/unicode';
	$files = glob("$path/*");

	# create small versions of each image
	$temp = dirname(__FILE__).'/temp';
	@mkdir($temp);

	echo "Resizing images ";
	$images = array();
	$map = array();
	$y = 0;
	foreach ($files as $file){

		if (!preg_match('!\.png$!', $file)) continue;

		$last = basename($file);
		$target = $temp.'/'.$last;

		exec("convert {$file} -resize 20x20 {$target}", $out, $code);
		if ($code){
			echo 'x';
		}else{
			echo '.';
		}

		$images[] = array($last, $y);
		$map[pathinfo($last, PATHINFO_FILENAME)] = $y;
		$y += 20;
		#echo "$file -> $target\n";
	}
	echo " DONE\n";

	echo "Writing image map ... ";
	$fh = fopen('css_catalog.php', 'w');
	fwrite($fh, '<'.'?php $css_data = ');
	fwrite($fh, var_export($map, true));
	fwrite($fh, ";\n");
	fclose($fh);
	echo "DONE\n";
	#exit;


	echo "Compositing images ";
	$pw = 20;
	$ph = 20 * count($images);
	#echo shell_exec("convert -size {$pw}x{$ph} xc:red {$temp}/sheet.png");
	echo shell_exec("convert -size {$pw}x{$ph} null: -matte -compose Clear -composite -compose Over {$temp}/sheet.png");

	foreach ($images as $image){

		$px = 0;
		$py = $image[1];

		echo shell_exec("composite -geometry +{$px}+{$py} {$temp}/{$image[0]} {$temp}/sheet.png {$temp}/sheet.png");
		echo '.';
	}
	echo " DONE\n";


	echo "Moving final images ";
	move("{$temp}/sheet.png", dirname(__FILE__).'/../emoji.png');
	shell_exec("rm -rf {$temp}/");
	echo " DONE\n";
