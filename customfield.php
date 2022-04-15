<?php
/**
 * @package     Joomla.Plugin
 *
 */

defined('_JEXEC') or die;

use Joomla\CMS\Form\Form;
use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Database\ParameterType;
use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Table\Table;

/**
 * A plugin to add custom form fields in the article view
 *
 */
class PlgContentcustomfield extends CMSPlugin
{

	/**
	 * @var    \Joomla\Database\DatabaseDriver
	 *
	 */
	protected $db;

	/**
	 * Load the language file on instantiation.
	 *
	 * @var    boolean
	 *
	 */
	protected $autoloadLanguage = true;
	

	/**
	 * Runs on content preparation
	 *
	 * @param   string  $context  The context for the data
	 * @param   object  $data     An object containing the data for the form.
	 *
	 * @return  boolean
	 *
	 */
	public function onContentPrepareData($context, $data)
	{
		// Check we are manipulating a valid form.
		if (!in_array($context, ['com_content.article']))
		{
			return true;
		}
		if (is_object($data))
		{
			$articleId = $data->id ?? 0;
			//Check if the form already has some data
			if (!isset($data->customfield) && $articleId > 0)
			{
				// Load the table data from the database
				$db = $this->db;
				$query = $db->getQuery(true)
					->select('*')
					->from($db->quoteName('#__customform'))
					->where('articleId = '.$articleId);
				$db->setQuery($query);
				$results = $db->loadAssoc();
				// Insert existing data into form fields
				$data->customfield = [];
				if(is_array($results)||is_object($results)){
					foreach ($results as $k=>$v)
					{
						$data->customfield[$k]=$v;
					}
				}
				else{
					//Insert article id as it is a hidden field
					$data->customfield = [];
					$data->customfield['articleId']=$articleId;
				}
			}
			else{
				//Insert article id as it is a hidden field
				$data->customfield = [];
				$data->customfield['articleId']=$articleId;
			}
		}
		return true;

	}	



	/**
	 * Adds custom fields to the article view
	 *
	 * @param   Form   $form  The form to be altered.
	 * @param   mixed  $data  The associated data for the form.
	 *
	 * @return  boolean
	 *
	 */
	public function onContentPrepareForm(Form $form, $data)
	{
		// Check we are manipulating a valid form
		$name = $form->getName();

		if (!in_array($name, ['com_content.article']))
		{
			return true;
		}

		//Load the form fields
		FormHelper::addFormPath(__DIR__ . '/forms');
		$form->loadFile('customfield');
		
		return true;
	}


	/**
	 * Saves form field data in the database
	 *
	 * @param   string   $context    Context of the content being passed
	 * @param   mixed	 $article    Reference to the JTableContent object that is being saved
	 * @param   boolean  $isNew  	 Set to true if the content is about to be created
	 * @param	mixed	 $data		 The data to be saved
	 *
	 * @return  boolean
	 *
	 */
	public function onContentBeforeSave($context, &$article, $isNew, $data)
	{	
		//Check if $data has the form data
		if (isset($data['customfield']) && count($data['customfield']))
		{
			$db = $this->db;
			//Delete the existing row to add updated data 
			if(!$isNew){
				$res=$db->getQuery(true)
				->delete($db->quoteName('#__customform'))
				->where('articleId = '.$article->id);
				$db->setQuery($res);
				$result = $db->execute();
			}
			//Create object to insert data into database
			$query=new stdClass();
			foreach($data['customfield'] as $k=>$v){
				$query->$k=$v;
			}
			$result=$db->insertObject('#__customform', $query);
		}
		return true;	
	}


	/**
	 * Displays the value of custom form fields in the frontend below the article
	 *
	 * @param   string   $context    Context of the content being passed
	 * @param   mixed	 $article    Article that is being rendered by the view
	 * @param   mixed  	 $params  	 JRegistry object of merged article and menu item params
	 * @param	int 	 $limitstart Integer that determines the "page" of the content that is to be generated
	 *
	 * @return  string
	 *
	 */
	public function onContentAfterDisplay($context,&$article,&$params,&$limitstart)
	{
		// Check we are manipulating a valid form.
		if (!in_array($context, ['com_content.article','com_content.category','com_content.categories']))
			return;

		$articleId=$article->id;
		$db = $this->db;
		$query = $db->getQuery(true)
			->select('*')
			->from($db->quoteName('#__customform'))
			->where('articleId = '.$articleId);
		$db->setQuery($query);
		//Storing output result in the form of associative array
		$results = $db->loadAssoc();
		if(is_array($results)||is_object($results)){
			$str='';
			foreach ($results as $k=>$v)
			{
				if($k!='articleId')
					$str.="<p>".$k." : ".$v."</p>";
			}
			//Display the custom form field data
			return $str;
		}
	}
}
