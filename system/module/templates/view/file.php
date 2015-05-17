<textarea id="code" name="code" class="form-control" rows="30"><?=$content;?></textarea>

<script>
$(document).ready(function(){
	window.codemirror = CodeMirror.fromTextArea(
		document.getElementById("code"),
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
	codemirror.setSize(null, ($("#main").height()-60)+"px")
})
</script>