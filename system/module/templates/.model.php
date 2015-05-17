<?php
/**
 * @package SyDES
 *
 * @copyright 2011-2015, ArtyGrand <artygrand.ru>
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

class TemplatesModel extends Model{
	public $template;
	public $template_path;
	public $layout_db;

	public function prepare(){
		$this->template = isset($this->request->get['tpl']) ? $this->request->get['tpl'] : $this->config_site['template'];
		$this->template_path = DIR_TEMPLATE . $this->template . '/';

		if (!is_file($this->template_path . 'page.html')){
			throw new BaseException(sprintf(t('error_template_not_found'), $this->template));
		}
		$this->layout_db = $this->template_path . 'layouts.php';

		if (isset($this->request->get['file']) && $this->request->get['file'] != 'clone'){
			$ext = strrchr($this->request->get['file'], '.');
			if (!$ext || !in_array($ext, array('.css', '.js', '.html', '.php'))){
				throw new BaseException(t('error_wrong_values_passed'));
			}

			$file = str_replace($ext, '', $this->request->get['file']);
			if (strpos($file, '.') !== false){
				throw new BaseException(t('error_wrong_values_passed'));
			}
		}
	}

	public function createLayouts(){
		$layouts = $this->getLayouts();
		$used = array();
		foreach ($layouts as $layout){
			$used[] = $layout['file'];
		}
		$files = $this->getFiles('html');
		$need = array_diff($files,$used);
		if ($need){
			foreach ($need as $file){
				$name = str_replace('.html', '', $file);
				if (!isset($layouts[$name])){
					$layouts[$name] = array('name' => $name, 'file' => $file, 'html' => '{content}');
				} else {
					$layouts[$name]['file'] = $file;
				}
			}
			arr2file($layouts, $this->layout_db);
		}
	}

	public function getLayouts(){
		return file_exists($this->layout_db) ? include $this->layout_db : array();
	}

	public function getFiles($exts = array('html','css','js')){
		foreach ((array)$exts as $ext){
			foreach (glob($this->template_path . '*.' . $ext) as $file){
				$file = str_replace($this->template_path, '', $file);
				$files[$file] = $file;
			}
		}
		return $files;
	}

	public function getIblocks(){
		$pre = '';
		foreach (glob(DIR_IBLOCK . '*') as $file){
			$pre .= str_replace(DIR_IBLOCK, '{iblock:', $file) . '}' . PHP_EOL;
		}
		if ($pre){
			return '<label>' . t('iblock_list') . '</label>' . '<pre>' . $pre . '</pre>';
		}
	}

	public function getOverrides($entity){
		$glob = $this->template_path . '/' . $entity . '/*/*.php';
		$preg = '/' . $entity . '\/([\w-]+)\/([\w-]+)\.php/';

		$files = array();
		$search = glob($glob);
		if (!$search){
			return $files;
		}
		foreach ($search as $file){
			preg_match($preg, $file, $files[]);
		}
		return $files;
	}
}