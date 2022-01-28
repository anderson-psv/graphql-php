<?php

declare(strict_types=1);

namespace App\graphql;

use App\Db\Sql;
use App\Model\Categoria;
use App\Model\Pedido;
use App\Model\PedidoItem;
use App\Model\Produto;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Definition\ObjectType;

class TypeRegistry
{
    /**
     * @var array<string, Type>
     */
    private array $types = [];
    private Connection $db;

    public function __construct()
    {
        $this->db = Sql::Db();
    }

    public function getDb() {
        return $this->db;
    }

    public function get(string $name): Type
    {
        /**
         * This way, keeps a single instance of ObjectType unique name
         */
        return $this->types[$name] ??= $this->{$name}();
    }

    private function Query(): ObjectType
    {
        //Load query's from model's if function exists
        $fields_query = [];
        foreach (glob('./app/Model/*.php') as $file) {
            $class = "\\App\\Model\\".basename($file, '.php');
            if(method_exists($class, 'getQueryes')) {
               $fields_query = array_merge($fields_query, $class::getQueryes($this));
            }
        }

        return new ObjectType([
            'name'   => 'Query',
            'fields' => fn () => $fields_query
        ]);
    }

    private function Produto(): ObjectType
    {
        return new ObjectType([
            'name'        => 'Produto',
            'description' => 'Type Produto',
            'fields'      => fn () => [
                'idproduto'   => Type::int(),
                'descricao'   => Type::string(),
                'valor'       => Type::float(),
                'idcategoria' => Type::int(),
                'categoria'   => fn () => [
                    'type'    => $this->get('Categoria'),
                    'resolve' => function ($produto, $args) {
                        $qb = new QueryBuilder($this->db);

                        $categoria = $qb->select('*')
                            ->from(Categoria::$table)
                            ->where(Categoria::$id . ' = :id')
                            ->setParameter('id', $produto['idcategoria'])
                            ->fetchAssociative();

                        return $categoria;
                    }
                ]
            ]
        ]);
    }

    private function Categoria(): ObjectType
    {
        return new ObjectType([
            'name'        => 'Categoria',
            'description' => 'Categoria de produto',
            'fields'      => fn () => [
                'idcategoria'     => Type::int(),
                'descricao'       => Type::string(),
                'idcategoria_pai' => Type::int(),
                'produtos' => fn () => [
                    'type' => Type::listOf($this->get('Produto')),
                    'resolve' => function ($categoria, $args) {
                        $qb = new QueryBuilder($this->db);

                        return $qb->select('*')
                            ->from(Produto::$table)
                            ->where(Produto::$id . ' = :id')
                            ->setParameter('id', $categoria['idcategoria'])
                            ->fetchAllAssociative();
                    }
                ]
            ]
        ]);
    }

    private function Pedido(): ObjectType
    {
        return new ObjectType([
            'name'        => 'Pedido',
            'description' => 'Pedido',
            'fields'      => fn ()    => [
                'idpedido' => Type::int() ,
                'status'   => Type::string() ,
                'itens'    => fn ()          => [
                    'type'    => Type::listOf($this->get('PedidoItem')),
                    'resolve' => function ($pedido, $args) {
                        $qb = new QueryBuilder($this->db);

                        return $qb->select('*')
                            ->from(PedidoItem::$table)
                            ->where(Pedido::$id . ' = :id')
                            ->setParameter('id', $pedido['idpedido'])
                            ->fetchAllAssociative();
                    }
                ]
            ]
        ]);
    }

    private function PedidoItem(): ObjectType
    {
        return new ObjectType([
            'name'        => 'PedidoItem' ,
            'description' => 'Item de Pedido' ,
            'fields'      => fn ()            => [
                'iditem'    => Type::int() ,
                'idproduto' => Type::int() ,
                'idpedido'  => Type::int() ,
                'valor'     => Type::float(),
                'produto'     => fn ()          => [
                    'type'    => Type::listOf($this->get('Produto')),
                    'resolve' => function ($pedido, $args) {
                        $qb = new QueryBuilder($this->db);

                        return $qb->select('*')
                            ->from(Produto::$table)
                            ->where(Produto::$id . ' = :id')
                            ->setParameter('id', $pedido['idproduto'])
                            ->fetchAllAssociative();
                    }
                ]
            ]
        ]);
    }
}
