<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of upload
 *
 * @author amots
 * 
 * source:
 * https://www.startutorial.com/articles/view/php_file_upload_tutorial_part_1

 */
class upload {

    public static function handleUpload($path) {
        /*
         * response: ['response'=>string, 'success'=> boolean]
         */
        $retData = ['response' => '', 'success' => FALSE];
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {

            $name = $_FILES['fileToUpload']['name'];
            $tmpName = $_FILES['fileToUpload']['tmp_name'];
            $code = $_FILES['fileToUpload']['error'];
            $size = $_FILES['fileToUpload']['size'];
            $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
            $retData['fileName'] = $name;
            if (!is_uploaded_file($tmpName)) {
                $retData['response'] = "Unknown error";
                return $retData;
            }
//            Debug::dump($tmpName,'tmpName uploads::handleUpload');

            switch ($code) { // TODO none of those messages were found php very 7
                case UPLOAD_ERR_OK: //0
                    $valid = true;
                    //validate file extensions
                    if (!in_array($ext, array('jpg', 'jpeg', 'png', 'gif'))) {
                        $valid = false;
                        $retData['response'] = 'Invalid file extension.';
                    }
                    //validate file size
                    if ($size / 1024 / 1024 > 2) {
                        $valid = false;
                        $retData['response'] = 'File size is exceeding maximum allowed size.';
                    }
                    //upload file
                    if ($valid) {
                        $targetPath = $path . DIRECTORY_SEPARATOR . $name;
                        $retData['finalpath'] = $targetPath;
                        if (file_exists($targetPath)) {
                            $retData['response'] = "File {$name} already exists";
                            $retData['success'] = FALSE;
//                            debug::dump($targetPath,"file  exists");
                        } else
                            try {
                                move_uploaded_file($tmpName, $targetPath);
                                chmod($targetPath, 0777);
                                $retData['response'] = "File loaded OK";
                                $retData['success'] = TRUE;
                            } catch (Exception $exc) {
                                $retData['success'] = FALSE;
                                $retData['response'] = $exc->getTraceAsString();
                            }
                    }
                    break;
                default :
                    $response = codeToMessage($code);
            }

            return $retData;
        }
    }

    private function codeToMessage($code) {
        switch ($code) {
            case UPLOAD_ERR_INI_SIZE: // 1
                $message = "The uploaded file exceeds the upload_max_filesize directive in php.ini";
                break;
            case UPLOAD_ERR_FORM_SIZE: // 2
                $message = "The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form";
                break;
            case UPLOAD_ERR_PARTIAL: // 3
                $message = "The uploaded file was only partially uploaded";
                break;
            case UPLOAD_ERR_NO_FILE: // 4
                $message = "No file was uploaded";
                break;
            case UPLOAD_ERR_NO_TMP_DIR: //6
                $message = "Missing a temporary folder";
                break;
            case UPLOAD_ERR_CANT_WRITE: //7
                $message = "Failed to write file to disk";
                break;
            case UPLOAD_ERR_EXTENSION: //8
                $message = "File upload stopped by extension";
                break;

            default:
                $message = "Unknown upload error";
                break;
        }
        return $message;
    }

    /*
     * source: 
     * https://stackoverflow.com/questions/13076480/php-get-actual-maximum-upload-size
     */

    static function file_upload_max_size() {
        static $max_size = -1;

        if ($max_size < 0) {
            // Start with post_max_size.
            $post_max_size = self::parse_size(ini_get('post_max_size'));
            if ($post_max_size > 0) {
                $max_size = $post_max_size;
            }

            // If upload_max_size is less, then reduce. Except if upload_max_size is
            // zero, which indicates no limit.
            $upload_max = self::parse_size(ini_get('upload_max_filesize'));
            if ($upload_max > 0 && $upload_max < $max_size) {
                $max_size = $upload_max;
            }
        }
        return $max_size;
    }

    static function parse_size($size) {
        $unit = preg_replace('/[^bkmgtpezy]/i', '', $size); // Remove the non-unit characters from the size.
        $size = preg_replace('/[^0-9\.]/', '', $size); // Remove the non-numeric characters from the size.
        if ($unit) {
            // Find the position of the unit in the ordered string which is the power of magnitude to multiply a kilobyte by.
            return round($size * pow(1024, stripos('bkmgtpezy', $unit[0])));
        } else {
            return round($size);
        }
    }

}
