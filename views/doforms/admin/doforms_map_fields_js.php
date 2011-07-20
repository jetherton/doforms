<?php

/**
 * JavaScript view for mapping DoForms fields to Ushahidi Report Fields 
 *
 * This plugin was written for ICRC by Etherton Technologies
 * 2011
 *
 * @package    DoForms plugin
 * @author     John Etherton, Etherton Technologies <john@ethertontech.com>
 * 
 */ 
?>

		// handle the save button click even
		$('.btn_save').live('click', function () {
			$("#save").attr("value", "1");
			$(this).parents("form").submit();
			return false;
		});
		
		
		
		//handle the Syncyhronize button click even
		$('.btn_sync').live('click', function () {
			//show the waiting graphic
			$('#btn_sync').append('<img id="sync_waiting" src="<?php echo url::base(); ?>media/img/loading_g.gif"/>');
			
			$.get("<?php echo url::site(); ?>admin/doforms_ajax/sync_forms/<?php echo $id; ?>",
			function(data){
				$("#sync_status").html(data);
				$('#sync_waiting').remove();
			});
			
			return false;
		});
		
		
		// handle the add description mapping button click event
		$('.btn_add_description_mapping').live('click', function () {
			var id = $("#description_mapping_id").val();
			$("#description_fields_table").append("<tr id=\"description_fields_row_"+id+"\">"
				+"<td> <?php echo Kohana::lang("doforms.pre_append_text"); ?>"
				+"<input type=\"text\" id=\"description_fields_plain_text_"+id+"\" name=\"description_fields_plain_text_"+id+"\" value=\"\"></td>"
				+"<td> <?php echo Kohana::lang("doforms.field_to_add"); ?>"
				+"<select  id=\"description_fields_field_"+id+"\" name=\"description_fields_field_"+id+"\" >"
				+$("#title").html()+"</select>"
				+"<td><a href=\"#\" id=\"description_fields_delete_"+id+"\"><?php echo Kohana::lang("doforms.delete"); ?></a></td></tr>");
			id = (id - 1) + 2;
			$("#description_mapping_id").val(id);
			return false;
		});
		
		
		//removes things from the list of fields that go into the description
		$("a[id^='description_fields_delete_']").live('click', function()
		{
			var ID = this.id.substring(26);
			$("#description_fields_row_" + ID).remove();
			return false;
		});