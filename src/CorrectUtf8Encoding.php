<?php

/**
 * Copyright 2019 API Skeletons <contact@apiskeletons.com>
 * Copyright 2019 Tom H Anderson <tom.h.anderson@gmail.com>
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * * The above copyright notice and this permission notice shall be included in
        all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED,
 * INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A
 * PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR
 * COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN
 * AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
 * WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */

namespace ApiSkeletons\Utf8;

use Exception;

final class CorrectUtf8Encoding
{
    /**
     * Given a valid or invalid UTF8 string parse it and convert
     * all UTF8 sequences into UTF8 characters while leaving valid
     * UTF8 characters alone.
     */
    public function __invoke($input, $counter = 0)
    {
        $length = mb_strlen($input);
        $return = '';
        $character = '';
        $showProgress = false;

        // Chunk string into working strings
        // mb_ functions are slow on large strings
        $chunkSize = 100;
        $stringChunks = mb_split('', $input, $chunkSize);

        $chunkCount = sizeof($stringChunks);
        $workingString = array_shift($stringChunks);
        while (mb_strlen($workingString)) {
            // If the working string is < 100 add the next chunk to the string
            if (mb_strlen($workingString) < 10) {
                if ($stringChunks) {
                    $workingString .= array_shift($stringChunks);
                }
            }

            // Fetch one UTF8 character
            $character = mb_substr($workingString, 0, 1, 'UTF-8');

            $characterUtf32BE= @mb_convert_encoding($character, 'UTF-32BE', 'UTF-8');
            if (!$characterUtf32BE) {
                throw new Exception('invalid character' . "\n" . $character . "\n" . $input);
            }
            $characterCode = hexdec(bin2hex($characterUtf32BE));

            $bytes = 1;
            $multibyte = false;

            // If this character defines bytes then it
            // could be a correct character or it could
            // be the start of an invalid sequence.
            if ($characterCode >= 0xf0 && $characterCode <= 0xf7) {
                $bytes = 4;
            } else if ($characterCode >= 0xe0 && $characterCode <= 0xef) {
                $bytes = 3;
            } else if ($characterCode >= 0xc0 && $characterCode <= 0xdf) {
                $bytes = 2;
            }

            // Convert invalid chars to utf8
            $i = 0;
            $characterLength = 1;
            while ($bytes > 1) {
                $i++;

                $nextCharacter = mb_substr($workingString, $i, 1, 'UTF-8');
                $nextCharacterUtf32BE = mb_convert_encoding($nextCharacter, 'UTF-32BE', 'UTF-8');
                $nextCharacterCode = hexdec(bin2hex($nextCharacterUtf32BE));

                // Does the original character stands alone and is not an invalid byte sequence?
                if ($nextCharacterCode < 0x80) {
                    // Yes, stand alone.  $character is correctly encoded and $nextCharacter is re-enqueued
                    // to be correctly encoded too.  This character is within the byte range of the
                    // first were the first invalid.
                    break;
                }

                $character .= $nextCharacter;
                $multibyte = true;
                $bytes--;
            }

            if ($multibyte) {
                // Try Windows-125X charsets
                $newUtf8Char = @mb_convert_encoding($character, 'Windows-1252');
                $checkUtf8Char = @mb_substr($newUtf8Char, 0, 1, 'UTF-8');
                $characterLength = mb_strlen($character);

                if ($checkUtf8Char) {
                    // Successfully restored a Windwows-125x character
                    $character = mb_convert_encoding($newUtf8Char, 'UTF-8');
                } else {
                    // Check UTF8

                    // If these functions fail then the derived encoding found is invalid
                    // and the string is one or more valid utf8 characters together
                    $newUtf8Char = @mb_convert_encoding($character, "ISO-8859-1");
                    $checkUtf8Char = @mb_substr($newUtf8Char, 0, 1, 'UTF-8');
                    $characterLength = mb_strlen($character);

                    if ($checkUtf8Char) {
                        // Successfully restored a UTF8 character
                        $newUtf8Char = @mb_convert_encoding($newUtf8Char, 'UTF-8');

                        if ($newUtf8Char) {
                            $character = $newUtf8Char;
                            $workingString = mb_substr($workingString, mb_strlen($newUtf8Char) - 1);

                            $characterLength = mb_strlen($character);
                        } else {
                            throw new Exception("Error \n$string\n[$newUtf8Char]");
                        }
                    }
                }
            }

            // $character may be multiple characters by this point
            $workingString = mb_substr($workingString, $characterLength);
            $return .= $character;

        }

        // Re-run to fix double or more encodings
        if ($input !== $return) {
            $counter ++;

            if ($counter > 5) {
                throw new Exception("$return \n $input \n break "
                    . "because 5 iterations of convertToUtf8 were ran.");
            }

            return $this->__invoke($return, $counter);
        }

        return $return;
    }
}
