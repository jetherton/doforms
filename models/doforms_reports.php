<?php defined('SYSPATH') or die('No direct script access.');
/**
 * DoForms model for tracking which DoForm data set has been converted to what Ushahidi Report
 *
 * This plugin was written for ICRC by Etherton Technologies
 * 2011
 *
 * @package    DoForms plugin
 * @author     John Etherton, Etherton Technologies <john@ethertontech.com>
 * 
 */ 

class Doforms_reports_Model extends ORM
{
	
	// Database table name
	protected $table_name = 'doforms_reports';
}
