<?php
/**
 * PHP Version 7
 *
 * Extended Array Class to improve array handling
 *
 * @category Extended_Class
 * @package  Breier\Model
 * @author   Andre Breier <breier.de@gmail.com>
 * @license  GPLv3 ./LICENSE
 * @link     none.io
 */

namespace Breier\Model;

use \SplFixedArray;
use \ArrayIterator;
use \ArrayObject;
use \Exception;

/**
 * ArrayIterator Class Entities
 *
 * @property int STD_PROP_LIST  = 1; Properties of the object have their normal functionality when accessed as list
 * @property int ARRAY_AS_PROPS = 2; Entries can be accessed as properties (read and write)
 *
 * @method null   append(mixed $value) ................. Append an element to the object
 * @method null   asort() .............................. Sort ascending by elements
 * @method int    count() .............................. The amount of elements
 * @method array  getArrayCopy() ....................... Back to good and old array
 * @method int    getFlags() ........................... Get behaviour flags of the ArrayIterator
 * @method mixed  key() ................................ Current position element index
 * @method null   ksort() .............................. Sort ascending by element indexes
 * @method null   natcasesort() ........................ Sort elements using case insensitive "natural order"
 * @method null   natsort() ............................ Sort elements using "natural order"
 * @method bool   offsetExists(mixed $index) ........... Validate element index
 * @method null   offsetSet(mixed $index, mixed $newval) Append an element with index name
 * @method null   offsetUnset(mixed $index) ............ Remove an element
 * @method string serialize() .......................... Applies PHP serialization to the object
 * @method null   setFlags(string $flags) .............. Set behaviour flags of the ArrayIterator
 * @method null   uasort(callable $cmp_function) ....... Sort by elements using given function
 * @method null   uksort(callable $cmp_function) ....... Sort by indexes using given function
 * @method null   unserialize(string $serialized) ...... Populates the object with using PHP unserialization
 * @method bool   valid() .............................. Validate element in the current position
 *
 * Extended Array Class
 *
 * @category Extended_Class
 * @package  Breier\Model
 * @author   Andre Breier <breier.de@gmail.com>
 * @license  GPLv3 ./LICENSE
 * @link     none.io
 */
class ExtendedArray extends ArrayIterator
{
    private $_positionMap;
    private $_lastCursorKey;

    /**
     * Instantiate an Extended Array
     *
     * @param array $array To be parsed into properties
     * @param int   $flags (STD_PROP_LIST | ARRAY_AS_PROPS)
     */
    public function __construct($array = null, int $flags = 2)
    {
        if ($array instanceof ArrayIterator || $array instanceof ArrayObject) {
            $array = $array->getArrayCopy();
        }

        if ($array instanceof SplFixedArray) {
            $array = $array->toArray();
        }

        if (empty($array)) {
            $array = [];
        }

        parent::__construct($array, $flags);

        $this->_updatePositionMap();
    }

    /**
     * Reverse Sort by element, polyfill for `arsort`
     *
     * @return void
     */
    public function arsort(): void
    {
        $this->uasort(
            function ($a, $b) {
                return $b <=> $a;
            }
        );

        $this->_updatePositionMap();
    }

    /**
     * Contains polyfill for `in_array`
     *
     * @param mixed $needle To search for
     * @param bool  $strict Hard or soft comparison
     *
     * @return bool
     */
    public function contains($needle, $strict = false): bool
    {
        $compare = $strict
            ? function ($a, $b) {
                return $a === $b;
            }
            : function ($a, $b) {
                return (object) $a == (object) $b;
            };

        $isContained = false;

        $this->_saveCursor();

        foreach ($this as $element) {
            if ($compare($element, $needle)) {
                $isContained = true;
                break;
            }
        }

        $this->_restoreCursor();

        return $isContained;
    }

    /**
     * Extending Current Method to return ExtendedArray instead of array
     *
     * @return mixed
     */
    public function current()
    {
        $item = parent::current();

        return is_array($item)
            ? new static($item)
            : $item;
    }

    /**
     * Element is an alias for Current
     *
     * @return mixed
     */
    public function element()
    {
        return $this->current();
    }

    /**
     * Move the Cursor to the End, polyfill for `end`
     *
     * @return ExtendedArray
     */
    public function end(): ExtendedArray
    {
        if ($this->count()) {
            $this->seek($this->count() -1);
        }

        return $this;
    }

    /**
     * Filter polyfill for `array_filter`
     *
     * @param callable $callback Function to use
     *
     * @return ExtendedArray
     */
    public function filter(callable $callback = null): ExtendedArray
    {
        if (is_null($callback)) {
            $callback = function ($item) {
                return !empty($item);
            };
        }

        $this->_saveCursor();

        $filteredArray = new static();

        foreach ($this as $key => $value) {
            if ($callback($value)) {
                $filteredArray->offsetSet($key, $value);
            }
        }

        $this->_restoreCursor();

        return $filteredArray;
    }

    /**
     * First is an alias for Rewind
     *
     * @return ExtendedArray
     */
    public function first(): ExtendedArray
    {
        return $this->rewind();
    }

    /**
     * ExtendedArray from JSON
     *
     * @param string $json    To parse
     * @param int    $depth   Recursion level
     * @param int    $options (JSON_THROW_ON_ERROR | JSON_BIGINT_AS_STRING | ...)
     *
     * @return ExtendedArray
     */
    public static function fromJSON(string $json, int $depth = 512, int $options = 0): ExtendedArray
    {
        return new static(
            json_decode($json, true, $depth, $options)
        );
    }

    /**
     * Is Array static function, extends `is_array`
     *
     * @param array|ExtendedArray $element To be validated
     *
     * @return bool
     */
    public static function isArray($element): bool
    {
        return (
            is_array($element)
            || $element instanceof ArrayObject
            || $element instanceof ArrayIterator
            || $element instanceof SplFixedArray
        );
    }

    /**
     * JSON Serialize
     *
     * @param int $options (JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | ...)
     * @param int $depth   Recursion level
     *
     * @return string
     */
    public function JsonSerialize(int $options = 0, $depth = 512): string
    {
        return json_encode($this, $options, $depth);
    }

    /**
     * Extended Array Keys, polyfill for `array_keys`
     *
     * @return ExtendedArray
     */
    public function keys(): ExtendedArray
    {
        $this->_saveCursor();

        $keysArray = new static();

        foreach ($this as $key => $value) {
            $keysArray->append($key);
        }

        $this->_restoreCursor();

        return $keysArray;
    }

    /**
     * Reverse Sort by index, polyfill for `krsort`
     *
     * @return void
     */
    public function krsort(): void
    {
        $this->uksort(
            function ($a, $b) {
                if (is_numeric($b) ^ is_numeric($a)) {
                    return is_numeric($b) <=> is_numeric($a);
                }

                return $b <=> $a;
            }
        );

        $this->_updatePositionMap();
    }

    /**
     * Last is an alias to End
     *
     * @return ExtendedArray
     */
    public function last(): ExtendedArray
    {
        return $this->end();
    }

    /**
     * Map polyfill for `array_map`
     *
     * @param callable $callback Function to use
     *
     * @return ExtendedArray
     */
    public function map(callable $callback): ExtendedArray
    {
        $this->_saveCursor();

        $mappedArray = new static();

        foreach ($this as $key => $value) {
            $mappedArray->offsetSet($key, $callback($value));
        }

        $this->_restoreCursor();

        return $mappedArray;
    }

    /**
     * Extending next Method to Return ExtendedArray instead of void
     *
     * @return ExtendedArray
     */
    public function next(): ExtendedArray
    {
        parent::next();

        return $this;
    }

    /**
     * Extending OffsetGet Method to Return ExtendedArray instead of array
     *
     * @param int|string $key Property to Get
     *
     * @return mixed
     */
    public function offsetGet($key)
    {
        $item = parent::offsetGet($key);

        return is_array($item)
            ? new static($item)
            : $item;
    }

    /**
     * Offset Get First
     *
     * @return mixed
     */
    public function offsetGetFirst()
    {
        $this->_saveCursor();

        $firstItem = $this->first()->element();

        $this->_restoreCursor();

        return is_array($firstItem)
            ? new static($firstItem)
            : $firstItem;
    }

    /**
     * Offset Get Last
     *
     * @return mixed
     */
    public function offsetGetLast()
    {
        $this->_saveCursor();

        $lastItem = $this->last()->element();

        $this->_restoreCursor();

        return $lastItem;
    }

    /**
     * Offset Get by given Position
     *
     * @param int $position To seek
     *
     * @return mixed
     */
    public function offsetGetPosition(int $position)
    {
        $this->_saveCursor();

        $item = $this->seek($position)->element();

        $this->_restoreCursor();

        return $item;
    }

    /**
     * Current Position, polyfill for `pos` of SplFixedArray
     *
     * @return int
     */
    public function pos(): int
    {
        return array_search(
            $this->key(),
            $this->_positionMap,
            true
        );
    }

    /**
     * Move the Cursor to Previous element
     *
     * @return ExtendedArray
     */
    public function prev(): ExtendedArray
    {
        $currentPosition = $this->pos();

        if (!$currentPosition) {
            return $this->end()->next();
        }

        return $this->seek($currentPosition - 1);
    }

    /**
     * Move the cursor to initial position
     *
     * @return ExtendedArray
     */
    public function rewind(): ExtendedArray
    {
        parent::rewind();

        return $this;
    }

    /**
     * Extending seek Method to Return ExtendedArray instead of void
     *
     * @param int $position To seek
     *
     * @return ExtendedArray
     */
    public function seek($position): ExtendedArray
    {
        parent::seek($position);

        return $this;
    }

    /**
     * Seek Key moves the pointer to given key
     *
     * @param int|string $key Property to seek
     *
     * @return ExtendedArray
     * @throws Exception
     */
    public function seekKey($key): ExtendedArray
    {
        if (!$this->offsetExists($key)) {
            throw new Exception("Key '{$key}' doesn't exist!");
        }

        $keyPosition = array_search(
            $key,
            $this->_positionMap,
            true
        );

        return $this->seek($keyPosition);
    }

    /**
     * Shuffle Elements Randomly, polyfill for `shuffle`
     *
     * @return void
     */
    public function shuffle(): void
    {
        $this->uasort(
            function ($a, $b) {
                return rand(-1, 1);
            }
        );

        $this->_updatePositionMap();
    }

    /**
     * Update Position Map
     *
     * @return null
     */
    private function _updatePositionMap()
    {
        $this->_positionMap = $this->keys()->getArrayCopy();
    }

    /**
     * Save Current Cursor Position so it can be restored
     *
     * @return void
     */
    private function _saveCursor(): void
    {
        $this->_lastCursorKey = $this->key();
    }

    /**
     * Restore Cursor Position
     *
     * @return void
     */
    private function _restoreCursor(): void
    {
        if (!is_null($this->_lastCursorKey)) {
            $this->seekKey($this->_lastCursorKey);
        }
    }
}
