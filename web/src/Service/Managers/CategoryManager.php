<?php

namespace App\Service\Managers;

use Exception;

class CategoryManager extends AbstractManager
{
    /**
     * @var false
     */
    private bool $functionCategoryDataLoaded;

    public function __construct()
    {
        $this->functionCategoryDataLoaded = false;
    }

    public function categoryData($repository): CategoryData
    {
        $this->functionCategoryDataLoaded = true;
        return new CategoryData($repository);
    }

    /**
     * @throws Exception
     */
    public function categoryUrl($categoriesList, $currentUrl): CategoryUrl
    {
        if ($this->functionCategoryDataLoaded) {
            return new CategoryUrl($categoriesList, $currentUrl); //$this->categoryData($this->repository)
        } else {
            throw new Exception('You need to call categoryData() function first!');
        }
    }

    public function categoryFilter($products): CategoryFilter
    {
        return new CategoryFilter($products);
    }
}

class CategoryData extends AbstractData
{
    public function getData()
    {
        return $this->repository->createQueryBuilder('c')
            ->addOrderBy('CASE 
            WHEN c.id = 146 THEN 1
            WHEN c.id = 23 THEN 2
            WHEN c.id = 26 THEN 3
            WHEN c.id = 27 THEN 4
            WHEN c.id = 38 THEN 5
            WHEN c.id = 39 THEN 6
            WHEN c.id = 44 THEN 7
            WHEN c.id = 45 THEN 8
            WHEN c.id = 4 THEN 9
            WHEN c.id = 46 THEN 10
            WHEN c.id = 47 THEN 11
            WHEN c.id = 61 THEN 12
            WHEN c.id = 48 THEN 13
            WHEN c.id = 49 THEN 14
            WHEN c.id = 50 THEN 15
            WHEN c.id = 51 THEN 16
            WHEN c.id = 52 THEN 17
            WHEN c.id = 56 THEN 18
            WHEN c.id = 53 THEN 19
            WHEN c.id = 90 THEN 20
            WHEN c.id = 79 THEN 21
            WHEN c.id = 70 THEN 22
            ELSE c
            END', 'ASC')
            ->getQuery()
            ->getResult();
    }
}

class CategoryUrl
{
    private array $categories;
    private string $currentUrl;

    public function __construct($categories, $currentUrl) {
        $this->categories = $categories;
        $this->currentUrl = $currentUrl;
    }

    public function getId(): int
    {
        $catId = 0;

        foreach ($this->categories as $category) {
            $catUrlName = $category->getUrlName();
            if ($catUrlName == $this->currentUrl) {
                $catId = $category->getId();
            }
        }
        return $catId;
    }

    public function getName(): string
    {
        $catName = "Produkty";

        foreach ($this->categories as $category) {
            $catUrlName = $category->getUrlName();
            if ($catUrlName == $this->currentUrl) {
                $catName = $category->getName();
            }
        }
        return $catName;
    }
}

class CategoryFilter
{
    private array $products;

    public function __construct($products)
    {
        $this->products = $products;
    }

    public function getName(): array
    {
        $output = array();
        $seen = array();

        foreach ($this->products as $product) {
            $categoriesList = $product->getCategoryId();
            $parametersList = $product->getParameterId();

            foreach ($parametersList as $parameter) {
                foreach ($categoriesList as $category) {
                    $array = array(
                        array($category => $parameter)
                    );

                    foreach ($array as $value) {
                        if (in_array($value, $seen)) {
                            continue;
                        }
                        $seen[] = $value;
                        $output[] = $value;
                    }
                }
            }
        }
        return $output;
    }

    public function getValue() {
        $arrayId = array();
        $arrayValue = array();
        $array = array();
        $output = array();

        foreach ($this->products as $product) {
            $parameterValueList = $product->getParameterValue();
            $parametersList = $product->getParameterId();

            foreach ($parametersList as $parameter) {
                $arrayId[] = array($parameter);
            }

            foreach ($parameterValueList as $parameterValue) {
                $arrayValue[] = array($parameterValue);
            }
        }

        $count = 0;
        while ($count <= count($arrayId)) {
            if (array_key_exists($count, $arrayId)) {
                $array[] = array_combine($arrayId[$count], $arrayValue[$count]);
            }
            $count++;
        }

        $count = 1;

        foreach ($array as $item) {
            foreach ($item as $key => $value) {
                foreach ($arrayValue as $val) {
                    foreach ($val as $a) {
                        if ($value == $a) {
                            $count++;
                            $output[$key][$value] = $count;
                        }
                    }
                }
                $count = 0;
            }
        }

        return $output;
    }
}