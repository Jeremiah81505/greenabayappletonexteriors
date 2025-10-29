<?php

namespace GoDaddy\WordPress\MWC\Core\Features\Commerce\V2\Mapping\Jobs;

use Exception;
use GoDaddy\WordPress\MWC\Common\DataObjects\AbstractDataObject;
use GoDaddy\WordPress\MWC\Common\Exceptions\SentryException;
use GoDaddy\WordPress\MWC\Common\Exceptions\WordPressDatabaseException;
use GoDaddy\WordPress\MWC\Common\Helpers\StringHelper;
use GoDaddy\WordPress\MWC\Common\Repositories\Exceptions\WordPressRepositoryException;
use GoDaddy\WordPress\MWC\Common\Repositories\WordPress\DatabaseRepository;
use GoDaddy\WordPress\MWC\Core\Features\Commerce\Exceptions\CommerceException;
use GoDaddy\WordPress\MWC\Core\Features\Commerce\Exceptions\Contracts\CommerceExceptionContract;
use GoDaddy\WordPress\MWC\Core\Features\Commerce\Models\ResourceMapCollection;
use GoDaddy\WordPress\MWC\Core\Features\Commerce\V2\Catalog\Providers\DataObjects\Reference;
use GoDaddy\WordPress\MWC\Core\Features\Commerce\V2\Mapping\DataObjects\ResourceMap;
use GoDaddy\WordPress\MWC\Core\Features\Commerce\V2\Mapping\Services\Contracts\ReferencesServiceContract;
use GoDaddy\WordPress\MWC\Core\JobQueue\Contracts\BatchJobContract;
use GoDaddy\WordPress\MWC\Core\JobQueue\Contracts\HasJobSettingsContract;
use GoDaddy\WordPress\MWC\Core\JobQueue\Contracts\QueueableJobContract;
use GoDaddy\WordPress\MWC\Core\JobQueue\DataObjects\BatchJobOutcome;
use GoDaddy\WordPress\MWC\Core\JobQueue\DataObjects\BatchJobSettings;
use GoDaddy\WordPress\MWC\Core\JobQueue\Traits\BatchJobTrait;

/**
 * @method BatchJobSettings getJobSettings()
 */
abstract class AbstractMappingJob implements QueueableJobContract, HasJobSettingsContract, BatchJobContract
{
    use BatchJobTrait;

    /** @var ReferencesServiceContract service class to fetch references from v2 API */
    protected ReferencesServiceContract $referencesService;

    /**
     * Processes a single batch of records.
     *
     * @return BatchJobOutcome
     * @throws CommerceException
     */
    protected function processBatch() : BatchJobOutcome
    {
        try {
            $localResourceMapCollection = $this->getV1Mappings();

            if (empty($localResourceMapCollection)) {
                return $this->makeOutcome();
            }

            $this->mapV1ResourcesToV2($localResourceMapCollection);

            return $this->makeOutcome();
        } catch (Exception|CommerceExceptionContract $e) {
            // Handle specific commerce exceptions if needed
            throw new CommerceException($e->getMessage(), $e);
        }
    }

    /**
     * Gets the v1 mappings that still require a corresponding v2 mapping.
     *
     * @return ResourceMapCollection|null
     * @throws WordPressRepositoryException
     */
    protected function getV1Mappings() : ?ResourceMapCollection
    {
        $localIds = $this->getUnmappedLocalResourceIds();

        if (empty($localIds)) {
            return null;
        }

        $resourceMapCollection = $this->getLocalResourceMapsByLocalIds($localIds);

        $this->setAttemptedResourcesCount(count($resourceMapCollection->getResourceMaps()));

        return $resourceMapCollection;
    }

    /**
     * Maps local resources to V2 references and persists the mappings.
     *
     * @param ResourceMapCollection $resourceMapCollection
     * @return void
     * @throws CommerceExceptionContract|Exception
     */
    protected function mapV1ResourcesToV2(ResourceMapCollection $resourceMapCollection) : void
    {
        // Get the V2 references from the Commerce API for the supplied V1 UUIDs
        $references = $this->referencesService->getReferencesByReferenceValues(
            $resourceMapCollection->getRemoteIds()
        );

        // Build mappings between the V2 data and our local resources.
        $referenceMappings = $this->buildReferenceMap($references, $resourceMapCollection);

        // Map local resources to V2 UUIDs
        foreach ($referenceMappings as $referenceMap) {
            $this->addLocalMappingRecordWithExceptionHandling($referenceMap);
        }
    }

    /**
     * Builds a mapping between the V2 references and the local resources.
     *
     * @param AbstractDataObject[] $references
     * @param ResourceMapCollection $resourceMapCollection
     * @return ResourceMap[]
     */
    abstract protected function buildReferenceMap(array $references, ResourceMapCollection $resourceMapCollection) : array;

    /**
     * Adds a local mapping record with exception handling.
     *
     * This method is used to handle exceptions when adding a local mapping record.
     * If the record already exists, it will skip the addition.
     *
     * @param ResourceMap $referenceMap
     * @return void
     */
    protected function addLocalMappingRecordWithExceptionHandling(ResourceMap $referenceMap) : void
    {
        try {
            $this->addLocalMappingRecord($referenceMap);
        } catch (WordPressDatabaseException $e) {
            if (StringHelper::startsWith($e->getMessage(), 'Duplicate entry')) {
                // If the record already exists, we can skip it.
                return;
            }

            // we don't want to throw exceptions here because we want to continue processing other records
            SentryException::getNewInstance($e->getMessage(), $e);
        }
    }

    /**
     * Adds the provided reference map to the local mapping table.
     *
     * @param ResourceMap $referenceMap
     * @return void
     * @throws WordPressDatabaseException
     */
    abstract protected function addLocalMappingRecord(ResourceMap $referenceMap) : void;

    /**
     * Gets the records from the local mapping table, giving us a link between the local bigint IDs and the v1
     * remote UUIDs.
     *
     * @param int[] $localIds
     * @return ResourceMapCollection
     */
    abstract protected function getLocalResourceMapsByLocalIds(array $localIds) : ResourceMapCollection;

    /**
     * Gets the local IDs of resources that exist in the v1 mapping table but do not yet exist in the v2 mapping table.
     *
     * @return int[]
     */
    protected function getUnmappedLocalResourceIds() : array
    {
        $limit = $this->getJobSettings()->maxPerBatch;

        $results = DatabaseRepository::getResults(
            $this->getUnmappedLocalIdsSqlString(),
            [
                $limit,
            ],
        );

        /** @var string[] $localIds */
        $localIds = array_column($results, 'local_id');

        return array_map('intval', $localIds);
    }

    /**
     * Gets the SQL string for the unmapped local IDs query.
     *
     * @return string
     */
    abstract protected function getUnmappedLocalIdsSqlString() : string;

    /**
     * Gets the string value of the v2 `reference.origin` property for the v1 containing the v1 `reference.value` needed for mapping.
     *
     * @return string
     */
    abstract protected function getV1OriginString() : string;

    /**
     * Gets the V1 reference value from the provided references.
     *
     * @param Reference[] $references
     * @return string|null
     */
    protected function getV1Reference(array $references) : ?string
    {
        foreach ($references as $reference) {
            if ($reference->origin === $this->getV1OriginString()) {
                return $reference->value;
            }
        }

        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function getJobKey() : string
    {
        // @phpstan-ignore-next-line
        return static::JOB_KEY;
    }
}
