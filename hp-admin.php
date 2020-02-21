<div class="wrap hp-options-wrapper">
	<h2>Reservia</h2>
	<p>下記のコードをコピーして、表示したいページに「テキスト」モードで貼り付けてください。<br>ショートコード：<input type="text" value="[reservia_review]" onclick="this.select()" readonly></p>

	<form method="post" action="options.php">
		<?php
			settings_fields($this->option_group);
			do_settings_sections($this->menu_slug);
			submit_button();
		?>
		<div class="hp-option-submit">
			<?php submit_button(); ?>
		</div>
	</form>
</div>

