<?php

namespace Eschmar\TableDictionary;

use Illuminate\Support\Facades\DB;

/**
 * Dictionary of an individual database table.
 */
class TableDictionary implements \Serializable
{
    /**
     * Table name
     *
     * @var string
     */
    protected $table;
    
    /**
     * Dictionary
     *
     * @var array
     */
    protected $dictionary = [];

    /**
     * Constructor.
     *
     * @param string $table
     */
    public function __construct(string $table)
    {
        $this->table = $table;
    }

    /**
     * Returns the table name.
     *
     * @return string
     */
    public function getTable(): string
    {
        return $this->table;
    }

    /**
     * Bulk generate dictionary entries.
     *
     * @param array $attributes
     * @param array $with
     * @return void
     * @throws \InvalidArgumentException Require attributes to be strings.
     */
    public function bulkGenerate(array $attributes, array $with = [])
    {
        foreach ($attributes as $attribute) {
            if (!is_string($attribute)) {
                throw new \InvalidArgumentException("Attributes need to be string names.");
            }

            $this->generate($attribute, [], $with);
        }
    }

    /**
     * Generates a dictionary entry for the given attribute.
     *
     * @param string $attribute
     * @param array $possibleValues
     * @param array $with
     * @return void
     */
    public function generate(string $attribute, array $possibleValues = [], array $with = [])
    {
        $query = "SELECT {$attribute}, COUNT({$attribute}) AS count FROM {$this->table} ";
        $query .= $this->parseWhere($with);
        $query .= " GROUP BY {$attribute} ORDER BY count DESC";

        $count = 0;
        $values = DB::select($query);
        foreach ($values as $val) {
            $count += $val->count;
        }

        $this->dictionary[$attribute] = [
            'sql' => $query,
            'count' => $count,
            'values' => $values
        ];
    }
    
    /**
     * Generates sql WHERE term for query usage.
     *
     * @param array $with
     * @return string
     */
    public function parseWhere(array $with = []): string
    {
        if (count($with) < 1) {
            return "WHERE 1";
        }

        $term = "WHERE ";
        foreach ($with as $key => $value) {
            if (is_string($value)) {
                $term .= "{$key} = \"{$value}\" ";
                continue;
            }

            $term .= "{$key} = {$value} ";
        }

        return trim($term);
    }

    /**
     * Checks whether the dictionary knows about an attribute.
     *
     * @param string $attribute
     * @return boolean
     */
    public function hasEntry(string $attribute): bool
    {
        return isset($this->dictionary[$attribute]);
    }

    /**
     * Returns the dictionary entry for an attribute when existent.
     *
     * @param string $attribute
     * @return array
     */
    public function getEntry(string $attribute): array
    {
        if (!$this->hasEntry($attribute)) {
            return null;
        }

        return $this->dictionary[$attribute];
    }

    /**
     * Returns a random value according to the distribution determined by the dictionary generation.
     *
     * @param string $attribute
     * @return mixed
     * @throws \UnexpectedValueException Only allow known attributes
     */
    public function getValueForAttribute(string $attribute)
    {
        if (!$this->hasEntry($attribute)) {
            throw new \UnexpectedValueException("Dictionary does not know of this attribute, yet.");
        }

        $entry = $this->getEntry($attribute);

        $lottery = rand(1, $entry["count"]);
        foreach ($entry['values'] as $item) {
            if ($lottery > $item->count) {
                $lottery -= $item->count;
                continue;
            }

            return $item->{$attribute};
        }
    }

    /**
     * Serialize dictionary for later usage.
     *
     * @return mixed
     */
    public function serialize()
    {
        return serialize($this->dictionary);
    }

    /**
     * Recover dictionary from serialized data.
     *
     * @param mixed $data
     * @return void
     */
    public function unserialize($data)
    {
        $this->dictionary = unserialize($data);
    }
}
