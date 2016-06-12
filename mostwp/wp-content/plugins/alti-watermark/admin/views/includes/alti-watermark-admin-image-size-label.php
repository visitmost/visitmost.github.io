<?php 
$image_size['label'] = $image_size['width'] . 'x' . $image_size['height'] . 'px';
if( $image_size['width'] == 0 ) $image_size['label'] = 'auto x ' . $image_size['height'] . 'px';
if( $image_size['height'] == 0 ) $image_size['label'] = $image_size['width'] . 'px x auto';
 ?>
<label for="size_<?php echo $image_size['name']; ?>">
	<input type="checkbox" value="<?php echo $image_size['width']; ?>x<?php echo $image_size['height']; ?>" name="sizes[]" id="size_<?php echo $image_size['name']; ?>" <?php if( in_array($image_size['width'] . 'x' . $image_size['height'], $this->get_watermark_width()) ) { ?>checked<?php } ?>>
	<strong><?php echo $image_size['name']; ?></strong>	
	<em class="description"><?php echo $image_size['label']; ?></em> 
	<?php if($image_size['crop']) { ?><span title="<?php _e('cropped', $this->plugin_name); ?>" class="dashicons dashicons-image-crop"></span>
	
	<?php if( intval($image_size['width']) > 0 && intval($image_size['height']) > 0 ) { ?>
	<input type="hidden" value="<?php echo $image_size['width']; ?>x<?php echo $image_size['height']; ?>" name="cropped[]"><?php } } ?>
	<?php if( intval($image_size['width']) == 0 || intval($image_size['height']) == 0  ) { ?>
	<span class="alert">(<?php _e('select also fullsize to ensure this format works', $this->plugin_name); ?>)</span>
	<?php } ?>
</label>
<br>