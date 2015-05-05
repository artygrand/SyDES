<?php
/**
 * @package SyDES
 *
 * @copyright 2011-2015, ArtyGrand <artygrand.ru>
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

class MetaController extends Controller{
	public $name = 'meta';
	private $meta;
	private $table;

	function __construct(){
		parent::__construct();
		$this->table = $this->request->get['module'] . '_meta';
		$this->meta = new Meta($this->request->get['module']);
	}

	public function index(){
		if (isset($this->config_admin['meta'])){
			$meta = $this->config_admin['meta'];
		} else {
			$stmt = $this->db->query("SELECT key FROM {$this->table} GROUP BY key ORDER BY key");
			$keys = $stmt->fetchAll(PDO::FETCH_COLUMN);
			foreach ($keys as $key){
				$meta[$key] = array(
					'title' => '',
					'type' => 'string',
					'config' => '',
				);
			}
		}

		$types = array(
			'string' => t('input_text'),
			'textarea' => t('input_textarea'),
			'html' => t('input_html'),
			'image' => t('input_image'),
			'file' => t('input_file'),
			'folder' => t('input_folder'),
			'date' => t('input_date'),
			'yesNo' => t('input_yesNo'),
			'listing' => t('input_listing'),
			/*'reference' => t('input_reference'),
			'video' => t('input_video'),
			'map' => t('input_map'),
			TODO later
			*/
		);

		$data['content'] = $this->load->view('common/meta-settings', array(
			'meta' => $meta,
			'types' => $types,
		));
		$data['sidebar_right'] = H::saveButton(DIR_SITE . 'config.php') . $this->load->view('common/meta-global', array());
		$data['form_url'] = '?route=common/meta/save&module=' . $this->request->get['module'];
		$data['meta_title'] = t('meta_data');
		$data['breadcrumbs'] = H::breadcrumb(array(
			array('title' => t('meta_data'))
		));
		
		$this->response->data = $data;
		$this->response->script[] = '/system/module/common/assets/meta.js';
		$this->response->script[] = '/system/module/common/assets/meta-settings.js';
		$this->response->addJsL10n(array(
			'add' => t('add'),
			'remove' => t('remove'),
			'temporarily_stored' => t('temporarily_stored'),
		));
	}

	public function save(){
		$config = $this->config_admin;
		$types = $this->request->post['metatype'];
		if ($types['new_type']['key'][0] != ''){
			foreach ($types['new_type']['key'] as $i => $key){
				if ($key == '') continue;
				$types[$key] = array(
					'title' => $types['new_type']['title'][$i],
					'type' => $types['new_type']['type'][$i],
					'config' => $types['new_type']['config'][$i],
				);

			}
		}
		unset($types['new_type']);
		$config['meta'] = $types;
		arr2file($config, DIR_SITE . 'config.php');

		$this->response->notify(t('saved'));
		$this->response->redirect('?route=common/meta&module=' . $this->request->get['module']);
	}

	public function get(){
		$page_id = $this->request->post['page_id'];
		$permanent = $this->request->post['permanent'];
		$count = count($permanent);
		$where = '';
		if ($count){
			$where = 'WHERE key NOT IN(' . str_repeat('?,', $count - 1) . '?)';
		}

		$stmt = $this->db->prepare("SELECT key FROM {$this->table} {$where} GROUP BY key ORDER BY key");
		$stmt->execute($permanent);
		$all_keys = $stmt->fetchAll(PDO::FETCH_COLUMN);

		$this->response->body['base'] = $this->load->view('common/meta', array(
			'all_keys' => H::select('keys', false, $all_keys, 'class="form-control" title="' . t('key') . '" id="keys"'),
			'meta_data' => $page_id == 0 ? t('global_meta_data') : t('meta_data'),
		));

		$meta = array();
		foreach ($permanent as $key){
			$meta[] = array(
				'id' => 0,
				'key' => $key,
				'value' => '',
			);
		}
		if ($page_id > -1){
			$meta = array_merge($meta, $this->meta->get($page_id));
		}

		$this->response->body['meta'] = $this->getFields($meta);
	}

	public function add(){
		$key = $this->request->post['key'];
		if (in_array($key, array('id', 'parent_id', 'alias', 'path', 'fullpath', 'position', 'status', 'layout', 'type', 'cdate', 'title', 'content'))){
			$this->response->notify(t('forbidden_meta_key'), 'danger');
			return;
		}
		$id = (int)$this->request->post['page_id'];
		if ($id > -1){
			$new_id = $this->meta->add($id, $key, $this->request->post['value']);
			$this->response->body['meta'] = $this->getFields($this->meta->getById($new_id));
			$this->response->notify(t('saved'));
		} else {
			$this->response->body['meta'] = $this->getFields(array(array('id' => 0, 'key' => $key, 'value' => $this->request->post['value'])));
		}
	}

	public function update(){
		$this->meta->updateById((int)$this->request->post['id'], $this->request->post['value']);
		$this->response->notify(t('saved'));
	}

	public function delete(){
		$this->meta->deleteById((int)$this->request->post['id']);
		$this->response->notify(t('deleted'));
	}

	public function load(){
		$key = $this->request->post['key'];
		$meta = $this->getFields(array(array('id' => 0, 'key' => $key, 'value' => '')));
		preg_match('/<div class="input-group">(.*)<span class="input-group-btn">/s', $meta[$key], $matches);
		$meta = '<div class="input-group meta-value">
	' . $matches[1] . '
	<span class="input-group-btn">
		<button class="btn btn-primary btn-sm" type="button" onclick="meta.add()" data-toggle="tooltip" data-placement="left" title="' . t('add') . '"><span class="glyphicon glyphicon-arrow-down"></span></button>
	</span>
</div>';
		$this->response->body['meta'] = $meta;
	}

	private function getFields($meta){
		if (!$meta) return;
		$magic = array(	
			'img' => 'image',
			'image' => 'image',
			'photo' => 'image',
			'preview' => 'image',
			'pic' => 'image',
			'picture' => 'image',
			'date' => 'date',
			'date_start' => 'date',
			'date_end' => 'date',
			'pdf' => 'pdf',
			'file' => 'file',
			'folder' => 'folder',
			'flash' => 'flash',
			'swf' => 'flash',
		);

		foreach ($meta as $item){
			if (isset($this->config_admin['meta'][$item['key']])){
				$field = $this->config_admin['meta'][$item['key']];
			} else {
				$field = array (
					'title' => '',
					'type' => 'string',
					'config' => '',
				);
			}
			if ($field['title'] == ''){
				$field['title'] = $item['key'];
			}

			switch ($field['type']){
				case 'listing':
					$config = json_decode(htmlspecialchars_decode($field['config']), true);
					$class = $config['display'] == 'select' ? array('form-control','input-sm') : array();
					if ($config['display'] == 'checkbox'){
						$item['value'] = explode(',', $item['value']);
					}
					$source = explode("\n", $config['source']);
					if (strpos($source[0], '|') !== false){
						foreach ($source as $row){
							$row = explode('|', $row);
							$option[$row[0]] = $row[1];
						}
						$source = $option;
					}
					$input = H::$config['display'](
						'meta[' . $item['key'] . ']',
						$item['value'],
						$source,
						array(
							'class' => $class
						)
					);
					break;
				case 'string':
				case 'image':
				case 'file':
				case 'folder':
				case 'date':
				case 'reference':
				case 'video':
				case 'map':
					if ($field['type'] = 'string'){
						$magic_class = isset($magic[$item['key']]) ? ' field-' . $magic[$item['key']] : '';
					} else {
						$magic_class = ' field-' . $field['type'];
					}
					$input = H::string(
						'meta[' . $item['key'] . ']',
						$item['value'],
						array(
							'class' => 'form-control input-sm' . $magic_class,
						)
					);
					break;
				case 'textarea':
				case 'html':
					$attr = $field['type'] == 'html' ? 'class="form-control lazy ckeditor" rows="10"' : 'class="form-control" rows="3"';
					$input = H::textarea(
						'meta[' . $item['key'] . ']',
						$item['value'],
						$attr
					);
					break;
				case 'yesNo':
					$input = H::yesNo(
						'meta[' . $item['key'] . ']',
						$item['value']
					);
					break;
				default:
					$input = H::string(
						'meta[' . $item['key'] . ']',
						$item['value'],
						array(
							'class' => 'form-control input-sm',
						)
					);
			}

			if ($item['id'] == 0){
				$del = '<button class="btn btn-default btn-sm disabled" type="button">&nbsp;</button>';
			} else {
				$del = '<button class="btn btn-primary btn-sm" type="button" onclick="meta.delete(' . $item['id'] . ')" data-toggle="tooltip" data-placement="left" title="' . t('delete') . '"><span class="glyphicon glyphicon-remove"></span></button>';
			}
			$out[$item['key']] = '
<div class="form-group meta-field" data-id="' . $item['id'] . '">
	<label>' . $field['title'] . '</label>
	<div class="input-group">' . $input . '<span class="input-group-btn">' . $del . '</span></div>
</div>';
		}
		return $out;
	}
}