<?php

/**
 * Description of itemPic
 *
 * @author amots
 * @since 2021-03-12
 */
class item_pic
{

    public $errors;

    static function getItemPics($id)
    {
        $pdo = db::getInstance();
        $sql = "SELECT * FROM `pictures` WHERE `item_id`=:id ORDER BY `order`";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        try {
            $stmt->execute();
        } catch (Exception $ex) {
            $this->errors[] = $ex->getMessage();
            Debug::dump($this->errors, 'messsage in ' . __METHOD__);
        }
        $items = $stmt->fetchAll();
        return $items;
    }

    static function renderItemPicturesPage($pics, $title = null)
    {
        if (!isset($pics) or util::is_array_empty($pics)) return NULL;
        $picsArray = [];
        foreach ($pics as $key => $pic) {
            $figCaption = [];
            $caption =  $pic['caption' . ucfirst(Lang::getLocale())];
            if (!util::IsNullOrEmptyString($caption)) {
                $figCaption[] = $pic['caption' . ucfirst(Lang::getLocale())];
            }
            $credit = $pic['credit' . ucfirst(Lang::getLocale())];

            if (!util::IsNullOrEmptyString($credit)) {
                $figCaption[]  = Lang::trans('general.credit') . ": " .
                    $pic['credit' . ucfirst(Lang::getLocale())];
            }
           
            $caption = join('<br />', $figCaption);
            $picsArray[] = <<<EOF
                <figure class="figure">                    
                    <img src="/assets/media/pics/items/{$pic['path']}" 
                        class="ezoom figure-img img-fluid rounded" alt="{$title}" title="{$title}"/>
                    <figcaption class="figure-caption">{$caption}</figcaption>
                </figure>
                EOF;
        }
        return join('', $picsArray);
    }

    public function registerItemPic($item_id, $path)
    {
        $sqlStr = "INSERT INTO `pictures` (item_id,path) VALUES (:item_id, :path);";
        $pdo = db::getInstance();
        $stmt = $pdo->prepare($sqlStr);
        $stmt->bindValue(':item_id', $item_id, PDO::PARAM_INT);
        $stmt->bindValue(':path', $path, PDO::PARAM_STR);
        try {
            $stmt->execute();
        } catch (Exception $ex) {
            $_SESSION['messages'][] = [2,print_r($ex->getMessage(),true)];
        }
    }

    public function renderEditItemPicsGallery($item_id, $token)
    {
        $cols = 3;
        $rendered = [];
        $pics = $this->getItemPics($item_id, $token);
        $renderer = new template_renderer(__SITE_PATH . '/includes/mng/editItemPicForm.html');

        foreach ($pics as $pic) {
            $renderer->viewData = [
                'csrf_token' => $token, 'pic' => $pic
            ];
            $rendered[] = $renderer->render();
        }

        return join('', $rendered);
    }
}
