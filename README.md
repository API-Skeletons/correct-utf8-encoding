Correct utf-8 Encoding
======================

When utf-8 data is mishandled it can become multiple encoded.  Data with invalid utf-8 sequences cannot be properly imported to databases and renders incorrectly in a correctly encoded web page.

The class provided by this module examines a string byte-by-byte.  It does not use predefined sequences to match against data.  Instead it walks through the string looking for possible utf8 data and looping on the data until it produces a valid utf-8 character.


Use
---

use ApiSkeletons\Utf8;

```php
$correctUtf8Encoding = new Utf8\CorrectUtf8Encoding();

$validString = $correctUtf8Encoding($invalidString);
```

Comment
-------

This library is unlike the other UTF8 correction tools available on packagist
at the time of this writing.  Whether this tool is better than other offerings is based soley on your success with the tool.  For my needs this tool corrected a 20 year old dataset for the entire database field-by-field and byte-by-byte.

Please don't hesitate to contact <contact@apiskeletons.com> with any stories of success or failure with this tool.
