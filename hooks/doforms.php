<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Hooks into events in Ushahidi for the DoForms plugin
 * 
 * This plugin was written for ICRC by Etherton Technologies
 *
 * @package    DoForms plugin
 * @author     John Etherton, Etherton Technologies <john@ethertontech.com>
 * 
 */

class doforms {
	
	/**
	 * Registers the main event add method
	 */
	public function __construct()
	{
	
		// Hook into routing
		Event::add('system.pre_controller', array($this, 'add'));
		
	}
	
	/**
	 * Adds all the events to the main Ushahidi application
	 */
	public function add()
	{
		//Just in case we need this
		Event::add('ushahidi_action.report_delete', array($this, '_delete_report'));	 //makes sure the doforms data is deleted along with the report
	}
	
	
	public function _delete_report()
	{
		$incident_id = Event::$data;
		$doforms_reports = ORM::factory("doforms_reports")
			->where("incident_id", $incident_id)
			->delete_all();	
	}

	
}//end class

new doforms;