<?php

/**
 * Description of documentation
 *
 * @author amots
 * $date 2021-11-12
 */
class documentation
{
    public $errors = [];
    public function __construct()
    {
    }
    public function renderDocPage()
    {
        $videoTitles = $this->renderVideoList();
        return $videoTitles;
    }
    private function renderVideoList()
    {
        $list = $this->getAllRecords('video');
        $gridDir = Lang::getLocale() == 'he' ? 'false' : 'true';
        $listItems = [];
        foreach ($list as $item) {
            $title = $item['title_' . Lang::getLocale()];
            $link = "/documentation/{$item['type']}/{$item['doc_id']}";
            $listItems[] = <<<EOF
            <div class="col-sm-6 col-lg-4 mb-1" 
                style="position: absolute; left: 0%; top: 0px;">
                <div class="card" style="border: unset; border-bottom:1px solid rgb(169, 71, 22,.125)">
                    <div class="card-body" style="padding:unset;">
                        <div class="card-text">
                            <a href="{$link}">{$title}</a>
                        </div>
                    </div>
                </div>
            </div>
            EOF;
        }
        $all = join('', $listItems);
        return <<<EOF
            <div class="row grid" 
                data-masonry='{"percentPosition": true,"originLeft": {$gridDir}}' 
                style="position: relative;">
                {$all}
            </div>
            EOF;
    }
    private function getAllRecords($type = null)
    {
        if (util::IsNullOrEmptyString($type)) {
            $whereStr = null;
        } else {
            $whereStr = "WHERE `type`=\"{$type}\"";
        }
        $pdo = db::getInstance();
        $sql = "SELECT * FROM `documentation` {$whereStr} ;";
        $stmt = $pdo->prepare($sql);
        try {
            $stmt->execute();
        } catch (Exception $exc) {
            array_push(
                $this->errors,
                util::simplifyArray($exc->getTraceAsString())
            );
            array_push($this->errors, util::simplifyArray($stmt->errorInfo()));
        }
        return $stmt->fetchAll();
    }
    public function getRecord($doc_id)
    {
        $pdo = db::getInstance();
        $sql = "SELECT * FROM `documentation` WHERE `doc_id` = :doc_id  LIMIT 1";
        try {
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['doc_id' => $doc_id]);
        } catch (Exception $ex) {
            array_push(
                $this->errors,
                util::simplifyArray($ex->getTraceAsString())
            );
            array_push($this->errors, util::simplifyArray($stmt->errorInfo()));
            return NULL;
        }
        $data = $stmt->fetch();
        return $data;
    }
}
