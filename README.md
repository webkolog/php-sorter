# W-PHP Sorter

[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)

**Version:** 1.0 (Sorter)

**Last Updated:** 2016-09-24

**Compatibility:** PHP 5.4

**Created By:** Ali Candan ([@webkolog](https://github.com/webkolog))

**Website:** [http://webkolog.net](http://webkolog.net)

**Copyright:** (c) 2015 Ali Candan

**License:** MIT License ([http://mit-license.org](http://mit-license.org))

**W-PHP Sorter** is a PHP class designed to simplify the sorting of database records. It provides easy-to-use methods for moving, exchanging, and deleting records while maintaining the correct order.

## Features

* **Move to First/Last:** Move a record to the beginning or end of the list.
* **Move Up/Down:** Move a record up or down by one position.
* **Move After:** Move a record after a specified target record.
* **Exchange:** Exchange the positions of two records.
* **Delete:** Delete a record and reorder the remaining records.
* **Get Free List Number:** Retrieve the next available order number.
* **Multi-language Support:** Supports multiple languages for error messages.
* **Filtering:** Allows filtering of records based on specified conditions.

## Compatibility and Requirements

* PHP 5.4+
* PDO

## Installation

1.  Copy the `sorter.php` file and the `language` folder into your project directory.
2.  Include the `sorter.php` file in your PHP script.

## Usage

```php
<?php
include 'sorter.php';

// Example: Assuming $db is your PDO database connection
$filter = array("top_id" => 5, "approval" => 1, "deleted" => 0);

$sorter = new Sorter($db, "categories", "order_no", $filter, array(5, 1, 0), 'en'); // 'en' for english language
//alternative usage
// $sorter = new Sorter($db);
// $sorter->tableName = "categories";
// $sorter->listRow = "order_no";
// $sorter->filter = $filter;
// $sorter->filterValues = array(5,1,0);
// $sorter->setLanguage('en');

$sorter->moveAfter(9, 6); // Move record with ID 9 after record with ID 6
$sorter->moveUp(9); // Move record with ID 9 up
$sorter->moveDown(9); // Move record with ID 9 down
$sorter->moveToFirst(8); // Move record with ID 8 to the first position
$sorter->moveToLast(8); // Move record with ID 8 to the last position
$sorter->exchange(9, 16); // Exchange positions of records with IDs 9 and 16
$sorter->delete(9); // Delete record with ID 9 and reorder the list
$freeListNo = $sorter->getFreeListNo(); // Get the next available order number
$errorMessage = $sorter->getErrorMessage(); // Get error message

//change language example
$sorter->setLanguage('tr');
$errorMessage = $sorter->getErrorMessage(); // Get error message in turkish
?>
```

## Methods
- `__construct($db, $tableName, $listRow, $filter, $filterValues, $language):` Initializes the Sorter object.
- `$db:` PDO database connection.
  - `$tableName:` Name of the database table.
  - `$listRow:` Name of the order column.
  - `$filter:` Filter conditions (array).
  - `$filterValues:` Filter values (array).
  - `$language:` Language to use for error messages.
- `setLanguage($language):` Sets the language for error messages.
- `moveToFirst($data_id):` Moves the record with the given ID to the first position.
- `moveToLast($data_id):` Moves the record with the given ID to the last position.
- `moveAfter($data_id, $target_id):` Moves the record with `$data_id` after the record with $target_id.
- `moveUp($data_id):` Moves the record with the given ID up by one position.
- `moveDown($data_id):` Moves the record with the given ID down by one position.
- `exchange($data_id, $target_id):` Exchanges the positions of the records with the given IDs.
- `delete($data_id):` Deletes the record with the given ID and reorders the list.
- `getFreeListNo():` Returns the next available order number.
- `getErrorMessage():` Returns the last error message.

## License
This W-PHP Sorter class is open-source software licensed under the [MIT license](https://mit-license.org/).
```
MIT License

Copyright (c) 2015 Ali Candan

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
```

## Contributing
Contributions are welcome! If you find any bugs or have suggestions for improvements, please `feel free to open an issue or submit a pull request on the GitHub repository.`

## Support
For any questions or support regarding the W-PHP Sorter, you can refer to the project's GitHub repository or contact the author.

