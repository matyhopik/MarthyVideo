<?php

namespace App\Service\Managers;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query\Parameter;
use Symfony\Component\HttpFoundation\Session\Session;

class ProductManager extends AbstractManager
{
    public function productData($repository, $limit = 0): ProductData
    {
        return new ProductData($repository, $limit);
    }

    public function productDetails($repository, $productId): ProductDetails
    {
        return new ProductDetails($repository, $productId);
    }

    public function productLatest($repository, $productId): ProductLatest
    {
        return new ProductLatest($repository, $productId);
    }
}

class ProductData extends AbstractData
{
    private int $limit;

    public function __construct($repository, $limit = 0)
    {
        parent::__construct($repository);
        $this->limit = $limit;
    }

    public function getData($currentUrlId)
    {
        return $this->repository->createQueryBuilder('p')
            ->where('p.categoryId LIKE :categoryCenter')
            ->orWhere('p.categoryId LIKE :categoryStart')
            ->orWhere('p.categoryId LIKE :categoryEnd')
            ->addOrderBy('p.name', 'ASC')
            ->setMaxResults($this->limit * 2)
            ->setParameters(New ArrayCollection(array(
                new Parameter('categoryCenter', '%,' . $currentUrlId . ',%'),
                new Parameter('categoryStart', '%,' . $currentUrlId . ']%'),
                new Parameter('categoryEnd', '%[' . $currentUrlId . ',%')
            )))
            ->getQuery()
            ->getResult();
    }
}

class ProductDetails extends AbstractData
{
    private mixed $productId;

    public function __construct($repository, $productId)
    {
        parent::__construct($repository);
        $this->productId = $productId;
    }

    public function getData()
    {
        return $this->repository->find($this->productId);
    }

    public function getParametersData($productDetails): array
    {
        $array = array();
        if ($this->productId != "") {
            $arrayId = array();
            $arrayValue = array();

            foreach ($productDetails->getParameterId() as $parameter) {
                $arrayId[] = array($parameter);
            }

            foreach ($productDetails->getParameterValue() as $parameterValue) {
                $arrayValue[] = array($parameterValue);
            }

            $count = 0;
            while ($count <= count($arrayId)) {
                if (array_key_exists($count, $arrayId)) {
                    $array[] = array_combine($arrayId[$count], $arrayValue[$count]);
                }
                $count++;
            }
        }

        return $array;
    }
}

class ProductLatest extends AbstractData
{
    private mixed $productId;

    public function __construct($repository, $productId)
    {
        parent::__construct($repository);
        $this->productId = $productId;
    }

    public function getDataArray()
    {
        $session = new Session();
        if (session_status() == PHP_SESSION_NONE) {
            $session->start();
        }

        $array = $session->get('my_array', []);

        if ($this->productId != "") {
            $key = array_search($this->productId, $array);
            if ($key !== false) {
                unset($array[$key]);
                $array = array_values($array);
            }
            if (count($array) > 9) {
                array_shift($array);
            }
            $array[] = $this->productId;
        }

        $session->set('my_array', $array);
        //$session->clear('my_array');

        return $array;
    }

    public function getData()
    {
        return $this->repository->findBy(['id' => $this->getDataArray()]);
    }
}