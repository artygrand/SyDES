<?php
/**
* Infoblock: Language switcher
* Shows a links to other translations 
* Usage:
* {iblock:lang_switcher} = languages of this page and links to page
* {iblock:lang_switcher?root} = all languages and links to root
*/

if (count($this->config_site['locales']) == 1) return;

$links = array();
if (isset($args['root']) or !isset($page['id'])){
	foreach ($this->config_site['locales'] as $lang){
		$links[] = array(
			'lang' => $lang,
			'path' => $lang
		);
	}
} else {
	$stmt = $this->db->query("SELECT locale FROM pages_content WHERE page_id = {$page['id']}");
	$langs = $stmt->fetchAll(PDO::FETCH_COLUMN);
	foreach ($langs as $lang){
		$path = $page['id'] == 1 ? '' : $page['path'];
		$links[] = array(
			'lang' => $lang,
			'path' => $lang . $path
		);
	}
}