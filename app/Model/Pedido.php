<?php

namespace App\Model;

use Exception;
use App\Db\Sql;
use App\iModel;
use App\graphql\TypeRegistry;
use Doctrine\DBAL\Connection;
use GraphQL\Type\Definition\Type;
use Doctrine\DBAL\Query\QueryBuilder;

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

    /**
     * Retorna os dados da instancia em formato de array
     *
     * @return array
     */
    public function asArray()
    {
        $data = [];
        foreach (self::$db_fields as $field) {
            $data[$field] = $this->$field;
        }

        return $data;
    }

    /**
     * Carrega os dados do pedido do banco de dados
     *
     * @param integer $idpedido
     * @return array
     * @throws Exception
     */
    public function getPedidoDb(int $idpedido)
    {
        try {
            $qb = new QueryBuilder($this->db);

            $qb->select(implode(',', self::$db_fields))
                ->from(self::$table)
                ->where("idpedido = :idpedido")
                ->setParameter('idpedido', $idpedido);

            $data = $qb->fetchAssociative();
        } catch (\Throwable $th) {
            throw new Exception("Erro ao solicitar pedido no banco de dados!");
        }

        $this->setData($data);

        return $data;
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

    /**
     * Deletar pedido do banco de dados
     *
     * @return bool
     * @throws Exception
     */
    public function delete()
    {
        if (empty($this->idpedido)) {
            throw new Exception("ID pedido inválido!");
        }

        try {
            $qb = new QueryBuilder($this->db);

            $qb->delete(self::$table)
                ->where('idpedido = :idpedido')
                ->setParameter('idpedido', $this->idpedido);

            if ($qb->executeQuery()) {
                return true;
            }
        } catch (\Throwable $th) {
            throw new Exception("Erro ao deletar pedido!");
        }

        return false;
    }

    static public function getQueryes(TypeRegistry $type_reg)
    {
        return [];
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
                    return (new Pedido())->getPedidoDb($args['idpedido']);
                }
            ],
        ];
    }
}
