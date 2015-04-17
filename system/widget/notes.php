<?php
/**
 * @package SyDES
 * @subpackage dashboard widgets
 * @copyright 2011-2015, ArtyGrand <artygrand.ru>
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

class NotesWidget extends Widget{
	public $name = 'notes';
	public function index(){
		$data = $this->getData();
		$this->title = t('widget_notes');
		$this->content = H::textarea('notes', $data, 'id="note" rows="10" placeholder="' . t('notes_placeholder') . '"');
		$this->wrap_body = false;
		$this->style = '#note{display:block;border:none;width:100%;max-width:100%;padding:5px 8px;}';
		$this->script = "
		$(document).on('change','#note',function(){
			$.ajax({url:'?route=dashboard/save', data:{widget:'notes', data:$(this).val()}})
		})
		";
		return $this->render();
	}
}