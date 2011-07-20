<?php

/**
 * View for mapping DoForms fields to Ushahidi Report Fields 
 *
 * This plugin was written for ICRC by Etherton Technologies
 * 2011
 *
 * @package    DoForms plugin
 * @author     John Etherton, Etherton Technologies <john@ethertontech.com>
 * 
 */ 
?>



	<?php print form::open(NULL, array('enctype' => 'multipart/form-data', 'id' => 'doform_field_mapping', 'name' => 'doform_field_mapping')); ?>
		<input type="hidden" name="save" id="save" value="">
				
		<div class="report-form">
			<!-- Handle errors -->
			<?php
			if ($form_error) {
			?>
				<!-- red-box -->
				<div class="red-box">
					<h3><?php echo Kohana::lang('ui_main.error');?></h3>
					<ul>
					<?php
					foreach ($errors as $error_item => $error_description)
					{
						print (!$error_description) ? '' : "<li>" . $error_description . "</li>";
					}
					?>
					</ul>
				</div>
			<?php
			}
			?>
			
			<?php
			if ($error) {
			?>
				<!-- red-box -->
				<div class="red-box">
					<h3><?php echo Kohana::lang('ui_main.error');?></h3>
					<ul>
					<?php					
						print "<li>" . $error. "</li>";					
					?>
					</ul>
				</div>
			<?php
			}
			
			?>
			<!-- / end error -->
			
			<?php 
			if ($form_saved) {
			?>
				<!-- green-box -->
				<div class="green-box">
					<h3><?php echo Kohana::lang('doforms.changes_saved');?></h3>
				</div>
			<?php
			}
			?>
			<div class="head">
				<h3><?php echo Kohana::lang("doforms.map_fields") ?></h3>
				<div class="btns" style="float:right;">
					<ul>
						<li><a href="#" class="btn_save"><?php echo Kohana::lang("doforms.save");?></a></li>
						<li><a href="<?php echo url::base().'admin/doforms_settings/';?>" class="btns_red"><?php echo Kohana::lang('doforms.cancel');?></a>&nbsp;&nbsp;&nbsp;</li>
						<li><a href="#" id="btn_sync" class="btn_sync"><?php echo Kohana::lang("doforms.sync");?></a></li>
					</ul>
				</div>
			</div>
			<div id="sync_status" class="sync_status"></div>
			<!-- f-col -->
			<div style="padding-left: 20px;">

				
				<div class="row">
					<h4><?php echo Kohana::lang("doforms.title_field")?></h4>
					<span class="required">Required</span><br/>
					<?php print form::dropdown('title', $all_fields, $form['title']); ?>
				</div>
				
				<div class="row">
					<h4><?php echo Kohana::lang("doforms.date_field")?></h4>
					<span class="required">Required</span><br/>
					<?php print form::dropdown('date', $date_fields, $form['date']); ?>
				</div>
				
				<div class="row">
					<h4><?php echo Kohana::lang("doforms.location_field")?></h4>
					<span class="required">Required</span><br/>
					<?php print form::dropdown('location', $location_fields, $form['location']); ?>
				</div>
				
				<div class="row">
					<h4><?php echo Kohana::lang("doforms.location_description_field")?></h4>
					<span class="required">Required</span><br/>
					<?php print form::dropdown('location_description', $all_fields, $form['location_description']); ?>
				</div>
				
				<div class="row">
					<h4><?php echo Kohana::lang("doforms.picture_field")?></h4>
					<?php print form::dropdown('picture', $picture_fields, $form['picture']); ?>
				</div>
				
				<div class="row">
					<h4><?php echo Kohana::lang("doforms.record_id_field")?></h4>
					<span class="required">Required</span><br/>
					<?php print form::dropdown('record_id', $all_fields, $form['record_id']); ?>
				</div>
				
				<div class="row">
					<h4><?php echo Kohana::lang("doforms.description_field")?></h4>
					<div class="btns"><ul><li>
					<a href="#" class="btn_add_description_mapping"><?php echo Kohana::lang("doforms.add_description_mapping");?></a>					
					</li></ul></div>
					<br/>
					<table id="description_fields_table">
						<?php
						foreach($description_mappings as $d)
						{
							echo "<tr id=\"description_fields_row_".$d->field_order."\">";
							echo "<td> " . Kohana::lang("doforms.pre_append_text");
							echo "<input type=\"text\" id=\"description_fields_plain_text_".$d->field_order."\" name=\"description_fields_plain_text_".$d->field_order."\" value=\"".$d->plain_text." \"></td>";
							echo "<td> ".Kohana::lang("doforms.field_to_add");
							print form::dropdown("description_fields_field_".$d->field_order, $all_fields, $d->doform_field_name);
							echo "</td>";							
							echo "<td><a href=\"#\" id=\"description_fields_delete_".$d->field_order."\">". Kohana::lang("doforms.delete")."</a></td></tr>";
						} 
						
						?>
					</table>
					<?php echo '<input type="hidden" name="description_mapping_id" value="'.count($description_mappings).'" id="description_mapping_id">'; ?>
				</div>	
					
			</div>
			<br/>
			<br/>		
			
		</div>
		
	<?php print form::close(); ?>
	

</div>
