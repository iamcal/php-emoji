# php-emoji - Process Emoji in PHP

This is a PHP library for dealing with Emoji, allowing you to convert between various native formats and displaying them using HTML.

You can read more about it and see a demo here: http://www.iamcal.com/emoji-in-web-apps/

The data this library is based on comes from another GitHub project: https://github.com/iamcal/emoji-data


## USAGE
```php
<?php
include('emoji.php');


# when you recieve text from a mobile device, convert it
# to the unified format.

$data = emoji_docomo_to_unified($data);   # DoCoMo devices
$data = emoji_kddi_to_unified($data);     # KDDI & Au devices
$data = emoji_softbank_to_unified($data); # Softbank & pre-iOS6 Apple devices
$data = emoji_google_to_unified($data);   # Google Android devices


# when sending data back to mobile devices, you can
# convert back to their native format.

$data = emoji_unified_to_docomo($data);   # DoCoMo devices
$data = emoji_unified_to_kddi($data);     # KDDI & Au devices
$data = emoji_unified_to_softbank($data); # Softbank & pre-iOS6 Apple devices
$data = emoji_unified_to_google($data);   # Google Android devices


# when displaying data to anyone else, you can use HTML
# to format the emoji.

$data = emoji_unified_to_html($data);

# if you want to use an editor(i.e:wysiwyg) to create the content, 
# you can use html_to_unified to store the unified value.

$data = emoji_html_to_unified(emoji_unified_to_html($data));
```

When using the HTML format, you'll also need to include the <code>emoji.css</code> file, which points 
to the <code>emoji.png</code> image.

IMPORTANT NOTE: This library currently only deals with UTF-8. If your source data is JIS
or Shift-JIS, you're out of luck for the moment.


## Credits

By Cal Henderson <cal@iamcal.com>

Images and Emoji data come from <a href="https://github.com/iamcal/emoji-data">emoji-data</a>.

This work is dual-licensed under the GPL v3 and the MIT license.


## Version History

* v1.0.0 - 2009-10-20 : First release
* v1.2.0 - 2011-03-27 : ?
* v1.3.0 - 2011-07-27 : ?
* v1.4.0 - 2016-02-15 : Switch to using emoji-data as the backend, at v2.4.0
