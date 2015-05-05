<?php
/**
 * @package SyDES
 * @subpackage dashboard widgets
 * @copyright 2011-2015, ArtyGrand <artygrand.ru>
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

class LastpagesWidget extends Widget{
	public $name = 'lastpages';
	public function index(){
		$pages = $this->load->model('pages', false);
		$result = $pages->getList(array("type != 'trash'"), 'id DESC', 10);
		foreach ($result as $row){
			$rows[] = array(
				H::link($row['title'], '//' . $this->base . '/' . $row['fullpath'], 'target="_blank"'),
				tDate($this->locale, 'd M Y', $row['cdate']),
				H::link(t('edit'), '?route=pages/edit&type=' . $row['type'] . '&id=' . $row['id'], 'class="btn btn-default btn-xs"')
			);
		}
		$this->title = t('widget_lastpages');
		$this->content = H::table($rows, false, 'class="table lastpages"');
		$this->style = ".lastpages td:nth-child(1){max-width:200px;white-space:nowrap;text-overflow:ellipsis;overflow:hidden;}.lastpages td:nth-child(2){width:100px;}.lastpages td:nth-child(3){text-align:right;width:80px;}";
		$this->wrap_body = false;
		return $this->render();
	}
}