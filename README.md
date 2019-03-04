AEngine Slim Database
====
It allows you connect the database to a project using a wrapper around the PDO.

#### Requirements
* PHP >= 7.0

#### Installation
Run the following command in the root directory of your web project:
  
> `composer require aengine/slim-database`

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
$container = $app->getContainer();

// register database plugin
$container['database'] = function ($c) {
    $settings = $c->get('settings')['database'];

    $db = new AEngine\Slim\Database\Db($settings);

    return $db;
};
```

Query execution
```php
$app->get('/example-route', function ($request, $response, $args) {
    $stm = $this->database->query('SELECT * FROM `user` WHERE `age` > 23');
    
    while ($a = $stm->fetch(PDO::FETCH_ASSOC)) {
        // some action
        var_dump($a);
    }
});
```

#### Aliases

Select rows
```php 
$list = $this->database->select('SELECT * FROM `products` WHERE `price` >= 150');
```

Select first element of array from `select` method
```php 
$first = $this->database->selectOne('SELECT * FROM `products` WHERE `price` >= 150');
```

Affect row and return count of affected
```php 
$affected = $this->database->affect('INSERT INTO `products` SET `name` = "Socks with owls", `price` = 200');
```

#### Contributing
Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

#### License
The Orchid Database is licensed under the MIT license. See [License File](LICENSE.md) for more information.
