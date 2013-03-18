<?php
/**
* Box module for working with configuration.
* 
* @version 1.4 (for sydes 1.7)
* @author ArtyGrand
*/

class Config extends Module{
	/**
	* Sets the name of default page template
	* @var string
	*/
	public $template = 'index';

	/**
	* Sets the native module name for menu
	* @var string
	*/
	public static $nativeName = 'Страницы';

	/**
	* Sets the allowed actions for user
	* @var array
	*/
	public static $allowedActions = array('view', 'save');

	/**
	* Sets the allowed actions for user over AJAX
	* @var array
	*/
	public static $allowedAjaxActions = array('metaadd', 'metaupdate', 'metadelete');

	function __construct(){
		$this -> setModuleName();
		include CLASS_DIR . 'meta.php';
		$this -> meta = new Meta($this -> name);
		parent::__construct();
	}

	/**
	* View all config data
	*/
	public function view(){
		$template = getSelect(globRecursive("../templates", array("/")), 'title', Core::$config['template'], 'name="template" class="full"');
		$admin_ip = implode(' ', Core::$config['admin_ip']);
		$final_template = implode(' ', Core::$config['final_template']);
		$locale = implode(' ', Core::$config['locale']);
		if (!$admin_ip) $admin_ip = getip();
		if (!Core::$config['base']) Core::$config['base'] = getbase(dirname($_SERVER['PHP_SELF']));
		Core::$config['work'] = Core::$config['work'] == 1 ? ' checked' : '';
		Core::$config['cookie'] = Core::$config['cookie'] == 1 ? ' checked' : '';
		Core::$config['need_cache'] = Core::$config['need_cache'] == 1 ? ' checked' : '';
		$meta_plugin = $this -> meta -> getPlugin(1);
		$pages = $this -> getPagesConf();
		ob_start();
			require_once TPL_DIR . 'form_config.html';
			$p['content'] = ob_get_contents();
		ob_end_clean();
		$p['breadcrumbs'] = lang('settings') . ' &gt; <span>' . lang('editor') . '</span>';
		$p['jquery'] = "$('.nav-tabs li').click(function(){ $('#tabs>div').hide();$('#' + $(this).data('target')).show();$('.nav-tabs li').removeClass('active');$(this).addClass('active')})";
		return $p;
	}
	
	/**
	* Save something
	*/
	public function save(){
		if (isset($_POST['locale']) and isset($_POST['template'])){
			if (!canEdit()){
				throw new Exception(lang('unauthorized_request'));
			}

			if ($_POST['pass']) $_POST['pass'] = md5($_POST['pass']);
			if ($_POST['new_code']) $_POST['master_code'] = md5($_POST['new_code']);
			$_POST['work'] = isset($_POST['work']) ? 1 : 2;
			$_POST['cookie'] = isset($_POST['cookie']) ? 1 : 2;
			$_POST['need_cache'] = isset($_POST['need_cache']) ? 1 : 2;
			$_POST['admin_ip'] = explode(' ', $_POST['admin_ip']);
			$_POST['final_template'] = explode(' ', $_POST['final_template']);
			$_POST['locale'] = explode(' ', $_POST['locale']);
			foreach(Core::$config as $k => &$post){
				if (!$_POST[$k]) continue;
				$post = $_POST[$k];
			}
			$string_to_put = serialize(Core::$config);
			$file = '../system/config.db';

			if(!file_put_contents($file, $string_to_put)){
				throw new Exception(lang('error_not_saved'));
			}
			// add page type
			if($_POST['pages']['neu']['type'] and $_POST['pages']['neu']['name']){
				$stmt = Core::$db -> prepare("INSERT INTO page_types (type, name, dflt_tpl) VALUES (:type, :name, :dflt_tpl)");
				$_POST['pages']['neu']['type'] = properUri(trim($_POST['pages']['neu']['type']));
				$_POST['pages']['neu']['dflt_tpl'] = properUri(trim($_POST['pages']['neu']['dflt_tpl']));
				$_POST['pages']['neu']['name'] = htmlspecialchars(trim($_POST['pages']['neu']['name']));
				if (!$stmt->execute($_POST['pages']['neu'])) throw new Exception(lang('error_not_saved'));
			}
			array_shift($_POST['pages']);
			// update page types
			$stmt = Core::$db -> prepare("UPDATE page_types SET name = :name, dflt_tpl = :dflt_tpl WHERE type = :type");
			foreach($_POST['pages'] as $type => $val){
				$val['type'] = $type;
				$stmt -> execute($val);
			}
			Core::clearMenuCache();
			clearAllCache();
			$p['redirect'] = 1;
			return $p;
		}
		throw new Exception(lang('no_value'));
	}
	
	/**
	* Config for pages module
	* Allows CRUD types
	*/
	private function getPagesConf(){
		$stmt = Core::$db -> query("SELECT * FROM page_types");
		$data = $stmt -> fetchAll(PDO::FETCH_ASSOC);
		$tpls = globRecursive('../templates/'.Core::$config['template'], array('html'));
		$p['type'] = '';
		$p['name'] = '';
		$p['dflt_tpl'] = '';
		foreach($data as $d){
			$p['type'] .= '<div class="list centered">' . $d['type'] . '</div>';
			$p['name'] .= '<div class="list"><input type="text" value="' . $d['name'] . '" name="pages[' . $d['type'] . '][name]" class="full middle"></div>';
			$p['dflt_tpl'] .= '<div class="list">' . getSelect($tpls, 'title', $d['dflt_tpl'], 'class="full" name="pages[' . $d['type'] . '][dflt_tpl]"') . '</div>';
		}
		$p['dflt_tpl'] .= '<div class="list">' . getSelect($tpls, 'title', 'index', 'class="full" name="pages[neu][dflt_tpl]"') . '</div>';
		return $p;
	}
	
	public function metaadd(){
		$json['content'] = $this -> meta -> add((int)$_POST['page_id'], $_POST['key'], $_POST['value']);
		if ($json['content']){
			$json['success'] = lang('saved');
		} else {
			$json['error'] = lang('no_value');
		}
		clearAllCache();
		return $json;
	}
	
	public function metaupdate(){	
		$this -> meta -> update((int)$_POST['id'], $_POST['value']);
		$json['success'] = lang('saved');
		clearAllCache();
		return $json;
	}
	
	public function metadelete(){
		$this -> meta -> delete((int)$_POST['id']);
		$json['success'] = lang('deleted');
		clearAllCache();
		return $json;
	}
}
?>