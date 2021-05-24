<?php

/**
 * Description of listItems
 *
 * @author amots
 */
class list_items
{

    private $list, $cols, $pages, $titles;
    static public $biPencilSquare = <<<EOF
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-pencil-square" viewBox="0 0 16 16">
        <path d="M15.502 1.94a.5.5 0 0 1 0 .706L14.459 3.69l-2-2L13.502.646a.5.5 0 0 1 .707 0l1.293 1.293zm-1.75 2.456l-2-2L4.939 9.21a.5.5 0 0 0-.121.196l-.805 2.414a.25.25 0 0 0 .316.316l2.414-.805a.5.5 0 0 0 .196-.12l6.813-6.814z"/>
        <path fill-rule="evenodd" d="M1 13.5A1.5 1.5 0 0 0 2.5 15h11a1.5 1.5 0 0 0 1.5-1.5v-6a.5.5 0 0 0-1 0v6a.5.5 0 0 1-.5.5h-11a.5.5 0 0 1-.5-.5v-11a.5.5 0 0 1 .5-.5H9a.5.5 0 0 0 0-1H2.5A1.5 1.5 0 0 0 1 2.5v11z"/>
        </svg>
        EOF;
    public $editIcon;
    public $viewIcon = <<<EOF
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-zoom-in" viewBox="0 0 16 16">
        <path fill-rule="evenodd" d="M6.5 12a5.5 5.5 0 1 0 0-11 5.5 5.5 0 0 0 0 11zM13 6.5a6.5 6.5 0 1 1-13 0 6.5 6.5 0 0 1 13 0z"/>
        <path d="M10.344 11.742c.03.04.062.078.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1.007 1.007 0 0 0-.115-.1 6.538 6.538 0 0 1-1.398 1.4z"/>
        <path fill-rule="evenodd" d="M6.5 3a.5.5 0 0 1 .5.5V6h2.5a.5.5 0 0 1 0 1H7v2.5a.5.5 0 0 1-1 0V7H3.5a.5.5 0 0 1 0-1H6V3.5a.5.5 0 0 1 .5-.5z"/>
        </svg>
        EOF;
    static public $thumbsDown = <<<EOF
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-hand-thumbs-down" viewBox="0 0 16 16">
        <path d="M8.864 15.674c-.956.24-1.843-.484-1.908-1.42-.072-1.05-.23-2.015-.428-2.59-.125-.36-.479-1.012-1.04-1.638-.557-.624-1.282-1.179-2.131-1.41C2.685 8.432 2 7.85 2 7V3c0-.845.682-1.464 1.448-1.546 1.07-.113 1.564-.415 2.068-.723l.048-.029c.272-.166.578-.349.97-.484C6.931.08 7.395 0 8 0h3.5c.937 0 1.599.478 1.934 1.064.164.287.254.607.254.913 0 .152-.023.312-.077.464.201.262.38.577.488.9.11.33.172.762.004 1.15.069.13.12.268.159.403.077.27.113.567.113.856 0 .289-.036.586-.113.856-.035.12-.08.244-.138.363.394.571.418 1.2.234 1.733-.206.592-.682 1.1-1.2 1.272-.847.283-1.803.276-2.516.211a9.877 9.877 0 0 1-.443-.05 9.364 9.364 0 0 1-.062 4.51c-.138.508-.55.848-1.012.964l-.261.065zM11.5 1H8c-.51 0-.863.068-1.14.163-.281.097-.506.229-.776.393l-.04.025c-.555.338-1.198.73-2.49.868-.333.035-.554.29-.554.55V7c0 .255.226.543.62.65 1.095.3 1.977.997 2.614 1.709.635.71 1.064 1.475 1.238 1.977.243.7.407 1.768.482 2.85.025.362.36.595.667.518l.262-.065c.16-.04.258-.144.288-.255a8.34 8.34 0 0 0-.145-4.726.5.5 0 0 1 .595-.643h.003l.014.004.058.013a8.912 8.912 0 0 0 1.036.157c.663.06 1.457.054 2.11-.163.175-.059.45-.301.57-.651.107-.308.087-.67-.266-1.021L12.793 7l.353-.354c.043-.042.105-.14.154-.315.048-.167.075-.37.075-.581 0-.211-.027-.414-.075-.581-.05-.174-.111-.273-.154-.315l-.353-.354.353-.354c.047-.047.109-.176.005-.488a2.224 2.224 0 0 0-.505-.804l-.353-.354.353-.354c.006-.005.041-.05.041-.17a.866.866 0 0 0-.121-.415C12.4 1.272 12.063 1 11.5 1z"/>
        </svg>
        EOF;
    static public $searchIcon = <<<EOF
        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="currentColor" class="bi bi-search" viewBox="0 0 16 16">
        <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001c.03.04.062.078.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1.007 1.007 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0z"/>
        </svg>
        EOF;
    static public $plus_square = <<<EOF
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-plus-square" viewBox="0 0 16 16">
        <path d="M14 1a1 1 0 0 1 1 1v12a1 1 0 0 1-1 1H2a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1h12zM2 0a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2H2z"/>
        <path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4z"/>
        </svg>
        EOF;
    /*  static public $addUser = <<<EOF
        <svg xmlns="http://www.w3.org/2000/svg"  width="24" height="24" fill="currentColor" data-name="Layer 1" viewBox="0 0 1024 1280" x="0px" y="0px"><title>Add user</title>
        <path d="M492.8 556.088889c-137.309091 0-249.018182-111.709091-249.018182-249.018182C243.781818 169.890909 355.490909 58.181818 492.8 58.181818c137.309091 0 248.888889 111.709091 248.888889 248.888889 0 137.309091-111.709091 249.018182-248.888889 249.018182z m0-429.511111c-99.555556 0-180.492929 80.937374-180.492929 180.492929s81.066667 180.492929 180.492929 180.492929c99.555556 0 180.492929-80.937374 180.492929-180.492929 0-99.426263-81.066667-180.492929-180.492929-180.492929z m0 0"/>
        <path d="M82.682828 966.206061c-18.876768 0-34.262626-15.385859-34.262626-34.262627 0-245.010101 199.240404-444.250505 444.379798-444.250505 108.347475 0 212.816162 39.563636 294.012121 111.191919 14.222222 12.541414 15.515152 34.133333 2.973738 48.355556s-34.133333 15.385859-48.355556 2.973737c-68.654545-60.638384-156.961616-94.125253-248.759596-94.125252C285.414141 556.088889 116.816162 724.686869 116.816162 931.943434c0 18.876768-15.256566 34.262626-34.133334 34.262627z m0 0M941.317172 849.19596H707.426263c-18.876768 0-34.262626-15.385859-34.262627-34.262627s15.385859-34.262626 34.262627-34.262626h233.890909c18.876768 0 34.262626 15.385859 34.262626 34.262626 0 19.006061-15.385859 34.262626-34.262626 34.262627z m0 0"/>
        <path d="M824.436364 966.206061c-18.876768 0-34.262626-15.385859-34.262627-34.262627V698.052525c0-18.876768 15.385859-34.262626 34.262627-34.262626s34.262626 15.385859 34.262626 34.262626v233.890909c-0.129293 18.876768-15.385859 34.262626-34.262626 34.262627z m0 0"/></svg>
        EOF; */

    static public $addUser = <<<EOT
        <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="currentColor" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" x="0px" y="0px" viewBox="0 0 100 125" style="enable-background:new 0 0 100 100;" xml:space="preserve">
        <path d="M42.5,10.4c-9.8,0-17.7,7.7-17.7,17.3c0,9,7.4,22.3,17.7,22.3s17.7-13.3,17.7-22.3C60.2,18.1,52.3,10.4,42.5,10.4z   M42.5,47.9c-8.3,0-15.7-11.3-15.7-20.3c0-8.4,7-15.3,15.7-15.3s15.7,6.8,15.7,15.3C58.2,36.6,50.8,47.9,42.5,47.9z"/>
        <path d="M76,83.1V72.5c0-10.9-9.1-19.9-20.3-20l-0.4,0L55,52.7c-3.4,2.8-7.6,4.2-12.5,4.2s-9.2-1.4-12.5-4.2l-0.3-0.2l-0.4,0  C18.1,52.6,9,61.6,9,72.5v10.7c0,1.7,1.2,3.1,2.8,3.4c8.9,2,19.5,3.1,30.7,3.1s21.8-1.1,30.7-3.1C74.8,86.2,76,84.8,76,83.1z   M42.5,87.6c-11,0-21.5-1-30.2-3c-0.7-0.2-1.3-0.8-1.3-1.5V72.5c0-9.7,8-17.7,18-18c3.7,2.9,8.2,4.4,13.5,4.4s9.8-1.5,13.5-4.4  c10,0.3,18,8.3,18,18v10.7c0,0.7-0.5,1.3-1.3,1.5C64,86.6,53.5,87.6,42.5,87.6z"/>
        <path d="M89,37h-8v-8c0-1.1-0.9-2-2-2h-4c-1.1,0-2,0.9-2,2v8h-8c-1.1,0-2,0.9-2,2v4c0,1.1,0.9,2,2,2h8v8c0,1.1,0.9,2,2,2h4  c1.1,0,2-0.9,2-2v-8h8c1.1,0,2-0.9,2-2v-4C91,37.9,90.1,37,89,37z M89,43h-8c-1.1,0-2,0.9-2,2v8h-4v-8c0-1.1-0.9-2-2-2h-8v-4h8  c1.1,0,2-0.9,2-2v-8h4v8c0,1.1,0.9,2,2,2l8,0V43z"/></svg>
        EOT;

        /*  static public $ok = <<<EOF
        <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" x="0px" y="0px" 
        width="32" height="32" fill="currentColor" 
        viewBox="0 0 100 125" enable-background="new 0 0 100 100" xml:space="preserve">
        <path d="M50,95c24.8,0,45-20.2,45-45S74.8,5,50,5C25.2,5,5,25.2,5,50S25.2,95,50,95z M50,11.2c21.4,0,38.8,17.4,38.8,38.8  c0,21.4-17.4,38.8-38.8,38.8S11.2,71.4,11.2,50C11.2,28.6,28.6,11.2,50,11.2z"/>
        <path d="M42.5,65.7c0.6,0.5,1.3,0.7,2,0.7c0.8,0,1.6-0.3,2.2-0.9l26.3-26.6c1.2-1.2,1.2-3.2,0-4.4c-1.2-1.2-3.2-1.2-4.4,0L44.3,59.1  l-10.4-8.8c-1.3-1.1-3.3-0.9-4.4,0.4c-1.1,1.3-0.9,3.3,0.4,4.4L42.5,65.7z"/></svg>
        EOF; */
        static public $ok = <<<EOF
            <svg xmlns="http://www.w3.org/2000/svg" 
            width="32" height="32" fill="currentColor" width="16" height="16" viewBox="0 0 64 80" x="0px" y="0px"><title>check, done, tick, interface icon</title><g data-name="Layer 1"><path d="M26.47,50.6h.05a2,2,0,0,0,1.53-.79L53.58,16.62a2,2,0,1,0-3.16-2.43l-24,31.23L13.52,30.28a2,2,0,1,0-3,2.59l14.46,17A2,2,0,0,0,26.47,50.6Z"/></g></svg>
            EOF;
    
        function __construct($items, $n = NULL, $titles = NULL)
    {
        $this->editIcon = self::$biPencilSquare;
        $this->list = $items;
        $this->cols = $n;
        $this->titles = $titles;
        $this->pages = util::balanceArrays($this->list, $this->cols);
    }

    public function getArticlesPage()
    {
        $formatedItems = [];
        foreach ($this->list as $key => $item) {
            $formatedItems[] = <<<EOF
                <li class="list-group-item" data-id="{$item['article_id']}">
                    <a href="/mng/editArticle/{$item['article_id']}">{$this->editIcon}</a>
                    <a href="/essays/article/{$item['article_id']}" target="_blank">
                    {$item['title_he']}</a>
                </li>
                EOF;
        }
        $listStr = '<ul class="list-group list-group-flush">' . join(
            '',
            $formatedItems
        ) . '</ul>';
        return $listStr;
    }

    public function getBriefsPage()
    {
        $formatedItems = [];
        foreach ($this->list as $key => $item) {
            $formatedItems[] = <<<EOF
                <li class="list-group-item" data-id="{$item['briefs_id']}">
                    <a href="/mng/editBrief/{$item['briefs_id']}">{$this->editIcon}</a>
                    <a href="/briefs/show/{$item['briefs_id']}" target="_blank">
                    {$item['title_he']}</a>
                </li>
                EOF;
        }
        $listStr = '<ul class="list-group list-group-flush">' . join(
            '',
            $formatedItems
        ) . '</ul>';
        return $listStr;
    }

    public function getHighlightsPage()
    {
        $formatedItems = [];
        foreach ($this->list as $key => $item) {
            $shortTitle = util::shorten_string($item['title_' . lang::getLocale()]);
            $formatedItems[] = <<<EOF
                <li class="list-group-item" data-id="{$item['highlights_id']}">
                    <a href="/mng/editHighlight/{$item['highlights_id']}">{$this->editIcon}</a>
                    {$item['expiration']}
                    <a href="/essays/article/{$item['highlights_id']}" target="_blank">
                    {$shortTitle}</a>
                </li>
                EOF;
        }
        $listStr = '<ul class="list-group list-group-flush">' . join(
            '',
            $formatedItems
        ) . '</ul>';
        return $listStr;
    }

    public function getAnnouncementsPage()
    {
        $formatedItems = [];
        foreach ($this->list as $key => $item) {
            $shortTitle = util::shorten_string($item['title_' . Lang::getLocale()]);
            $formatedItems[] = <<<EOF
                <li class="list-group-item" data-id="{$item['news_id']}">
                <a href="/mng/editAnnouncemet/{$item['news_id']}">{$this->editIcon}</a>
                    {$item['expiration']}
                    {$shortTitle}
                </li>
                EOF;
        }
        $listStr = '<ul class="list-group list-group-flush">' . join(
            '',
            $formatedItems
        ) . '</ul>';
        return $listStr;
    }
    public function getSearchResultsPage()
    {
        $formatedItems = [];
        foreach ($this->list as $key => $item) {
            // Debug::dump($item,'item at ' . util::getCaller());
            $desc = collection::renderTitle($item);
            $link = "/collection/item/{$item['item_id']}";
            $formatedItems[] = <<<EOT
                <li class="list-group-item">
                <a href="{$link}">{$desc}</a>
                </li>
                EOT;
        }
        $itemized = join('', $formatedItems);
        // Debug::dump($itemized,'itemized at ' . util::getCaller());
        $listStr = <<<EOT
            <ul class="list-group list-group-flush">{$itemized}</ul>
            EOT;
        return $listStr;
        // return "TODO at " . util::getCaller();
    }
    public function getCurrentCompPage()
    {
        // Debug::dump($this->list, 'list at ' . util::getCaller());
        $lines = [];
        foreach ($this->list as $item) {
            $title = collection::renderTitle($item);
            $lines[] = <<<EOF
                <li class="list-group-item">
                <a href="/collection/item/{$item['item_id']}">{$title}</a>
                </li>
                EOF;
        }
        $itemized = join(' ', $lines);
        $returnStr = <<<EOF
            <ul class="list-group list-group-flush">{$itemized}</ul>
            EOF;
        // $size = count($this->list);
        // return 
        // "<div dir=ltr>TODO list of [{$size}] items at " .  util::getCaller() . "</div>";
        return $returnStr;
    }
}
