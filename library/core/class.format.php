<?php
/**
 * Gdn_Format.
 *
 * @author Mark O'Sullivan <markm@vanillaforums.com>
 * @author Todd Burry <todd@vanillaforums.com>
 * @author Lincoln Russell <lincoln@vanillaforums.com>
 * @copyright 2009-2019 Vanilla Forums Inc.
 * @license GPL-2.0-only
 * @package Core
 * @since 2.0
 */

use Garden\EventManager;
use \Vanilla\Formatting;
use \Vanilla\Formatting\Formats;
use \Vanilla\Formatting\FormatUtil;
use \Vanilla\Formatting\Html;

/**
 * Output formatter.
 *
 * Utility class that helps to format strings, objects, and arrays.
 */
class Gdn_Format {
    use \Garden\StaticCacheTranslationTrait;

    /**
     * @var bool Flag which allows plugins to decide if the output should include rel="nofollow" on any <a> links.
     *
     * @example a plugin can run on "BeforeCommentBody" to check the current users role and decide if his/her post
     * should contain rel="nofollow" links. The default setting is true, meaning all links will contain
     * the rel="nofollow" attribute.
     */
    public static $DisplayNoFollow = true;

    /** @var bool Whether or not to replace plain text links with anchors. */
    public static $FormatLinks = true;

    /** @var string  */
    public static $MentionsUrlFormat = '/profile/{name}';

    /** @var array  */
    protected static $SanitizedFormats = [
        'html', 'bbcode', 'wysiwyg', 'text', 'textex', 'markdown', 'rich'
    ];

    /**
     * The ActivityType table has some special sprintf search/replace values in the
     * FullHeadline and ProfileHeadline fields.
     *
     * The ProfileHeadline field is to be used on this page (the user profile page).
     * The FullHeadline field is to be used on the main activity page.
     *
     * The replacement definitions are as follows:
     *  %1$s = ActivityName
     *  %2$s = ActivityName Possessive
     *  %3$s = RegardingName
     *  %4$s = RegardingName Possessive
     *  %5$s = Link to RegardingName's Wall
     *  %6$s = his/her
     *  %7$s = he/she
     *  %8$s = route & routecode
     *  %9$s = gender suffix (some languages require this).
     *
     * @param object $activity An object representation of the activity being formatted.
     * @param int $profileUserID If looking at a user profile, this is the UserID of the profile we are
     *  looking at.
     * @return string
     */
    public static function activityHeadline($activity, $profileUserID = '', $viewingUserID = '') {
        $activity = (object)$activity;
        if ($viewingUserID == '') {
            $session = Gdn::session();
            $viewingUserID = $session->isValid() ? $session->UserID : -1;
        }

        $genderSuffixCode = 'First';
        $genderSuffixGender = $activity->ActivityGender;

        if ($viewingUserID == $activity->ActivityUserID) {
            $activityName = $activityNameP = t('You');
        } else {
            $activityName = $activity->ActivityName;
            $activityNameP = formatPossessive($activityName);
            $genderSuffixCode = 'Third';
        }

        if ($profileUserID != $activity->ActivityUserID) {
            // If we're not looking at the activity user's profile, link the name
            $activityNameD = urlencode($activity->ActivityName);
            $activityName = anchor($activityName, userUrl($activity, 'Activity'));
            $activityNameP = anchor($activityNameP, userUrl($activity, 'Activity'));
            $genderSuffixCode = 'Third';
        }

        $gender = t('their'); //TODO: this isn't preferable but I don't know a better option
        $gender2 = t('they'); //TODO: this isn't preferable either
        if ($activity->ActivityGender == 'm') {
            $gender = t('his');
            $gender2 = t('he');
        } elseif ($activity->ActivityGender == 'f') {
            $gender = t('her');
            $gender2 = t('she');
        }

        if ($viewingUserID == $activity->RegardingUserID || ($activity->RegardingUserID == '' && $activity->ActivityUserID == $viewingUserID)) {
            $gender = $gender2 = t('your');
        }

        $isYou = false;
        if ($viewingUserID == $activity->RegardingUserID) {
            $isYou = true;
            $regardingName = t('you');
            $regardingNameP = t('your');
            $genderSuffixGender = $activity->RegardingGender;
        } else {
            $regardingName = $activity->RegardingName == '' ? t('somebody') : $activity->RegardingName;
            $regardingNameP = formatPossessive($regardingName);

            if ($activity->ActivityUserID != $viewingUserID) {
                $genderSuffixCode = 'Third';
            }
        }
        $regardingWall = '';
        $regardingWallLink = '';

        if ($activity->ActivityUserID == $activity->RegardingUserID) {
            // If the activityuser and regardinguser are the same, use the $Gender Ref as the RegardingName
            $regardingName = $regardingProfile = $gender;
            $regardingNameP = $regardingProfileP = $gender;
        } elseif ($activity->RegardingUserID > 0 && $profileUserID != $activity->RegardingUserID) {
            // If there is a regarding user and we're not looking at his/her profile, link the name.
            $regardingNameD = urlencode($activity->RegardingName);
            if (!$isYou) {
                $regardingName = anchor($regardingName, userUrl($activity, 'Regarding'));
                $regardingNameP = anchor($regardingNameP, userUrl($activity, 'Regarding'));
                $genderSuffixCode = 'Third';
                $genderSuffixGender = $activity->RegardingGender;
            }
            $regardingWallActivityPath = userUrl($activity, 'Regarding');
            $regardingWallLink = url($regardingWallActivityPath);
            $regardingWall = anchor(t('wall'), $regardingWallActivityPath);
        }
        if ($regardingWall == '') {
            $regardingWall = t('wall');
        }

        if ($activity->Route == '') {
            $activityRouteLink = '';
            if ($activity->RouteCode) {
                $route = t($activity->RouteCode);
            } else {
                $route = '';
            }
        } else {
            $activityRouteLink = url($activity->Route);
            $route = anchor(t($activity->RouteCode), $activity->Route);
        }

        // Translate the gender suffix.
        $genderSuffixCode = "GenderSuffix.$genderSuffixCode.$genderSuffixGender";
        $genderSuffix = t($genderSuffixCode, '');
        if ($genderSuffix == $genderSuffixCode) {
            $genderSuffix = ''; // in case translate doesn't support empty strings.
        }
        /*
          Debug:
        return $ActivityName
        .'/'.$ActivityNameP
        .'/'.$RegardingName
        .'/'.$RegardingNameP
        .'/'.$RegardingWall
        .'/'.$Gender
        .'/'.$Gender2
        .'/'.$Route
        .'/'.$GenderSuffix.($GenderSuffixCode)
        */

        $fullHeadline = t("Activity.{$activity->ActivityType}.FullHeadline", t($activity->FullHeadline));
        $profileHeadline = t("Activity.{$activity->ActivityType}.ProfileHeadline", t($activity->ProfileHeadline));
        $messageFormat = ($profileUserID == $activity->ActivityUserID || $profileUserID == '' || !$profileHeadline ? $fullHeadline : $profileHeadline);

        return sprintf($messageFormat, $activityName, $activityNameP, $regardingName, $regardingNameP, $regardingWall, $gender, $gender2, $route, $genderSuffix, $regardingWallLink, $activityRouteLink);
    }

    /**
     * Removes all non-alpha-numeric characters (except for _ and -) from
     *
     * @param string $mixed An object, array, or string to be formatted.
     * @return string
     */
    public static function alphaNumeric($mixed) {
        if (!is_string($mixed)) {
            return self::to($mixed, 'ForAlphaNumeric');
        } else {
            return preg_replace('/([^\w-])/', '', $mixed);
        }
    }

    /**
     * Takes an object and convert's it's properties => values to an associative
     * array of $array[Property] => Value sets.
     *
     * @param array $array An array to be converted to object.
     * @return stdClass
     */
    public static function arrayAsObject($array) {
        if (!is_array($array)) {
            return $array;
        }

        $return = new stdClass();
        foreach ($array as $property => $value) {
            $return->$property = $value;
        }
        return $return;
    }

    /**
     * Formats a string so that it can be saved to a PHP file in double-quotes of an array value assignment.
     *
     * @example from garden/library/core/class.locale.php:
     *  $FileContents[] = "\$LocaleSources['".$SafeLocaleName."'][] = '".$Format->arrayValueForPhp($LocaleSources[$i])."';";
     *
     * @param string The string to be formatted.
     * @return string
     */
    public static function arrayValueForPhp($string) {
        return str_replace('\\', '\\', html_entity_decode($string, ENT_QUOTES));
    }

    /**
     * Takes a mixed variable, filters unsafe things, renders BBCode and returns it.
     *
     * @param mixed $mixed An object, array, or string to be formatted.
     * @return string
     * @deprecated 3.2 The formatting method should be saved in the DB.
     */
    public static function auto($mixed) {
        deprecated(__FUNCTION__, 'Any other formatting method.');
        $formatter = c('Garden.InputFormatter');
        if (!method_exists('Gdn_Format', $formatter)) {
            return $mixed;
        }

        return Gdn_Format::$formatter($mixed);
    }

    /**
     * Format BBCode into HTML.
     *
     * @param mixed $mixed An object, array, or string to be formatted.
     * @return string Sanitized HTML.
     * @deprecated 3.2 FormatService::renderHtml($str, Formats\BBCodeFormat::FORMAT_KEY);
     */
    public static function bbCode($mixed) {
        if (!is_string($mixed)) {
            return self::to($mixed, 'BBCode');
        } else {
            return Gdn::formatService()->renderHtml($mixed, Formats\BBCodeFormat::FORMAT_KEY);
        }
    }

    /**
     * Format a number by putting K/M/B suffix after it when appropriate.
     *
     * @param mixed $number The number to format. If a number isn't passed then it is returned as is.
     * @return string The formatted number.
     * @todo Make this locale aware.
     */
    public static function bigNumber($number, $format = '') {
        if (!is_numeric($number)) {
            return $number;
        }

        $negative = false;
        $workingNumber = $number;
        if ($number < 0) {
            $negative = true;
            $workingNumber = $number - ($number * 2);
        }

        if ($workingNumber >= 1000000000) {
            $number2 = $workingNumber / 1000000000;
            $suffix = "B";
        } elseif ($workingNumber >= 1000000) {
            $number2 = $workingNumber / 1000000;
            $suffix = "M";
        } elseif ($workingNumber >= 1000) {
            $number2 = $workingNumber / 1000;
            $suffix = "K";
        } else {
            $number2 = $number;
        }

        if ($negative) {
            $number2 = $number2 - ($number2 * 2);
        }

        if (isset($suffix)) {
            $result = number_format($number2, 1);
            if (substr($result, -2) == '.0') {
                $result = substr($result, 0, -2);
            }

            $result .= $suffix;
        } else {
            $result = $number;
        }

        if ($format == 'html') {
            $result = wrap($result, 'span', ['title' => number_format($number)]);
        }

        return $result;
    }

    /**
     * Format a number as if it's a number of bytes by adding the appropriate B/K/M/G/T suffix.
     *
     * @param int $bytes The bytes to format.
     * @param int $precision The number of decimal places to return.
     * @return string The formatted bytes.
     */
    public static function bytes($bytes, $precision = 2) {
        $units = ['B', 'K', 'M', 'G', 'T'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        return round($bytes, $precision).$units[$pow];
    }

    /** @var array Unicode to ascii conversion table. */
    protected static $_CleanChars = [
        '-' => ' ', '_' => ' ', '&lt;' => '', '&gt;' => '', '&#039;' => '', '&amp;' => '',
        '&quot;' => '', 'À' => 'A', 'Á' => 'A', 'Â' => 'A', 'Ã' => 'A', 'Ä' => 'Ae',
        '&Auml;' => 'A', 'Å' => 'A', 'Ā' => 'A', 'Ą' => 'A', 'Ă' => 'A', 'Æ' => 'Ae',
        'Ç' => 'C', 'Ć' => 'C', 'Č' => 'C', 'Ĉ' => 'C', 'Ċ' => 'C', 'Ď' => 'D', 'Đ' => 'D',
        'Ð' => 'D', 'È' => 'E', 'É' => 'E', 'Ê' => 'E', 'Ë' => 'E', 'Ē' => 'E',
        'Ę' => 'E', 'Ě' => 'E', 'Ĕ' => 'E', 'Ė' => 'E', 'Ĝ' => 'G', 'Ğ' => 'G',
        'Ġ' => 'G', 'Ģ' => 'G', 'Ĥ' => 'H', 'Ħ' => 'H', 'Ì' => 'I', 'Í' => 'I',
        'Î' => 'I', 'Ï' => 'I', 'Ī' => 'I', 'Ĩ' => 'I', 'Ĭ' => 'I', 'Į' => 'I',
        'İ' => 'I', 'Ĳ' => 'IJ', 'Ĵ' => 'J', 'Ķ' => 'K', 'Ł' => 'K', 'Ľ' => 'K',
        'Ĺ' => 'K', 'Ļ' => 'K', 'Ŀ' => 'K', 'Ñ' => 'N', 'Ń' => 'N', 'Ň' => 'N',
        'Ņ' => 'N', 'Ŋ' => 'N', 'Ò' => 'O', 'Ó' => 'O', 'Ô' => 'O', 'Õ' => 'O',
        'Ö' => 'Oe', '&Ouml;' => 'Oe', 'Ø' => 'O', 'Ō' => 'O', 'Ő' => 'O', 'Ŏ' => 'O',
        'Œ' => 'OE', 'Ŕ' => 'R', 'Ř' => 'R', 'Ŗ' => 'R', 'Ś' => 'S', 'Š' => 'S',
        'Ş' => 'S', 'Ŝ' => 'S', 'Ș' => 'S', 'Ť' => 'T', 'Ţ' => 'T', 'Ŧ' => 'T',
        'Ț' => 'T', 'Ù' => 'U', 'Ú' => 'U', 'Û' => 'U', 'Ü' => 'Ue', 'Ū' => 'U',
        '&Uuml;' => 'Ue', 'Ů' => 'U', 'Ű' => 'U', 'Ŭ' => 'U', 'Ũ' => 'U', 'Ų' => 'U',
        'Ŵ' => 'W', 'Ý' => 'Y', 'Ŷ' => 'Y', 'Ÿ' => 'Y', 'Ź' => 'Z', 'Ž' => 'Z',
        'Ż' => 'Z', 'Þ' => 'T', 'à' => 'a', 'á' => 'a', 'â' => 'a', 'ã' => 'a',
        'ä' => 'ae', '&auml;' => 'ae', 'å' => 'a', 'ā' => 'a', 'ą' => 'a', 'ă' => 'a',
        'æ' => 'ae', 'ç' => 'c', 'ć' => 'c', 'č' => 'c', 'ĉ' => 'c', 'ċ' => 'c',
        'ď' => 'd', 'đ' => 'd', 'ð' => 'd', 'è' => 'e', 'é' => 'e', 'ê' => 'e',
        'ë' => 'e', 'ē' => 'e', 'ę' => 'e', 'ě' => 'e', 'ĕ' => 'e', 'ė' => 'e',
        'ƒ' => 'f', 'ĝ' => 'g', 'ğ' => 'g', 'ġ' => 'g', 'ģ' => 'g', 'ĥ' => 'h',
        'ħ' => 'h', 'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i', 'ī' => 'i',
        'ĩ' => 'i', 'ĭ' => 'i', 'į' => 'i', 'ı' => 'i', 'ĳ' => 'ij', 'ĵ' => 'j',
        'ķ' => 'k', 'ĸ' => 'k', 'ł' => 'l', 'ľ' => 'l', 'ĺ' => 'l', 'ļ' => 'l',
        'ŀ' => 'l', 'ñ' => 'n', 'ń' => 'n', 'ň' => 'n', 'ņ' => 'n', 'ŉ' => 'n',
        'ŋ' => 'n', 'ò' => 'o', 'ó' => 'o', 'ô' => 'o', 'õ' => 'o', 'ö' => 'oe',
        '&ouml;' => 'oe', 'ø' => 'o', 'ō' => 'o', 'ő' => 'o', 'ŏ' => 'o', 'œ' => 'oe',
        'ŕ' => 'r', 'ř' => 'r', 'ŗ' => 'r', 'š' => 's', 'ù' => 'u', 'ú' => 'u',
        'û' => 'u', 'ü' => 'ue', 'ū' => 'u', '&uuml;' => 'ue', 'ů' => 'u', 'ű' => 'u',
        'ŭ' => 'u', 'ũ' => 'u', 'ų' => 'u', 'ŵ' => 'w', 'ý' => 'y', 'ÿ' => 'y',
        'ŷ' => 'y', 'ž' => 'z', 'ż' => 'z', 'ź' => 'z', 'þ' => 't', 'ß' => 'ss',
        'ſ' => 'ss', 'ый' => 'iy', 'А' => 'A', 'Б' => 'B', 'В' => 'V', 'Г' => 'G',
        'Д' => 'D', 'Е' => 'E', 'Ё' => 'YO', 'Ж' => 'ZH', 'З' => 'Z', 'И' => 'I',
        'И' => 'I', 'І' => 'I', 'Й' => 'Y', 'К' => 'K', 'Л' => 'L', 'М' => 'M',
        'Н' => 'N', 'О' => 'O', 'П' => 'P', 'Р' => 'R', 'С' => 'S', 'Т' => 'T',
        'У' => 'U', 'Ф' => 'F', 'Х' => 'H', 'Ц' => 'C', 'Ч' => 'CH', 'Ш' => 'SH',
        'Щ' => 'SCH', 'Ъ' => '', 'Ы' => 'Y', 'Ь' => '', 'Э' => 'E', 'Ю' => 'YU',
        'Я' => 'YA', 'Є' => 'YE', 'Ї' => 'YI', 'а' => 'a', 'б' => 'b', 'в' => 'v',
        'г' => 'g', 'д' => 'd', 'е' => 'e', 'ё' => 'yo', 'ж' => 'zh', 'з' => 'z',
        'и' => 'i', 'і' => 'i', 'й' => 'y', 'к' => 'k', 'л' => 'l', 'м' => 'm',
        'н' => 'n', 'о' => 'o', 'п' => 'p', 'р' => 'r', 'с' => 's', 'т' => 't',
        'у' => 'u', 'ф' => 'f', 'х' => 'h', 'ц' => 'c', 'ч' => 'ch', 'ш' => 'sh',
        'щ' => 'sch', 'ъ' => '', 'ы' => 'y', 'ь' => '', 'э' => 'e', 'є' => 'ye',
        'ю' => 'yu', 'я' => 'ya', 'ї' => 'yi'
    ];

    /**
     * Convert certain unicode characters into their ascii equivalents.
     *
     * @param mixed $mixed The text to clean.
     * @return string
     */
    public static function clean($mixed) {
        if (!is_string($mixed)) {
            return self::to($mixed, 'Clean');
        }
        $mixed = strtr($mixed, self::$_CleanChars);
        $mixed = preg_replace('/[^A-Za-z0-9 ]/', '', urldecode($mixed));
        $mixed = preg_replace('/ +/', '-', trim($mixed));
        return strtolower($mixed);
    }


    /**
     * Formats a Mysql DateTime string in the specified format.
     *
     * For instructions on how the format string works:
     * @link http://us.php.net/manual/en/function.strftime.php
     *
     * @param string $timestamp A timestamp or string in Mysql DateTime format. ie. YYYY-MM-DD HH:MM:SS
     * @param string $format The format string to use. Defaults to the application's default format.
     * @return string
     */
    public static function date($timestamp = '', $format = '') {
        // Was a mysqldatetime passed?
        if ($timestamp !== null && !is_numeric($timestamp)) {
            $timestamp = self::toTimestamp($timestamp);
        }

        if (function_exists('FormatDateCustom') && (!$format || strcasecmp($format, 'html') == 0)) {
            if (!$timestamp) {
                $timestamp = time();
            }

            return formatDateCustom($timestamp, $format);
        }

        if ($timestamp === null) {
            return t('Null Date', '-');
        }

        if (!$timestamp) {
            $timestamp = time(); // return '&#160;'; Apr 22, 2009 - found a bug where "Draft Saved At X" returned a nbsp here instead of the formatted current time.
        }        $gmTimestamp = $timestamp;

        $now = time();

        // Alter the timestamp based on the user's hour offset.
        $hourOffset = Gdn::session()->hourOffset();

        if ($hourOffset <> 0) {
            $secondsOffset = $hourOffset * 3600;
            $timestamp += $secondsOffset;
            $now += $secondsOffset;
        }

        $html = false;
        if (strcasecmp($format, 'html') == 0) {
            $format = '';
            $html = true;
        }

        if ($format == '') {
            // If the timestamp was during the current day
            if (date('Y m d', $timestamp) == date('Y m d', $now)) {
                // Use the time format
                $format = self::t('Date.DefaultTimeFormat', '%l:%M%p');
            } elseif (date('Y', $timestamp) == date('Y', $now)) {
                // If the timestamp is the same year, show the month and date
                $format = self::t('Date.DefaultDayFormat', '%B %e');
            } elseif (date('Y', $timestamp) != date('Y', $now)) {
                // If the timestamp is not the same year, just show the year
                $format = self::t('Date.DefaultYearFormat', '%B %Y');
            } else {
                // Otherwise, use the date format
                $format = self::t('Date.DefaultFormat', '%B %e, %Y');
            }
        }

        $fullFormat = self::t('Date.DefaultDateTimeFormat', '%c');

        // Emulate %l and %e for Windows.
        if (strpos($format, '%l') !== false) {
            $format = str_replace('%l', ltrim(strftime('%I', $timestamp), '0'), $format);
        }
        if (strpos($format, '%e') !== false) {
            $format = str_replace('%e', ltrim(strftime('%d', $timestamp), '0'), $format);
        }

        $result = strftime($format, $timestamp);

        if ($html) {
            $result = wrap($result, 'time', ['title' => strftime($fullFormat, $timestamp), 'datetime' => gmdate('c', $gmTimestamp)]);
        }
        return $result;
    }

    /**
     * Formats a MySql datetime or a unix timestamp for display in the system.
     *
     * @param int $timestamp
     * @param string $format
     * @since 2.1
     */
    public static function dateFull($timestamp, $format = '') {
        if ($timestamp === null) {
            return t('Null Date', '-');
        }

        // Was a mysqldatetime passed?
        if (!is_numeric($timestamp)) {
            $timestamp = self::toTimestamp($timestamp);
        }

        if (!$timestamp) {
            $timestamp = time(); // return '&#160;'; Apr 22, 2009 - found a bug where "Draft Saved At X" returned a nbsp here instead of the formatted current time.
        }        $gmTimestamp = $timestamp;

        $now = time();

        // Alter the timestamp based on the user's hour offset
        $session = Gdn::session();
        if ($session->UserID > 0) {
            $secondsOffset = ($session->User->HourOffset * 3600);
            $timestamp += $secondsOffset;
            $now += $secondsOffset;
        }

        $html = false;
        if (strcasecmp($format, 'html') == 0) {
            $format = '';
            $html = true;
        }

        $fullFormat = t('Date.DefaultDateTimeFormat', '%c');

        // Emulate %l and %e for Windows.
        if (strpos($fullFormat, '%l') !== false) {
            $fullFormat = str_replace('%l', ltrim(strftime('%I', $timestamp), '0'), $fullFormat);
        }
        if (strpos($fullFormat, '%e') !== false) {
            $fullFormat = str_replace('%e', ltrim(strftime('%d', $timestamp), '0'), $fullFormat);
        }

        $result = strftime($fullFormat, $timestamp);

        if ($html) {
            $result = wrap($result, 'time', ['title' => strftime($fullFormat, $timestamp), 'datetime' => gmdate('c', $gmTimestamp)]);
        }
        return $result;
    }

    /**
     * Format a string from of "Deleted" content (comment, message, etc).
     *
     * @param mixed $mixed An object, array, or string to be formatted.
     * @return string
     */
    public static function deleted($mixed) {
        if (!is_string($mixed)) {
            return self::to($mixed, 'Deleted');
        } else {
            $formatter = Gdn::factory('HtmlFormatter');
            if (is_null($formatter)) {
                return Gdn_Format::display($mixed);
            } else {
                return $formatter->format(wrap($mixed, 'div', ' class="Deleted"'));
            }
        }
    }

    /**
     * Return the default input formatter.
     *
     * @param bool|null $is_mobile Whether or not you want the format for mobile browsers.
     * @return string
     */
    public static function defaultFormat($is_mobile = null) {
        if ($is_mobile === true || ($is_mobile === null && isMobile())) {
            return c('Garden.MobileInputFormatter', c('Garden.InputFormatter', 'Html'));
        } else {
            return c('Garden.InputFormatter', 'Html');
        }
    }

    /**
     * Takes a mixed variable, formats it for display on the screen, and returns it.
     *
     * @param mixed $mixed An object, array, or string to be formatted.
     * @return string
     * @deprecated 3.2 Use a specific formatting method.
     */
    public static function display($mixed) {
        if (!is_string($mixed)) {
            return self::to($mixed, 'Display');
        } else {
            $mixed = htmlspecialchars($mixed, ENT_QUOTES, 'UTF-8');
            $mixed = str_replace(["&quot;", "&amp;"], ['"', '&'], $mixed);

            /** @var Html\HtmlEnhancer $htmlEnhancer */
            $htmlEnhancer = Gdn::getContainer()->get(Html\HtmlEnhancer::class);
            $mixed = $htmlEnhancer->enhance($mixed);
            return $mixed;
        }
    }

    /**
     * Formats an email address in a non-scrapable format.
     *
     * @param string $email
     * @return string
     */
    public static function email($email) {
        $max = max(3, floor(strlen($email) / 2));
        $chunks = str_split($email, mt_rand(3, $max));
        $chunks = array_map('htmlentities', $chunks);

        $st = mt_rand(0, 1);
        $end = count($chunks) - mt_rand(1, 4);

        $result = '';
        foreach ($chunks as $i => $chunk) {
            if ($i >= $st && $i <= $end) {
                $result .= '<span style="display:inline;display:none">'.str_rot13($chunk).'</span>';
            }

            $result .= '<span style="display:none;display:inline">'.$chunk.'</span>';
        }

        return '<span class="Email">'.$result.'</span>';
    }

    /**
     * Takes a mixed variable, formats it for display in a form, and returns it.
     *
     * @param mixed $mixed An object, array, or string to be formatted.
     * @return string
     */
    public static function form($mixed) {
        if (!is_string($mixed)) {
            return self::to($mixed, 'Form');
        } else {
            if (c('Garden.Format.ReplaceNewlines', true)) {
                return nl2br(htmlspecialchars($mixed, ENT_QUOTES, 'UTF-8'));
            } else {
                return htmlspecialchars($mixed, ENT_QUOTES, 'UTF-8');
            }
        }
    }

    /**
     * Show times relative to now, e.g. "4 hours ago".
     *
     * Credit goes to: http://byteinn.com/res/426/Fuzzy_Time_function/
     *
     * @param int optional $timestamp, otherwise time() is used
     * @return string
     */
    public static function fuzzyTime($timestamp = null, $morePrecise = false) {
        if (is_null($timestamp)) {
            $timestamp = time();
        } elseif (!is_numeric($timestamp))
            $timestamp = self::toTimestamp($timestamp);

        $time = $timestamp;

        $nOW = time();
        if (!defined('ONE_MINUTE')) {
            define('ONE_MINUTE', 60);
        }
        if (!defined('ONE_HOUR')) {
            define('ONE_HOUR', 3600);
        }
        if (!defined('ONE_DAY')) {
            define('ONE_DAY', 86400);
        }
        if (!defined('ONE_WEEK')) {
            define('ONE_WEEK', ONE_DAY * 7);
        }
        if (!defined('ONE_MONTH')) {
            define('ONE_MONTH', ONE_WEEK * 4);
        }
        if (!defined('ONE_YEAR')) {
            define('ONE_YEAR', ONE_MONTH * 12);
        }

        $secondsAgo = $nOW - $time;

        // sod = start of day :)
        $sod = mktime(0, 0, 0, date('m', $time), date('d', $time), date('Y', $time));
        $sod_now = mktime(0, 0, 0, date('m', $nOW), date('d', $nOW), date('Y', $nOW));

        // Used to convert numbers to strings
        $convert = [0 => t('a'), 1 => t('a'), 2 => t('two'), 3 => t('three'), 4 => t('four'), 5 => t('five'), 6 => t('six'), 7 => t('seven'), 8 => t('eight'), 9 => t('nine'), 10 => t('ten'), 11 => t('eleven')];

        // Today
        if ($sod_now == $sod) {
            if ($time > $nOW - (ONE_MINUTE * 3)) {
                return t('just now');
            } elseif ($time > $nOW - (ONE_MINUTE * 7)) {
                return t('a few minutes ago');
            } elseif ($time > $nOW - (ONE_HOUR)) {
                if ($morePrecise) {
                    $minutesAgo = ceil($secondsAgo / 60);
                    return sprintf(t('%s minutes ago'), $minutesAgo);
                }
                return t('less than an hour ago');
            }
            return sprintf(t('today at %s'), date('g:ia', $time));
        }

        // Yesterday
        if (($sod_now - $sod) <= ONE_DAY) {
            if (date('i', $time) > (ONE_MINUTE + 30)) {
                $time += ONE_HOUR / 2;
            }
            return sprintf(t('yesterday around %s'), date('ga', $time));
        }

        // Within the last 5 days.
        if (($sod_now - $sod) <= (ONE_DAY * 5)) {
            $str = date('l', $time);
            $hour = date('G', $time);
            if ($hour < 12) {
                $str .= t(' morning');
            } elseif ($hour < 17) {
                $str .= t(' afternoon');
            } elseif ($hour < 20) {
                $str .= t(' evening');
            } else {
                $str .= t(' night');
            }
            return $str;
        }

        // Number of weeks (between 1 and 3).
        if (($sod_now - $sod) < (ONE_WEEK * 3.5)) {
            if (($sod_now - $sod) < (ONE_WEEK * 1.5)) {
                return t('about a week ago');
            } elseif (($sod_now - $sod) < (ONE_DAY * 2.5)) {
                return t('about two weeks ago');
            } else {
                return t('about three weeks ago');
            }
        }

        // Number of months (between 1 and 11).
        if (($sod_now - $sod) < (ONE_MONTH * 11.5)) {
            for ($i = (ONE_WEEK * 3.5), $m = 0; $i < ONE_YEAR; $i += ONE_MONTH, $m++) {
                if (($sod_now - $sod) <= $i) {
                    return sprintf(t('about %s month%s ago'), $convert[$m], (($m > 1) ? 's' : ''));
                }
            }
        }

        // Number of years.
        for ($i = (ONE_MONTH * 11.5), $y = 0; $i < (ONE_YEAR * 10); $i += ONE_YEAR, $y++) {
            if (($sod_now - $sod) <= $i) {
                return sprintf(t('about %s year%s ago'), $convert[$y], (($y > 1) ? 's' : ''));
            }
        }

        // More than ten years.
        return t('more than ten years ago');
    }

    /**
     * Takes a mixed variable, filters unsafe HTML and returns it.
     *
     * Does "magic" formatting of links, mentions, link embeds, emoji, & linebreaks.
     *
     * @param mixed $mixed An object, array, or string to be formatted.
     * @return string Sanitized HTML.
     * @deprecated 3.2 FormatService::renderHtml($str, Formats\HtmlFormat::FORMAT_KEY);
     */
    public static function html($mixed) {
        if (!is_string($mixed)) {
            return self::to($mixed, 'Html');
        }

        return Gdn::formatService()->renderHTML($mixed, Formats\HtmlFormat::FORMAT_KEY);
    }

    /**
     * Takes a mixed variable, filters unsafe HTML and returns it.
     *
     * Use this instead of Gdn_Format::html() when you do not want magic formatting.
     *
     * @param mixed $mixed An object, array, or string to be formatted.
     * @param array $options An array of filter options. These will also be passed through to the formatter.
     *              - codeBlockEntities: Encode the contents of code blocks? Defaults to true.
     * @return string Sanitized HTML.
     * @deprecated 3.2 HtmlSanitizer
     */
    public static function htmlFilter($mixed, $options = []) {
        if (!is_string($mixed)) {
            return self::to($mixed, 'HtmlFilter');
        } else {
            /** @var Html\HtmlSanitizer $htmlSanitizer */
            $htmlSanitizer = Gdn::getContainer()->get(Html\HtmlSanitizer::class);
            return $htmlSanitizer->filter((string) $mixed);
        }
    }

    /**
     * Format an encoded string of image properties as HTML.
     *
     * @param string $body a encoded array of image properties (Image, Thumbnail, Caption)
     * @return string HTML
     */
    public static function image($body) {
        if (is_string($body)) {
            $image = dbdecode($body);

            if (!$image) {
                return Gdn_Format::html($body);
            }
        }

        $url = val('Image', $image);
        $caption = Gdn_Format::plainText(val('Caption', $image));
        return '<div class="ImageWrap">'
            .'<div class="Image">'
            .img($url, ['alt' => $caption, 'title' => $caption])
            .'</div>'
            .'<div class="Caption">'.$caption.'</div>'
            .'</div>';
    }

    /**
     * Returns spoiler text wrapped in a HTML spoiler wrapper.
     *
     * Parsers for NBBC and Markdown should use this function to format thier spoilers.
     * All spoilers in HTML-formatted posts are saved in this way. We use javascript in
     * spoilers.js to add markup and render Spoilers with the "Spoiler" css class name.
     *
     * @param string $spoilerText The inner text of the spoiler.
     * @return string
     */
    public static function spoilerHtml($spoilerText) {
        return "<div class=\"Spoiler\">{$spoilerText}</div>";
    }

    /**
     * Format a string as plain text.
     *
     * @param string $body The text to format.
     * @param string $format The current format of the text.
     *
     * @return string Sanitized HTML.
     * @since 2.1
     * @deprecated 3.2 FormatService::renderPlainText
     */
    public static function plainText($body, $format = 'Html') {
        $plainText = Gdn::formatService()->renderPlainText((string) $body, (string) $format);

        // Even though this shouldn't be sanitized here (it should be sanitized in the view layer)
        // it's kind of stuck here since https://github.com/vanilla/vanilla/commit/21c800bb0e326b72a320c6e8f61e89b45e19ec96
        // Some use cases RELY on this sanitization, so if you want to remove this, you'll have to go find those usages.
        // Really just use `FormatInterface::renderPlainText()` instead.
        $sanitized = htmlspecialchars($plainText);
        return $sanitized;
    }

    /**
     * Format a string as an excerpt. Like plaintText but with additional types of content removed.
     *
     * Currently replaces:
     * - Common HTML tags with plaintext.
     * - Replaces spoilers and their text with (Spoiler).
     * - Replaces quotes and their text with (Quote).
     *
     * @param string $body The text to format.
     * @param string $format The current format of the text.
     * @param bool $collapse Treat a group of closing block tags as one when replacing with newlines.
     *
     * @return string Sanitized HTML.
     * @since 2.1
     * @deprecated 3.2 FormatService::renderExcerpt
     */
    public static function excerpt($body, $format = 'Html', $collapse = false) {
        $plainText = Gdn::formatService()->renderExcerpt((string) $body, (string) $format);

        // Even though this shouldn't be sanitized here (it should be sanitized in the view layer)
        // it's kind of stuck here since https://github.com/vanilla/vanilla/commit/21c800bb0e326b72a320c6e8f61e89b45e19ec96
        // Some use cases RELY on this sanitization, so if you want to remove this, you'll have to go find those usages.
        // Really just use `FormatInterface::renderExcerpt()` instead.
        $sanitized = htmlspecialchars($plainText);
        return $sanitized;
    }

    /**
     * Format some text in a way suitable for passing into an rss/atom feed.
     *
     * @since 2.1
     * @param string $text The text to format.
     * @param string $format The current format of the text.
     * @return string
     */
    public static function rssHtml($text, $format = 'Html') {
        if (!in_array($format, ['Html', 'Raw'])) {
            $text = Gdn_Format::to($text, $format);
        }

        if (function_exists('FormatRssHtmlCustom')) {
            return formatRssHtmlCustom($text);
        } else {
            return Gdn_Format::html($text);
        }
    }


    /**
     * Executes the callback function on parts of the string excluding html tags.
     *
     * Optionally skips the contents of an anchor tag <a> or a code tag <code>.
     *
     * @param string $html The html-formatted string to parse.
     * @param callable $callback The callback function to execute on appropriate segments of the string.
     * @param bool $skipAnchors Whether to call the callback function on anchor tag content.
     * @param bool $skipCode  Whether to call the callback function on code tag content.
     * @return string
     */
    public static function tagContent($html, $callback, $skipAnchors = true, $skipCode = true) {
        $regex = "`([<>])`i";
        $parts = preg_split($regex, $html, null, PREG_SPLIT_DELIM_CAPTURE);

        $inTag = false;
        $inAnchor = false;
        $inCode = false;

        foreach ($parts as $i => $str) {
            switch ($str) {
                case '<':
                    $inTag = true;
                    break;
                case '>':
                    $inTag = false;
                    break;
                case '':
                    break;
                default;
                    if ($inTag) {
                        if ($str[0] == '/') {
                            $tagName = preg_split('`\s`', substr($str, 1), 2);
                            $tagName = $tagName[0];

                            if ($tagName == 'a') {
                                $inAnchor = false;
                            }
                            if ($tagName == 'code') {
                                $inCode = false;
                            }
                        } else {
                            $tagName = preg_split('`\s`', trim($str), 2);
                            $tagName = $tagName[0];

                            if ($tagName == 'a') {
                                $inAnchor = true;
                            }
                            if ($tagName == 'code') {
                                $inCode = true;
                            }
                        }
                    } else {
                        if (!($inAnchor && $skipAnchors) && !($inCode && $skipCode)) {
                            // We're not in an anchor and not in a code block
                            $parts[$i] = call_user_func($callback, $str);
                        }
                    }
                    break;
            }
        }

        return implode($parts);
    }

    /**
     * Formats the anchor tags around the links in text.
     *
     * @param mixed $mixed An object, array, or string to be formatted.
     * @param bool $isHtml Should $mixed be considered to be a valid HTML string?
     * @param bool $doEmbeds Should we format links into embeds?
     *
     * @return string
     */
    public static function links($mixed, bool $isHtml = false, bool $doEmbeds = true) {
        if (!is_string($mixed)) {
            return self::to($mixed, 'Links');
        }

        if (!c('Garden.Format.Links', true)) {
            return $mixed;
        }

        $linksCallback = function ($matches) use ($isHtml, $doEmbeds) {
            static $inTag = 0;
            static $inAnchor = false;

            $inOut = $matches[1];
            $tag = strtolower($matches[2]);

            if ($inOut == '<') {
                $inTag++;
                if ($tag == 'a') {
                    $inAnchor = true;
                }
            } elseif ($inOut == '</') {
                $inTag++;
                if ($tag == 'a') {
                    $inAnchor = false;
                }
            } elseif ($matches[3]) {
                $inTag--;
            }

            if (c('Garden.Format.WarnLeaving', false) && isset($matches[4]) && $inTag && $inAnchor) {
                // This is a the href url value in an anchor tag.
                $url = $matches[4];
                $domain = parse_url($url, PHP_URL_HOST);
                if (!isTrustedDomain($domain)) {
                    // If this is valid HTMl, the link text's HTML special characters should be encoded. Decode them to their raw state for URL encoding.
                    if ($isHtml) {
                        $url = htmlspecialchars_decode($url);
                    }
                    return url('/home/leaving?target='.urlencode($url)).'" class="Popup';
                }
            }

            if (!isset($matches[4]) || $inTag || $inAnchor) {
                return $matches[0];
            }
            // We are not in a tag and what we matched starts with //
            if (preg_match('#^//#', $matches[4])) {
                return $matches[0];
            }

            $url = $matches[4];

            if ($doEmbeds) {
                $embeddedResult = self::getLegacyReplacer()->replaceUrl($url ?? '');
                if ($embeddedResult !== '') {
                    return $embeddedResult;
                }
            }

            // Unformatted links
            if (!self::$FormatLinks) {
                return $url;
            }

            // Strip punctuation off of the end of the url.
            $punc = '';

            // Special case where &nbsp; is right after an url and is not part of it!
            // This can happen in WYSIWYG format if the url is the last text of the body.
            while (stringEndsWith($url, '&nbsp;')) {
                $url = substr($url, 0, -6);
                $punc .= '&nbsp;';
            }

            if (preg_match('`^(.+)([.?,;!:])$`', $url, $matches)) {
                $url = $matches[1];
                $punc = $matches[2].$punc;
            }

            // Get human-readable text from url.
            $text = $url;
            if (strpos($text, '%') !== false) {
                $text = rawurldecode($text);
                $text = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
            }

            $nofollow = (self::$DisplayNoFollow) ? ' rel="nofollow"' : '';

            if (c('Garden.Format.WarnLeaving', false)) {
                // This is a plaintext url we're converting into an anchor.
                $domain = parse_url($url, PHP_URL_HOST);
                if (!isTrustedDomain($domain)) {
                    // If this is valid HTMl, the link text's HTML special characters should be encoded. Decode them to their raw state for URL encoding.
                    if ($isHtml) {
                        $url = htmlspecialchars_decode($url);
                    }
                    return '<a href="'.url('/home/leaving?target='.urlencode($url)).'" class="Popup">'.$text.'</a>'.$punc;
                }
            }

            return '<a href="'.$url.'"'.$nofollow.'>'.$text.'</a>'.$punc;
        };
        // Strip  Right-To-Left override.
        $mixed = str_replace("\xE2\x80\xAE", '', $mixed);
        if (unicodeRegexSupport()) {
            $regex = "`(?:(</?)([!a-z]+))|(/?\s*>)|((?:(?:https?|ftp):)?//[@\p{L}\p{N}\x21\x23-\x27\x2a-\x2e\x3a\x3b\/\x3f-\x7a\x7e\x3d]+)`iu";
        } else {
            $regex = "`(?:(</?)([!a-z]+))|(/?\s*>)|((?:(?:https?|ftp):)?//[@a-z0-9\x21\x23-\x27\x2a-\x2e\x3a\x3b\/\x3f-\x7a\x7e\x3d]+)`i";
        }

        $mixed = FormatUtil::replaceButProtectCodeBlocks(
            $regex,
            $linksCallback,
            $mixed,
            true
        );

        Gdn::getContainer()
            ->get(EventManager::class)
            ->fire(
                'Format_Links',
                null, // To comply with the only handler type expecting (mixed $sender, array $args)
                ['Mixed' => &$mixed]
            );

        return $mixed;
    }

    /**
     * Strips out embed/iframes we support and replaces with placeholder.
     *
     * This allows later parsing to insert a sanitized video video embed normally.
     * Necessary for backwards compatibility from when we allowed embed & object tags.
     *
     * This is not an HTML filter; it enables old YouTube videos to theoretically work,
     * it doesn't effectively block YouTube iframes or objects.
     *
     * @param mixed $mixed
     * @return string
     * @deprecated 3.2 \Vanilla\EmbeddedContent\LegacyEmbedReplacer::unembedContent()
     */
    public static function unembedContent($mixed) {
        deprecated(__FUNCTION__, '\Vanilla\EmbeddedContent\LegacyEmbedReplacer::unembedContent()');
        if (!is_string($mixed)) {
            return self::to($mixed, 'UnembedContent');
        } else {
            return self::getLegacyReplacer()->unembedContent($mixed);
        }
    }


    /**
     * @return \Vanilla\EmbeddedContent\EmbedConfig
     */
    private static function getEmbedConfig(): \Vanilla\EmbeddedContent\EmbedConfig {
        $embedReplacer = Gdn::getContainer()->get(\Vanilla\EmbeddedContent\EmbedConfig::class);
        return $embedReplacer;
    }

    /**
     * Get an instance of the legacy embed replacer.
     *
     * @return \Vanilla\EmbeddedContent\LegacyEmbedReplacer
     */
    private static function getLegacyReplacer(): \Vanilla\EmbeddedContent\LegacyEmbedReplacer {
        $embedReplacer = Gdn::getContainer()->get(\Vanilla\EmbeddedContent\LegacyEmbedReplacer::class);
        return $embedReplacer;
    }

    /**
     * Returns embedded video width and height, based on configuration.
     *
     * @deprecated 3.2 \Vanilla\EmbeddedContent\EmbedConfig::getLegacyEmbedSize()
     * @return array array(Width, Height)
     */
    public static function getEmbedSize() {
        deprecated(__FUNCTION__, '\Vanilla\EmbeddedContent\EmbedConfig::getLegacyEmbedSize()');
        return self::getEmbedConfig()->getLegacyEmbedSize();
    }

    /**
     * Format a string using Markdown syntax.
     *
     * @param mixed $mixed An object, array, or string to be formatted.
     * @param null $flavor Deprecated param.
     * @return string Sanitized HTML.
     * @deprecated 3.2 FormatService::renderHtml($mixed, Formats\MarkdownFormat::FORMAT_KEY)
     */
    public static function markdown($mixed, $flavor = null) {
        if (!is_string($mixed)) {
            return self::to($mixed, 'Markdown');
        } else {
            if ($flavor) {
                deprecated(
                    __FUNCTION__ . ' param $flavor',
                    'config `Garden.Format.UseVanillaMarkdownFlavor`'
                );
            }
            return Gdn::formatService()->renderHTML($mixed, Formats\MarkdownFormat::FORMAT_KEY);
        }
    }

    /**
     * Adds a link to all mentions in a given string.
     *
     * Supports most usernames by using double-quotes, for example:  @"a $pecial user's! name."
     * Without double-quotes, a mentioned username is terminated by any of the following characters:
     * whitespace | . | , | ; | ? | ! | : | '
     *
     * @since 2.3
     *
     * @param string $str The html-formatted string to format mentions in.
     * @return string The formatted string.
     */
    protected static function formatMentionsCallback($str) {
        $parts = preg_split('`\B@`', $str);

        // We have no mentions here.
        if (count($parts) == 1) {
            return $str;
        }

        foreach ($parts as $i => $str) {
            // Text before the mention.
            if ($i == 0) {
                $str[$i] = htmlspecialchars($str);
                continue;
            }

            // There was an escaped @@.
            if (empty($str)) {
                $parts[$i - 1] = '';
                continue;
            }

            if (preg_match('`\w$`', $parts[$i - 1])) {
                $str[$i] = htmlspecialchars($str);
                continue;
            }

            // Grab the mention.
            $mention = false;
            $suffix = '';

            // Quoted mention.
            $hasQuote = false;
            $quote = '"';
            $quoteLength = strlen($quote);

            if (strpos($str, '"') === 0) {
                $hasQuote = true;
            } else if (strpos($str, '&quot;') === 0) {
                $hasQuote = true;
                $quote = '&quot;';
                $quoteLength = strlen($quote);
            }

            if ($hasQuote) {
                $pos = strpos($str, $quote, $quoteLength);

                if ($pos === false) {
                    $str = substr($str, $quoteLength);
                } else {
                    $mention = substr($str, $quoteLength, $pos - $quoteLength);
                    $suffix = substr($str, $pos + $quoteLength);
                }
            }

            // Unquoted mention.
            if (!$mention && !empty($str)) {
                $parts2 = preg_split('`([\s.,;?!:\'])`', $str, 2, PREG_SPLIT_DELIM_CAPTURE);
                $mention = $parts2[0];
                $suffix = val(1, $parts2, '') . val(2, $parts2, '');
            }

            if ($mention) {
                $attributes = [];
                if (self::$DisplayNoFollow) {
                    $attributes['rel'] = 'nofollow';
                }
                $parts[$i] =
                    anchor(
                        '@' . $mention,
                        url(str_replace('{name}', rawurlencode($mention), self::$MentionsUrlFormat), true),
                        '',
                        $attributes
                    ) . $suffix;
            } else {
                $parts[$i] = '@' . $parts[$i];
            }
        }

        return implode('', $parts);
    }

    /**
     * Handle mentions formatting.
     *
     * @param $mixed
     * @return mixed|string
     */
    public static function mentions($mixed) {
        if (!is_string($mixed)) {
            return self::to($mixed, 'Mentions');
        } else {
            // Check for a custom formatter.
            $formatter = Gdn::factory('MentionsFormatter');
            if (is_object($formatter)) {
                return $formatter->formatMentions($mixed);
            }

            // Handle @mentions.
            if (c('Garden.Format.Mentions', true)) {
                // Only format mentions that are not already in anchor tags or code tags.
                $mixed = self::tagContent($mixed, 'Gdn_Format::formatMentionsCallback');
            }

            // Handle #hashtag searches
            if (c('Garden.Format.Hashtags', false)) {
                $mixed = FormatUtil::replaceButProtectCodeBlocks(
                    '/(^|[\s,\.>])\#([\w\-]+)(?=[\s,\.!?<]|$)/i',
                    '\1'.anchor('#\2', url('/search?Search=%23\2&Mode=like', true)).'\3',
                    $mixed
                );
            }

            // Handle "/me does x" action statements
            if (c('Garden.Format.MeActions', false)) {
                $mixed = FormatUtil::replaceButProtectCodeBlocks(
                    '/(^|[\n])(\/me)(\s[^(\n)]+)/i',
                    '\1'.wrap(wrap('\2', 'span', ['class' => 'MeActionName']).'\3', 'span', ['class' => 'AuthorAction']),
                    $mixed
                );
            }

            return $mixed;
        }
    }

    /**
     * Reduces multiple whitespaces including line breaks and tabs to one single space character.
     *
     * @param string $string The string which should be optimized
     */
    public static function reduceWhiteSpaces($string) {
        return trim(preg_replace('/\s+/', ' ', $string));
    }

    /**
     * @deprecated 2.9 Use Formatting\FormatUtil::replaceButProtectCodeBlocks
     */
    public static function replaceButProtectCodeBlocks($search, $replace, $subject, $isCallback = false) {
        deprecated(__FUNCTION__, 'FormatUtil::replaceButProtectCodeBlocks');
        return Formatting\FormatUtil::replaceButProtectCodeBlocks(
            (string) $search,
            (string) $replace,
            (string) $subject,
            (bool) $isCallback
        );
    }

    /**
     * Return the input without any operations performed at all.
     *
     * This format should only be used when administrators have access.
     *
     * @deprecated 9 Nov 2016
     * @param string|object|array $mixed The data to format.
     * @return string
     */
    public static function raw($mixed) {
        deprecated('raw', 'wysiwyg');
        if (!is_string($mixed)) {
            return self::to($mixed, 'Raw');
        } else {
            // Deprecate raw formatting. It's too dangeous.
            return self::wysiwyg($mixed);
        }
    }

    /**
     * Takes an object and converts its properties => values to an associative
     * array of $Array[Property] => Value sets.
     *
     * @param object $object The object to be converted to an array.
     * @return unknown
     * @todo could be just "return (array) $object;"?
     */
    public static function objectAsArray($object) {
        if (!is_object($object)) {
            return $object;
        }

        $return = [];
        foreach (get_object_vars($object) as $property => $value) {
            $return[$property] = $value;
        }
        return $return;
    }

    /**
     * Formats seconds in a human-readable way (ie. 45 seconds, 15 minutes, 2 hours, 4 days, 2 months, etc).
     *
     * @param int $seconds
     * @return string
     */
    public static function seconds($seconds) {
        if (!is_numeric($seconds)) {
            $seconds = abs(time() - self::toTimestamp($seconds));
        }

        $minutes = round($seconds / 60);
        $hours = round($seconds / 3600);
        $days = round($seconds / 86400);
        $weeks = round($seconds / 604800);
        $months = round($seconds / 2629743.83);
        $years = round($seconds / 31556926);

        if ($seconds < 60) {
            return sprintf(plural($seconds, '%s second', '%s seconds'), $seconds);
        } elseif ($minutes < 60)
            return sprintf(plural($minutes, '%s minute', '%s minutes'), $minutes);
        elseif ($hours < 24)
            return sprintf(plural($hours, '%s hour', '%s hours'), $hours);
        elseif ($days < 7)
            return sprintf(plural($days, '%s day', '%s days'), $days);
        elseif ($weeks < 4)
            return sprintf(plural($weeks, '%s week', '%s weeks'), $weeks);
        elseif ($months < 12)
            return sprintf(plural($months, '%s month', '%s months'), $months);
        else {
            return sprintf(plural($years, '%s year', '%s years'), $years);
        }
    }

    /**
     * Takes any variable and serializes it.
     *
     * @param mixed $mixed An object, array, or string to be serialized.
     * @return string The serialized version of the string.
     */
    public static function serialize($mixed) {
        if (is_array($mixed) || is_object($mixed)
            || (is_string($mixed) && (substr_compare('a:', $mixed, 0, 2) !== 0  && substr_compare('O:', $mixed, 0, 2) !== 0
                    && substr_compare('arr:', $mixed, 0, 4) !== 0 && substr_compare('obj:', $mixed, 0, 4) !== 0))
        ) {
            $result = serialize($mixed);
        } else {
            $result = $mixed;
        }
        return $result;
    }

    /**
     * Takes a mixed variable, formats it for display on the screen as plain text.
     *
     * @param mixed $mixed An object, array, or string to be formatted.
     * @param bool|null $addBreaks
     * @return string Sanitized HTML.
     * @deprecated Formats\TextFormat::renderHtml()
     */
    public static function text($mixed, $addBreaks = null) {
        if (!is_string($mixed)) {
            return self::to($mixed, 'Text');
        }

        if ($addBreaks) {
            deprecated(__FUNCTION__ . ' param $addBreaks', 'config `Garden.Format.ReplaceNewlines`');
        }

        return Gdn::formatService()->renderHTML((string) $mixed, Formats\TextFormat::FORMAT_KEY);
    }

    /**
     * Process as plain text + our magic formatting.
     *
     * @param string $str
     * @return string Sanitized HTML.
     * @since 2.1
     * @deprecated 3.2 FormatService::renderHtml($str, Formats\TextExFormat::FORMAT_KEY)
     */
    public static function textEx($str) {
        if (!is_string($str)) {
            return self::to($str, 'TextEx');
        }

        return Gdn::formatService()->renderHTML($str, Formats\TextExFormat::FORMAT_KEY);
    }

    /**
     * Takes a mixed variable, formats it in the specified format type, and returns it.
     *
     * @param mixed $mixed An object, array, or string to be formatted.
     * @param string $formatMethod The method with which the variable should be formatted.
     * @return mixed
     * @deprecated 3.2 FormatService::renderHtml
     */
    public static function to($mixed, $formatMethod) {
        // Process $Mixed based on its type.
        if (is_string($mixed)) {
            if (in_array(strtolower($formatMethod), self::$SanitizedFormats) && method_exists('Gdn_Format', $formatMethod)) {
                $mixed = self::$formatMethod($mixed);
            } elseif (function_exists('gdn_formatter_'.$formatMethod)) {
                $formatMethod = 'gdn_formatter_'.$formatMethod;
                $mixed = $formatMethod($mixed);
            } elseif ($formatter = Gdn::factory($formatMethod.'Formatter')) {
                $mixed = $formatter->format($mixed);
            } else {
                $mixed = Gdn_Format::text($mixed);
            }
        } elseif (is_array($mixed)) {
            foreach ($mixed as $key => $val) {
                $mixed[$key] = self::to($val, $formatMethod);
            }
        } elseif (is_object($mixed)) {
            foreach (get_object_vars($mixed) as $prop => $val) {
                $mixed->$prop = self::to($val, $formatMethod);
            }
        }
        return $mixed;
    }

    /**
     * Format a timestamp or the current time to go into the database.
     *
     * @param int $timestamp
     * @return string The formatted date.
     */
    public static function toDate($timestamp = '') {
        if ($timestamp == '') {
            $timestamp = time();
        } elseif (!is_numeric($timestamp)) {
            $timestamp = self::toTimestamp($timestamp);
        }

        return date('Y-m-d', $timestamp);
    }

    /**
     * Format a timestamp or the current time to go into the database.
     *
     * @param int $timestamp
     * @return string The formatted date and time.
     */
    public static function toDateTime($timestamp = '') {
        if ($timestamp == '') {
            $timestamp = time();
        }
        return date('Y-m-d H:i:s', $timestamp);
    }

    /**
     * Convert a datetime to a timestamp.
     *
     * @param string $dateTime The Mysql-formatted datetime to convert to a timestamp. Should be in one
     * of the following formats: YYYY-MM-DD or YYYY-MM-DD HH:MM:SS.
     * @return string|bool Returns FALSE upon failure.
     */
    public static function toTimestamp($dateTime = '') {
        if ($dateTime === '0000-00-00 00:00:00') {
            return false;
        } elseif (($testTime = strtotime($dateTime)) !== false) {
            return $testTime;
        } elseif (preg_match('/^(\d{4})-(\d{1,2})-(\d{1,2})(?:\s{1}(\d{1,2}):(\d{1,2})(?::(\d{1,2}))?)?$/', $dateTime, $matches)) {
            $year = $matches[1];
            $month = $matches[2];
            $day = $matches[3];
            $hour = val(4, $matches, 0);
            $minute = val(5, $matches, 0);
            $second = val(6, $matches, 0);
            return mktime($hour, $minute, $second, $month, $day, $year);
        } elseif (preg_match('/^(\d{4})-(\d{1,2})-(\d{1,2})$/', $dateTime, $matches)) {
            $year = $matches[1];
            $month = $matches[2];
            $day = $matches[3];
            return mktime(0, 0, 0, $month, $day, $year);
        } else {
            return false;
        }
    }

    /**
     * Formats a timestamp to the current user's timezone.
     *
     * @param int $timestamp The timestamp in gmt.
     * @return int The timestamp according to the user's timezone.
     */
    public static function toTimezone($timestamp) {
        // Alter the timestamp based on the user's hour offset.
        $hourOffset = Gdn::session()->hourOffset();

        if ($hourOffset <> 0) {
            $timestamp += $hourOffset * 3600;
        }

        return $timestamp;
    }

    /**
     *
     *
     * @param int $timespan
     * @return string
     */
    public static function timespan($timespan) {
        //$timespan -= 86400 * ($days = (int) floor($timespan / 86400));
        $timespan -= 3600 * ($hours = (int)floor($timespan / 3600));
        $timespan -= 60 * ($minutes = (int)floor($timespan / 60));
        $seconds = $timespan;

        $result = sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
        return $result;
    }

    /** @var array  */
    protected static $_UrlTranslations = ['–' => '-', '—' => '-', 'À' => 'A', 'Á' => 'A', 'Â' => 'A', 'Ã' => 'A', 'Ä' => 'Ae', 'Ä' => 'A', 'Å' => 'A', 'Ā' => 'A', 'Ą' => 'A', 'Ă' => 'A', 'Æ' => 'Ae', 'Ç' => 'C', 'Ć' => 'C', 'Č' => 'C', 'Ĉ' => 'C', 'Ċ' => 'C', 'Ď' => 'D', 'Đ' => 'D', 'Ð' => 'D', 'È' => 'E', 'É' => 'E', 'Ê' => 'E', 'Ë' => 'E', 'Ē' => 'E', 'Ě' => 'E', 'Ĕ' => 'E', 'Ė' => 'E', 'Ĝ' => 'G', 'Ğ' => 'G', 'Ġ' => 'G', 'Ģ' => 'G', 'Ĥ' => 'H', 'Ħ' => 'H', 'Ì' => 'I', 'Í' => 'I', 'Î' => 'I', 'Ï' => 'I', 'Ī' => 'I', 'Ĩ' => 'I', 'Ĭ' => 'I', 'Į' => 'I', 'İ' => 'I', 'Ĳ' => 'IJ', 'Ĵ' => 'J', 'Ķ' => 'K', 'Ł' => 'K', 'Ľ' => 'K', 'Ĺ' => 'K', 'Ļ' => 'K', 'Ŀ' => 'K', 'Ñ' => 'N', 'Ń' => 'N', 'Ň' => 'N', 'Ņ' => 'N', 'Ŋ' => 'N', 'Ò' => 'O', 'Ó' => 'O', 'Ô' => 'O', 'Õ' => 'O', 'Ö' => 'Oe', 'Ö' => 'Oe', 'Ō' => 'O', 'Ő' => 'O', 'Ŏ' => 'O', 'Œ' => 'OE', 'Ŕ' => 'R', 'Ŗ' => 'R', 'Ś' => 'S', 'Š' => 'S', 'Ş' => 'S', 'Ŝ' => 'S', 'Ť' => 'T', 'Ţ' => 'T', 'Ŧ' => 'T', 'Ț' => 'T', 'Ù' => 'U', 'Ú' => 'U', 'Û' => 'U', 'Ü' => 'Ue', 'Ū' => 'U', 'Ü' => 'Ue', 'Ů' => 'U', 'Ű' => 'U', 'Ŭ' => 'U', 'Ũ' => 'U', 'Ų' => 'U', 'Ŵ' => 'W', 'Ý' => 'Y', 'Ŷ' => 'Y', 'Ÿ' => 'Y', 'Ź' => 'Z', 'Ž' => 'Z', 'Ż' => 'Z', 'Þ' => 'T', 'à' => 'a', 'á' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'ae', 'ä' => 'ae', 'å' => 'a', 'ā' => 'a', 'ą' => 'a', 'ă' => 'a', 'æ' => 'ae', 'ç' => 'c', 'ć' => 'c', 'č' => 'c', 'ĉ' => 'c', 'ċ' => 'c', 'ď' => 'd', 'đ' => 'd', 'ð' => 'd', 'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e', 'ē' => 'e', 'ę' => 'e', 'ě' => 'e', 'ĕ' => 'e', 'ė' => 'e', 'ƒ' => 'f', 'ĝ' => 'g', 'ğ' => 'g', 'ġ' => 'g', 'ģ' => 'g', 'ĥ' => 'h', 'ħ' => 'h', 'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i', 'ī' => 'i', 'ĩ' => 'i', 'ĭ' => 'i', 'į' => 'i', 'ı' => 'i', 'ĳ' => 'ij', 'ĵ' => 'j', 'ķ' => 'k', 'ĸ' => 'k', 'ł' => 'l', 'ľ' => 'l', 'ĺ' => 'l', 'ļ' => 'l', 'ŀ' => 'l', 'ñ' => 'n', 'ń' => 'n', 'ň' => 'n', 'ņ' => 'n', 'ŉ' => 'n', 'ŋ' => 'n', 'ò' => 'o', 'ó' => 'o', 'ô' => 'o', 'õ' => 'o', 'ö' => 'oe', 'ö' => 'oe', 'ø' => 'o', 'ō' => 'o', 'ő' => 'o', 'ŏ' => 'o', 'œ' => 'oe', 'ŕ' => 'r', 'ř' => 'r', 'ŗ' => 'r', 'š' => 's', 'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'ü' => 'ue', 'ū' => 'u', 'ü' => 'ue', 'ů' => 'u', 'ű' => 'u', 'ŭ' => 'u', 'ũ' => 'u', 'ų' => 'u', 'ŵ' => 'w', 'ý' => 'y', 'ÿ' => 'y', 'ŷ' => 'y', 'ž' => 'z', 'ż' => 'z', 'ź' => 'z', 'þ' => 't', 'ß' => 'ss', 'ſ' => 'ss', 'А' => 'A', 'Б' => 'B', 'В' => 'V', 'Г' => 'G', 'Д' => 'D', 'Е' => 'E', 'Ё' => 'YO', 'Ж' => 'ZH', 'З' => 'Z', 'И' => 'I', 'И' => 'I', 'І' => 'I', 'Й' => 'Y', 'К' => 'K', 'Л' => 'L', 'М' => 'M', 'Н' => 'N', 'О' => 'O', 'П' => 'P', 'Р' => 'R', 'С' => 'S', 'ș' => 's', 'ț' => 't', 'Ț' => 'T', 'Т' => 'T', 'У' => 'U', 'Ф' => 'F', 'Х' => 'H', 'Ц' => 'C', 'Ч' => 'CH', 'Ш' => 'SH', 'Щ' => 'SCH', 'Ъ' => '', 'Ы' => 'Y', 'Ь' => '', 'Э' => 'E', 'Ю' => 'YU', 'Я' => 'YA', 'Є' => 'YE', 'Ї' => 'YI', 'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd', 'е' => 'e', 'ё' => 'yo', 'ж' => 'zh', 'з' => 'z', 'и' => 'i', 'і' => 'i', 'й' => 'y', 'к' => 'k', 'л' => 'l', 'м' => 'm', 'н' => 'n', 'о' => 'o', 'п' => 'p', 'р' => 'r', 'с' => 's', 'т' => 't', 'у' => 'u', 'ф' => 'f', 'х' => 'h', 'ц' => 'c', 'ч' => 'ch', 'ш' => 'sh', 'щ' => 'sch', 'ъ' => '', 'ы' => 'y', 'ь' => '', 'э' => 'e', 'ю' => 'yu', 'я' => 'ya', 'є' => 'ye', 'ї' => 'yi'];

    /**
     * Creates URL codes containing only lowercase Roman letters, digits, and hyphens.
     *
     * @param mixed $mixed An object, array, or string to be formatted.
     * @return string
     */
    public static function url($mixed) {
        if (!is_string($mixed)) {
            return self::to($mixed, 'Url');
        }

        // Preliminary decoding
        $mixed = strip_tags(html_entity_decode($mixed, ENT_COMPAT, 'UTF-8'));
        $mixed = strtr($mixed, self::$_UrlTranslations);
        $mixed = preg_replace('`[\']`', '', $mixed);

        // Convert punctuation, symbols, and spaces to hyphens
        if (unicodeRegexSupport()) {
            $mixed = preg_replace('`[\pP\pS\s]`u', '-', $mixed);
        } else {
            $mixed = preg_replace('`[\W_]`', '-', $mixed);
        }

        // Lowercase, no trailing or repeat hyphens
        $mixed = preg_replace('`-+`', '-', strtolower($mixed));
        $mixed = trim($mixed, '-');

        return rawurlencode($mixed);
    }

    /**
     * Takes a serialized variable and unserializes it back into its original state.
     *
     * @param string $serializedString A json or php serialized string to be unserialized.
     * @return mixed
     * @deprecated
     */
    public static function unserialize($serializedString) {
        $result = $serializedString;

        if (is_string($serializedString)) {
            if (substr_compare('a:', $serializedString, 0, 2) === 0 || substr_compare('O:', $serializedString, 0, 2) === 0) {
                $result = unserialize($serializedString, ['allowed_classes' => false]);
            } elseif (substr_compare('obj:', $serializedString, 0, 4) === 0) {
                $result = json_decode(substr($serializedString, 4), false);
            } elseif (substr_compare('arr:', $serializedString, 0, 4) === 0) {
                $result = json_decode(substr($serializedString, 4), true);
            }
        }
        return $result;
    }

    /**
     *
     *
     * @param $placeholderString
     * @param $replaceWith
     * @return mixed
     */
    public static function vanillaSprintf($placeholderString, $replaceWith) {
        // Set replacement array inside callback
        Gdn_Format::vanillaSprintfCallback(null, $replaceWith);

        $finalString = preg_replace_callback('/({([a-z0-9_:]+)})/i', ['Gdn_Format', 'VanillaSprintfCallback'], $placeholderString);

        // Cleanup replacement list
        Gdn_Format::vanillaSprintfCallback(null, []);

        return $finalString;
    }

    /**
     *
     *
     * @param $match
     * @param bool $internalReplacementList
     * @return mixed
     */
    protected static function vanillaSprintfCallback($match, $internalReplacementList = false) {
        static $internalReplacement = [];

        if (is_array($internalReplacementList)) {
            $internalReplacement = $internalReplacementList;
        } else {
            $matchStr = $match[2];
            $format = (count($splitMatch = explode(':', $matchStr)) > 1) ? $splitMatch[1] : false;

            if (array_key_exists($matchStr, $internalReplacement)) {
                if ($format) {
                    // TODO: Apply format
                }
                return $internalReplacement[$matchStr];
            }

            return $match[1];
        }
    }

    /**
     * Format text from WYSIWYG editor input.
     *
     * @param $mixed
     * @return mixed|string
     *
     * @deprecated 3.2 FormatService::renderHtml($string, Formats\WysiwygFormat::FORMAT_KEY)
     */
    public static function wysiwyg($mixed) {
        static $customFormatter;
        if (!isset($customFormatter)) {
            $customFormatter = c('Garden.Format.WysiwygFunction', false);
        }

        if (!is_string($mixed)) {
            return self::to($mixed, 'Wysiwyg');
        } elseif (is_callable($customFormatter)) {
            deprecated(
                'Garden.Format.WysiwygFunction',
                'Replace WysiwygFormat using Garden\Container'
            );
            return $customFormatter($mixed);
        } else {
            return Gdn::formatService()->renderHTML($mixed, Formats\WysiwygFormat::FORMAT_KEY);
        }
    }

    /**
     * Format text from Rich editor input.
     *
     * @param string $deltas A JSON encoded array of Quill deltas.
     *
     * @return string - The rendered HTML output.
     * @deprecated 3.2 FormatService::renderHtml($content, Formats\RichFormat::FORMAT_KEY)
     */
    public static function rich(string $deltas): string {
        deprecated(__FUNCTION__, 'FormatService::renderHtml($content, Formats\RichFormat::FORMAT_KEY)');
        return Gdn::formatService()->renderHTML($deltas, Formats\RichFormat::FORMAT_KEY);
    }

    /**
     * Generate a quote to embed in a Rich quote for all existing formats.
     *
     * @param string|array $body The string or array body content of the post.
     * @param string $format The initial format of the post.
     *
     * @return string
     * @deprecated 3.2 FormatService::renderQuote($body, $format)
     */
    public static function quoteEmbed($body, string $format): string {
        deprecated(__FUNCTION__, 'FormatService::renderQuote($body, $format)');
        $body = is_array($body) ? json_encode($body) : $body;
        return Gdn::formatService()->renderQuote($body, $format);
    }

    /**
     * Sanitize a URL to ensure that it matches a whitelist of approved url schemes. If the url does not match one of these schemes, prepend `unsafe:` before it.
     * Get the usernames mention in a rich post.
     *
     * @param string $body The contents of a post body.
     *
     * @return string[]
     * @deprecated 3.2 FormatService::parseMentions($body, Formats\RichFormat::FORMAT_KEY)
     */
    public static function getRichMentionUsernames(string $body): array {
        deprecated(__FUNCTION__, 'RichFormat::parseMentions($body)');
        return Gdn::formatService()->parseMentions($body, Formats\RichFormat::FORMAT_KEY);
    }

    const SAFE_PROTOCOLS = [
        "http",
        "https",
        "tel",
        "mailto",
    ];

    /**
     * Encode special CSS characters as hex.
     *
     * Allowed protocols
     * - "http:",
     * - "https:",
     * - "tel:",
     * - "mailto:",
     *
     * @param string $url The url to sanitize.
     *
     * @return string
     */
    public static function sanitizeUrl(string $url): string {
        $protocol = parse_url($url, PHP_URL_SCHEME) ?: "";
        $isSafe = in_array($protocol, self::SAFE_PROTOCOLS, true);

        if ($isSafe) {
            return $url;
        } else {
            return "unsafe:".$url;
        }
    }
}
