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
	public $settings;
	public $settings_file;

	public function prepare(){
		$this->template = isset($this->request->get['tpl']) ? $this->request->get['tpl'] : $this->config_site['template'];
		$this->template_path = DIR_TEMPLATE . $this->template . '/';

		if (!is_file($this->template_path . 'page.html')){
			throw new BaseException(sprintf(t('error_template_not_found'), $this->template));
		}

		$this->settings_file = $this->template_path . 'manifest.ini';
		if (is_file($this->settings_file)){
			$this->settings = parse_ini_file($this->settings_file, true);
		} else {
			$this->settings = array(
				'theme' => array(
					'name' => 'Theme name ' . token(4),
					'description' => 'Theme description',
					'version' => '1.0',
					'author' => 'You',
					'author_uri' => ''
				),
				'layouts' => array()
			);
			$this->createLayouts();
		}

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
		$used = array();
		foreach ($this->settings['layouts'] as $layout){
			$used[] = $layout['file'];
		}
		$files = $this->getFiles('html');
		$need = array_diff($files,$used);
		if ($need){
			if (!is_dir($this->template_path . 'layout')){
				mkdir($this->template_path . 'layout', 0777);
			}
			foreach ($need as $file){
				$name = str_replace('.html', '', $file);
				if (!isset($this->settings['layouts'][$name])){
					$this->settings['layouts'][$name] = array('name' => $name, 'file' => $file);
					file_put_contents($this->template_path . 'layout/' . $name . '.html', '{content}');
				} else {
					$this->settings['layouts'][$name]['file'] = $file;
				}
			}
			write_ini_file($this->settings, $this->settings_file, true);
		}
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