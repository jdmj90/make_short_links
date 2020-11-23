<?php

namespace Drupal\make_short_links\Controller;


use Drupal\Core\Controller\ControllerBase;

class make_short_linksController extends ControllerBase	
{

	public function content()
	{
		return [
			'#type' => 'markup',
			'#markup' => $this->t('Hello, World!'),
		];
	}
}