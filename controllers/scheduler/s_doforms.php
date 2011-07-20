<?php defined('SYSPATH') or die('No direct script access.');
/**
 * DoForms scheduler Controller - Checks up on the doforms on a regular basis
 * 
 * This plugin was written for ICRC by Etherton Technologies
 * 2011
 *
 * @package    DoForms plugin
 * @author     John Etherton, Etherton Technologies <john@ethertontech.com>
 * 
 */ 

class S_Doforms_Controller extends Controller {
	
	public function __construct()
    {
        parent::__construct();
	}	
	
	public function index() 
	{
		$doforms = ORM::factory("doforms")->where("active",1)->find_all();
		
		foreach($doforms as $doform)
		{
			$retVal = doforms_parser::sync($doform->id);
			echo $retVal;
		}
	}
}
