<?php
/*✓*/
class Import{
	public $name = 'import';
	public static $allowed4html = array('view', 'put', 'get');
	public static $allowed4ajax = array();
	public static $allowed4demo = array('view');

	public function view(){
		$select = getSelect($this->getTables(), '', 'name="table" class="form-control"');
		$crumbs[] = array('title' => lang('module_name'));
		$r['contentCenter'] = render('module/import/tpl/view.php', array('select' => $select));
		$r['title'] = lang('module_name');
		$r['breadcrumbs'] = getBreadcrumbs($crumbs);
		$r['js'] = '$(document).on("submit",".importform",function(){$(this).prop("action", $(this).prop("action")+$(this).find("select").val())})';
		return $r;
	}

	public function get(){
		$t = $this->getTables();
		$t[] = 'pages';
		if (!isset($_GET['table']) or (!in_array($_GET['table'], $t))){
			throw new Exception(lang('no_value'));
		}
		if ($_GET['table'] == 'pages'){
			if (!isset($_GET['type']) or !in_array($_GET['type'], array_keys(Admin::$siteConfig['page_types']))){
				throw new Exception(lang('unauthorized_request'));
			}
			$stmt = Admin::$db->query("SELECT MAX(id)+1 FROM pages");
			$maxId = $stmt->fetchColumn();
			$stmt = Admin::$db->query("SELECT position, id, parent_id, alias, status, layout FROM pages WHERE type = '{$_GET['type']}' ORDER BY position, fullpath");
			$rawPages = $stmt->fetchAll(PDO::FETCH_ASSOC);
			if(!$rawPages){
				$t = isset($_GET['type']) ? $_GET['type'] : $_GET['table'];
				header('Content-Type: text/csv');
				header("Content-Disposition: attachment;filename=syd_{$t}_" . date("Ymd") . ".{$_POST['encoding']}.csv");
				$t = 'position;id;parent_id;alias;status;layout';
				foreach(Admin::$config['sites'][Admin::$site]['locales'] as $loc){
					$t .= ";{$loc}_title";
					$t .= ";{$loc}_content";
				}
				$t .= PHP_EOL . '"' . sprintf(lang('csv_tip'), $maxId) . '"';
				if ($_POST['encoding'] == 'cp1251'){
					$t = iconv('utf-8', 'cp1251//TRANSLIT', $t);
				}
				die($t);
			}
			$stmt = Admin::$db->query("SELECT key FROM pages_meta WHERE page_id IN (SELECT id FROM pages WHERE type = '{$_GET['type']}') GROUP BY key");
			$rawMetaTitles = $stmt->fetchAll(PDO::FETCH_ASSOC);
			$stmt = Admin::$db->query("SELECT page_id, key, value FROM pages_meta WHERE page_id IN (SELECT id FROM pages WHERE type = '{$_GET['type']}')");
			$rawMetaData = $stmt->fetchAll(PDO::FETCH_ASSOC);
			$stmt = Admin::$db->query("SELECT * FROM pages_content WHERE page_id IN (SELECT id FROM pages WHERE type = '{$_GET['type']}')");
			$rawPagesContent = $stmt->fetchAll(PDO::FETCH_ASSOC);
			foreach(Admin::$config['sites'][Admin::$site]['locales'] as $loc){
				$rawDataContentTitle[]['key'] = $loc . '_title';
				$rawDataContentTitle[]['key'] = $loc . '_content';
			}
			
			// set a title row
			$titles[0] = array_keys($rawPages[0]);
			foreach($rawDataContentTitle as $title){
				$titles[0][] = $title['key'];
			}
			foreach($rawMetaTitles as $title){
				$titles[0][] = $title['key'];
			}
			
			// set a data rows
			foreach($rawPagesContent as $data){
				$truDataContent[$data['page_id']][$data['locale'].'_title'] = $data['title'];
				$truDataContent[$data['page_id']][$data['locale'].'_content'] = $data['content'];
			}
			
			foreach($rawMetaData as $meta){
				$truDataMeta[$meta['page_id']][$meta['key']] = $meta['value'];
			}

			foreach($rawPages as &$data){
				foreach($rawDataContentTitle as $title){
					@$data[$title['key']] = $truDataContent[$data['id']][$title['key']];
				}
				foreach($rawMetaTitles as $title){
					@$data[$title['key']] = $truDataMeta[$data['id']][$title['key']];
				}
			}
			
			$titles = array_merge($titles, $rawPages);
			$titles[] = array(sprintf(lang('csv_tip'), $maxId));
		} else {
			$stmt = Admin::$db->query("SELECT * FROM {$_GET['table']} ORDER BY id");
			$rawData = $stmt->fetchAll(PDO::FETCH_ASSOC);
			if ($rawData){
				$titles[0] = array_keys($rawData[0]);
				$titles = array_merge($titles, $rawData);
			} else {
				$stmt = Admin::$db->query("PRAGMA table_info({$_GET['table']})");
				$rawData = $stmt->fetchAll(PDO::FETCH_ASSOC);
				foreach($rawData as $data){
					$titles[0][] = $data['name'];
				}
			}
		}
		$t = isset($_GET['type']) ? $_GET['type'] : $_GET['table'];
		header('Content-Type: text/csv');
		header("Content-Disposition: attachment;filename=syd_{$t}_" . date("Ymd") . ".{$_POST['encoding']}.csv");
		$t = '';
		foreach($titles as $row){
			$t .= '"' . implode('";"', str_replace('"', '""', $row)) . '"' . PHP_EOL;
		}
		if ($_POST['encoding'] == 'cp1251'){
			$t = iconv('utf-8', 'cp1251//TRANSLIT', $t);
		}
		echo $t;
		die;
	}

	public function put(){
		$t = $this->getTables();
		$t[] = 'pages';
		if (!isset($_GET['table']) or (!in_array($_GET['table'], $t))){
			throw new Exception(lang('no_value'));
		}
		$meta = explode('.', $_FILES['file']['name']);
		if(!is_uploaded_file($_FILES['file']['tmp_name']) or array_pop($meta) != 'csv'){
			throw new Exception(lang('file_not_exist'));
		}
		$csv = file_get_contents($_FILES['file']['tmp_name']);
		
		//ini_set('pcre.backtrack_limit',10000000); //if preg_match is failed
		if (count($meta) == 2){
			if ($meta[1] == 'cp1251'){
				$csv = iconv('cp1251', 'utf-8', $csv);
			}
		} elseif (!preg_match('//u', $csv)){
			$csv = iconv('cp1251', 'utf-8', $csv);
		}
		set_time_limit(0);
		$arr = explode(PHP_EOL, $csv);
		foreach($arr as &$r){
			$r = str_getcsv($r, ';', '"');
		}
		$headers = array_shift($arr);
		if ($_GET['table'] == 'pages'){
			$beforeMeta = count(Admin::$config['sites'][Admin::$site]['locales'])*2;
			$title['base'] = array_slice($headers, 0, 6);
			$title['content'] = array_slice($headers, 6, $beforeMeta);
			$title['meta'] = array_slice($headers, 6 + $beforeMeta);
			foreach($arr as $r){
				if (!is_numeric($r[1])){continue;}
				$raw['base'][$r[1]] = array_slice($r, 0, 6);
				$tmp = array_chunk(array_slice($r, 6, $beforeMeta), 2);
				foreach($tmp as $i => $t){
					$raw['content'][$r[1].'_'.$i]['page_id'] = $r[1];
					$raw['content'][$r[1].'_'.$i]['locale'] = Admin::$config['sites'][Admin::$site]['locales'][$i];
					$raw['content'][$r[1].'_'.$i]['title'] = $t[0];
					$raw['content'][$r[1].'_'.$i]['content'] = $t[1];
				}
				$tmp = array_slice($r, 6 + $beforeMeta);
				foreach($tmp as $i => $t){
					if($t){
						$raw['meta_upd'][$r[1].'_'.$i]['page_id'] = $r[1];
						$raw['meta_upd'][$r[1].'_'.$i]['key'] = $title['meta'][$i];
						$raw['meta_upd'][$r[1].'_'.$i]['value'] = $t;
					} else {
						$raw['meta_del'][$r[1].'_'.$i]['page_id'] = $r[1];
						$raw['meta_del'][$r[1].'_'.$i]['key'] = $title['meta'][$i];
					}
				}
			}
			//generate fullpath
			$stmt = Admin::$db->query("SELECT id, fullpath FROM pages");
			$paths = $stmt->fetchAll(PDO::FETCH_ASSOC);
			foreach($paths as $p){
				$fullpath[$p['id']] = $p['fullpath'];
			}
			unset($paths);
			foreach($raw['base'] as &$row){
				if (empty($row[2])){
					$row[2] = Admin::$siteConfig['page_types'][$_GET['type']]['root'];
				}
				if ($row[2] == 0){
					$row[6] = '/'. $row[3];
				} else {
					if (isset($raw['base'][$row[2]][6])){
						$row[6] = $raw['base'][$row[2]][6] . '/'. $row[3];
					} else {
						$row[6] = $fullpath[$row[2]] . '/'. $row[3];
					}
				}
			}
			
			//TODO разобраться с position, сделать интуитивную сортировку, убрать вообще столбец position
			
			
			//insert in database
			Admin::$db->beginTransaction();
			$stmt = Admin::$db -> prepare("INSERT OR REPLACE INTO pages (position, id, parent_id, alias, status, layout, fullpath, type) VALUES (?,?,?,?,?,?,?,'{$_GET['type']}')");
			foreach($raw['base'] as $data){
				$stmt -> execute($data);
			}
			$stmt = Admin::$db -> prepare("INSERT OR REPLACE INTO pages_content (page_id, locale, title, content) VALUES (:page_id, :locale, :title, :content)");
			foreach($raw['content'] as $data){
				$stmt -> execute($data);
			}
			
			$stmt = Admin::$db -> prepare("INSERT OR REPLACE INTO pages_meta (page_id, key, value) VALUES (:page_id, :key, :value)");
			foreach($raw['meta_upd'] as $data){
				$stmt -> execute($data);
			}
			$stmt = Admin::$db -> prepare("DELETE FROM pages_meta WHERE page_id = :page_id AND key = :key");
			foreach($raw['meta_del'] as $data){
				$stmt -> execute($data);
			}
			Admin::$db->commit();
		} else {
			$count = count($headers);
			$values = str_pad('?', ($count*2)-1, ',?');
			$stmt = Admin::$db->prepare("INSERT OR REPLACE INTO {$_GET['table']} VALUES ({$values})");
			Admin::$db -> beginTransaction();
			foreach ($arr as $data){
				if (count($data) == $count){
					$stmt -> execute($data);
				}
			}
			Admin::$db->commit();
		}
		clearCache();
		redirect('?mod=import', lang('saved'), 'success');
	}

	private function getTables(){
		$tables[0] = lang('select_table');
		foreach(Admin::$siteConfig['page_types'] as $row => $data){
			$tables['pages&type=' . $row] = $data['title'];
		}
		$skip = array('pages','pages_content','pages_meta','config_meta');
		$skip = "'" . implode("','", $skip) . "'";
		$stmt = Admin::$db->query("SELECT name FROM sqlite_master WHERE name NOT IN ({$skip}) AND name NOT LIKE 'sqlite_%'");
		$raw = $stmt->fetchAll(PDO::FETCH_ASSOC);
		if ($raw){
			foreach($raw as $row){
				$tables[$row['name']] = $row['name'];
			}
		}
		return $tables;
	}
}
?>