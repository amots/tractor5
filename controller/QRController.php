<?php

/**
 * Description of QRController
 *
 * @author amots
 * @Date 2018-10-18
 * url example: tractor.org.il/QR/005
 */
class QRController Extends Controller {

    private $rt = NULL;
    private $qr;

    public function __construct($registry) {
        parent::__construct($registry);
        $this->rt = explode('/', $_REQUEST['rt']);
        $this->qr = new QR();
    }

    public function index() {
        $dest = 'location: /index/';
        $id = isset($this->rt[1]) ? $this->rt[1] : NULL;
        if (!is_null($id)) {
            $link = $this->qr->getLink($id);
            if (!is_null($link)) {
                $dest = 'location: ' . $link;
            }
        }
        header($dest);
    }

}
