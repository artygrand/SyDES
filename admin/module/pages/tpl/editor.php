<input type="hidden" id="id" value="<?php echo $page['id'];?>">
<div class="form-group">
	<div class="input-group">
		<label class="input-group-addon fullpath"><select name="parent_id" id="parent" class="ani" data-rel="no"><?php echo $parents;?></select><span id="path" class="ani"><?php echo str_replace($page['alias'],'', $page['fullpath']);?></span></label>
		<input type="text" class="form-control" name="alias" placeholder="<?php echo lang('slug');?>" value="<?php echo $page['alias'];?>">
		<span class="input-group-addon"><a href="..<?php echo (count(Admin::$config['sites'][Admin::$site]['locales']) > 1) ? '/' . Admin::$locale : '', '/', $page['id'];?>" data-toggle="tooltip" title="<?php echo lang('tip_view_page');?>"><?php echo lang('view_page');?></a></span>
	 </div>
</div>
<div class="form-group">
	<label><?php echo lang('page_title');?></label>
	<div class="input-group">
		<span class="input-group-addon"><?php echo Admin::$locale;?></span>
		<input type="text" name="title" class="form-control" value="<?php echo $page['title'];?>">
	</div>
</div>
<div class="form-group">
	<label><?php echo lang('page_content');?></label>
	<textarea class="form-control" rows="25" name="content" id="editor"><?php echo $page['content'];?></textarea>
</div>

<script src="/admin/ckeditor/ckeditor.js"></script>
<script src="/admin/ckfinder/ckfinder.js"></script>
<script>
var editor = CKEDITOR.replace('editor',{height:400});
CKFinder.setupCKEditor(editor,'/admin/ckfinder/') ;
</script>