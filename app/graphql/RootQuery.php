<?php

namespace App\graphql;

use App\Db\Sql;
use App\graphql\Types\Categoria;
use App\graphql\Types\Produto;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Types\Types as DbalType;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;

class RootQuery extends ObjectType
{
    public function __construct()
    {
        $db        = Sql::db();
        $categoria = new Categoria();
        $produto   = new Produto($categoria);

        $config = [
            'name'        => 'Query',
            'description' => 'Query root',
            'fields'      => [
                'produtos' => [
                    'type' => Type::listOf($produto),
                    'resolve' => function ($root, $args) use ($db, $produto): array {
                        $qb = new QueryBuilder($db);

                        $produtos = $qb->select('*')
                            ->from($produto::$tabela)
                            ->fetchAllAssociative();

                        return $produtos;
                    }
                ],
                'produto' => [
                    'type' => $produto,
                    'args' => [
                        'id' => Type::nonNull(Type::int())
                    ],
                    'resolve' => function ($root, $args) use ($db, $produto) {
                        $qb = new QueryBuilder($db);

                        $produto = $qb->select('*')
                            ->from($produto::$tabela)
                            ->where('idproduto = :idproduto')
                            ->setParameter('idproduto', $args['id'], DbalType::INTEGER)
                            ->fetchAssociative();

                        return $produto;
                    }
                ],
                'categorias' => [
                    'type' => Type::listOf($categoria),
                    'resolve' => function ($root, $args) use ($db, $categoria): array {
                        $qb = new QueryBuilder($db);

                        $categorias = $qb->select('*')
                            ->from($categoria::$tabela)
                            ->fetchAllAssociative();

                        return $categorias;
                    }
                ],
                'categoria' => [
                    'type' => $categoria,
                    'args' => [
                        'id' => Type::nonNull(Type::int())
                    ],
                    'resolve' => function ($root, $args) use ($db, $categoria) {
                        $qb = new QueryBuilder($db);

                        $categoria = $qb->select('*')
                            ->from($categoria::$tabela)
                            ->where('idcategoria = :idcategoria')
                            ->setParameter('idcategoria', $args['id'], DbalType::INTEGER)
                            ->fetchAssociative();

                        return $categoria;
                    }
                ],
            ]
        ];

        parent::__construct($config);
    }
}
