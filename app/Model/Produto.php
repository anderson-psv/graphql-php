<?php

namespace App\Model;

use Exception;
use App\iModel;
use App\Db\Sql;
use App\graphql\TypeRegistry;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use GraphQL\Type\Definition\Type;

class Produto implements iModel
{
    private Connection $db;

    public static $id        = 'idproduto';
    public static $table     = 'graph_produto';
    public static $db_fields = [
        'idproduto',
        'descricao',
        'valor',
        'idcategoria'
    ];

    private int $idproduto;
    private int $idcategoria;
    private string $descricao;
    private float $valor;

    public function __construct($data = [])
    {
        $this->db = Sql::Db();

        if ($data) {
            $this->setData($data);
        }
    }

    public function setIdproduto(int $idproduto)
    {
        $this->idproduto = $idproduto;
    }

    public function getIdproduto()
    {
        return $this->idproduto;
    }

    public function setIdcategoria(int $idcategoria)
    {
        $this->idcategoria = $idcategoria;
    }

    public function getIdcategoria()
    {
        return $this->idcategoria;
    }

    public function setDescricao(string $descricao)
    {
        if (empty($descricao)) {
            throw new Exception("Descrição vazia!");
        }
        $this->descricao = $descricao;
    }

    public function getDescricao()
    {
        return $this->descricao;
    }

    public function setValor(float $valor)
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

    public function getProdutoDb(int $idproduto)
    {
        try {
            $qb = new QueryBuilder($this->db);

            $data = $qb->select(implode(',', self::$db_fields))
                ->from(self::$table)
                ->where("idproduto = :idproduto")
                ->setParameter('idproduto', $idproduto);

            $data = $qb->fetchAssociative();
        } catch (\Throwable $th) {
            throw new Exception("Erro ao solicitar produto do banco de dados!");
        }

        $this->setData($data);

        return $qb;
    }

    public function save()
    {
        $qb = new QueryBuilder($this->db);

        $fields =  self::$db_fields;
        unset($fields[0]); //Removes idproduto

        if ($this->idproduto) {
            //is update
            $qb->update(self::$table)
                ->where('idproduto = :idproduto')
                ->setParameter('idproduto', $this->idproduto);

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
                $this->setIdproduto = $result['idproduto'];
                return true;
            }
        }

        return false;
    }

    /**
     * Deletar produto do banco de dados
     *
     * @return bool
     * @throws Exception
     */
    public function delete()
    {
        if (empty($this->idproduto)) {
            throw new Exception("ID produto inválido!");
        }

        try {
            $qb = new QueryBuilder($this->db);

            $qb->delete(self::$table)
                ->where('idproduto = :idproduto')
                ->setParameter('idproduto', $this->idproduto);

            if ($qb->executeQuery()) {
                return true;
            }
        } catch (\Throwable $th) {
            throw new Exception("Erro ao deletar produto!");
        }

        return false;
    }

    static public function getQueryes(TypeRegistry $type_reg)
    {
        return [
            'produtos' => [
                'type'    => Type::listOf($type_reg->get('Produto')),
                'resolve' => function ($rootValue, $args) use ($type_reg) {
                    $qb = new QueryBuilder($type_reg->getDb());

                    return $qb->select(Produto::$db_fields)
                        ->from(Produto::$table)
                        ->fetchAllAssociative();
                }
            ],
            'produto' => [
                'type' => $type_reg->get('Produto'),
                'args' => [
                    'idproduto' => Type::nonNull(Type::int())
                ],
                'resolve' => function ($rootValue, $args) use ($type_reg) {
                    $qb = new QueryBuilder($type_reg->getDb());

                    return $qb->select(Produto::$db_fields)
                        ->from(Produto::$table)
                        ->where('idproduto = :id')
                        ->setParameter('id', $args['idproduto'])
                        ->fetchAssociative();
                }
            ],
        ];
    }
}
