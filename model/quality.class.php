<?php

/**
 * @author amots
 * @since 2020-10-18
 */
class quality
{
    private $rt;
    public $errors = [];
    public $messages = [];
    private $request = null;
    private $menu = [
        'noPics' => ['literal' => 'פריט ללא תמונה', 'callback' => 'renderNoPicsTable'],
        'reusedPictures' => ['literal' => 'תמונות בשימוש כפול', 'callback' => 'renderReusedPictures'],
        'orphandPictures' => ['literal' => 'תמונות שלא בשימוש', 'callback' => 'renderOrphanPictures'],
    ];

    public function __construct()
    {
        $this->rt = explode('/', $_REQUEST['rt']);
        $indexAt = 2;
        if (isset($this->rt[$indexAt]) and !util::IsNullOrEmptyString($this->rt[$indexAt])) {
            $this->request = $this->rt[$indexAt];
        }
    }

    public function renderQualityMenu()
    {
        $items = [];
        foreach ($this->menu as  $key => $value) {
            $active = ($this->request and $this->request == $key) ? 'active' : null;
            $items[] = <<<EOF
                <li class="list-group-item list-group-item-light {$active}">
                    <a class="" href="/mng/quality/{$key}">{$value['literal']}</a>
                </li>
                EOF;
        }

        $list = join('', $items);
        return <<<EOT
            <ul class="list-group list-group-flush">{$list}</ul>
            EOT;
    }
    public function renderQualityPage()
    {
        // Debug::dump($this->rt,'rt at ' . util::getCaller());
        $indexAt = 2;
        // if (isset($this->rt[$indexAt]) and !util::IsNullOrEmptyString($this->rt[$indexAt])) {
        //     $funcName = $this->menu[$this->rt[$indexAt]]['callback'];
        if ($this->request) {
            $funcName = $this->menu[$this->request]['callback'];
            if (method_exists($this, $funcName)) {
                // Debug::dump($funcName, 'callback name at ' . util::getCaller());
                return $this->$funcName();
            }
        }
        return null;
    }

    private function renderNoPicsTable()
    {
        $records = $this->get_noPics();
        $list = [];
        foreach ($records as $key => $record) {
            $title = Collection::renderTitle($record);
            $list[] = <<<EOF
                <tr><td>{$record['item_id']}</td><td>{$title}</td></tr>
                EOF;
        }
        $pre = <<<EOF
            <table id="list2Sort" class="table table-sm table-hover table-responsive tablesorter">
                <thead><tr><th>מספר</th><th>כותר</th></tr></thead><tbody>
            EOF;
        $post = "</tbody></table>";
        return $pre . join('', $list) . $post;
    }

    private function renderReusedPictures()
    {
        $records = $this->getReusedPictures();
        $list = [];
        foreach ($records as $key => $record) {
            $title = Collection::renderTitle($record);
            $iten_id = $record['item_id'];
            $list[] = <<<EOF
                <tr><td>{$record['item_id']}</td>
                <td><a href="/collection/item/{$iten_id}&forceview" target="_BLANK">{$title}</a></td>
                <td class="ltr">{$record['path']}</td></tr>
                EOF;
        }
        $pre = <<<EOF
            <table id="list2Sort" class="table table-sm table-hover table-responsive tablesorter">
                <thead><tr><th>מספר</th><th>כותר</th><th>תמונה</th></tr></thead><tbody>
            EOF;
        $post = "</tbody></table>";
        return $pre . join('', $list) . $post;
    }

    private function renderOrphanPictures()
    {
        $data = $this->getorphanPics();
        // Debug::dump($data, 'orphan pictures at ', util::getCaller());
        $list = [];    
        foreach($data as $item) {
            $list[] = <<<EOF
            <tr><td class="ltr">{$item}</td></tr>
            EOF;
        }$pre = <<<EOF
            <table id="list2Sort" class="table table-sm table-hover table-responsive tablesorter">
                <thead><tr><th>file name</th></tr></thead><tbody>
            EOF;
    $post = "</tbody></table>";
    return $pre . join('', $list) . $post;
    }
    /* private function renderItemsList($records)
    {
        $list = [];
        foreach ($records as $key => $record) {
            $title = tractorCollection::renderTitle($record);
            $list[] = <<<EOF
                <tr><td>{$record['item_id']}</td><td>{$title}</td></tr>
                EOF;
        }
        $pre = <<<EOF
            <table id="list2sort" 
                class="table table-sm table-hover table-responsive tablesorter tablesorter-default">
                <thead><tr><th>מספר</th><th>כותר</th></tr></thead><tbody>
            EOF;
        $post = "</tbody></table>";
        return $pre . join('', $list) . $post;
    } */

    private function get_noPics()
    {
        $pdo = db::getInstance();
        $sqlStr = "SELECT * FROM `items` WHERE `items`.item_id NOT IN(SELECT `item_id` FROM `pictures`)";

        $stmt = $pdo->prepare($sqlStr);
        try {
            $stmt->execute();
        } catch (Exception $ex) {
            $_SESSION['errors'][] = $ex->getTraceAsString();
            $_SESSION['errors'][] = $stmt->errorInfo();
        }
        $records = $stmt->fetchAll();
        return $records;
    }

    private function getReusedPictures()
    {
        $pdo = db::getInstance();
        $sqlStr = <<<EOF
            SELECT dtl.path as path, items.item_id, items.companyHe,items.modelHe, items.year,items.sourceHe 
                        FROM items JOIN (SELECT `picture_id`, `item_id`, `path`
            FROM
                pictures
            JOIN(
                SELECT
                    `path` AS dp
                FROM
                    pictures
                GROUP BY
                    `path`
                HAVING
                    COUNT(`path`) > 1
            ) dbl
            ON
                dbl.dp = pictures.path
            ) dtl ON
            dtl.item_id = items.item_id
            EOF;
        $stmt = $pdo->prepare($sqlStr);
        try {
            $stmt->execute();
        } catch (Exception $ex) {
            $_SESSION['errors'][] = $ex->getTraceAsString();
            $_SESSION['errors'][] = $stmt->errorInfo();
        }
        $records = $stmt->fetchAll();
        return $records;
    }
    private function getorphanPics()
    {
        $directory = __SITE_PATH . '/assets/media/pics/items';
        $scanned_directory = array_diff(scandir($directory, SCANDIR_SORT_ASCENDING), ['..', '.']);
        // Debug::dump($scanned_directory, 'directory listing pictures at ', util::getCaller());
        // Debug::dump(sizeof($scanned_directory), 'directory listing pictures at ', util::getCaller());
        $picsInUse = $this->getPicsInUse();
        // Debug::dump($picsInUse, 'pics in use at ', util::getCaller());
        // Debug::dump(sizeof($picsInUse), 'pics in use at ', util::getCaller());
        // $diff = array_udiff($scanned_directory, $picsInUse, [$this, 'name_compare']);
        $diff = array_diff($scanned_directory, $picsInUse);
        // $diff = array_diff($picsInUse,$scanned_directory);
        // $diff = $this->name_compare($scanned_directory, $picsInUse);
        // $diff= array_diff($diff,$picsInUse);
        $manualDiff = $this->findPath($diff);
        // Debug::dump($manualDiff, 'manuall diff in use at ', util::getCaller());
        // return array_diff($diff,$manualDiff);
        return $manualDiff;
    }
    private function getPicsInUse()
    {
        $sqlStr = "SELECT `path` FROM `pictures` WHERE 1 ORDER BY `path`";
        $pdo = db::getInstance();
        $stmt = $pdo->prepare($sqlStr);
        try {
            $stmt->execute();
        } catch (\Throwable $th) {
            Debug::dump($th->getMessage() . 'error at ' . util::getCaller());
        }
        return util::simplifyArray($stmt->fetchAll());
    }
    private function name_compare($a, $b)
    {

        foreach ($a as $value) {
            $retArray = [];
            $retArray[] = sizeof($a);
            $retArray[] = sizeof($b);
            // $equalKey = [];
            $key = array_search($value, $b);
            if ($key === false) {
                $retArray[] = $value;
            } else {
                // $equalKey[] = $key;
            }
        }
        return $retArray;

        /* if (trim($a) == trim($b)) {
            return 0;
        } 
        if (trim($a)<trim($b)){
            return -1;
        }
        if (trim($a)>trim($b)) {
            return 1;
        } */
    }
    private function findPath($a)
    {
        $diff = [];
        $pdo = db::getInstance();
        $sqlStr = "SELECT * FROM `pictures` WHERE `path` = :path LIMIT 1";
        $stmt = $pdo->prepare($sqlStr);
        foreach ($a as $value) {
            $stmt->bindValue(':path',$value);
            $stmt->execute();
            $result = $stmt->fetch();
            if (!$result) {
                $diff[] = $value;
            }
        }
        // Debug::dump($diff, 'pics in use at ', util::getCaller());
        return $diff;
    }
}
