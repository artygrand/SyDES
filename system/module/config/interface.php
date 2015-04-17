<?php
/**
 * @package SyDES
 *
 * @copyright 2011-2015, ArtyGrand <artygrand.ru>
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

class InterfaceController extends Controller{
	public static $name = 'interface';

	public function modal(){
		$languages = str_replace(DIR_LANGUAGE, '', glob(DIR_LANGUAGE .'*'));
		$skins = str_replace(array(DIR_SYS . 'assets/css/skin.', '.css'), '', glob(DIR_SYS . 'assets/css/skin.*.css'));

		$body = $this->load->view('config/interface', array(
			'languages' => $languages,
			'language' => $this->language,
			'menu' => isset($_COOKIE['menu']),
			'menu_pos' => isset($_COOKIE['menu_pos']),
			'skins' => $skins
		));

		$footer = H::button(t('save'), 'button', 'class="btn btn-primary apply-modal"');

		$this->response->body = H::modal(t('interface_setup'), $body, $footer, '?route=config/interface/save');
	}

	public function save(){
		setcookie('language', $this->request->post['language'], time()+604800);
		if ((bool)$this->request->post['menu']){
			setcookie('menu', 'click', time()+604800);
		} else {
			setcookie('menu', '', time()-1);
		}
		if ((bool)$this->request->post['menu_pos']){
			setcookie('menu_pos', 'left', time()+604800);
		} else {
			setcookie('menu_pos', '', time()-1);
		}
		$this->response->notify(t('saved'));
	}
}