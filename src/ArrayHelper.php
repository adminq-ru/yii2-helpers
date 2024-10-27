<?php
namespace adminq\yii\helpers;

use yii\helpers\BaseArrayHelper;
use yii\helpers\VarDumper;

class ArrayHelper extends BaseArrayHelper
{
    /**
     * Вернет значения только указанного ключа исходного массива
     * @param $array
     * @param $key
     * @return array
     */
    public static function mapKeys($array, $key)
    {
        return array_keys(static::map($array, $key, $key));
    }

    /**
     * Делает одномерный массив из любого массива
     * @param $array
     * @param array $result
     * @return array
     */
    public static function flat($array, & $result = [])
    {
        foreach ($array as $item) {
            if (is_array($item)) {
                self::flat($item, $result);
            } else {
                $result[] = $item;
            }
        }
        return $result;
    }

    /**
     * Объединяет массивы (обновляет значения первого - из второго, если они НЕ NULL)
     * @param $a - например, ['one' => 1, 'two' => 1]
     * @param $b - например, ['one' => null, 'two' => 2]
     * @return array - вернет ['one' => 1, 'two' => 2]
     */
    public static function union($a, $b)
    {
        $a = array_filter($a);
        $b = array_filter($b);
        return static::merge($a, $b);
    }

    /**
     * Вернет массив, где значения, если они не являются скалярными,
     * преобразовываются в оформатированные отладочные строки
     * (используется для выводя массивов данных с отладочной информацией)
     * @param $array
     * @return array
     */
    public static function toAssocScalar($array)
    {
        $items = [];
        foreach ($array as $key => $value) {
            $value = (!is_scalar($value)) ? VarDumper::dumpAsString($value, 10, true) : $value;
            $items[ $key ] = $value;
        }
        return $items;
    }

    /**
     * Если передан массив с одними значениями, будет создан массив, где ключи будут значениями
     * например, если в $keys передан массив ['one', 'two'], то вернет
     *  ['one' => 'one', 'two' => 'two']
     * @param $keys
     * @param null $values
     * @return mixed|null
     */
    public static function combine($keys, $values = null)
    {
        if ($values === null) {
            $values = $keys;
        }
        return array_combine($keys, $values);
    }

    /**
     * Делает из плоского массива key => value массив подходящий для виджета \kartik\depdrop\DepDrop
     * @param $array
     * @return array - массив вида
     * [
     *  ['id' => key, 'name' => value],
     *  ...
     * ]
     */
    public static function depDrop($array)
    {
        $items = [];
        foreach ($array as $key => $value) {
            $items[] = [
                'id' => $key,
                'name' => $value,
            ];
        }
        return $items;
    }

    /**
     * Вернет часть массива указанной длины (именно такой, даже если сдвиг не дает нужной длины)
     * Если массив [1,2,3,4,5,6], то subArrayLength($array, 3, 5) - вернет в отличии от array_slice
     * все равно 3 элемента [4, 5, 6], хотя по сдвигу 5 остается только [6]
     * @param $array
     * @param $length
     * @param int $offset
     * @return array
     */
    public static function subArrayLength($array, $length, $offset = 0)
    {
        if ($offset < 0) {
            $offset = 0;
        }
        $chain = array_slice($array, $offset, $length);
        if (count($chain) != $length) {
            $chain = array_slice($array, count($array)-$length, $length);
        }
        return $chain;
    }
}
