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
        'nonMatchingFields' => ['literal' => 'שדות לא תואמים', 'callback' => 'renderMisMatchedFields'],
        'dumpItemsList' => ['literal' => 'הורדת רשימת פריטים', 'callback' => 'dumpItemsList'],
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
        if ($this->request) {
            $funcName = $this->menu[$this->request]['callback'];
            if (method_exists($this, $funcName)) {
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
                <tr>
                <td><a href="/inventory/editItem/{$record['item_id']}">ערוך</td>
                <td>{$record['item_id']}</td><td>{$title}</td>
                </tr>
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
        $list = [];
        foreach ($data as $item) {
            $list[] = <<<EOF
            <tr><td class="ltr">{$item}</td></tr>
            EOF;
        }
        $pre = <<<EOF
            <table id="list2Sort" class="table table-sm table-hover table-responsive tablesorter">
                <thead><tr><th>file name</th></tr></thead><tbody>
            EOF;
        $post = "</tbody></table>";
        return $pre . join('', $list) . $post;
    }

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
        $picsInUse = $this->getPicsInUse();
        $diff = array_diff($scanned_directory, $picsInUse);
        $manualDiff = $this->findPath($diff);
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
    }
    private function findPath($a)
    {
        $diff = [];
        $pdo = db::getInstance();
        $sqlStr = "SELECT * FROM `pictures` WHERE `path` = :path LIMIT 1";
        $stmt = $pdo->prepare($sqlStr);
        foreach ($a as $value) {
            $stmt->bindValue(':path', $value);
            $stmt->execute();
            $result = $stmt->fetch();
            if (!$result) {
                $diff[] = $value;
            }
        }
        return $diff;
    }
    private function renderMisMatchedFields()
    {
        $fields2compare = [
            ['title' => 'כותר', 'field1' => 'caption_he', 'field2' => 'caption_en'],
            ['title' => 'יצרן', 'field1' => 'companyHe', 'field2' => 'companyEn'],
            ['title' => 'דגם', 'field1' => 'modelHe', 'field2' => 'modelEn'],
        ];
        $list = [];
        foreach ($fields2compare as $r) {
            $list = array_merge($list, $this->getMisMatchedFields($r));
        }

        return $this->renderList($list);
    }
    private function getMisMatchedFields($r)
    {
        $pdo = db::getInstance();
        $sqlStr = <<<EOF
            SELECT
                *
            FROM
                `items`
            WHERE
                (
                    `{$r['field1']}` IS NULL OR `{$r['field1']}` = ''
                ) ^(
                    `{$r['field2']}` is NULL OR `{$r['field2']}` = ''
                ) and (`display` = 1)
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
    private function renderList($records)
    {
        $list = [];
        foreach ($records as $key => $record) {
            $title = Collection::renderTitle($record);
            $list[] = <<<EOF
                <tr>
                <td><a href="/inventory/editItem/{$record['item_id']}">ערוך</td>
                <td>{$record['item_id']}</td><td>{$title}</td>
                </tr>
                EOF;
        }
        $pre = <<<EOF
            <table id="list2Sort" class="table table-sm table-hover table-responsive tablesorter">
                <thead><tr><th>מספר</th><th>כותר</th></tr></thead><tbody>
            EOF;
        $post = "</tbody></table>";
        return $pre . join('', $list) . $post;
    }
    private function dumpItemsList()
    {
        // Debug::dump($this->request, util::getCaller());
        $pdo = db::getInstance();
        $sqlStr = <<<EOF
            SELECT
                items.`item_id`,
                `registration`,
                `caption_he`,
                `companyHe`,
                `modelHe`,
                `year`,
                `sourceHe`,
                ownership.owner,
                ownership.transaction_year,
                ownership.ownership_id
            FROM
                `items`
            LEFT JOIN ownership ON ownership.item_id = items.item_id
            ORDER BY
                items.item_id,
                ownership.ownership_id
            ;
            EOF;
        $pdo = db::getInstance();
        $stmt = $pdo->prepare($sqlStr);
        try {
            $stmt->execute();
        } catch (\Throwable $th) {
            Debug::dump($th->getMessage() . 'error at ' . util::getCaller());
        }
        $results = $stmt->fetchAll();
        $cleanData = self::clearDoubleOwnerships($results);

        // Debug::dump(count($cleanData), util::getCaller());
        $keysToDelete = ['ownership_id'];
        foreach ($cleanData as $key => $r) {
            $cleanData[$key]['description'] = self::renderSimpleTitle($r);
            self::unlinkFields($cleanData[$key], [
                'ownership_id', 'modelHe', 'sourceHe', 'caption_he', 'companyHe','year',
            ]);
            
        }
        array_unshift($cleanData, [
            'item_id' => 'אינדקס',
            'registration' => 'קוד רישום',
            'owner' => 'בעלים',
            'transaction_year' => 'שנת רישום',
            'description' => 'פריט',
        ]);
        // Debug::dump($cleanData, util::getCaller());
        self::array_to_csv_download($cleanData);
        exit();
    }
    private function clearDoubleOwnerships($table)
    {
        $prevKey = null;
        foreach ($table as $key => $r) {
            if (is_null($prevKey)) {
                $prevKey = $key;
            } else {
                // debug::dump([$prevKey, $key], util::getCaller());
                if ($r['item_id'] == $table[$prevKey]['item_id']) {
                    if ($r['transaction_year'] == $table[$prevKey]['transaction_year']) {
                        Debug::dump('Same year ' . $r['registration'], util::getCaller());
                        if ($r['ownership_id'] > $table[$prevKey]['ownership_id']) {
                            unset($table[$prevKey]);
                        } else {
                            unset($table[$key]);
                        }
                    } else {
                        // Debug::dump('different years ' . $r['registration'], util::getCaller());
                        if ($r['transaction_year'] > $table[$prevKey]['transaction_year']) {
                            unset($table[$prevKey]);
                        } else {
                            unset($table[$key]);
                        }
                    }
                    // Debug::dump([$key, $table[$prevKey]['transaction_year'],$r['transaction_year'], $table[$prevKey]['ownership_id'],$r['ownership_id']], util::getCaller());
                }
                $prevKey = $key;
            }
        }

        return $table;
    }
    private function array_to_csv_download($array, $filename = "items.csv", $delimiter = ",")
    {
        // open raw memory as file so no temp files needed, you might run out of memory though
        $f = fopen('php://memory', 'w');
        // loop over the input array
        foreach ($array as $line) {
            // generate csv lines from the inner arrays
            fputcsv($f, $line, $delimiter);
        }
        // reset the file pointer to the start of the file
        fseek($f, 0);
        // tell the browser it's going to be a csv file
        header('Content-Encoding: UTF-8');
        header('Content-type: text/csv; charset=UTF-8');
        // tell the browser we want to save it instead of displaying it
        header('Content-Disposition: attachment; filename=' . $filename);
        echo "\xEF\xBB\xBF"; // UTF-8 BOM
        // make php send the generated csv lines to the browser
        fpassthru($f);
    }
    private function renderSimpleTitle($item)
    {
        $retData = [];
        $ext = ucfirst(Lang::getLocale());
        foreach (['caption_' . Lang::getLocale(), 'company' . $ext, 'model' . $ext, 'year', 'source' . $ext] as
            $key) {
            if (isset($item[$key]) and !util::IsNullOrEmptyString($item[$key])) {
                $retData[] = trim($item[$key]);
            }
        }
        // $textStr = '<bdi>' . join("</bdi>, <bdi>", $retData) . '</bdi>';
        $textStr = join(",", $retData);
        return $textStr;
    }
    private function unlinkFields(&$arr, $fields)
    {
        foreach ($fields as $keyToDelete) {
            if (array_key_exists($keyToDelete, $arr)) {
                unset($arr[$keyToDelete]);
            }
        }
    }
}
