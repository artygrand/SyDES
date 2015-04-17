<?php
/**
 * @package SyDES
 * @subpackage dashboard widgets
 * @copyright 2011-2015, ArtyGrand <artygrand.ru>
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

class Widget extends HasRegistry{
	public $name;
	public $title;
	public $content;
	public $footer;
	public $style;
	public $script;
	public $data;
	public $wrap_body = true;
	public $has_settings = false;

	protected function render(){
		$tools = '';
		if ($this->has_settings){
			$tools .= '<a href="?route=dashboard/setup&widget=' . $this->name . '" data-toggle="modal" data-target="#modal">
			<span class="glyphicon glyphicon-cog" data-toggle="tooltip" title="' . t('set_up') . '"></span>
			</a>';
		}
		$tools .= '<a href="?route=dashboard/remove&widget=' . $this->name . '"">
			<span class="glyphicon glyphicon-trash" data-toggle="tooltip" title="' . t('remove') . '"></span>
			</a>';
		$html = '
	<div class="panel panel-default widget widget-' . $this->name . '" data-widget="' . $this->name . '">
	<div class="widget-tools">' . $tools . '</div>';
		if ($this->title){
			$html .= '<div class="panel-heading">
				<h4 class="panel-title">' . $this->title . '</h4>
			</div>';
		}
		if ($this->wrap_body){
			$html .= '<div class="panel-body">' . $this->content . '</div>';
		} else {
			$html .= $this->content;
		}
		if ($this->footer){
			$html .= '<div class="panel-footer">' . $this->footer . '</div>';
		}
		$html .= '</div>';
		
		if ($this->style){
			$html .= '<style>' . $this->style . '</style>';
		}
		if ($this->script){
			$html .= '<script>' . $this->script . '</script>';
		}
		
		return $html;
	}
	
	protected function getData(){
		$config = new Config('widget');
		return $config->get($this->name);
	}
}