<?php
/**
* Module. Import/export all tables from/in CSV.
* @version: 1.3.0
* @author ArtyGrand
*/
 
class Import extends Module{
	/**
	* Sets the native module name for menu
	* @var array
	*/
	public static $nativeName = array(
		'ru' => 'Импорт/Экспорт',
		'en' => 'Import/Export',
		'de' => 'Import/Export'
	);
	
	/**
	* Show "add more" button in menu?
	* @var bool
	*/
	public static $quickAdd = false;
	
	/**
	* Sets the allowed actions for user
	* @var array
	*/
	public static $allowedActions = array('view', 'get', 'put');
	
	/**
	* Sets module language package
	* @var array
	*/
	public $ml = array(
		'ru' => array(
			'export' => 'Экспорт',
			'import' => 'Импорт',
			'select_table' => 'Выберите таблицу',
			'download' => 'Скачать CSV файл',
			'upload' => 'Залить CSV файл',
			'csv_tip' => 'При добавлении новых страниц начинайте с id = %d.
При изменении алиаса, измените так же fullpath дочерних страниц.
При сохранении убедитесь что кодировка UTF-8, а разделитель - ;
Вы можете удалить эту ячейку.'
		),
		'en' => array(
			'export' => 'Export',
			'import' => 'Import',
			'select_table' => 'Select the table',
			'download' => 'Download CSV file',
			'upload' => 'Upload CSV file',
			'csv_tip' => 'When you add new pages, start with id = %d.
If you change the alias, also change fullpath of child pages.
When you save, make sure that you use UTF-8 encoding and separator - ;
You can delete this cell.'
		)
	);
	public $notUserTables = array('pages','pages_content','pages_meta','page_types','config_meta','access');
	
	function __construct(){
		$this -> setModuleName();
		parent::__construct();
	}
	
	public function install(){
		$this -> setModuleName();
		// create table and add settings, if needed. Or just
		$this -> register();
	}
	
	/**
	* Get import and export interfaces
	* @return array
	*/
	public function view(){
		$p['jsfunc'] = "
	function exprt(){window.location = '?mod=import&act=get&table=' + $('#exp option:selected').val()}
	";
		$p['style'] = ".content input{font-size: 12px;} .span4:first-child .list{text-align:right;}";

		$t = $this -> getTables(false);
		$tables = array_merge(array(array('name'=>lang('select_table'),'link'=>'')), $t['type'], $t['tables']);


		$select = '<select id="exp" class="full">' . PHP_EOL;
		foreach($tables as $table){
			$select .= '	<option value="' . $table['link'] . '">' . $table['name'] . '</option>' . PHP_EOL;
		}
		$select .= '</select>' . PHP_EOL;

		$p['content'] = '<form enctype="multipart/form-data" method="post" action="?mod=import&act=put" class="form"><div class="span4"><div class="list">' . lang('export') . '</div><div class="list">' . lang('import') . '</div></div><div class="span4"><div class="list">' . $select . '</div>';
		$select = str_replace('id="exp"', 'id="imp" name="table"', $select);
		$p['content'] .= '<div class="list">' . $select . '</div></div><div class="span4"><div class="list"></div><div class="list"><input type="file" name="file" accept="text/comma-separated-values,text/csv,application/csv" required></div></div><div class="span4"><div class="list"><button class="small button" type="button" onClick="exprt()">' . lang('download') . '</button></div><div class="list"><button class="small button" type="submit" >' . lang('upload') . '</button></div></div></form>';

		$p['breadcrumbs'] = self::$nativeName[Core::$language];
		return $p;
	}

	public function get(){
		$t = $this -> getTables();
		$t[] = 'pages';
		if (!$_GET['table'] or (!in_array($_GET['table'], $t))){
			throw new Exception(lang('no_value'));
		}

		if ($_GET['table'] == 'pages'){
			$stmt = Core::$db -> prepare("SELECT id, parent_id, alias, fullpath, status, template FROM pages WHERE type = :type ORDER BY fullpath");
			$stmt -> execute(array('type' => $_GET['type']));
			if(!$rawDataBase = $stmt -> fetchAll(PDO::FETCH_ASSOC)) throw new Exception(lang('unauthorized_request'));

			$stmt = Core::$db -> prepare("SELECT key FROM pages_meta WHERE page_id IN (SELECT id FROM pages WHERE type = :type) GROUP BY key");
			$stmt -> execute(array('type' => $_GET['type']));
			$rawDataMetaTitle = $stmt -> fetchAll(PDO::FETCH_ASSOC);

			$stmt = Core::$db -> prepare("SELECT page_id, key, value FROM pages_meta WHERE page_id IN (SELECT id FROM pages WHERE type = :type)");
			$stmt -> execute(array('type' => $_GET['type']));
			$rawDataMeta = $stmt -> fetchAll(PDO::FETCH_ASSOC);

			$stmt = Core::$db -> prepare("SELECT * FROM pages_content WHERE page_id IN (SELECT id FROM pages WHERE type = :type)");
			$stmt -> execute(array('type' => $_GET['type']));
			$rawDataPageContent = $stmt -> fetchAll(PDO::FETCH_ASSOC);
			
			$stmt = Core::$db -> query("SELECT MAX(id)+1 FROM pages");
			$maxId = $stmt -> fetchColumn();

			foreach(Core::$config['locale'] as $loc){
				$rawDataContentTitle[]['key'] = $loc . '_title';
				$rawDataContentTitle[]['key'] = $loc . '_content';
			}
			
			// set a title row
			$titles[0] = array_keys($rawDataBase[0]);
			foreach($rawDataContentTitle as $title){
				$titles[0][] = $title['key'];
			}
			foreach($rawDataMetaTitle as $title){
				$titles[0][] = $title['key'];
			}
			
			// set a data rows
			foreach($rawDataPageContent as $data){
				$truDataContent[$data['page_id']][$data['locale'].'_title'] = $data['title'];
				$truDataContent[$data['page_id']][$data['locale'].'_content'] = $data['content'];
			}
			
			foreach($rawDataMeta as $meta){
				$truDataMeta[$meta['page_id']][$meta['key']] = $meta['value'];
			}

			foreach($rawDataBase as &$data){
				foreach($rawDataContentTitle as $title){
					@$data[$title['key']] = $truDataContent[$data['id']][$title['key']];
				}
				foreach($rawDataMetaTitle as $title){
					@$data[$title['key']] = $truDataMeta[$data['id']][$title['key']];
				}
			}
			
			$titles = array_merge($titles, $rawDataBase);
			$titles[] = array(sprintf(lang('csv_tip'), $maxId));
			
		} else {
			$stmt = Core::$db -> query("SELECT * FROM {$_GET['table']} ORDER BY id");
			$rawData = $stmt -> fetchAll(PDO::FETCH_ASSOC);
			if ($rawData){
				$titles[0] = array_keys($rawData[0]);
				$titles = array_merge($titles, $rawData);
			} else {
				$stmt = Core::$db -> query("PRAGMA table_info({$_GET['table']})");
				$rawData = $stmt -> fetchAll(PDO::FETCH_ASSOC);
				foreach($rawData as $data){
					$titles[0][] = $data['name'];
				}
			}
		}
		
		$t = isset($_GET['type']) ? $_GET['type'] : $_GET['table'];
		header('Content-Type: text/csv');
		header('Content-Disposition: attachment;filename=syd_' . $t . '_' . date("Ymd") . '.csv');
		$fp = fopen('php://output', 'w');
		
		foreach($titles as &$title){
			fputcsv($fp, $title, ';', '"');
		}
		
		fclose($fp);
		die;
	}

	public function put(){
		$t = $this -> getTables();
		if (!$_POST['table'] or !in_array($_POST['table'], $t)){
			throw new Exception(lang('no_value'));
		}
		if(!is_uploaded_file($_FILES['file']['tmp_name']) or substr($_FILES['file']['name'], -4) != '.csv'){
			throw new Exception(lang('file_not_exist'));
		}
		if(($handle = fopen($_FILES['file']['tmp_name'], "r")) !== false){
			set_time_limit(0);
			$headers = fgetcsv($handle, 0, ';', '"');

			Core::$db -> beginTransaction();
			
			$path = explode('&type=', $_POST['table']);
			if ($path[0] == 'pages'){
				$beforeMeta = count(Core::$config['locale'])*2;
				$title['base'] = array_slice($headers, 0, 6);
				$title['content'] = array_slice($headers, 6, $beforeMeta);
				$title['meta'] = array_slice($headers, 6 + $beforeMeta);
				
				while(($data = fgetcsv($handle, 0, ';', '"')) !== FALSE){
					if ((int)$data[0] == 0){
						continue;
					}
					$raw['base'][] = array_slice($data, 0, 6);
					$tmp = array_chunk(array_slice($data, 6, $beforeMeta), 2);
					foreach($tmp as $i => $t){
						$raw['content'][$data[0].'_'.$i]['page_id'] = $data[0];
						$raw['content'][$data[0].'_'.$i]['locale'] = Core::$config['locale'][$i];
						$raw['content'][$data[0].'_'.$i]['title'] = $t[0];
						$raw['content'][$data[0].'_'.$i]['content'] = $t[1];
					}
					
					$tmp = array_slice($data, 6 + $beforeMeta);
					foreach($tmp as $i => $t){
						if($t){
							$raw['meta_upd'][$data[0].'_'.$i]['page_id'] = $data[0];
							$raw['meta_upd'][$data[0].'_'.$i]['key'] = $title['meta'][$i];
							$raw['meta_upd'][$data[0].'_'.$i]['value'] = $t;
						} else {
							$raw['meta_del'][$data[0].'_'.$i]['page_id'] = $data[0];
							$raw['meta_del'][$data[0].'_'.$i]['key'] = $title['meta'][$i];
						}
					}
					
				}

				$stmt = Core::$db -> prepare("INSERT OR REPLACE INTO pages (id, parent_id, alias, fullpath, status, template, type) VALUES (?,?,?,?,?,?,'{$path[1]}')");
				foreach($raw['base'] as $data){
					$stmt -> execute($data);
				}
				$stmt = Core::$db -> prepare("INSERT OR REPLACE INTO pages_content (page_id, locale, title, content) VALUES (:page_id, :locale, :title, :content)");
				foreach($raw['content'] as $data){
					$stmt -> execute($data);
				}
				
				$stmt = Core::$db -> prepare("INSERT OR REPLACE INTO pages_meta (page_id, key, value) VALUES (:page_id, :key, :value)");
				foreach($raw['meta_upd'] as $data){
					$stmt -> execute($data);
				}
				$stmt = Core::$db -> prepare("DELETE FROM pages_meta WHERE page_id = :page_id AND key = :key");
				foreach($raw['meta_del'] as $data){
					$stmt -> execute($data);
				}
			} else {
				$count = count($headers);
				$count = str_pad('?', ($count*2)-1, ',?');
				$stmt = Core::$db -> prepare("INSERT OR REPLACE INTO {$_POST['table']} VALUES ({$count})");
				while(($data = fgetcsv($handle, 0, ';', '"')) !== false){
					$stmt -> execute($data);
				}
			}
			Core::$db -> commit();
			fclose($handle);
			
			clearAllCache();
			
		$p['redirect']['url'] = '?mod=import';
		$p['redirect']['message'] = lang('saved');
		return $p;
		}
	}

	private function getTables($check = true){
		// get all user tables
		$not = "'" . implode("','", $this -> notUserTables) . "'";
		$stmt = Core::$db -> query("SELECT name, name AS link FROM sqlite_master WHERE name NOT IN ({$not}) AND name NOT LIKE 'sqlite_%'");
		$t['tables'] = $stmt -> fetchAll(PDO::FETCH_ASSOC);
		
		// get all page types
		$stmt = Core::$db -> query("SELECT type AS name, 'pages&type=' || type AS link FROM page_types");
		$t['type'] = $stmt -> fetchAll(PDO::FETCH_ASSOC);
		
		if($check){
			foreach($t['type'] as $type){
				$tables[] = $type['link'];
			}
			foreach($t['tables'] as $type){
				$tables[] = $type['link'];
			}
			return $tables;
		}
		return $t;
	}
 }
?>