<?php

namespace Extead\UAParser;

/**
 * Class Util
 * @package Extead\UAParser
 */
class Util
{
    /**
     * @param $regexes
     * @param $extensions
     * @return array
     */
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

    /**
     * @param $str1
     * @param $str2
     * @return bool
     */
    public function has($str1, $str2) {
        if (is_string($str1)) {
            return strpos(strtolower($str2), strtolower($str1)) !== false;
        } else {
            return false;
        }
    }

    /**
     * @param $str
     * @return string
     */
    public function lowerize($str) {
        return strtolower($str);
    }

    /**
     * @param $version
     * @return null
     */
    public function major($version) {
        return is_string($version) ? explode(".", preg_replace('/[^\d\.]/', '', $version))[0] : null;
    }

    /**
     * @param $str
     * @return mixed
     */
    public function trim($str) {
        return preg_replace('/^[\s\xFEFF\xA0]+|[\s\xFEFF\xA0]+$/', '', $str);
    }
}