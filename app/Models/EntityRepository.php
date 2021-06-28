<?php declare(strict_types=1);

namespace App\Models;

use GuzzleHttp\Exception\BadResponseException;
use Vin\ShopwareSdk\Client\CreateClientTrait;
use Vin\ShopwareSdk\Data\Context;
use Vin\ShopwareSdk\Data\Criteria;
use Vin\ShopwareSdk\Data\Entity\EntityCollection;
use Vin\ShopwareSdk\Data\Entity\EntityDefinition;
use Vin\ShopwareSdk\Exception\ShopwareSearchResponseException;
use Vin\ShopwareSdk\Repository\EntityRepository as BaseEntityRepository;
use Vin\ShopwareSdk\Repository\RepositoryInterface;
use Vin\ShopwareSdk\Repository\Struct\AggregationResultCollection;
use Vin\ShopwareSdk\Repository\Struct\EntitySearchResult;
use Vin\ShopwareSdk\Repository\Struct\SearchResultMeta;

class EntityRepository extends BaseEntityRepository
{
    use CreateClientTrait;

    public string $entityName;

    public string $route;

    private EntityDefinition $definition;

    private RepositoryInterface $repository;

    public function __construct(BaseEntityRepository $repository)
    {
        $this->entityName = $repository->getDefinition()->getEntityName();
        $this->httpClient = $repository->getHttpClient();
        $this->definition = $repository->getDefinition();
        $this->route = $repository->route;

        parent::__construct($this->entityName, $this->definition, $this->route);
    }

    public function search(Criteria $criteria, Context $context): EntitySearchResult
    {
        try {
            $response = $this->httpClient->post($this->getSearchApiUrl($context->apiEndpoint), [
                'headers' => $this->buildHeaders($context),
                'body' => json_encode($criteria->parse())
            ])->getBody()->getContents();
        } catch (BadResponseException $exception) {
            $message = $exception->getResponse()->getBody()->getContents();

            throw new ShopwareSearchResponseException($message, $exception->getResponse()->getStatusCode(), $criteria, $exception);
        }

        $response = $this->decodeResponse($response);

        $aggregations = new AggregationResultCollection($response['aggregations']);

        $meta = new SearchResultMeta($response['meta']['total'], $response['meta']['totalCountMode']);

        $searchResult = new EntitySearchResult(
            $this->entityName,
            $meta,
            new EntityCollection(),
            $aggregations,
            $criteria,
            $context
        );

        $data = $this->hydrateSearchResult($response);

        $searchResult->setData(iterable_to_array($data));

        return $searchResult;
    }

    private function hydrateSearchResult(array $response): iterable
    {
        $includes = [];

        foreach ($response['included'] as $include) {
            $includes[$include['type']][$include['id']] = $include;
        }

        foreach ($response['data'] as $dataIndex => $data) {
            yield $dataIndex => $this->resolveRelationships($data, $includes);
        }
    }

    private function decodeResponse(string $response): array
    {
        return \json_decode($response, true) ?? [];
    }

    private function resolveRelationships($data, $includes): ?array
    {
        foreach ($data['relationships'] as $relationshipName => $relationship) {
            $relationship = $relationship['data'] ?? null;

            if ($relationship === null) {
                continue;
            }

            $type = $relationship['type'] ?? null;
            $id = $relationship['id'] ?? null;

            if ($type === null || $id === null) {
                foreach ($relationship as $relationshipIndex => $relatedCollection) {
                    $type = $relatedCollection['type'] ?? null;
                    $id = $relatedCollection['id'] ?? null;

                    if ($type === null || $id === null) {
                        continue;
                    }

                    $relationshipData = $includes[$type][$id] ?? null;

                    if ($relationshipData === null) {
                        continue;
                    }

                    $data['attributes'][$relationshipName][$relationshipIndex] = $this->resolveRelationships($relationshipData, $includes);
                }
            } else {
                $relationshipData = $includes[$type][$id] ?? null;

                if ($relationshipData === null) {
                    continue;
                }

                $data['attributes'][$relationshipName] = $this->resolveRelationships($relationshipData, $includes);
            }
        }

        return $data['attributes'];
    }
}
