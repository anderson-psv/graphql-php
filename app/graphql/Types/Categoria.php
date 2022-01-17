<?php

namespace App\graphql\Types;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;

class Categoria extends ObjectType
{
    public static $id     = 'idcategoria';
    public static $tabela = 'graph_categoria';

    public function __construct()
    {
        $config = [
            'name'        => 'Categoria',
            'description' => 'Categoria de produto',
            'fields'      => function() {
                return [
                    'idcategoria'       => Type::int(),
                    'descricao'         => Type::string(),
                    'idcategoria_pai'   => Type::float(),
                ];
            }
        ];

        parent::__construct($config);
    }
}
