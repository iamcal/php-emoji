Just looking for the library?
=============================

You don't need to worry about the contents of this directory - just use `emoji.php`,
`emoji.css` and `emoji.png` in the parent directory.


I'm a developer, tell me more...
================================

The scripts in this directory allow you to build the library from the emoji-data source material.

The emoji-data repo contains a list of emoji with supporting images (and spritesheets).

We use this data to build the PHP map and the CSS file:

    ./build.sh

The following files are created by this process:

    ../emoji.png
    ../emoji.php
    ../emoji.css
    ../table.htm
