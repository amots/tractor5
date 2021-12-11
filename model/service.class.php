<?php

/**
 * Description of service
 *
 * @author amots
 * @since 2021-03030
 */
class service
{

    var $errors = [];
    var $messages = [];
    private $searchData = [
        'item_id', 'PageHe', 'registration', 'vin', 'engine_number',
        'sn', 'companyHe'
    ];
    static $onHoldTypes = ['0' => 'בעבודה', '1' => 'בהמתנה'];

    private $retrieveStr = <<<EOF
            SELECT
            service_id,
            service.`item_id`,
            people.sur_name_he,
            people.last_name_he,
            items.caption_he,
            items.caption_en,
            items.companyHe,
            items.companyEn,
            items.modelHe,
            items.modelEn,
            items.year,
            items.sourceHe,
            items.sourceEn,
            items.registration,
            storage_location.name as `location`
        FROM
            `service`
        JOIN items ON items.item_id = service.item_id
        LEFT JOIN people ON people.people_id = service.service_people_id
        LEFT JOIN storage_location ON storage_location.location_id = items.location
        EOF;
    private $tableHeaders;
    static $status = [0=>'complete',1=>'onHold',2=>'atWork'];
    static $statusStyle = [0=>'table-success',1=>'table-warning',2=>'table-danger'];
    public function __construct()
    {
        $itemNameLiteral = Lang::trans('service.itemName');
        $inChargeLiteral = Lang::trans('service.personInCharge');
        $registractionLiteral = Lang::trans('item.registration');
        $locationLiteral = 'מיקום';
        $this->tableHeaders = <<<EOF
            <thead><tr><td class="noSort"></td><td>$registractionLiteral</td><td>{$inChargeLiteral}</td><td>{$itemNameLiteral}</td>
            <td>{$locationLiteral}</td></tr></thead>
            EOF;
    }

    public function renderatWorkListBoard()
    {
        $sql = <<<EOF
            {$this->retrieveStr} 
            WHERE (`close_date` IS NULL AND (`on_hold` = 0 OR `on_hold` IS NULL)) 
            ORDER BY `companyHe`
            EOF;
        $list = $this->getServicedList($sql);
        return $this->renderServicedItems($list, 'atWork');
    }

    private function getServicedList($sql)
    {
        $pdo = db::getInstance();
        try {
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
        } catch (Exception $exc) {
            $this->errors[] = $exc->getTraceAsString();
            $this->errors[] = $stmt->errorInfo();
        }
        return $stmt->fetchAll();
    }

    public function renderOnHoldListBoard()
    {
        $sql = $this->retrieveStr . " WHERE `close_date` IS NULL and `on_hold` = 1 ORDER BY `companyHe`";
        $list = $this->getServicedList($sql);
        return $this->renderServicedItems($list, 'onHold');
    }

    private function renderServicedItems($list, $id)
    {
        $items = [];
        $editIcon = list_items::$biPencilSquare;
        foreach ($list as $key => $value) {
            $link2item = "/collection/item?id={$value['item_id']}";
            $link2service = "/service/editService/{$value['item_id']}/{$value['service_id']}";
            $title = collection::renderTitle($value);
            $person = join(' ', [$value['sur_name_he'], $value['last_name_he']]);
            $items[] = <<<EOF
                <td><a href="$link2service">{$editIcon}</a></td>
                <td>{$value['registration']}</td>
                <td>{$person}</td>
                <td><a href="{$link2item}" target="_blank">{$title}</a></td>
                <td>{$value['location']}</td>
                EOF;
        }
        $joinedList = join('</tr><tr>', $items);
        $renderedList = <<<EOF
            <table class="table tablesorter table-hover" id={$id}>{$this->tableHeaders}<tr>{$joinedList}</tr></table>
            EOF;
        return $renderedList;
    }

    public function renderServiceSearchPage()
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
        $renderer = new template_renderer(
            __SITE_PATH . '/includes/mng/search_page.html',
            $data
        );
        return $renderer->render();
    }

    private function renderSearchContent()
    {
        if (!isset($_POST['submit'])) {
            return NULL;
        }
        if (!util::validatePostToken('csrf_token', 'csrf_token')) {
            $_SESSION['errors'][] = 'failed to validate token';
            header('location: /service');
        }
        unset($_SESSION['csrf_token']);
        $list = $this->getSearchData();
        $listArray = [];
        foreach ($list as $key => $item) {
            $listArray[] = $this->renrerResultItem($item);
        }
        return join('<br />', $listArray);
    }

    public function getItem($requestID)
    {
        $pdo = db::getInstance();

        $whereStr = "item_id=:requestID";
        $fromStr = "items";
        $sqlStr = sprintf("SELECT * FROM %s WHERE %s;", $fromStr, $whereStr);
        $sth = $pdo->prepare($sqlStr);
        try {
            $sth->execute(array(':requestID' => $requestID));
        } catch (Exception $ex) {
            array_push(
                $this->errors,
                util::simplifyArray($ex->getTraceAsString())
            );
            array_push($this->errors, util::simplifyArray($stmt->errorInfo()));
        }

        $results = $sth->fetch();
        return ($results);
    }

    public function getRecord($service_id)
    {
        $pdo = db::getInstance();
        $sql = "SELECT * FROM service WHERE service_id = :service_id LIMIT 1";
        $stmt = $pdo->prepare($sql);
        try {
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['service_id' => $service_id]);
        } catch (Exception $exc) {
            $this->errors[] = $exc->getTraceAsString();
            $this->errors[] = $stmt->errorInfo();
        }
        $record = $stmt->fetch();
        return $record;
    }

    public function getListOfRecords($id)
    {
        $pdo = db::getInstance();
        $sql = "SELECT * FROM `service` WHERE `item_id` = :id;";
        try {
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['id' => $id]);
        } catch (Exception $exc) {
            $this->errors[] = $exc->getTraceAsString();
            $this->errors[] = $stmt->errorInfo();
        }
        $results = $stmt->fetchAll();
        return $this->renderItemServicesList($results, $id);
    }

    private function renderItemServicesList($list, $id)
    {
        $newLink = sprintf('<a href="/service/editService/%s">חדש</a>', $id);
        $items = [];
        foreach ($list as $key => $item) {
            $brief = util::shorten_string($item['note'], 30);
            $items[] = <<<EOF
                <a href="/service/editService/{$item['item_id']}/{$item['service_id']}">ערוך</a>
                {$item['open_date']}
                {$brief}
                EOF;
        }
        $items[] = '<hr><div>' . $newLink . '</div>';
        return join('<br />', $items);
    }

    public function renderEditPage($record, $list)
    {
        $token = util::RandomToken();
        $_SESSION['csrf_token'] = $token;
        $record['csrf_token'] = $token;
        $record['listOfRecords'] = $list;
        $templateFile = __SITE_PATH . '/includes/service/editService.html';
        $renderer = new template_renderer($templateFile, $record);
        return $renderer->render();
    }

    public function renderServiceMenu()
    {
        $renderer = new template_renderer(__SITE_PATH . '/includes/service/serviceNav.html');
        return $renderer->render();
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
            'item_id', 'caption_he', 'companyHe', 'modelHe', 'sourceHe', 'registration',
            'year'
        ];
        $elementsStr = '`' . join('`,`', $elements2get) . '`';
        $sql = "SELECT {$elementsStr} FROM items WHERE {$column} LIKE '%{$searchStr}%';";
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

    public function renderItemDesc($item)
    {
        $retData = [];
        foreach (['caption_he', 'companyHe', 'modelHe', 'year', 'sourceHe'] as $key) {
            if (!util::IsNullOrEmptyString($item[$key])) {
                $retData[] = $item[$key];
            }
        }
        $textStr = join(", ", $retData);
        return $textStr;
    }

    public function renderServiceOnHoldForm($type)
    {
        $rows = [];
        foreach (self::$onHoldTypes as $key => $literal) {
            $active = ($key == $type) ? 'checked = "checked"' : NULL;
            $rows[] = <<<EOF
                <input type="radio" name="on_hold" value="{$key}" {$active}> {$literal}
                EOF;
        }
        return '<div class="radio">' . join('<br />', $rows) . '</div>';
    }

    public function renderServicePersonOptions($service_people_id)
    {
        $list = $this->getServicePeopleList();
        $options = ['<option value="">---</option>'];
        foreach ($list as $key => $value) {
            $selected = $service_people_id == $value['people_id'] ? 'selected' : NULL;
            $full_name = join(
                ' ',
                [$value['sur_name_he'], $value['last_name_he']]
            );
            $val = $value['people_id'];
            $options[] = <<<EOF
                <option value="{$val}" $selected>{$full_name}</option>
                EOF;
        }
        return join('', $options);
    }

    public function renderListAllPage()
    {
        $sql = <<<EOF
            SELECT
                service.item_id,
                service.open_date,
                service.close_date,
                service.on_hold,
                items.year,
                items.companyHe,
                items.modelHe,
                items.caption_he,
                items.sourceHe,
                items.registration
            FROM
                `service`
            JOIN items ON service.item_id = items.item_id
                #GROUP by service.item_id
            ORDER BY
                items.item_id
            EOF;
        $raw =    $this->getServicedList($sql);
        foreach ($raw as $key=>$item) {
            $status = 2;
            if ($item['on_hold']) $status = 1;
            if (!util::IsNullOrEmptyString($item['close_date'])) $status = 0;
            $raw[$key]['status'] = $status;
        }
        // Debug::dump($raw, 'raw at ' . util::getCaller());
        $data = $this->consolidateServiced($raw);
        // Debug::dump($data, 'list at ' . util::getCaller());
        $listClass = new list_items($data);
        return $listClass->renderAllServiced();
    }

    private function consolidateServiced($list)
    {
        $reducedList = [];
        foreach ($list as $value) {
            // Debug::dump($value, 'value at ' . util::getCaller());
            $id = $value['item_id'];
            if (array_key_exists($id, $reducedList)) {
                $count = $reducedList[$id]['count'];
                // if ($value['close_date'] > $reducedList[$id]['close_date']) {
                //     $reducedList[$id] = $value;
                // }
                if($value['status'] > $reducedList[$id]['status']) {
                    $reducedList[$id]['status'] = $value['status'];
                }
                $reducedList[$id]['count'] = $count + 1;
            } else {
                $reducedList[$id] = $value;
                $reducedList[$id]['count'] = 1;
            }
            // Debug::dump($reducedList[$id], 'new value at ' . util::getCaller());
        }
        return $reducedList;
    }
    private function getServicePeopleList()
    {
        $sql = <<<EOF
            SELECT
                people.`people_id`,people.sur_name_he, people.last_name_he
            FROM
                `service_people`
            JOIN people ON service_people.people_id = people.people_id
            ORDER BY
                people.sur_name_he
            EOF;
        $pdo = db::getInstance();
        $stmt = $pdo->prepare($sql);
        try {
            $stmt->execute();
        } catch (Exception $ex) {
            $_SESSION['errors'][] = $stmt->errorInfo();
            Debug::dump(
                $stmt->errorInfo(),
                'error in ' . __METHOD__ . ' line ' . __LINE__
            );
        }
        $list = $stmt->fetchAll();
        return $list;
    }

    private function renrerResultItem($item)
    {
        $retData = [];
        $editIcon = list_items::$biPencilSquare;
        foreach (['registration', 'caption_he', 'companyHe', 'modelHe', 'year', 'sourceHe'] as
            $key) {
            if (!util::IsNullOrEmptyString($item[$key])) {
                $retData[] = $item[$key];
            }
        }

        $textStr = join(" ", $retData);
        $ret = <<<EOF
            <a href="/service/editService/{$item["item_id"]}">{$editIcon}</a>
            {$textStr}
            EOF;
        return $ret;
    }
}
