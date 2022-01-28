<?php

namespace App\Model;

use Exception;
use App\iModel;
use App\Db\Sql;
use App\graphql\TypeRegistry;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use GraphQL\Type\Definition\Type;

class Pedido implements iModel
{
    private Connection $db;

    public static $id        = 'idpedido';
    public static $table     = 'graph_pedido';
    public static $db_fields = [
        'idpedido',
        'status'
    ];

    private int $idpedido;
    private string $status;

    public function __construct($data = [])
    {
        $this->db = Sql::Db();

        if ($data) {
            $this->setData($data);
        }
    }

    public function setIdpedido(int $idpedido)
    {
        $this->idpedido = $idpedido;
    }

    public function getIdpedido()
    {
        return $this->idpedido;
    }

    public function setStatus(string $status)
    {
        if (empty($status)) {
            throw new Exception("Status vazio!");
        }
        $this->status = $status;
    }

    public function getStatus()
    {
        return $this->status;
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
            ->where("idpedido = :idpedido")
            ->setParameter('idpedido', $id);

        return $qb->fetchAssociative();
    }

    public function save()
    {
        $qb = new QueryBuilder($this->db);

        $fields =  self::$db_fields;
        unset($fields[0]); //Removes idpedido

        if ($this->idpedido) {
            //is update
            $qb->update(self::$table)
                ->where('idpedido = :idpedido')
                ->setParameter('idpedido', $this->idpedido);

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
                $this->idpedido = $result['idpedido'];
                return true;
            }
        }

        return false;
    }

    static public function getQueryes(TypeRegistry $type_reg)
    {
        return [
            'pedidos' => [
                'type'    => Type::listOf($type_reg->get('Pedido')),
                'resolve' => function ($rootValue, $args) use ($type_reg) {
                    $qb = new QueryBuilder($type_reg->db);

                    return $qb->select(Pedido::$db_fields)
                        ->from(Pedido::$table)
                        ->fetchAllAssociative();
                }
            ],
            'pedido' => [
                'type' => $type_reg->get('Pedido'),
                'args' => [
                    'idpedido' => Type::nonNull(Type::int())
                ],
                'resolve' => function ($rootValue, $args) use ($type_reg) {
                    return (new Pedido())->getData($args['idpedido']);
                }
            ],
        ];
    }
}
