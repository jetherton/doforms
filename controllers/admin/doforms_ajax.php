<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Main DoForms Ajax Controller
 * 
 * This plugin was written for ICRC by Etherton Technologies
 * 2011
 *
 * @package    DoForms plugin
 * @author     John Etherton, Etherton Technologies <john@ethertontech.com>
 * 
 */ 

class Doforms_Ajax_Controller extends Admin_Controller
{
	/**
	 * Called to add a new DoForm URL
	 * Enter description here ...
	 */
	public function add_new()
	{
		$this->auto_render = FALSE;
		$this->template = "";
	
		//check and see if there are other do form settings out there
		$is_first = false;
		$count = ORM::factory("doforms")->count_all();		
		if($count<1)
		{
			$is_first = true;
		}
		
		$doform_setting = ORM::factory("doforms");
		$doform_setting->url = " ";
		$doform_setting->save();
		
		//get the available Ushahidi forms
		$available_forms_db = ORM::factory("form")->where("form_active", 1)->find_all();
		$available_forms = array();
		
		foreach($available_forms_db as $form)
		{
			$available_forms[$form->id] = $form->form_title;
		}
		
		//get the available Ushahidi Categories
		$available_categories_db = ORM::factory("category")->where("category_visible", 1)->find_all();
		$available_categories = array(-1=>"---".Kohana::lang("doforms.none")."---");
		foreach($available_categories_db as $cat)
		{
			$available_categories[$cat->id] = $cat->category_title;
		}
		
		
		
		$view = new View("doforms/admin/doform_setting");
		$view->available_forms = $available_forms;
		$view->available_categories = $available_categories;
		$view->id = $doform_setting->id;
		$view->active = $doform_setting->active;
		$view->selected_form = $doform_setting->form_id;
		$view->selected_cat = -1;
		$view->url = "";
		$view->is_first = $is_first;
		$view->show_map_fields = false;
		$view->publish = 1;
		$view->render(TRUE);		
		

	}
	
	/**
	 * Called to update a DoForm url
	 *
	 * @param unknown_type $id - ID of the entry in the database
	 * @param unknown_type $url - new value of the URL
	 */
	public function save()
	{
		$this->auto_render = FALSE;
		$this->template = "";
		
		
		
		//make sure we've got valid data coming in
		if(isset($_POST["id"]))
		{
			$id = $_POST["id"]; 
		}
		else
		{
			echo '<span style="background:#ffaaaa;color:#880000; border:solid 1px #880000; padding:5px;">'.Kohana::lang("doforms.error_doform_id_not_set").'</span>';
			return;
		}
		
		if(isset($_POST["url"]))
		{
			$url = $_POST["url"]; 
		}
		else
		{
			echo '<span style="background:#ffaaaa;color:#880000; border:solid 1px #880000; padding:5px;">'.Kohana::lang("doforms.error_url_not_set").'</span>';
			return;	
		}
		
		if(isset($_POST["active"]))
		{
			$active = $_POST["active"]; 
		}
		else
		{
			echo '<span style="background:#ffaaaa;color:#880000; border:solid 1px #880000; padding:5px;">'.Kohana::lang("doforms.error_active_not_set").'</span>';
			return;	
		}
		
		if(isset($_POST["formId"]))
		{
			$form_id = $_POST["formId"]; 
		}
		else
		{
			echo '<span style="background:#ffaaaa;color:#880000; border:solid 1px #880000; padding:5px;">'.Kohana::lang("doforms.error_form_id_not_set").'</span>';
			return;	
		}
		
		if(isset($_POST["publish"]))
		{
			$publish = $_POST["publish"]; 
		}
		else
		{
			$publish = 0;
		}
		
		if(isset($_POST["category"]))
		{
			$category = $_POST["category"]; 
		}
		else
		{
			$category = -1;	
		}

		//save the changes to the database
		$doform_setting = ORM::factory("doforms")->where("id", $id)->find();
		$doform_setting->url = trim($url);
		$doform_setting->active = $active;
		$doform_setting->form_id = $form_id;
		$doform_setting->publish = $publish;
		$doform_setting->category = $category;
		$doform_setting->save();
		
		//check the URL to make sure it's valid
		$form_fields = doforms_parser::getFormFields($url);
		if(!is_array($form_fields))
		{
			echo '<div style="background:#ffaaaa;color:#880000; border:solid 1px #880000; padding:5px;">'.Kohana::lang("doforms.error").': '.$form_fields.'</div>';
			return;
		}
		$form_fields_count = count($form_fields);
		if($form_fields_count > 0)
		{
			echo '<div style="background:#aaffaa;color:#008800; border:solid 1px #008800; padding:5px;">'.
				Kohana::lang("doforms.saved_form_has"). ': '. $form_fields_count.' '. Kohana::lang("doforms.fields").'</div>';
			return;
		}
		else
		{
			echo '<div style="background:#ffaaaa;color:#880000; border:solid 1px #880000; padding:5px;">'.Kohana::lang("doforms.saved_zero_fields").'</div>';
			return;	
		}
	}
	
	
	/**
	 * Used to delete a do form setting
	 * Pass in ID as a url param
	 */
	function delete()
	{
		$this->auto_render = FALSE;
		$this->template = "";
		
		if(isset($_GET["id"]))
		{
			$id = $_GET["id"]; 
		}
		else
		{
			echo "error";
			return;
		}
		
		$doform_setting = ORM::factory("doforms")->where("id", $id)->delete_all();
	}
	
	
	/**
	 * Used to Synchronize a form with Ushahidi
	 * @param unknown_type $id the ID of the form in question
	 */
	function sync_forms($id)
	{
		$this->auto_render = FALSE;
		$this->template = "";
		
		$retVal = doforms_parser::sync($id);
		
		echo $retVal;
	}
	
}