<div id="wrap" class="<?php echo $this->plugin_name ?>">
	<?php
		$plugin = new Alti_Watermark_Admin($this->plugin_name, $this->version);
		$plugin->check_gd_library();
		$plugin->check_modrewrite();
		$plugin->check_previous_htaccess();
		$plugin->check_watermark_folder();
		$plugin->get_watermark_width();
		$plugin->check_uploads_url();
		if($_POST) {
			$plugin->save_settings();
			$plugin->generate_htaccess();
		}
		$plugin->display_messages();	
	?>
	<h2>Watermark <span><?php _e('by', $this->plugin_name); ?> <a href="http://alticreation.com/en">alticreation.com</a></span></h2>
	<p class="description"><?php _e('Apply a watermark on all your photographies. This action is cancelable just by deactivating the plugin. <br>The watermark will be applied even in your photos already uploaded.', $this->plugin_name); ?></p>
	<form action="" method="POST" enctype="multipart/form-data">

		<table class="form-table">
			<tbody>
				<tr>
					<th scope="row">
						<label for="size"><span class="dashicons dashicons-format-gallery"></span> <?php _e('Images format', $this->plugin_name); ?></label>
					</th>
					<td>
						<fieldset>
							<legend class="screen-reader-text">
								<span><?php _e('Images format', $this->plugin_name); ?></span>
							</legend>
							<?php foreach($plugin->get_image_sizes() as $image_size) { $plugin->render_image_sizes($image_size); } ?>
							<label for="size_fullsize">
								<input type="checkbox" value="fullsize" name="sizes[]" id="size_fullsize" <?php if( in_array('fullsize', $plugin->get_watermark_width()) ) { ?>checked<?php } ?>>
								<strong><?php _e('fullsize', $this->plugin_name); ?></strong> <span class="small">(<?php _e('original image not resized by Wordpress', $this->plugin_name); ?>)</span>
							</label>
						</fieldset>
						
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="watermarkFile"><span class="dashicons dashicons-tag"></span> <?php _e('Choose a watermark', $this->plugin_name); ?></label>
					</th>
					<td>
						<input type="file" name="watermarkFile" id="watermarkFile">
						<p class="description">*<?php _e('For transparent background watermark, use a PNG image.', $this->plugin_name); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for=""><span class="dashicons dashicons-welcome-view-site"></span> <?php _e('Preview', $this->plugin_name); ?></label>
					</th>
					<td>
						<?php $watermark = getimagesize(WP_PLUGIN_URL . '/' . $plugin->plugin_name . '-data' . '/watermark.png?' . rand(1,10000)); ?>
						<?php if($watermark[0] > 200) { $width = '200'; } else { $width = $watermark[0]; } ?>
						<img class="watermark" width="<?php echo $width; ?>" src="<?php echo WP_PLUGIN_URL . '/' . $plugin->plugin_name . '-data' . '/watermark.png?' . rand(1,10000); ?>" alt="">
						<p class="description"> <?php _e('Real size', $this->plugin_name); ?> : <?php $watermarkSize = $this->get_watermark_size(); echo $watermarkSize[0]; ?> x <?php echo $watermarkSize[1]; ?>px</p>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for=""><span class="dashicons dashicons-welcome-learn-more"></span> <?php _e('Support', $this->plugin_name); ?></label>
					</th>
					<td>
						<p><?php _e('alti Watermark Plugin <a href="http://www.alticreation.com/en/alti-watermark/" target="_blank">support page</a>.', $this->plugin_name); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row">
					</th>
					<td>
						<input type="submit" id="submit" value="<?php _e('Update', $this->plugin_name); ?>" name="submit" class="button button-primary">
					</td>
				</tr>
			</tbody>
		</table>
	</form>
	<div id="message" class="updated alti-watermark-footer">
		<a class="logo" href="http://www.alticreation.com?plugin=alti-watermark"><img src="http://alticreation.com/logos/alticreation_color_01.png" alt="alticreation"></a>
		<p><?php _e('alti Watermark plugin is developped by', $this->plugin_name); ?> <a href="http://www.alticreation.com/en/profile">Alexis Blondin</a>.</p>
		<div class="share">
			<a href="http://www.alticreation.com?plugin=alti-watermark" target="_blank">alticreation.com</a>
			<a href="https://plus.google.com/+AlexisBlondin" target="_blank">google&nbsp;+</a>
			<a href="https://twitter.com/alticreation" target="_blank">twitter</a>
		</div>
	</div>
</div>

