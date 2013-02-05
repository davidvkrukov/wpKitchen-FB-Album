<?php global $wpk_facebook ?>
<?php if($wpk_facebook->getUser()==0): ?>
<script type="text/javascript">
<!--
top.location.href="<?php echo $wpk_facebook->getLoginUrl() ?>";
//-->
</script>
<?php else: ?>
<table class="form-table">
	<tr valign="top">
		<th scope="row"><?php _e('Post attached images to Facebook album','wpkitchen-fb-album'); ?></th>
		<td>
			<input type="checkbox" id="wpk_fb_album_add" name="wpk_fb_album_add" value="1"<?php echo get_option('wpk_fb_post_by_default',1)==1?' checked="checked"':''; ?>/>
		</td>
	</tr>
	<tr valign="top">
		<th scope="row"><?php _e('Album name','wpkitchen-fb-album'); ?></th>
		<td><input class="regular-text" type="text" name="wpk_fb_album" value="<?php echo $album_name_preformatted; ?>" /></td>
	</tr>
	<tr>
		<th scope="row"><?php _e('Images in post','wpkitchen-fb-album'); ?></th>
		<td id="wpk_metabox_container"></td>
	</tr>
</table>
<?php endif; ?>