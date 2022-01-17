<?php

require('vendor/autoload.php');

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type, Access-Control-Alow-Headers, X-Requested-With');

use App\graphql\RootQuery;
use GraphQL\GraphQL;
use GraphQL\Type\Schema;
use GraphQL\Error\DebugFlag;

try {
    $debug = DebugFlag::INCLUDE_DEBUG_MESSAGE | DebugFlag::INCLUDE_TRACE;

    $schema = new Schema([
        'query' => new RootQuery()
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