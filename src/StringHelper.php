<?php

namespace adminq\helpers;

class StringHelper extends \yii\helpers\StringHelper
{
    /**
     * Кодирует данные в JSON а потом в Base64
     *
     * @param $input
     * @return string
     */
    public static function base64JsonEncode($input)
    {
        return parent::base64UrlEncode(Json::encode($input));
    }

    /**
     * Декодирует строку из Base64 а потом из JSON в массив данных
     *
     * @param $input
     * @return mixed|null
     */
    public static function base64JsonDecode($input)
    {
        return Json::decode(parent::base64UrlDecode($input));
    }
    
    /**
     * Заменяет в тексте коды вставки вида {name} значениями массива (или объекта)
     * с ключом "name"
     *
     * @param $text - текст, где будет производиться замена
     * @param $array - массив (объект), ключи (свойства) которого будут использоваться для замены
     * @return string|string[]|null
     * @throws \Exception
     */
    public static function parse($text, $array)
    {
        return preg_replace_callback("`\{(.*)\}`Usmi", function ($match) use ($array) {
            $key = trim($match[1]);
            return ArrayHelper::getValue($array, $key);
        }, $text);
    }

    /**
     * Делает из строки массив
     *
     * @param string $text
     * @param array $separators
     * @return array
     */
    public static function textList($text, array $separators = [])
    {
        $separators = array_merge([PHP_EOL, "\n", "\r", "\n\r"], $separators);

        return self::explode(str_replace($separators, '§', $text), '§', true, true);
    }

    /**
     * Преобразует текст вида "key1::value1
     * key2::value2" в массив параметров
     *  ['key1' => 'value1', 'key2' => 'value2']
     *
     * @param $text
     * @param null $keys - массив ключей для создания ассоциативных массивов из каждой строки
     * @param array $options
     *  -dl - разделитель, по умолчанию "::"
     *  -asObj - делать ли итоговый массив как объект
     * @return array
     */
    public static function textToParams($text, $keys = null, $options = [])
    {
        if (!$lines = array_map('trim', self::textList($text))) {
            return [];
        }

        $dl = ArrayHelper::remove($options, 'dl', '::');
        $asObj = ArrayHelper::remove($options, 'asObj', false);

        $params = [];
        $isAssoc = true; // в итоге получится ассоциативный массив

        foreach ($lines as $line) {
            if (empty($line)) {
                continue;
            }

            try {
                $parts = explode($dl, $line);
            } catch (\Exception $e) {
                return [];
            }

            if (count($parts) == 2 && $keys === null) {
                if (empty($parts[0]) || empty($parts[1])) {
                    continue;
                }

                $params[ $parts[0] ] = $parts[1];
            } else {
                $array = ($keys === null) ? $parts : ArrayHelper::combine($keys, $parts);
                $params[] = ($asObj) ? (object) $array : $array;
                $isAssoc = false;
            }
        }

        if ($asObj && $isAssoc) {
            $params = (object) $params;
        }

        return $params;
    }
}
