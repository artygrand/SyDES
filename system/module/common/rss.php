<?php
/**
 * @package SyDES
 *
 * @copyright 2011-2015, ArtyGrand <artygrand.ru>
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

class RssController extends Controller{
	public $name = 'rss';
	public static $front = array('view');

	public function index(){
		
	}

	public function view(){
		if (preg_match('![^\w-]!', $this->value) || $this->value == 'trash'){
			throw new BaseException(t('error_page_not_found'));
		}

		$this->load->model('pages');
		$items = $this->pages_model->getList(array("type = '{$this->value}'", "position NOT LIKE '#%'", "status > 0"), 'cdate DESC', 10);

		if (empty($items)){
			throw new BaseException(t('error_page_not_found'));
		}

		$last = current($items);
		$rss = '<?xml version="1.0" encoding="UTF-8"?>
<rss version="2.0"
	xmlns:content="http://purl.org/rss/1.0/modules/content/"
	xmlns:atom="http://www.w3.org/2005/Atom"
	xmlns:sy="http://purl.org/rss/1.0/modules/syndication/"
	>
<channel>
	<title>' . $this->config_site['name'] . '</title>
	<atom:link href="http://' . $_SERVER['SERVER_NAME'] . '/rss/' . $this->value . '" rel="self" type="application/rss+xml" />
	<link>http://' . $_SERVER['SERVER_NAME'] . '</link>
	<description></description>
	<lastBuildDate>' . date('r', $last['cdate']) . '</lastBuildDate>
	<generator>http://sydes.ru</generator>
	<language>' . $this->locale . '</language>
	<sy:updatePeriod>weekly</sy:updatePeriod>
	<sy:updateFrequency>1</sy:updateFrequency>
';

		foreach ($items as $item){
			$rss .= '	<item>
		<title>' . $item['title'] . '</title>
		<link>http://' . $_SERVER['SERVER_NAME'] . '/' . $item['fullpath'] . '</link>
		<pubDate>' . date('r', $item['cdate']) . '</pubDate>
		<guid isPermaLink="false">' . $_SERVER['SERVER_NAME'] . '.' . $item['id'] . '</guid>
		<description><![CDATA[' . strip_tags($item['content']) . ']]></description>
		<content:encoded><![CDATA[' . $item['content'] . ']]></content:encoded>
	</item>
';
		}
		$rss .= '</channel>
</rss>';
		$this->response->mime = 'xml';
		$this->response->body = $rss;
	}
}