<?php
/**
* Box module. View an access log.
* @varsion: 1.0.0
* @author ArtyGrand
*/
 
class Access extends Module{
	/**
	* Sets the allowed actions for user
	* @var array
	*/
	public static $allowedActions = array('view');
	
	/**
	* Just view data, in table
	* @return array
	*/
	public function view(){
		$rawData = Core::$db -> query("SELECT * FROM access ORDER BY id DESC");
		$p['content'] = '<table class="table full"><thead><tr><th>' . lang('date') . '</th><th>IP</th><th>' . lang('login') . '</th><th>' . lang('password') . '</th><th>' . lang('comment') . '</th></tr></thead>';
		if ($rawData){
			foreach($rawData as $data){
				$data['text'] = in_array($data['text'], array('Used Cookies', 'Access granted')) ? '<span style="color:green">' . $data['text'] . '</span>' : '<span style="color:red">' . $data['text'] . '</span>';
				$p['content'] .= '<tr><td>' . $data['date'] . '</td><td>' . $data['ip'] . '</td><td>' . $data['login'] . '</td><td>' . $data['pass'] . '</td><td>' . $data['text'] . '</td></tr>';
			}
		}

		$p['content'] .= '</table>';
		$p['breadcrumbs'] =  lang('access_log') . ' &gt; <span>' . lang('view') . '</span>';
		return $p;
	}
	
	/**
	* Clear table ##WARNING##
	* @return array
	*/
	private function deleteme(){
		Core::$db -> query("TRUNCATE TABLE access");
		$p['redirect'] = 1;
		return $p;
	}
}
?>