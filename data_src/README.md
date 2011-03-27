To rebuild the catalog and map from the original dtables

    wget http://www.unicode.org/~scherer/emoji4unicode/snapshot/full.html
    patch < source_html.patch
    php parse.php full.html > catalog.php
    php build.php > ../emoji_map.php

Then you'll want to rebuild the CSS

    cd ../css_data
    php parse.php > ../emoji.css
