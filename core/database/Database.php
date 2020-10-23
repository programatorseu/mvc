<?php
class Database {
    public static function connect($config)
    {
        try {
            return new PDO(
                $config['host'] .';dbname=' . $config['db'],
                $config['user'],
                $config['pass'],
                $config['options']
            );
        } catch(PDOException $e) {
            die('Could not connect' . $e->getMessage());
        }
    }
}
