<?php

class Lang {

    var $registry;
    var $langCookieName = 'tractor[Language]';
    static $availLangs = ['he', 'en'];

    function __construct($registry) {
        $this->registry = $registry;
        if (isset($_REQUEST['lang'])) {
            $this->registry->language = $this->langCode($_REQUEST['lang']);
            setcookie($this->langCookieName, $this->registry->language,
                    time() + (86400 * 30), '/');
        } elseif (isset($_COOKIE['tractor']['Language'])) {
            $this->registry->language = $_COOKIE['tractor']['Language'];
        } else {
            $this->registry->language = 'he';
            setcookie($this->langCookieName, $this->registry->language,
                    time() + (86400 * 30), '/');
        }
        
    }

    private function langCode($lang) {
        return $lang;
    }

    public static function trans($term, $lang = NULL) {
        try {
            if (is_null($lang)) {
                $lang = self::getLocale();
            }
            $termArray = explode('.', $term);
            $key = $termArray[1];
            $filecomp = $termArray[0];
            $filename = $_SERVER['DOCUMENT_ROOT'] . "/assets/lang_resources/{$lang}/{$filecomp}.php";
            @$data = include $filename;
            if ((isset($data[$key])) and ! util::IsNullOrEmptyString($data[$key]) /* sizeof($data[$key]) > 0 */)
                    return $data[$key];
            else return $term;
        } catch (Exception $ex) {
            return $term;
        }
    }

    public static function getLocale() {
        if (isset($_REQUEST['lang'])) {
            return $_REQUEST['lang'];
        } elseif (isset($_COOKIE['tractor']['Language'])) {
            return $_COOKIE['tractor']['Language'];
        } else {
            return 'he';
        }
    }

    public static function getOtherLocale() {
        $langList = [];
        $currentLocale = isset($registry->locale) ? $registry->locale : self::getLocale();
        foreach (self::$availLangs as $lang) {
            if (!($lang == $currentLocale)) {
                $langList[] = $lang;
            }
        }
        return ($langList);
    }

}

?>