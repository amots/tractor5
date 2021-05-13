<?php

/**
 * Description of breadCrumbs
 *
 * @author amots
 * @date 2021-03-10
 */
class breadCrumbs {

    static function genBreadCrumbs($list) {
        $lastElement = end($list);
        $items = [];
        foreach ($list as $key => $value) {
            if ($value == $lastElement) {
                $items[] = <<<EOF
                    <li class="breadcrumb-item active" aria-current="page">{$value['literal']}</li>
                    EOF;
            } else {
                $items[] = <<<EOF
                    <li class="breadcrumb-item"><a href="{$value['link']}">{$value['literal']}</a></li>
                    EOF;
            }
        }
        $joined = join('', $items);
        return <<<EOF
            <nav aria-label="breadcrumb"><ol class="breadcrumb">{$joined}</ol></nav>
            EOF;
    }

}
