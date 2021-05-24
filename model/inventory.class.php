<?php

/**
 * Description of inventory
 *
 * @author amots
 */
class inventory
{

    private $searchData = [
        'item_id', 'PageHe', 'registration', 'vin', 'engine_number',
        'sn', 'companyHe'
    ];
    private $errors = [];

    function __construct()
    {
    }

    public function renderList($param)
    {
        $lines = [];
        $data = $this->getInventoryRecords($param);
        $renderer = new template_renderer(__SITE_PATH . '/includes/mng/inventoryNav.html');
        $inventoryNav = $renderer->render();
        $isAdmin = User::permission() & User::permission_administrator;
        $infoIcon = file_get_contents(__SITE_PATH . "/assets/media/icons/noun_Info_2269947.svg");
        foreach ($data as $key => $item) {
            $enableViewRestricted = $item['display'] > 0 ? '' : '&' . collection::$viewRestricted;
            $itemLink = "/collection/item/" . $item['item_id'] . $enableViewRestricted;
            $editLink = "/inventory/editItem/{$item['item_id']}";
            if (strtoupper($param) == 'ARCHIVE') {
                if (!$isAdmin) {
                    $editLink = $itemLink;
                }
            } 

            $name = collection::renderTitle($item);
            // $registrationTxt = (util::IsNullOrEmptyString($item['registration'])) ? 'N/A' : $item['registration'];

            $statusText = (isset(collection::$status[$item['status']]['he'])) ? collection::$status[$item['status']]['he']
                : '';
            $displayed = $item['display'] > 0 ? '' : list_items::$thumbsDown;
            $editIcon = ((strtoupper($param) == 'ARCHIVE') and !$isAdmin) ? $infoIcon : list_items::$biPencilSquare;
            $lines[] = <<<EOF
                <td class="text-right">
                    <a href="{$editLink}">
                    <span class="svg-icon svg-baseline">{$editIcon}</span>
                    {$item['item_id']}
                    </a>
                </td>
                <td class="text-left"><span dir="ltr">{$item['registration']}</span></td>   
                <td>{$displayed}</td>
                <td><a href="{$itemLink}" target=_blank>{$name}</a></td>
                <td>{$item['location']}</td>
                <td>{$statusText}</td>
                EOF;
        }
        $linesStr = '<tr>' . join('</tr><tr>', $lines) . '</tr>';
        $retStr = <<<EOF
            <div class="text-center py-2">{$inventoryNav}</div>
            <table id="list2Sort" class="table table-sm table-hover table-responsive tablesorter">
                <thead>
                    <tr>
                        <th>#</th>
                        <th id="right" class="text-left">רישום</th>
                        <th class="noSort">&nbsp;</th>
                        <th>שם</th>
                        <th>אתר</th>
                        <th>מצב</th>
                    </tr>
                </thead>
                <tbody>
                    {$linesStr}
                </tbody>
            </table>
            EOF;
        return $retStr;
    }

    private function getInventoryRecords($param)
    {
        $sql = $this->renderSql4List($param);
        $pdo = db::getInstance();
        try {
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
        } catch (Exception $ex) {
            array_push($this->errors, $ex->getMessage());
            array_push($_SERVER['errors'], $ex->getMessage());
            return NULL;
        }
        $items = $stmt->fetchAll();
        return $items;
    }

    public static function renderInventoryDesc($id)
    {
        $item = collection::getFullItem($id);
        $fields = ['registration', 'vin', 'engine_number', 'sn'];
        $list = [];
        foreach ($fields as $fieldName) {
            if (!util::IsNullOrEmptyString($item[$fieldName])) {
                $list[] = Lang::trans("item.{$fieldName}") . ': ' . $item[$fieldName];
            }
        }
        return join("; ", $list);
    }

    public function renderInventorySearchPage()
    {
        $data = [];
        foreach ($this->searchData as $key) {
            $data[$key] = isset($_POST[$key]) ? $_POST[$key] : NULL;
        }
        $data['searchResults'] = $this->renderSearchContent();
        $token = util::RandomToken();
        $_SESSION['csrf_token'] = $token;
        $data['csrf_token'] = $token;
        $data['searchIcon'] = list_items::$searchIcon;
        $renderer = new template_renderer(__SITE_PATH . '/includes/mng/inventoryNav.html');
        $inventoryNav = $renderer->render();
        $inventoryNav = <<<EOF
            <div class="text-center py-2">{$inventoryNav}</div>
            EOF;
        $renderer = new template_renderer(
            __SITE_PATH . '/includes/mng/search_page.html',
            $data
        );
        $searchForm = $renderer->render();
        return $inventoryNav . $searchForm;
    }

    private function renderSearchContent()
    {
        if (!isset($_POST['submit'])) {
            return NULL;
        }
        if (!util::validatePostToken('csrf_token', 'csrf_token')) {
            $_SESSION['errors'][] = 'failed to validate token';
            header('location: /inventory');
        }
        unset($_SESSION['csrf_token']);
        $list = $this->getSearchData();
        //        Debug::dump($list, 'list in ' . __METHOD__ . ' line ' . __LINE__);
        $listArray = [];
        foreach ($list as $key => $item) {
            $listArray[] = $this->renrerResultItem($item);
            //            Debug::dump($item, 'item in ' . __METHOD__ . ' line ' . __LINE__);
        }
        //        Debug::dump($listArray,
        //                'listArray in ' . __METHOD__ . ' line ' . __LINE__);
        return join('<br />', $listArray);
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
            'item_id', 'companyHe', 'modelHe', 'sourceHe', 'registration',
            'year'
        ];
        $elementsStr = '`' . join('`,`', $elements2get) . '`';
        $sql = "SELECT {$elementsStr} FROM items WHERE {$column} LIKE '%{$searchStr}%';";
        //    Debug::dump($sql, 'sql string in ' . __METHOD__ . ' line ' . __LINE__);
        $pdo = db::getInstance();
        $stmt = $pdo->prepare($sql);
        try {
            $stmt->execute();
        } catch (Exception $ex) {
            array_push(
                $this->errors,
                util::simplifyArray($ex->getTraceAsString())
            );
            array_push($this->errors, util::simplifyArray($stmt->errorInfo()));
        }
        $list = $stmt->fetchAll();
        return $list;
    }

    private function renrerResultItem($item)
    {
        $retData = [];
        $editIcon = list_items::$biPencilSquare;
        foreach (['registration', 'companyHe', 'modelHe', 'year', 'sourceHe'] as
            $key) {
            if (!util::IsNullOrEmptyString($item[$key])) {
                $retData[] = $item[$key];
            }
        }

        $textStr = join(" ", $retData);
        $ret = <<<EOF
                <a href="/inventory/editItem/{$item["item_id"]}">{$editIcon}</a>
                {$textStr}
                EOF;
        return $ret;
    }

    private function renderSql4List($param)
    {
        $groups = collection::get_collection_groups();
        // $where = 1;
        $where[] = '(`archive` = 0 or `archive` is null)';
        $found = FALSE;
        foreach ($groups as $key => $value) {
            if ($param == strtoupper($value['link'])) {
                if ($value['collection_group_id'] == 1) {
                    $where[] = <<<EOF
                        `mGroup` = 1 and upper(left(`registration`,1)) = "T";
                        EOF;
                    $found = TRUE;
                    break;
                } else {
                    $where[] = "`mgroup` = {$value['collection_group_id']}";
                    $found = TRUE;
                    break;
                }
            }
            if (!$found and $param == 'CTRACK') {
                $where[] = 'left(`registration`,1) = "5"';
                $found = TRUE;
            }
            if (!$found and $param == 'ARCHIVE') {
                $where = ["`archive` = 1"];
                $found = TRUE;
            }
        }

        $field2get = join(
            ',',
            [
                'item_id', 'registration', 'vin', 'companyHe', 'companyEn', 'modelHe',
                'modelEn',
                'drive_mechanism', 'fuel_type', 'color', 'year', 'status', 'sourceHe',
                'sourceEn',
                'sl.name as location', 'display'
            ]
        );
        $whereStr = join(' AND ', $where);
        // Debug::dump($whereStr, 'where string in ', util::getCaller());
        $sql = <<<EOF
            SELECT {$field2get} FROM `items` it 
                LEFT JOIN storage_location sl ON
                sl.location_id = it.`location` 
            WHERE {$whereStr}
            EOF;
        //    Debug::dump([$param,$where], 'where in ' . __METHOD__ . ' line ' . __LINE__);
        return $sql;
    }
}
