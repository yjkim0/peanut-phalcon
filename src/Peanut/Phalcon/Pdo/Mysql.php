<?php

namespace Peanut\Phalcon\Pdo;

class Mysql extends \Phalcon\Db\Adapter\Pdo\Mysql
{

    public static $instance;

    public function connect($descriptor = NULL)
    {
        if ($descriptor === null)
        {
            $descriptor = $this->_descriptor;
        }

        if (true === isset($descriptor['username']))
        {
            $username = $descriptor['username'];
            unset($descriptor['username']);
        }
        else
        {
            $username = null;
        }

        if (true === isset($descriptor['password']))
        {
            $password = $descriptor['password'];
            unset($descriptor['password']);
        }
        else
        {
            $password = null;
        }

        if (true === isset($descriptor['options']))
        {
            $options = $descriptor['options'];
            unset($descriptor['options']);
        }
        else
        {
            $options = [];
        }

        if (true === isset($descriptor['persistent']))
        {
            if ($descriptor['persistent'])
            {
                $options[\Pdo::ATTR_PERSISTENT] = true;
            }
            unset($descriptor['persistent']);
        }

        if (true === isset($descriptor['dialectClass']))
        {
            unset($descriptor['dialectClass']);
        }

        if (true === isset($descriptor['dsn']))
        {
            $dsnAttributes = $descriptor['dsn'];
        }
        else
        {
            $dsnParts = [];
            foreach($descriptor as $key => $value)
            {
               $dsnParts[] = $key . '=' . $value;
            }
            $dsnAttributes = join(';', $dsnParts);
        }
        $this->_pdo = new \Pdo($dsnAttributes, $username, $password, $options);
    }

    public static function name($name)
    {
        if (false === isset(self::$instance[$name]))
        {
            try
            {
                $di = \Phalcon\Di::getDefault();
                self::$instance[$name] = new Self ($di['databases'][$name]);
            }
            catch(\Phalcon\Di\Exception $e)
            {
                throw $e;
            }
            catch(\PDOException $e)
            {
                throw $e;
            }
            catch (\Throwable $e)
            {
                throw new \Exception($e->getMessage());
            }
        }
        return self::$instance[$name];
    }

    public function gets($statement, $bindParameters = [], $mode = \Phalcon\Db::FETCH_ASSOC)
    {
        try
        {
            return parent::fetchAll($statement, $mode, $bindParameters);
        }
        catch (\PDOException $e)
        {
            throw $e;
        }
    }

    public function get($statement, $bindParameters = [], $mode = \Phalcon\Db::FETCH_ASSOC)
    {
        try
        {
            return parent::fetchOne($statement, $mode, $bindParameters);
        }
        catch (\PDOException $e)
        {
            throw $e;
        }
    }

    public function get1($statement, $bindParameters = [], $mode = \Phalcon\Db::FETCH_ASSOC)
    {
        try
        {
            $results = parent::fetchOne($statement, $mode, $bindParameters);
            if(true === is_array($results))
            {
                foreach($results as $result)
                {
                    return $result;
                }
            }
            return $results;
        }
        catch (\PDOException $e)
        {
            throw $e;
        }
    }

    public function set($statement, $bindParameters = [], $mode = \Phalcon\Db::FETCH_ASSOC)
    {
        try
        {
            return parent::execute($statement, $bindParameters, $mode);
        }
        catch (\PDOException $e)
        {
            throw $e;
        }
    }

    public function setId($statement, $bindParameters = [], $mode = \Phalcon\Db::FETCH_ASSOC)
    {
        if (true === self::set($statement, $bindParameters, $mode))
        {
            return parent::lastInsertId();
        }
        return false;
    }

    public function transaction(callable $callback)
    {
        try {
            parent::begin();
            $return = call_user_func($callback);
            parent::commit();
            return $return;
        }
        catch (\Throwable $e)
        {
            parent::rollback();
            throw new \Exception($e->getMessage());
        }
    }

}
