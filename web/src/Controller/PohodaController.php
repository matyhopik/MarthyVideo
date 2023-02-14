<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\Human;
use App\Entity\Parameter;
use App\Entity\Product;
use App\Repository\ProductRepository;
use App\Service\PohodaManager;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Doctrine\Persistence\ManagerRegistry;
use SimpleXMLElement;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpClient\HttpClient;
//use GuzzleHttp\Client;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use XMLReader;
use function Symfony\Component\DependencyInjection\Loader\Configurator\param;

class PohodaController extends AbstractController
{
    public function __construct(ManagerRegistry $doctrine)
    {
        $this->em = $doctrine->getManager();
    }
    /**
     * @return Response
     * @Route("/a", name="homepage_default")
     * @throws \Exception
     */
    public function default(ManagerRegistry $doctrine): Response
    {
        $stock = 0;
        $paramIds = 0;

        $categoryPath = 'PohodaXML/categories.xml';
        $parameterPath = 'PohodaXML/parameters.xml';
        $productPath = 'PohodaXML/products.xml';


        $pohoda = new PohodaManager($doctrine);
        //$pohoda->getCategoryXml($categoryPath);
        //$pohoda->getParametersXml($parameterPath);
        //$pohoda->getProductXml($productPath);

        $productRepository = $this->em->getRepository(Product::class);
        $parameterRepository = $this->em->getRepository(Parameter::class);
        $categoryRepository = $this->em->getRepository(Category::class);

        $products = $productRepository->findAll();
        $parameter = $parameterRepository->findAll();
        $category = $categoryRepository->findAll();



        return $this->render("Homepage/default.html.twig", [
            'products' => $products,
            'stocks' => $stock,
            'parameters' => $parameter,
            'categories' => $category,
            ]);
    }
}
