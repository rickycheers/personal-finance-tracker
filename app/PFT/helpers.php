<?php

/**
 * @param $string
 * @return bool|string
 */
function base64UrlDecode($string)
{
    // First, Base64 needs padding to the end
    // "In Base64 encoding, the length of output encoded String must be a multiple of 3.
    $b64 = $string . str_repeat('=', 3 - (3 + mb_strlen($string, '8bit')) % 4);
    // Base64 uses + and / in place of - and _
    $b64 = strtr($b64, '-_', '+/');
    $decoded = base64_decode($b64, true);
    if ($decoded === false) {
        throw new InvalidArgumentException('Could not decode, invalid data');
    }
    // Return the decoded string
    return $decoded;
}

function cleanSpace($str)
{
    $str = preg_replace("/\s+/", " ", $str);
    $str = preg_replace("/[\pZ\pC]/u"," ", $str); // replace unicode spaces and other unicode chars
    $str = preg_replace("/\s{2,}/", " ", $str);
    $str = trim($str);
    return $str;
}
