<?php

/**
 * Description of itemPic
 *
 * @author amots
 * @date 2021-03-12
 */
class itemPic {

    public $errors;

    static function getItemPics($id) {
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

    static function renderItemPicturesPage($pics) {
        if (!isset($pics) or util::is_array_empty($pics)) return NULL;
        $picsArray = [];
        foreach ($pics as $key => $pic) {
            $picsArray[] = <<<EOF
<img src="/assets/media/pics/{$pic['path']}" 
    class="img-fluid" alt="..." />
EOF;
        }
        return join('', $picsArray);
    }

    public function registerItemPic($item_id, $path) {
//        Debug::dump($_REQUEST, 'request in ' . __METHOD__ . 'line ' . __LINE__);
        $sqlStr = "INSERT INTO `pictures` (item_id,path) VALUES (:item_id, :path);";
        $pdo = db::getInstance();
        $stmt = $pdo->prepare($sqlStr);
        $stmt->bindValue(':item_id', $item_id, PDO::PARAM_INT);
        $stmt->bindValue(':path', $path, PDO::PARAM_STR);
        try {
            $stmt->execute();
        } catch (Exception $ex) {
            $_SESSION['errors'][] = $ex->getMessage();
        }
    }

    public function renderEditItemPicsGallery($item_id, $token) {
        $cols = 3;
        $rendered = [];
        $pics = $this->getItemPics($item_id, $token);
//        Debug::dump($pics,'pics in ' . __METHOD__ . ' line ' . __LINE__);
        $renderer = new template_renderer(__SITE_PATH . '/includes/mng/editItemPicForm.html');
//        $rendered[] = $renderer->render();

        foreach ($pics as $pic) {
            $renderer->viewData = [
                'csrf_token' => $token, 'pic' => $pic
            ];
            $rendered[] = $renderer->render();
        }
//                Debug::dump($rendered,'rendered in ' . __METHOD__ . ' line ' . __LINE__);
        /*

          $picsCols = util::balanceArrays($rendered, $cols);
          //        Debug::dump($picsCols,'pics cols in ' . __METHOD__ . ' line ' . __LINE__);
          $colStr = [];
          foreach ($picsCols as $key => $pic_col) {
          $colStr[] = '<div>' . join('</div><div>', $pic_col) . '</div>';
          }
          $col_width = 12 / $cols;
          $divClass = 'class="col-md-4"';

          return '<div class="row">' . "<div {$divClass}>" . join("</div><div {$divClass}>",
          $colStr) . '</div></div>';

         */
        return join('', $rendered);
    }

}
