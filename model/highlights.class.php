<?php

/**
 * Description of highlights
 *
 * @author amots
 */
class highlights
{

    var $lang;
    var $highlights;
    var $orderedItems;

    function __construct()
    {
        $this->highlights = $this->loadHighlights();
        $this->orderedItems = $this->sortHighlights($this->highlights);
    }

    function loadHighlights()
    {
        $now = new DateTime();
        $today_date = $now->format('Y-m-d');
        $items = [];
        $sql = "SELECT * FROM `highlights` WHERE `expiration`>=:eToday and `creation`<=:cToday";
        $pdo = db::getInstance();
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':eToday', $today_date);
        $stmt->bindValue(':cToday', $today_date);
        try {
            $stmt->execute();
        } catch (Exception $ex) {
            Debug::dump(
                $ex->getMessage(),
                'excute error in ' . __METHOD__ . ' line ' . __LINE__
            );
        }
        return $stmt->fetchAll();
    }

    public function renderHighlights()
    {
        $str = "";
        if (count($this->orderedItems) > 0) {
            foreach ($this->orderedItems as $highLight) {
                $str .= $this->formatHighlight($highLight);
            }
        }
        return $str;
    }

    public function renderSingleHighlight($highlights_id)
    {
        return $this->formatHighlight($this->fetchSingleHighlight($highlights_id));
    }

    private function formatHighlight($item)
    {
        $strf = '';
        switch (lang::getLocale()) {
            case "he":
                $title = $item['title_he'];
                $body = $item['body_he'];
                $linkTitle = 'לפרטים נוספים';
                break;
            default:
                $title = $item['title_en'];
                $body = $item['body_en'];
                $linkTitle = 'for further details';
        }

        //if (strlen($item['link']) > 0) {
        if (!util::IsNullOrEmptyString($item['link'])) {
            $linkStr = sprintf(
                '<button class="btn" type="button" onclick="window.open(\'%s\', \'_blank\')">%s</button>',
                $item['link'],
                $linkTitle
            );
        } else $linkStr = '';

        if (!util::IsNullOrEmptyString($title) || !util::IsNullOrEmptyString($body)) {
            $strf .= '<div class="well  well-sm">';
            $strf .= strlen($title) > 0 ? '<h4>' . $title . '</h4>' : '';
            $strf .= strlen($body) > 0 ? $body : '';
            $strf .= $linkStr;
            $strf .= '</div>';
        }
        if (!util::IsNullOrEmptyString($item['embed'])) {
            $strf .= $item['embed'];
        }
        if (!util::IsNullOrEmptyString($item['image'])) {
            $strf .= '<div><img src="' . $item['image'] . '" class="img-fluid mx-auto"  /></div>';
        }
        return $strf;
    }

    private function sortHighlights($items)
    {
        if (!empty($items)) {
            foreach ($items as $key => $row) {
                $w[$key] = $row['weight'];
                $s[$key] = $row['sticky'];
                $d[$key] = $row['creation'];
            }
            array_multisort(
                $w,
                SORT_ASC,
                $s,
                SORT_DESC,
                $d,
                SORT_DESC,
                SORT_STRING,
                $items
            );
        }
        return $items;
    }

    public function renderHighlightsList($mode = null)
    {
        $items = [];
        $list = $this->fetchHighlights($mode);
        foreach ($list as $key => $value) {
            $title = util::shorten_string($value['title_he']);
            $items[] = <<<EOF
                <tr>
                    <td><a href="/highlights/{$value['highlights_id']}">{$title}</a></td>
                    <td>{$value['creation']}</td>
                    <td>{$value['expiration']}</td>
                </tr>                    
                EOF;
        }
        return '<table class="table"><tr><th>כותר</th><th>התחלה</th><th>סיום</th></tr><tr>' . join(
            '</tr><tr>',
            $items
        ) . '</tr></table>';
    }

    public function fetchHighlights($mode = NULL)
    {

        switch ($mode) {
            case 'CURRENT':
                $now = new DateTime();
                $today_date = $now->format('Y-m-d');
                $where = "`expiration`>=:eToday and `creation`<=:cToday";
                break;
            default:
                $where = "1";
                break;
        }
        $sql = "SELECT `highlights_id`, `title_he`,`creation`,`expiration` FROM `highlights` WHERE {$where} ORDER BY `expiration` DESC;";
        $pdo = db::getInstance();
        $stmt = $pdo->prepare($sql);
        if ($mode == 'CURRENT') {
            $stmt->bindValue(':eToday', $today_date);
            $stmt->bindValue(':cToday', $today_date);
        }
        try {
            $stmt->execute();
        } catch (Exception $ex) {
            Debug::dump(
                $ex->getMessage(),
                'excute error in ' . __METHOD__ . ' line ' . __LINE__
            );
        }
        return $stmt->fetchAll();
    }

    static function fetchSingleHighlight($id)
    {
        $pdo = db::getInstance();
        $sql = "SELECT * FROM `highlights` WHERE `highlights_id` = :highlights_id LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':highlights_id', $id);
        try {
            $stmt->execute();
        } catch (Exception $ex) {
            Debug::dump(
                $ex->getMessage(),
                'excute error in ' . __METHOD__ . ' line ' . __LINE__
            );
        }
        return $stmt->fetch();
    }

    public function renderMngBreadcrumbs($type = NULL)
    {
        switch ($type) {
            case 'current':
                return breadCrumbs::genBreadCrumbs([
                    ['literal' => Lang::trans('nav.homePage'), 'link' => '/'],
                    ['literal' => Lang::trans('mng.mng'), 'link' => '/mng'],
                    ['literal' => Lang::trans('mng.content'), 'link' => '/mng/content'],
                    [
                        'literal' => Lang::trans('mng.currentHighlights'),
                        'link' => NULL
                    ],
                ]);
                break;
            default:
                return breadCrumbs::genBreadCrumbs([
                    ['literal' => Lang::trans('nav.homePage'), 'link' => '/'],
                    ['literal' => Lang::trans('mng.mng'), 'link' => '/mng'],
                    ['literal' => Lang::trans('mng.content'), 'link' => '/mng/content'],
                    [
                        'literal' => Lang::trans('mng.allHighlights'),
                        'link' => NULL
                    ],
                ]);
        }
    }

    public function renderHighlightEditContent($highlights_id)
    {
        $token = util::RandomToken();
        $_SESSION['csrf_token'] = $token;

        if (is_numeric($highlights_id))
            $item = $this->fetchSingleHighlight($highlights_id);
        else {
            $form = new form('highlights');
            $item = $form->genEmptyRecord();
        }
        $item['csrf_token'] = $token;
        if (!isset($item['updated'])) $item['updated'] = NULL;
        if (!isset($item['creation'])) $item['creation'] = date("Y-m-d");
        if (!isset($item['highlights_id'])) {
            $item['expiration'] = NULL;
            $item['highlights_id'] = NULL;
        }
        $renderer = new template_renderer(__SITE_PATH . '/includes/mng/highlightEditContent.html');
        $renderer->viewData = ['item' => $item];
        return $renderer->render();
    }
}
