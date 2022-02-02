<?php

namespace App\Model;

use Exception;
use App\Db\Sql;
use App\iModel;
use App\graphql\TypeRegistry;
use Doctrine\DBAL\Connection;
use GraphQL\Type\Definition\Type;
use Doctrine\DBAL\Query\QueryBuilder;

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
        if (!is_numeric($idcategoria)) {
            throw new Exception("ID categoria inválido!");
        }
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

    /**
     * Seta os dados em seu respectivo set
     *
     * @param array $data
     * @return void
     * @throws Exception
     */
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
    public function getData()
    {
        $data = [];
        foreach (self::$db_fields as $field) {
            $data[$field] = $this->$field;
        }

        return $data;
    }

    /**
     * Carrega os dados da categoria do banco de dados
     *
     * @param integer $idcategoria
     * @return array
     * @throws Exception
     */
    public function getCategoriaDb(int $idcategoria)
    {
        try {
            $qb = new QueryBuilder($this->db);

            $qb->select(implode(',', self::$db_fields))
                ->from(self::$table)
                ->where("idcategoria = :idcategoria")
                ->setParameter('idcategoria', $idcategoria);

            $data = $qb->fetchAssociative();
        } catch (\Throwable $th) {
            throw new Exception("Erro ao solicitar categoria no banco de dados!");
        }

        $this->setData($data);

        return $data;
    }

    /**
     * Salva a categoria.
     * Update se setado idcategoria, senão insert
     *
     * @return bool
     * @throws Exception
     */
    public function save()
    {
        $qb = new QueryBuilder($this->db);

        $data = [];
        foreach (self::$db_fields as $field) {
            $data[$field] = $this->$field;
        }

        if ($this->idcategoria) {
            //Revalidate fields before update
            $this->setData($data);

            try {
                //is update
                $qb->update(self::$table)
                    ->where('idcategoria = :idcategoria')
                    ->setParameter('idcategoria', $this->idcategoria);

                foreach ($data as $field => $value) {
                    $qb->set($field, ":$field")
                        ->setParameter($field, $value);
                }

                if ($qb->executeQuery()) {
                    return true;
                }
            } catch (\Throwable $th) {
                throw new Exception("Erro ao atualizar categoria!");
            }
        } else {
            //Removes id because is incremental in database
            unset($data['idcategoria']);

            //Revalidate fields before insert 
            $this->setData($data);

            try {
                //is insert
                $qb->insert(self::$table);

                foreach ($data as $field => $value) {
                    $qb->setValue($field, ":$field")
                        ->setParameter($field, $value);
                }

                if ($result = $qb->executeQuery()) {
                    $this->idcategoria = $result['idcategoria'];
                    return true;
                }
            } catch (\Throwable $th) {
                throw new Exception("Erro ao cadastrar categoria!");
            }
        }

        return false;
    }

    /**
     * Deletar categoria do banco de dados
     *
     * @return bool
     * @throws Exception
     */
    public function delete()
    {
        if (empty($this->idcategoria)) {
            throw new Exception("ID categoria inválido!");
        }

        try {
            $qb = new QueryBuilder($this->db);

            $qb->delete(self::$table)
                ->where('idcategoria = :idcategoria')
                ->setParameter('idcategoria', $this->idcategoria);

            if ($qb->executeQuery()) {
                return true;
            }
        } catch (\Throwable $th) {
            throw new Exception("Erro ao deletar categoria!");
        }

        return false;
    }

    /**
     * Queryes carregadas pelo Graphql
     *
     * @param TypeRegistry $type_reg
     * @return array
     */
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
                    return (new Categoria())->getCategoriaDb($args['idcategoria']);
                }
            ]
        ];
    }
}
