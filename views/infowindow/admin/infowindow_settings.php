<div id="infowindow-settings">
	<fieldset>
		<legend><?php print form::label("show_custom_forms","Show Custom forms in the infowindow ? "); ?></legend>
		<?php print form::label("show_custom_forms","Show Infowindow"); ?>: 
		<?php print form::checkbox("show_custom_forms","show",$form["show_custom_forms"]); ?>
	</fieldset>
	<br />
	<fieldset>
		<legend><?php print form::label("show_images","Show images in the infowindow ? "); ?></legend>
		<?php print form::label("show_images","Show Images"); ?>: 
		<?php print form::checkbox("show_images","show",$form["show_images"]); ?>
	</fieldset>
</div>

