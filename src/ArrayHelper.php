<?php

namespace adminq\helpers;

use yii\helpers\BaseArrayHelper;
use yii\helpers\VarDumper;

class ArrayHelper extends BaseArrayHelper
{
    /**
     * Если передан массив с одними значениями, будет создан массив, где ключи будут значениями
     * например, если в $keys передан массив ['one', 'two'], то вернет
     *  ['one' => 'one', 'two' => 'two']
     *
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
     *
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
     * Делает одномерный массив без сохранения ключей из любого массива
     *
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
     * Дозаполняет массив $array значениями $value до указанного размера $count
     * 
     * @param $array
     * @param $value
     * @param $count
     * @return array
     */
    public static function fill($array, $value, $count)
    {
        if (count($array) >= $count) {
            return $array;
        }

        return array_merge($array, array_fill(0, $count-count($array), $value));
    }
    
    /**
     * Возвращает массив с элементами, распределенными по колонкам
     * Например, если $items = [1,2,3,4,5], $cols = 3, то вернет
     *  [
     *      [1,2],
     *      [3,4],
     *      [5],
     *  ]
     *
     * @param array $items - массив элементов
     * @param int $cols - кол-во колонок
     * @return array
     */
    public static function getItemsByColumns($items, $cols = 3)
    {
        $count = count($items);
        $data = [];
        $offset = 0;
        for ($i=0; $i<$cols; $i++) {
            $length = ceil(($count - $offset) / ($cols - $i));
            $data[] = array_slice($items, $offset, $length);
            $offset += $length;
        }

        return $data;
    }

    /**
     * @param $array
     * @param $group
     * @return array
     */
    public static function groupForSelect($array, $group)
    {
        $arrayGroups = static::index($array, null, $group);

        $result = [];
        foreach (static::index($array, null, $group) as $key => $value) {
            $result[] = [
                'text' => $key,
                'children' => $value,
            ];
        }

        return $result;
    }

    /**
     * Возвращает массив, группируя элементы по указанному полю и выбирая из
     * группировки случайный элемент
     *
     * @param $items
     * @param $group
     * @return array
     */
    public static function groupRand($items, $group)
    {
        $result = [];
        foreach (self::index($items, null, $group) as $groupItems) {
            $randIndex = array_rand($groupItems);
            $result[] = $groupItems[$randIndex];
        }

        return $result;
    }

    /**
     * Делает все тоже самое, что родительский метод index, но с сохранением ключей
     * исходного массива
     *
     * @param $array
     * @param $key
     * @param array $groups
     * @return array
     */
    public static function indexKeys($array, $key, $groups = [])
    {
        $result = [];
        $groups = (array) $groups;

        foreach ($array as $k => $element) {
            $lastArray = &$result;

            foreach ($groups as $group) {
                $value = static::getValue($element, $group);
                if (!array_key_exists($value, $lastArray)) {
                    $lastArray[$value] = [];
                }
                $lastArray = &$lastArray[$value];
            }

            if ($key === null) {
                if (!empty($groups)) {
                    $lastArray[ $k ] = $element;
                }
            } else {
                $value = static::getValue($element, $key);
                if ($value !== null) {
                    if (is_float($value)) {
                        $value = StringHelper::floatToString($value);
                    }
                    $lastArray[$value] = $element;
                }
            }
            unset($lastArray);
        }

        return $result;
    }

    /**
     * Вернет значения только указанного ключа исходного массива
     *
     * @param $array
     * @param $key
     * @return array
     */
    public static function mapKeys($array, $key)
    {
        return array_keys(static::map($array, $key, $key));
    }


    /**
     * @param $array
     * @param $from
     * @param $to
     * @return array
     */
    public static function mapValues($array, $from, $to)
    {
        return array_values(static::map($array, $from, $to));
    }

    /**
     * Возвращает НЕ уникальные элементы массива
     *
     * @param $a
     * @return array
     */
    public static function notUnique($a)
    {
        return array_diff_key($a , array_unique($a));
    }

    /**
     * Вставляет элементы в указанную позицию в массиве
     *
     * @param array $array - исходный массив
     * @param array|mixed $value - элемент(ы), которые нужно вставить
     * @param int $position - позиция, начиная с 1 (начало массива)
     * @return array
     */
    public static function pushPosition($array, $value, $position = 1)
    {
        if (!is_array($value)) {
            $value = [ $value ];
        }

        if (!is_numeric($position) || $position < 1) {
            $position = 1;
        }

        return array_merge(array_slice($array, 0, $position-1), $value, array_slice($array, $position-1));
    }
    
    /**
     * Удаляет пустые значения из массива
     *
     * @param $array
     * @return array
     */
    public static function removeEmpty($array)
    {
        return array_filter($array, function ($item) {
            if (is_array($item)) {
                return array_filter($item);
            } else {
                return $item;
            }
        });
    }

    /**
     * Заменяет часть массива
     * 
     * @param $input
     * @param $offset
     * @param $length
     * @param array $replacement
     * @return array
     */
    public static function spliceAssoc($input, $offset, $length, $replacement = [])
    {
        $replacement = (array) $replacement;
        $key_indices = array_flip(array_keys($input));

        if (isset($input[$offset]) && is_string($offset)) {
            $offset = $key_indices[$offset];
        }

        if (isset($input[$length]) && is_string($length)) {
            $length = $key_indices[$length] - $offset;
        }

        $input = array_slice($input, 0, $offset, TRUE)
            + $replacement
            + array_slice($input, $offset + $length, NULL, TRUE);
        
        return $input;
    }

    /**
     * Вернет часть массива указанной длины (именно такой, даже если сдвиг не дает нужной длины)
     * Если массив [1,2,3,4,5,6], то subArrayLength($array, 3, 5) - вернет в отличии от array_slice
     * все равно 3 элемента [4, 5, 6], хотя по сдвигу 5 остается только [6]
     *
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
    
    /**
     * Вернет массив с суммами значений по ключам, например, на входе:
     * [
     *  [
     *      'one' => 1,
     *      'two' => 2,
     *  ],
     *  [
     *      'one' => 10,
     *      'two' => 20,
     *  ]
     * ]
     * На выходе:
     *  [
     *      'one' => 11,
     *      'two' => 22,
     *  ]
     *
     * @param $items
     * @return mixed
     */
    public static function sumValuesAssoc($items)
    {
        $result = [];

        foreach ($items as $array) {
            foreach ($array as $key => $number) {
                if (!is_numeric($number)) {
                    continue;
                }

                if (!isset($result[$key])) {
                    $result[$key] = 0;
                }

                $result[$key] += $number;
            }
        }

        return $result;
    }

    /**
     * Вернет массив, где значения, если они не являются скалярными,
     * преобразовываются в оформатированные отладочные строки
     * (используется для выводя массивов данных с отладочной информацией)
     *
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
     * @param $array
     * @return mixed
     */
    public static function toObjects($array)
    {
        array_walk($array, function (& $item) {
            $item = (object) $item;
        });

        return $array;
    }
    
    /**
     * Объединяет массивы (обновляет значения первого - из второго, если они НЕ NULL)
     *
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
}
