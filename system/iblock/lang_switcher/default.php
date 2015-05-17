<ul class="lang_switcher">
<?php
$this->response->style[] = 'system/iblock/lang_switcher/style.css';
foreach ($links as $link){
	if ($this->locale == $link['lang']){
		echo '<li><span class="', $link['lang'], '">', $link['lang'], '</span></li>';
	} else {
		echo '<li><a href="', $link['path'], '" class="', $link['lang'], '">', $link['lang'], '</a></li>';
	}
}
?>
</ul>