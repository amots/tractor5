<?php

/**
 * Description of people
 *
 * @author amots
 * @since 2020-03-17
 */
class people
{

    private $locale;
    static $picPath = '/assets/media/pics/people/';
    public $messages = [];
    public function __construct()
    {
        $this->locale = Lang::getLocale();
    }

    public function renderActiveVolunteers()
    {
        $data = $this->getActiveVolunteers();
        $names = [];
        foreach ($data as $key => $individual) {
            $surname = $individual["sur_name_{$this->locale}"];
            $lastname = $individual["last_name_{$this->locale}"];
            $names[] = "{$surname} {$lastname}";
        }
        return join(', ', $names);
    }

    public function renderAllVolunteers()
    {
        $data = $this->getAllVolunteers();
        $names = [];
        foreach ($data as $key => $individual) {
            $surname = $individual["sur_name_{$this->locale}"];
            $lastname = $individual["last_name_{$this->locale}"];
            $names[] = "{$surname} {$lastname}";
        }
        return join(', ', $names);
    }

    public function renderVolunteersPage()
    {
        $data = $this->getActiveVolunteers();
        return $this->formatPeopleList($data, 2, 'volunteers');
    }

    public function renderFoundersPage()
    {
        $data = $this->getFounders();
        return $this->formatPeopleList($data, 1, 'founders');
    }

    public function renderPeopleList4Edit()
    {

        $list = $this->getAllPeople();
        // util::var_dump_pre($list, 'Peolple list ' . util::getCaller());
        return $this->renderList($list, true);
        // return "TODO " . util::getCaller();
    }

    public function renderEditPerson($people_id)
    {
        $token = util::RandomToken();
        $_SESSION['csrf_token'] = $token;
        $lan = Lang::getLocale();
        if (is_numeric($people_id)) {
            $person = self::getPerson($people_id);
            $person['personName'] = join(' ', [$person['sur_name_' . $lan], $person['last_name_' . $lan]],);
        } else {
            $person['personName'] = Lang::trans('people.newPerson');
        }
        $person['selectGrouping']  = self::renderGroupingSelect($person['grouping']);
        $person['csrf_token'] = $token;
        $renderer = new template_renderer(__SITE_PATH . "/includes/mng/editPerson.html");
        $renderer->viewData = ['person' => $person,];
        return $renderer->render();
    }

    private function getActiveVolunteers()
    {
        $pdo = db::getInstance();
        $keyOn = 'volunteer';
        $keyOff = 'founder';
        $sqlStr = <<<EOF
            SELECT * FROM `people` 
                WHERE `display`=1 
                    and FIND_IN_SET('{$keyOn}', `grouping`)
                    and !FIND_IN_SET('{$keyOff}', `grouping`)
                ORDER BY `sur_name_{$this->locale}`
                    #LIMIT 5
                        ;
            EOF;
        $stmt = $pdo->prepare($sqlStr);
        try {
            $stmt->execute();
        } catch (Exception $ex) {

            Debug::dump($ex->getTraceAsString());
            Debug::dump(util::simplifyArray($stmt->errorInfo()));
        }
        return $stmt->fetchAll();
    }

    private function getAllVolunteers()
    {
        $pdo = db::getInstance();
        $key = 'memory';
        $sqlStr = <<<EOF
            SELECT * FROM `people` 
                WHERE `display`=1 and not FIND_IN_SET('{$key}', `grouping`)
                ORDER BY `sur_name_{$this->locale}`
                    #LIMIT 5
                        ;
            EOF;
        $stmt = $pdo->prepare($sqlStr);
        try {
            $stmt->execute();
        } catch (Exception $ex) {

            Debug::dump($ex->getTraceAsString());
            Debug::dump(util::simplifyArray($stmt->errorInfo()));
        }
        return $stmt->fetchAll();
    }

    private function getFounders()
    {
        $pdo = db::getInstance();
        $key = 'founder';
        $sqlStr = <<<EOF
            SELECT * FROM `people` 
                WHERE `display`=1 and FIND_IN_SET('{$key}', `grouping`)
                ORDER BY `sur_name_{$this->locale}`
                    #LIMIT 5
                        ;
            EOF;
        $stmt = $pdo->prepare($sqlStr);
        try {
            $stmt->execute();
        } catch (Exception $ex) {

            Debug::dump($ex->getTraceAsString());
            Debug::dump(util::simplifyArray($stmt->errorInfo()));
        }
        return $stmt->fetchAll();
    }

    private function formatPeopleList($data, $columns, $id)
    {
        $peoplePanel = [];
        $rip = Lang::trans('general.rip');
        foreach ($data as $key => $individual) {
            $surname = $individual["sur_name_{$this->locale}"];
            $lastname = $individual["last_name_{$this->locale}"];
            if ($individual['deceased']) {
                $lastname = "{$lastname} {$rip}";
            }
            $homeTown = $individual["home_town_{$this->locale}"];
            $nameParts = [];
            $nameParts[] = <<<EOF
                <span class="head4">{$surname} {$lastname}</span>
                EOF;

            if (!util::IsNullOrEmptyString($homeTown)) {
                $nameParts[] = $homeTown;
            }
            $nameStr = join(', ', $nameParts);
            $about = $individual["about_{$this->locale}"];
            if (
                util::IsNullOrEmptyString($individual['image_path']) or
                ! file_exists(__SITE_PATH . self::$picPath . $individual['image_path'])
            ) {
                $imgPath = self::$picPath . 'anon.jpg';
            } else {
                $imgPath = self::$picPath . $individual['image_path'];
            }
            $colVal = 12 / $columns;
            $peoplePanel[] = <<<EOF
                <div class="d-flex align-items-center col-{$colVal}">
                    <div class="flex-shrink-0">
                    <figure class="figure">
                    <img class="media-object p-1" src="{$imgPath}"  alt="{$surname} {$lastname}" />
                </figure>  
                    </div>
                    <div class="flex-grow-1 ms-1">
                        <div>{$nameStr}</div>
                        {$about}
                    </div>
                </div>
                EOF;
        }
        $gridDir = Lang::getLocale() == 'he' ? 'false' : 'true';
        $content = join(' ', $peoplePanel);
        return <<<EOF
            <div class="row grid" id="$id"
            data-masonry='{"percentPosition": true,"originLeft": {$gridDir}}' 
            style="position: relative;">
                {$content}
            </div>
            EOF;
    }

    private function getAllPeople()
    {
        $pdo = db::getInstance();
        $sqlStr = "SELECT * FROM `people`";
        $stmt = $pdo->prepare($sqlStr);
        try {
            $stmt->execute();
        } catch (Exception $ex) {
            $this->messages[] = [2, util::simplifyArray($stmt->errorInfo())];
            Debug::dump($ex->getTraceAsString());
            Debug::dump(util::simplifyArray($stmt->errorInfo()));
        }
        return $stmt->fetchAll();
    }
    private function renderList($list, $edit = false)
    {
        $ret = [];
        foreach ($list as $key => $item) {

            $title = join(
                ' ',
                [
                    $item['sur_name_' . Lang::getLocale()],
                    $item['last_name_' . Lang::getLocale()],
                ]
            );
            if (!util::IsNullOrEmptyString($item['home_town_' . Lang::getLocale()])) {
                $title = join(", ", [$title, $item['home_town_' . Lang::getLocale()]]);
            }
            if ($edit) {
                $icon = list_items::$biPencilSquare;
                $line = <<<EOT
                    <a href="/mng/editPerson/{$item['people_id']}">{$icon}
                    {$title}</a>
                    EOT;
            } else {
                $line = <<<EOT
                    <a href="/briefs/show/{$item['people_id']}">{$title}</a>
                    EOT;
            }
            $ret[] = <<<EOF
                <li class="list-group-item">{$line}</li>
                EOF;
        }
        $container = ['pre' => '<ul class="list-group list-group-flush">', 'post' => '</ul>'];
        return $container['pre'] . implode('', $ret) . $container['post'];
        // return "TODO " . util::getCaller();
    }

    private function getPerson($people_id)
    {
        $pdo = db::getInstance();
        $sqlStr = "SELECT * FROM `people` WHERE `people_id` = $people_id LIMIT 1";
        $stmt = $pdo->prepare($sqlStr);
        try {
            $stmt->execute();
        } catch (Exception $ex) {
            $this->messages[] = [2, util::simplifyArray($stmt->errorInfo())];
        }
        return $stmt->fetch();
    }

    private function renderGroupingSelect($grouping)
    {
        // util::var_dump_pre($grouping,'grouping '.util::getCaller());
        $form = new form('people');
        $gropings = $form->get_set_values('grouping');
        // util::var_dump_pre($gropings, 'groupings ' . util::getCaller());
        $selects[] =  '';
        foreach ($gropings as $g) {
            $glocale = Lang::trans("people.{$g}");
            $selects[] = <<<EOF
                <option value="{$g}">{$glocale}</option>
                EOF;
        }
        $pre = '<select id="grouping" class="form-select" multiple>';
        $post = '</select>';
        return $pre.join('',$selects).$post;
    }
}
