<?php

namespace AEngine\Database;

use AEngine\Database\Exception\DatabaseException;
use PDO;
use PDOException;
use PDOStatement;
use RuntimeException;
use UnexpectedValueException;

class Db
{
    /**
     * Array of connections
     *
     * @var array
     */
    protected $connection = [];

    /**
     * @var PDO
     */
    protected $lastConnection;

    /**
     * @var PDOStatement
     */
    public $lastQuery;

    /**
     * Setup the Database
     *
     * @param array $configs
     *
     * @throws RuntimeException
     */
    public function __construct(array $configs = [])
    {
        $default = [
            'dsn' => '',
            'username' => '',
            'password' => '',
            'options' => [],
            'role' => 'master',
            'pool_name' => 'default',
        ];

        foreach ($configs as $index => $config) {
            $config = array_merge($default, $config);
            $this->connection[$config['pool_name']][$config['role'] == 'master' ? 'master' : 'slave'][] = function () use ($config) {
                try {
                    return new PDO(
                        $config['dsn'],
                        $config['username'],
                        $config['password'],
                        $config['options']
                    );
                } catch (PDOException $ex) {
                    throw new DatabaseException(
                        'The connection to the database server fails (' . $ex->getMessage() . ')',
                        0,
                        $ex
                    );
                }
            };
        }
    }

    /**
     * Returns PDO object
     *
     * @param bool   $use_master
     * @param string $pool_name
     *
     * @return PDO
     * @throws UnexpectedValueException
     */
    public function getConnection($use_master = false, $pool_name = 'default')
    {
        $pool = [];
        $role = $use_master ? 'master' : 'slave';

        switch (true) {
            case !empty($this->connection[$pool_name][$role]):
                $pool = $this->connection[$pool_name][$role];
                break;
            case !empty($this->connection[$pool_name]['master']):
                $pool = $this->connection[$pool_name]['master'];
                $role = 'master';
                break;
            case !empty($this->connection[$pool_name]['slave']):
                $pool = $this->connection[$pool_name]['slave'];
                $role = 'slave';
                break;
        }

        if ($pool) {
            if (is_array($pool)) {
                return $this->connection[$pool_name][$role] = $pool[array_rand($pool)]();
            } else {
                /** @var PDO $pool */
                return $pool;
            }
        }

        throw new UnexpectedValueException('Unable to establish connection for current pool');
    }

    /**
     * Prepares and executes a database query
     *
     * @param string $query
     * @param array  $params
     * @param bool   $use_master
     * @param string $pool_name
     *
     * @return PDOStatement
     */
    public function query(String $query, array $params = [], $use_master = false, $pool_name = 'default')
    {
        $this->lastConnection = static::getConnection($use_master, $pool_name); // obtain connection
        $this->lastQuery = $this->lastConnection->prepare($query);
        $this->lastQuery->execute($params);

        return $this->lastQuery;
    }

    /**
     * Executing a select query and returning rows
     *
     * @param string $query
     * @param array  $params
     * @param string $pool_name
     * @param int    $fetch_mode
     *
     * @return array
     */
    public function select($query, array $params = [], $pool_name = 'default', $fetch_mode = PDO::FETCH_ASSOC)
    {
        return static::query($query, $params, false, $pool_name)->fetchAll($fetch_mode);
    }

    /**
     * Executing a select query and returning a single line
     *
     * @param string $query
     * @param array  $params
     * @param string $pool_name
     * @param int    $fetch_mode
     *
     * @return array
     */
    public function selectOne($query, array $params = [], $pool_name = 'default', $fetch_mode = PDO::FETCH_ASSOC)
    {
        $records = static::select($query, $params, $pool_name, $fetch_mode);

        return array_shift($records);
    }

    /**
     * Execute the query and return the number of affected rows
     *
     * @param string $query
     * @param array  $params
     * @param string $pool_name
     *
     * @return int
     */
    public function affect($query, array $params = [], $pool_name = 'default')
    {
        $sth = static::query($query, $params, true, $pool_name);

        return $sth->rowCount();
    }

    /**
     * Returns the ID of the last inserted row
     *
     * @return string
     */
    public function lastInsertId()
    {
        return $this->lastConnection->lastInsertId();
    }
}
