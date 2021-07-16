<?php

/**
 * @author amots
 * @since 2021-07-14
 */
class search
{
    private $searchStructure = [
        'item_id' => ['dir' => 'ltr', 'fields' => ['item_id'], 'literal' => 'קוד פריט'],
        'free_text' => ['dir' => 'rtl', 'fields' => ['caption_he',  'PageHe'], 'literal' => 'טקסט'],
        'registration' => ['dir' => 'ltr', 'fields' => ['registration'], 'literal' => 'קוד רישום'],
        'vin' => ['dir' => 'ltr', 'fields' => ['vin'], 'literal' => 'מספר שילדה'],
        'engine_number' => ['dir' => 'ltr', 'fields' => ['engine_number'], 'literal' => 'מספר מנוע'],
        'sn' => ['dir' => 'ltr', 'fields' => ['sn'], 'literal' => 'מספר סידורי']
    ];
    private $searchData = [
        'item_id', 'caption_he', 'PageHe', 'registration', 'vin', 'engine_number',
        'sn', 'companyHe'
    ];
    private $target_url;
    private $view_url;
    public function __construct($target_url, $view_url = null)
    {
        $this->target_url = $target_url;
        $this->view_url = $view_url;
        // Debug::dump($_SERVER,'server at ' . util::getCaller());
    }

    public function renderSearchPage()
    {
        $data = [];
        foreach ($this->searchData as $key) {
            $data[$key] = isset($_POST[$key]) ? $_POST[$key] : NULL;
        }
        $data['searchResults'] = $this->renderSearchContent();
        $form = $this->renderSearchForm();
        $data['genratedForm'] = $form;
        // $token = util::RandomToken();
        // $_SESSION['csrf_token'] = $token;
        // $data['csrf_token'] = $token;
        // $data['searchIcon'] = list_items::$searchIcon;
        $renderer = new template_renderer(
            __SITE_PATH . '/includes/mng/search_page.html',
            $data
        );
        $searchForm = $renderer->render();
        return $searchForm;
    }

    public function renderSearchContent()
    {
        if (!isset($_POST['submit'])) {
            return NULL;
        }
        if (!util::validatePostToken('csrf_token', 'csrf_token')) {
            $_SESSION['errors'][] = 'failed to validate token';
            header('location: ' . $_SERVER['REQUEST_URI']);
        }
        unset($_SESSION['csrf_token']);
        $list = $this->getSearchData();
        //    Debug::dump($list, 'list in ' . __METHOD__ . ' line ' . __LINE__);
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
        $wherStr = $this->buildWhereStr();
        // Debug::dump($wherStr, 'where string in ' . util::getCaller());
        foreach ($this->searchData as $key) {
            if (isset($_POST[$key])) {
                $column = $key;
                $searchStr = $_POST[$key];
            }
        }
        $elements2get = [
            'item_id', 'companyHe', 'caption_he', 'modelHe', 'sourceHe', 'registration',
            'year'
        ];
        $elementsStr = '`' . join('`,`', $elements2get) . '`';
        // $sql = "SELECT {$elementsStr} FROM items WHERE {$column} LIKE '%{$searchStr}%';";
        $sql = "SELECT {$elementsStr} FROM items WHERE {$wherStr}";
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
        foreach (['registration', 'caption_he', 'companyHe', 'modelHe', 'year', 'sourceHe'] as
            $key) {
            if (!util::IsNullOrEmptyString($item[$key])) {
                $retData[] = $item[$key];
            }
        }

        $viewStr = $textStr = join(" ", $retData);
        if (!util::IsNullOrEmptyString($this->view_url)) {
            $viewStr = <<<EOF
            <a href="{$this->view_url}/{$item["item_id"]}" target=_blank>{$textStr}</a>
            EOF;
        }
        $ret = <<<EOF
                <a href="{$this->target_url}{$item["item_id"]}">{$editIcon}</a>
                {$viewStr}
                EOF;
        return $ret;
    }
    private function renderSearchForm()
    {
        $searchIcon = list_items::$searchIcon;
        $csrf_token = util::RandomToken();
        $_SESSION['csrf_token'] = $csrf_token;
        $formData = [];
        foreach (array_keys($this->searchStructure) as $key) {
            $formData[$key] = isset($_POST[$key]) ? $_POST[$key] : NULL;
        }
        // Debug::dump($formData, 'form data in ' . util::getCaller());
        $formComponents = [];
        foreach ($this->searchStructure as $key => $data) {
            $directionClass = $data['dir'] == 'ltr' ? 'ltr text-left' : '';
            $formComponents[] = <<<EOF
            <form  role="search" method="POST">
                <input type="hidden" name="csrf_token" id="token" value="{$csrf_token}" />
                <div class="container-fluid">
                <div class="row">    
                    <div class="col-md-4  text-wrap">
                    <label for="{$key}" >{$data['literal']}</label>
                    </div>
                    <div class="col-md-6" >
                    <input type="search" name="{$key}" id="{$key}" class="form-control {$directionClass}" 
                            placeholder="{$data['literal']}" value="{$formData[$key]}" />
                    </div>
                    <div class="col-md-2">
                    <button type="submit" name="submit" id="search_submit" class="btn btn-primary btn-sm">{$searchIcon}</button>
                    </div>
                </div>
                </div>
            </form>
            EOF;
        }
        return join(' ', $formComponents);
    }
    private function buildWhereStr()
    {
        $strArray = [];
        foreach ($this->searchStructure as $key => $data) {
            if (isset($_POST[$key])) {
                foreach ($data['fields'] as $DbField) {
                    $strArray[] = <<<EOF
                        `{$DbField}` LIKE '%$_POST[$key]%'
                        EOF;
                }
            }
        }
        return join(' OR ', $strArray);
    }
}
