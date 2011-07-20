<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Main DoForms Settings Controller
 * 
 * This plugin was written for ICRC by Etherton Technologies
 * 2011
 *
 * @package    DoForms plugin
 * @author     John Etherton, Etherton Technologies <john@ethertontech.com>
 * 
 */ 

class Doforms_Settings_Controller extends Admin_Controller
{
	public function index()
	{
		$this->template->this_page = 'addons';

		//set up the CSS
		plugin::add_stylesheet("doforms/css/doforms");
		
		//add some extra java script to make things cool and ajaxy
		$this->template->js = new View('doforms/admin/doforms_settings_js');
		
		// Standard Settings View
		$this->template->content = new View("doforms/admin/doforms_settings");
		

		//get the settings that we already have
		$doforms_settings = ORM::factory("doforms")->find_all();
		
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
		
		
		$this->template->content->available_forms = $available_forms;
		$this->template->content->available_categories = $available_categories;
		$this->template->content->doforms_settings = $doforms_settings;

	}
	
	public function map_fields($saved = false)
	{
		
		//set up the CSS
		plugin::add_stylesheet("doforms/css/doforms");
		
		
		$this->template->content = new View('doforms/admin/doforms_map_fields');
        $this->template->content->title = Kohana::lang("doforms.map_fields");
        $this->template->content->current_description_mapping_id = 0;
        
        $this->template->content->id = "";
        $this->template->content->form = "";
        $this->template->content->errors = "";
        $this->template->content->form_error = "";
        $this->template->content->form_saved = "";
        $this->template->content->description_mappings = array();
		$this->template->content->date_fields = array();
		$this->template->content->location_fields = array();
		$this->template->content->picture_fields = array();
		$this->template->content->all_fields = array();
	
        // setup and initialize form field names
        $form = array
        (
            'date'      => '',
            'location'      => '',
        	'location_description'      => '',
            'picture'           => '',
	    	'title' => '',
	    	'record_id' => ''
		);
		
		$this->template->this_page = 'addons';

		//  copy the form as errors, so the errors will be stored with keys corresponding to the form field names
        $this->template->content->error = null;				
		$errors = $form;
        $form_error = FALSE;
        if ($saved == 'saved')
        {
            $form_saved = TRUE;
        }
        else
        {
            $form_saved = FALSE;
        }
        
        //make sure the id has been set
		if(!isset($_GET["id"]))
		{
			$this->template->content->error = Kohana::lang("doforms.dofrom_id_not_specified");
			$this->template->content->form = $form;
			$this->template->content->form_error = $form_error;
			return;
		}
		$id = $_GET["id"];
		
		//are we getting this because we're viewing the mapping or changing the mappig?
		if($_POST)
		{
			// Instantiate Validation, use $post, so we don't overwrite $_POST fields with our own things
			$post = Validation::factory(array_merge($_POST,$_FILES));
			//  Add some filters			
			$post->pre_filter('trim', TRUE);
			$post->add_rules('date','required', 'length[1,255]');
			$post->add_rules('location','required', 'length[1,255]');
			$post->add_rules('location_description','required', 'length[1,255]');
			//$post->add_rules('picture','required', 'length[1,255]');
			$post->add_rules('title','required', 'length[1,255]');
			$post->add_rules('record_id','required', 'length[1,255]');
			
			if ($post->validate())
			{
				//delete all the current info for mappings for this form
				ORM::factory("doforms_fields")->where("doform_id", $id)->delete_all();
				
				//create the mapping for the title
				$title_mapping = ORM::factory("doforms_fields");
				$title_mapping->doform_id = $id;
				$title_mapping->use_title = 1;
				$title_mapping->doform_field_name = $post->title;
				$title_mapping->save();
				
				//create the mapping for the date
				$date_mapping = ORM::factory("doforms_fields");
				$date_mapping->doform_id = $id;
				$date_mapping->use_date = 1;
				$date_mapping->doform_field_name = $post->date;
				$date_mapping->save();
				
				//create the mapping for the location
				$location_mapping = ORM::factory("doforms_fields");
				$location_mapping->doform_id = $id;
				$location_mapping->use_location = 1;
				$location_mapping->doform_field_name = $post->location;
				$location_mapping->save();
				
				//create the mapping for the location description
				$location_description_mapping = ORM::factory("doforms_fields");
				$location_description_mapping->doform_id = $id;
				$location_description_mapping->use_location_description = 1;
				$location_description_mapping->doform_field_name = $post->location_description;
				$location_description_mapping->save();
				
				//create the mapping for the picture
				if($post->picture && $post->picture != "")
				{
					$picture_mapping = ORM::factory("doforms_fields");
					$picture_mapping->doform_id = $id;
					$picture_mapping->use_picture = 1;
					$picture_mapping->doform_field_name = $post->picture;
					$picture_mapping->save();
				}
				
				//create the mapping for the record ID
				$record_id_mapping = ORM::factory("doforms_fields");
				$record_id_mapping->doform_id = $id;
				$record_id_mapping->use_record_id = 1;
				$record_id_mapping->doform_field_name = $post->record_id;
				$record_id_mapping->save();
				
				//now the slightly more complex par
				//We're going to look through all the uploaded data and check
				//to see which ones are about mapping doform fields to the
				//report description field. Here we go...
				$order = 0;
				foreach($_POST as $key=>$value)
				{
					if(substr($key,0,25) == "description_fields_field_")
					{
						$new_id = substr($key,25);
						
						$description_mapping = ORM::factory("doforms_fields");
						$description_mapping->doform_id = $id;
						$description_mapping->use_description = 1;
						$description_mapping->doform_field_name = trim($_POST["description_fields_field_$new_id"]);
						$description_mapping->plain_text = $_POST["description_fields_plain_text_$new_id"];
						$description_mapping->field_order = $order;
						$description_mapping->save();
						
						$order++;
					}
				}
				
				url::redirect('admin/doforms_settings/map_fields/saved/?id='.$id);
			}//end passed validation
			else 
			{
				// repopulate the form fields
				$form = arr::overwrite($form, $post->as_array());
				// populate the error fields, if any
				$errors = arr::overwrite($errors, $post->errors('simplegroups'));
				$form_error = TRUE;	
			}//end didn't pass validation
			
		}
		
		else
		{
		
			//get the doform URL in question
			$doform_setting = ORM::factory("doforms")->where("id", $id)->find();
			$url = $doform_setting->url;
			$doform_setting->save();
			
			//check the URL to make sure it's valid
			$form_fields = doforms_parser::getFormFields($url);
			if(!is_array($form_fields) || count($form_fields) == 0)
			{
				$this->template->content->error = Kohana::lang("doforms.error_opening_url");
				$this->template->content->form = $form;
				$this->template->content->form_error = $form_error;
				return;
			}
			
			//loop through and organize the questions by type
			$date_fields = array();
			$location_fields = array();
			$picture_fields = array();
			$all_fields = array();
			
			foreach($form_fields as $field)
			{
				if($field["data_type"]=="JAVA_ROSA_DATETIME" || $field["data_type"]=="JAVA_ROSA_DATE")
				{
					$date_fields[$field["name"]] = $field["name"];
				}
				elseif ($field["data_type"]=="GEOPOINT")
				{
					$location_fields[$field["name"]] = $field["name"];
				}
				elseif ($field["data_type"]=="PICTURE")
				{
					$picture_fields[$field["name"]] = $field["name"];
				}
				$all_fields[$field["name"]] = $field["name"];
			}
			
			$this->template->content->date_fields = $date_fields;
			$this->template->content->location_fields = $location_fields;
			$this->template->content->picture_fields = $picture_fields;
			$this->template->content->all_fields = $all_fields;
			
			//Check and see what mapping already exists
			$field_mappings = ORM::factory("doforms_fields")
				->where("doform_id", $id)
				->find_all();
				
			$description_mappings = array();
				
			//loop through and populate the fields with their data
			foreach($field_mappings as $field_mapping)
			{
				if($field_mapping->use_title)
				{
					$form["title"] = $field_mapping->doform_field_name;
				}
				elseif ($field_mapping->use_date)
				{
					$form["date"] = $field_mapping->doform_field_name;
				}
				elseif ($field_mapping->use_location)
				{
					$form["location"] = $field_mapping->doform_field_name;
				}
				elseif ($field_mapping->use_location_description)
				{
					$form["location_description"] = $field_mapping->doform_field_name;
				}
				elseif ($field_mapping->use_picture)
				{
					$form["picture"] = $field_mapping->doform_field_name;
				}
				elseif ($field_mapping->use_record_id)
				{
					$form["record_id"] = $field_mapping->doform_field_name;
				}
				elseif ($field_mapping->use_description)
				{
					$description_mappings[$field_mapping->field_order] = $field_mapping;
				}
			}
		}//end else (not a post)
		
		$this->template->content->id = $id;
        $this->template->content->form = $form;
        $this->template->content->errors = $errors;
        $this->template->content->form_error = $form_error;
        $this->template->content->form_saved = $form_saved;
        $this->template->content->description_mappings = $description_mappings;
        $this->template->js = new View('doforms/admin/doforms_map_fields_js');
        $this->template->js->id = $id;
	
	}//end method
	
}//end class