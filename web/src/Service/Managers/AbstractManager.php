<?php

namespace App\Service\Managers;


class AbstractManager
{

}

class AbstractData
{
    public $repository;

    public function __construct($repository)
    {
        $this->repository = $repository;
    }
}