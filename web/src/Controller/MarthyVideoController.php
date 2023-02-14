<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\Parameter;
use App\Entity\Product;
use App\Service\Managers\CategoryManager;
use App\Service\Managers\PohodaManager;
use App\Service\Managers\ProductManager;
use Symfony\Component\HttpFoundation\Session\Session;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use Exception;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MarthyVideoController extends BaseController
{
    private ObjectManager $em;
    private ManagerRegistry $doctrine;

    public function __construct(ManagerRegistry $doctrine)
    {
        $this->doctrine = $doctrine;
        $this->em = $doctrine->getManager();
    }

    /**
     * @throws Exception
     * @Route("/pohoda", name="pohoda")
     */
    public function pohoda(): Response
    {
        $parametersArray = NULL;
        $categoriesArray = NULL;
        $productsArray = NULL;

        $parameters = $this->em->getRepository(Parameter::class)->findAll();
        $categories = $this->em->getRepository(Category::class)->findAll();
        $products = $this->em->getRepository(Product::class)->findAll();

        foreach ($parameters as $parameter) {
            $parametersArray[] = $parameter->getId();
        }

        foreach ($categories as $category) {
            $categoriesArray[] = $category->getId();
        }

        foreach ($products as $product) {
            $productsArray[] = $product->getId();
        }

        $pohoda = new PohodaManager($this->doctrine);
        $pohoda->removeData($this->em->getRepository(Parameter::class), $parametersArray)->getParameterXml('PohodaXML/parameters.xml');
        $pohoda->removeData($this->em->getRepository(Category::class), $categoriesArray)->getCategoryXml('PohodaXML/categories.xml');
        $pohoda->removeData($this->em->getRepository(Product::class), $productsArray)->getProductXml('PohodaXML/products.xml');

        return $this->render("MarthyVideo/pohoda.html.twig", [

        ]);
    }

    /**
     * @param $categoryUrl
     * @param $productId
     * @param $limit
     * @param CategoryManager $cm
     * @param ProductManager $pm
     * @return Response
     * @throws Exception
     * @Route("/{categoryUrl}/{productId}/{limit}", defaults={"categoryUrl" = "", "productId" = "", "limit" = 1}, name="marthy_default")
     */
    public function default($categoryUrl, $productId, $limit, CategoryManager $cm, ProductManager $pm): Response
    {
        $productsLatest[] = NULL;
        $productDetails[] = NULL;
        $parameterDetails[] = NULL;

        $categories = $cm->categoryData($this->em->getRepository(Category::class))->getData();
        $parameters = $this->em->getRepository(Parameter::class)->findAll();
        $products = $pm->productData($this->em->getRepository(Product::class), 9999)->getData($cm->categoryUrl($categories, $categoryUrl)->getId());

        if ($cm->categoryUrl($categories, $categoryUrl)->getId() == 0) {
            $productId = $categoryUrl;
            $categoryUrl = "";
        }

        if ($productId != NULL) {
            $productDetails = $pm->productDetails($this->em->getRepository(Product::class), $productId)->getData();
            $parameterDetails = $pm->productDetails($this->em->getRepository(Product::class), $productId)->getParametersData($productDetails);
        }

        $productsLatestArray = $pm->productLatest(NULL, $productId)->getDataArray();
        if ($categoryUrl == $productId || $categoryUrl == "") {
            $productsLatest = $pm->productLatest($this->em->getRepository(Product::class), $productId)->getData();
        }

        return $this->render("MarthyVideo/default.html.twig", [
            'currentUrl' => $categoryUrl,
            'productId' => $productId,
            'limit' => $limit,
            'products' => $products,
            'productsLatest' => $productsLatest,
            'productsLatestArray' => $productsLatestArray,
            'productDetails' => $productDetails,
            'categories' => $categories,
            'categoryTitle' => $cm->categoryUrl($categories, $categoryUrl)->getName(),
            'categoryId' => $cm->categoryUrl($categories, $categoryUrl)->getId(),
            'parameters' => $parameters,
            'paramInCategory' => $cm->categoryFilter($products)->getName(),
            'parameterValues' => $cm->categoryFilter($products)->getValue(),
            'parameterDetails' => $parameterDetails,
        ]);
    }
}