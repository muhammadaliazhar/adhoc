<?php
/**
 * This file is part of the SetaPDF-FormFiller Component
 *
 * @copyright  Copyright (c) 2022 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_FormFiller
 * @subpackage Field
 * @license    https://www.setasign.com/ Commercial
 * @version    $Id$
 */

/**
 * Field formatter class emulating various standard formatting functions from Acrobat.
 *
 * @copyright  Copyright (c) 2022 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @category   SetaPDF
 * @package    SetaPDF_FormFiller
 * @subpackage Field
 * @license    https://www.setasign.com/ Commercial
 */
class SetaPDF_FormFiller_Field_Formatter
{
    /**
     * Main method which can be used as appearance value callback.
     *
     * @see SetaPDF_FormFiller_Field_AppearanceValueCallbackInterface::setAppearanceValueCallback()
     * @param SetaPDF_FormFiller_Field_AppearanceValueCallbackInterface $field
     * @param string $encoding
     * @return string
     */
    public static function format(SetaPDF_FormFiller_Field_AppearanceValueCallbackInterface $field, $encoding)
    {
        $value = $field->getVisibleValue();
        $additionalActions = SetaPDF_Core_Type_Dictionary_Helper::resolveAttribute($field->getFieldDictionary(), 'AA');
        if ($additionalActions && $additionalActions->offsetExists('F')) {
            /** @var SetaPDF_Core_Document_Action_JavaScript $action */
            $action = SetaPDF_Core_Document_Action::byObjectOrDictionary($additionalActions->getValue('F'));

            if (preg_match('~(?<function>AF[A-Za-z_]*?)\((?<arguments>.*?)\);~', $action->getJavaScript(), $m)) {
                $function = $m['function'];
                // escape argument string to be able to decode it as JSON.
                $argumentsStringIn = $m['arguments'];
                $argumentsStringOut = '';
                for ($i = 0, $len = strlen($argumentsStringIn); $i < $len; $i++) {
                    $char = $argumentsStringIn[$i];
                    switch ($char) {
                        case "\t":
                            $argumentsStringOut .= '\t';
                            break;
                        case '\\':
                            $argumentsStringOut .= $char;
                            if (isset($argumentsStringIn[$i + 1])) {
                                switch ($argumentsStringIn[$i + 1]) {
                                    case '"':
                                    case 'u':
                                        break 2;
                                }
                            }
                        default:
                            $argumentsStringOut .= $char;
                    }
                }

                $arguments = json_decode('[' . $argumentsStringOut . ']', true, 2);

                if ($arguments !== null && method_exists('SetaPDF_FormFiller_Field_Formatter', $function)) {
                    try {
                        $value = self::$function($field, $arguments);
                    } catch (InvalidArgumentException $e) {
                        // silence/ignore invalid arguments
                    }
                }
            }
        }

        return SetaPDF_Core_Encoding::convert($value, 'UTF-8', $encoding);
    }

    /**
     * Creates a DateTime object from a value and an optional format string.
     *
     * @param string $value
     * @param null|string $format
     * @return DateTime|false
     */
    public static function makeDate($value, $format = null)
    {
        if (trim($value) === '') {
            return false;
        }

        $delemitter = ' \-:\./|\\\\';

        if ($format !== null) {
            $regex = '~(?J)';
            foreach (self::_tokenizeFormat($format) as $token) {
                switch ($token) {
                    case 'd':
                    case 'dd':
                        $regex .= '(?<day>[^' . $delemitter .']+)';
                        break;
                    case 'ddd':
                    case 'dddd':
                        $regex .= '([^' . $delemitter .']+)';
                        break;
                    case 'm':
                    case 'mm':
                    case 'mmm':
                    case 'mmmm':
                        $regex .= '(?<month>[^' . $delemitter .']+)';
                        break;
                    case 'yy':
                    case 'yyyy':
                        $regex .= '(?<year>[^' . $delemitter .']+)';
                        break;
                    case 'h':
                    case 'HH':
                        $regex .= '(?<hour>[^' . $delemitter .']+)';
                        break;
                    case 'M':
                    case 'MM':
                        $regex .= '(?<minutes>[^' . $delemitter .']+)';
                        break;
                    case 's':
                    case 'ss':
                        $regex .= '(?<seconds>[^' . $delemitter .']+)';
                        break;
                    case 't':
                    case 'tt':
                        $regex .= '(?<amOrPm>[^' . $delemitter .']+)';
                        break;
                    default:
                        if (preg_match('~^[' . $delemitter .']+$~', $token)) {
                            $regex .= '[' . $delemitter .']+';
                        } else {
                            if ($token[0] === '\\') {
                                $token = substr($token, 1);
                            }
                            $regex .= preg_quote($token, '~');
                        }
                }
            }

            $regex .= '~';

            if (preg_match($regex, $value, $m)) {
                $s = '';
                if (isset($m['year'])) {
                    $s .= $m['year'];
                } else {
                    $s .= '1970';
                }

                $s .= '-';

                if (isset($m['month'])) {
                    $s .= str_pad($m['month'], 2, '0', STR_PAD_LEFT);
                } else {
                    $s .= '01';
                }

                $s .= '-';

                if (isset($m['day'])) {
                    $s .= str_pad($m['day'], 2, '0', STR_PAD_LEFT);
                } else {
                    $s .= '01';
                }

                $s .= ' ';

                if (isset($m['hour'])) {
                    $hour = $m['hour'];
                    if ($hour < 12 && isset($m['amOrPm']) && $m['amOrPm'][0] === 'p') {
                        $hour += 12;
                    }

                    if ($hour == 12 && isset($m['amOrPm']) && $m['amOrPm'][0] === 'a') {
                        $hour = 0;
                    }

                    $s .= str_pad($hour, 2, '0', STR_PAD_LEFT);
                } else {
                    $s .= '00';
                }

                $s .= ':';

                if (isset($m['minutes'])) {
                    $s .= str_pad($m['minutes'], 2, '0', STR_PAD_LEFT);
                } else {
                    $s .= '00';
                }

                $s .= ':';

                if (isset($m['seconds'])) {
                    $s .= str_pad($m['seconds'], 2, '0', STR_PAD_LEFT);
                } else {
                    $s .= '00';
                }

                try {
                    $date = new DateTime($s);
                } catch (Exception $e) {
                    return false;
                }

                return $date;
            }
        }

        // pad all numbers to a length of 2 to avoid conflicts with recognization of time-zone values
        $parts = preg_split('~([' . $delemitter . '])~', $value, -1, PREG_SPLIT_DELIM_CAPTURE);
        $value = implode('', array_map(static function($item) {
            if (is_numeric($item)) {
                return str_pad($item, 2, '0', STR_PAD_LEFT);
            }

            return $item;
        }, $parts));

        try {
            $date = new DateTime($value);
        } catch (Exception $e) {
            return false;
        }

        return $date;
    }

    /**
     * Makes a number from a string value by accepting various decimal and thousand delimiters.
     *
     * @param string $value
     * @return float
     */
    public static function makeNumber($value)
    {
        $value = preg_replace('/[^\d.,\-]/u', '', $value);

        $hasPoint = preg_match_all('/\./', $value, $points, PREG_OFFSET_CAPTURE);
        $hasComma = preg_match_all('/,/', $value, $commas, PREG_OFFSET_CAPTURE);

        if ($hasPoint && $hasComma) {
            if ($points[0][0][1] < $commas[0][0][1]) {
                $value = str_replace('.', '', $value);
                $value = str_replace(',', '.', $value);
            } else {
                $value = str_replace(',', '', $value);
            }
        } elseif ($hasComma) {
            // thousand operator
            if (count($commas[0]) > 1) {
                $value = str_replace(',', '', $value);
            // decimal
            } else {
                $value = str_replace(',', '.', $value);
            }
        } elseif ($hasPoint) {
            // thousan operator
            if (count($points[0]) > 1) {
                $value = str_replace('.', '', $value);
            }
        }

        return (float)$value;
    }

    /**
     * Implementation of AFNumber_Format
     *
     * @param SetaPDF_FormFiller_Field_AppearanceValueCallbackInterface $field
     * @param array $arguments
     * @return string
     */
    protected static function AFNumber_Format(
        SetaPDF_FormFiller_Field_AppearanceValueCallbackInterface $field,
        array $arguments
    )
    {
        if (count($arguments) !== 6) {
            throw new InvalidArgumentException('Not enough or to much arguments passed to "AFNumber_Format".');
        }

        $value = $field->getVisibleValue();
        if (trim($value) === '') {
            return '';
        }

        $value = self::makeNumber($value);

        list($nDec, $sepStyle, $negStyle, $currStyle, $strCurrency, $bCurrencyPrepend) = $arguments;

        $sign = ($value < 0 ? -1 : 1);
        if ($negStyle !== 0 /* MinusBlack */ || $bCurrencyPrepend) {
            $value = abs($value);
        }

        if (($negStyle === 2 /* ParensBlack */ || $negStyle === 3 /* ParensRed */) && $sign < 0) {
            $formatValue = '(';
        } else {
            $formatValue = '';
        }

        if ($bCurrencyPrepend) {
            $formatValue .= $strCurrency;
        }

        $formatValue .= number_format(
            $value,
            $nDec,
            ($sepStyle === 2 || $sepStyle === 3) ? ',' : '.',
            $sepStyle === 0 ? ',' : ($sepStyle === 2 ? '.' : ($sepStyle === 4 ? "'" : ''))
        );

        if (!$bCurrencyPrepend) {
            $formatValue .= $strCurrency;
        }

        if (($negStyle === 2 /* ParensBlack */ || $negStyle === 3 /* ParensRed */) && $sign < 0) {
            $formatValue .= ')';
        }

        if ($negStyle === 1 /* Red */ || $negStyle === 3 /* ParensRed */) {
            if ($sign > 0) {
                $field->setAppearanceTextColor(SetaPDF_Core_DataStructure_Color::createByComponents(0));
            } else {
                $field->setAppearanceTextColor(SetaPDF_Core_DataStructure_Color::createByComponents([1, 0, 0]));
            }
        }

        if ($sign < 0 && $bCurrencyPrepend && $negStyle === 0) {
            $formatValue = '-' . $formatValue; /* prepend the -ve sign */
        }

        return $formatValue;
    }

    /**
     * Implementation of AFPercent_Format
     *
     * @param SetaPDF_FormFiller_Field_AppearanceValueCallbackInterface $field
     * @param array $arguments
     * @return string
     */
    protected static function AFPercent_Format(
        SetaPDF_FormFiller_Field_AppearanceValueCallbackInterface $field,
        array $arguments
    )
    {
        if (count($arguments) !== 2) {
            throw new InvalidArgumentException('Not enough or to much arguments passed to "AFPercent_Format".');
        }

        list($nDec, $sepStyle) = $arguments;
        return number_format(
                self::makeNumber($field->getVisibleValue()) * 100,
                $nDec,
                ($sepStyle === 0 || $sepStyle === 1) ? '.' : ',',
                $sepStyle === 0 ? ',' : ($sepStyle === 2 ? '.' : '')
            ) . '%';
    }

    /**
     * Implementation of AFTime_Format
     *
     * @param SetaPDF_FormFiller_Field_AppearanceValueCallbackInterface $field
     * @param array $arguments
     * @return mixed|string
     */
    protected static function AFTime_Format(
        SetaPDF_FormFiller_Field_AppearanceValueCallbackInterface $field,
        array $arguments
    )
    {
        list($ptf) = $arguments;

        /* 0 = 24HR_MM [ 14:30 ]
         * 1 = 12HR_MM [ 2:30 PM ]
         * 2 = 24HR_MM_SS [ 14:30:15 ]
         * 3 = 12HR_MM_SS [ 2:30:15 PM ]
         */
        $format = null;
        $formats = ['HH:MM', 'h:MM tt', 'HH:MM:ss', 'h:MM:ss tt'];
        if (isset($formats[$ptf])) {
            $format = $formats[$ptf];
        }

        $time = self::makeDate(SetaPDF_Core_Encoding::convertPdfString($field->getVisibleValue()), $format);
        if ($time === false) {
            return '';
        }

        if (isset($format)) {
            return self::printd($format, $time);
        }

        return $field->getVisibleValue();
    }

    /**
     * Implementation of AFTime_FormatEx
     *
     * @param SetaPDF_FormFiller_Field_AppearanceValueCallbackInterface $field
     * @param array $arguments
     * @return string
     */
    protected static function AFTime_FormatEx(
        SetaPDF_FormFiller_Field_AppearanceValueCallbackInterface $field,
        array $arguments
    ) {
        return self::AFDate_FormatEx($field, $arguments);
    }

    /**
     * Implementation of AFDate_Format
     *
     * @param SetaPDF_FormFiller_Field_AppearanceValueCallbackInterface $field
     * @param array $arguments
     * @return string
     */
    protected static function AFDate_Format(
        SetaPDF_FormFiller_Field_AppearanceValueCallbackInterface $field,
        array $arguments
    )
    {
        list($format) = $arguments;
        $oldFormats = [
            'm/d', 'm/d/yy', 'mm/dd/yy', 'mm/yy', 'd-mmm', 'd-mmm-yy', 'dd-mmm-yy', 'yy-mm-dd',
            'mmm-yy', 'mmmm-yy', 'mmm d, yyyy', 'mmmm d, yyyy', 'm/d/yy h:MM tt', 'm/d/yy HH:MM'
        ];

        return self::AFDate_FormatEx($field, [$oldFormats[$format]]);
    }

    /**
     * Implementation of AFDate_FormatEx
     *
     * @param SetaPDF_FormFiller_Field_AppearanceValueCallbackInterface $field
     * @param array $arguments
     * @return string
     */
    protected static function AFDate_FormatEx(
        SetaPDF_FormFiller_Field_AppearanceValueCallbackInterface $field,
        array $arguments
    ) {
        list($format) = $arguments;

        // Sadly this seems to be a wrong implementation in Acrobat itself
        $format = stripslashes($format);

        $date = self::makeDate($field->getVisibleValue(), $format);
        if ($date === false) {
            return '';
        }

        return self::printd($format, $date);
    }

    /**
     * Implementation of AFSpecial_Format
     *
     * @param SetaPDF_FormFiller_Field_AppearanceValueCallbackInterface $field
     * @param array $arguments
     * @return string
     */
    protected static function AFSpecial_Format(
        SetaPDF_FormFiller_Field_AppearanceValueCallbackInterface $field,
        array $arguments
    )
    {
        list($format) = $arguments;

        $value = $field->getVisibleValue();
        if ($value === false) {
            return '';
        }

        switch ((int)$format) {
            case 0:
                return self::printx('99999', $value);
            case 1:
                return self::printx('99999-9999', $value);
            case 2:
                if (strlen(self::printx('9999999999', $value)) >= 10) {
                    $format = '(999) 999-9999';
                } else {
                    $format = '999-9999';
                }
                return self::printx($format, $value);
            case 3:
                return self::printx('999-99-9999', $value);
        }

        throw new InvalidArgumentException('Unknown format.');
    }

    /**
     * Splits a format string into tokens.
     *
     * @param string $format
     * @return array
     */
    protected static function _tokenizeFormat($format)
    {
        $parts = [];
        $prev = $curr = null;
        $tmp = '';
        for ($i = 0, $length = SetaPDF_Core_Encoding::strlen($format); $i < $length; $i++) {
            $curr = SetaPDF_Core_Encoding::substr($format, $i, 1);
            if ($prev !== $curr && $i !== 0 && $prev !== '\\') {
                $parts[] = $tmp;
                $tmp = '';
            }

            $tmp .= $curr;
            $prev = $curr;
        }

        $parts[] = $tmp;

        return $parts;
    }

    /**
     * Implementation of printd() method from Acrobat JS API.
     *
     * @see https://www.adobe.com/content/dam/acom/en/devnet/acrobat/pdfs/js_api_reference.pdf
     * @param string $cFormat
     * @param DateTimeInterface $date
     * @return string
     */
    public static function printd($cFormat, DateTimeInterface $date)
    {
        // todo:
//        0 — PDF date format. Example: D:20000801145605+07'00'
//        1 — Universal. Example: D:20000801145605+07'00'
//        2 — Localized string. Example: 2000/08/01 14:56:05
        if ($cFormat === '0') {
            return self::printd('D:yyyymmddHHMMss', $date);
        }

        if ($cFormat === '1') {
            return self::printd('yyyy.mm.dd HH:MM:ss', $date);
        }

        if ($cFormat === '2') {
            return self::printd('m/d/yy h:MM:ss tt', $date);
        }

        $formatValue = '';
        foreach (self::_tokenizeFormat($cFormat) as $part) {
            switch ($part) {
                // A two digit representation of a year
                case 'yy':
                    $formatValue .= $date->format('y');
                    break;
                // A full numeric representation of a year, 4 digits
                case 'yyyy':
                    $formatValue .= $date->format('Y');
                    break;
                // Day of the month without leading zeros
                case 'd':
                    $formatValue .= $date->format('j');
                    break;
                // Day of the month with leading zeros
                case 'dd':
                    $formatValue .= $date->format('d');
                    break;
                case 'ddd':
                    $formatValue .= $date->format('D');
                    break;
                case 'dddd':
                    $formatValue .= $date->format('l');
                    break;
                // Numeric representation of a month, without leading zeros
                case 'm':
                    $formatValue .= $date->format('n');
                    break;
                // Numeric representation of a month, with leading zeros
                case 'mm':
                    $formatValue .= $date->format('m');
                    break;
                // A short textual representation of a month, three letters
                case 'mmm':
                    $formatValue .= $date->format('M');
                    break;
                // A full textual representation of a month, such as January or March
                case 'mmmm':
                    $formatValue .= $date->format('F');
                    break;
                // 12-hour format of an hour without leading zeros
                case 'h':
                    // 12-hour format of an hour with leading zeros
                case 'hh':
                    $meridiem = $date->format('a');
                    $hour = $date->format($part === 'h' ? 'g' : 'h');
                    if ($meridiem === 'am' && $hour === '12') {
                        if ($part === 'h') {
                            $hour = '0';
                        } else {
                            $hour = '00';
                        }
                    }
                    $formatValue .= $hour;
                    break;
                // 24-hour format of an hour with leading zeros
                case 'HH':
                    $formatValue .= $date->format('H');
                    break;
                // Minutes with leading zeros
                case 'MM':
                    $formatValue .= $date->format('i');
                    break;
                // Minutes without leading zeros
                case 'M':
                    $formatValue .= str_pad(ltrim($date->format('i'), '0'), 2, ' ', STR_PAD_LEFT);
                    break;
                // Seconds with leading zeros
                case 'ss':
                    $formatValue .= $date->format('s');
                    break;
                // Seconds without leading zeros
                case 's':
                    $formatValue .= str_pad(ltrim($date->format('s'), '0'), 2, ' ', STR_PAD_LEFT);
                    break;
                // Lowercase Ante meridiem and Post meridiem
                case 'tt':
                    $formatValue .= $date->format('a');
                    break;
                // Lowercase Ante meridiem and Post meridiem
                case 't':
                    $formatValue .= $date->format('a')[0];
                    break;
                default:
                    if ($part[0] === '\\') {
                        $part = substr($part, 1);
                    }
                    $formatValue .= $part;
            }
        }

        return $formatValue;
    }

    /**
     * Implementation of printx() method from Acrobat JS API.
     *
     * @see https://www.adobe.com/content/dam/acom/en/devnet/acrobat/pdfs/js_api_reference.pdf
     * @param string $cFormat
     * @param string $value
     * @return string
     */
    public static function printx($cFormat, $value)
    {
        $formatValue = '';
        $case = '=';

        $charClasses = [
            '9' => '\d',
            'A' => 'A-Za-z',
            'X' => '\w',
        ];

        for ($i = 0, $len = SetaPDF_Core_Encoding::strlen($cFormat); $i < $len; $i++) {
            /** @var string $token */
            $token = SetaPDF_Core_Encoding::substr($cFormat, $i, 1);
            switch ($token) {
                case '9':
                case 'A':
                case 'X':
                    $charClass = $charClasses[$token];
                    if (preg_match('/[' . $charClass . ']/u', $value, $m)) {
                        $char = $m[0];
                    } else {
                        return $formatValue;
                    }

                    if ($token === '9') {
                        $formatValue .= $char;
                    } else if ($case === '>') {
                        $formatValue .= mb_strtoupper($char, 'utf-8');
                    } elseif ($case === '<') {
                        $formatValue .= mb_strtolower($char, 'utf-8');
                    } else {
                        $formatValue .= $char;
                    }

                    $value = preg_replace('/^[^' . $charClass . ']*[' . $charClass . ']/u', '', $value);
                    break;
                case '?':
                    $char = SetaPDF_Core_Encoding::substr($value, 0, 1);
                    $value = SetaPDF_Core_Encoding::substr($value, 1);
                    if ($case === '>') {
                        $formatValue .= mb_strtoupper($char, 'utf-8');
                    } elseif ($case === '<') {
                        $formatValue .= mb_strtolower($char, 'utf-8');
                    } else {
                        $formatValue .= $char;
                    }
                    break;
                case '*';
                    $formatValue .= $value;
                    return $formatValue;
                case '>':
                case '<':
                case '=':
                    $case = SetaPDF_Core_Encoding::substr($cFormat, $i, 1);
                    break;
                case '\\':
                    $i++;
                    if ($i > $len) {
                        break;
                    }
                    $formatValue .= SetaPDF_Core_Encoding::substr($cFormat, $i, 1);
                    break;
                default:
                    $formatValue .= SetaPDF_Core_Encoding::substr($cFormat, $i, 1);
            }
        }

        return $formatValue;
    }
}
