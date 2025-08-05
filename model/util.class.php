<?php

/**
 * Description of util
 *
 * @author amots
 */
class util
{

    static function IsNullOrEmptyString($question)
    {
        return (!isset($question) || trim($question) === '');
    }

    static function email_encode($address, $txt = "")
    {
        $hrefTxt = 'mailto:' . $address;
        $id = uniqid("", false);
        $addArray = "";
        for ($i = 0; $i < strlen($hrefTxt); $i++) {
            if ($i > 0) {
                $addArray .= ",";
            }

            $addArray .= ord(substr($hrefTxt, $i, 1));
        }
        $response = "\n<script type=\"text/javascript\">\n";
        $response .= "var c$id=new Array($addArray)\n";
        $response .= "var E$id=''\n";
        $response .= "for (i=0;i<c$id.length;i++)\n";
        $response .= "E$id+=String.fromCharCode(c$id" . "[i])\n";
        $response .= "document.write('<a href=\"'+E$id+'\">";
        if ($txt != "") {
            $response .= $txt;
        } else {
            $response .= "'+E$id+'";
        }

        $response .= "<\/a>')\n";
        $response .= "</script>\n";
        return $response;
    }

    static function shortenHebrewText($text, $maxLength = 50)
    { // from chatgpt
        // Ensure UTF-8 encoding
        $text = trim($text ?? '');

        // If the text is already short enough
        if (mb_strlen($text, 'UTF-8') <= $maxLength) {
            return $text;
        }

        // Cut off at max length
        $shortText = mb_substr($text, 0, $maxLength, 'UTF-8');

        // Find last space (word boundary)
        $lastSpace = mb_strrpos($shortText, ' ', 0, 'UTF-8');

        if ($lastSpace !== false) {
            $shortText = mb_substr($shortText, 0, $lastSpace, 'UTF-8');
        }

        return $shortText . '…'; // optional: append ellipsis
    }

    static function shorten_string($string, $max = 50)
    {
        $workStr = strip_tags($string ?? '');
        // if (util::IsNullOrEmptyString($workStr)) return '';
        // debug::dump($workStr,'workstring '. util::getCaller());
        $tok = strtok($workStr, ' ');
        $workStr = '';
        while ($tok !== false && mb_strlen($workStr) < $max) {
            if (mb_strlen($workStr) + mb_strlen($tok) <= $max)
                $workStr .= $tok . ' ';
            else break;
            $tok = strtok(' ');
        }
        return trim($workStr) . '&hellip;';
    }

    static function RandomToken($length = 32)
    {
        if (!isset($length) || intval($length) <= 8) {
            $length = 32;
        }
        if (function_exists('random_bytes')) {
            return bin2hex(random_bytes($length));
        }
        if (function_exists('mcrypt_create_iv')) {
            return bin2hex(mcrypt_create_iv($length, MCRYPT_DEV_URANDOM));
        }
        if (function_exists('openssl_random_pseudo_bytes')) {
            return bin2hex(openssl_random_pseudo_bytes($length));
        }
    }

    static function validatePostToken($postname, $sessionTokenID)
    {
        $valid = FALSE;
        if (isset($_SESSION[$sessionTokenID])) {
            $valid = ($_POST[$postname] === $_SESSION[$sessionTokenID]);
        }
        return $valid;
    }

    static function validateValueToken($tokenValue, $sessionTokenName)
    {
        if (isset($_SESSION[$sessionTokenName]))
            return $tokenValue === $_SESSION[$sessionTokenName];
        else return FALSE;
    }

    static function simplifyArray($data)
    {
        if (is_null($data)) return $data;
        if (!is_array($data)) {
            $tmp = [];
            $tmp[] = $data;
            $data = $tmp;
        }
        $return = [];
        array_walk_recursive(
            $data,
            function ($a) use (&$return) {
                $return[] = $a;
            }
        );
        return $return;
    }

    static function is_array_empty($multidim_array)
    {
        $simplified = self::simplifyArray($multidim_array);
        foreach ($simplified as $key => $value) {
            if (self::IsNullOrEmptyString($value)) {
                unset($simplified[$key]);
            }
        }
        return empty($simplified);
    }

    static function renderErrors($errors)
    {
        $data = [];
        $errors = self::simplifyArray($errors);
        foreach ($errors as $key => $value) {
            if (!self::IsNullOrEmptyString($value))
                $data[] = <<<EOF
                    <div class="alert alert-danger p-0" role="alert">{$value}</div>
                    EOF;
        }
        return join(' ', $data);
    }

    static function renderMessages($messages)
    {
        $data = [];
        $messages = self::simplifyArray($messages);
        foreach ($messages as  $value) {
            if (!self::IsNullOrEmptyString($value))
                $data[] = <<<EOF
                    <div class="alert alert-info p-0" role="alert">{$value}</div>
                    EOF;
        }
        return join(' ', $data);
    }

    static function rendeWarning($messages)
    {
        $data = [];
        $messages = self::simplifyArray($messages);
        foreach ($messages as $key => $value) {
            if (!self::IsNullOrEmptyString($value))
                $data[] = <<<EOF
                    <div class="alert alert-warning p-0 ltr" role="alert">{$value}</div>
                    EOF;
        }
        return join(' ', $data);
    }
    static function renderAnnouncements($registry, $messages)
    {
        $registry->template->errors = self::renderErrors(self::messageSubset($messages, 2));
        $registry->template->warnings = self::rendeWarning(self::messageSubset($messages, 1));
        $registry->template->messages = self::renderMessages(self::messageSubset($messages, 0));
    }
    static function messageSubset($a, $key)
    {
        // debug::dump([$a, $key], "$a, $key ".self::getCaller());
        /**
         * 0 - ok
         * 1 - notification
         * 2 - error
         */
        $results = [];
        $keys = self::simplifyArray($key);
        foreach ($a as $tuple) {
            foreach ($keys as $k) {
                if (isset($tuple[0]) and $tuple[0] == $k)
                    $results[] = $tuple[1];
            }
        }
        return $results;
    }
    static function balanceArrays($source, $n)
    {
        $new = [];
        for ($i = 0; $i < $n; $i++) {
            $new[$i] = [];
            $count = ceil(count($source) / ($n - $i));
            for ($j = 0; $j < $count; $j++) {
                $item = array_shift($source);
                array_push($new[$i], $item);
            }
        }
        return $new;
    }

    static function balance2levelArray($dataTable, $n)
    {
        $source = $dataTable;
        $new = [];
        for ($i = 0; $i < $n; $i++) {
            $new[$i] = [];
            $lines = 0;
            foreach ($source as $group) {
                $lines += count($group[0]) + count($group[1]);
            }
            $quota = ceil($lines / ($n - $i));
            $accumulated = 0;
            $notfull = TRUE;
            while ($notfull) {
                $item = array_shift($source);
                $len = count($item[0]) + count($item[1]);
                if (($accumulated + round($len / 2)) > $quota) {
                    array_unshift($source, $item);
                    $notfull = FALSE;
                } else {
                    array_push($new[$i], $item);
                    $accumulated += $len;
                }
                if (sizeof($source) == 0) $notfull = FALSE;
            }
        }
        return $new;
    }

    static function renderLastUpdated($date)
    {
        $retStr = '';
        if (strlen($date) > 0) {
            $formated = date("Y-m-d", strtotime($date));
            $retStr = <<<EOF
                    <small class="text-muted">
                    עודכן: {$formated}
                    </small>
                    EOF;
        }
        return $retStr;
    }

    static function shuffle_assoc(&$array)
    {
        $keys = array_keys($array);
        shuffle($keys);
        foreach ($keys as $key) {
            $new[$key] = $array[$key];
        }
        $array = $new;
        return true;
    }

    static function auto_copyright($year = 'auto')
    {
        if (intval($year) == 'auto') {
            $year = date('Y');
        }
        if (intval($year) == date('Y')) {
            echo intval($year);
        }
        if (intval($year) < date('Y')) {
            echo intval($year) . '-' . date('Y');
        }
        if (intval($year) > date('Y')) {
            echo date('Y');
        }
    }

    static function renderIncompeteDate($year, $month, $day)
    {
        $collection = [];
        if (!util::IsNullOrEmptyString($year))
            $collection[] = "<bdi>{$year}</bdi>";
        if (!util::IsNullOrEmptyString($month)) {
            $monthName = self::monthName($month);
            $collection[] = "<bdi>{$monthName}</bdi>";
            if (!util::IsNullOrEmptyString($day))
                $collection[] = "<bdi>{$day}</bdi>";
        }
        return join(" ", array_reverse($collection));
    }

    static function monthName($num)
    {
        $month = [
            1 => 'ינואר',
            2 => 'פברואר',
            3 => 'מרץ',
            4 => 'אפריל',
            5 => 'מאי',
            6 => 'יוני',
            7 => 'יולי',
            8 => 'אוגוסט',
            9 => 'ספטמבר',
            10 => 'אוקטובר',
            11 => 'נובמבר',
            12 =>
            'דצמבר'
        ];
        if ($num > 0 and $num <= 12) {
            return $month[$num];
        }
        return NULL;
    }

    static function getCaller()
    {
        $dbt = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
        $caller = isset($dbt[1]['function']) ? $dbt[1]['function'] : null;
        $class = isset($dbt[1]['class']) ? $dbt[1]['class'] : null;
        $line = isset($dbt[0]['line']) ? $dbt[0]['line'] : null;
        return "{$class}::{$caller} line {$line}";
    }

    static function var_dump_pre($mixed = null, $str = null)
    {
        $var = print_r($mixed, true);
        $out = <<<EOF
            <div class="ltr">
                <h4>{$str}</h4>
                <pre>{$var}</pre>
            </div>
            EOF;
        echo $out;
        return null;
    }
    static function printR($data, $txt = NULL)
    {
        $dbt = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
        $caller = isset($dbt[1]['function']) ? $dbt[1]['function'] : null;
        $class = isset($dbt[1]['class']) ? $dbt[1]['class'] : null;
        $line = isset($dbt[0]['line']) ? $dbt[0]['line'] : null;
        $print = print_r($data, TRUE);
        return <<<EOF
            <div class="card">
                <h4>{$txt} at {$class}::{$caller} line {$line}</h4>
                <div class="card-body"><div class="card-text"><pre>{$print}</pre></div></div>
            </div>        
            EOF;
    }
}

/** * end of class ** */
