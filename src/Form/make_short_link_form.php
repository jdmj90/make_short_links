<?php

namespace Drupal\make_short_links\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Component\Utility\UrlHelper;
use Drupal\path_alias\PathAliasStorage;
use Drupal\Core\Language\Language;

class make_short_link_form extends FormBase	
{

	public function getFormID()
	{
		return 'make_short_link_form';
	}


	public function buildForm(array $form, FormStateInterface $form_state)
	{

		$form['short_link_url'] = 
			array(
				'#type' => 'textfield',
				'#title' => $this->t('URL'),
				'#description' => $this->t('Enter URL to be make into a short link, please include http(s):'),
				'#required' => TRUE,
			);
		$form['short_link_name'] = 
			array(
				'#type' => 'textfield',
				'#title' => $this->t('Short Link'),
				'#description' => $this->t('Enter the short vanity link you would like to use. Alphanumberical characters or underscore only'),
				//'#required' => TRUE,
			);	
		$form['submit'] = 
			array(
      			'#type' => 'submit',
      			'#value' => $this->t('Save'),
      			'#button_type' => 'primary',
    		);	

    	return $form;
	}

	public function validateForm(array &$form, FormStateInterface $form_state)
	{
		// add validation that url
		
		if(!UrlHelper::isValid($form_state->getValue('short_link_url'), TRUE))
		{
			$form_state->setErrorByName('short_link_url', 'There is a issue in the url. Please check it, and try again.');
		}
		
		//var_dump(print_r($form_state->getValue('short_link_url'), TRUE));
		
		if(!filter_var($form_state->getValue('short_link_url'), FILTER_VALIDATE_URL))
		{
			$form_state->setErrorByName('short_link_url', 'There is a issue in the url2. Please check it, and try again.');
		}
	
		/*if(!preg_match('|http(s)?://\w+|i', $form_state->getValue('short_link_url')))
		{
			$form_state->setErrorByName('short_link_url', 'There is a issue in the url3. Please check it, and try again.');	
		}
		*/

		
		//validate the name

		if(preg_match('[^A-Za-z0-9_]', $form_state->getValue('short_link_name')) !== 0 )
		{
			$form_state->setErrorByName('short_link_name', 'There is a issue in the short link name. Please make sure it is only Alphanumberical characters or underscore');
		}


		if(!empty($form_state->getValue('short_link_name')))
		{
			//does name already exist

			$connection = \Drupal::service('database');
			$retrieved_link = $connection
			  ->select('short_links', 'sl')
			  ->fields('sl')
			  ->condition('sl.short_link', $form_state->getValue('short_link_name'))
			  ->range(0,1);
			$retrieved_link = $retrieved_link->execute()->fetchAll();

			if(!empty($retrieved_link))
			{
				$form_state->setErrorByName('short_link_name', 'That name is already in use. Please Try a diffect name.');	
			}
		}
		

	}

	public function submitForm(array &$form, FormStateInterface $form_state)
	{
		$connection = \Drupal::service('database');


		// id a short name is given, use it
		if(!empty($form_state->getValue('short_link_name')))
		{
			$short_name = $form_state->getValue('short_link_name');
		}
		else
		{
		// else generate one
			$name_unique = FALSE;
			$alpha_numberics = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';	
			while($name_unique === FALSE)
			{

				$test_name = substr(str_shuffle($alpha_numberics), 0, 9);
				// and make sure its not in use already
				$check_link = $connection
				  ->select('short_links', 'sl')
				  ->fields('sl')
				  ->condition('sl.short_link', $test_name)
				  ->countQuery()->execute()->fetchField();
			  	if($check_link == 0)
			  	{
					$name_unique = TRUE;
				}
			}

			$short_name = $test_name;

		}

		//insert link
		$result = $connection->insert('short_links')
		  ->fields([
		    'short_link' => $short_name,
		    'short_link_url' => $form_state->getValue('short_link_url'),
		  ])
		  ->execute();

		//create short link alias
		$path_alias = \Drupal::entityTypeManager()->getStorage('path_alias')->create([
		  'path' => '/goto/'.$short_name,
		  'alias' => '/'.$short_name,
		  'langcode' => Language::LANGCODE_NOT_SPECIFIED,
		]);
		$path_alias->save();
    

		$form_state->setRedirect('make_short_links.view_url', array('short_link' => $short_name,));  
			
	}

}





