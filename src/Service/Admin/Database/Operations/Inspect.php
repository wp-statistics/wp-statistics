<?php

namespace WP_Statistics\Service\Admin\Database\Operations;

/**
 * Handles inspection of database tables.
 *
 * This class provides functionality to check the existence of a specified table
 * in the database.
 */
class Inspect extends AbstractTableOperation
{
    /**
     * The result of the table inspection query.
     *
     * @var mixed
     */
    private $result;

    /**
     * Executes the table inspection operation.
     *
     * @return $this
     */
    public function execute()
    {
        $this->validateTableName();
        $this->setFullTableName();

        $query = $this->wpdb->prepare("SHOW TABLES LIKE %s", $this->fullName);

        $this->result = $this->wpdb->get_var($query);

        return $this;
    }

    /**
     * Retrieves the result of the table inspection.
     *
     * @return mixed The result of the inspection query.
     */
    public function getResult()
    {
        return $this->result;
    }
}
