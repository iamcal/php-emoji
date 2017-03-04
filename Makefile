all:
	cd build && ./build.sh
	@php test/test.php
