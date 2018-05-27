<?php

namespace Eschmar\TableDictionary;

use Illuminate\Support\Facades\Storage;

/**
 * Service for easy TableDictionary interaction.
 */
class TableDictionaryService
{
    const OUTPUT_FOLDER = "table_dictionary/";

    /**
     * Returns the expected filename for a table.
     *
     * @param string $table
     * @return string
     */
    public static function getFileName(string $table)
    {
        return $table . ".txt";
    }

    /**
     * Returns cached dictionary if possible or new one.
     *
     * @param string $table
     * @param boolean $checkCache
     * @return TableDictionary
     */
    public static function getDictionary(string $table, bool $checkCache = true): TableDictionary
    {
        $dict = new TableDictionary($table);

        if (!Storage::disk('local')->has(self::getFileName($table))) {
            return $dict;
        }

        $dict->unserialize(Storage::disk('local')->get(self::getFileName($table)));
        return $dict;
    }

    /**
     * Writes dictionary to disk for later use.
     *
     * @param TableDictionary $dict
     * @return void
     */
    public static function writeDictionary(TableDictionary $dict)
    {
        Storage::disk('local')->makeDirectory(self::OUTPUT_FOLDER);
        Storage::disk('local')->put(
            self::getFileName($dict->getTable()),
            $dict->serialize()
        );
    }
}
