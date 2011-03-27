To rebuild the catalog from the original data tables:

    wget http://www.unicode.org/~scherer/emoji4unicode/snapshot/full.html
    patch < source_html.patch
    php parse.php full.html > catalog.php

You can then use the catalog to build the PHP map and the CSS file:

    php build_map.php > ../emoji_map.php
    php build_css.php > ../emoji.css
