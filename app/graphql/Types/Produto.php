<?php

namespace App\graphql\Types;

use App\Db\Sql;
use Doctrine\DBAL\Query\QueryBuilder;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;

class Produto extends ObjectType
{
    public static $id     = 'idproduto';
    public static $tabela = 'graph_produto';

    public function __construct($categoria)
    {
        $db = Sql::Db();

        $config = [
            'name'        => 'Produto',
            'description' => 'Type do Produto',
            'fields'      => function () use ($db, $categoria) {
                return [
                    'idproduto' => Type::int(),
                    'descricao' => Type::string(),
                    'valor'     => Type::float(),
                    'categoria' => [
                        'type' => Type::listOf($categoria),
                        'resolve' => function ($produto, $args) use ($db) {
                            $qb = new QueryBuilder($db);

                            $qb->select('*')
                                ->from('graph_categoria')
                                ->where('idcategoria = :idcategoria')
                                ->setParameter(':idcategoria', $produto['idcategoria']);
                        }
                    ]
                ];
            }
        ];

        parent::__construct($config);
    }
}
