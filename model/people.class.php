<?php

/**
 * Description of people
 *
 * @author amots
 * @date 2020-03-17
 */
class people {

    private $locale;
    static $picPath = '/assets/media/pics/people/';

    public function __construct() {
        $this->locale = Lang::getLocale(); 
    }

    public function renderActiveVolunteers() {
        $data = $this->getActiveVolunteers();
        $names = [];
        foreach ($data as $key => $individual) {
            $surname = $individual["sur_name_{$this->locale}"];
            $lastname = $individual["last_name_{$this->locale}"];
            $names[] = "{$surname} {$lastname}";
        }
        return join(', ', $names);
    }

    public function renderAllVolunteers() {
        $data = $this->getAllVolunteers();
        $names = [];
        foreach ($data as $key => $individual) {
            $surname = $individual["sur_name_{$this->locale}"];
            $lastname = $individual["last_name_{$this->locale}"];
            $names[] = "{$surname} {$lastname}";
        }
        return join(', ', $names);
    }

    public function renderVolunteersPage() {
        $data = $this->getActiveVolunteers();
        return $this->formatPeopleList($data, 2);

    }

    public function renderFoundersPage() {
        $data = $this->getFounders();
        return $this->formatPeopleList($data, 1);
    }

    private function getActiveVolunteers() {
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

    private function getAllVolunteers() {
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

    private function getFounders() {
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

    private function formatPeopleList($data, $columns) {
        $peoplePanel = [];
        foreach ($data as $key => $individual) {
            $surname = $individual["sur_name_{$this->locale}"];
            $lastname = $individual["last_name_{$this->locale}"];
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
            if (util::IsNullOrEmptyString($individual['image_path']) or
                    ! file_exists(__SITE_PATH . self::$picPath . $individual['image_path'])) {
                $imgPath = self::$picPath . 'anon.jpg';
            } else {
                $imgPath = self::$picPath . $individual['image_path'];
            }
            $colVal = 12/$columns;
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
            /* $peoplePanel[] = <<<EOF
                <div class="media p-1 col-{$colVal}">
                    <div class="pull-right">
                        <figure class="figure">
                            <img class="media-object p-1" src="{$imgPath}"  alt="{$surname} {$lastname}" />
                        </figure>    
                    </div>
                    <div class="media-body media-bottom">
                        <div>{$nameStr}</div>
                        {$about}
                    </div>
                </div>
                EOF; */
        }
        $gridDir = Lang::getLocale() == 'he' ? 'false' : 'true';
        $content = join(' ',$peoplePanel);
        return <<<EOF
            <div class="row grid" 
            data-masonry='{"percentPosition": true,"originLeft": {$gridDir}}' 
            style="position: relative;">
                {$content}
            </div>
            EOF;
       /*  $twoCol = util::balanceArrays($peoplePanel, $columns);
        $cols = [];
        foreach ($twoCol as $col) {
            $cols[] = join('', $col);
        }
        if ($columns > 1) {
            return '<div class="col-md-6">' . join('</div><div class="col-md-6">',
                            $cols) . '</div>';
        } else {
            return '<div class="col-md-12">' . join('', $cols) . '</div>';
        } */
    }

}
