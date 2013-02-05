<div class="wrap">
	<div id="icon-options-general" class="icon32"><br></div>
	<h2><?php _e('wpKitchen FB Album','wpkitchen-fb-album'); ?></h2>
	<form method="post" action="options.php">
		<?php settings_fields('wpkfb-settings-group'); ?>
		<?php do_settings_sections('wpkfb-settings-group'); ?>
		<table class="form-table">
			<tr valign="top">
				<th scope="row" colspan="2"><h3 class="title"><?php _e('Main settings','wpkitchen-fb-album'); ?></h3></th>
			</tr>
			<tr valign="top">
				<th scope="row"><?php _e('Application ID','wpkitchen-fb-album'); ?></th>
				<td><input class="regular-text" type="text" name="wpk_fb_app_id" value="<?php echo get_option('wpk_fb_app_id'); ?>" /></td>
			</tr>
			<tr valign="top">
				<th scope="row"><?php _e('Secret','wpkitchen-fb-album'); ?></th>
				<td><input class="regular-text" type="text" name="wpk_fb_app_secret" value="<?php echo get_option('wpk_fb_app_secret'); ?>" /></td>
			</tr>
			<tr valign="top">
				<th scope="row"><?php _e('Post to Facebook by default','wpkitchen-fb-album'); ?></th>
				<td>
					<fieldset>
						<p>
							<label>
								<input name="wpk_fb_post_by_default" type="radio" value="1" <?php echo get_option('wpk_fb_post_by_default',1)==1?'checked="checked"':''; ?>> <?php _e('Yes','wpkitchen-fb-album'); ?>
							</label>
							<br>
							<label>
								<input name="wpk_fb_post_by_default" type="radio" value="0" <?php echo get_option('wpk_fb_post_by_default',1)==0?'checked="checked"':''; ?>> <?php _e('No','wpkitchen-fb-album'); ?>
							</label>
						</p>
					</fieldset>
				</td>
			</tr>
			<?php $post_types=get_post_types(array('public'=>true,'_builtin'=>true),'objects'); ?>
			<tr valign="top">
				<th scope="row"><?php _e('Post types','wpkitchen-fb-album'); ?></th>
				<td>
				<?php $_opt=get_option('wpk_fb_use_type',array()); ?>
				<?php foreach($post_types as $post_type): ?>
					<label>
						<input type="checkbox" name="wpk_fb_use_type[<?php echo $post_type->name; ?>]" value="1"<?php checked(isset($_opt[$post_type->name])); ?>/> <?php echo $post_type->labels->name; ?>
					</label>
					<br/>
  				<?php endforeach; ?>
	  			</td>
  			</tr>
			<tr valign="top">
				<th scope="row"><?php _e('Album name template','wpkitchen-fb-album'); ?></th>
				<td>
					<input class="regular-text" type="text" name="wpk_fb_album_template" value="<?php echo get_option('wpk_fb_album_template'); ?>" />
					<p class="description"><?php _e('Input field accepting normal text plus the following tags:','wpkitchen-fb-album'); ?></p>
					<p class="description"><?php _e('%year% %month% %month-name% %week% %day% %day-name% %post-title%','wpkitchen-fb-album'); ?></p>
				</td>
			</tr>
		</table>
		<?php submit_button(); ?>
	</form>
</div>
