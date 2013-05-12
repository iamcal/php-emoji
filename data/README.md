Just looking for the library?
=============================

You don't need to worry about the contents of this directory - just use `emoji.php`,
`emoji.css` and `emoji.png` in the parent directory.


I'm a developer, tell me more...
================================

The scripts in this directory allow you to build the library from the unicode.org source materials
and the images from the gemoji project.

The main catalog contains an array of Emoji, each record containing the different codepoints, names, etc.
The CSS catalog contains a mapping of codepoints to positions within the spritesheet image.

We use these intermediate mappings to create the end-user PHP and CSS files.


To rebuild the catalog from the original data tables:

    wget http://www.unicode.org/~scherer/emoji4unicode/snapshot/full.html
    patch < source_html.patch
    php parse.php full.html > catalog.php

To rebuild the spritesheet image and its catalog, from the gemoji source files (this step requires 
ImageMagick or GraphicsMagick for the compositing):

    php build_image.php

You can then use the catalogs to build the PHP map and the CSS file:

    php build_map.php > ../emoji.php
    php build_css.php > ../emoji.css
    php build_table.php > ../table.htm
