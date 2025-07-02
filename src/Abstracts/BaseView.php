<?php

namespace WP_Statistics\Abstracts;


abstract class BaseView
{
    protected $dataProvider;

    abstract protected function render();    
}