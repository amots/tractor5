<?php

/**
 * Description of collection
 *
 * @author amots
 * @date 2021-03-10
 */
class collection {

    public static $viewRestricted = 'forceview';
    public static $status = [
        '' => ['he' => '', 'en' => ''],
        '1' => ['he' => 'מצויין', 'en' => 'Excellent'],
        '2' => ['he' => 'תקין', 'en' => 'Functional'],
        '3' => ['he' => 'בשיפוץ', 'en' => 'In repair'],
        '4' => ['he' => 'זקוק לשיפוץ', 'en' => 'To be repaired'],
        '5' => ['he' => 'לא תקין', 'en' => 'Nonfunctional']
    ];

    public function renderCompaniesList() {
        $companies = $this->getCompaniesList(1);
//        Debug::dump($companies,
//                'companies in ' . __METHOD__ . ' line ' . __LINE__);
        $names = [];
        $index = 'company' . Lang::getLocale();
        foreach ($companies as $key => $value) {
//            Debug::dump($value, 'value in ' . __METHOD__ . ' line ' . __LINE__);

            $names[] = $value[$index];
        }
        return join(' 	&bull; ', $names);
    }

    private function getCompaniesList($title) {
        $lang = Lang::getLocale();
        $pdo = db::getInstance();
//        $companyLocale = 'company'.$lang;
        $sql = <<<EOF
SELECT DISTINCT `company{$lang}` FROM `items`
WHERE
    `mGroup` = :title AND(`archive` IS NULL OR `archive` = 0) AND(NOT `company{$lang}` IS NULL)
ORDER BY `company{$lang}`
EOF;
//    Debug::dump($sql, 'sql in ' . __METHOD__ . ' line ' . __LINE__);
        try {
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':title' => $title]);
        } catch (Exception $ex) {
            Debug::dump($stmt->errorInfo(),
                    'error in ' . __METHOD__ . ' line ' . __LINE__);
            return NULL;
        }
        $companies = $stmt->fetchAll();
        return $companies;
    }

    public function renderCollectionLandingPage() {
        $collage = new collage();
        $data = $this->genLandingPageData();
        return $collage->renderCollage($data, 2, 2);
    }

    private function genLandingPageData() {
        /*
         * path to picture
         * link
         */

        return[
            [
                'path' => '/assets/media/pics/woodenSprayer.jpg',
                'link' => '/collection/tools',
                'literal' => Lang::trans('collage.tools'),
                'mGroup' => 3,
            ],
            [
                'path' => '/assets/media/pics/PorscheJunior_after.jpg',
                'link' => '/collection/tractors',
                'literal' => Lang::trans('collage.tractors'),
                'mGroup' => 1,
            ],
            [
                'path' => '/assets/media/pics/274_Dodge_power_wagon_IMG_2798.jpg',
                'link' => '/collection/vehicles',
                'literal' => Lang::trans('collage.vehicles'),
                'mGroup' => 2,
            ],
            [
                'path' => '/assets/media/pics/295_grinder_img_4451.jpg',
                'link' => '/collection/agron',
                'literal' => Lang::trans('collage.collections'),
                'mGroup' => 4,
            ],
//            [
//                'path' => '/assets/media/pics/woodenSprayer.jpg',
//                'link' => '/activities/family',
//                'literal' => Lang::trans('collage.4family'),
//            ],
//            [
//                'path' => '/assets/media/pics/woodenSprayer.jpg',
//                'link' => '/activities/kids',
//                'literal' => Lang::trans('collage.4kids'),
//            ],
        ];
    }

    public function renderCollectionGroupPage($collection_group) {
        $list = $this->getItemsByGroup($collection_group);
        $gridDir = Lang::getLocale() == 'he' ? 'false' : 'true';
//        Debug::dump($list, 'list in ' . __METHOD__ . ' line ' . __LINE__);
        $items = [];
        foreach ($list as $key => $value) {
            $title = $this->renderTitle($value);
            $link = "/collection/item/" . $value['item_id'];
            $items[] = <<<EOF
<div class="col-sm-6 col-lg-4 mb-1" 
    style="position: absolute; left: 0%; top: 0px;">
      <div class="card" style=" border: unset; border-bottom:1px solid rgb(169, 71, 22,.125)">
        <div class="card-body" style="padding:unset;">
          <div class="card-text">
             <a href="{$link}">{$title}</a>
          </div>
        </div>
      </div>
    </div>
EOF;
            $all = join('', $items);
        }
        return <<<EOF

<h1></h1>
<div class="row grid" 
    data-masonry="{&quot;percentPosition&quot;: true,&quot;originLeft&quot;: {$gridDir}}" 
    style="position: relative;">
    {$all}
  </div>
EOF;
    }

    public function getItemsByGroup($collection_group) {
        $pdo = db::getInstance();
        $order = sprintf("`company%s`", Lang::getLocale());
        $sql = "SELECT * FROM `items` WHERE `mGroup` = :mGroup  ORDER BY {$order}";
//        Debug::dump($sql, 'sql in ' . __METHOD__ . ' line ' . __LINE__);
        try {
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['mGroup' => $collection_group]);
        } catch (Exception $ex) {
            Debug::dump($stmt->errorInfo(),
                    'error in ' . __METHOD__ . ' line ' . __LINE__);
            return NULL;
        }
        return $stmt->fetchAll();
    }

    static function get_collection_groups() {
        $pdo = db::getInstance();
        $sql = "SELECT * FROM `collection_group`";
        try {
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
        } catch (Exception $ex) {
            Debug::dump($stmt->errorInfo(),
                    'error in ' . __METHOD__ . ' line ' . __LINE__);
            return NULL;
        }
        return $stmt->fetchAll();
    }

    static function renderTitle($item) {
        $retData = [];
        $ext = ucfirst(Lang::getLocale());
        foreach (['company' . $ext, 'model' . $ext, 'year', 'source' . $ext] as
                    $key) {
            if (!util::IsNullOrEmptyString($item[$key])) {
                $retData[] = trim($item[$key]);
            }
        }
        $textStr = '<bdi>' . join("</bdi>, <bdi>", $retData) . '</bdi>';
        return $textStr;
    }

    public function getItem($requestID) {
        $pdo = db::getInstance();
        /*
          switch ($this->registry->language) {
          case "he":
          $selectStr = "item_id, display, pics, mGroup, companyHe as company, modelHe as model, year, status, sourceHe as source, pageHe as page, last_update";

          break;

          default:
          $selectStr = "item_id, display, pics, mGroup, companyEn as company, modelEn as model, year, status, sourceEn as source, pageEn as page, last_update";
          }
          $whereStr = "item_id=:requestID";
          $fromStr = "items";
          $sqlStr = sprintf("SELECT %s FROM %s WHERE %s;", $selectStr, $fromStr,
          $whereStr);
         */
        $sqlStr = <<<EOF
SELECT * FROM `items` WHERE `item_id`=:requestID;
EOF;
        $sth = $pdo->prepare($sqlStr);
        try {
            $sth->execute(array(':requestID' => $requestID));
        } catch (Exception $ex) {
            Debug::dump($stmt->errorInfo(),
                    'error in ' . __METHOD__ . ' line ' . __LINE__);
        }

        $results = $sth->fetch(PDO::FETCH_ASSOC);
//        Debug::dump($results, 'results in ' . __METHOD__ . ' line ' . __LINE__);
        $results['pics'] = [];
        $itemPics = new itemPic();
        foreach ($itemPics->getItemPics($requestID) as $record) {
//            $results['pics'][] = $record['path'];
            $results['pics'][] = $record;
        }
//        Debug::dump($results, 'results in ' . __METHOD__ . ' line ' . __LINE__);
        return ($results);

        //        return $sqlStr;
    }

    public function renderItemPage($item) {
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

    public function navLiteralFromGroup($mGroup) {
        foreach ($this->genLandingPageData() as $value) {
            if (strval($value['mGroup']) == $mGroup) return $value['literal'];
        }
        return NULL;
    }

    public function navLinkFromGroup($mGroup) {
        foreach ($this->genLandingPageData() as $value) {
            if (strval($value['mGroup']) == $mGroup) return $value['link'];
        }
        return NULL;
    }

    public function renderEditItem($item_id = NULL) {
        $token = util::RandomToken();
        $_SESSION['csrf_token'] = $token;
        if ($item_id) {
            $item = $this->getItem($item_id);
//            Debug::dump($item, 'item in ' . __METHOD__ . ' line ' . __LINE__);
        } else {
            $form = new form('items');
            $item = $form->genEmptyRecord();
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
        $renderer = new template_renderer(__SITE_PATH . '/includes/mng/editItem.html');
        $renderer->viewData = ['item' => $item];
        $content = $renderer->render();
//        $content = sprintf('<div class="text-center">edit item %s in %s line %s</div>',
//                $item_id, __METHOD__, __LINE__);

        return $content;
    }

    private function renderEditMGroup($mGroup) {
        $groups = $this->get_collection_groups();
//        Debug::dump($groups, 'groups in ' . __METHOD__ . ' line ' . __LINE__);
//        Debug::dump($mGroup, 'mGroup in ' . __METHOD__ . ' line ' . __LINE__);
        $data = [];
        foreach ($groups as $key => $value) {
            $checked = ($mGroup == strval($value['collection_group_id'])) ? 'checked'
                        : NULL;
            $data[] = <<<EOF
<div>
  <input type="radio" id="{$value['collection_group_id']}" name="mGroupRadio" data-id="{$value['collection_group_id']}"
  {$checked}>
  <label for="{$value['collection_group_id']}">{$value['collection_group_' . Lang::getLocale()]}</label>
</div>
EOF;
        }
//        return "TODO " . __METHOD__ . ' line ' . __LINE__;
        return join("", $data);
    }

    private function renderEditLocation($location) {
        $locations = $this->getLocations();
//        Debug::dump($locations, 'locations in ' . __METHOD__ . ' line ' . __LINE__);
        $data = ['<option></option>'];
        foreach ($locations as $key => $value) {
            $selected = ($location == strval($value['location_id'])) ? 'selected'
                        : NULL;
            $data[] = <<<EOF
<option value="{$value['location_id']}" {$selected}>{$value['name']}
</option>
EOF;
        }
//        return "<div>TODO [location: {$location}] " . __METHOD__ . ' line ' . __LINE__ . "</div>";
        return join('', $data);
    }

    private function renderStatusSelect($curentStstus) {
        $data = ['<option></option>'];
        foreach (self::$status as $key => $value) {
            $selected = ($curentStstus == strval($key)) ? 'selected' : NULL;
            $data[] = <<<EOF
<option value="{$key}" {$selected}>{$value[Lang::getLocale()]}
</option>
EOF;
        }
//        return "TODO " . __METHOD__ . ' line ' . __LINE__;
        return join('', $data);
    }

    private function renderFuelTypeSelect($fuel) {
        $fuels = [NULL, 'סולר', 'בנזין', 'בנזין-נפט', 'בנזין-נפט-מים'];
        $data = [];
        foreach ($fuels as $value) {
//            Debug::dump($value, 'value in ' . __METHOD__ . ' line ' . __LINE__);
            $selected = ($fuel == $value) ? 'selected' : NULL;
            $data[] = <<<EOF
<option value = "{$value}" {$selected}>{$value}</option>
EOF;
        }
        return join('', $data);
    }

    private function renderColorSelect($color) {
        $colors = [NULL, 'אדום', 'ירוק', 'כחול', 'אפור', 'שחור', 'כתום', 'צהוב',
            'לבן',];
        $data = [];
        foreach ($colors as $value) {
            $selected = ($color == $value) ? 'selected' : NULL;
            $data[] = <<<EOF
<option value = "{$value}" {$selected}>{$value}</option>                    
EOF;
        }
        return join('', $data);
    }

    private function renderEditDriveMechanism($drive = NULL) {
        /*
          $form = new form('items');
          $enum = $form->get_enum_values('drive_mechanism');
          //        Debug::dump($enum, 'enu, in ' . __METHOD__ . ' line ' . __LINE__);
         */
        $struct = [
            ['enum' => 'wheels', 'literal' => 'wheels'],
            ['enum' => 'Continuous track', 'literal' => 'continuous_track'],
            ['enum' => NULL, 'literal' => 'IR']];
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

    private function getLocations() {
        $pdo = db::getInstance();
        $sql = "SELECT * FROM `storage_location`";
        try {
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
        } catch (Exception $ex) {
            Debug::dump($stmt->errorInfo(),
                    'error in ' . __METHOD__ . ' line ' . __LINE__);
            return NULL;
        }
        return $stmt->fetchAll();
    }

    public function renderEditItemPics($item_id) {
        $itemPic = new itemPic();
        $token = util::RandomToken();
        $_SESSION['csrf_token'] = $token;
        $renderer = new template_renderer(__SITE_PATH . '/includes/mng/editItemPicsGallery.html');
        $renderer->viewData = [
            'item_id' => $item_id,
            'csrf_token' => $token,
            'gallery' => $itemPic->renderEditItemPicsGallery($item_id, $token),
        ];
        return $renderer->render();
//        return '<div class="text-center">TODO: in ' . __METHOD__ . ' line ' . __LINE__ . "</div>";
//        return $itemPic->renderEditItemPicsGallery($item_id);
    }

    static public function getFullItem($requestID) {
        $pdo = db::getInstance();
        $sqlStr = 'SELECT * FROM `items` WHERE `item_id` = :requestID LIMIT 1;';
        $sth = $pdo->prepare($sqlStr);
        $sth->execute(array(':requestID' => $requestID));
        $result =  $sth->fetch();
//        Debug::dump($result, 'result in ' . __METHOD__ . ' line ' . __LINE__);
        return $result;
    }

}
