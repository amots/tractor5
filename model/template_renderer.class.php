<?php

/**
 * Description of templateRenderer
 *
 * @author amots
 * @date 2017-11-07
 */
class template_renderer {

    protected $viewData;
    protected $viewFile;

    function __construct($file=NULL, $data=[]) {
        $this->viewFile = $file;
        $this->viewData = $data;
    }

    public function render() {
        extract($this->viewData);
        ob_start();
        include($this->viewFile);
        return ob_get_clean();
    }

    public function __set($name, $value) {
        $this->$name = $value;
    }

}
