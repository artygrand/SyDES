<?php
/**
 * @package SyDES
 *
 * @copyright 2011-2015, ArtyGrand <artygrand.ru>
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

class DashboardController extends Controller{
	public $name = 'dashboard';
	public $config;

	public function __construct(){
		parent::__construct();
		$this->config = new Config('dashboard');
	}

	public function install(){
		$this->config->set('widgets', array(array('notes'), array('lastpages')))->save();
		$this->registerModule(true);
		$this->response->notify(t('installed'));
		$this->response->redirect('?route=config/modules');
	}

	public function uninstall(){
		$this->config->delete()->save();
		$this->unregisterModule();
		$this->response->notify(t('uninstalled'));
		$this->response->redirect('?route=config/modules');
	}

	public function config(){
		$this->response->redirect('?route=dashboard');
	}

	public function index(){
		$this->addContextMenu('columns', t('count_of_columns'));
		$this->addToContextMenu('columns', array('title' => '1', 'link' => '?route=dashboard/columns&q=1'));
		$this->addToContextMenu('columns', array('title' => '2', 'link' => '?route=dashboard/columns&q=2'));
		$this->addToContextMenu('columns', array('title' => '3', 'link' => '?route=dashboard/columns&q=3'));
		$this->addToContextMenu('columns', array('title' => '4', 'link' => '?route=dashboard/columns&q=4'));

		$widgets = $this->config->get('widgets');
		$exists = str_replace(array(DIR_WIDGET, '.php'), '', glob(DIR_WIDGET . '*.php'));

		$used = array();
		foreach ($widgets as $i => $column){
			foreach ($column as $j => $widget){
				if (in_array($widget, $exists)){
					$widgets[$i][$j] = $this->loadWidget($widget);
					$used[] = $widget;
				} else {
					unset($widgets[$i][$j]);
				}				
			}
		}

		if (isset($used)){
			$unused = array_diff($exists, $used);
			if (!empty($unused)){
				$unused = $this->getWidgetNames($unused);
			}
		} else {
			$unused = $this->getWidgetNames($exists);
		}
		if (!empty($unused)){
			$this->addContextMenu('widgets', t('add_widget'));
			foreach ($unused as $widget => $name){
				$this->addToContextMenu('widgets', array('title' => $name, 'link' => '?route=dashboard/add&widget=' . $widget));
			}
		}

		$data = array();
		$data['content'] = $this->load->view('dashboard/dashboard', array(
			'col_sm' => 12 / count($widgets),
			'columns' => $widgets,
		));
		if (count($widgets) < 3){
			$data['sidebar_left'] = $data['sidebar_right'] = ' ';
		}
		$data['meta_title'] = t('module_dashboard');

		$crumbs = array(
			array('title' => t('module_dashboard'))
		);
		$data['breadcrumbs'] = H::breadcrumb($crumbs);

		$this->response->data = $data;
	}

	public function columns(){
		if (in_array($this->request->get['q'], array(1,2,3,4))){
			$columns = $this->config->get('widgets');
			$count = count($columns);

			if ($count > $this->request->get['q']){
				for($i = $this->request->get['q']; $i < $count; $i++){
					foreach ($columns[$i] as $widget){
						$columns[0][] = $widget;
					}
					unset($columns[$i]);
				}
			} elseif ($count < $this->request->get['q']){
				for($i = $count; $i < $this->request->get['q']; $i++){
					$columns[$i] = array();
				}
			}
			$this->config->set('widgets', $columns)->save();
		}
		$this->response->redirect('?route=dashboard');
	}

	public function sort(){
		$this->config->set('widgets', $this->request->post['sort'])->save();
	}

	public function add(){
		if (isset($this->request->get['widget']) && !preg_match("/[^a-z]+/", $this->request->get['widget']) && is_file(DIR_WIDGET . $this->request->get['widget'] . '.php')){
			$widgets = $this->config->get('widgets');
			$widgets[0][] = $this->request->get['widget'];
			$this->config->set('widgets', $widgets)->save();
		}
		$this->response->redirect('?route=dashboard');
	}

	public function remove(){
		if (isset($this->request->get['widget']) && !preg_match("/[^a-z]+/", $this->request->get['widget']) && is_file(DIR_WIDGET . $this->request->get['widget'] . '.php')){
			$stmt = $this->db->query("SELECT value FROM config WHERE module = 'dashboard' AND key = 'widgets'");
			$widgets = str_replace(array('"' . $this->request->get['widget'] . '"', ',]', '[,'), array('', ']', '['), $stmt->fetchColumn());
			$stmt = $this->db->prepare("UPDATE config SET value = :value WHERE module = 'dashboard' AND key = 'widgets'");
			$stmt->execute(array('value' => $widgets));
		}
		$this->response->redirect('?route=dashboard');
	}

	public function save(){
		if (isset($this->request->post['widget']) && !preg_match("/[^a-z]+/", $this->request->post['widget'])){
			$stmt = $this->db->prepare("INSERT OR REPLACE INTO config (module, key, value) VALUES ('widget', :key, :value)");
			$stmt->execute(array('key' => $this->request->post['widget'], 'value' => json_encode($this->request->post['data'])));
			$this->response->notify(t('saved'));
		}
	}

	public function setup(){
		$body = 'TODO settings';
		$footer = H::button(t('save'), 'submit', 'class="btn btn-primary"');
		$this->response->body = H::modal(t('settings'), $body, $footer, '?route=dashboard/savesettings&widget=' . $this->request->get['widget']);
	}

	public function savesettings(){
		$this->response->redirect('?route=dashboard');
	}

	private function loadWidget($widget){
		$file = DIR_WIDGET . $widget . '.php';
		$class = ucfirst($widget) . 'Widget';
		if (is_file($file)){
			$this->load->language('widget_' . $widget);
			include_once $file;
			$widget = new $class($this->registry);
			return $widget->index();
		}
	}
	
	private function getWidgetNames($widgets){
		$arr = array();
		foreach ($widgets as $widget){
			$translate = $this->load->language('widget_' . $widget, false);
			$arr[$widget] = $translate['widget_' . $widget];
		}
		return $arr;
	}
}