<?
/**
* Infoblock: Breadcrumbs
*
* @author ArtyGrand
* @since 1.6
*/

$defaults = array(
	'hide_current' => false,
	'separator' => '»',
);
$args = array_merge($defaults, $args);

if(!isset($page['id']) or $page['id'] < 2) return;

$stmt = $this->db->query("SELECT p1.path, pc.title
	FROM pages p1, pages p2
	LEFT JOIN pages_content pc ON pc.page_id = p1.id AND pc.locale = '{$this->locale}'
	WHERE p2.id = '{$page['parent_id']}' AND (p2.position LIKE p1.position || '%' OR p1.id = 1) AND p1.id != 0  AND p1.type != 'trash' ORDER BY p1.position");
$crumbs = $stmt->fetchAll(PDO::FETCH_ASSOC);
if(!$crumbs) return;

$arr = array();
foreach($crumbs as $crumb){
	$fullpath = $crumb['path'] == '/' ? $this->pages_model->in_url : ($this->pages_model->in_url ? $this->pages_model->in_url . $crumb['path'] : substr($crumb['path'], 1));
	$arr[] = '<a href="' . $fullpath . '">' . $crumb['title'] . '</a>';
}
if (!$args['hide_current']){
	$arr[] = '<span>' . $page['title'] . '</span>';
}
echo '<div class="breadcrumbs">', implode(" {$args['separator']} ", $arr), '</div>';