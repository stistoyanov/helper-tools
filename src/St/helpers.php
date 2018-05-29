<?php

use St\Dumper;
use St\SqlFormatter;
use St\Debug;

if (!function_exists('dd')) {
    /**
     * Dump values
     */
    function dd()
    {
        array_map(function ($x) {
            (new Dumper)->dump($x);
        }, func_get_args());
        die(1);
    }
}


if (!function_exists('validateLatin')) {
    /**
     * Validate only latin characters
     * @param string $string String to validate
     * @return boolean True if Latin only, false otherwise
     */
    function validateLatin($string)
    {
        $result = false;

        if (preg_match("/^[\w\d\s.,-]*$/", $string)) {
            $result = true;
        }

        return $result;
    }
}

if (!function_exists('getDatesPeriod')) {
    /**
     * Get list of dates between given period
     * @param $startDate
     * @param $endDate
     * @param null $format
     * @return array
     * @throws Exception
     */
    function getDatesPeriod($startDate, $endDate, $format = null)
    {
        $allowedDateFormats = [
            'Y-m-d',
            'Y-m-d H:i:s',
            'Y-m-d\TH:i:s',
            'd/m/Y',
            'd/m/Y H:i:s',
            'd.m.Y',
            'd.m.Y H:i:s',
        ];

        $startDate = !is_object($startDate) ? new \DateTime($startDate) : $startDate;
        $endDate = !is_object($endDate) ? new \DateTime($endDate) : $endDate;

        $period = new \DatePeriod(
            $startDate,
            new \DateInterval('P1D'),
            $endDate
        );

        $dates = [];
        foreach ($period as $key => $value) {
            $dates[$key] = isset($format) && in_array((string)$format, $allowedDateFormats) ? $value->format($format) : $value;
        }

        return $dates;
    }
}

if (!function_exists('ds')) {
    /**
     * Dump SQL query
     * @param string $sql
     * @param bool $die
     * @param bool $output
     * @return String
     */
    function ds($sql = '', $die = true, $output = true)
    {
        if (is_object($sql)) {
            if (method_exists($sql, "toSql")) {
                $sql = $sql->toSql();
            } else if (method_exists($sql, "getSqlString")) {
                $sql = $sql->getSqlString();
            } else if (method_exists($sql, "getQuery")) {
                $sql = $sql->getQuery()->getSqlString();
            } else {
                $sql = "Invalid input type.";
            }
        }

        $formatted = SqlFormatter::format($sql);
        $formatted = str_replace("-- ", "\n\n-- ", $formatted);

        if ($output) {

            if (PHP_SAPI === 'cli') {

                $trace = debug_backtrace(false);
                $offset = (@$trace[2]['function'] === 'dump_d') ? 2 : 0;
                echo "\033[0;30m ‣ " .
                    @$trace[1 + $offset]['class'] . " ‣ " .
                    "\033[0;34m" .
                    @$trace[1 + $offset]['function'] . " ‣ " .
                    @$trace[0 + $offset]['line'] . " " .
                    "\033[0;33m ‣ " .
                    @$trace[0 + $offset]['file'] . "\033[0m\n";

                echo("\n\033[0;31m---------------------------------------------\n");

            } else {
                echo "\n<pre style=\"border:1px solid #ccc;padding:6px;" .
                    "margin:10px;font:14px Arial !important;background:whitesmoke;" .
                    "display:block;border-radius:4px;\">\n";

                $trace = debug_backtrace(false);
                $offset = (@$trace[2]['function'] === 'dump_d') ? 2 : 0;

                echo "<b><span style=\"color:#DA4B23\">" .
                    @$trace[1 + $offset]['class'] . "</span>:" .
                    "<span style=\"color:#3574A9;\">" .
                    @$trace[1 + $offset]['function'] . "</span>:" .
                    @$trace[0 + $offset]['line'] . " " .
                    "<span style=\"color:#7CAD44;\">" .
                    @$trace[0 + $offset]['file'] . "</span></b>\n\n";

                echo('<div style="margin: 16px 0px;  white-space: normal;">');
                echo clippy(html_entity_decode(strip_tags($formatted, $tags = '')));
                echo("</div>");
            }
            echo($formatted);
            if (PHP_SAPI !== 'cli') {
                echo("</pre>");
            } else {
                echo("\n\033[0;31m---------------------------------------------\n\033[0m\n");
            }
        }

        if ($die) die();

        return $formatted;
    }
}

function dumpQueryLog($die = true)
{

    $queryLog = DB::getQueryLog();
    if (is_array($queryLog) && count($queryLog) > 0) {
        foreach ($queryLog as $query) {
            $sql = bindParams($query['query'] . "\n-- " . $query['time'], $query['bindings']);
            ds($sql, false);
        }
    }

    if ($die) {
        die();
    }
}

if (!function_exists('vd')) {
    /**
     * Dump && Die
     * @param string $var
     * @param bool $die
     * @param bool|string $title
     */
    function vd($var = "", $die = true, $title = false, $bgColor = "")
    {

        if (PHP_SAPI === 'cli') {

            if ($title === false) {
                $trace = debug_backtrace(false);
                $offset = (@$trace[2]['function'] === 'dump_d') ? 2 : 0;
                echo "\033[0;30m ‣ " .
                    @$trace[1 + $offset]['class'] . " ‣ " .
                    "\033[0;34m" .
                    @$trace[1 + $offset]['function'] . " ‣ " .
                    @$trace[0 + $offset]['line'] . " " .
                    "\033[0;33m ‣ " .
                    @$trace[0 + $offset]['file'] . "\033[0m\n";
            } else {
                echo "\033[0;30m ‣ " . $title . "\033[0m\n";
            }

            echo("\n\033[0;31m");
            var_dump($var);
            echo("\033[0m\n");

        } else {


            if ($bgColor !== "") {
                if (strpos($bgColor, "#") !== 0) {
                    $bgColor = "#" . $bgColor;
                }
                $bgColor = "background-color:{$bgColor};";
            } else {
                $bgColor = "background-color:whitesmoke;";
            }

            echo "\n<pre style=\"border:1px solid #ccc;padding:6px;" .
                "margin:10px;font:13px Arial !important;" .
                "display:block;border-radius:4px;{$bgColor};\">\n";

            $trace = debug_backtrace(false);
            $offset = (@$trace[2]['function'] === 'dump_d') ? 2 : 0;

            if ($title === false) {
                echo "<b style='opacity: 1;'><span style=\"color:#DA4B23\"> <small style=\"vertical-align:top\"> &#9656; </small> " .
                    @$trace[1 + $offset]['class'] . "</span> <small style=\"vertical-align:top\"> &#9656; </small> " .
                    "<span style=\"color:#3574A9;\">" .
                    @$trace[1 + $offset]['function'] . "</span> <small style=\"vertical-align:top\"> &#9656; </small> " .
                    @$trace[0 + $offset]['line'] . " " .
                    "<span style=\"color:#7CAD44;\"> <small style=\"vertical-align:top\"> &#9656; </small> " .
                    @$trace[0 + $offset]['file'] . "</span></b>";
            } else {
                echo "<b style='opacity: 1;'><span style=\"color:#DA4B23\">{$title}</span></b>";
            }

            if ($title !== false && $var == '-') {
                //Skip value
            } else {
                echo("\n\n<hr style=\"border: 0; height: 1px; background: #BBB;margin: 0px 0px 8px 0px\"/>");
                Debug::dump($var);
            }
            echo("</pre>");

        }

        if ($die) {
            exit();
        }

    }
}

if (!function_exists('vd')) {
    /**
     * Dump array in HTML table
     * @param $arr
     * @param bool $additional
     * @param bool $return
     * @return int|string
     */
    function dt($arr, $additional = TRUE, $return = FALSE, $colorColumns = array(), $conditions = array(), $bgColor = "", $id = "")
    {
        $output = "";
        $screenClass = "";

        if ($bgColor !== "") {
            if (strpos($bgColor, "#") !== 0) {
                $bgColor = "#" . $bgColor;
            }
            $bgColor = "background-color:{$bgColor};";
            $screenClass = "class=\"screen\"";
        } else {
            $bgColor = "background-color:whitesmoke;";
        }

        $output .= "\n<div style=\"border:1px solid #ccc;padding:6px;" .
            "margin:10px;font:14px Arial !important;" .
            "display:block;border-radius:4px;overflow:auto;{$bgColor}\">\n";

        if (!is_array($arr))
            return 0;

        if (count($arr) <= 0)
            return 0;


        if ($id == "") {
            $id = "t" . rand(100000, 999999);
        }
        $output .= "<table id=\"$id\" style=\"margin: 16px 0px;border-collapse: collapse;border:solid 1px #CCC;font:12px Arial !important;\" {$screenClass}>";
        $counter = 0;

        if ($additional) {

            $array = (array)current($arr);
            $names = array("#");
            foreach ($array as $k => $v) {
                $names[] = $k;
            }

            foreach ($names as $a) {
                $output .= "<td style=\"border-collapse: collapse;border:solid 1px #CCC;padding:2px;color:#999\"><b>{$a}</b></td>";
            }
        }

        $row = 0;
        foreach ($arr as $a) {
            $row++;
            $array = (array)$a;
            $counter++;
            $output .= '<tr>';

            if ($additional)
                $output .= "<td style=\"border-collapse: collapse;border:solid 1px #CCC;padding:2px;color:#999\">{$counter}</td>";
            $col = 0;
            foreach ($array as $item) {
                $col++;
                if (is_object($item)) {
                    $item = "[OBJECT]";
                }

                $add_style = "";
                if (count($colorColumns) > 0) {
                    $ok = true;
                    if (isset($conditions[$col])) {
                        $condition = str_replace("{value}", (string)$item, $conditions[$col]);
                        $ok = eval($condition);
                    }
                    if (in_array($col, $colorColumns) && $ok) {
                        $cols = str_to_color((string)$item);
                        $add_style = "color:{$cols["f"]};background-color:{$cols["b"]};";
                    }
                }
                if (is_array($item)) {
                    $item = "<pre>" . json_encode($item, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "</pre>";
                }
                $output .= "<td style=\"border-collapse: collapse;border:solid 1px #CCC;padding:2px;{$add_style}\">{$item}</td>";
            }
            $output .= '</tr>';
        }

        $output .= '</table>';
        $output .= "</div>";

        if (!$return)
            echo($output);

        return $output;
    }
}
/**
 * Output the clippy HTML code / Flash object
 * @param string $text
 * @return string
 */
function clippy($text = '-')
{

    $text = urlencode($text);

    return '

<object classid = "clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" type="application/x-shockwave-flash" width="128px" height="32px" id="clippy">
<param name = "movie" value = "/utility/clippy.swf" />
<param name = "allowScriptAccess" value = "always" />
<param name = "quality" value = "high" />
<param name = "scale" value = "exactfit" />
<param name = "wmode" value = "transparent" />
<param NAME = "FlashVars" value = "text=' . $text . '" >
<param name = "bgcolor" value = "#FFFFFF" >
<embed src = "/utility/clippy.swf"
width = "128px"
height = "32px"
scale = "exactfit"
name = "clippy"
quality = "high"
wmode="transparent"
allowScriptAccess = "always"
type = "application/x-shockwave-flash"
pluginspage = "http://www.macromedia.com/go/getflashplayer"
FlashVars = "text=' . $text . '" bgcolor = "#FFFFFF" />
</object >
';

}

/**
 * @param $var
 */
function df($var)
{
    echo($var);
    flush();
    ob_flush();
}

/**
 * Print message for console
 * @param $text
 */
function console_out($text = "", $type = 0)
{

    echo($text . PHP_EOL);

}

function str_to_color($input)
{
    $return = array(
        "b" => "#000000",
        "f" => "#FFFFFF"
    );

    $color = substr(hash('sha256', $input), 0, 6);

    if ($input == "0" || strtolower($input) == "no" || strtolower($input == "error")) {
        $color = "DA4B23";
    }

    if ($input == "0.0000") {
        $color = "CCCCCC";
    }

    if ($input == "1" || strtolower($input) == "yes" || strtolower($input == "ok") || strtolower($input == "success")) {
        $color = "7CAD44";
    }

    if (strtolower($input) == "null" || strtolower($input) == "info") {
        $color = "3574A9";
    }

    if (strtolower($input) == "validation") {
        $color = "EEEEAA ";
    }

    if (strtolower($input) == "warning") {
        $color = "FA7C00";
    }

    if (strtolower($input) == "debug") {
        $color = "CCCCCC";
    }

    $red = hexdec(substr($color, 0, 2));
    $green = hexdec(substr($color, 2, 2));
    $blue = hexdec(substr($color, 4, 2));

    $brightness = calc_brightness($red, $green, $blue);
    if ($brightness > 127) {
        $return["f"] = "#000000";
    }

    $return["b"] = "#" . $color;
    return $return;
}

function calc_brightness($red = 0, $green = 0, $blue = 0)
{
    $red = (int)$red;
    $green = (int)$green;
    $blue = (int)$blue;

    $brightness = (($red * 299) + ($green * 587) + ($blue * 114)) / 1000;
    return $brightness;
}

function parse_cli_params($vars = array(), $registerGlobals = true)
{

    if (!isset($_SERVER["argv"])) {
        return false;
    }

    $env = $_SERVER["argv"];
    $envParsed = array();
    if (is_array($env) && count($env) > 0) {
        array_shift($env);
        foreach ($env as $var) {
            $var = preg_replace("/[^[:alnum:][:space:]=-]/u", '', $var);
            $var = explode("=", $var);

            if (count($var) == 1) {
                $var[0] = str_replace("-", "", $var[0]);
                $envParsed[$var[0]] = true;
            }

            if (count($var) == 2) {
                $var[0] = str_replace("-", "", $var[0]);
                $envParsed[$var[0]] = $var[1];
            }

        }
    }

    if (!is_array($vars)) {
        $vars = array($vars);
    }

    if (empty($vars)) {
        if ($registerGlobals) {
            //$GLOBALS = array_merge($GLOBALS,$envParsed);
        }
        return $envParsed;
    }

    $output = array();
    foreach ($vars as $var) {
        if (isset($envParsed[$var])) {
            $output[$var] = $envParsed[$var];
        }
    }

    if ($registerGlobals) {
        //$GLOBALS = array_merge($GLOBALS,$envParsed);
        extract($output);
    }

    return $output;
}

function backtrace()
{
    $bt = debug_backtrace();
    $caller = array_shift($bt);
    return $caller['file'] . ' ' . $caller['line'];
}

function cmsg($msg = "", $color = "red", $suffix = "\n")
{
    $out = "";

    switch ($color) {
        case "green":
            $out = "\033[1;37m\033[42m"; //Green background
            break;
        case "red":
            $out = "\033[0;30m\033[41m"; //Red background
            break;
        case "yellow":
            $out = "\033[0;30m\033[43m"; //Yellow background
            break;
        case "blue":
            $out = "\033[1;37m\033[44m"; //Blue background
            break;
        case "gray":
            $out = "\033[0;30m\033[47m"; //Gray background
            break;
        case "black":
            $out = "\33[1;37m\033[40m"; //Black background
            break;
        default:
            break;
    }

    echo "{$out}{$msg}\033[0m" . $suffix;
}

if (!function_exists("getMemUsageHuman")) {

    function getMemUsageHuman($realUsage = true)
    {
        $size = memory_get_usage($realUsage);
        $unit = array('b', 'kb', 'mb', 'gb', 'tb', 'pb');
        return @round($size / pow(1024, ($i = floor(log($size, 1024)))), 2) . ' ' . $unit[$i];
    }

}

if (!function_exists("getPeakMemUsageHuman")) {

    function getPeakMemUsageHuman($realUsage = true)
    {
        $size = memory_get_peak_usage($realUsage);
        $unit = array('b', 'kb', 'mb', 'gb', 'tb', 'pb');
        return @round($size / pow(1024, ($i = floor(log($size, 1024)))), 2) . ' ' . $unit[$i];
    }
}

if (!function_exists("memGauge")) {

    function memGaugeInMB()
    {
        $char = "|";
        $size = memory_get_usage(true);
        $size = $size / 1048576;
        $size = $size / 10; //10 MB
        $output = str_repeat($char, (int)$size);

        $green = substr($output, 0, 10);
        if ($green == false) $green = $char;
        $yellow = substr($output, 10, 10);
        if ($yellow == false) $yellow = '';
        $red = substr($output, 20, 30);
        if ($red == false) $red = '';

        $msg = str_pad("MEMORY [Usag] " . getMemUsageHuman() . "  :: ", 30);
        cmsg($msg, "black", "");
        cmsg($green, "green", "");
        cmsg($yellow, "yellow", "");
        cmsg($red, "red", "");
        cmsg("", "gray");
    }

}

if (!function_exists("memPeakGaugeInMB")) {

    function memPeakGaugeInMB()
    {
        $char = "|";
        $size = memory_get_peak_usage(true);
        $size = $size / 1048576;
        $size = $size / 10; //10 MB
        $output = str_repeat($char, (int)$size);

        $green = substr($output, 0, 10);
        if ($green == false) $green = $char;
        $yellow = substr($output, 10, 10);
        if ($yellow == false) $yellow = '';
        $red = substr($output, 20, 30);
        if ($red == false) $red = '';

        $msg = str_pad("MEMORY [Peak] " . getPeakMemUsageHuman() . "  :: ", 30);
        cmsg($msg, "black", "");
        cmsg($green, "green", "");
        cmsg($yellow, "yellow", "");
        cmsg($red, "red", "");
        cmsg("", "gray");
    }

}

function bell()
{
    echo "\007";
}

function renderReportInfo($data = [], $return = false, $die = true)
{

    $res = "\n<pre style=\"border:1px solid #ccc;padding:6px;" .
        "margin:10px;font:14px Arial !important;background:whitesmoke;" .
        "display:block;border-radius:4px;\">\n";

    foreach ($data as $section => $sec_data) {
        $res .= "<b>☀ $section</b>";
        foreach ($sec_data as &$item) {
            if (isset($item['title'])) {
                $item['title'] = str_replace('[', '  <small>[', $item['title']);
                $item['title'] = str_replace(']', ']</small>  ', $item['title']);
            }
        }
        $res .= dt($sec_data, true, true, [1]);
    }

    $res .= "</pre>";

    if ($return) {
        return $res;
    }

    echo($res);

    if ($die) {
        die();
    }
}

// function remove_accents()
/**
 * Unaccent the input string string. An example string like `ÀØėÿᾜὨζὅБю`
 * will be translated to `AOeyIOzoBY`. More complete than :
 *   strtr( (string)$str,
 *          "ÀÁÂÃÄÅàáâãäåÒÓÔÕÖØòóôõöøÈÉÊËèéêëÇçÌÍÎÏìíîïÙÚÛÜùúûüÿÑñ",
 *          "aaaaaaaaaaaaooooooooooooeeeeeeeecciiiiiiiiuuuuuuuuynn" );
 *
 * @param $str input string
 * @param $utf8 if null, function will detect input string encoding
 * @return string input string without accent
 */
function remove_accents($str, $utf8 = true)
{
    $str = (string)$str;
    if (is_null($utf8)) {
        if (!function_exists('mb_detect_encoding')) {
            $utf8 = (strtolower(mb_detect_encoding($str)) == 'utf-8');
        } else {
            $length = strlen($str);
            $utf8 = true;
            for ($i = 0; $i < $length; $i++) {
                $c = ord($str[$i]);
                if ($c < 0x80) $n = 0; # 0bbbbbbb
                elseif (($c & 0xE0) == 0xC0) $n = 1; # 110bbbbb
                elseif (($c & 0xF0) == 0xE0) $n = 2; # 1110bbbb
                elseif (($c & 0xF8) == 0xF0) $n = 3; # 11110bbb
                elseif (($c & 0xFC) == 0xF8) $n = 4; # 111110bb
                elseif (($c & 0xFE) == 0xFC) $n = 5; # 1111110b
                else return false; # Does not match any model
                for ($j = 0; $j < $n; $j++) { # n bytes matching 10bbbbbb follow ?
                    if ((++$i == $length)
                        || ((ord($str[$i]) & 0xC0) != 0x80)) {
                        $utf8 = false;
                        break;
                    }

                }
            }
        }

    }

    if (!$utf8)
        $str = utf8_encode($str);
    $transliteration = array(
        'Ĳ' => 'I', 'Ö' => 'O', 'Œ' => 'O', 'Ü' => 'U', 'ä' => 'a', 'æ' => 'a',
        'ĳ' => 'i', 'ö' => 'o', 'œ' => 'o', 'ü' => 'u', 'ß' => 's', 'ſ' => 's',
        'À' => 'A', 'Á' => 'A', 'Â' => 'A', 'Ã' => 'A', 'Ä' => 'A', 'Å' => 'A',
        'Æ' => 'A', 'Ā' => 'A', 'Ą' => 'A', 'Ă' => 'A', 'Ç' => 'C', 'Ć' => 'C',
        'Č' => 'C', 'Ĉ' => 'C', 'Ċ' => 'C', 'Ď' => 'D', 'Đ' => 'D', 'È' => 'E',
        'É' => 'E', 'Ê' => 'E', 'Ë' => 'E', 'Ē' => 'E', 'Ę' => 'E', 'Ě' => 'E',
        'Ĕ' => 'E', 'Ė' => 'E', 'Ĝ' => 'G', 'Ğ' => 'G', 'Ġ' => 'G', 'Ģ' => 'G',
        'Ĥ' => 'H', 'Ħ' => 'H', 'Ì' => 'I', 'Í' => 'I', 'Î' => 'I', 'Ï' => 'I',
        'Ī' => 'I', 'Ĩ' => 'I', 'Ĭ' => 'I', 'Į' => 'I', 'İ' => 'I', 'Ĵ' => 'J',
        'Ķ' => 'K', 'Ľ' => 'K', 'Ĺ' => 'K', 'Ļ' => 'K', 'Ŀ' => 'K', 'Ł' => 'L',
        'Ñ' => 'N', 'Ń' => 'N', 'Ň' => 'N', 'Ņ' => 'N', 'Ŋ' => 'N', 'Ò' => 'O',
        'Ó' => 'O', 'Ô' => 'O', 'Õ' => 'O', 'Ø' => 'O', 'Ō' => 'O', 'Ő' => 'O',
        'Ŏ' => 'O', 'Ŕ' => 'R', 'Ř' => 'R', 'Ŗ' => 'R', 'Ś' => 'S', 'Ş' => 'S',
        'Ŝ' => 'S', 'Ș' => 'S', 'Š' => 'S', 'Ť' => 'T', 'Ţ' => 'T', 'Ŧ' => 'T',
        'Ț' => 'T', 'Ù' => 'U', 'Ú' => 'U', 'Û' => 'U', 'Ū' => 'U', 'Ů' => 'U',
        'Ű' => 'U', 'Ŭ' => 'U', 'Ũ' => 'U', 'Ų' => 'U', 'Ŵ' => 'W', 'Ŷ' => 'Y',
        'Ÿ' => 'Y', 'Ý' => 'Y', 'Ź' => 'Z', 'Ż' => 'Z', 'Ž' => 'Z', 'à' => 'a',
        'á' => 'a', 'â' => 'a', 'ã' => 'a', 'ā' => 'a', 'ą' => 'a', 'ă' => 'a',
        'å' => 'a', 'ç' => 'c', 'ć' => 'c', 'č' => 'c', 'ĉ' => 'c', 'ċ' => 'c',
        'ď' => 'd', 'đ' => 'd', 'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e',
        'ē' => 'e', 'ę' => 'e', 'ě' => 'e', 'ĕ' => 'e', 'ė' => 'e', 'ƒ' => 'f',
        'ĝ' => 'g', 'ğ' => 'g', 'ġ' => 'g', 'ģ' => 'g', 'ĥ' => 'h', 'ħ' => 'h',
        'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i', 'ī' => 'i', 'ĩ' => 'i',
        'ĭ' => 'i', 'į' => 'i', 'ı' => 'i', 'ĵ' => 'j', 'ķ' => 'k', 'ĸ' => 'k',
        'ł' => 'l', 'ľ' => 'l', 'ĺ' => 'l', 'ļ' => 'l', 'ŀ' => 'l', 'ñ' => 'n',
        'ń' => 'n', 'ň' => 'n', 'ņ' => 'n', 'ŉ' => 'n', 'ŋ' => 'n', 'ò' => 'o',
        'ó' => 'o', 'ô' => 'o', 'õ' => 'o', 'ø' => 'o', 'ō' => 'o', 'ő' => 'o',
        'ŏ' => 'o', 'ŕ' => 'r', 'ř' => 'r', 'ŗ' => 'r', 'ś' => 's', 'š' => 's',
        'ť' => 't', 'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'ū' => 'u', 'ů' => 'u',
        'ű' => 'u', 'ŭ' => 'u', 'ũ' => 'u', 'ų' => 'u', 'ŵ' => 'w', 'ÿ' => 'y',
        'ý' => 'y', 'ŷ' => 'y', 'ż' => 'z', 'ź' => 'z', 'ž' => 'z', 'Α' => 'A',
        'Ά' => 'A', 'Ἀ' => 'A', 'Ἁ' => 'A', 'Ἂ' => 'A', 'Ἃ' => 'A', 'Ἄ' => 'A',
        'Ἅ' => 'A', 'Ἆ' => 'A', 'Ἇ' => 'A', 'ᾈ' => 'A', 'ᾉ' => 'A', 'ᾊ' => 'A',
        'ᾋ' => 'A', 'ᾌ' => 'A', 'ᾍ' => 'A', 'ᾎ' => 'A', 'ᾏ' => 'A', 'Ᾰ' => 'A',
        'Ᾱ' => 'A', 'Ὰ' => 'A', 'ᾼ' => 'A', 'Β' => 'B', 'Γ' => 'G', 'Δ' => 'D',
        'Ε' => 'E', 'Έ' => 'E', 'Ἐ' => 'E', 'Ἑ' => 'E', 'Ἒ' => 'E', 'Ἓ' => 'E',
        'Ἔ' => 'E', 'Ἕ' => 'E', 'Ὲ' => 'E', 'Ζ' => 'Z', 'Η' => 'I', 'Ή' => 'I',
        'Ἠ' => 'I', 'Ἡ' => 'I', 'Ἢ' => 'I', 'Ἣ' => 'I', 'Ἤ' => 'I', 'Ἥ' => 'I',
        'Ἦ' => 'I', 'Ἧ' => 'I', 'ᾘ' => 'I', 'ᾙ' => 'I', 'ᾚ' => 'I', 'ᾛ' => 'I',
        'ᾜ' => 'I', 'ᾝ' => 'I', 'ᾞ' => 'I', 'ᾟ' => 'I', 'Ὴ' => 'I', 'ῌ' => 'I',
        'Θ' => 'T', 'Ι' => 'I', 'Ί' => 'I', 'Ϊ' => 'I', 'Ἰ' => 'I', 'Ἱ' => 'I',
        'Ἲ' => 'I', 'Ἳ' => 'I', 'Ἴ' => 'I', 'Ἵ' => 'I', 'Ἶ' => 'I', 'Ἷ' => 'I',
        'Ῐ' => 'I', 'Ῑ' => 'I', 'Ὶ' => 'I', 'Κ' => 'K', 'Λ' => 'L', 'Μ' => 'M',
        'Ν' => 'N', 'Ξ' => 'K', 'Ο' => 'O', 'Ό' => 'O', 'Ὀ' => 'O', 'Ὁ' => 'O',
        'Ὂ' => 'O', 'Ὃ' => 'O', 'Ὄ' => 'O', 'Ὅ' => 'O', 'Ὸ' => 'O', 'Π' => 'P',
        'Ρ' => 'R', 'Ῥ' => 'R', 'Σ' => 'S', 'Τ' => 'T', 'Υ' => 'Y', 'Ύ' => 'Y',
        'Ϋ' => 'Y', 'Ὑ' => 'Y', 'Ὓ' => 'Y', 'Ὕ' => 'Y', 'Ὗ' => 'Y', 'Ῠ' => 'Y',
        'Ῡ' => 'Y', 'Ὺ' => 'Y', 'Φ' => 'F', 'Χ' => 'X', 'Ψ' => 'P', 'Ω' => 'O',
        'Ώ' => 'O', 'Ὠ' => 'O', 'Ὡ' => 'O', 'Ὢ' => 'O', 'Ὣ' => 'O', 'Ὤ' => 'O',
        'Ὥ' => 'O', 'Ὦ' => 'O', 'Ὧ' => 'O', 'ᾨ' => 'O', 'ᾩ' => 'O', 'ᾪ' => 'O',
        'ᾫ' => 'O', 'ᾬ' => 'O', 'ᾭ' => 'O', 'ᾮ' => 'O', 'ᾯ' => 'O', 'Ὼ' => 'O',
        'ῼ' => 'O', 'α' => 'a', 'ά' => 'a', 'ἀ' => 'a', 'ἁ' => 'a', 'ἂ' => 'a',
        'ἃ' => 'a', 'ἄ' => 'a', 'ἅ' => 'a', 'ἆ' => 'a', 'ἇ' => 'a', 'ᾀ' => 'a',
        'ᾁ' => 'a', 'ᾂ' => 'a', 'ᾃ' => 'a', 'ᾄ' => 'a', 'ᾅ' => 'a', 'ᾆ' => 'a',
        'ᾇ' => 'a', 'ὰ' => 'a', 'ᾰ' => 'a', 'ᾱ' => 'a', 'ᾲ' => 'a', 'ᾳ' => 'a',
        'ᾴ' => 'a', 'ᾶ' => 'a', 'ᾷ' => 'a', 'β' => 'b', 'γ' => 'g', 'δ' => 'd',
        'ε' => 'e', 'έ' => 'e', 'ἐ' => 'e', 'ἑ' => 'e', 'ἒ' => 'e', 'ἓ' => 'e',
        'ἔ' => 'e', 'ἕ' => 'e', 'ὲ' => 'e', 'ζ' => 'z', 'η' => 'i', 'ή' => 'i',
        'ἠ' => 'i', 'ἡ' => 'i', 'ἢ' => 'i', 'ἣ' => 'i', 'ἤ' => 'i', 'ἥ' => 'i',
        'ἦ' => 'i', 'ἧ' => 'i', 'ᾐ' => 'i', 'ᾑ' => 'i', 'ᾒ' => 'i', 'ᾓ' => 'i',
        'ᾔ' => 'i', 'ᾕ' => 'i', 'ᾖ' => 'i', 'ᾗ' => 'i', 'ὴ' => 'i', 'ῂ' => 'i',
        'ῃ' => 'i', 'ῄ' => 'i', 'ῆ' => 'i', 'ῇ' => 'i', 'θ' => 't', 'ι' => 'i',
        'ί' => 'i', 'ϊ' => 'i', 'ΐ' => 'i', 'ἰ' => 'i', 'ἱ' => 'i', 'ἲ' => 'i',
        'ἳ' => 'i', 'ἴ' => 'i', 'ἵ' => 'i', 'ἶ' => 'i', 'ἷ' => 'i', 'ὶ' => 'i',
        'ῐ' => 'i', 'ῑ' => 'i', 'ῒ' => 'i', 'ῖ' => 'i', 'ῗ' => 'i', 'κ' => 'k',
        'λ' => 'l', 'μ' => 'm', 'ν' => 'n', 'ξ' => 'k', 'ο' => 'o', 'ό' => 'o',
        'ὀ' => 'o', 'ὁ' => 'o', 'ὂ' => 'o', 'ὃ' => 'o', 'ὄ' => 'o', 'ὅ' => 'o',
        'ὸ' => 'o', 'π' => 'p', 'ρ' => 'r', 'ῤ' => 'r', 'ῥ' => 'r', 'σ' => 's',
        'ς' => 's', 'τ' => 't', 'υ' => 'y', 'ύ' => 'y', 'ϋ' => 'y', 'ΰ' => 'y',
        'ὐ' => 'y', 'ὑ' => 'y', 'ὒ' => 'y', 'ὓ' => 'y', 'ὔ' => 'y', 'ὕ' => 'y',
        'ὖ' => 'y', 'ὗ' => 'y', 'ὺ' => 'y', 'ῠ' => 'y', 'ῡ' => 'y', 'ῢ' => 'y',
        'ῦ' => 'y', 'ῧ' => 'y', 'φ' => 'f', 'χ' => 'x', 'ψ' => 'p', 'ω' => 'o',
        'ώ' => 'o', 'ὠ' => 'o', 'ὡ' => 'o', 'ὢ' => 'o', 'ὣ' => 'o', 'ὤ' => 'o',
        'ὥ' => 'o', 'ὦ' => 'o', 'ὧ' => 'o', 'ᾠ' => 'o', 'ᾡ' => 'o', 'ᾢ' => 'o',
        'ᾣ' => 'o', 'ᾤ' => 'o', 'ᾥ' => 'o', 'ᾦ' => 'o', 'ᾧ' => 'o', 'ὼ' => 'o',
        'ῲ' => 'o', 'ῳ' => 'o', 'ῴ' => 'o', 'ῶ' => 'o', 'ῷ' => 'o', 'А' => 'A',
        'Б' => 'B', 'В' => 'V', 'Г' => 'G', 'Д' => 'D', 'Е' => 'E', 'Ё' => 'E',
        'Ж' => 'ZH', 'З' => 'Z', 'И' => 'I', 'Й' => 'I', 'К' => 'K', 'Л' => 'L',
        'М' => 'M', 'Н' => 'N', 'О' => 'O', 'П' => 'P', 'Р' => 'R', 'С' => 'S',
        'Т' => 'T', 'У' => 'U', 'Ф' => 'F', 'Х' => 'H', 'Ц' => 'TS', 'Ч' => 'CH',
        'Ш' => 'SH', 'Щ' => 'SHT', 'Ы' => 'Y', 'Э' => 'E',
        'Ъ' => 'U', 'Ь' => 'Y',
        'Ю' => 'YU', 'Я' => 'YA',
        'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd', 'е' => 'e',
        'ё' => 'e', 'ж' => 'zh', 'з' => 'z', 'и' => 'i', 'й' => 'i', 'к' => 'k',
        'л' => 'l', 'м' => 'm', 'н' => 'n', 'о' => 'o', 'п' => 'p', 'р' => 'r',
        'с' => 's', 'т' => 't', 'у' => 'u', 'ф' => 'f', 'х' => 'h', 'ц' => 'ts',
        'ч' => 'ch', 'ш' => 'sh', 'щ' => 'sht', 'ы' => 'y', 'э' => 'e',
        'ъ' => 'u', 'ь' => 'y', 'ю' => 'yu',
        'я' => 'ya', 'ð' => 'd', 'Ð' => 'D', 'þ' => 't', 'Þ' => 'T', 'ა' => 'a',
        'ბ' => 'b', 'გ' => 'g', 'დ' => 'd', 'ე' => 'e', 'ვ' => 'v', 'ზ' => 'z',
        'თ' => 't', 'ი' => 'i', 'კ' => 'k', 'ლ' => 'l', 'მ' => 'm', 'ნ' => 'n',
        'ო' => 'o', 'პ' => 'p', 'ჟ' => 'z', 'რ' => 'r', 'ს' => 's', 'ტ' => 't',
        'უ' => 'u', 'ფ' => 'p', 'ქ' => 'k', 'ღ' => 'g', 'ყ' => 'q', 'შ' => 's',
        'ჩ' => 'c', 'ც' => 't', 'ძ' => 'd', 'წ' => 't', 'ჭ' => 'c', 'ხ' => 'k',
        'ჯ' => 'j', 'ჰ' => 'h'
    );
    $str = str_replace(array_keys($transliteration),
        array_values($transliteration),
        $str);
    return $str;
}

function remove_nonascii($string = '', $leave = ['-'], $replacement = '_')
{
    $leave_str = '';
    if (is_array($leave) && count($leave) > 0) {
        $leave_str = "\\" . implode("\\", $leave);
    }
    return preg_replace("/[^a-zA-Z0-9{$leave_str}]+/", $replacement, $string);
}

function arrayToInputs($input = [], $prefix = '', $type = "hidden", $labels = true)
{
    $res = '';

    if (!is_array($input)) {
        $input = [];
    }

    foreach ($input as $key => $value) {
        if (!is_array($value) && !is_object($value)) {
            if ($prefix != "") {
                $key = "{$prefix}[{$key}]";
            }

            if ($labels) {
                $res .= "<label for=\"{$key}\">{$key}: </label>\n";
            }
            $value = htmlentities($value);

            $res .= "<input type=\"{$type}\" value=\"{$value}\" id=\"$key\" name=\"{$key}\" /> \n";
        } else {
            $res .= arrayToInputs($value, $key, $type);
        }
    }

    return $res;
}

function getExceptionTraceAsString($exception)
{
    $rtn = "";
    $count = 0;
    foreach ($exception->getTrace() as $frame) {


        $args = "";
        if (isset($frame['args'])) {
            $args = array();
            foreach ($frame['args'] as $arg) {
                if (is_string($arg)) {
                    $args[] = "'" . $arg . "'";
                } elseif (is_array($arg)) {
                    $args[] = "Array";
                } elseif (is_null($arg)) {
                    $args[] = 'NULL';
                } elseif (is_bool($arg)) {
                    $args[] = ($arg) ? "true" : "false";
                } elseif (is_object($arg)) {
                    $args[] = get_class($arg);
                } elseif (is_resource($arg)) {
                    $args[] = get_resource_type($arg);
                } else {
                    $args[] = $arg;
                }
            }
            $args = join(", ", $args);
        }
        $current_file = "[internal function]";
        if (isset($frame['file'])) {
            $current_file = $frame['file'];
        }
        $current_line = "";
        if (isset($frame['line'])) {
            $current_line = $frame['line'];
        }
        $rtn .= sprintf("#%s %s(%s): %s(%s)\n",
            $count,
            $current_file,
            $current_line,
            $frame['function'],
            $args);
        $count++;
    }
    return $rtn;
}

function hex2rgb($hex)
{
    $hex = str_replace("#", "", $hex);

    if (strlen($hex) == 3) {
        $r = hexdec(substr($hex, 0, 1) . substr($hex, 0, 1));
        $g = hexdec(substr($hex, 1, 1) . substr($hex, 1, 1));
        $b = hexdec(substr($hex, 2, 1) . substr($hex, 2, 1));
    } else {
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));
    }
    $rgb = array($r, $g, $b);

    return $rgb;
}

function findConstantsFromObject($object, $filter = null, $find_value = null)
{
    $reflect = new ReflectionClass($object);
    $constants = $reflect->getConstants();

    foreach ($constants as $name => $value) {
        if (!is_null($filter) && !preg_match($filter, $name)) {
            unset($constants[$name]);
            continue;
        }

        if (!is_null($find_value) && $value != $find_value) {
            unset($constants[$name]);
            continue;
        }
    }

    return $constants;
}

function asset_version($path, $secure = null)
{

    $version = config('app.version', '0.0.0');
    $versionHash = substr(md5($version), 1, 10);
    if (strpos($path, '?') === false) {
        $path .= '?ver=' . $versionHash;
    } else {
        $path .= '&ver=' . $versionHash;
    }

    return asset($path, $secure);
}

function arrays_add($arr1, $arr2)
{
    if (!is_array($arr1) || !is_array($arr2)) return $arr1;

    foreach ($arr2 as $key => $value) {
        if (isset($arr1[$key])) {
            if (!is_array($arr1[$key] && !is_object($arr1[$key]))) {
                $arr1[$key] += $value;
            }
        } else {
            $arr1[$key] = $value;
        }
    }

    return $arr1;
}

function isJson($string)
{
    if (!is_string($string)) {
        return false;
    }
    json_decode($string);
    return (json_last_error() == JSON_ERROR_NONE);
}