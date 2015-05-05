<?php
/**
 * @package SyDES
 *
 * @copyright 2011-2015, ArtyGrand <artygrand.ru>
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

class Front extends HasRegistry{
	private $translate;
	private $config;
	public function render(){
		if (!isset($this->pages_model)){
			$this->load->model('pages');
		}
		$response = $this->response;
		$this->translate = $this->load->language('front', false, $this->locale);
		$this->config = new Config('front');

		$toolbar = '';
		if ($this->user->is_editor){
			$toolbar = $this->getToolbar();
		}

		$layout = include DIR_TEMPLATE . $this->config_site['template'] . '/layouts.php';
		$layout = $layout[$response->data['layout']];

		$layout_file = DIR_TEMPLATE . $this->config_site['template'] . '/' . $layout['file'];
		if (!is_file($layout_file)){
			die(sprintf(t('error_layout_file_not_found'), $layout_file));
		}

		$template = file_get_contents($layout_file);
		$template = str_replace('{layout}', htmlspecialchars_decode($layout['html']), $template);

		$response->script[] = '//ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js';
		$response->script[] = '//maxcdn.bootstrapcdn.com/bootstrap/3.3.4/js/bootstrap.min.js';
		$response->script[] = '/system/assets/js/sydes.js';
		$response->script[] = '/system/assets/js/front.js';
		$response->style[] = '//maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap.min.css';
		$response->style[] = '/system/assets/css/front.css';

		$this->compile($response->data['content']);
		$this->compile($template);

		$head[] = '<title>' . $response->data['meta_title'] . '</title>';
		$head[] = empty($response->data['meta_description']) ? '' : '<meta name="description" content="' . $response->data['meta_description'] . '">';
		$head[] = empty($response->data['meta_keywords']) ? '' : '<meta name="keywords" content="' . $response->data['meta_keywords'] . '">';
		$head[] = '<meta name="generator" content="SyDES">';
		$head[] = '<base href="http://' . $this->base . '/">';

		if (!empty($response->style)){
			foreach($response->style as $file){
				$head[] = '<link rel="stylesheet" href="' . $file . '" media="screen">';
			}
		}
		if (!empty($response->script)){
			foreach($response->script as $file){
				$head[] = '<script src="' . $file . '"></script>';
			}
		}
		if (!empty($response->translations)){
			$head[] = '<script>syd.translations = ' . json_encode($response->translations) . '</script>';
		}

		$common = array(
			'year'     => date('Y'),
			'template' => 'template/' . $this->config_site['template'] . '/',
			'language' => $this->locale,
			'head'     => implode("\n\t", $head),
			'toolbar'  => $toolbar,
		);

		$alerts = '<div id="alerts">';
		foreach($response->alerts as $a){
			$alerts .= '<div class="alert alert-' . $a['status'] . ' alert-dismissible"><button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>' . $a['message'] . '</div>';
		}
		$alerts .= '</div>';
		$response->data['alerts'] = $alerts;

		$replace_arr = array_merge($common, $response->data);

		foreach($replace_arr as $key => $val){
			$find[] = '{' . $key . '}';
			$replace[] = $val;
		}

		$template = str_replace($find, $replace, $template);
		$response->body = preg_replace('!{\w+}!', '', $template);
	}

	private function compile(&$html){
		if (preg_match_all('/{(iblock|t|config):([^\?]+?)(\?.+)?}/', $html, $matches)){
			for ($i = 0; $i <= $count = count($matches[2])-1; $i++){
				$method = $matches[1][$i];
				$arParams = array();
				if ($matches[3][$i]){
					$matches[3][$i] = str_replace(array('?', '&amp;', '&quot;', '#39;'), array('', '&', '"', "'"), $matches[3][$i]);
					parse_str($matches[3][$i], $arParams);
					$arParams = str_replace('"', '', $arParams);
				}
				$content = $this->$method($matches[2][$i], $arParams);
				if ($this->user->is_editor and in_array($method, array('iblock', 'config'))){
					if (!$content){
						$content = '&nbsp;';
					}
					$tools = '<span data-module="' . $method . '" data-item="' . $matches[2][$i] . '" class="block-edit"></span>';
					if (isset($arParams['template']) and file_exists(DIR_TEMPLATE . $this->config_site['template'] . '/iblock/' . $matches[2][$i] . '/' . $arParams['template'] . '.php')){
						$tools .= '<span data-item="' . $matches[2][$i] . '" data-template="' . $arParams['template'] . '" class="block-template"></span>';
					}
					$content = '<div class="block-wrapper"><div class="tools">' . $tools . '</div>' . $content . '</div>';
				}
				$html = str_replace($matches[0][$i], $content, $html);
			}
		}
	}

	public function iblock($iblock_name, $params = false){
		if (!is_file(DIR_IBLOCK . $iblock_name . '/iblock.php')){
			return sprintf(t('error_iblock_not_found'), $iblock_name);
		}
		$page = $this->response->data;
		
		$args['template'] = 'default';
		if ($params){
			$args = array_merge($args, $params);
		}
		$pos = strpos($iblock_name . $args['template'], '.');
		if ($pos !== false){
			return;
		}

		ob_start();
		$out = include DIR_IBLOCK . $iblock_name . '/iblock.php';
		if ($out !== NULL){
			$tpl_override = DIR_TEMPLATE . $this->config_site['template'] . '/iblock/' . $iblock_name . '/' . $args['template'] . '.php';
			$tpl_original = DIR_IBLOCK . $iblock_name . '/' . $args['template'] . '.php';

			if (is_file($tpl_override)){
				include $tpl_override;
			} elseif(is_file($tpl_original)){
				include $tpl_original;
			} elseif($args['template'] != 'default'){
				ob_end_clean();
				return sprintf(t('error_iblock_template_not_found'), $args['template'], $iblock_name);
			}
		}

		return ob_get_clean();
	}

	public function t($text){
		return isset($this->translate[$text]) ? $this->translate[$text] : $text;
	}

	public function config($key){
		return $this->config->get($key);
	}

	public function getToolbar(){
		$types = array();
		foreach($this->config_site['page_types'] as $type => $data){
			if (isset($data['hidden'])){
				continue;
			}
			$types[$type] = $data['title'];
		}

		$menu = array();
		foreach($this->response->context as $key => $data){
			$menu[$key]['title'] = $data['title'];
			$menu[$key]['link'] = $data['link'];
			foreach($data['children'] as $child){
				$modal = '';
				if ($child['modal']){
					$size = '';
					if ($child['modal'] === 'small'){
						$size = 'data-size="sm"';
					} elseif ($child['modal'] === 'large'){
						$size = 'data-size="lg"';
					}
					$modal = 'data-toggle="modal" data-target="#modal" ' . $size . ' ';
				}
				$menu[$key]['children'][] = '<a ' . $modal . 'href="' . $child['link'] . '">' . $child['title'] . '</a>';
			}
		}

		return render(DIR_ROOT . ADMIN . '/view/toolbar.php', array(
			'page' => $this->response->data,
			'types' => $types,
			'template' => $this->config_site['template'],
			'menu' => $menu,
			'request_uri' => $this->request->server['REQUEST_URI'],
		));
	}
}