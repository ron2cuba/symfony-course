<?php
namespace App\Controller;

use App\Taxes\Detector;
use App\Taxes\Calculator;
use Cocur\Slugify\Slugify;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TestController
{
    /**
     * @Route("/", name="index")
     */
    public function index()
    {
        dump("Yeah !");
        return new Response("Yeah baby!");
    }

    /**
     * @Route("/test/{param<\w+>?yeah}", name="test")
     */
    public function test($param, LoggerInterface $logger, Slugify $slugify)
    {
        $slugify = new Slugify;

        dump($slugify->slugify("Hello baby!"));
        
        dump("test !");
        return new Response("$param baby!");
    }

    /**
     * ne pas oublier de mettre $name en parametre de la function
     * @Route("/hello/{name?World}", name="hello")
     */

    public function hello($name, loggerInterface $logger, Calculator $calculator, Detector $detector)
    {
        dump($detector->detect(101));
        dump($detector->detect(10));
        
        $logger->info("Mesage de log !");

        $tva = $calculator->calcul(100);

        dump($tva);
        
        return new Response("Hello $name");
    }
}