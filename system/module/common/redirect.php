<?php
/**
 * @package SyDES
 *
 * @copyright 2011-2015, ArtyGrand <artygrand.ru>
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

class RedirectController extends Controller{
	public $name = 'redirect';
	public static $front = array('index');

	public function index(){
		$this->response->redirect($this->request->get['url']);
	}
}