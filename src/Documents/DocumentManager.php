<?php
declare(strict_types=1);

namespace ElasticAdapter\Documents;

use ElasticAdapter\Search\SearchRequest;
use ElasticAdapter\Search\SearchResponse;
use Elasticsearch\Client;

class DocumentManager
{
    /**
     * @var Client
     */
    private $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * @param  string  $indexName
     * @param  Document[]  $documents
     * @param  bool  $refresh
     * @return DocumentManager
     */
    public function index(string $indexName, array $documents, bool $refresh = false): self
    {
        $params = [
            'index' => $indexName,
            'refresh' => $refresh ? 'true' : 'false',
            'body' => []
        ];

        foreach ($documents as $document) {
            $params['body'][] = ['index' => ['_id' => $document->getId()]];
            $params['body'][] = $document->getContent();
        }

        $this->client->bulk($params);

        return $this;
    }

    /**
     * @param  string  $indexName
     * @param  string[]  $documentIds
     * @param  bool  $refresh
     * @return DocumentManager
     */
    public function delete(string $indexName, array $documentIds, bool $refresh = false): self
    {
        $params = [
            'index' => $indexName,
            'refresh' => $refresh ? 'true' : 'false',
            'body' => []
        ];

        foreach ($documentIds as $documentId) {
            $params['body'][] = ['delete' => ['_id' => $documentId]];
        }

        $this->client->bulk($params);

        return $this;
    }

    public function search(string $indexName, SearchRequest $request): SearchResponse
    {
        $params = [
            'index' => $indexName,
            'body' => $request->toArray()
        ];

        $response = $this->client->search($params);

        return new SearchResponse($response);
    }
}