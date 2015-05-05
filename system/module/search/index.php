<?php
/**
 * @package SyDES
 *
 * @copyright 2011-2015, ArtyGrand <artygrand.ru>
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

class SearchController extends Controller{
	public static $front = array('index');
	public $name = 'search';

	public function index(){
		mb_regex_encoding('UTF-8');
		mb_internal_encoding('UTF-8');

		$page['layout'] = 'page';
		$page['title'] = t('search');
		$locale = count($this->config_site['locales']) > 1 ? $this->locale : '';

		if (!empty($this->request->get['s'])){
			$words = explode(' ', preg_replace('/\s+/', ' ', $this->request->get['s']));
			$query = implode(' ', $words);

			$stemmer = new Lingua_Stem_Ru();
			foreach ($words as $i => $word){
				$words[$i] = $stemmer->stem_word($word);
			}

			$implode = array();
			$insert = array();
			foreach ($words as $word){
				$implode[] = "tolower(pc.title) LIKE ?";
				$insert[] = '%' . lower($word) . '%';
			}
			$part1 = implode(" AND ", $implode);

			$implode = array();
			foreach ($words as $word){
				$implode[] = "tolower(pc.content) LIKE ?";
				$insert[] = '%' . lower($word) . '%';
			}
			$part2 = implode(" AND ", $implode);

			$sql = "SELECT pages.path, pc.title FROM pages, pages_content pc WHERE pages.id = pc.page_id AND pc.locale = '{$this->locale}' AND (";
			$sql .= '(' . $part1 . ') OR (' . $part2 . ')';
			$sql .= ') ORDER BY pc.title LIMIT 500';

			$this->db->sqliteCreateFunction('tolower', 'lower', 1);
			$stmt = $this->db->prepare($sql);
			$stmt->execute($insert);
			$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

			if (count($result) == 1){
				$fullpath = $locale ? $locale . $result[0]['path'] : substr($result[0]['path'], 1);
				$this->response->redirect($fullpath);
			}

			if ($result){
				$page['meta_title'] = sprintf(t('search_found'), pluralize(count($result), t('match'), t('matches'), t('matches2')));
			} else {
				$page['meta_title'] = t('search_not_found');
			}
		} else {
			$query = '';
			$result = array();
			$page['meta_title'] = t('search');
		}

		$page['content'] = $this->load->view('search/form', array(
			'query' => $query,
			'locale' => $locale,
			'result' => $result,
		));

		$this->response->data = $page;
	}
}