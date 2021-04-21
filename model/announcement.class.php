<?php
/**
 * Description of news
 *
 * @author amots
 * $date 2021-03-10
 */
class announcement {

    public function renderValidNews() {
        $list = $this->getCurrentAnnouncements();
        // Debug::dump($list, 'current news in ' . __METHOD__ . ' line ' . __LINE__);

        return $this->renderAnnouncementsSection($list);
    }

    public function getCurrentAnnouncements() {
        $sql = <<<EOF
            SELECT * FROM `news`
            WHERE
                (`creation` IS NULL OR CURRENT_DATE >= `creation`) 
                AND(`expiration` IS NULL OR CURRENT_DATE <= `expiration`)
            EOF;
        $pdo = db::getInstance();
        try {
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
        } catch (Exception $exc) {
            Debug::dump($exc->getMessage(),
                    'excute error in ' . __METHOD__ . ' line ' . __LINE__);
        }
        $list = $stmt->fetchAll();
        return $list;
    }

    public function getAllAnnouncements() {
        $sql = <<<EOF
            SELECT * FROM `news` WHERE 1
            EOF;
        $pdo = db::getInstance();
        try {
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
        } catch (Exception $exc) {
            Debug::dump($exc->getMessage(),
                    'excute error in '. util::getCaller());
        }
        $list = $stmt->fetchAll();
        return $list;
    }

    private function renderAnnouncementsSection($list) {
        $list = $this->sortAnnouncements($list);
        $cards = [];
        foreach ($list as $key => $value) {
            $title = $value['title_' . Lang::getLocale()];
            $body = $value['body_' . Lang::getLocale()];
            $image = util::IsNullOrEmptyString($value['image']) ? NULL : <<<EOF
                <img class="card-img-bottom" src="{$value['image']}" />
                EOF;
            $link = util::IsNullOrEmptyString($value['link']) ? NULL : $value['link'];
            $cards[] = <<<EOF
                <div class="card m-1">  
                    {$image}
                    <div class="card-body">
                        <div class="card-title">{$title}</div>
                        <div class="card-body">{$body}</div>
                    </div>
                </div>
                EOF;
        }
        return '<div class="card-group">' . join(' ', $cards) . '</div>';
    }

    private function sortAnnouncements($news) {
        foreach ($news as $key => $row) {
            $w[$key] = $row['weight'];
            $s[$key] = $row['sticky'];
            $d[$key] = $row['creation'];
        }
        array_multisort($w, SORT_ASC, $s, SORT_DESC, $d, SORT_DESC, SORT_STRING,
                $news);
        return $news;
    }

    public function renderMngBreadcrumbs($type = NULL) {
        switch ($type) {
            case 'current' :
                return breadCrumbs::genBreadCrumbs([
                            ['literal' => Lang::trans('nav.homePage'), 'link' => '/'],
                            ['literal' => Lang::trans('mng.mng'), 'link' => '/mng'],
                            ['literal' => Lang::trans('mng.content'), 'link' => '/mng/content'],
                            ['literal' => Lang::trans('mng.currentAnnouncements'),
                                'link' => NULL],
                ]);
                break;
            default :
                return breadCrumbs::genBreadCrumbs([
                            ['literal' => Lang::trans('nav.homePage'), 'link' => '/'],
                            ['literal' => Lang::trans('mng.mng'), 'link' => '/mng'],
                            ['literal' => Lang::trans('mng.content'), 'link' => '/mng/content'],
                            ['literal' => Lang::trans('mng.allAnnouncements'), 'link' => NULL],
                ]);
        }
    }

    public function renderEditAnnouncementContent($news_id) {
        $token = util::RandomToken();
        $_SESSION['csrf_token'] = $token;

        if (is_numeric($news_id))
                $item = $this->fetchSingleAnnouncement($news_id);
        else {
            $form = new form('news');
            $item = $form->genEmptyRecord();
        }
        $item['csrf_token'] = $token;
         if (!isset($item['updated'])) $item['updated'] = NULL;
        if (!isset($item['creation'])) $item['creation'] = date("Y-m-d");
        if (!isset($item['news_id'])) {
            $item['expiration'] = NULL;
            $item['news_id'] = NULL;
        }
    //    Debug::dump($item, 'item in ' . __METHOD__ . ' line ' . __LINE__);
    //    $content = '<div class="text-center">TODO: ' . $news_id . ' in ' . __METHOD__ . ' line ' . __LINE__ . "</div>";
        $renderer = new template_renderer(__SITE_PATH . '/includes/mng/anouncementEditContent.html');
        $renderer->viewData = ['item' => $item];
        return $renderer->render();
    }

    public function fetchSingleAnnouncement($news_id) {
        $sql = "SELECT * FROM `news` WHERE `news_id` = :news_id LIMIT 1";
        $pdo = db::getInstance();
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':news_id', $news_id);
        try {
            $stmt->execute();
        } catch (Exception $ex) {
        //    Debug::dump($pdo->errorInfo(),
        //            'PDO error in ' . __METHOD__ . ' line ' . __LINE__);
        }
        return $stmt->fetch();
    }

}
