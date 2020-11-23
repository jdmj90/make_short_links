<?php

namespace Drupal\make_short_links\Controller;


use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;
use Drupal\Core\Routing\TrustedRedirectResponse;

class go_to_short_linkController extends ControllerBase	
{

	public function content($short_link)
	{
		$cleaned_link = preg_replace("/[^a-zA-Z0-9_]/", "", $short_link);
		$connection = \Drupal::service('database');
		$retrieved_link = $connection
		  ->select('short_links', 'sl')
		  ->fields('sl')
		  ->condition('sl.short_link', $cleaned_link)
		  ->range(0,1)
		  ->execute()
		  ->fetchAll();

		// 404 if link isnt found
		if(empty($retrieved_link))
		{
			throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
		}
		else
		{
			return new TrustedRedirectResponse($retrieved_link[0]->short_link_url);
		}
		
	}
}