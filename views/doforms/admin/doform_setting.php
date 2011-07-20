<?php 

/**
 * DoForms view for the setting of one Do Form
 *
 * This plugin was written for ICRC by Etherton Technologies
 * 2011
 *
 * @package    DoForms plugin
 * @author     John Etherton, Etherton Technologies <john@ethertontech.com>
 * 
 */ 

?>



<tr class="do_setting_row" <?php if(!$is_first){echo "style=\"border-top:5px solid #F0F0F0;\"";}?> id="doforms_setting_row_<?php echo $id;?>">
	<td class="status_column" >
	</td>
	<td>
		<div><?php echo Kohana::lang("doforms.active"); ?>: 
			<input style="width:auto;" type="checkbox" name="doform_active_<?php echo $id;?>" id="doform_active_<?php echo $id;?>" value="active"
				<?php echo $active ? "checked" : ""; ?>/>
		</div>
		<br/>
		<div>
			<?php echo Kohana::lang("doforms.form"); ?>:
			<?php print form::dropdown('doform_form_'.$id, $available_forms, $selected_form); ?>
		</div>
	</td>
	
	<td style="padding-left:5px;">
		<div><?php echo Kohana::lang("doforms.url"); ?>: <input type="text" name="doform_url_<?php echo $id;?>" id="doform_url_<?php echo $id;?>" value="<?php echo $url?>"/></div>
		<br/>
		<div id="doform_status_<?php echo $id;?>"></div>
		<div>
			<?php echo Kohana::lang("doforms.publish"); ?>:
			<input style="width:auto;" type="checkbox" name="doform_publish_<?php echo $id;?>" id="doform_publish_<?php echo $id;?>" value="active"
				<?php echo $publish ? "checked" : ""; ?>/>
		</div>
		<br/>
		<div>
			<?php echo Kohana::lang("doforms.category"); ?>:
			<?php print form::dropdown('doform_cat_'.$id, $available_categories, $selected_cat); ?>
		</div>
		
	</td>
	<td>
		
		<span style="cursor:pointer; padding: 5px; background: #f2f7fa; color: #5c5c5c; border: 1px #d8d8d8 solid" class="save-rep-btn"
			onclick="saveSetting('<?php echo $id;?>'); return false;">
			<?php echo Kohana::lang("doforms.save"); ?>
		</span>
		<span style="cursor:pointer; padding: 5px; background: #f2f7fa; color: #5c5c5c; border: 1px #d8d8d8 solid" class="save-rep-btn"
			onclick="deleteSetting('<?php echo $id;?>'); return false;">
			<?php echo Kohana::lang("doforms.delete"); ?>
		</span>
		
		<span id="mappings_<?php echo $id;?>" style="cursor:pointer;
			<?php if(!$show_map_fields){ echo " display:none; ";} ?> 
			padding: 5px; background: #f2f7fa; color: #5c5c5c; border: 1px #d8d8d8 solid" class="save-rep-btn"
			onclick="document.location.href='<?php echo url::site(); ?>admin/doforms_settings/map_fields?id=<?php echo $id;?>'; return false;">
			<?php echo Kohana::lang("doforms.map_fields"); ?>
		</span>
		
	</td>
</tr>

