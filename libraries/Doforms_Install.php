<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Performs install/uninstall methods for the DoForms Plugin
 * 
 * This plugin was written for ICRC by Etherton Technologies
 *
 * @package    DoForms plugin
 * @author     John Etherton, Etherton Technologies <john@ethertontech.com>
 * 
 */
class Doforms_Install {
	
	/**
	 * Constructor to load the shared database library
	 */
	public function __construct()
	{
		$this->db =  new Database();
	}

	/**
	 * Creates the required columns for the FrontlineSMS Plugin
	 */
	public function run_install()
	{
		
		// ****************************************
		// DATABASE STUFF
		
		//Create the main table that stores all the URLs we're pulling data from
		$this->db->query("
			CREATE TABLE IF NOT EXISTS `".Kohana::config('database.default.table_prefix')."doforms`
			(
				id int(11) unsigned NOT NULL AUTO_INCREMENT,
				url longtext DEFAULT NULL,
				active tinyint(4) NOT NULL default '0',
				form_id int(11) unsigned DEFAULT NULL,
				PRIMARY KEY (`id`)
			);
		");
		
		//Create the table that stores how we map fields in doForms to fields in Ushahidi
		$this->db->query("
			CREATE TABLE IF NOT EXISTS `".Kohana::config('database.default.table_prefix')."doforms_fields`
			(
				id int(11) unsigned NOT NULL AUTO_INCREMENT,
				data_type varchar(256) DEFAULT NULL,
				doform_id int(11) unsigned DEFAULT NULL,
				doform_field_name varchar(256) DEFAULT NULL,
				plain_text varchar(256) DEFAULT NULL,
				report_field_id int(11) unsigned DEFAULT NULL,
				use_description tinyint(4) NOT NULL default '0',
				use_title tinyint(4) NOT NULL default '0',
				field_order int(11) unsigned DEFAULT NULL,
				use_date tinyint(4) NOT NULL default '0',
				use_location tinyint(4) NOT NULL default '0',
				use_location_description tinyint(4) NOT NULL default '0',
				use_picture tinyint(4) NOT NULL default '0',
				use_record_id tinyint(4) NOT NULL default '0',
				PRIMARY KEY (`id`)
			);
		");
		// ****************************************
		
		
		//Create the table that keeps track of which doform record correlates to which Ushahidi report
		//so we don't create duplicate reports in Ushahdii 
		$this->db->query("
			CREATE TABLE IF NOT EXISTS `".Kohana::config('database.default.table_prefix')."doforms_reports`
			(
				id int(11) unsigned NOT NULL AUTO_INCREMENT,
				doform_id int(11) unsigned DEFAULT NULL,
				incident_id int(11) unsigned DEFAULT NULL,
				record_id varchar(256) DEFAULT NULL,
				PRIMARY KEY (`id`)
			);
		");
		// ****************************************
		
		
		//Creates an entry in the scheduler for doforms 
		$this->db->query("
			INSERT INTO `".Kohana::config('database.default.table_prefix')."scheduler` 
			(`scheduler_name`, `scheduler_last`, `scheduler_weekday`, `scheduler_day`, `scheduler_hour`, `scheduler_minute`, 
			`scheduler_controller`, `scheduler_active`) VALUES			
			('DoForms', 1309382249, -1, -1, -1, 0, 's_doforms', 1);"
			);
		// ****************************************
		
		
			
		//Check to see if we should add the publish field		
		$result = $this->db->query('DESCRIBE `'.Kohana::config('database.default.table_prefix').'doforms`');
		$has_publish = false;
		foreach($result as $row)
		{
			if($row->Field == "publish")
			{
				$has_publish = true;
				break;
			}
		}
		
		if(!$has_publish)
		{
			$this->db->query('ALTER TABLE `'.Kohana::config('database.default.table_prefix').'doforms` ADD `publish` TINYINT(4) NULL DEFAULT NULL');
		}
		//****************************************
		

		//Check to see if we should add the category field		
		$result = $this->db->query('DESCRIBE `'.Kohana::config('database.default.table_prefix').'doforms`');
		$has_category = false;
		foreach($result as $row)
		{
			if($row->Field == "category")
			{
				$has_category = true;
				break;
			}
		}
		
		if(!$has_category)
		{
			$this->db->query('ALTER TABLE `'.Kohana::config('database.default.table_prefix').'doforms` ADD `category` INT(11) NULL DEFAULT NULL');
		}
		//****************************************
		
		
		//Check to see if we should add the name field		
		$result = $this->db->query('DESCRIBE `'.Kohana::config('database.default.table_prefix').'doforms`');
		$has_category = false;
		foreach($result as $row)
		{
			if($row->Field == "name")
			{
				$has_category = true;
				break;
			}
		}
		
		if(!$has_category)
		{
			$this->db->query('ALTER TABLE `'.Kohana::config('database.default.table_prefix').'doforms` ADD `name` varchar(256) NULL DEFAULT NULL');
		}
		//****************************************
	}

	/**
	 * Drops the FrontlineSMS Tables
	 */
	public function uninstall()
	{
		//drop the main table
		//$this->db->query("DROP TABLE ".Kohana::config('database.default.table_prefix')."doforms;");
		
		//drop the table that maps do form fields to Ushahidi report fields
		//$this->db->query("DROP TABLE ".Kohana::config('database.default.table_prefix')."doforms_fields;");
		
		//drop the table that maps do form rows to Ushahidi reports to prevent duplication
		//$this->db->query("DROP TABLE ".Kohana::config('database.default.table_prefix')."doforms_reports;");
		
	}
}