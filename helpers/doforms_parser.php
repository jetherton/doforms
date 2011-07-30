<?php
/**
 * DoForms XML parser helper class
 *
 * This plugin was written for ICRC by Etherton Technologies
 * 2011
 *
 * @package    DoForms plugin
 * @author     John Etherton, Etherton Technologies <john@ethertontech.com>
 * 
 */ 
class doforms_parser_Core {

	
	public static $NO_RECORD_ID = 1;
	public static $NO_TITLE = 2;
	public static $NO_DATE = 3;
	public static $NO_LOCATION = 4;
	public static $NO_LOCATION_DESCRIPTION = 5;
	public static $DUPLICTE = 6;
	public static $SUCCESS = 7;

	/**
	 * Help function to parse the XML at the given url and return an array with information about the fields found
	 * @param unknown_type $url - Where the XML is
	 */
	public static function getFormFields($url)
	{
		$retVal = array();
		$url = 	trim($url);
		$xmlReader = false;			
		$xmlReader = @XMLReader::open($url);
		
		
		if($xmlReader === false)
		{		
			return Kohana::lang("doforms.error_opening_url");
		}
		
		if(!$xmlReader->isValid())
		{
			return Kohana::lang("doforms.error_opening_url");
		}
		
		//read till you hit a row
		while($xmlReader->read() && $xmlReader->name !== "row")
		
		//no look through all the fields till you hit the next row
		$last_name = "";
		while($xmlReader->read() && $xmlReader->name !== "row")
		{
			if($xmlReader->nodeType != XMLReader::ELEMENT || $last_name == $xmlReader->name)
			{
				continue;
			}
			
			$field_array = array("name"=>$xmlReader->name, "data_type"=>$xmlReader->getAttribute("type"), "node_type"=>$xmlReader->nodeType);
			$retVal[] = $field_array;
			$last_name = $xmlReader->name;
		}
		return $retVal;
	}
	
	
	/**
	 * Helper function to sync up a doform with the Ushahidi reports
	 * @param unknown_type $id ID of the dofrom we're syncing
	 */
	public static function sync($id)
	{
		//first get all the info on this form so lets go to the database
		$doForm = ORM::factory("doforms")
			->where("id", $id)
			->find();
		//make sure it's valid
		if(!$doForm->loaded)
		{
			return Kohana::lang("doforms.not_valid_doform_id");
		}
		
		$url = trim($doForm->url);		
		$form_id = $doForm->form_id;
		
		//now get the mappings
		$mappings = ORM::factory("doforms_fields")
			->where("doform_id",$id)
			->find_all();

		//put all the mappings into a associative array
		$mapping_array = array();
		
		//as we're doing this make sure that the use has set mappings for the required fields
		$mapped_title = false;
		$mapped_date = false;
		$mapped_location = false;
		$mapped_location_desc = false;
		$mapped_record_id = false;
		
		foreach($mappings as $mapping)
		{
			if($mapping->use_title)
			{
				$mapped_title = true;
			}
			elseif($mapping->use_date)
			{
				$mapped_date = true;
			}
			elseif($mapping->use_location)
			{
				$mapped_location = true;
			}
			elseif($mapping->use_location_description)
			{
				$mapped_location_desc = true;
			}			
			elseif($mapping->use_record_id)
			{
				$mapped_record_id = true;
			}
			
			//There can be a one to many relationship with our mapping so prepare for that
			if(isset($mapping_array[$mapping->doform_field_name]))
			{
				$mapping_array[$mapping->doform_field_name][] = $mapping;
			}
			else
			{
				$mapping_array[$mapping->doform_field_name] = array();
				$mapping_array[$mapping->doform_field_name][] = $mapping;
			}
		}
		
		//now make sure they mapped everything like they should
		if(!$mapped_title)
		{
			return Kohana::lang("doforms.error_title_not_mapped");
		}
		if(!$mapped_date)
		{
			return Kohana::lang("doforms.error_date_not_mapped");
		}
		if(!$mapped_location)
		{
			return Kohana::lang("doforms.error_location_not_mapped");
		}
		if(!$mapped_location_desc)
		{
			return Kohana::lang("doforms.error_location_description_not_mapped");
		}			
		if(!$mapped_record_id)
		{
			return Kohana::lang("doforms.error_record_id_not_mapped");
		}
		
		//now get the data and walk through it
		$xmlReader = false;
		$xmlReader = @XMLReader::open($url);
		
		
		if($xmlReader === false)
		{		
			return Kohana::lang("doforms.error_opening_url");
		}
		
		if(!$xmlReader->isValid())
		{
			return Kohana::lang("doforms.error_opening_url");
		}
		
		//read till you hit a row
		$retVals = "";
		
		$total = 0;
		$errors = 0;
		$duplicates = 0;
		$successful = 0;
		
		while($xmlReader->read())
		{
			 if($xmlReader->name === "row" && $xmlReader->nodeType == XMLReader::ELEMENT)
			 {
			 	//get the XML for the row element and parse it
			 	$row_xml = $xmlReader->readOuterXML();
			 	//create a new xml reader for the row
			 	$xmlRowReader = new XMLReader();
			 	$xmlRowReader->xml($row_xml);
			 	$error_code = self::parseRow($xmlRowReader, $mapping_array,$id,$form_id, $doForm);
			 	$total++;
			 	switch($error_code)
			 	{
			 		case self::$NO_RECORD_ID:
			 			$retVals .= Kohana::lang("doforms.error_no_record_id") . $total. "<br/>";
			 			$errors++;
			 			break;
			 		case self::$NO_DATE:
			 			$retVals .= Kohana::lang("doforms.error_no_date") . $total. "<br/>";
			 			$errors++;
			 			break;
			 		case self::$NO_TITLE:
			 			$retVals .= Kohana::lang("doforms.error_no_title") . $total. "<br/>";
			 			$errors++;
			 			break;
			 		case self::$NO_LOCATION:
			 			$retVals .= Kohana::lang("doforms.error_no_location") . $total. "<br/>";
			 			$errors++;
			 			break;
			 		case self::$NO_LOCATION_DESCRIPTION:
			 			$retVals .= Kohana::lang("doforms.error_no_location_descrition") . $total. "<br/>";
			 			$errors++;
			 			break;
			 		case self::$DUPLICTE:
			 			$retVals .= Kohana::lang("doforms.warning_duplicate") . $total. "<br/>";
			 			$duplicates++;
			 			break;
			 		case self::$SUCCESS:
			 			$successful++;
			 			break;
			 	}
			 	//$retVals .= self::parseRow($xmlRowReader, $mapping_array,$id) . "<br/>";
			 }
		}
		$summary = Kohana::lang("doforms.total").": $total, ".Kohana::lang("doforms.duplicates").": $duplicates, "
			.Kohana::lang("doforms.errors"). ": $errors, ". Kohana::lang("doforms.successful"). ": $successful<br/><br>"; 
		return $summary.$retVals;
	}//end method
	
	
	/**
	 * Helper method for parsing each individual row of data
	 * @param unknown_type $xmlReader XMLReader instance for each row
	 * @param unknown_type $mapping_array the mapping of fields in DoForms to fields in Ushahidi reports
	 * @param unknown_type $id id for the doform we're reading from
	 * * @param unknown_type $id id of the form this is going into in Ushahidi
	 */
	private static function parseRow($xmlReader, $mapping_array, $id, $form_id, $doForm_settings)
	{
		//echo "<br/><br/><br/>";
		
		//variables we need to fill
		$title = null;
		$date = null;
		$location = null;
		$location_description = null;
		$picture = null;
		$record_id = null;
		$description = array();
		
		
		$last_name = "";
		while($xmlReader->read())
		{
			if($xmlReader->nodeType != XMLReader::ELEMENT || $last_name == $xmlReader->name || $xmlReader->name === "row")
			{
				continue;
			}
			
			$last_name = $xmlReader->name;
			$data = $xmlReader->readString();		
			//echo "<strong>".$last_name . "</strong>  ".$data."<br/>";
			
			//check and see if the current field is mapped to something
			if(!isset($mapping_array[$last_name]))
			{
				continue;
			}
			//if it is check it out
			$current_mappings = $mapping_array[$last_name];
			
			foreach($current_mappings as $current_mapping)
			{
				if($current_mapping->use_title)
				{
					$title = $data;
				}
				elseif($current_mapping->use_date)
				{
					$date = $data;
				}
				elseif($current_mapping->use_location)
				{
					$location = $data;
				}
				elseif($current_mapping->use_location_description)
				{
					$location_description = $data;
				}
				elseif($current_mapping->use_picture)
				{
					$picture = $data;
				}
				elseif($current_mapping->use_record_id)
				{
					$record_id = $data;
				}
				elseif($current_mapping->use_description)
				{
					$description[$current_mapping->field_order] = $current_mapping->plain_text . " ". $data . "\r\n";
				}
				
			}//end of loop over mapings
		}//end of loop over XML	
		
		//first check and see if we have a valid record ID and make sure it hasn't already been entered into the database
		if($record_id == null)
		{
			//echo "NO RECORD ID";
			return self::$NO_RECORD_ID;	
		}
		elseif($title == null)
		{
			//echo "NO TITLE";
			return self::$NO_TITLE;
		}
		elseif($location == null)
		{
			//echo "NO LOCATION";
			return self::$NO_LOCATION;
		}
		elseif($location_description == null)
		{
			//echo "NO LOCATION DESCRIPTION";
			return self::$NO_LOCATION_DESCRIPTION;
		}
		elseif($date == null)
		{
			//echo "NO DATE";
			return self::$NO_DATE;
		}
		
		$doforms_reports = ORM::factory("doforms_reports")
			->where("record_id", $record_id)
			->find();
			
		if($doforms_reports->loaded)
		{
			return self::$DUPLICTE;
		}
		
		//so we seem to be all good to go so lets get this party started
		
		//parse out the lat and lon
		$location_data = explode(":", $location);
		$lat = $location_data[0];
		$lon = $location_data[1];
		
		// STEP 1: SAVE LOCATION
		$location = new Location_Model();
		$location->location_name = $location_description;
		$location->latitude = $lat;
		$location->longitude = $lon;
		$location->location_date = date("Y-m-d H:i:s",strtotime($date));
		$location->save();
		
		//now create the desription
		ksort($description);
		$description_text = "";
		foreach($description as $d)
		{
			$description_text .= $d;
		}
		
		// STEP 2: SAVE INCIDENT
		$incident = new Incident_Model();
		$incident->location_id = $location->id;
		$incident->form_id = $form_id; 
		$incident->user_id = 0;
		$incident->incident_title = $title;
		$incident->incident_description = $description_text;
		$incident->incident_date = date("Y-m-d H:i:s",strtotime($date));
		$incident->incident_dateadd = date("Y-m-d H:i:s",time());
		if ($doForm_settings->publish)
		{
			$incident->incident_active = 1;
			$incident->incident_verified = 1;
		}
		$incident->save();
		
		 // Record Approval/Verification Action
		$verify = new Verify_Model();
		$verify->incident_id = $incident->id;
		$verify->user_id = "0";          // Record 'Verified By' Action
		$verify->verified_date = date("Y-m-d H:i:s",time());

		if ($doForm_settings->publish)
		{
		    $verify->verified_status = '1';		
		}		
		else
		{
			$verify->verified_status = '0';
		}
		$verify->save();
		
		
		//STEP 3: SAVE CATEGORIES
		ORM::factory('Incident_Category')->where('incident_id',$incident->id)->delete_all();        // Delete Previous Entries
		if($doForm_settings->category != -1)
		{
			//check to make sure the category still exists
			$cat = ORM::factory("category")->where("id", $doForm_settings->category)->count_all();
			if($cat > 0)
			{
				$incident_category = new Incident_Category_Model();
			    $incident_category->incident_id = $incident->id;
			    $incident_category->category_id = $doForm_settings->category;
			    $incident_category->save();
			}
		}
				
		
		if($picture != null)
		{
			$picture_data = null;
			$picture_data = @file_get_contents($picture);
			
			//make sure we got some valid data
			if($picture_data != null)
			{
				$file_type = ".png";
				
				//write that stuff to file
				$new_filename = $incident->id."_doform_".time();
				file_put_contents(Kohana::config('upload.directory', TRUE).$new_filename.$file_type, $picture_data);
				
				// Medium size
				Image::factory(Kohana::config('upload.directory', TRUE).$new_filename.$file_type)->resize(400,300,Image::HEIGHT)
						->save(Kohana::config('upload.directory', TRUE).$new_filename."_m".$file_type);
					
				// Thumbnail
				Image::factory(Kohana::config('upload.directory', TRUE).$new_filename.$file_type)->resize(89,59,Image::HEIGHT)
						->save(Kohana::config('upload.directory', TRUE).$new_filename."_t".$file_type);	
						
				// Save to DB				
				$photo = new Media_Model();
				$photo->location_id = $location->id;
				$photo->incident_id = $incident->id;
				$photo->media_type = 1; // Images
				$photo->media_link = $new_filename.$file_type;
				$photo->media_medium = $new_filename."_m".$file_type;
				$photo->media_thumb = $new_filename."_t".$file_type;
				$photo->media_date = date("Y-m-d H:i:s",time());
				$photo->save();
			}
		}
		
		
		// STEP 5: SAVE PERSONAL INFORMATION
		$person = new Incident_Person_Model();
		$person->location_id = $location->id;
		$person->incident_id = $incident->id;
		$person->person_first = "DoForms Plugin";
		$person->person_last = "Record#: ". $record_id;
		$person->person_date = date("Y-m-d H:i:s",time());
		$person->save();
		
		//Make a note of this so we don't import this record again
		$doforms_reports = new Doforms_reports_Model();
		$doforms_reports->doform_id = $id;
		$doforms_reports->record_id = $record_id;
		$doforms_reports->incident_id = $incident->id;		
		$doforms_reports->save();
		
		//echo "SUCCESSFULLY ADDED NEW RECORD";
		
		
		
		return self::$SUCCESS;
		
	}//end of method

}//end class locationhighlight_core



