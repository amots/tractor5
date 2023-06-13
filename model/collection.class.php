<?php

/**
 * Description of collection
 *
 * @author amots
 * @since 2021-03-10
 */
class collection
{

    public static $viewRestricted = 'forceview';
    protected $mGroup;
    public static $status = [
        '' => ['he' => '', 'en' => ''],
        '1' => ['he' => 'מצויין', 'en' => 'Excellent'],
        '2' => ['he' => 'תקין', 'en' => 'Functional'],
        '3' => ['he' => 'בשיפוץ', 'en' => 'In repair'],
        '4' => ['he' => 'זקוק לשיפוץ', 'en' => 'To be repaired'],
        '5' => ['he' => 'לא תקין', 'en' => 'Nonfunctional']
    ];


    public function __construct()
    {
        $this->mGroup = self::collectionGroupsIndexed();
    }

    public function renderCompaniesList($title = NULL)
    {
        $companies = $this->getCompaniesList($title);
        if ($title) {
            $names = [];
            // $index = 'company' . ucfirst(Lang::getLocale());
            $index = 'company';
            foreach ($companies as $value) {
                // $data = ['comp'=>];
                $searchStr = urlencode($value[$index]);
                $names[] = <<<EOF
                    <a href="/collection/compList?comp={$searchStr}&t={$title}">
                        $value[$index]
                    </a>
                    EOF;
            }
        } else {
            $names = $this->renderCategorizedCompaniesList($companies);
        }
        return join(' &bull; ', $names);
    }

    private function renderCategorizedCompaniesList($companies)
    {
        $items = $names = [];
        foreach ($companies as $value) {
            $items[$value['mGroup']][] = $value['company'];
        }
        foreach ($this->mGroup as $key => $value) {
            $txt = $value['group_' . lang::getLocale()];
            $names[] = <<<EOT
            
                <a href="/collection/{$key}">
                <button type="button" class="btn btn-outline-secondary btn-sm" 
                style=padding:0.1rem;>
                {$txt}</button>
                </a>
                EOT;
            foreach ($items[$key] as $company) {
                $names[] = <<<EOF
                    <a href="/collection/compList?comp={$company}&t={$key}">
                        {$company}
                    </a>
                    EOF;
            }
        }
        return $names;
    }


    private function getCompaniesList($title)
    {
        // Debug::dump($_SERVER['HTTP_REFERER'], 'referer at ' . util::getCaller());
        $lang = ucfirst(Lang::getLocale());
        // debug::dump($lang,'lang at ' . util::getCaller());
        $pdo = db::getInstance();
        $mgroupWhere = null;
        if (!util::IsNullOrEmptyString($title)) {
        //     $mgroupWhere = null;
        // } else {
            $mgroupWhere = "`mGroup` = :title AND ";
        }
        $sql = <<<EOF
            SELECT DISTINCT `company{$lang}`AS company, `mGroup` FROM `items`
            WHERE
                {$mgroupWhere} (`archive` IS NULL 
                OR `archive` = 0) 
                AND (NOT `company{$lang}` IS NULL)
                AND `display` = 1
            ORDER BY `company{$lang}`
            EOF;
        // Debug::dump($sql, util::getCaller());
        $stmt = $pdo->prepare($sql);
        if (!util::IsNullOrEmptyString($mgroupWhere))
            $stmt->bindParam(':title', $title);

        try {

            $stmt->execute();
            // $stmt->execute([':title' => $title]);
        } catch (Exception $ex) {
            Debug::dump($stmt->errorInfo(), 'error in ' . util::getCaller());
            Debug::dump($ex->getMessage(), util::getCaller());
            return NULL;
        }
        $companies = $stmt->fetchAll();
        return $companies;
    }

    public function renderCollectionLandingPage()
    {
        $collage = new collage();
        $data = $this->genLandingPageData();
        return $collage->renderCollage($data, 2, 2, true);
    }

    private function genLandingPageData()
    {
        /*
         * path to picture
         * link
         */

        return [
            [
                'path' => '/assets/media/pics/items/274_Dodge_power_wagon_IMG_2798.jpg',
                'link' => '/collection/2',
                'literal' => Lang::trans('collage.vehicles'),
                'mGroup' => 2,
            ],
            [
                'path' => '/assets/media/pics/items/295_grinder_img_4451.jpg',
                'link' => '/collection/4',
                'literal' => Lang::trans('collage.collections'),
                'mGroup' => 4,
            ],
            [
                'path' => '/assets/media/pics/items/PorscheJunior_after.jpg',
                'link' => '/collection/1',
                'literal' => Lang::trans('collage.tractors'),
                'mGroup' => 1,
            ],
            [
                'path' => '/assets/media/pics/items/49_woodenSprayer.jpg',
                'link' => '/collection/3',
                'literal' => Lang::trans('collage.tools'),
                'mGroup' => 3,
            ],
        ];
    }

    public function renderCollectionGroupPage($collection_group)
    {
        $list = $this->getItemsByGroup($collection_group);
        foreach ($list as $key => $value) {
            $list[$key]['title'] = $this->renderTitle($value);
        }
        $titles = array_column($list, 'title');
        array_multisort($titles, SORT_ASC, $list);
        $gridDir = Lang::getLocale() == 'he' ? 'false' : 'true';
        $items = [];
        foreach ($list as $key => $value) {
            $link = "/collection/item/" . $value['item_id'];
            $items[] = <<<EOF
                <div class="col-sm-6 col-lg-4 mb-1" 
                    style="position: absolute; left: 0%; top: 0px;">
                    <div class="card" style="border: unset; border-bottom:1px solid rgb(169, 71, 22,.125)">
                        <div class="card-body" style="padding:unset;">
                        <div class="card-text">
                            <a href="{$link}">{$value['title']}</a>
                        </div>
                        </div>
                    </div>
                    </div>
                EOF;
            $all = join('', $items);
        }
        return <<<EOF
            <div class="row grid" 
                data-masonry='{"percentPosition": true,"originLeft": {$gridDir}}' 
                style="position: relative;">
                {$all}
            </div>
            EOF;
    }

    public function getItemsByGroup($collection_group)
    {
        $pdo = db::getInstance();
        $order = sprintf("`company%s`", Lang::getLocale());
        $sql = "SELECT * FROM `items` WHERE `mGroup` = :mGroup  ORDER BY {$order}";
        try {
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['mGroup' => $collection_group]);
        } catch (Exception $ex) {
            Debug::dump(
                $stmt->errorInfo(),
                'error in ' . __METHOD__ . ' line ' . __LINE__
            );
            return NULL;
        }
        return $stmt->fetchAll();
    }

    static function get_collection_groups()
    {
        $pdo = db::getInstance();
        $sql = "SELECT * FROM `collection_group`";
        try {
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
        } catch (Exception $ex) {
            Debug::dump(
                $stmt->errorInfo(),
                'error in ' . __METHOD__ . ' line ' . __LINE__
            );
            return NULL;
        }
        return $stmt->fetchAll();
    }

    static function collectionGroupsIndexed()
    {
        $output = [];
        foreach (self::get_collection_groups() as  $value) {
            $output[$value['collection_group_id']] = [
                'group_he' => $value['group_he'],
                'group_en' => $value['group_en'],
                'link' => $value['link'],
            ];
        }
        return $output;
    }
    static function renderTitle($item)
    {
        $retData = [];
        $ext = ucfirst(Lang::getLocale());
        foreach (['caption_' . Lang::getLocale(), 'company' . $ext, 'model' . $ext, 'year', 'source' . $ext] as
            $key) {
            if (!util::IsNullOrEmptyString($item[$key])) {
                $retData[] = trim($item[$key]);
            }
        }
        $textStr = '<bdi>' . join("</bdi>, <bdi>", $retData) . '</bdi>';
        return $textStr;
    }

    public function getItem($requestID)
    {
        $pdo = db::getInstance();
        $sqlStr = <<<EOF
            SELECT * FROM `items` WHERE `item_id`=:requestID;
            EOF;
        $sth = $pdo->prepare($sqlStr);
        try {
            $sth->execute(array(':requestID' => $requestID));
        } catch (Exception $ex) {
            Debug::dump(
                $sth->errorInfo(),
                'error in ' . __METHOD__ . ' line ' . __LINE__
            );
        }

        $results = $sth->fetch(PDO::FETCH_ASSOC);
        $results['pics'] = [];
        $itemPics = new item_pic();
        foreach ($itemPics->getItemPics($requestID) as $record) {
            $results['pics'][] = $record;
        }
        return ($results);

        //        return $sqlStr;
    }

    public function renderItemPage($item)
    {
        $uclang = ucfirst(Lang::getLocale());
        $components = [];
        $components[] = '<h4>' . collection::renderTitle($item) . "</h4>";
        $components[] = <<<EOF
            <div id="pageContent{$uclang}">
                    {$item['Page' . ucfirst(Lang::getLocale())]}
                </div>
            EOF;
        $components[] = '<div class="col-10 mt-4">' . util::renderLastUpdated($item['last_update']) . '</div>';
        return join('', $components);
    }

    public function navLiteralFromGroup($mGroup)
    {
        foreach ($this->genLandingPageData() as $value) {
            if (strval($value['mGroup']) == $mGroup) return $value['literal'];
        }
        return NULL;
    }

    public function navLinkFromGroup($mGroup)
    {
        foreach ($this->genLandingPageData() as $value) {
            if (strval($value['mGroup']) == $mGroup) return $value['link'];
        }
        return NULL;
    }

    public function renderEditItem($item_id = NULL)
    {
        $token = util::RandomToken();
        $_SESSION['csrf_token'] = $token;
        if ($item_id) {
            // Debug::dump('Item found', util::getCaller());
            $item = $this->getItem($item_id);
        } else {
            // Debug::dump('No item', util::getCaller());
            $form = new form('items');
            // Debug::dump($form, 'Form at ' . util::getCaller());
            $item = $form->genEmptyRecord();
            $item['item_id'] = null;
            // Debug::dump($item, 'Expecting an empty item here at ' . util::getCaller());
        }
        $item['title'] = $this->renderTitle($item);
        $item['csrf_token'] = $token;
        $item['mGroupRadio'] = $this->renderEditMGroup($item['mGroup']);
        $item['locationSelect'] = $this->renderEditLocation($item['location']);
        $item['ownership'] = ownership::renderOwnershipString($item['item_id']);
        $item['statusSelect'] = $this->renderStatusSelect($item['status']);
        $item['driveMechanism'] = $this->renderEditDriveMechanism($item['drive_mechanism']);
        $item['fuelTypeSelect'] = $this->renderFuelTypeSelect($item['fuel_type']);
        $item['colorSelect'] = $this->renderColorSelect($item['color']);
        $item['displayArchive'] = (User::permission() & User::permission_administrator) ? 'contents' : 'none';
        $renderer = new template_renderer(__SITE_PATH . '/includes/mng/editItem.html');
        $renderer->viewData = ['item' => $item];
        $content = $renderer->render();
        return $content;
    }

    private function renderEditMGroup($mGroup)
    {
        $groups = $this->get_collection_groups();
        $data = [];
        foreach ($groups as $key => $value) {
            $checked = ($mGroup == strval($value['collection_group_id'])) ? 'checked'
                : NULL;
            $data[] = <<<EOF
                <div>
                <input type="radio" id="{$value['collection_group_id']}" name="mGroupRadio" data-id="{$value['collection_group_id']}"
                {$checked}>
                <label for="{$value['collection_group_id']}">{$value['group_' . Lang::getLocale()]}</label>
                </div>
                EOF;
        }
        return join("", $data);
    }

    private function renderEditLocation($location)
    {
        $locations = $this->getLocations();
        $data = ['<option></option>'];
        foreach ($locations as $key => $value) {
            $selected = ($location == strval($value['location_id'])) ? 'selected'
                : NULL;
            $data[] = <<<EOF
                <option value="{$value['location_id']}" {$selected}>{$value['name']}
                </option>
                EOF;
        }
        return join('', $data);
    }

    private function renderStatusSelect($curentStstus)
    {
        $data = ['<option></option>'];
        foreach (self::$status as $key => $value) {
            $selected = ($curentStstus == strval($key)) ? 'selected' : NULL;
            $data[] = <<<EOF
                <option value="{$key}" {$selected}>{$value[Lang::getLocale()]}
                </option>
                EOF;
        }
        return join('', $data);
    }

    private function renderFuelTypeSelect($fuel)
    {
        $fuels = [NULL, 'סולר', 'בנזין', 'בנזין-נפט', 'בנזין-נפט-מים'];
        $data = [];
        foreach ($fuels as $value) {
            $selected = ($fuel == $value) ? 'selected' : NULL;
            $data[] = <<<EOF
                <option value = "{$value}" {$selected}>{$value}</option>
                EOF;
        }
        return join('', $data);
    }

    private function renderColorSelect($color)
    {
        $colors = [
            NULL, 'אדום', 'ירוק', 'כחול', 'אפור', 'שחור', 'כתום', 'צהוב',
            'לבן',
        ];
        $data = [];
        foreach ($colors as $value) {
            $selected = ($color == $value) ? 'selected' : NULL;
            $data[] = <<<EOF
                <option value = "{$value}" {$selected}>{$value}</option>                    
                EOF;
        }
        return join('', $data);
    }

    private function renderEditDriveMechanism($drive = NULL)
    {
        /*
          $form = new form('items');
          $enum = $form->get_enum_values('drive_mechanism');
          //        Debug::dump($enum, 'enu, in ' . __METHOD__ . ' line ' . __LINE__);
         */
        $struct = [
            ['enum' => 'wheels', 'literal' => 'wheels'],
            ['enum' => 'Continuous track', 'literal' => 'continuous_track'],
            ['enum' => NULL, 'literal' => 'IR']
        ];
        $data = [];
        foreach ($struct as $key => $value) {
            $checked = ($drive == $value['enum']) ? 'checked' : NULL;
            $id = "drive{$key}";
            $label = Lang::trans('item.' . $value['literal']);
            $data[] = <<<EOF
                <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="drive_mechanism" id="{$id}" {$checked}
                    value="{$value['enum']}" />
                <label class="form-check-label" for="{$id}">{$label}</label>
                </div>
                EOF;
        }
        return join(' ', $data);
    }

    private function getLocations()
    {
        $pdo = db::getInstance();
        $sql = "SELECT * FROM `storage_location`";
        try {
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
        } catch (Exception $ex) {
            Debug::dump(
                $stmt->errorInfo(),
                'error in ' . __METHOD__ . ' line ' . __LINE__
            );
            return NULL;
        }
        return $stmt->fetchAll();
    }

    public function renderEditItemPics($item_id)
    {
        $itemPic = new item_pic();
        $token = util::RandomToken();
        $_SESSION['csrf_token'] = $token;
        $renderer = new template_renderer(__SITE_PATH . '/includes/mng/editItemPicsGallery.html');
        $renderer->viewData = [
            'item_id' => $item_id,
            'csrf_token' => $token,
            'gallery' => $itemPic->renderEditItemPicsGallery($item_id, $token),
        ];
        return $renderer->render();
    }

    static public function getFullItem($requestID)
    {
        $pdo = db::getInstance();
        $sqlStr = 'SELECT * FROM `items` WHERE `item_id` = :requestID LIMIT 1;';
        $sth = $pdo->prepare($sqlStr);
        $sth->execute(array(':requestID' => $requestID));
        $result =  $sth->fetch();
        return $result;
    }
    public function searchResults()
    {
        // Debug::dump($_POST, 'post at ' . util::getCaller());
        $postStr = trim(filter_input(INPUT_GET, 'searchString'/*, FILTER_SANITIZE_STRING*/));
        // Debug::dump($postStr, 'filtered posted string at ' . util::getCaller());
        $searchTerms = mb_split("[\s,]+", $postStr);
        // Debug::dump($searchTerms, 'search terms at ' . util::getCaller());
        $whereList = [];
        $fields2search = [
            '`companyHe`', '`modelHe`',
            '`sourceHe`', '`PageHe`',
            '`companyEn`', '`modelEn`',
            '`sourceEn`', '`PageEn`',
            '`year`'
        ];
        foreach ($fields2search as $field) {
            foreach ($searchTerms as $term) {
                $whereList[] = "({$field} like ?)";
                $executeList[] = "%{$term}%";
            }
            $internalList[] = "(" . implode(" AND ", $whereList) . ")";
            unset($whereList);
        }
        $formatedList = join(' OR ', $internalList);
        $sqlStr = <<<EOF
            SELECT * FROM `items` 
            WHERE (`display` = 1) 
                AND ((`archive` is null) OR `archive` = 0) 
                AND ({$formatedList});
            EOF;

        // Debug::dump($sqlStr, "sql at " . util::getCaller());
        // Debug::dump($executeList, "execute list at " . util::getCaller());
        $pdo = db::getInstance();
        try {
            $query = $pdo->prepare($sqlStr);
            $query->execute($executeList);
        } catch (\Throwable $th) {
            Debug::dump($query->errorInfo(), 'error at ' . util::getCaller());
        }
        $list = $query->fetchAll();
        return $list;
    }
    public function getItemsByCompany($company, $collection_group_id = null)
    {
        // debug::dump($company,'company at ' . util::getCaller());
        $lang = ucfirst(Lang::getLocale());
        $wherwStr = "`company{$lang}`= :company AND `display` = 1";
        $data = [':company' => $company];
        if (!util::IsNullOrEmptyString($collection_group_id)) {
            $wherwStr .= " AND `mGroup` = :mGroup AND `display` = 1";
            $data[':mGroup'] = $collection_group_id;
        }
        // $sql = "SELECT * FROM `items` WHERE `company{$lang}`= :company";
        $sql = "SELECT * FROM `items` WHERE {$wherwStr}";
        // debug::dump($sql,'company at ' . util::getCaller());
        $pdo = db::getInstance();
        try {
            $stmt = $pdo->prepare($sql);
            /* $stmt->bindValue(':company', $company, PDO::PARAM_STR);
            if (!util::IsNullOrEmptyString($collection_group_id)) {
                $stmt->bindValue(':mGroup', $collection_group_id, PDO::PARAM_INT);
            } */
            $stmt->execute($data);
        } catch (\Throwable $th) {
            $erros[] = $th->getMessage();
            Debug::dump($erros, 'Error at ' . util::getCaller());
        }
        return $stmt->fetchAll();
    }
    public function renderCollectionCrumbs($title_id = null)
    {
        // $mGroup = $this->collectionGroupsIndexed();
        // Debug::dump($mGroup,'mGroup at ' . util::getCaller());
        if ($title_id) {
            $group = $this->mGroup[$title_id];
            $crumbs = [
                ['literal' => Lang::trans('nav.homePage'), 'link' => '/'],
                ['literal' => Lang::trans('nav.theCollection'), 'link' => '/collection'],
                ['literal' => $group['group_' . lang::getLocale()], 'link' => NULL],
            ];
        } else {
            $crumbs = [
                ['literal' => Lang::trans('nav.homePage'), 'link' => '/'],
                ['literal' => Lang::trans('nav.theCollection'), 'link' => null],
                // ['literal' => Lang::trans('nav.theTractors'), 'link' => NULL],
            ];
        }
        return breadCrumbs::genBreadCrumbs($crumbs);
    }
    public function renderPageTitle($title_id = null)
    {
        if ($title_id) {
            return $this->mGroup[$title_id]['group_' . lang::getLocale()];
        } else {
            return Lang::trans('nav.theCollection');
        }
    }
}
