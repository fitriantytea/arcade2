<div class="navbar-collapse collapse justify-content-end" id="navb">
	<ul class="navbar-nav ml-auto text-uppercase">
		<?php render_nav_menu('top_nav', array(
			'no_ul'	=> true,
			'li_class' => 'nav-item',
			'a_class' => 'nav-link',
		)); ?>
		<li class="nav-item">
			<?php
			if(is_null($login_user)){
				if(get_setting_value('show_login')){
					echo('<a class="nav-link" href="'.get_permalink('login').'">'._t('Login').'</a>');
				}
			}
			?>
		</li>
	</ul>
	<form class="form-inline my-2 my-lg-0 search-bar" action="/index.php">
		<div class="input-group">
			<input type="hidden" name="viewpage" value="search" />
			<input type="text" class="form-control rounded-left search" placeholder="<?php _e('Search game') ?>" name="slug" minlength="2" required />
			<div class="input-group-append">
				<button type="submit" class="btn btn-secondary" type="button">
					<i class="fa fa-search"></i>
				</button>
			</div>
		</div>
	</form>
</div>