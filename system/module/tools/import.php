<?php
/**
 * @package SyDES
 *
 * @copyright 2011-2015, ArtyGrand <artygrand.ru>
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

class ImportController extends Controller{
	public $name = 'import';

	public function index(){
		$data['sidebar_left'] = $this->getSideMenu('tools/import');
		$data['sidebar_right'] = ' ';
		$data['content'] = $this->load->view('tools/import', array('select' => H::select('table', false, $this->getTables(), 'class="form-control"')));
		$data['meta_title'] = t('module_import');
		$data['breadcrumbs'] = H::breadcrumb(array(
			array('title' => t('module_import'))
		));

		$this->response->data = $data;
	}

	public function get(){
		$t = $this->getTables();
		$t[] = 'pages';
		if (!isset($_GET['table']) or (!in_array($_GET['table'], $t))){
			throw new BaseException(t('error_empty_values_passed'));
		}
		if ($_GET['table'] == 'pages'){
			if (!isset($_GET['type']) or !in_array($_GET['type'], array_keys($this->config_site['page_types']))){
				throw new BaseException(t('unauthorized_request'));
			}
			$stmt = $this->db->query("SELECT MAX(id)+1 FROM pages");
			$maxId = $stmt->fetchColumn();
			$stmt = $this->db->query("SELECT id, parent_id, alias, status, layout, strftime('%d.%m.%Y', cdate, 'unixepoch') as cdate, position FROM pages WHERE type = '{$_GET['type']}' ORDER BY position");
			$rawPages = $stmt->fetchAll(PDO::FETCH_ASSOC);
			if (!$rawPages){
				$titles[0] = array('id','parent_id','alias','status','layout', 'cdate', 'position');
				foreach ($this->config_site['locales'] as $loc){
					$titles[0][] = "{$loc}_title";
					$titles[0][] = "{$loc}_content";
				}
			} else {
				$stmt = $this->db->query("SELECT key FROM pages_meta WHERE page_id IN (SELECT id FROM pages WHERE type = '{$_GET['type']}') GROUP BY key");
				$rawMetaTitles = $stmt->fetchAll(PDO::FETCH_COLUMN);
				$stmt = $this->db->query("SELECT page_id, key, value FROM pages_meta WHERE page_id IN (SELECT id FROM pages WHERE type = '{$_GET['type']}')");
				$rawMetaData = $stmt->fetchAll(PDO::FETCH_ASSOC);
				$stmt = $this->db->query("SELECT * FROM pages_content WHERE page_id IN (SELECT id FROM pages WHERE type = '{$_GET['type']}')");
				$rawPagesContent = $stmt->fetchAll(PDO::FETCH_ASSOC);
				foreach ($this->config_site['locales'] as $loc){
					$rawDataContentTitle[]['key'] = $loc . '_title';
					$rawDataContentTitle[]['key'] = $loc . '_content';
				}

				// set a title row
				$titles[0] = array_keys($rawPages[0]);
				foreach ($rawDataContentTitle as $title){
					$titles[0][] = $title['key'];
				}
				foreach ($rawMetaTitles as $title){
					$titles[0][] = $title;
				}

				// set a data rows
				foreach ($rawPagesContent as $data){
					$truDataContent[$data['page_id']][$data['locale'].'_title'] = $data['title'];
					$truDataContent[$data['page_id']][$data['locale'].'_content'] = $data['content'];
				}

				foreach ($rawMetaData as $meta){
					$truDataMeta[$meta['page_id']][$meta['key']] = $meta['value'];
				}

				foreach ($rawPages as &$data){
					foreach ($rawDataContentTitle as $title){
						$data[$title['key']] = $truDataContent[$data['id']][$title['key']];
					}
					foreach ($rawMetaTitles as $title){
						$data[$title['key']] = $truDataMeta[$data['id']][$title];
					}
				}

				$titles = array_merge($titles, $rawPages);
			}
			$titles[] = array(sprintf(t('csv_tip'), $maxId));
		} else {
			$stmt = $this->db->query("SELECT * FROM {$_GET['table']} ORDER BY id");
			$rawData = $stmt->fetchAll(PDO::FETCH_ASSOC);
			if ($rawData){
				$titles[0] = array_keys($rawData[0]);
				$titles = array_merge($titles, $rawData);
			} else {
				$stmt = $this->db->query("PRAGMA table_info({$_GET['table']})");
				$rawData = $stmt->fetchAll(PDO::FETCH_ASSOC);
				foreach ($rawData as $data){
					$titles[0][] = $data['name'];
				}
			}
		}
		$t = isset($_GET['type']) ? $_GET['type'] : $_GET['table'];
		$this->response->mime = 'csv';
		$this->response->addHeader("Content-Disposition: attachment;filename=syd_{$t}_" . date("Ymd") . ".{$_POST['encoding']}.csv");
		$t = '';
		foreach ($titles as $row){
			$t .= '"' . implode('";"', str_replace('"', '""', $row)) . '"' . PHP_EOL;
		}
		if ($_POST['encoding'] == 'cp1251'){
			$t = iconv('utf-8', 'cp1251//TRANSLIT', $t);
		}

		$this->response->body = htmlspecialchars_decode($t);
	}

	public function put(){
		$t = $this->getTables();
		$t[] = 'pages';
		if (!isset($_GET['table']) or (!in_array($_GET['table'], $t))){
			throw new BaseException(t('error_empty_values_passed'));
		}
		$meta = explode('.', $_FILES['file']['name']);
		if (!is_uploaded_file($_FILES['file']['tmp_name']) or array_pop($meta) != 'csv'){
			throw new BaseException(t('file_not_exist'));
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
		foreach ($arr as $r){
			$rows[] = str_getcsv($r, ';', '"');
		}
		unset($arr, $csv);

		$headers = array_shift($rows);
		if ($_GET['table'] == 'pages'){
			$beforeMeta = count($this->config_site['locales'])*2;
			$meta_keys = array_slice($headers, 7 + $beforeMeta);
			foreach ($rows as $r){
				if (!is_numeric($r[0])){
					continue;
				}
				$id = $r[0];
				$raw['main'][$id] = array_slice($r, 0, 7);
				$raw['main'][$id][5] = strtotime($raw['main'][$id][5]);
				$tmp = array_chunk(array_slice($r, 7, $beforeMeta), 2);
				foreach ($tmp as $i => $t){
					if ($t[0] == ''){
						continue;
					}
					$raw['content'][$id.'_'.$i] = array(
						'page_id' => $id,
						'locale' => $this->config_site['locales'][$i],
						'title' => $t[0],
						'content' => $t[1]
					);
					
				}
				$tmp = array_slice($r, 7 + $beforeMeta);
				foreach ($tmp as $i => $t){
					if ($t != ''){
						$raw['meta_upd'][$id.'_'.$i] = array(
							'page_id' => $id,
							'key' => $meta_keys[$i],
							'value' => $t
						);
					} else {
						$raw['meta_del'][$id.'_'.$i] = array(
							'page_id' => $id,
							'key' => $meta_keys[$i]
						);
					}
				}
				$pages[$id] = $r[1]; // id = parent
			}

			if (isset($raw['main'][1])){
				$raw['main'][1][1] = 0;
				$raw['main'][1][2] = '';
			}

			//insert in database
			$this->db->beginTransaction();
			if (isset($raw['main'])){
				$stmt = $this->db->prepare("INSERT OR REPLACE INTO pages (id, parent_id, alias, status, layout, cdate, position, type) VALUES (?,?,?,?,?,?,?,'{$_GET['type']}')");
				foreach ($raw['main'] as $data){
					$stmt->execute($data);
				}
			}

			if (isset($raw['content'])){
				$stmt = $this->db->prepare("INSERT OR REPLACE INTO pages_content (page_id, locale, title, content) VALUES (:page_id, :locale, :title, :content)");
				foreach ($raw['content'] as $data){
					$stmt->execute($data);
				}
			}

			if (isset($raw['meta_upd'])){
				$stmt = $this->db->prepare("INSERT OR REPLACE INTO pages_meta (page_id, key, value) VALUES (:page_id, :key, :value)");
				foreach ($raw['meta_upd'] as $data){
					$stmt->execute($data);
				}
			}
			if (isset($raw['meta_del'])){
				$stmt = $this->db->prepare("DELETE FROM pages_meta WHERE page_id = :page_id AND key = :key");
				foreach ($raw['meta_del'] as $data){
					$stmt->execute($data);
				}
			}
			$this->db->commit();

			// generate position and full paths
			$pages_model = $this->load->model('pages', false);
			if ($this->config_site['page_types'][$_GET['type']]['structure'] == 'tree'){
				$pages_model->rebuildPositions($pages);
			} else {
				$this->db->exec("UPDATE pages SET position = 100 WHERE position = '' AND type = '{$_GET['type']}'");
			}
			$pages_model->rebuildPaths();
		} else {
			$count = count($headers);
			$values = str_pad('?', ($count*2)-1, ',?');
			$this->db->beginTransaction();
			$stmt = $this->db->prepare("INSERT OR REPLACE INTO {$_GET['table']} VALUES ({$values})");
			foreach ($arr as $data){
				if (count($data) == $count){
					$stmt->execute($data);
				}
			}
			$this->db->commit();
		}

		$this->cache->clear();
		$this->response->notify(t('saved'));
		$this->response->redirect('?route=tools/import');
	}

	private function getTables(){
		$tables[0] = t('select_table');
		foreach ($this->config_site['page_types'] as $type => $data){
			if ($type == 'trash'){
				continue;
			}
			$tables['pages&type=' . $type] = $data['title'];
		}
		$skip = array('pages','pages_content','pages_meta','config','routes');
		$skip = "'" . implode("','", $skip) . "'";
		$stmt = $this->db->query("SELECT name FROM sqlite_master WHERE name NOT IN ({$skip}) AND name NOT LIKE 'sqlite_%'");
		$raw = $stmt->fetchAll(PDO::FETCH_ASSOC);
		if ($raw){
			foreach ($raw as $row){
				$tables[$row['name']] = $row['name'];
			}
		}
		return $tables;
	}
}