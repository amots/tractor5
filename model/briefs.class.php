<?php

/**
 * Description of briefs
 *
 * @author amots
 * @created 2018-12-20
 */
class briefs
{

    public $messages = [];
    public $brief = NULL;

    public function __construct()
    {
    }

    public function renderBriefsList()
    {
        $list = $this->getBriefsList();
        return $this->renderList($list);
    }

    public function renderBriefsList4Edit()
    {
        $list = $this->getBriefsList();
        return $this->renderList($list, true);
        // return "TODO " . util::getCaller();
    }

    public function getBriefsList()
    {
        $pdo = db::getInstance();
        $sql = "SELECT `briefs_id`,`title_he`,`title_en` FROM `briefs` order by `position`;";
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

    private function renderList($list, $edit = false)
    {
        $ret = [];
        foreach ($list as $key => $item) {
            $title = $item['title_' . Lang::getLocale()];
            if ($edit) {
                $icon = list_items::$biPencilSquare;
                $line = <<<EOT
                    <a href="/mng/editBrief/{$item['briefs_id']}">{$icon}</a> 
                    <a href="/briefs/show/{$item['briefs_id']}" target="_blank">{$title}</a>
                    EOT;
            } else {
                $line = <<<EOT
                    <a href="/briefs/show/{$item['briefs_id']}">{$title}</a>
                    EOT;
            }
            $ret[] = <<<EOF
                <li class="list-group-item">{$line}</li>
                EOF;
        }
        $container = ['pre' => '<ul class="list-group list-group-flush">', 'post' => '</ul>'];
        return $container['pre'] . implode('', $ret) . $container['post'];
    }

    public function getBrief($id)
    {
        $pdo = db::getInstance();
        $sql = 'SELECT * FROM `briefs` where `briefs_id` = :id LIMIT 1';
        try {
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['id' => $id]);
        } catch (Exception $ex) {
            array_push(
                $this->errors,
                util::simplifyArray($ex->getTraceAsString())
            );
            array_push($this->errors, util::simplifyArray($stmt->errorInfo()));
            return NULL;
        }
        $this->brief = $stmt->fetch();
        return $this->brief;
    }

    public function renderBrief($data)
    {
        $title = $data['title_' . Lang::getLocale()];
        $content = $data['content_' . Lang::getLocale()];
        $updated = util::renderLastUpdated($data['updated']);
        return <<<EOT
            <h2>{$title}</h2>
            {$content} 
            {$updated}
            EOT;
    }

    /* public function updateBrief_outdated()
    {
        $fields = $_POST;
        unset($fields['csrf_token']);

        $itemID = $fields['item_id'];
        unset($fields['item_id']);
        $requiredAction = $fields['requiredAction'];
        unset($fields['requiredAction']);
        $bindArray = [];
        if ($requiredAction == 'insert') {
            $insertFields = $insertHolder = [];
            foreach ($fields as $key => $val) {
                $insertFields[] = $key;
                $holder = ":" . $key;
                $insertHolder[] = $holder;
                $bindArray[$holder] = $val;
            }
            $insertFieldsStr = implode(',', $insertFields);
            $insertHoldersStr = implode(',', $insertHolder);
            $sql = "INSERT INTO briefs ({$insertFieldsStr}) VALUES ({$insertHoldersStr})";
        } else {
            foreach ($fields as $key => $val) {
                $holder = ":" . $key;
                $sqlSetArray[] = $key . '=' . $holder;
                $bindArray[$holder] = $val;
            }
            $updateStr = implode(',', $sqlSetArray);
            $sql = "UPDATE briefs SET {$updateStr} WHERE briefs_id=:briefs_id;";
        }
        $pdo = db::getInstance();
        $stmt = $pdo->prepare($sql);

        foreach ($bindArray as $key => &$content) {
            $stmt->bindValue($key, $content, PDO::PARAM_STR);
        }
        if ($requiredAction == 'update') {
            echo 'we have update on ' . $itemID;
            $stmt->bindParam(':briefs_id', $itemID, PDO::PARAM_INT);
        }
        debug::dump(
            $bindArray,
            'bind array in ' . __METHOD__ . ' line ' . __LINE__
        );
        try {
            $stmt->execute();
        } catch (Exception $ex) {
            Debug::dump($ex->getTraceAsString(), 'trace info in ' . __METHOD__);
            Debug::dump($ex->getMessage(), 'messsage in ' . __METHOD__);
            Debug::dump($stmt->errorInfo(), 'sql info in ' . __METHOD__);
            Debug::dump(
                'updated FAiled',
                'in ' . __METHOD__ . ' line ' . __LINE__
            );
            exit();
        }
        $this->messages[] = 'updated OK in ' . __METHOD__ . ' line ' . __LINE__;
        return TRUE;
    } */

    public function renderEditBrief($briefs_id)
    {
        $token = util::RandomToken();
        $_SESSION['csrf_token'] = $token;
        if (is_numeric($briefs_id))
            $item = $this->getBrief($briefs_id);
        else {
            $form = new form('briefs');
            $item = $form->genEmptyRecord();
            $item['briefs_id'] = null;
            $item['updated'] = null;
        }
        $item['csrf_token'] = $token;
        $renderer = new template_renderer(__SITE_PATH . "/includes/mng/editBrief.html");
        $renderer->viewData = ['item' => $item];
        $content = $renderer->render();
        return $content;
        /*return "TODO in " . util::getCaller();  */
    }
}
