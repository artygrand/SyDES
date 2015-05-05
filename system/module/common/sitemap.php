<?php
/**
 * @package SyDES
 *
 * @copyright 2011-2015, ArtyGrand <artygrand.ru>
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

class SitemapController extends Controller{
	public $name = 'sitemap';
	public static $front = array('index');
	
	public function index(){
		$sitemap = $this->cache->get($this->site . '.' . $this->locale . '.' . 'sitemap');
		if (!$sitemap){
			$stmt = $this->db->query("SELECT p.path, p.cdate FROM pages p, pages_content pc
			WHERE p.status > 0 AND pc.page_id = p.id AND pc.locale = '{$this->locale}' AND pc.title != '' ORDER BY p.path");
			$pages = $stmt->fetchAll(PDO::FETCH_ASSOC);

			if (!$pages){
				return;
			}

			$in_url = count($this->config_site['locales']) > 1 ? $this->locale : '';
			$sitemap = '<?xml version="1.0" encoding="UTF-8"?>
<?xml-stylesheet type="text/xsl" href="/system/module/common/assets/xml-sitemap.xsl"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

			foreach ($pages as $page){
				$page['path'] = $page['path'] == '/' ? $in_url : ($in_url ? $in_url . $page['path'] : substr($page['path'], 1));
				$sitemap .= '
	<url>
		<loc>http://' . $this->base . '/' . $page['path'] . '</loc>
		<lastmod>' . date('Y-m-d', $page['cdate']) . '</lastmod>
		<changefreq>monthly</changefreq>
		<priority>0.5</priority>
	</url>';
			}

			$sitemap .= PHP_EOL . '</urlset>';
			$this->cache->set($this->site . '.' . $this->locale . '.' . 'sitemap', $sitemap, 2592000);
		}

		$this->response->mime = 'xml';
		$this->response->body = $sitemap;
	}
}