<?php

namespace Koala\ContentBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Koala\ContentBundle\Entity\Page;
use Koala\ContentBundle\Entity\Region;
use Koala\ContentBundle\Type\PageType;

class ContentController extends Controller
{
	/**
	 * @Route("/new", name="page_new")
	 * @Template()
	 */
	public function newAction(Request $request)
	{
		$page = new Page();
		$form = $this->createForm(new PageType(), $page);

		if ($request->getMethod() == 'POST')
		{
			$form->bindRequest($request);

			if ($form->isValid())
			{
				$em = $this->getDoctrine()->getEntityManager();
				$em->persist($page);
				$em->flush();
			}
		}

		return array('form'=>$form->createView());
	}

	/**
	 * @Route("/content/{url}", defaults={"url"="/"}, requirements={"url"=".*"})
	 * @Method("GET")
	 * @Template()
	 */
	public function pageAction($url = "/")
	{
		$repo = $this->getDoctrine()
			->getRepository('KoalaContentBundle:Page');
		$page = $repo
			->findOneByUrl($url);

		if (!$page) {
			throw $this->createNotFoundException('404 - Not found!');
		}

		$regions = array();
		foreach ($page->getRegions() as $r)
		{
			$regions[$r->getName()] = $r->getContent();
		}

		$factory = $this->container->get('knp_menu.factory');
		$menu = $factory->createItem('root');
		$menu->setCurrentUri($this->container->get('request')->getRequestUri());
		foreach ($repo->getRootNodes() as $root)
		{
			$menu->addChild($factory->createFromNode($root));
		}

		return array('page' => $page, 'regions' => $regions, 'menu'=>$menu);
	}
}
