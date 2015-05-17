<?php
/**
 * @package SyDES
 *
 * @copyright 2011-2015, ArtyGrand <artygrand.ru>
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

class Admin extends HasRegistry{
	public function getLocale(){
		$upd = true;
		if (!empty($_GET['locale'])){
			$this->locale = $_GET['locale'];
		} elseif (!empty($_COOKIE['locale'])){
			$this->locale = $_COOKIE['locale'];
			$upd = false;
		}
		$locales = $this->config_site['locales'];
		if (!in_array($this->locale, $locales)){
			$this->locale = $locales[0];
			$upd = true;
		}
		if ($upd){
			setcookie('locale', $this->locale, time()+604800);
		}
	}
	
	public function getSite(){
		$upd = true;
		if (!empty($_GET['site'])){
			$this->site = $_GET['site'];
		} elseif (!empty($_COOKIE['site'])){
			$this->site = $_COOKIE['site'];
			$upd = false;
		}
		$sites = str_replace(DIR_SITE, '', glob(DIR_SITE . 's*'));
		if (empty($sites)){
			$this->site = false;
			return;
		}
		if (!in_array($this->site, $sites)){
			$this->site = $sites[0];
			$upd = true;
		}
		if ($upd){
			setcookie('site', $this->site, time()+604800);
		}
		$domains = include DIR_SITE . $this->site . '/domains.php';
		$this->base = $domains[0];
	}
	
	public function render(){
		$dummy = array(
			'language' => $this->language,
			'context_menu' => $this->site ? $this->getContextMenu() : '',
			'meta_title' => '',
			'token' => $this->user->token,
			'menu_pos' => isset($this->request->cookie['menu_pos']) ? 'left' : 'top',
			'site_name' => $this->config_site['name'],
			'page_types' => $this->site ? $this->getPagesList() : '',
			'modules' => $this->site ? $this->getModuleList() : '',
			'menu_sections' => $this->site ? $this->getMenuSections() : array(),
			'breadcrumbs' => '',
			'form_url' => '',
			'sidebar_left' => '',
			'content' => '',
			'sidebar_right' => '',
			'footer_left' => '',
			'footer_center' => '',
			'version' => VERSION,
			'skin' => isset($this->request->cookie['skin']) ? $this->request->cookie['skin'] : 'black',
			'col_sm' => 12,
			'col_lg' => 12,
			'styles' => $this->response->style,
			'scripts' => $this->response->script,
			'alerts' => $this->response->alerts,
			'base' => '//' . $this->base,
		);
		if (!empty($this->response->data['sidebar_left'])){
			$dummy['col_sm'] = $dummy['col_sm']-3;
			$dummy['col_lg'] = $dummy['col_lg']-2;
		}
		if (!empty($this->response->data['sidebar_right'])){
			$dummy['col_sm'] = $dummy['col_sm']-3;
			$dummy['col_lg'] = $dummy['col_lg']-2;
		}
		$dummy['js'] = '<script>$.extend(syd, ' . json_encode($this->response->js) . ');</script>';

		$this->response->data = array_merge($dummy, $this->response->data);
		$this->response->body = render('view/main.php', $this->response->data);
	}

	public function getPagesList(){
		$list = array();
		foreach ($this->config_site['page_types'] as $type => $data){
			if (isset($data['hidden'])){
				continue;
			}
			$list['?route=pages&type=' . $type] = $data['title'] . '</a><a href="?route=pages/edit&type=' . $type . '" class="add-more" data-toggle="tooltip" data-placement="right" title="' . t('tip_add_more'). '">[+1]';
		}
		return H::listLinks($list, '', 'class="list-unstyled"');
	}

	public function getModuleList(){
		$list = array();
		foreach ($this->config_site['modules'] as $module => $data){
			if (!$data['in_menu']){
				continue;
			}
			$this->load->language('module_' . $module);
			$list['?route=' . $module] = t('module_' . $module);
			if ($data['quick_add']){
				$list['?route=' . $module] .= '</a><a href="?route=' . $module . '/edit" class="add-more" data-toggle="tooltip" data-placement="right" title="' . t('tip_add_more'). '">[+1]';
			}
		}
		return H::listLinks($list, '', 'class="list-unstyled"');
	}

	public function getMenuSections(){
		$sections = array();
		if (isset($this->config_site['menu_sections'])){
			foreach ($this->config_site['menu_sections'] as $sect => $data){
				$sections[] = array(
					'title' => $data['title'],
					'list' => H::listLinks($data['list'], '', 'class="list-unstyled"')
				);
			}
		}
		return $sections;
	}
	
	public function getContextMenu(){
		$this->response->context['setup']['title'] = '<span class="glyphicon glyphicon-cog"></span> ' . t('set_up');
		$this->response->context['setup']['link'] = '';
		$this->response->context['setup']['children'][] = array(
			'title' => t('interface'),
			'link' => '?route=config/interface/modal',
			'modal' => true,
		);

		$menu = array();
		foreach ($this->response->context as $key => $data){
			$menu[$key]['title'] = $data['title'];
			$menu[$key]['link'] = $data['link'];
			foreach ($data['children'] as $child){
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
		return $menu;
	}
}