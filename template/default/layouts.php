<?php return array (
  'page' => 
  array (
    'name' => 'Page',
    'file' => 'page.html',
    'html' => '{content}
<div>{iblock:my}</div>',
  ),
  'news-item' => 
  array (
    'name' => 'News Item',
    'file' => 'page.html',
    'html' => '<div class="row">
	<div class="col-sm-8">
		<h1>{title}</h1>
		{content}
	</div>
	<div class="col-sm-4">
		{iblock:links?show=siblings}
	</div>
</div>',
  ),
);