#!/bin/bash
cp -f emoji-data/sheet_apple_64.png ../lib/emoji.png
php build_map.php > ../lib/emoji.php
php build_css.php > ../lib/emoji.css
php build_table.php > ../demo/table.htm
