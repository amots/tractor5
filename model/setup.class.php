<?php

/**
 * Description of setup
 *
 * @author amots
 * @since 2021-03
 */
class setup {

    static function setHeaders(&$registry) {
        $registry->template->body_param = Lang::getLocale() == 'he' ? 'class="rtl"'
                    : '';
//        $registry->template->ms_auto = Lang::getLocale() == 'he' ? 'ms-auto-left'
//                    : 'ms-auto';
        $registry->template->langLink = Lang::getOtherLocale();
        $langLinks = [];

        $registry->template->langLinks = self::setLangLinks();
        $registry->template->bannerFileName = self::drawBannerImg(__SITE_PATH .'/assets/media/banners');
        $registry->template->headerStuff = NULL;
        $registry->template->footerStuff = NULL;
    }

    static function setLangLinks() {
        foreach (Lang::getOtherLocale() as $lang) {
            $langLiteral = Lang::trans('general.myLang', $lang);
            $langLinks[] = <<<EOF
                <li class="nav-item "><a href="?lang={$lang}" class="nav-link">{$langLiteral}</a></li>                           
                EOF;
        }
        return join('&nbsp;', $langLinks);
    }

    static function drawBannerImg($path) {
       
        $arr = array();
        if ($handle = opendir($path)) {
            while (false !== ($file = readdir($handle))) {
                if ($file != "." && $file != "..") {
                    $ext = strtolower(substr($file, strlen($file) - 3));
                    if ($ext == "jpg") {
                        $arr[] = $file;
                    }
                }
            }
            closedir($handle);
        }
        return $arr[array_rand($arr)];
 
    }

}
