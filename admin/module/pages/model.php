<?php
class PagesModel{
	public function getPage($id){
		$stmt = Admin::$db->prepare("SELECT pages.*, pc.title, pc.content FROM pages LEFT JOIN pages_content pc ON pc.page_id = pages.id AND pc.locale = :locale WHERE pages.id = :id");
		$stmt->execute(array('locale' => Admin::$locale, 'id' => $id));
		return $stmt->fetch(PDO::FETCH_ASSOC);
	}

	public function getPages($type, $order, $filter = false){
		$pathPrefix = (count(Admin::$config['sites'][Admin::$site]['locales']) > 1) ? '/' . Admin::$locale : '';
		$where = isset($filter['page']) ? 'AND ' . implode(' AND ', (array)$filter['page']) : '';
		$stmt = Admin::$db->prepare("SELECT p1.id, pc1.title, '$pathPrefix' || p1.fullpath as fullpath, p1.parent_id, p1.status, p1.position, count(p1.id)-1 as haschilds, pc2.title as parent_title
			FROM pages p1, pages p2 
			LEFT JOIN pages_content pc1 ON pc1.page_id = p1.id AND pc1.locale = :locale
			LEFT JOIN pages_content pc2 ON pc2.page_id = p1.parent_id AND pc2.locale = :locale
			WHERE p1.type = :type AND p2.type = :type AND (p1.id = p2.parent_id OR p1.id = p2.id) {$where} GROUP BY p1.id ORDER BY {$order}");
		$stmt->execute(array('locale' => Admin::$locale, 'type' => $type));
		$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

		if (!$data){
			return false;
		}

		foreach($data as $d){
			$id[] = $d['id'];
			$pages[$d['id']] = $d;
		}
		$id = implode(',', $id);
		$stmt = Core::$db -> query("SELECT page_id, key, value FROM pages_meta WHERE page_id IN({$id})");
		$meta = $stmt -> fetchAll(PDO::FETCH_ASSOC);

		foreach($meta as $m){
			if (isset($m['key'][2]) and $m['key'][2] == '_' and substr($m['key'], 0, 2) == Admin::$locale){
				$pages[$m['page_id']]['meta_' . substr($m['key'], 3)] = $m['value'];
			} else {
				$pages[$m['page_id']]['meta_' . $m['key']] = $m['value'];
			}
		}
		// filter by meta
		if (isset($filter['meta'])){
			foreach($pages as $k => $v){
				foreach($filter['meta'] as $j => $m){
					if (!isset($v['meta_' . $j]) or $v['meta_' . $j] != $m){unset($pages[$k]);} //TODO add less then/more then comparison
				}
			}
		}
		return $pages;
	}
	
	public function getBranch($id){
		$stmt = Admin::$db->query("SELECT p1.id, pc.title, p1.fullpath FROM pages p1, pages p2 LEFT JOIN pages_content pc ON pc.page_id = p1.id AND pc.locale = '" . Admin::$locale . "' WHERE p2.id = $id and p1.fullpath LIKE p2.fullpath || '%' and p1.type = p2.type ORDER BY p1.position, p1.fullpath");
		$parents = $stmt->fetchAll(PDO::FETCH_ASSOC);
		
		foreach($parents as &$p){
			if ($p['id'] == 0){
				$p['title'] = lang('root');
				break;
			}
		}
		return $parents;
	}

	public function getChilds($ids){
		$pages = array();
		foreach($ids as $id){
			$stmt = Admin::$db->query("SELECT id FROM pages WHERE fullpath LIKE (SELECT fullpath FROM pages WHERE id = $id) || '%'");
			$pages = array_merge($pages, $stmt->fetchAll(PDO::FETCH_COLUMN));
		}
		return array_unique($pages);
	}
	
	public function setPositionAfter($id, $delta = 0, $including = false){
		$equal = $including ? '=' : '';
		$stmt = Admin::$db->query("SELECT id, position, fullpath FROM pages WHERE position >{$equal} (SELECT position FROM pages WHERE id = $id) AND parent_id = (SELECT parent_id FROM pages WHERE id = $id)");
		$pages = $stmt->fetchAll(PDO::FETCH_ASSOC);

		foreach($pages as $page){
			$newpos = explode('.', $page['position']);
			if(count($newpos) > 1){
				$end = array_pop($newpos);
				$newpos[] = $end + $delta;
				$newpos = implode('.', $newpos);
			} else {
				$newpos = '#' . (substr($newpos[0], 1) + $delta);
			}
			$stmt = Admin::$db->query("UPDATE pages SET position = replace(position, '{$page['position']}', '{$newpos}') WHERE fullpath LIKE '{$page['fullpath']}%'");
		}
	}

	public function delete($id){
		if (is_array($id)){
			$id = implode(',', $id);
		}
		Admin::$db->exec("DELETE FROM pages WHERE id IN ({$id})");
		Admin::$db->exec("DELETE FROM pages_content WHERE page_id IN ({$id})");
		Admin::$db->exec("DELETE FROM pages_meta WHERE page_id IN ({$id})");
	}

	public function getFilter($type){
		if (isset($_GET['filter'])){
			if ($_GET['filter'] == 'clear'){
				setcookie($type . 'filter', '', time()-10);
				unset($_GET['filter']);
			} else {
				setcookie($type . 'filter', serialize($_GET['filter']), time()+3600);
			}
		} elseif (isset($_COOKIE[$type . 'filter'])){
			$_GET['filter'] = unserialize($_COOKIE[$type . 'filter']);
		}
		if (!isset($_GET['filter'])){
			return false;
		}
		
		$filter = array();
		foreach($_GET['filter'] as $k => $v){
			if (is_array($v)){
				$filter['meta'] = $v;
			} else {
				if ($k == 'title'){
					$filter['page'][] = "pc1.title LIKE '%{$v}%'";
				} else {
					$filter['page'][] = "p1.$k = $v";
				}
			}
		}
		return $filter;
	}

	public function getLastChildPos($id){
		$stmt = Admin::$db->query("SELECT position FROM pages WHERE parent_id = {$id} ORDER BY position DESC LIMIT 1");
		$path = $stmt->fetchColumn();
		if ($id > 0){
			if($path){
				$pos = substr(strrchr($path, '.'), 1);
			} else {
				$pos = 999;
			}
		} else {
			$pos = substr($path, 1);
		}
		return $pos;
	}

	public function getPos($id){
		$stmt = Admin::$db->query("SELECT position FROM pages WHERE id = {$id}");
		$path = $stmt->fetchColumn();
		$pos = substr(strrchr($path, '.'), 1);
		return $pos;
	}

	public function getMaxId(){
		$stmt = Admin::$db->query("SELECT MAX(id)+1 FROM pages");
		return $stmt->fetchColumn();
	}

	public function getData($id, $what){
		$stmt = Admin::$db->query("SELECT $what FROM pages WHERE id = $id");
		return $stmt->fetch(PDO::FETCH_ASSOC);
	}

	public function setData($id, $what, $data){
		$data = (array)$data;
		foreach((array)$what as $i => $key){
			$set[] = $key . " = '" . $data[$i] . "'";
		}
		$set = implode(',', $set);
		
		$stmt = Admin::$db->exec("UPDATE pages SET $set WHERE id = $id");
	}

	public function setStatus($id, $val){
		Admin::$db->exec("UPDATE pages SET status = $val WHERE EXISTS(
			SELECT * FROM pages as t1
			WHERE t1.id IN ($id) AND (
				pages.fullpath = t1.fullpath
				OR pages.fullpath LIKE t1.fullpath || '/%' AND pages.status > $val 
				OR t1.fullpath LIKE pages.fullpath || '/%' AND pages.status < $val
			))"
		);
	}

	public function getParentSelect($pages, $parent, $current){
		$listing = '';
		$disableLevel = 1800;
		$format = '	<option value="%d" %s%s>%s %s</option>';
		foreach ($pages as $page){
			$thisSelect = $parent == $page['id'] ? 'selected' : '';
			$level = substr_count($page['fullpath'], '/') * 2;
			if ($current == $page['id']){
				$thisDisable = 'disabled';
				$disableLevel = $level;
			} elseif ($disableLevel < $level){
				$thisDisable = 'disabled';
			} elseif ($disableLevel >= $level){
				$thisDisable = '';
				$disableLevel = 1800;
			}
			$shift = str_pad('',$level - 2,'- ');
			if (empty($page['title'])) $page['title'] = lang('no_translation');
			$listing .= sprintf($format, $page['id'], $thisSelect, $thisDisable, $shift, $page['title']) . PHP_EOL;
		}
		return $listing;
	}

	public function getSiblingByPos($index, $id){
		$index = 1000 + $index;
		$stmt = Admin::$db->query("SELECT position, id FROM pages WHERE id = (SELECT parent_id FROM pages WHERE id = $id)");
		$data = $stmt->fetch(PDO::FETCH_ASSOC);
		$pos = $data['id'] == 0 ? '#' . $index : $data['position'] . '.' . $index;
		$stmt = Admin::$db->query("SELECT id FROM pages WHERE position = '{$pos}'");
		return array('id' => $stmt->fetchColumn(), 'position' => $pos);
	}

	public function updateChilds($id, $oldPath = false, $oldPos = false){
		$where =  $oldPath ? "fullpath LIKE '{$oldPath}/%'" : "position LIKE '{$oldPos}.%'";
		$stmt = Admin::$db->query("SELECT id, fullpath, position FROM pages WHERE {$where}");
		$childs = $stmt->fetchAll(PDO::FETCH_ASSOC);
		
		$stmt = Admin::$db->query("SELECT fullpath, position FROM pages WHERE id = $id");
		$new = $stmt->fetch(PDO::FETCH_ASSOC);

		$stmt = Admin::$db->prepare("UPDATE pages SET fullpath = :fullpath, position = :position WHERE id = :id");
		foreach($childs as $child){
			if ($oldPath){
				$child['fullpath'] = str_replace_once($oldPath, $new['fullpath'], $child['fullpath']);
			}
			if ($oldPos){
				$child['position'] = str_replace_once($oldPos, $new['position'], $child['position']);
			}
			$stmt -> execute($child);
		}
	}

	public function setParent($id, $parent_id, $parent_fullpath, $pos){
		Admin::$db->exec("UPDATE pages SET parent_id = $parent_id, position = '$pos', fullpath = '{$parent_fullpath}/' || alias WHERE id = $id");
	}
}
?>