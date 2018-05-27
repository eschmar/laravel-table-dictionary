# Laravel Table Dictionary

Introduces the `TableDictionary` data structure, which is capable of extracting a statistical distribution of your database column contents. This distribution can be used to generate more of the same content, utilizing a lottery ticket system. Exposes the `TableDictionaryService` with static functions to generate the `TableDictionary` data structure for your current database tables and importing them within another environment. 

## Usage
```php
// Sample usage
$dict = new TableDictionary("table_name");
$dict->generate("attribute_name");

// Persist dictionary
TableDictionaryService::writeDictionary($dict);

// Retrieve dictionary
TableDictionaryService::writeDictionary($dict);
$dict = TableDictionaryService::getDictionary("table_name");
```
