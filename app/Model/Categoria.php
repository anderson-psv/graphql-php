<?php

namespace App\Model;

use App\iModel;
use App\Db\Sql;
use App\graphql\TypeRegistry;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use GraphQL\Type\Definition\Type;

class Categoria implements iModel
{
    private Connection $db;

    public static $id        = 'idcategoria';
    public static $table     = 'graph_categoria';
    public static $db_fields = [
        'idcategoria',
        'idcategoria_pai',
        'descricao'
    ];

    private int $idcategoria;
    private int $idcategoria_pai;
    private string $descricao;

    public function __construct($data = [])
    {
        $this->db = Sql::Db();

        if ($data) {
            $this->setData($data);
        }
    }

    public function setIdcategoria(int $idcategoria)
    {
        $this->idcategoria = $idcategoria;
    }

    public function getIdcategoria()
    {
        return $this->idcategoria;
    }

    public function setIdcategoria_pai(int $idcategoria_pai)
    {
        $this->idcategoria_pai = $idcategoria_pai;
    }

    public function getIdcategoria_pai()
    {
        return $this->idcategoria_pai;
    }

    public function setDescricao(int $descricao)
    {
        $this->descricao = $descricao;
    }

    public function getDescricao()
    {
        return $this->descricao;
    }

    public function setData(array $data)
    {
        foreach (self::$db_fields as $field) {
            if ($value = $data[$field]) {
                $this->{'set' . ucfirst($field)}($value);
            }
        }
    }

    public function getData(int $id)
    {
        $qb = new QueryBuilder($this->db);

        $qb->select(implode(',', self::$db_fields))
            ->from(self::$table)
            ->where("idcategoria = :idcategoria")
            ->setParameter('idcategoria', $id);

        return $qb->fetchAssociative();
    }

    public function save()
    {
        $qb = new QueryBuilder($this->db);

        $fields =  self::$db_fields;
        unset($fields[0]); //Removes idcategoria

        if ($this->idcategoria) {
            //is update
            $qb->update(self::$table)
                ->where('idcategoria = :idcategoria')
                ->setParameter('idcategoria', $this->idcategoria);

            foreach ($fields as $field) {
                $qb->set($field, ":$field")
                    ->setParameter($field, $this->$$field);
            }

            if ($qb->executeQuery()) {
                return true;
            }
        } else {
            //is insert
            $qb->insert(self::$table);

            foreach ($fields as $field) {
                $qb->setValue($field, ":$field")
                    ->setParameter($field, $this->$$field);
            }

            if ($result = $qb->executeQuery()) {
                $this->idcategoria = $result['idcategoria'];
                return true;
            }
        }

        return false;
    }

    static public function getQueryes(TypeRegistry $type_reg)
    {
        return [
            'categorias' => [
                'type'    => Type::listOf($type_reg->get('Categoria')),
                'resolve' => function ($rootValue, $args) use ($type_reg) {
                    $qb = new QueryBuilder($type_reg->getDb());

                    return $qb->select(Categoria::$db_fields)
                        ->from(Categoria::$table)
                        ->fetchAllAssociative();
                }
            ],
            'categoria' => [
                'type' => $type_reg->get('Categoria'),
                'args' => [
                    'idcategoria' => Type::nonNull(Type::int())
                ],
                'resolve' => function ($rootValue, $args) use ($type_reg) {
                    return (new Categoria())->getData($args['idcategoria']);
                }
            ]
        ];
    }
}
