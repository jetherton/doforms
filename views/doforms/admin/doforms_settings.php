

<form action="" method="post" name="plugin_settings">
	<div class="report-form">
		<div class="head">
			<h3><?php echo Kohana::lang("doforms.doforms_settings"); ?></h3>
			<span onclick="document.location.href='<?php echo url::site(); ?>admin/addons/plugins/'; return false;" 
				style="cursor:pointer; padding: 5px; background: #f2f7fa; color: #aa3333; border: 1px #d8d8d8 solid" 
				class="save-rep-btn">
				<?php echo Kohana::lang("doforms.cancel"); ?>
			</span>
			<span style="cursor:pointer; padding: 5px; background: #f2f7fa; color: #5c5c5c; border: 1px #d8d8d8 solid" class="save-rep-btn"
				onclick="addNew(); return false;">
				<?php echo Kohana::lang("doforms.add_new"); ?>
			</span>	
			<span class="save-rep-btn" id="add_new_waiting"></span>			
		</div>				
					
		<div class="settings_holder" >
			<table  class="my_table" id="settings_table">
			<?php
				$is_first = true;
				foreach($doforms_settings as $doform_setting)
				{
					$view = new View("doforms/admin/doform_setting");
					$view->id = $doform_setting->id;
					$view->active = $doform_setting->active;
					$view->url = $doform_setting->url;
					$view->name = $doform_setting->name;
					$view->is_first = $is_first;
					$view->show_map_fields = true;
					$view->selected_form =  $doform_setting->form_id;
					$view->available_forms = $available_forms;
					$view->available_categories = $available_categories;
					$view->selected_cat = $doform_setting->category;
					$view->publish = $doform_setting->publish;
					$view->render(TRUE);
					$is_first = false;
				}
			
			?>
			</table>					
		</div>
		<div class="simple_border"></div>
		
	</div>
</form>