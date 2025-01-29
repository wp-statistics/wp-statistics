<?php
namespace WP_Statistics\Service\Admin\Database;

use WP_Statistics\Service\Admin\Database\DatabaseFactory;
use WP_Statistics\Service\Admin\Database\Managers\TableHandler;

abstract class AbstractTable
{
    protected $tableName;

    /**
     * Returns the name of the table.
     * @return string
     */
    public function getName()
    {
        return $this->tableName;
    }

    /**
     * Returns the schema for the table.
     * @return array
     */
    abstract public function getSchema();

    /**
     * Creates the table in the database if it does not already exist.
     * @return void
     */
    public function create()
    {
        $inspect = DatabaseFactory::table('inspect')
            ->setName($this->getName())
            ->execute();

        if (!$inspect->getResult()) {
            TableHandler::createTable(
                $this->getName(),
                $this->getSchema()
            );
        }
    }

    /**
     * Drop the table, if it exists.
     * @return void
     */
    public function drop()
    {
        $inspect = DatabaseFactory::table('inspect')
            ->setName($this->getName())
            ->execute();

        if ($inspect->getResult()) {
            TableHandler::dropTable($this->getName());
        }
    }
}