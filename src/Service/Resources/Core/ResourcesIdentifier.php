<?php

namespace WP_Statistics\Service\Resources\Core;

use WP_Statistics\Models\ResourceModel;

/**
 * Identifies and manages resource-related data.
 */
class ResourcesIdentifier
{
    /**
     * Holds an instance of ResourceDetector used to gather detailed resource information.
     *
     * @var ResourceDetector|null
     */
    private $detector;

    /**
     * The row ID associated with the resource.
     *
     * @var int|null
     */
    private $rowId = null;

    /**
     * The resource data retrieved from the database.
     *
     * @var object|null
     */
    public $resource = null;

    /**
     * Initializes the resource with its properties.
     *
     * @param int|null $rowId Unique identifier for the resource.
     */
    public function __construct($rowId = null)
    {
        $this->rowId = $rowId;

        if (! empty($this->rowId)) {
            $this->getRestource();
            return;
        }

        $this->setRestource();
    }

    /**
     * Retrieves the resource data from the database using the row ID.
     *
     * @return void
     */
    private function getRestource()
    {
        if (empty($this->rowId)) {
            return;
        }

        $this->resource = $this->getModel()->get(['ID' => $this->rowId]);
    }

    /**
     * Returns a ResourceModel instance based on the current resource data.
     *
     * This allows further manipulation of the resource record using the ResourceModel.
     *
     * @return ResourceModel
     */
    public function getModel()
    {
        return new ResourceModel($this->resource);
    }

    /**
     * Sets the resource record.
     *
     * @return void
     */
    private function setRestource()
    {
        $currentPage = home_url(add_query_arg(null, null));

        $this->resource = $this->getModel()->get(['resource_url' => $currentPage]);

        if (! empty($this->resource)) {
            return;
        }

        if (empty($this->detector)) {
            $this->detector = new ResourceDetector();
        }

        $insertId = $this->getModel()->insert([
            'resource_id'        => $this->detector->getResourceId(),
            'resource_type'      => $this->detector->getResourceType(),
            'resource_url'       => $currentPage,
            'cached_title'       => $this->detector->getCachedTitle(),
            'cached_terms'       => $this->detector->getCachedTerms(),
            'cached_author_id'   => $this->detector->getCachedAuthorId(),
            'cached_author_name' => $this->detector->getCachedAuthorName(),
            'cached_date'        => $this->detector->getCachedDate(),
            'resource_meta'      => $this->detector->getResourceMeta(),
        ]);

        if (empty($insertId)) {
            return;
        }

        $this->resource = $this->getModel()->get(['ID' => $insertId]);
    }
}
