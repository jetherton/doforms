<?php 

/**
 * DoForms settings java script view. Holds all the java script used in the main settings page
 *
 * This plugin was written for ICRC by Etherton Technologies
 * 2011
 *
 * @package    DoForms plugin
 * @author     John Etherton, Etherton Technologies <john@ethertontech.com>
 * 
 */ 

?>


function addNew()
{
	$('#add_new_waiting').html('<img src="<?php echo url::base(); ?>media/img/loading_g.gif"/>');
	$.get("<?php echo url::site(); ?>admin/doforms_ajax/add_new",
			function(data){
				$("#settings_table").append(data);
				$('#add_new_waiting').html('');
			});
}


function saveSetting(id)
{
	$('#doform_status_'+id).html('<img src="<?php echo url::base(); ?>media/img/loading_g.gif"/>');	
	var form_url = $("#doform_url_"+id).val().toString();
	
	var active = 0;
	var doform_id = id;
	var publish = 0;
	
	var temp = $("#doform_active_"+id).attr("checked")
	if (typeof temp !== 'undefined' && temp !== false)
	{
		active = 1;
	}
	
	temp = $("#doform_publish_"+id).attr("checked")
	if (typeof temp !== 'undefined' && temp !== false)
	{
		publish = 1;
	}
	
	var form_id = $('#doform_form_'+id+' option:selected').val();
	var cat_id = $('#doform_cat_'+id+' option:selected').val();
	
	
	$.ajax({url: "<?php echo url::site(); ?>admin/doforms_ajax/save",
		   dataType: "html",
		   data: {"id":doform_id, "url":form_url, "formId":form_id, "active":active, "publish":publish, "category":cat_id},
		   type:"POST", 
		   success:
			function(data){
				$("#doform_status_"+id).html(data);
				
				timeOutStr = "$('#doform_status_"+id+"').html(\"\")";
				setTimeout(timeOutStr, 4000);
				
				if(data.indexOf("color:#008800") != -1)
				{
					$("#mappings_"+id).css("display", "inline");
				}
			}});

}

function deleteSetting(id)
{	

	$('#doform_status_'+id).html('<img src="<?php echo url::base(); ?>media/img/loading_g.gif"/>');
	$.get("<?php echo url::site(); ?>admin/doforms_ajax/delete?id="+id,
			function(data){
				$("#doforms_setting_row_"+id).remove();				
			});
}