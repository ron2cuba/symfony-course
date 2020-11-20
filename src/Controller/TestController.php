<?php
namespace App\Controller;

use Cocur\Slugify\Slugify;
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
    public function test($param)
    {
        $slugify = new Slugify;

        dump($slugify->slugify("Hello baby!"));
        
        dump("test !");
        return new Response("$param baby!");
    }
}