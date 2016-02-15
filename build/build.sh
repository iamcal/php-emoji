#!/bin/bash
cp -f emoji-data/sheet_apple_64.png ../emoji.png
php build_map.php > ../emoji.php
php build_css.php > ../emoji.css
php build_table.php > ../table.htm
