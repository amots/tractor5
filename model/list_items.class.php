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
