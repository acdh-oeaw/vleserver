<?php
namespace wde\V2\Rest\Entries;

use ArrayObject;

/**
 * Callback helper 
 */
function chr_utf8_callback($matches) {
    return chr_utf8(hexdec($matches[1]));
}

/**
 * Multi-byte chr(): Will turn a numeric argument into a UTF-8 string.
 * 
 * @param mixed $num
 * @return string
 */
function chr_utf8($matches) {
    $num = $matches[1];
    if ($num < 128) {
        return chr($num);
    }
    if ($num < 2048) {
        return chr(($num >> 6) + 192) . chr(($num & 63) + 128);
    }
    if ($num < 65536) {
        return chr(($num >> 12) + 224) . chr((($num >> 6) & 63) + 128) . chr(($num & 63) + 128);
    }
    if ($num < 2097152) {
        return chr(($num >> 18) + 240) . chr((($num >> 12) & 63) + 128) . chr((($num >> 6) & 63) + 128) . chr(($num & 63) + 128);
    }
    return '';
}

class EntriesEntity extends ArrayObject
{
    /**
 * Decodes all HTML entities, including numeric and hexadecimal ones.
 * 
 * Helper function to fully decode html entities including numeric entities
 * see http://stackoverflow.com/questions/2764781/how-to-decode-numeric-html-entities-in-php
 * @param string|array $string A string or array of strings that should be decoded into UTF-8. 
 * @param const $flags Flags used by html_entity_decode see it's documentation
 * @param string $charset Charset used by html_entity_decode. Noter: Other replcements are UTF-8 only.
 * @return string UTF-8 encoded string.
 */
protected function html_entity_decode_numeric($string, $flags = NULL, $charset = "UTF-8") {
    if (!isset($flags)) {
        $flags = (ENT_COMPAT | ENT_HTML401);
    }
    $namedEntitiesDecoded = html_entity_decode($string, $flags, $charset);
    $hexEntitiesDecoded = preg_replace_callback('~&#x([0-9a-fA-F]+);~i', "\\wde\\V2\\Rest\\Entries\\chr_utf8_callback", $namedEntitiesDecoded);
    $decimalEntitiesDecoded = preg_replace_callback('~&#([0-9]+);~', '\\wde\\V2\\Rest\\Entries\\chr_utf8', $hexEntitiesDecoded);
    return $decimalEntitiesDecoded;
}

    protected function convertCharly($in) {
        $in = str_replace('#8#', '&#', $in);
        $in = str_replace('#9#', ';', $in);
        $in = str_replace('#9#', ';', $in);
        $in = str_replace('%gt;', '&gt;', $in);
        $in = str_replace('%lt;', '&lt;', $in);
        $in = $this->html_entity_decode_numeric($in);
        return $in;
    }
    public function exchangeArray($input) {
        foreach (array_keys($input) as $item) {
            $input[$item] = $this->convertCharly($input[$item]);
        }
        parent::exchangeArray($input);
    }
}
