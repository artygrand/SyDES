<?php
/*actions*/
$l['delete'] = 'Delete';
$l['update'] = 'Update';
$l['edit'] = 'Edit';
$l['all_selected_pages'] = 'All selected pages';
$l['add_more'] = 'Add more';
$l['show'] = 'Show';
$l['hide'] = 'Hide';
$l['save'] = 'Save';
$l['view'] = 'View';
$l['not_writeable'] = 'Not writable';
$l['show_or_hide'] = 'Show or Hide';
$l['check_all'] = 'Check all';
$l['recover'] = 'Recover';
/*main*/
$l['admin'] = 'Admin';
$l['to_site'] = 'To the site';
$l['content'] = 'Content';
$l['modules'] = 'Modules';
$l['settings'] = 'Settings';
$l['exit'] = 'Exit';
$l['configuration'] = 'Configuration';
$l['template'] = 'Template';
$l['iblocks'] = 'Infoblocks';
$l['access_log'] = 'Access log';
$l['license'] = 'Licensed for GNU/GPL';
$l['welcome'] = 'Welcome! Again.';
$l['login'] = 'Login';
$l['account_login'] = 'Account login';
$l['password'] = 'Password';
$l['username'] = 'Username';
$l['remember_me'] = 'Remember me';
$l['signup'] = 'Signup';
$l['status'] = 'Status';
$l['hidden'] = 'hidden';
$l['visible'] = 'visible';
$l['trash'] = 'Trash bin';
$l['add_type'] = 'Add page type';
$l['date'] = 'Date';
$l['comment'] = 'Comment';
$l['log_in'] = 'Log In';
$l['site_title_empty'] = 'Add site_title meta data in config';
/*messages*/
$l['unauthorized_request'] = 'Unauthorized request';
$l['no_value'] = 'No value has passed';
$l['deleted'] = 'Deleted';
$l['not_deleted'] = 'Error: hasn\'t deleted';
$l['saved'] = 'Saved';
$l['error_not_saved'] = 'Error: hasn\'t saved';
$l['folder_created'] = 'Folder has created';
$l['folder_not_created'] = 'Folder hasn\'t created';
$l['folder_not_deleted'] = 'folder hasn\'t deleted. May be not empty?';
$l['db_not_open'] = 'Data base hasn`t open';
$l['no_table_created'] = 'Table hasn`t created';
$l['file_not_exist'] = 'File not exist';
$l['file_empty'] = 'File is empty';
$l['already_exists'] = 'Already exists';
$l['success'] = 'Success!';
$l['module_installed'] = 'Module was installed';
$l['confirm_deletion'] = 'Are you sure?';
/*mod pages*/
$l['yet_empty'] = 'Yet empty. Just click [+1] for adding one page.';
$l['page_title'] = 'Title';
$l['link'] = 'Link';
$l['actions'] = 'Actions';
$l['not_saved'] = 'Page<br>not saved';
$l['parent'] = 'Parent';
$l['editor'] = 'Editor';
$l['page_alias'] = 'Slug';
$l['page_content'] = 'Page content';
$l['root'] = 'Root';
$l['show_all_pages'] = 'Show all pages?';
$l['view_page'] = 'View on site';
$l['hide_page'] = 'Hide page';
$l['show_page'] = 'Show page';
$l['clear_cache'] = 'Clear cache';
$l['not_cached'] = 'Not cached';
$l['no_translation'] = 'No translation';
/*system modules*/
$l['iblock_list'] = 'List of infoblocks';
$l['add_iblock'] = 'Click me for adding new infoblock';
$l['insert_title'] = 'Now enter a name and press Enter key to save';
$l['epmty'] = 'Empty';
$l['other_files'] = 'Other files';
$l['developer_code'] = 'Master code';
$l['site'] = 'Site';
$l['site_work'] = 'Maintenance mode';
$l['site_location'] = 'Site location';
$l['site_template'] = 'Site template';
$l['admin_ip'] = 'Access with these IP when maintenance';
$l['maintenance'] = 'Maintenance text';
$l['locales'] = 'Site locales';
$l['admin_login'] = 'Login';
$l['new_pass'] = 'New password';
$l['new_developer_code'] = 'New master code';
$l['final_template'] = 'Final templates';
$l['pages'] = 'Pages';
$l['your_ip'] = 'Your IP:';
$l['type'] = 'Page type';
$l['name'] = 'Name in menu';
$l['default_template'] = 'Default template';
$l['need_cache'] = 'Enable Caching';
/*meta plugin*/
$l['meta_data'] = 'Meta data';
$l['meta_tip'] = "Select a key from the list or enter a new\nThe teplate output from the {meta:key}";
$l['meta_key'] = 'Key';
$l['meta_value'] = 'Value';
$l['add'] = 'Add';
$l['exist_keys'] = 'Existing keys';
/*tips*/
$l['tip_alias'] = 'Page alias';
$l['tip_title'] = 'A few words';
$l['click_to_show'] = 'Click to show';
$l['tip_developer_code'] = 'Firewall for hackers';
$l['tip_final_template'] = 'Pages, who use these templates, is invisible for parent select';
$l['tip_enter_with_spacebar'] = 'Separate keys with spaces';
$l['tip_locales'] = "Enter separated by spaces two-letter\nlanguage codes: en ru de it";

//clean the string
function properUri($str){
	if(preg_match('![^\w-]!', $str)){
		$tr = array(' '=>'-','*'=>'-','+'=>'-');
		$str = strtr($str,$tr);
		$str = preg_replace('![^\w-]!', '', $str);
	}
	return strtolower($str);
}
?>