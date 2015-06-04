<?php
/**
 * @package SyDES
 * @subpackage Base module
 * @copyright 2011-2015, ArtyGrand <artygrand.ru>
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

class DataTable extends Controller{
	public $name;
	public $structure; // child module's table structure

	public function install(){
		$this->createTableByArray($this->name, $this->structure);
		$this->registerModule(true, true);
		$this->response->notify(t('installed'));
		$this->response->redirect('?route=config/modules');
	}

	public function uninstall(){
		$this->db->exec("DROP TABLE IF EXISTS {$this->name}");
		$this->unregisterModule();
		$this->response->notify(t('uninstalled'));
		$this->response->redirect('?route=config/modules');
	}

	public function config(){
		$this->response->redirect('?route=' . $this->name);
	}

	public function index(){
		$cols = 0;
		$data = array();
		$data['content'] = '<table class="table table-condensed table-striped table-hover"><thead><tr><th style="width:30px;">ID</th>';
		foreach ($this->structure as $h){
			if ($h['visible'] == false) continue;
			$data['content'] .= '<th>' . t($h['label']) . '</th>';
			$cols++;
		}
		$data['content'] .= '<th style="width:30px;">' . t('status') . '</th><th style="width:150px;">' . t('actions') . '</th></tr></thead>';

		$stmt = $this->db->query("SELECT * FROM {$this->name} ORDER BY id DESC");
		$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
		if ($rows){
			foreach ($rows as $row){
				$data['content'] .= '<tr><td>' . $row['id'] . '</td>';
					foreach ($this->structure as $name => $col){
						if ($col['visible'] == false) continue;
						if ($col['type'] == 'checkbox'){
							$data['content'] .= '<td>' . implode(', ', json_decode($row[$name]), true) . '</td>';
						} elseif ($col['type'] == 'textarea'){
							$data['content'] .= '<td>' . mb_substr(strip_tags(htmlspecialchars_decode($row[$name])), 0, 50, 'utf-8') . '</td>';
						} else {
							$data['content'] .= '<td>' . $row[$name] . '</td>';
						}
					}
				$data['content'] .= '<td>' . $row['status'] . '</td>';
				$data['content'] .= '<td><div class="btn-group pull-right btn-group-sm">
	<a class="btn btn-default" href="?route=' . $this->name . '/edit&id=' . $row['id'] . '">' . t('edit') . '</a>
	<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown"><span class="caret"></span></button>
	<ul class="dropdown-menu dropdown-menu-right">
		<li><a class="danger" href="?route=' . $this->name . '/delete&id=' . $row['id'] . '">' . t('delete') . '</a></li>
	</ul>
</div></td></tr>';
			}
		} else {
			$data['content'] .= '<tr><td colspan="' . ($cols+2) . '">' . t('empty') . '</td></tr>';
		}
		$data['content'] .= '</table>';

		$data['meta_title'] = t('module_' . $this->name);
		$data['breadcrumbs'] = H::breadcrumb(array(
			array('title' => t('module_' . $this->name))
		));

		$this->response->data = $data;
	}

	public function edit(){
		if (!isset($this->request->get['id'])){
			foreach ($this->structure as $name => &$col){
				$col['value'] = '';
				$col['label'] = t($col['label']);
			}
			$stmt = $this->db->query("SELECT MAX(id) FROM {$this->name}");
			$id = $stmt->fetchColumn() + 1;
		} else {
			$id = (int)$this->request->get['id'];
			$stmt = $this->db->query("SELECT * FROM {$this->name} WHERE id = " . $id);
			$rows = $stmt->fetch(PDO::FETCH_ASSOC);
			if (!$rows) throw new BaseException(t('error_empty_values_passed'));
			foreach ($this->structure as $name => &$col){
				if ($this->structure[$name]['type'] == 'checkbox'){
					$col['value'] = json_decode($rows[$name]);
				} else {
					$col['value'] = $rows[$name];
				}
				$col['label'] = t($col['label']);
			}
		}
		$this->structure['status'] = array(
			'label' => t('active'),
			'type' => 'yesNo',
			'value' => isset($rows) ? $rows['status'] : 1,
		);
		$data['content'] = H::form($this->structure);
		$data['sidebar_right'] = H::saveButton(DIR_SITE . $this->site . '/database.db');
		$data['form_url'] = "?route={$this->name}/save&id=" . $id;
		$data['meta_title'] = t('editing') . ' ' . t('module_' . $this->name);
		$data['breadcrumbs'] = H::breadcrumb(array(
			array('url' => "?route={$this->name}", 'title' => t('module_' . $this->name)),
			array('title' => t('editing'))
		));
		$this->response->data = $data;
		$this->response->script[] = '/vendor/ckeditor/ckeditor.js';
	}

	public function save(){
		if (!isset($this->request->get['id'])) throw new BaseException(t('error_empty_values_passed'));
		$id = (int)$this->request->get['id'];
		$table_keys = array_keys($this->structure);
		$data = array($id);
		foreach ($this->request->post as $key => $value){
			if (in_array($key, $table_keys)){
				if ($this->structure[$key]['type'] == 'checkbox'){
					$data[] = json_decode($value);
				} else {
					$data[] = $value;
				}
			}
			if ($key == 'status'){
				$data[] = $value;
			}
		}
		$count = count($this->structure) + 2;
		$count = str_pad('?', ($count*2)-1, ',?');
		$stmt = $this->db->prepare("INSERT OR REPLACE INTO {$this->name} VALUES ({$count})");
		if (!$stmt->execute($data)) throw new BaseException(t('error_not_saved'));
		$this->response->notify(t('saved'));
		$this->response->redirect('?route=' . $this->name);
	}

	public function delete(){
		$this->db->exec("DELETE FROM {$this->name} WHERE id = " . (int)$this->request->get['id']);
		$this->response->notify(t('deleted'));
		$this->response->redirect('?route=' . $this->name);
	}
}