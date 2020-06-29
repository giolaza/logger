# Logger by GioLaza

Logger for engine

## Installation

```bash
composer require giolaza/logger
```

## Configuration

```php
define('GIOLAZA_SHOW_ERRORS', false, 1);
define('GIOLAZA_SAVE_ERRORS', true, 1);
define('GIOLAZA_LOGS_FOLDER', __DIR__ . '/../___productionLogs', 1);
```

Constant `GIOLAZA_SHOW_ERRORS` defines display detail information about error or not. if value is `false` engine will print `something went wrong`.


Constant `GIOLAZA_SAVE_ERRORS` defines save detail information about error in files or not.


Constant `GIOLAZA_LOGS_FOLDER` defines log folder link. It's recommended to use folder outside root folder or add `.htaccess` to restrict direct access from `www`

## Usage
```php
GioLaza\Logger\Log::logError(text: 'ANY TEXT',filename: 'filename.log', engineForceStop: true, dispalyErrors: true)
```
`engineForceStop` - means need or not use `die`
`dispalyErrors` - is method variable, if constant allows show errors, method will print it.

```php
GioLaza\Logger\Log::logError(text: 'DB connection error ',filename: 'db.log')
```
This code will print your text,save text to file and force stop php execution

```php
GioLaza\Logger\Log::logError(text: 'DB connection error ',filename: 'db.log',engineForceStop: true, dispalyErrors: false)
```
This code will NOT print your text,save text in file and force stop php execution


```php
GioLaza\Logger\Log::logError(text: 'User id: 12 ',filename: 'db.log',engineForceStop: true, dispalyErrors: false)
```
This code will NOT print your text or force stop php execution, just save text in file

## License
[MIT](https://choosealicense.com/licenses/mit/)