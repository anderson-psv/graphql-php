<?php

//
/** 
 * Função copiada do StackOverflow, adiciona token a chamada da query
 */
function graphql_query(string $endpoint, string $query, array $variables = [], ?string $token = null): array
{
    $headers = ['Content-Type: application/json', 'User-Agent: Dunglas\'s minimal GraphQL client'];
    if (null !== $token) {
        $headers[] = "Authorization: Bearer $token";
    }

    $context = [
        'http' => [
            'method' => 'POST',
            'header' => $headers,
            'content' => json_encode(['query' => $query, 'variables' => $variables]),
        ]
    ];

    $data = @file_get_contents($endpoint, false, stream_context_create($context));

    if (false === $data) {
        $error = error_get_last();
        throw new \ErrorException($error['message'], $error['type']);
    }

    return json_decode($data, true);
}
