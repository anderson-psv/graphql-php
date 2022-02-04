<?php

namespace App\Model;

use Exception;
use App\iModel;
use App\Db\Sql;
use App\graphql\TypeRegistry;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use GraphQL\Type\Definition\Type;

class PedidoItem implements iModel
{
    private Connection $db;

    public static $id        = 'iditem';
    public static $table     = 'graph_pedido_item';
    public static $db_fields = [
        'iditem',
        'idproduto',
        'idpedido',
        'valor'
    ];

    private int $iditem;
    private int $idproduto;
    private int $idpedido;
    private float $valor;


    private string $status;

    public function __construct($data = [])
    {
        $this->db = Sql::Db();

        if ($data) {
            $this->setData($data);
        }
    }

    public function setIditem(int $iditem)
    {
        $this->iditem = $iditem;
    }

    public function getIditem()
    {
        return $this->iditem;
    }

    public function setIdproduto(int $idproduto)
    {
        $this->idproduto = $idproduto;
    }

    public function getIdproduto()
    {
        return $this->idproduto;
    }

    public function setIdpedido(int $idpedido)
    {
        $this->idpedido = $idpedido;
    }

    public function getIdpedido()
    {
        return $this->idpedido;
    }

    public function setValor(int $valor)
    {
        if ($valor < 0) {
            throw new Exception("Valor não pode ser negativo!");
        }
        $this->valor = $valor;
    }

    public function getValor()
    {
        return $this->valor;
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
     * Carrega os dados do item de pedido, do banco de dados
     *
     * @param integer $iditem
     * @return array
     * @throws Exception
     */
    public function getPedidoItemDb(int $iditem)
    {
        try {
            $qb = new QueryBuilder($this->db);

            $qb->select(implode(',', self::$db_fields))
                ->from(self::$table)
                ->where("iditem = :iditem")
                ->setParameter('iditem', $iditem);

            $data = $qb->fetchAssociative();
        } catch (\Throwable $th) {
            throw new Exception("Erro ao solicitar item de pedido no banco de dados!");
        }

        $this->setData($data);

        return $data;
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

    public function save()
    {
        $qb = new QueryBuilder($this->db);

        $fields =  self::$db_fields;
        unset($fields[0]); //Removes iditem

        if ($this->iditem) {
            //is update
            $qb->update(self::$table)
                ->where('iditem = :iditem')
                ->setParameter('iditem', $this->iditem);

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
                $this->iditem = $result['iditem'];
                return true;
            }
        }

        return false;
    }

    /**
     * Deletar item de pedido do banco de dados
     *
     * @return bool
     * @throws Exception
     */
    public function delete()
    {
        if (empty($this->iditem)) {
            throw new Exception("ID item inválido!");
        }

        try {
            $qb = new QueryBuilder($this->db);

            $qb->delete(self::$table)
                ->where('iditem = :iditem')
                ->setParameter('iditem', $this->iditem);

            if ($qb->executeQuery()) {
                return true;
            }
        } catch (\Throwable $th) {
            throw new Exception("Erro ao deletar item de pedido!");
        }

        return false;
    }

    static public function getQueryes(TypeRegistry $type_reg)
    {
        return [
            'itens' => [
                'type'    => Type::listOf($type_reg->get('PedidoItem')),
                'resolve' => function ($rootValue, $args) use ($type_reg) {
                    $qb = new QueryBuilder($type_reg->getDb());

                    return $qb->select(PedidoItem::$db_fields)
                        ->from(PedidoItem::$table)
                        ->fetchAllAssociative();
                }
            ],
            'item' => [
                'type' => $type_reg->get('PedidoItem'),
                'args' => [
                    'iditem' => Type::nonNull(Type::int())
                ],
                'resolve' => function ($rootValue, $args) use ($type_reg) {
                    return (new PedidoItem())->getPedidoItemDb($args['iditem']);
                }
            ],
        ];
    }
}
