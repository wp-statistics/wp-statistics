<?php
namespace WP_Statistics\Abstracts;

abstract class BaseMetabox
{
    abstract public function getKey();

    abstract public function getName();

    abstract public function isActive();

    abstract public function getPriority();

    abstract public function showOnDashboard();

    abstract public function showOnOverview();

    abstract public function getOptions();

    abstract public function render();
}