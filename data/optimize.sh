#!/bin/sh

optipng -zc1-9 -zm1-9 -zs0-3 -f0-5 -out ../emoji_optipng.png ../emoji.png

pngcrush -brute ../emoji.png ../emoji_pngcrush.png

pngout-static -y ../emoji.png ../emoji_pngout.png

