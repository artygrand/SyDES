<?php
/**
 * @package SyDES
 *
 * @copyright 2011-2015, ArtyGrand <artygrand.ru>
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

final class App extends HasRegistry{
	protected $events = array();
	public static $STATUS_CODE = array(
		100 => 'Continue',
		101 => 'Switching Protocols',
		200 => 'OK',
		201 => 'Created',
		202 => 'Accepted',
		203 => 'Non-Authoritative Information',
		204 => 'No Content',
		205 => 'Reset Content',
		206 => 'Partial Content',
		300 => 'Multiple Choices',
		301 => 'Moved Permanently',
		302 => 'Found',
		303 => 'See Other',
		304 => 'Not Modified',
		305 => 'Use Proxy',
		307 => 'Temporary Redirect',
		400 => 'Bad Request',
		401 => 'Unauthorized',
		402 => 'Payment Required',
		403 => 'Forbidden',
		404 => 'Not Found',
		405 => 'Method Not Allowed',
		406 => 'Not Acceptable',
		407 => 'Proxy Authentication Required',
		408 => 'Request Timeout',
		409 => 'Conflict',
		410 => 'Gone',
		411 => 'Length Required',
		412 => 'Precondition Failed',
		413 => 'Request Entity Too Large',
		414 => 'Request-URI Too Long',
		415 => 'Unsupported Media Type',
		416 => 'Request Range Not Satisfiable',
		417 => 'Expectation Failed',
		500 => 'Internal Server Error',
		501 => 'Not Implemented',
		502 => 'Bad Gateway',
		503 => 'Service Unavailable',
		504 => 'Gateway Timeout',
		505 => 'HTTP Version Not Supported'
	);
	public static $MIME_TYPE = array(
		'css'   => 'text/css',
		'csv'   => 'application/vnd.ms-excel',
		'doc'   => 'application/msword',
		'html'  => 'text/html',
		'json'  => 'application/json',
		'js'    => 'application/x-javascript',
		'txt'   => 'text/plain',
		'rss'   => 'application/rss+xml',
		'atom'  => 'application/atom+xml',
		'zip'   => 'application/zip',
		'pdf'   => 'application/pdf',
		'xls'   => 'application/vnd.ms-excel',
		'gtar'  => 'application/x-gtar',
		'gzip'  => 'application/x-gzip',
		'tar'   => 'application/x-tar',
		'xhtml' => 'application/xhtml+xml',
		'rtf'   => 'text/rtf',
		'xsl'   => 'text/xml',
		'xml'   => 'text/xml'
	);

	public function getLanguage(){
		$this->language = isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) ? substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2) : 'en';
		$upd = true;
		if (!empty($_GET['language'])){
			$this->language = $_GET['language'];
		} elseif (!empty($_COOKIE['language'])){
			$this->language = $_COOKIE['language'];
			$upd = false;
		}
		$langs = str_replace(DIR_LANGUAGE, '', glob(DIR_LANGUAGE . '*'));
		if (empty($langs)){
			die('Missing default language package');
		}
		if (!in_array($this->language, $langs)){
			$this->language = $langs[0];
			$upd = true;
		}
		if ($upd){
			setcookie('language', $this->language, time()+604800, '/');
		}
	}

	public function parseUri(){
		$domains = $this->cache->get('domains');
		if (!$domains){
			foreach (glob(DIR_SITE . 's*', GLOB_ONLYDIR) as $sitepath){
				$site_domains = include $sitepath . '/domains.php';
				$site = str_replace(DIR_SITE, '', $sitepath);
				foreach ($site_domains as $domain){
					$domains[$domain] = $site;
				}
			}
			$this->cache->set('domains', $domains, 31536000);
		}

		if (!isset($domains[$_SERVER['HTTP_HOST']])){
			header("HTTP/1.1 404 Not Found");
			die(t('error_domain_not_associated'));
		}

		$this->site = $domains[$_SERVER['HTTP_HOST']];
		if (PRESERVE_BASE){
			$domains = include DIR_SITE . $this->site . '/domains.php';
			$this->base = $domains[0];
		} else {
			$this->base = $_SERVER['HTTP_HOST'];
		}
		$part = explode('?', $_SERVER['REQUEST_URI'], 2);
		$this->uri = empty($part[0]) ? '/' : $part[0];
	}

	public function parseRequest(){
		$path = explode('/', substr($this->uri, 1));
		
		// try to find locale
		if (count($this->config_site['locales']) > 1){
			if (in_array($path[0], $this->config_site['locales'])){
				$this->locale = $path[0];
				if (!isset($path[1])){
					$this->uri = '/';
				} else {
					$this->uri = substr($this->uri, 3);
				}
			} else {
				if ($this->uri == '/'){
					header('HTTP/1.1 301 Moved Permanently');
					header('Location: http://' . $this->base . '/' . $this->config_site['locales'][0]);
					die;
				} else {
					$this->locale = $this->config_site['locales'][0];
				}
			}
		} else {
			$this->locale = $this->config_site['locales'][0];
		}

		// try to find page number (no idea how to use)
		$last = array_pop($path);
		$page = explode('-', $last);

		if (count($page) == 2 && $page[0] == 'page' && ctype_digit($page[1])){
			$this->page_number = $page[1];
			$this->uri = str_replace('/' . $last, '', $this->uri);
		} else {
			$this->page_number = 1;
		}
	}

	public function getRoute(){
		if (!empty($this->request->get['route'])){
			$route = $this->request->get['route'];
		} else {
			// try to find predefined url
			$stmt = $this->db->prepare('SELECT * FROM routes WHERE alias = :alias');
			$stmt->execute(array('alias' => $_SERVER['REQUEST_URI']));
			$data = $stmt->fetch(PDO::FETCH_ASSOC);
			if ($data){
				$this->route = $data['route'];
				parse_str($data['params'], $get);
				$this->request->get = array_merge($this->request->get, $get);
				return;
			}

			// try to find page
			$stmt = $this->db->prepare("SELECT id FROM pages WHERE path = :path");
			$stmt->execute(array('path' => $this->uri));
			$id = $stmt->fetchColumn();
			if ($id){
				$this->route = 'pages/view/' . $id;
				return;
			}

			$route = substr($this->uri, 1);
		}
		$pos = strpos($route, '.');
		if ($pos !== false){
			$this->route = 'common/error/e404';
		} else {
			$this->route = $route;
		}
	}

	public function connect2db(){
		if (!$this->site) return;
		$this->db = new PDO('sqlite:' . DIR_SITE . $this->site . '/database.db');
		$this->db->exec('SET NAMES "utf8"');
		$this->db->exec('SET time_zone = "'. date_default_timezone_get() .'"');
	}

	public function action($route){
		$parts = explode('/', $route);
		if (!$parts[0] || !is_dir(DIR_MODULE . $parts[0])){
			throw new BaseException(t('error_module_not_found'));
		} else {
			$path = DIR_MODULE . $parts[0] . '/';
			$file = $path . 'index.php';
			$class = ucfirst($parts[0]) . 'Controller';
			$method = 'index';
			$name = $parts[0];
		}
		if (isset($parts[1])){
			if (file_exists($path . $parts[1] . '.php')){
				$file = $path . $parts[1] . '.php';
				$class = ucfirst($parts[1]) . 'Controller';
				$name = $parts[1];
			} else {
				$method = $parts[1];
			}
		}
		if (isset($parts[2])){
			if ($method == 'index'){
				$method = $parts[2];
			} else {
				$this->value = $parts[2];
			}
		}
		if (isset($parts[3])){
			$this->value = $parts[3];
		}

		include_once $file;

		if (substr($method, 0, 2) == '__' || $this->section == 'front' && !in_array($method, $class::$front)){
			throw new BaseException(t('error_method_forbidden'));
		}

		$controller = new $class();
		$this->load->language('module_' . $name);

		if (is_callable(array($controller, $method))){
			return call_user_func(array($controller, $method));
		} elseif (is_callable(array($controller, 'view'))){
			$this->value = $method;
			return call_user_func(array($controller, 'view'));
		} else {
			throw new BaseException(sprintf(t('error_method_not_found'), $method, $class));
		}
	}

	public function run($section){
		$this->trigger('before.module');

		try {
			$this->action($this->route);
		} catch (BaseException $e){
			$this->route = $this->section == 'admin' ? 'common/error/common' : 'common/error/e404';
			$this->action($this->route);
		}

		$this->trigger('after.module');

		if ($this->response->body === NULL && $this->response->data && !IS_AJAX){
			$this->trigger('before.render');
			$section->render();
			$this->trigger('after.render');
		}
		$this->response->flush();
	}

	public function on($event, $routes, $callback, $priority = 0){
		if (!isset($this->events[$event])) $this->events[$event] = array();
		$this->events[$event][] = array('routes' => $routes, 'fn' => $callback, 'prio' => $priority);
	}

	public function off($event){
		if (!isset($this->events[$event])) return;
		$this->events[$event] = array();
	}

	public function trigger($event, $params = array()){
		if (!isset($this->events[$event]) || !count($this->events[$event])){
			return;
		}

		$queue = new SplPriorityQueue();
		foreach ($this->events[$event] as $index => $action){
			$queue->insert($index, $action['prio']);
		}

		$queue->top();
		while ($queue->valid()){
			$index = $queue->current();
			if (isset($this->route)){
				$routes = explode(',', $this->events[$event][$index]['routes']);
				$current_route = false;
				foreach ($routes as $route){
					if (fnmatch(trim($route), $this->route)){
						$current_route = true;
						break;
					}
				}
			} else {
				$current_route = true;
			}
			if ($current_route && is_callable($this->events[$event][$index]['fn'])){
				if (call_user_func_array($this->events[$event][$index]['fn'], $params) === false) {
					break;
				}
			}
			$queue->next();
		}
	}

	public function checkUpdate(){
		if (!$this->config_site['check_updates']){
			return;
		}
		$update_checked = $this->cache->get('update');
		if (!$update_checked){
			$need = file_get_contents('http://sydes.ru/update/?version=' . VERSION . '&site=' . md5($_SERVER['HTTP_HOST']));
			$update_text = 0;
			if ($need == 1){
				$update_text = t('common_update_cms');
			} elseif ($need == 2){
				$update_text = t('security_update_cms');
			}
			$this->cache->set('update_text', $update_text, 600);
			$this->cache->set('update', '1', 86400);
		}
		$update_text = $this->cache->get('update_text');
		if ($update_text){
			$this->response->alert($update_text);
		}
	}
}