<?php

class Widget_HTML extends Widget {
	function __construct() {
 		$this->name = 'HTML';
 		$this->id_base = 'html';
 		$this->description = 'Show HTML / TEXT';
	}
	public function widget( $instance, $args = array() ){
		echo $instance['text'];
	}

	public function form( $instance = array() ){

		if(!isset( $instance['text'] )){
			$instance['text'] = '';
		}
		?>
		<div class="mb-3">
			<label class="form-label">HTML / TEXT:</label>
			<textarea class="form-control" rows="5" name="text"><?php echo $instance['text'] ?></textarea>
		</div>
		<?php
	}
}

register_widget( 'Widget_HTML' );

class Widget_Paragraph extends Widget {
	function __construct() {
 		$this->name = 'Paragraph';
 		$this->id_base = 'paragraph';
 		$this->description = 'Show text paragraph (HTML not allowed)';
	}
	public function widget( $instance, $args = array() ){
		if(!isset( $instance['text'] )){
			$instance['text'] = '';
		}
		if(!isset( $instance['align'] )){
			$instance['align'] = 'none';
		}
		$align_class = null;
		if($instance['align'] != 'none'){
			if($instance['align'] == 'left'){
				$align_class = 'text-start text-left';
			} else if($instance['align'] == 'center'){
				$align_class = 'text-center';
			} else if($instance['align'] == 'right'){
				$align_class = 'text-end text-right';
			}
		}
		echo '<p'.($align_class ? ' class="' . $align_class . '"' : '').'>';
		echo nl2br(htmlentities($instance['text']));
		echo '</p>';
	}

	public function form( $instance = array() ){

		if(!isset( $instance['text'] )){
			$instance['text'] = '';
		}
		if(!isset( $instance['align'] )){
			$instance['align'] = 'none';
		}
		?>
		<div class="mb-3">
			<label class="form-label">TEXT:</label>
			<textarea class="form-control" rows="5" name="text"><?php echo $instance['text'] ?></textarea>
		</div>
		<div class="mb-3">
			<label class="form-label"><?php _e('Align') ?>:</label>
			<select class="form-control" name="align">
				<?php

				$opts = array(
					'none' => 'None',
					'left' => 'Left',
					'center' => 'Center',
					'right' => 'Right'
				);

				foreach ($opts as $key => $value) {
					$selected = '';
					if($key == $instance['align']){
						$selected = 'selected';
					}
					echo '<option value="'.$key.'" '.$selected.'>'.$value.'</option>';
				}
				?>
			</select>
		</div>
		<?php
	}
}

register_widget( 'Widget_Paragraph' );

class Widget_Heading extends Widget {
	function __construct() {
 		$this->name = 'Heading';
 		$this->id_base = 'heading';
 		$this->description = 'Heading typography, can be used as widget title or label.';
	}
	public function widget( $instance, $args = array() ){
		if(!isset( $instance['tag'] )){
			$instance['tag'] = 'h3';
		}
		if(!isset( $instance['class'] )){
			$instance['class'] = '';
		}
		if(!isset( $instance['text'] )){
			$instance['text'] = '';
		}
		echo '<'.$instance['tag'].' class="'.$instance['class'].'">';
		echo htmlentities($instance['text']);
		echo '</'.$instance['tag'].'>';
	}

	public function form( $instance = array() ){

		if(!isset( $instance['tag'] )){
			$instance['tag'] = 'h3';
		}
		if(!isset( $instance['class'] )){
			$instance['class'] = '';
		}
		if(!isset( $instance['text'] )){
			$instance['text'] = '';
		}
		?>
		<div class="mb-3">
			<label class="form-label"><?php _e('Heading tag') ?>:</label>
			<select class="form-control" name="tag">
				<?php

				$opts = array(
					'h1' => 'h1',
					'h2' => 'h2',
					'h3' => 'h3',
					'h4' => 'h4',
					'h5' => 'h5',
					'div' => 'div',
				);

				foreach ($opts as $key => $value) {
					$selected = '';
					if($key == $instance['tag']){
						$selected = 'selected';
					}
					echo '<option value="'.$key.'" '.$selected.'>'.$value.'</option>';
				}
				?>
			</select>
		</div>
		<div class="mb-3">
			<label class="form-label">TEXT:</label>
			<textarea class="form-control" rows="5" name="text"><?php echo $instance['text'] ?></textarea>
		</div>
		<div class="mb-3">
			<label class="form-label"><?php _e('Div class (Optional)') ?>:</label>
			<input type="text" class="form-control" name="class" placeholder="widget" value="<?php echo $instance['class'] ?>">
		</div>
		<?php
	}
}

register_widget( 'Widget_Heading' );

class Widget_Banner extends Widget {
	function __construct() {
 		$this->name = 'Banner Ad';
 		$this->id_base = 'banner_ad';
 		$this->description = 'Show banner advertisement';
	}
	public function widget( $instance, $args = array() ){
		echo '<div class="banner-ad-wrapper"><div class="banner-ad-content" style="padding: 20px 0; text-align: center;">';
		echo $instance['text'];
		echo '</div></div>';
	}

	public function form( $instance = array() ){
		if(!isset( $instance['text'] )){
			$instance['text'] = '';
		}
		?>
		<p>This widget is similar to HTML widget, the difference is that it comes with a banner div to fit the theme style. You can also style it on theme style.css</p>
		<div class="mb-3">
			<label class="form-label">HTML / TEXT:</label>
			<textarea class="form-control" rows="5" name="text"><?php echo $instance['text'] ?></textarea>
		</div>
		<?php
	}
}

register_widget( 'Widget_Banner' );

class Widget_URL_Link extends Widget {
	function __construct() {
 		$this->name = 'URL Link';
 		$this->id_base = 'url_link';
 		$this->description = 'Create a link with customizable text or image';
	}

	public function widget( $instance, $args = array() ){
		$target = !empty($instance['new_tab']) ? ' target="_blank"' : '';
		$class = !empty($instance['class']) ? ' class="'.htmlspecialchars($instance['class']).'"' : '';
		
		if ($instance['display_type'] === 'image' && !empty($instance['image_url'])) {
			$content = '<img src="'.htmlspecialchars($instance['image_url']).'" alt="'.htmlspecialchars($instance['url']).'">';
		} else {
			$content = htmlspecialchars($instance['link_text']);
		}
		
		// Output the link
		echo '<a href="'.htmlspecialchars($instance['url']).'"'.$target.$class.'>'.$content.'</a>';
		
		// Add line break if the option is enabled
		if (!empty($instance['line_break'])) {
			echo '<br>';
		}
	}

	public function form( $instance = array() ){
		$url = isset($instance['url']) ? $instance['url'] : 'https://';
		$link_text = isset($instance['link_text']) ? $instance['link_text'] : '';
		$class = isset($instance['class']) ? $instance['class'] : '';
		$new_tab = isset($instance['new_tab']) ? $instance['new_tab'] : '';
		$image_url = isset($instance['image_url']) ? $instance['image_url'] : '';
		$display_type = isset($instance['display_type']) ? $instance['display_type'] : 'text';
		$line_break = isset($instance['line_break']) ? $instance['line_break'] : '1';
		?>
		<div class="mb-3">
			<label class="form-label">URL (required):</label>
			<input type="text" name="url" class="form-control" autocomplete="off" value="<?php echo htmlspecialchars($url); ?>" required>
		</div>
		<div class="mb-3">
			<label class="form-label">Display Type:</label>
			<select name="display_type" class="form-select" onchange="toggleDisplayType(this.value)">
				<option value="text" <?php echo $display_type == 'text' ? 'selected' : ''; ?>>Link Text</option>
				<option value="image" <?php echo $display_type == 'image' ? 'selected' : ''; ?>>Image</option>
			</select>
		</div>
		<div id="link_text_field" class="mb-3" style="display: <?php echo ($display_type === 'text') ? 'block' : 'none'; ?>">
			<label class="form-label">Link Text (required):</label>
			<input type="text" name="link_text" class="form-control" autocomplete="off" value="<?php echo htmlspecialchars($link_text); ?>" <?php echo ($display_type === 'text') ? 'required' : ''; ?>>
		</div>
		<div id="image_url_field" class="mb-3" style="display: <?php echo ($display_type === 'image') ? 'block' : 'none'; ?>">
			<label class="form-label">Image URL (required):</label>
			<input type="text" name="image_url" class="form-control" autocomplete="off" value="<?php echo htmlspecialchars($image_url); ?>" <?php echo ($display_type === 'image') ? 'required' : ''; ?>>
		</div>
		<div class="mb-3">
			<label class="form-label" for="url_link_new_tab">Open link in a new tab:</label>
			<input type="checkbox" name="new_tab" value="1" <?php echo $new_tab == '1' ? 'checked' : ''; ?> id="url_link_new_tab">
		</div>
		<div class="mb-3">
			<label class="form-label" for="url_link_line_break">Add Line Break:</label>
			<input type="checkbox" name="line_break" value="1" <?php echo $line_break == '1' ? 'checked' : ''; ?> id="url_link_line_break">
		</div>
		<div class="mb-3">
			<label class="form-label">Class (optional):</label>
			<input type="text" name="class" class="form-control" autocomplete="off" value="<?php echo htmlspecialchars($class); ?>">
		</div>
		<script>
			function toggleDisplayType(value) {
				if (value === 'image') {
					document.getElementById('link_text_field').style.display = 'none';
					document.getElementById('image_url_field').style.display = 'block';
				} else {
					document.getElementById('link_text_field').style.display = 'block';
					document.getElementById('image_url_field').style.display = 'none';
				}
			}
		</script>
		<?php
	}
}

register_widget( 'Widget_URL_Link' );

?>