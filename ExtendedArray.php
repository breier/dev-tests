<?php
/**
 * PHP Version 7
 *
 * Script to test Arrays against ArrayObjects
 *
 * @category CLI_Script
 * @package  BREIER\Tools
 * @author   Andre Breier <breier.de@gmail.com>
 * @license  GPLv3 ./LICENSE
 * @link     none.io
 */

class ExtendedArray extends ArrayIterator
{
    public function __construct(array $array = null)
    {
        if (empty($array)) {
            $array = [];
        }

        parent::__construct($array);
    }

    public function offsetGetFirst()
    {
        foreach($this as $item) {
            return $item;
        }

        return null;
    }

    public function offsetGetLast()
    {
        if (!$this->count()) {
            return null;
        }

        $initialKey = $this->key();

        $this->seek($this->count() - 1);
        $lastItem = $this->current();

        $this->seekKey($initialKey);

        return $lastItem;
    }

    public function seekKey($key)
    {
        if (!$this->offsetExists($key)) {
            throw new Exception("Key '{$key}' doesn't exist!");
        }

        for ($this->rewind(); $this->valid(); $this->next()) {
            if ($this->key() == $key) {
                break;
            }
        }
    }

    public function filter(callable $callback): BreierArray
    {
        $filteredArray = new static();

        foreach($this as $key => $value) {
            if ($callback($value)) {
                $filteredArray->offsetSet($key, $value);
            }
        }

        return $filteredArray;
    }

    public function map(callable $callback): BreierArray
    {
        $mappedArray = new static();

        foreach ($this as $key => $value) {
            $mappedArray->offsetSet($key, $callback($value));
        }

        return $mappedArray;
    }
}

/**
 * Declaring Array
 */
$array = new ExtendedArray(
    [
        'test' => 'one',
        [
            'two' => 1
        ]
    ]
);

/**
 * Small function to simplify Output
 */
function stringify($mixed) {
    return substr(json_encode($mixed), 0, 30) . '...';
}

/**
 * Object Oriented Version
 */
echo 'first item => ' . stringify($array->offsetGetFirst()) . PHP_EOL;
echo 'last item => ' . stringify($array->offsetGetLast()) . PHP_EOL;

$filteredArray = $array->filter(
    function($item) {
        return @count($item) >= 10;
    }
);

echo 'first item => ' . stringify($filteredArray->offsetGetFirst()) . PHP_EOL;
echo 'last item => ' . stringify($filteredArray->offsetGetLast()) . PHP_EOL;

$mappedArray = $array->map(
    function($item) {
        if (!is_array($item)) return false;
        return stringify($item);
    })->filter(
        function($item) {
            return $item !== false;
        }
    );

echo 'first item => ' . stringify($mappedArray->offsetGetFirst()) . PHP_EOL;
echo 'last item => ' . stringify($mappedArray->offsetGetLast()) . PHP_EOL;

/**
 * Structured Version
 */

echo 'first item => ' . stringify(reset($array)) . PHP_EOL;
echo 'last item => ' . stringify(end($array)) . PHP_EOL;

$filtered_array = array_filter(
    $array,
    function($item) {
        return @count($item) >= 10;
    }
);

echo 'first item => ' . stringify(reset($filtered_array)) . PHP_EOL;
echo 'last item => ' . stringify(end($filtered_array)) . PHP_EOL;

$mapped_array = array_filter(
    array_map(
        function($item) {
            if (!is_array($item)) return false;
            return end($item);
        },
        $array
    ),
    function($item) {
        return $item !== false;
    }
);

echo 'first item => ' . stringify(reset($mapped_array)) . PHP_EOL;
echo 'last item => ' . stringify(end($mapped_array)) . PHP_EOL;
