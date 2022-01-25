<?php

use App\graphql\TypeRegistry;
use GraphQL\Error\DebugFlag;
use GraphQL\GraphQL;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Schema;

require('vendor/autoload.php');

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type, Access-Control-Alow-Headers, X-Requested-With');

try {
    $debug        = DebugFlag::INCLUDE_DEBUG_MESSAGE | DebugFlag::INCLUDE_TRACE;
    $typeRegistry = new TypeRegistry();

    $schema = new Schema([
        'query'      => $typeRegistry->get('Query'),
        'typeLoader' => static fn (string $name): Type => $typeRegistry->get($name),
    ]);

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