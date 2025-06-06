<?php

/**
 * Description of ownership
 *
 * @author amots
 * @since 2020-09-06
 */
class ownership
{

    private $searchData = [
        'item_id',
        'PageHe',
        'registration',
        'vin',
        'engine_number',
        'sn',
        'companyHe'
    ];
    private $table = 'ownership';
    private $registry;
    private $editIcon;
    public $errors = [];
    public $messages = [];

    public function __construct()
    {
        $this->editIcon = list_items::$biPencilSquare;
    }

    public function renderOwnSearchPage()
    {
        $data['searchResults'] = isset($_POST['submit']) ? $this->searchResults()
            : NULL;
        foreach ($this->searchData as $key) {
            $data[$key] = isset($_POST[$key]) ? $_POST[$key] : NULL;
        }
        $data['csrf_token'] = $this->set_CSRF_Token();
        $data['searchIcon'] = list_items::$searchIcon;
        $renderer = new template_renderer(
            __SITE_PATH . '/includes/mng/search_page.html',
            $data
        );
        $listAllLiteral = Lang::trans('mng.listAll');
        $listAllAnchor = <<<EOF
            <div class="m-3"><a href="/ownership/all">$listAllLiteral</a></div>
            EOF;
        return $listAllAnchor . $renderer->render();
    }

    public function renderOwnRecordsList($item_id)
    {
        $records = $this->getAllOwnRecordItem($item_id);
        $title = $this->renderTitle($item_id);
        $plus = list_items::$plus_square;
        $recordsList = $this->renderOwnershipRecords($records);
        return <<<EOF
            <script> $(document).ready(function(){ $('svg').attr('height',40);$('svg').attr('width',40);})</script>
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">[{$item_id}] {$title}</h4>                
                    <div class="text-right card-text">
                        <div>{$recordsList}</div>
                        <a href="/ownership/{$item_id}/new">{$plus}</a>
                    </div>
                </div>
            </div>                
            EOF;
    }

    public function renderItemOwnEditPage($item_id, $ownership_id = NULL)
    {
        $ownData = $this->getOwnItem($ownership_id);
        //    Debug::dump($ownData, 'ownership data in ' . __METHOD__ . ' line ' . __LINE__);
        $data = $ownData;
        $data['item_id'] = $item_id;
        $data['maxYear'] = date('Y');
        $data['ownership_id'] = $ownership_id;
        $data['csrf_token'] = $this->set_CSRF_Token();
        $data['caller'] = "/ownership/{$item_id}";

        $renderer = new template_renderer(
            __SITE_PATH . '/includes/ownership/editPage.html',
            $data
        );
        $title = $this->renderTitle($item_id);
        return "<h4>{$title}</h4>" . $renderer->render();
    }

    private function set_CSRF_Token()
    {
        $token = util::RandomToken();
        $_SESSION['csrf_token'] = $token;
        return $token;
    }

    private function searchResults()
    {
        if (!util::validatePostToken('csrf_token', 'csrf_token')) {
            $_SESSION['messages'][] = [2,'failed to validate token'];
            header('location: /ownership');
        }
        unset($_SESSION['csrf_token']);
        $list = $this->getSearchData();
        //    Debug::dump($list, 'search results ' . __METHOD__ . ' line ' . __LINE__);
        $formatterList = [];
        foreach ($list as $key => $item) {
            $link = "/ownership/{$id}";
            $formatterList[] = join(
                ' - ',
                [
                    $item['item_id'],
                    "<a href=\"{$link}\">" . collection::renderTitle($item) . "</a>",
                    util::shorten_string($item['PageHe']),
                ]
            );
        }
        return join('<br />', $formatterList);
    }

    private function getSearchData()
    {
        foreach ($this->searchData as $key) {
            if (isset($_POST[$key])) {
                $column = $key;
                $searchStr = $_POST[$key];
            }
        }
        $elements2get = [
            'item_id',
            'caption_he',
            'companyHe',
            'modelHe',
            'sourceHe',
            'registration',
            'year',
            'PageHe'
        ];
        $elementsStr = '`' . join('`,`', $elements2get) . '`';
        $sql = "SELECT {$elementsStr} FROM items WHERE {$column} LIKE '%{$searchStr}%';";
        //        Debug::dump($sql, 'sql string in ' . __METHOD__ . ' line ' . __LINE__);
        $pdo = db::getInstance();
        $stmt = $pdo->prepare($sql);
        try {
            $stmt->execute();
        } catch (Exception $ex) {
            array_push(
                $this->errors,
                util::simplifyArray($ex->getTraceAsString()),
                util::simplifyArray($stmt->errorInfo())
            );
        }
        $list = $stmt->fetchAll();
        return $list;
    }

    private function getOwnItem($ownership_id)
    {
        if (!isset($ownership_id) or util::IsNullOrEmptyString($ownership_id))
            return $this->generateEmptyOwnItem($ownership_id);

        $pdo = db::getInstance();
        $sqlStr = "SELECT * FROM `ownership` WHERE `ownership_id` = :ownership_id LIMIT 1";
        $stmt = $pdo->prepare($sqlStr);
        $ownership_id_val = intval($ownership_id);
        try {
            $stmt->bindParam(
                ':ownership_id',
                $ownership_id_val,
                // intval($ownership_id),
                PDO::PARAM_INT
            );
            $stmt->execute();
        } catch (Exception $exc) {
            Debug::dump(
                $stmt->errorInfo(),
                'error in ' . __METHOD__ . ' line ' . __LINE__
            );
            $this['errors'][] = $stmt->errorInfo();
            $this['errors'][] = $exc->getMessage();
        }
        $item = $stmt->fetch();
        return $item;
    }

    public function listAllOwnershipItems()
    {
        $pdo = db::getInstance();
        $sqlStr = "SELECT * FROM `ownership` ORDER BY `item_id`";
        $sqlStr = <<<EOT
            SELECT
                ownership.*,items.registration, items.caption_he,items.companyHe, items.modelHe,items.year,items.sourceHe
            FROM
                `ownership`
            JOIN `items` ON items.item_id = ownership.item_id
            ORDER BY `item_id` 
            EOT;

        $stmt = $pdo->prepare($sqlStr);
        try {
            $stmt->execute();
        } catch (Exception $exc) {
            Debug::dump(
                $stmt->errorInfo(),
                'error in ' . __METHOD__ . ' line ' . __LINE__
            );
            $this['errors'][] = $stmt->errorInfo();
            $this['errors'][] = $exc->getMessage();
        }
        $items = $stmt->fetchAll();
        $sievedItems = $this->sieveCurrentOwnerships($items);
        return $this->renderListAll($sievedItems);
    }
    private function sieveCurrentOwnerships($items)
    {
        // Debug::dump($items, 'Items in ' . util::getCaller());
        // Debug::dump(date('Y-m-d', mktime(0, 0, 0, 0, 0, 1900)), util::getCaller());
        $detected = [];
        $returnItems = [];
        foreach ($items as $key => $item) {
            $UnixTimestamp =  mktime(0, 0, 0, $item['transaction_month'] ?? 0, $item['transaction_day'] ?? 0, $item['transaction_year'] ?? 1900);
            $detected[$item['item_id']][] = [ // group all items with same id
                // 'id' => $item['item_id'],
                'time' => $UnixTimestamp,
                // 'ownership_id' => $item['ownership_id'],
                // 'date' => date('Y-m-d', $UnixTimestamp),
                'item' =>$item,
            ];
            // $returnItems[] = $item;
        }
        // Debug::dump($detected, 'detected in ' . util::getCaller());
        foreach ($detected as $key=>$dItem) { // sort the gouped items. most recent is the last (end) element
            if (count($dItem) > 1) {
                usort($dItem, function ($a, $b) {
                    return  $a['time'] <=> $b['time'];
                });
                $detected[$key] = $dItem;
            }
            // Debug::dump($dItem, 'dItem in ' . util::getCaller());
        }
        // Debug::dump($detected, 'detected in ' . util::getCaller());
        foreach ($detected as $dItem){
            if (count($dItem) > 1) {
                $returnItems[] = end($dItem)['item'];
            } else {
                $returnItems[] = $dItem[0]['item'];
            }
        }
        // Debug::dump($returnItems, 'returnItems in ' . util::getCaller());
        return $returnItems;
    }
    private function renderListAll($items)
    {
        $headerItems = ['פריט', 'רישום', 'שם פריט', 'ברשות', 'מאז'];
        // Debug::dump(sizeof($items), util::getCaller());
        $table_pre = '<table id="allOwnerships" class="table table-sm table-hover table-responsive tablesorter tablesorter-default">';
        $table_post = '</table>';
        $thead = '<thead><tr><th>' . join('</th><th>', $headerItems) . '</th>' . '</tr></thead>';
        $formatedItem = [];
        // $acc = [];
        foreach ($items as $key => $item) {
            $id = $item['item_id'];
            // $time = mktime(
            //     0,
            //     0,
            //     0,
            //     $item['transaction_month'],
            //     $item['transaction_day'],
            //     $item['transaction_year']
            // );
            /* if (isset($formatedItem[$id])) {
                if ($acc[$id]['time'] > $time) {
                    // break;
                }
            } */
            // if (isset($formatedItem[$id])) {
            //     if ($acc[$id]['time'] > $time) {
            // continue;
            // } 
            // }
            // $acc[$id] = [
            //     'time' => $time,
            //     'date' => date('Y-m-d', $time),
            //     'y' => $item['transaction_year'],
            //     'm' => $item['transaction_month'],
            //     'd' => $item['transaction_day']
            // ];
            $name = collection::renderTitle($item);
            $since = util::renderIncompeteDate(
                $item['transaction_year'],
                $item['transaction_month'],
                $item['transaction_day']
            );
            $itemLink = <<<EOF
                <a href="/collection/item/{$id}" target=_blank>{$name}</a>
                EOF;
            $ownershipEditLink = <<<EOF
                <a href="/ownership/{$id}" target="_blank">$this->editIcon {$id}</a>
                EOF;
            $formatedItem[] = <<<EOF
                <td>{$ownershipEditLink}</td><td>{$item['registration']}</td><td>{$itemLink}</td><td>{$item['owner']}</td><td>{$since}</td>
                EOF;
        }
        $content = $table_pre . $thead . '<tbody>' . '<tr>' . join(
            '</tr><tr>',
            $formatedItem
        ) . '</tr>' . '</tbody>' . $table_post;
        return $content;
    }

    private function generateEmptyOwnItem($item_id)
    {
        $form = new form('ownership');
        $emptyItem = $form->genEmptyRecord();
        /*        $emptyItem = [];
       $fields = $this->getFields();
       //        Debug::dump($fields, ' fields in '.__METHOD__ . ' line ' . __LINE__);
       foreach ($fields as $key => $value) {
           $emptyItem[$value['COLUMN_NAME']] = $value['COLUMN_DEFAULT'];
       }
       $emptyItem['item_id'] = $item_id;
       //        Debug::dump($emptyItem, ' fields in '.__METHOD__ . ' line ' . __LINE__); */
        return $emptyItem;
    }

    private function getAllOwnRecordItem($item_id)
    {
        $sqlStr = "SELECT * FROM `ownership` WHERE `item_id` = :item_id";
        $pdo = db::getInstance();
        $stmt = $pdo->prepare($sqlStr);
        $stmt->bindParam(':item_id', $item_id, PDO::PARAM_INT);
        try {
            $stmt->execute();
        } catch (Exception $ex) {
            $this['errors'][] = $stmt->errorInfo();
            $this['errors'][] = $ex->getMessage();
            Debug::dump(
                $stmt->errorInfo(),
                'errorInfo ' . __METHOD__ . ' line ' . __LINE__
            );
            Debug::dump(
                $ex->getMessage(),
                'getMessage ' . __METHOD__ . ' line ' . __LINE__
            );
        }
        return $stmt->fetchAll();
    }

    private function renderOwnershipRecords($list)
    {
        $items = [];
        foreach ($list as $key => $value) {
            $date = util::renderIncompeteDate(
                $value['transaction_year'],
                $value['transaction_month'],
                $value['transaction_day']
            );
            $items[] = <<<EOF
                <a href="/ownership/{$value['item_id']}/{$value['ownership_id']}">
                {$date} בעלות
                {$value['owner']}
                </a>
                EOF;
        }
        return join('<br />', $items);
    }

    private function renderTitle($item_id)
    {
        $tractor_record = collection::getFullItem($item_id);
        return collection::renderTitle($tractor_record);
    }

    static public function renderOwnershipString($item_id)
    {
        if (util::IsNullOrEmptyString($item_id)) {
            return null;
        }
        $pdo = db::getInstance();
        $sqlStr = "SELECT * FROM `ownership` WHERE `item_id` = :item_id ORDER by `transaction_year` DESC,`transaction_month`DESC,`transaction_day`DESC LIMIT 1";
        $stmt = $pdo->prepare($sqlStr);
        $id = intval($item_id);
        try {
            $stmt->bindParam(':item_id', $id, PDO::PARAM_INT);
            $stmt->execute();
        } catch (Exception $exc) {
            // Debug::dump(
            //     $stmt->errorInfo(),
            //     'error in ' . __METHOD__ . ' line ' . __LINE__
            // );
            // $this->errors[] = $stmt->errorInfo();
            // $this->errors[] = $exc->getMessage();
        }
        $item = $stmt->fetch();
        if (isset($item['owner'])){
        return $item['owner'];}
        
    }
}
