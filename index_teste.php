<?php

require('vendor/autoload.php');

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type, Access-Control-Alow-Headers, X-Requested-With');

use App\graphql\TypeRegistry;
use GraphQL\GraphQL;
use GraphQL\Error\DebugFlag;
use GraphQL\Language\Parser;
use GraphQL\Utils\AST;
use GraphQL\Utils\BuildSchema;

try {
    $debug         = DebugFlag::INCLUDE_DEBUG_MESSAGE | DebugFlag::INCLUDE_TRACE;
    $graphql_folder = "./app/graphql";
    $cacheFilename  = $graphql_folder . "/cache/schema_cached.php";

    if (!file_exists($cacheFilename)) {
        $document = Parser::parse(file_get_contents($graphql_folder . "/schema.graphql"));
        //Salva o cache
        #file_put_contents($cacheFilename, "<?php\nreturn " . var_export(AST::toArray($document), true) . ";\n");
    } else {
        $document = AST::fromArray(require $cacheFilename); // fromArray() is a lazy operation as well
    }

    $typeConfigDecorator = function ($typeConfig, $typeDefinitionNode) {
        $name = $typeConfig['name'];
        $dados = implode(" \n", array_keys($typeConfig));

        $typeRegistry = new TypeRegistry();

        if ($name == 'Produto') {
            $typeConfig['resolveField'] = function($root, $args) {
                
            };
        }
        return $typeConfig;
    };

    $schema = BuildSchema::build($document, $typeConfigDecorator);

    $rawInput = file_get_contents('php://input');
    $query    = json_decode($rawInput, true)['query'];

    $result = GraphQL::executeQuery($schema, $query);
    $output = $result->toArray($debug);
} catch (\Throwable $th) {
    $output = [
        'error' => [
            'code'    => $th->getCode(),
            'message' => $th->getMessage()
        ]
    ];
}

header('Content-Type: application/json', true);
echo json_encode($output);
