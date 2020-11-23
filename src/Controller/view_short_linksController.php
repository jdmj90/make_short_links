<?php

namespace Drupal\make_short_links\Controller;


use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\URL;
use Drupal\Core\Link;

class view_short_linksController extends ControllerBase	
{

	public function content($short_link)
	{

		$connection = \Drupal::service('database');
		$retrieved_link = $connection
		  ->select('short_links', 'sl')
		  ->fields('sl')
		  ->condition('sl.short_link', $short_link)
		  ->range(0,1);
		
		$retrieved_link = $retrieved_link->execute()->fetchAll();

		if(empty($retrieved_link))
		{
			throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
		}

		//create hyperlink
		$hyperlink = Link::fromTextAndUrl($retrieved_link[0]->short_link_url, Url::fromUri($retrieved_link[0]->short_link_url));

		//create QR code
		$page_url = urlencode($retrieved_link[0]->short_link_url);
        $url = "http://chart.apis.google.com/chart?chs=512x512&cht=qr&chl={$page_url}";

		return [
			'#type' => 'markup',

			'#markup' =>'<div>'.$hyperlink->toString().'</div><div><img src='.$url.' /></div>',
			//'#markup' => '<div style="width:40%;display:inline-block;">'.$hyperlink->toString().'</div><div style="width:40%;display:inline-block;"><img src='.$url.' /></div>',
		];
	}
}

