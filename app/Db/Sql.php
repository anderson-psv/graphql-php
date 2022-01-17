<?php
namespace App\Db;

use Doctrine\DBAL\DriverManager;

class Sql
{
    public static function Db()
    {
        return DriverManager::getConnection([
            "host"     => "mysql-db",
            "user"     => "root",
            "password" => "root",
            "dbname"   => "db_graphql",
            "driver"   => "pdo_mysql"
        ]);
    }
}
