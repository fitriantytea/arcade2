<div class="addgame-wrapper" id="json">
	<p>Bulk import your game data with JSON format.</p>
	<p>Read "User Documentation" for sample JSON structure or code.</p>
	<p>Open browser log to see the import progress.</p>
	<p>Paste your JSON data below.</p>
	<form id="form-json">
		<div class="mb-3">
			<label class="form-label" for="json-importer">JSON data:</label>
			<textarea class="form-control" name="json-importer" rows="8" required /></textarea>
		</div>
		<button type="submit" class="btn btn-primary btn-md"><?php _e('Import') ?></button>
	</form>
	<br>
	<p>Preview JSON data (Game list) before submited.</p>
	<button class="btn btn-primary btn-md" id="json-preview"><?php _e('Preview') ?></button>
	<br><br>
	<table class="table" style="display: none;" id="table-json-preview">
		<thead>
			<tr>
				<th>#</th>
				<th><?php _e('Title') ?></th>
				<th><?php _e('Slug') ?></th>
				<th><?php _e('URL') ?></th>
				<th><?php _e('Width') ?></th>
				<th><?php _e('Height') ?></th>
				<th><?php _e('Thumb') ?> 1</th>
				<th><?php _e('Thumb') ?> 2</th>
				<th><?php _e('Category') ?></th>
				<th><?php _e('Source') ?></th>
			</tr>
		</thead>
		<tbody id="json-list-preview">
		</tbody>
	</table>
</div>