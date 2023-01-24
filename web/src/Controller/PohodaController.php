<?php

namespace App\Controller;

use App\Entity\Human;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;
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

class PohodaController extends AbstractController
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @return Response
     * @Route("/", name="homepage_default")
     */
    public function default(): Response
    {
        // Vytahnuti xml souboru
        $fs = new Filesystem();
        $filePath = 'test.xml';

        if (!$fs->exists($filePath)) {
            throw new Exception('Xml file not found');
        }

        $xml = simplexml_load_file($filePath);

        $array = json_decode(json_encode($xml), true);
        $object = $array['HumanGroup']['Human'];

        foreach($object as $item){
            $entity = new Human();
            $entity->setName($item['name']);
            $entity->setAge($item['age']);
            // ...
            $this->em->persist($entity);
        }
        //$this->em->flush();

        $human = $this->em->getRepository(Human::class)->findAll();

        return $this->render("Homepage/default.html.twig", [
            'humans' => $human
        ]);
    }
}
