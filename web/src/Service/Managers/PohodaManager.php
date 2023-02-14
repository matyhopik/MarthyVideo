<?php

namespace App\Service\Managers;

use App\Entity\Category;
use App\Entity\Parameter;
use App\Entity\Product;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use Exception;
use SimpleXMLElement;
use XMLReader;

class PohodaManager extends AbstractManager
{
    private ObjectManager $em;

    public function __construct(ManagerRegistry $doctrine)
    {
        $this->em = $doctrine->getManager();
    }

    public function removeData($repository, $dataIds): static
    {
        foreach ($dataIds as $dataId) {
            $entity = $repository->find($dataId);
            $this->em->remove($entity);
            $this->em->flush();
        }

        return $this;
    }

    /**
     * @throws Exception
     */
    public function getProductXml($xmlPath): void
    {
        $xml = new XMLReader();
        $xml->open($xmlPath);
        $overloadCount = 0;

        $categoryId = [];
        $parameterId = [];
        $parameterValue = [];

        while ($xml->read()) {
            $product = new Product();
            $metadata = $this->em->getClassMetaData(get_class($product));
            $metadata->setIdGeneratorType(ClassMetadataInfo::GENERATOR_TYPE_NONE);

            if ($xml->nodeType == XMLReader::ELEMENT && $xml->name === 'lStk:stock') {
                $xml->read();

                if ($xml->nodeType == XMLReader::ELEMENT && $xml->name === 'stk:stockHeader') {
                    $node = new SimpleXMLElement($xml->readOuterXML());

                    $id = (int)$node->xpath('stk:id')[0];
                    $code = (string)$node->xpath('stk:code')[0];
                    $name = (string)$node->xpath('stk:name')[0];

                    if (isset($node->xpath('stk:description')[0])) {
                        $description = (string)$node->xpath('stk:description')[0];
                    } else {
                        $description = null;
                    }

                    if (isset($node->xpath('stk:pictures/stk:picture/stk:filepath')[0])) {
                        $pictureUrl = (string)$node->xpath("stk:pictures/stk:picture[@default='true']/stk:filepath")[0];
                    } else {
                        $pictureUrl = "";
                    }

                    if (isset($node->xpath('stk:count')[0])) {
                        $inStock = (int)$node->xpath('stk:count')[0];
                    } else {
                        $inStock = 0;
                    }

                    $inReservation = (int)$node->xpath('stk:reservation')[0];

                    if (isset($node->xpath('stk:categories')[0])) {
                        $elements = $node->xpath("stk:categories/stk:idCategory");
                        foreach ($elements as $element) {
                            $categoryId[] = (int)$element;
                        }
                    }

                    if (isset($node->xpath('stk:intParameters/stk:intParameter')[0])) {
                        $elements = $node->xpath("stk:intParameters/stk:intParameter/stk:intParameterID");
                        foreach ($elements as $element) {
                            $parameterId[] = (int)$element;
                        }
                    }

                    if (isset($node->xpath('stk:intParameters/stk:intParameter/stk:intParameterID')[0])) {
                        $elements = $node->xpath("stk:intParameters/stk:intParameter/stk:intParameterValues/stk:intParameterValue/stk:parameterValue");
                        foreach ($elements as $element) {
                            $parameterValue[] = (string)$element;
                        }
                    }

                    $product->setId($id);
                    $product->setCode($code);
                    $product->setName($name);
                    $product->setDescription($description);
                    $product->setPictureUrl($pictureUrl);
                    $product->setInStock($inStock);
                    $product->setInReservation($inReservation);
                    $product->setCategoryId($categoryId);
                    $product->setParameterId($parameterId);
                    $product->setParameterValue($parameterValue);
                }

                $this->em->persist($product);
                $categoryId = [];
                $parameterId = [];
                $parameterValue = [];
                $overloadCount++;
            }
            if ($overloadCount % 10 === 0) {
                $this->em->flush();
                $this->em->clear();
                $overloadCount = 0;
            }
        }
        $this->em->flush();
    }

    /**
     * @throws Exception
     */
    public function getCategoryXml($xmlPath): void
    {
        $xml = new XMLReader();
        $xml->open($xmlPath);
        $overloadCount = 0;
        $parentId = null;

        $czLetters = array('ě', 'š', 'č', 'ř', 'ž', 'ý', 'á', 'í', 'é', 'ň', 'Ř');
        $enLetters = array('e', 's', 'c', 'r', 'z', 'y', 'a', 'i', 'e', 'n', 'r');

        while ($xml->read()) {
            $category = new Category();
            $metadata = $this->em->getClassMetaData(get_class($category));
            $metadata->setIdGeneratorType(ClassMetadataInfo::GENERATOR_TYPE_NONE);

            if ($xml->nodeType == XMLReader::ELEMENT && $xml->name === 'ctg:category') {
                $node = new SimpleXMLElement($xml->readOuterXML());

                if ($xml->depth > 4) {
                    // Sub Category
                    $id = (int)$node->xpath('ctg:id')[0];
                    $name = (string)$node->xpath('ctg:name')[0];
                    $urlName = strtolower(str_replace('---', '-', str_replace($czLetters, $enLetters, str_replace(',', '', str_replace(' ', '-', $name))))) . '-' . $parentId;

                    $category->setId($id);
                    $category->setParentId($parentId);

                } else {
                    // Parent Category
                    $parentId = (int)$node->xpath('ctg:id')[0];
                    $name = (string)$node->xpath('ctg:name')[0];
                    $urlName = strtolower(str_replace('---', '-', str_replace($czLetters, $enLetters, str_replace(',', '', str_replace(' ', '-', $name)))));

                    $category->setId($parentId);

                }

                $category->setName($name);
                $category->setUrlName($urlName);
                $this->em->persist($category);

                $overloadCount++;
            }

            if ($overloadCount % 10 === 0) {
                $this->em->flush();
                $this->em->clear();
                $overloadCount = 0;
            }
        }
        $this->em->flush();
    }

    /**
     * @throws Exception
     */
    public function getParameterXml($xmlPath): void
    {
        $xml = new XMLReader();
        $xml->open($xmlPath);
        $overloadCount = 0;

        while ($xml->read()) {
            $parameter = new Parameter();

            $metadata = $this->em->getClassMetaData(get_class($parameter));
            $metadata->setIdGeneratorType(ClassMetadataInfo::GENERATOR_TYPE_NONE);

            if ($xml->nodeType == XMLReader::ELEMENT && $xml->name === 'ipm:intParam') {
                $node = new SimpleXMLElement($xml->readOuterXML());

                $id = (int)$node->xpath('ipm:id')[0];
                $name = (string)$node->xpath('ipm:name')[0];
                $parameterType = (string)$node->xpath('ipm:parameterType')[0];

                $parameter->setId($id);
                $parameter->setName($name);
                $parameter->setParameterType($parameterType);

                $this->em->persist($parameter);
                $overloadCount++;
                dump($id);
            }
            if ($overloadCount % 10 === 0) {
                $this->em->flush();
                $this->em->clear();
                $overloadCount = 0;
            }
        }
        $this->em->flush();
    }
}