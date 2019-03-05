AEngine Database
====
It allows you connect the database to a project using a wrapper around the PDO.

#### Requirements
* PHP >= 7.0

#### Installation
Run the following command in the root directory of your web project:
  
> `composer require aengine/database`

#### Usage

Add params in `src/settings.php` file
```php
return [
    'settings' => [
        'displayErrorDetails' => true, // set to false in production
        'addContentLengthHeader' => false, // Allow the web server to send the content-length header

        // Database settings
        'database' => [
            [
                'dsn' => 'mysql:host=HOST;dbname=DB_NAME',
                'username' => 'DB_USER',
                'password' => 'DB_PASS',
                // additional can be passed options, server-role and pool name:
                // 'option'     => [
                //     PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'UTF8'",
                //     PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                // ],
                // 'role'       => 'master', // or slave
                // 'pool_name'  => 'default', // pool list of connections
            ],
            // possible another connection config
            // for the implementation of master-slave
        ], ...
```

Add function in DI by edit `src/dependencies.php` file
```php
$db = new AEngine\Database\Db([
    [
        'dsn' => 'mysql:host=HOST;dbname=DB_NAME',
        'username' => 'DB_USER',
        'password' => 'DB_PASS',
        // additional can be passed options, server-role and pool name:
        // 'option'     => [
        //     PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'UTF8'",
        //     PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        // ],
        // 'role'       => 'master', // or slave
        // 'pool_name'  => 'default', // pool list of connections
    ],
    // possible another connection config
    // for the implementation of master-slave
]);
```

Query execution
```php
$stm = $db->query('SELECT * FROM `user` WHERE `age` > 23');

while ($a = $stm->fetch(PDO::FETCH_ASSOC)) {
    // some action
    var_dump($a);
}
```

#### Aliases

Select rows
```php 
$list = $db->select('SELECT * FROM `products` WHERE `price` >= 150');
```

Select first element of array from `select` method
```php 
$first = $db->selectOne('SELECT * FROM `products` WHERE `price` >= 150');
```

Affect row and return count of affected
```php 
$affected = $db->affect('INSERT INTO `products` SET `name` = "Socks with owls", `price` = 200');
```

#### Contributing
Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

#### License
The Orchid Database is licensed under the MIT license. See [License File](LICENSE.md) for more information.
