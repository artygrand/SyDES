<div class="row">
	<div class="col-sm-6 col-lg-4">
		<div class="form-group">
			<label class="control-label"><?=t('layout_name');?></label>
			<input type="text" class="form-control" name="name" value="<?=$name;?>">
		</div>
	</div>
	<div class="col-sm-0 col-lg-4">
	</div>
	<div class="col-sm-6 col-lg-4">
		<div class="form-group">
			<label class="control-label"><?=t('file');?></label>
			<?=$files;?>
		</div>
	</div>
</div>

<div class="form-group">
	<textarea id="html" name="html" class="form-control" rows="30"><?=$html;?></textarea>
</div>

<script>
$(document).ready(function(){
	window.codemirror = CodeMirror.fromTextArea(
		document.getElementById("html"),
		{
			mode:"<?=$mode;?>",
			lineNumbers:true,
			lineWrapping:true,
			matchBrackets:true,
			autoCloseBrackets:true,
			indentUnit:4,
			indentWithTabs:true,
			enterMode:"keep",
			tabMode:"shift"
		}
	)
	codemirror.setSize(null, ($("#main").height()-135)+"px")
})
</script>