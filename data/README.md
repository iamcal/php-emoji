Just looking for the library?
=============================

You don't need to worry about the contents of this directory - just use <code>emoji.php</code> and <code>emoji.css</code> in the parent directory.


I'm a developer, tell me more...
================================

The scripts in this directory allow you to build the library from the unicode.org source materials.

The catalog contains an array of Emoji, each record containing the different codepoints, names, etc.

We use this intermediate mapping to create the end-user PHP and CSS files.


To rebuild the catalog from the original data tables:

    wget http://www.unicode.org/~scherer/emoji4unicode/snapshot/full.html
    patch < source_html.patch
    php parse.php full.html > catalog.php

You can then use the catalog to build the PHP map and the CSS file:

    php build_map.php > ../emoji.php
    php build_css.php > ../emoji.css
    php build_table.php > ../table.htm
