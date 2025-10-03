<?php

if(file_exists( ABSPATH . TEMPLATE_PATH . '/options.php' )){ // Fix bug open the page but there is no theme options
	?>
	<div class="section section-full">
		<div class="general-wrapper">
			<?php
			if(file_exists( ABSPATH . TEMPLATE_PATH . '/options.php' )){
				require_once( ABSPATH . TEMPLATE_PATH . '/options.php' );
			}
			?>
		</div>
	</div>
	<?php
} else {
	echo '<h3>'._t('Current active theme doesn\'t support theme options!').'</h3>';
}

?>