<?php

/**
 * Description of QR
 *
 * @author amots
 * @DATE 2019-01-17
 */
class QR {

    private $errors = [];
    private $messages = [];

    public function getLink($param) {
        $pdo = db::getInstance();
        $sql = "SELECT * FROM `qr` WHERE `qr_id` = :param limit 1";
//        Debug::dump(['param' => $param, 'sql' => $sql], __METHOD__);
        $stmt = $pdo->prepare($sql);
        try {
            $stmt->bindParam(':param', $param, PDO::PARAM_STR);
//            $stmt->execute(['param'=>$param]);
            $stmt->execute();
        } catch (Exception $ex) {
            array_push($this->errors,
                    util::simplifyArray($ex->getTraceAsString()));
            array_push($this->errors, util::simplifyArray($stmt->errorInfo()));
            return NULL;
        }
        $val = $stmt->fetch();
        return $val['link'];
    }

    public function listQR() {
        $items = [];
        foreach ($this->getAllQRs() as $key => $rec) {

            $items[] = "<td>{$rec['qr_id']}</td>"
                    . "<td dir=\"ltr\"><a href=\"{$rec['link']}\">{$rec['link']}</a></td>"
                    . "<td>{$rec['description']}</td>";
        }
        return '<table class="table table-striped"><tr>' . join('</tr><tr>',
                        $items) . '</tr></table>';
    }

    private function getAllQRs() {
        $pdo = db::getInstance();
        $sql = "SELECT * FROM `qr` WHERE 1";
        try {
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
        } catch (Exception $ex) {
            array_push($this->errors,
                    util::simplifyArray($ex->getTraceAsString()));
            array_push($this->errors, util::simplifyArray($stmt->errorInfo()));
            return NULL;
        }
        return $stmt->fetchAll();
    }

    public function getErrors() {
        return $this->errors;
    }

}
