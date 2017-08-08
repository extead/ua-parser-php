<?php

namespace Extead\UAParser;

/**
 * Class Util
 * @package Extead\UAParser
 */
class Util
{
    public function extend($regexes, $extensions) {
        $margedRegexes = [];
        foreach ($regexes as $key => $item) {
            if (isset($extensions[$key]) && count($extensions[$key]) % 2 === 0) {
                $margedRegexes[$key] = array_merge($extensions[$key], $regexes[$key]);
            } else {
                $margedRegexes[$key] = $regexes[$key];
            }
        }
        return $margedRegexes;
    }

    public function has($str1, $str2) {
        if (is_string($str1)) {
            return strpos(strtolower($str2), strtolower($str1)) !== false;
        } else {
            return false;
        }
    }

    public function lowerize($str) {
        return strtolower($str);
    }

    public function major($version) {
        return is_string($version) ? explode(".", preg_replace('/[^\d\.]/', '', $version))[0] : null;
    }

    public function trim($str) {
        return preg_replace('/^[\s\uFEFF\xA0]+|[\s\uFEFF\xA0]+$/', '', $str);
    }
}