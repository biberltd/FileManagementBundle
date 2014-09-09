<?php

namespace BiberLtd\Bundle\FileManagementBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('BiberLtdFileManagementBundle:Default:index.html.twig', array('name' => $name));
    }
}
