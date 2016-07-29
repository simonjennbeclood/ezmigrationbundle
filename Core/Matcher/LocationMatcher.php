<?php

namespace Kaliop\eZMigrationBundle\Core\Matcher;

use eZ\Publish\API\Repository\Repository;
use eZ\Publish\API\Repository\SearchService;
use eZ\Publish\API\Repository\Values\Content\Content;
use eZ\Publish\API\Repository\Values\Content\Query;
use Kaliop\eZMigrationBundle\Core\Collection\LocationCollection;

class LocationMatcher
{
    const MATCH_CONTENT_ID = 'content_id';
    const MATCH_LOCATION_ID = 'location_id';
    const MATCH_CONTENT_REMOTE_ID = 'content_remote_id';
    const MATCH_LOCATION_REMOTE_ID = 'location_remote_id';
    const MATCH_PARENT_LOCATION_ID = 'parent_location_id';
    const MATCH_PARENT_LOCATION_REMOTE_ID = 'parent_location_remote_id';

    protected $repository;

    /**
     * @todo inject the services needed, not thw whole repository
     */
    public function __construct(Repository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @param array $conditions key: condition, value: int / array of ints
     * @return LocationCollection
     */
    public function matchLocation(array $conditions)
    {
        foreach ($conditions as $key => $values) {

            if (!is_array($values)) {
                $values = array($values);
            }

            switch ($key) {
                case self::MATCH_CONTENT_ID:
                   return new LocationCollection($this->findLocationsByContentIds($values));

                case self::MATCH_LOCATION_ID:
                    return new LocationCollection($this->findLocationsByLocationIds($values));

                case self::MATCH_CONTENT_REMOTE_ID:
                    return new LocationCollection($this->findLocationsByContentRemoteIds($values));

                case self::MATCH_LOCATION_REMOTE_ID:
                    return new LocationCollection($this->findLocationsByLocationRemoteIds($values));

                case self::MATCH_PARENT_LOCATION_ID:
                    return new LocationCollection($this->findLocationsByParentLocationIds($values));

                case self::MATCH_PARENT_LOCATION_REMOTE_ID:
                    return new LocationCollection($this->findLocationsByParentLocationRemoteIds($values));
            }
        }
    }

    /**
     * Returns all locations of a set of objects
     *
     * @param int[] $contentIds
     * @return Location[]
     */
    protected function findLocationsByContentIds(array $contentIds)
    {
        $locations = [];

        foreach ($contentIds as $contentId) {
            $content = $this->repository->getContentService()->loadContent($contentId);
            $locations = array_merge($locations, $this->repository->getLocationService()->loadLocations($content->contentInfo));
        }

        return $locations;
    }

    /**
     * Returns all locations of a set of objects
     *
     * @param int[] $remoteContentIds
     * @return Location[]
     */
    protected function findLocationsByContentRemoteIds(array $remoteContentIds)
    {
        $locations = [];

        foreach ($remoteContentIds as $remoteContentId) {
            $content = $this->repository->getContentService()->loadContentByRemoteId($remoteContentId);
            $locations = array_merge($locations, $this->repository->getLocationService()->loadLocations($content->contentInfo));
        }

        return $locations;
    }

    /**
     * @param int[] $locationIds
     * @return Location[]
     */
    protected function findLocationsByLocationIds(array $locationIds)
    {
        $locations = [];

        foreach ($locationIds as $locationId) {
            $locations[] = $this->repository->getLocationService()->loadLocation($locationId);
        }

        return $locations;
    }

    /**
     * @param int[] $locationRemoteIds
     * @return Location[]
     */
    protected function findLocationsByLocationRemoteIds($locationRemoteIds)
    {
        $locations = [];

        foreach ($locationRemoteIds as $locationRemoteId) {
            $locations[] = $this->repository->getLocationService()->loadLocationByRemoteId($locationRemoteId);
        }

        return $locations;
    }

    /**
     * @param int[] $parentLocationIds
     * @return Location[]
     */
    protected function findLocationsByParentLocationIds($parentLocationIds)
    {
        $query = new Query();
        $query->limit = PHP_INT_MAX;
        $query->filter = new Query\Criterion\ParentLocationId($parentLocationIds);

        $results = $this->repository->getSearchService()->findLocations($query);

        $locations = [];

        foreach ($results->searchHits as $result) {
            $locations[] = $result->valueObject;
        }

        return $locations;
    }

    /**
     * @param int[] $parentLocationRemoteIds
     * @return Location[]
     */
    protected function findLocationsByParentLocationRemoteIds($parentLocationRemoteIds)
    {
        $locationIds = [];

        foreach ($parentLocationRemoteIds as $parentLocationRemoteId) {
            $location = $this->repository->getLocationService()->loadLocationByRemoteId($parentLocationRemoteId);
            $locationIds[] = $location->id;
        }

        return $this->findLocationsByParentLocationIds($locationIds);
    }
}