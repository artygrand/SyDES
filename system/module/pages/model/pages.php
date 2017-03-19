<?php
/**
 * @package SyDES
 *
 * @copyright 2011-2015, ArtyGrand <artygrand.ru>
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

class PagesModel extends Model{
	public $in_url;
	private $use_alias;

	public function __construct(){
		parent::__construct();

		$this->in_url = count($this->config_site['locales']) > 1 ? $this->locale : '';
		$this->use_alias = $this->config_site['use_alias_as_path'];
	}

	public function create($main, $content, $meta = false){
		$main['alias'] = convertToAscii($main['alias'], $this->use_alias);

		if ($this->use_alias){
			$main['path'] = '/' . $main['alias'];
			$and = '';
		} else {
			$main['path'] = $this->getValue($main['parent_id'], 'path') . '/' . $main['alias'];
			$and = " AND parent_id = {$main['parent_id']}";
		}

		$stmt = $this->db->query("SELECT id FROM pages WHERE alias = '{$main['alias']}'{$and}"); 
		$exists = $stmt->fetchColumn();
		if ($exists) {
			$stmt = $this->db->query("SELECT MAX(id)+1 FROM pages"); 
			$myid = $stmt->fetchColumn();

			$main['alias'] = $main['alias'] . '-'.$myid;
			$main['path'] = $main['path'] . '-'.$myid;
		}

		if ($this->config_site['page_types'][$main['type']]['structure'] == 'tree'){
			$pos = $this->getLastChildPos($main['parent_id']) + 1;
			$parent_pos = $this->getValue($main['parent_id'], 'position');
			$main['position'] = $parent_pos . '#' . $pos;
		}

		$stmt = $this->db->prepare("INSERT INTO pages (parent_id, alias, path, position, status, layout, type, cdate)
		VALUES (:parent_id, :alias, :path, :position, :status, :layout, :type, :cdate)");
		$stmt->execute($main);

		$id = $this->db->lastInsertId();

		$stmt = $this->db->prepare("INSERT INTO pages_content VALUES (:id, :locale, :title, :content)");
		foreach ($content as $l => $c){
			$stmt->execute(array('id' => $id, 'locale' => $l, 'title' => $c['title'], 'content' => $c['content']));
		}

		if ($meta){
			$stmt = $this->db->prepare("INSERT INTO pages_meta (page_id, key, value) VALUES (:id, :key, :value)");
			foreach ($meta as $k => $v){
				if ($v == ''){
					continue;
				}
				$stmt->execute(array('id' => $id, 'key' => $k, 'value' => $v));
			}
		}

		return $id;
	}

	public function read($id){
		$stmt = $this->db->prepare("SELECT pages.*, pc.title, pc.content, pc.locale FROM pages
			LEFT JOIN pages_content pc ON pc.page_id = pages.id WHERE pages.id = :id");
		$stmt->execute(array('id' => $id));
		$page = $stmt->fetchAll(PDO::FETCH_ASSOC);

		if (!$page){
			return false;
		}

		foreach ($page as $locale){
			$titles[$locale['locale']] = $locale['title'];
			$contents[$locale['locale']] = $locale['content'];
		}
		$page[0]['title'] = $titles;
		$page[0]['content'] = $contents;
		$page[0]['fullpath'] = $page[0]['path'] == '/' ? $this->in_url : ($this->in_url ? $this->in_url . $page[0]['path'] : substr($page[0]['path'], 1));
		return $page[0];
	}

	public function update($id, $main, $content){
		$data = array('id' => $id, 'status' => $main['status'], 'layout' => $main['layout']);
		$addition = '';

		if ($main['position']){
			$addition .= ', position = :position';
			$data['position'] = $main['position'];
		}
		if ($main['cdate']){
			$addition .= ', cdate = :cdate';
			$data['cdate'] = $main['cdate'];
		}

		$old_alias = $this->getValue($id, 'alias');
		$main['alias'] = convertToAscii($main['alias'], $this->use_alias);
		if ($old_alias != $main['alias'] && $id > 1){
			$addition .= ', alias = :alias, path = :path';
			$data['alias'] = $main['alias'];

			if ($this->use_alias){
				$data['path'] = '/' . $main['alias'];
			} else {
				$parent_path = $this->getValue($main['parent_id'], 'path');
				$old_path = $parent_path . '/' . $old_alias;
				$data['path'] = $parent_path . '/' . $main['alias'];
				$l1 = strlen($old_path);
				$l2 = $l1+1;
				$this->db->exec("UPDATE pages SET path = replace(substr(path, 1, {$l1}), '{$old_path}', '{$data['path']}') || substr(path,{$l2}) WHERE path LIKE '{$old_path}/%'");
			}
		}

		$stmt = $this->db->prepare("UPDATE pages SET status = :status, layout = :layout{$addition} WHERE id = :id");
		$stmt->execute($data);

		$stmt = $this->db->prepare("INSERT OR REPLACE INTO pages_content VALUES (:id, :locale, :title, :content)");
		foreach ($content as $l => $c){
			$stmt->execute(array('id' => $id, 'locale' => $l, 'title' => $c['title'], 'content' => $c['content']));
		}
	}

	public function delete($id){
		if ($id < 2){
			return;
		}
		$this->db->exec("DELETE FROM pages WHERE id = {$id}");
		$this->db->exec("DELETE FROM pages_content WHERE page_id = {$id}");
		$this->db->exec("DELETE FROM pages_meta WHERE page_id = {$id}");
	}

	public function getByFilter($type, $filter){
		$filter_page = $filter_meta = $filter_content = $filter_meta_tables = $meta_cols = $meta_table = $meta_group = '';
		$show_meta = $this->config_site['page_types'][$type]['structure'] == 'list' ? $this->config_site['page_types'][$type]['list']['meta'] : false;
		if ($show_meta){
			foreach ($show_meta as $i => $m){
				$show_meta[$i] = ",\n MAX(CASE WHEN m.key='{$m}' THEN m.value ELSE NULL END) as '{$m}'";
			}
			$meta_cols = implode('', $show_meta);
			$meta_table = "\nLEFT JOIN pages_meta m ON p.id = m.page_id";
			$meta_group = "\nGROUP BY p.id";
		}

		$data = array('locale' => $this->locale);
		if (isset($filter['page'])){
			$filter_page = array();
			foreach ($filter['page'] as $key => $search){
				$filter_page[] = "p.{$key} {$search['condition']} :{$key}";
				$data[$key] = $search['value'];
			}
			$filter_page = implode("\n AND ", $filter_page);
		}

		if (isset($filter['content']['title'])){
			$search = $filter['content']['title'];
			$filter_content = "\n AND pc.title LIKE :title";
			$data['title'] = $search['value'];
		}

		if (isset($filter['meta'])){
			$i = 0;
			$filter_meta = array();
			foreach ($filter['meta'] as $key => $search){
				$int = (is_numeric($search['value']) || is_array($search['value'])) ? '+0' : '';
				$filter_meta_tables .= "\nINNER JOIN pages_meta m{$i} ON p.id = m{$i}.page_id";
				if ($search['condition'] == 'BETWEEN'){
					$filter_meta[] = "m{$i}.key = '{$key}' AND m{$i}.value{$int} BETWEEN :{$key}0 AND :{$key}1";
					$data[$key . '0'] = (int)$search['value'][0];
					$data[$key . '1'] = (int)$search['value'][1];
					
				} elseif ($search['condition'] == 'IN'){
					foreach ($search['value'] as $j => $val){
						$vals[] = ':' .$key . $j;
						$data[$key . $j] = (int)$val;
					}
					$vals = implode(',', $vals);
					$filter_meta[] = "m{$i}.key = '{$key}' AND m{$i}.value{$int} IN ({$vals})";
				} else {
					$filter_meta[] = "m{$i}.key = '{$key}' AND m{$i}.value{$int} {$search['condition']} :{$key}";
					$data[$key] = $search['value'];
				}
				$i++;
			}
			$filter_meta = "\n AND (" . implode(")\n AND (", $filter_meta) . ")";
		}

		if (isset($filter['skip'], $filter['limit'])){
			$int = in_array($filter['orderby'], array('title', 'parent_id', 'status', 'cdate')) ? '' : '+0';
			$end = "\nORDER BY {$filter['orderby']}{$int} {$filter['order']} LIMIT {$filter['skip']},{$filter['limit']}";

			// for pagination
			$total_sql = "SELECT count(*) FROM pages p\nLEFT JOIN pages_content pc ON p.id = pc.page_id AND pc.locale = :locale" . $filter_meta_tables . "\nWHERE " . $filter_page . $filter_content . $filter_meta;
			$stmt = $this->db->prepare($total_sql);
			foreach ($data as $param => $value){
				if (is_numeric($value)){
					$stmt->bindValue(':' . $param, $value, PDO::PARAM_INT);
				} else {
					$stmt->bindValue(':' . $param, $value);
				}
			}
			$stmt->execute();
			$this->total_pages = $stmt->fetchColumn();
			if ($this->total_pages == 0 || $this->total_pages < $filter['skip']){
				return false;
			}
		} else {
			$end = "\nORDER BY p.position";
		}

		$sql = "SELECT p.*, pc.title, pc.content" . $meta_cols . "\nFROM pages p\nLEFT JOIN pages_content pc ON p.id = pc.page_id AND pc.locale = :locale" .
		$filter_meta_tables . $meta_table . "\nWHERE " . $filter_page . $filter_content . $filter_meta . $meta_group . $end;

		$stmt = $this->db->prepare($sql);
		foreach ($data as $param => $value){
			if (is_numeric($value)){
				$stmt->bindValue(':' . $param, $value, PDO::PARAM_INT);
			} else {
				$stmt->bindValue(':' . $param, $value);
			}
		}

		$stmt->execute();
		$page = $stmt->fetchAll(PDO::FETCH_ASSOC);

		if (!$page){
			return false;
		}

		foreach ($page as $i => $p){
			$page[$i]['fullpath'] = $p['path'] == '/' ? $this->in_url : ($this->in_url ? $this->in_url . $p['path'] : substr($p['path'], 1));
		}

		return $page;
	}

	public function getFilter($type){
		$get = array();
		$filter['page']['type'] = array('condition' => '=', 'value' => $type);
		if (isset($this->request->get['filter'])){
			if ($this->request->get['filter'] == 'clear'){
				setcookie($type . '_filter', '');
			} else {
				$get = $this->request->get['filter'];
				setcookie($type . '_filter', serialize($get), time()+3600);
			}
		} elseif (isset($this->request->cookie[$type . '_filter'])){
			$get = unserialize($this->request->cookie[$type . '_filter']);
		}

		foreach ($get as $key => $value){
			if ($key == 'meta'){
				foreach ($value as $k => $meta){
					if (!empty($meta) || $meta === '0'){
						$filter['meta'][$k] = $this->parseFilter($meta);
					}
				}
			} else {
				if ($key == 'title'){
					$filter['content']['title'] = (strpos($value, '*') === false) ? array('value' => '%'.$value.'%', 'condition' => 'LIKE') : $this->parseFilter($value);
				} elseif (in_array($key, array('parent_id','status','cdate','layout'))){
					$filter['page'][$key] = $this->parseFilter($value);
				}
			}
		}
		
		return $filter;
	}

	private function parseFilter($value){
		$value = str_replace(array('&lt;','&gt;'), array('<','>'), $value);
		preg_match("/([<>*]?) ?([\w ,-]+) ?([*]?)/iu", $value, $output);

		if (empty($output[2]) && $output[2] != 0){
			return false;
		} else {
			$return['value'] = $output[2];
		}

		if ($output[1] == '*'){
			$return['condition'] = 'LIKE';
			$return['value'] = '%' . $return['value'];
		} elseif (in_array($output[1], array('>','<'))){
			$return['condition'] = $output[1];
		} elseif (!$output[1]){
			$return['condition'] = '=';
			if (strpos($output[2], ',') !== false){
				$return['condition'] = 'IN';
				$return['value'] = explode(',', $output[2]);
			} elseif (strpos($output[2], '-') !== false){
				$vals = explode('-', $output[2]);
				$vals[0] = trim($vals[0]);
				$vals[1] = trim($vals[1]);
				if (count($vals) == 2 && is_numeric($vals[0]) && is_numeric($vals[1])){
					$return['condition'] = 'BETWEEN';
					$return['value'] = $vals;
				}
			}
		}

		if ($output[3] == '*'){
			$return['condition'] = 'LIKE';
			$return['value'] .= '%';
		}

		return $return;
	}

	public function getValue($id, $key, $locale = false){
		if ($id == 0){
			return;
		}
		if (in_array($key, array('parent_id','alias','path','position','status','layout','type','cdate'))){
			$stmt = $this->db->prepare("SELECT {$key} FROM pages WHERE id = :id");
			$stmt->execute(array('id' => $id));
		} elseif (in_array($key, array('title','content'))){
			if (!$locale){
				$locale = $this->locale;
			}
			$stmt = $this->db->prepare("SELECT {$key} FROM pages_content WHERE page_id = :id AND locale = :locale");
			$stmt->execute(array('id' => $id, 'locale' => $locale));
		} else {
			$stmt = $this->db->prepare("SELECT value FROM pages_meta WHERE key = :key AND page_id = :id");
			$stmt->execute(array('key' => $key, 'id' => $id));
		}
		return $stmt->fetchColumn();
	}

	public function setValue($id, $key, $value, $locale = false){
		if ($id == 0){
			return;
		}
		if (in_array($key, array('parent_id','alias','path','position','status','layout','type'))){
			$stmt = $this->db->prepare("UPDATE pages SET {$key} = :value WHERE id = :id");
			$stmt->execute(array('value' => $value, 'id' => $id));
		} elseif (in_array($key, array('title','content'))){
			if (!$locale){
				$locale = $this->locale;
			}
			$stmt = $this->db->prepare("UPDATE pages_content SET {$key} = :value WHERE page_id = :id AND locale = :locale");
			$stmt->execute(array('value' => $value, 'id' => $id, 'locale' => $locale));
		} else {
			$stmt = $this->db->prepare("INSERT OR REPLACE INTO pages_meta (page_id, key, value) VALUES (:id, :key, :value)");
			$stmt->execute(array('key' => $key, 'value' => $value, 'id' => $id));
		}
	}

	public function delValue($id, $key, $locale = false){
		if (in_array($key, array('title','content'))){
			if (!$locale){
				$locale = $this->locale;
			}
			$stmt = $this->db->prepare("UPDATE pages_content SET {$key} = '' WHERE page_id = :id AND locale = :locale");
			$stmt->execute(array('id' => $id, 'locale' => $locale));
		} else {
			$stmt = $this->db -> prepare("DELETE FROM pages_meta WHERE page_id = :id AND key = :key");
			$stmt->execute(array('key' => $key, 'id' => $id));
		}
	}

	public function getLastChildPos($id){
		$stmt = $this->db->query("SELECT position FROM pages WHERE parent_id = {$id} AND position LIKE '#%' ORDER BY position DESC LIMIT 1");
		$path = $stmt->fetchColumn();
		if ($id > 1){
			if ($path){
				$pos = substr(strrchr($path, '#'), 1);
			} else {
				$pos = 999;
			}
		} else {
			$pos = substr($path, 1);
		}
		return $pos;
	}

	private function getBranchData($type){
		if (isset($this->_branchData)){
			return $this->_branchData;
		}
		$root = $this->config_site['page_types'][$type]['root'];
		$condition = $type == 'page' ? " AND (p1.type='page' OR p1.id = 0)" : '';
		$stmt = $this->db->query("SELECT p1.id, pc.title, p1.position, p1.path
		FROM pages p1, pages p2 
		LEFT JOIN pages_content pc ON pc.page_id = p1.id AND pc.locale = '{$this->locale}' 
		WHERE p2.id = {$root} AND (p1.position LIKE p2.position || '#%' OR p1.id = {$root}){$condition}
		ORDER BY p1.position");
		$this->_branchData = $stmt->fetchAll(PDO::FETCH_ASSOC);
		return $this->_branchData;
	}

	public function getParentSelect($type, $parent, $current){
		if ($current == 1){
			return '<option value="0"> ' . t('root') . '</option>';
		}
		$pages = $this->getBranchData($type);

		$listing = '';
		$disableLevel = 1800;
		$format = '	<option value="%d" %s%s>%s%s</option>';
		foreach ($pages as $page){
			if ($page['id'] == 1){
				continue;
			}
			if ($page['id'] == 0){
				$page['title'] = t('root');
			}

			$selected = $parent == $page['id'] ? 'selected' : '';
			$level = substr_count($page['position'], '#');
			$disabled = 'disabled';
			if ($current == $page['id']){
				$disableLevel = $level;
			} elseif ($disableLevel >= $level){
				$disabled = '';
				$disableLevel = 1800;
			}
			$shift = str_pad('',($level - 1) * 9,'¦ &nbsp;');
			if (empty($page['title'])){
				$page['title'] = t('no_translation');
			}
			$listing .= sprintf($format, $page['id'], $selected, $disabled, $shift, $page['title']) . PHP_EOL;
		}
		return $listing;
	}

	public function getParents($type){
		$pages = $this->getBranchData($type);
		foreach ($pages as $p){
			$p['fullpath'] = $p['path'] == '/' ? $this->in_url : ($this->in_url ? $this->in_url . $p['path'] : substr($p['path'], 1));
			if ($p['id'] == 0){
				$p['title'] = t('root');
			} elseif ($p['id'] == 1){
				continue;;
			}
			$return[$p['id']] = array(
				'title' => $p['title'],
				'fullpath' => $p['fullpath'],
			);
		}
		return $return;
	}

	public function canRebuildPaths(){
		if ($this->use_alias){
			$stmt = $this->db->query("SELECT alias FROM pages WHERE id > 1 GROUP BY alias HAVING count(alias) > 1");
			$duplicates = $stmt->fetchAll(PDO::FETCH_COLUMN);
			if ($duplicates){
				throw new BaseException(sprintf(t('error_found_alias_duplicates'), implode(', ', $duplicates)));
			}
		} else {
			$stmt = $this->db->query("SELECT alias FROM pages WHERE alias LIKE '%/%'");
			$aliases = $stmt->fetchAll(PDO::FETCH_COLUMN);
			if ($aliases){
				throw new BaseException(sprintf(t('error_found_alias_with_slash'), implode(', ', $aliases)));
			}
		}
	}

	public function rebuildPaths(){
		$stmt = $this->db->query("SELECT id, parent_id, alias FROM pages ORDER BY position");
		$pages = $stmt->fetchAll(PDO::FETCH_ASSOC);

		unset($pages[0]); // root
		if ($this->use_alias){
			foreach ($pages as $page){
				$update[] = array(
					'id' => $page['id'],
					'path' => '/' . $page['alias']
				);
			}
		} else {
			$source[0] = array('path' => '');
			foreach ($pages as $page){
				$source[$page['id']] = $page;
				$path = $source[$page['parent_id']]['path'] . '/' . $page['alias'];
				$source[$page['id']]['path'] = $path;
				$update[] = array(
					'id' => $page['id'],
					'path' => $path
				);
			}
		}

		$this->db->beginTransaction();
		$stmt = $this->db->prepare("UPDATE pages SET path = :path WHERE id = :id");
		foreach ($update as $data){
			$stmt->execute($data);
		}
		$this->db->commit();
	}

	public function rebuildPositions($pages){
		$stmt = $this->db->prepare("SELECT type FROM pages WHERE id = :id");
		$stmt->execute(array('id' => key($pages)));
		$type = $stmt->fetchColumn();

		if ($type == 'page'){
			$pages = array(1 => 0) + $pages;
			$first = 0;
			$sorted[0]['position'] = '';
		} else {
			reset($pages);
			$first = current($pages);
			$sorted[$first]['position'] = $this->getValue($first, 'position');
		}
		$last[$first] = 999;

		foreach ($this->config_site['page_types'] as $type => $data){
			if ($data['root'] == 0 || $data['structure'] == 'list') continue;
			$roots[$data['root']] = 1;
		}

		foreach ($pages as $id => $parent_id){
			$sorted[$id] = array(
				'position' => $sorted[$parent_id]['position'] . '#' . ++$last[$parent_id],
				'parent_id' => $parent_id,
				'id' => $id,
			);
			$last[$id] = 999;
			if (isset($roots[$id])){
				$old_position = $this->getValue($id, 'position');
				$l1 = strlen($old_position);
				$l2 = $l1+1;
				$this->db->exec("UPDATE pages SET position = replace(substr(position, 1, {$l1}), '{$old_position}', '{$sorted[$id]['position']}') || substr(position,{$l2}) WHERE position LIKE '{$old_position}#%'");
			}
		}
		unset($sorted[$first]);

		$this->db->beginTransaction();
		$stmt = $this->db->prepare("UPDATE pages SET position = :position, parent_id = :parent_id WHERE id = :id");
		foreach ($sorted as $page){
			$stmt->execute($page);
		}
		$this->db->commit();
	}

	public function setStatus($id, $status){
		$stmt = $this->db->query("SELECT type FROM pages WHERE id IN ({$id}) GROUP BY type");
		$type = $stmt->fetchColumn();

		if ($this->config_site['page_types'][$type]['structure'] == 'tree'){
			$this->db->exec("UPDATE pages SET status = {$status} WHERE EXISTS(
				SELECT * FROM pages p1
				WHERE p1.id IN ({$id}) AND (
					pages.position = p1.position
					OR pages.position LIKE p1.position || '#%' AND pages.status > {$status} 
					OR p1.position LIKE pages.position || '#%' AND pages.status < {$status}
				))"
			);
		} else {
			$this->db->exec("UPDATE pages SET status = {$status} WHERE id IN ({$id})");
		}
	}

	public function updPositionAfter($position, $delta = 0){
		$parent_position = substr($position, 1, strrpos($position, '#'));
		$stmt = $this->db->query("SELECT position FROM pages WHERE position > '{$position}' AND position LIKE '#{$parent_position}%' AND position NOT LIKE '{$position}%'");
		$pages = $stmt->fetchAll(PDO::FETCH_ASSOC);
		if (!$pages){
			return;
		}
		
		$path = explode('#', $position);
		$level = count($path) - 1;
		$stmt = $this->db->query("UPDATE pages SET position = :new_position WHERE position = :position");
		$this->db->beginTransaction();
		foreach ($pages as $page){
			$positions = explode('#', $page['position']);
			$positions[$level] = $positions[$level] + $delta;
			$page['new_position'] = implode('#', $positions);
			$stmt->execute($page);
		}
		$this->db->commit();
	}

	public function updatePositions($old, $new){
		$stmt = $this->db->query("SELECT position FROM pages WHERE position LIKE '{$old}%'");
		$pages = $stmt->fetchAll(PDO::FETCH_ASSOC);
		if (!$pages){
			return;
		}

		$stmt = $this->db->query("UPDATE pages SET position = :new WHERE position = :old");
		$this->db->beginTransaction();
		foreach ($pages as $page){
			$position = str_replace_once($old, $new, $page['position']);
			$stmt->execute(array('old' => $page['position'], 'new' => $position));
		}
		$this->db->commit();
	}

	public function getChildren($id){
		$stmt = $this->db->query("SELECT id, type FROM pages WHERE parent_id = {$id} OR position LIKE (SELECT position FROM pages WHERE id = {$id}) || '#%'");
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}

	public function find($term){
		mb_regex_encoding('UTF-8');
		mb_internal_encoding('UTF-8');

		$sql = "SELECT pages.path, pages.id, pc.title FROM pages, pages_content pc WHERE pages.id = pc.page_id AND pc.locale = '{$this->locale}' AND tolower(pc.title) LIKE :term AND status > 0 AND type != 'trash' ORDER BY pc.title LIMIT 20";
		$this->db->sqliteCreateFunction('tolower', 'lower', 1);
		$stmt = $this->db->prepare($sql);
		$stmt->execute(array('term' => '%' . lower($term) . '%'));
		$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

		foreach ($result as $item){
			$out[] = array(
				'label' => $item['title'],
				'value' => $item['title'],
				'url' => ($item['path'] == '/') ? $this->in_url : ($this->in_url ? $this->in_url . $item['path'] : substr($item['path'], 1)),
				'id' => $item['id'],
			);
		}
		return $out;
	}

	/* FRONT USEFUL */
	public function getList($filter, $aOrder = 'id DESC', $aLimit = false, $skip = 0){
		$limit = $aLimit ? " LIMIT {$skip}, {$aLimit}" : '';
		$order = $aOrder ? " ORDER BY {$aOrder}" : '';
		$where = implode(' AND ', (array)$filter);

		$stmt = $this->db->query("SELECT pages.*, pc.title, pc.content
			FROM pages LEFT JOIN pages_content pc ON pc.page_id = pages.id AND pc.locale = '{$this->locale}' AND pc.title != ''
			WHERE {$where}{$order}{$limit}");
		$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

		if (!$data){
			return false;
		}

		$pages = array();
		foreach ($data as $d){
			if (!$d['title']){
				continue;
			}
			$d['fullpath'] = $d['path'] == '/' ? $this->in_url : ($this->in_url ? $this->in_url . $d['path'] : substr($d['path'], 1));

			$content = explode('<hr id="cut" />', htmlspecialchars_decode($d['content']), 2);
			if (count($content) == 2){
				$d['preview'] = $content[0];
				$d['content'] = $content[1];
			} else {
				$d['preview'] = '';
				$d['content'] = $content[0];
			}
			$pages[$d['id']] = $d;
		}
		return $pages;
	}

	public function getListWithMeta($filter, $aOrder = 'id DESC', $aLimit = false, $skip = 0){
		$pages = $this->getList($filter, $aOrder, $aLimit, $skip);
		if (!$pages){
			return false;
		}
		$ids = implode(',', array_keys($pages));
		$stmt = $this->db->query("SELECT page_id, key, value FROM pages_meta WHERE page_id IN({$ids})");
		$meta = $stmt->fetchAll(PDO::FETCH_ASSOC);

		if ($meta){
			foreach ($meta as $m){
				if (isset($m['key'][2]) && $m['key'][2] == '_' && substr($m['key'], 0, 2) == $this->locale){
					$pages[$m['page_id']][ substr($m['key'], 3)] = $m['value'];
				} else {
					$pages[$m['page_id']][$m['key']] = $m['value'];
				}
			}
		}
		return $pages;
	}

	public function getCount($filter){
		$where = implode(' AND ', (array)$filter);
		$stmt =  $this->db->query("SELECT count(*) FROM pages, pages_content pc
		WHERE {$where} AND pages.id = pc.page_id AND pc.locale = '{$this->locale}'");
		return $stmt->fetchColumn();
	}
}
