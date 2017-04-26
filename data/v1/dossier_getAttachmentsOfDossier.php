<?php


/** @var \HF\ApiClient\Query\Query $query */
$query     = $params[0] ?? \HF\ApiClient\Query\Query::create();
$dossierId = $params[1] ?? null;

if (! $dossierId) {
    throw new \Exception('You must provide a dossierId');
}

return [
    'url'      => sprintf('/dossier/dossiers/%s/attachments%s', $dossierId, $query),
    'method'   => 'GET',
    'response' => [
        'format'      => 'json',
        'valid_codes' => ['200'],
    ],
];
