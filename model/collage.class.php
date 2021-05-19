<?php

/**
 * Description of collage
 *
 * @author amots
 * @creation 2018-12-11
 */
class collage
{

    private $path;
    private $picTable = 'pictures';
    private $linkTableName = 'items';
    private $link_id = 'item_id';
    private $itemLiteral;
    private $picture_id = 'picture_id';
    private $pic_path_column = 'path';
    public $errors = [];
    static $tableStyle = "border-collapse: separate; border-spacing: 2px; margin-left:auto; margin-right:auto;";

    const IMG_PATH = '/assets/media/pics/items/';
    const empty_pic_path = '/assets/media/dummy500x500.jpg';

    public function __construct()
    {
        $this->itemLiteral = 'model' . lang::getLocale();
    }

    public function getCollage($width, $height, $showLiteral = false)
    {
        $picList = $this->getImages($width * $height);
        return $this->formatRandomPicsCollage($picList, $width, $height);
    }

    private function getImages($count)
    {
        $pdo = db::getInstance();
        $sql = "SELECT `{$this->picture_id}` as `id` FROM `{$this->picTable}`;";
        try {
            $stmt = $pdo->prepare($sql);
            $stmt->execute([]);
        } catch (Exception $ex) {
            //            array_push($this->errors, $ex->getTraceAsString());
            array_push($this->errors, $stmt->errorInfo());
            return NULL;
        }
        $ids = $stmt->fetchAll();
        $selected = [];
        for ($i = 0; $i < $count; $i++) {
            $r = rand(0, count($ids) - 1);
            $selected[] = $ids[$r]['id'];
            array_splice($ids, $r, 1);
        }
        return $selected;
    }

    public function formatRandomPicsCollage($picList, $cols, $rows, $showLiteral = false)
    {
        $data = [];
        foreach ($picList as $key => $id) {
            $info = $this->getPicInfo($id);
            //            Debug::dump($info, 'info in ' . __METHOD__ . ' line ' . __LINE__);
            $collageData['path'] = self::IMG_PATH . $info[$this->pic_path_column];
            $collageData['link'] = '/collection/item/' . $info[$this->link_id];
            $collageData['literal'] = $info[$this->itemLiteral];
            $data[] = $collageData;
        }
        //        Debug::dump($data, 'data in ' . __METHOD__ . ' line ' . __LINE__);
        return $this->renderCollage($data, $cols, $rows);
    }

    /* public function formatRandomPicsCollage_deprecated($picList, $width, $height)
    {
        if (sizeof($picList) == 0) return NULL;
        $tableStyle = self::$tableStyle;
        $retStr = '';
        $info = [];
        foreach ($picList as $key => $id) {
            $info[] = $this->getPicInfo($id);
        }
        $wp = 100 / $width;
        $retStr .= <<<EOF
        <div class="table-responsive-md">
            <table class="table" style="{$tableStyle}">
        EOF;

        $imgPath = $this::IMG_PATH;
        for ($row = 0; $row < $height; $row++) {
            $retStr .= '<tr>';
            for ($col = 0; $col < $width; $col++) {
                $inf = array_pop($info);

                $fname = $inf[$this->pic_path_column];
                $currrentStyle = <<<EOF
                    background-image: url({$imgPath}{$fname});
                    background-size:cover;
                    background-position:center;
                    background-repeat:no-repeat;
                    width: {$wp}%;
                    ;
                    EOF;
                $retStr .= <<<EOF
                    <td style="{$currrentStyle}"> {$this->renderSingleImg($inf)} </td>
                    EOF;
            }
            $retStr .= '</tr>';
        }
        $retStr .= '</table></div>';
        return $retStr;
    } */

    private function renderSingleImg($inf)
    {
        $id = $inf[$this->link_id];
        $nameLocale = $inf[$this->itemLiteral];
        $imgPath = '/assets/media/';
        $fname = 'empty150x150.jpg';
        $str = <<<EOF
            <a href="/collection/item?id={$id}"> 
                <img class="img-fluid mx-auto" 
                    style="max-height:30%;width:auto;opacity:0" 
                    src="{$imgPath}{$fname}" title="{$nameLocale}" />
            </a> 
            EOF;
        return $str;
    }

    private function getPicInfo($id)
    {
        $pdo = db::getInstance();
        $tbl = $this->linkTableName;
        $selectStr = "{$tbl}.`{$this->link_id}`,{$tbl}.`{$this->itemLiteral}`,{$this->picTable}.{$this->pic_path_column}";
        $fromStr = 'items';
        $joinStr = "{$this->picTable} ON {$this->picTable}.{$this->link_id} = {$tbl}.{$this->link_id}";
        $whereStr = "{$this->picTable}.{$this->picture_id} = :id";
        $sql = sprintf(
            "SELECT %s FROM %s JOIN %s WHERE %s;",
            $selectStr,
            $fromStr,
            $joinStr,
            $whereStr
        );
        try {
            $stmt = $pdo->prepare($sql);
            $stmt->execute(["id" => $id]);
        } catch (Exception $ex) {
            //            array_push($this->errors, $ex->getTraceAsString());
            $this->errors[] = $sql;
            array_push($this->errors, $stmt->errorInfo());
            return NULL;
        }
        return $stmt->fetch();
    }

    public function renderCollage($data, $cols, $rows, $showLiteral = false)
    {
        /*
         * path
         * link
         */

        $wp = 100 / $cols;
        $tableStyle = self::$tableStyle;
        $elements = [];
        $elements[] = <<<EOF
            <div class="table-responsive-md">
                <table class="table" style="{$tableStyle}">
            EOF;

        $rowElements = [];
        for ($row = 0; $row < $rows; $row++) {
            $colElements = [];
            for ($col = 0; $col < $cols; $col++) {
                $inf = array_pop($data);
                /* $formaptedEmptyPic = <<<EOF
                    <svg xmlns="http://www.w3.org/2000/svg" 
                        width="100%" height="200" 
                        viewBox="0 0 300 150">
                        <rect fill="#ddd" width="300" height="150" fill-opacity="0" />
                        <text fill="rgba(169, 71, 22,1)" 
                        font-family="sans-serif" 
                        font-size="30" dy="10.5" font-weight="bold" x="50%" y="50%" 
                        text-anchor="middle">
                    {$inf['literal']}           
                        </text>
                    </svg>                        
                    EOF; */

                $currrentStyle = <<<EOF
                    background-image: url({$inf['path']});
                    background-size:cover;
                    background-position:center;
                    background-repeat:no-repeat;
                    width: {$wp}%;
                    ;
                    EOF;
                $singleImg = <<<EOF
                    <img class="img-fluid mx-auto" 
                        style="max-height:30%;width:auto;opacity:0"
                        src="/assets/media/empty500x500.jpg" />                
                    EOF;
                /* $colElements[] = <<<EOF
                <td style="{$currrentStyle}"><a href="{$inf['link']}">{$singleImg}</a></td>
                EOF; */
                if ($showLiteral) {
                    $overlayText = <<< EOT
                        <div class="px-2" style="position: absolute; top: 50%; left: 50%;
                                transform: translate(-50%, -50%);color:#A94716;font-size: 2.5rem;
                                background-color: rgba(249, 240, 240, 0.5);
                                font-weight:bold">
                            {$inf['literal']}
                        </div>
                        EOT;
                } else $overlayText = null;
                $colElements[] = <<<EOF
                    <td style="{$currrentStyle}">
                        <div style="position: relative; text-align: center;">
                            {$overlayText}
                                <a href="{$inf['link']}">{$singleImg}</a>
                        </div>
                    </td>
                    EOF;
            }
            $rowElements[] = join('', $colElements);
        }
        $elements[] = '<tr>' . join('</tr><tr>', $rowElements) . '</tr>';
        $elements[] = '</table></div>';
        return join('', $elements);
    }
}
