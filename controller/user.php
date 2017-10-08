<?php 
// USER ============================================================================
function atk_user_view_data() {
	$judul = array(array('data'=>t('No.'),),
			array('data'=>t('Username'),),
			array('data'=>t('Fullname'),),
			array('data'=>t('Lokasi'),),
			array('data'=>t('Hak Akses'),),
			array('data'=>t('Atasan 1'),),
			array('data'=>t('Atasan 2'),),
			array('data'=>t('Atasan 3'),),
			array('data'=>t('Edit'),),
			array('data'=>t('Delete'),)
	);
	db_set_active('pep');
	$db_data = db_query("SELECT * FROM atk_user ORDER BY lokasi,fullname");
	while($row = db_fetch_array($db_data)) {
		$edit 	= "<a href='" .base_path(). "atk/online/user/set/?id=" .$row['username']. "'>edit</a>";
		$delete	= "<a href='" .base_path(). "atk/online/user/delete/?id=" .$row['username']. "' onclick='if(confirm(\"are you sure ?\") != true){ return false }'>hapus</a>";
		
		$usr	= atk_user_roles(); 	// cek data login
		$adm	= $usr['admin'];
		$sa		= $usr['sa'];
		$un		= $usr['nama'];
		$usr_atk= atk_user_data($un); 	// cek data ATK
		$lokasi	= $usr_atk['lokasi'];   // int
		
		if($sa){
			$isi[] 	= array(++$xyz, $row['username'], $row['fullname'], cek_lokasi_atk($row['lokasi']), cek_akses_atk($row['username']), $row['atasan1'], $row['atasan2'], $row['atasan3'], $edit, $delete);
		}elseif($lokasi == $row['lokasi']){
			$isi[] 	= array(++$xyz, $row['username'], $row['fullname'], cek_lokasi_atk($row['lokasi']), cek_akses_atk($row['username']), $row['atasan1'], $row['atasan2'], $row['atasan3'], $edit, $delete);
		}
		
	}
	db_set_active();
	$hasil['judul'] = $judul;
	$hasil['isi'] 	= $isi;
	return $hasil;
}
function atk_user_view_data2() {
	$judul = array(array('data'=>t('No.'),),
			array('data'=>t('Username'),),
			array('data'=>t('Fullname'),),
			array('data'=>t('Lokasi'),),
			array('data'=>t('Hak Akses'),),
			array('data'=>t('Atasan 1'),),
			array('data'=>t('Atasan 2'),),
			array('data'=>t('Atasan 3'),)
	);
	db_set_active('pep');
	$db_data = db_query("SELECT * FROM atk_user ORDER BY lokasi,fullname");
	while($row = db_fetch_array($db_data)) {
		$usr	= atk_user_roles(); 	// cek data login
		$adm	= $usr['admin'];
		$sa		= $usr['sa'];
		$un		= $usr['nama'];
		$usr_atk= atk_user_data($un); 	// cek data ATK
		$lokasi	= $usr_atk['lokasi'];   // int
		
		if($sa){
			$isi[] 	= array(++$xyz, $row['username'], $row['fullname'], cek_lokasi_atk($row['lokasi']), cek_akses_atk($row['username']), $row['atasan1'], $row['atasan2'], $row['atasan3']);
		}elseif($lokasi == $row['lokasi']){
			$isi[] 	= array(++$xyz, $row['username'], $row['fullname'], cek_lokasi_atk($row['lokasi']), cek_akses_atk($row['username']), $row['atasan1'], $row['atasan2'], $row['atasan3']);
		}
		
	}
	db_set_active();
	$hasil['judul'] = $judul;
	$hasil['isi'] 	= $isi;
	return $hasil;
}
function atk_user_view_toexcel(){
	$data 		= atk_user_view_data2();
	$judul		= $data['judul'];
	$isi		= $data['isi'];

	$workbook = new Spreadsheet_Excel_Writer();
	$workbook->send('list_all_user.xls');
	$worksheet =& $workbook->addWorksheet('list_all_user');
	$worksheet->freezePanes(array(1, 0));
	$format =& $workbook->addFormat(array('Size' => 10,
			'Align' => 'center',
			'Bold' => 1,
			'Color' => 'white',
			'Pattern' => 1,
			'BgColor' => 'white',
			'FgColor' => 'grey'));
	$worksheet->setColumn(0,0,5);
	$worksheet->setColumn(1,1,20); // username
	$worksheet->setColumn(2,2,30); // Fullname
	$worksheet->setColumn(3,3,30); // Lokasi
	$worksheet->setColumn(4,4,20); // Akses
	$worksheet->setColumn(5,5,30); // Atasan

	foreach ($judul as $key => $value){
		foreach ($value as $key1 => $hasil){
			$worksheet->write(0, $key, $hasil, $format);
		}
	}
	$r = 1;
	foreach ($isi as $key => $value){
		foreach ($value as $key1 => $hasil){
			$worksheet->write($r, $key1, $hasil);
		}
		$r++;
	}
	$workbook->close();
}
function atk_user_data($id) {
	db_set_active('pep');
	$db_data = db_query("SELECT * FROM atk_user WHERE username = '$id'");
	while($row = db_fetch_array($db_data)) {
		$hasil['username'] 	= $row['username'];
		$hasil['fullname'] 	= $row['fullname'];
		$hasil['lokasi']	= $row['lokasi'];
		$hasil['akses'][169]	= $row['akses5'];
		$hasil['akses'][170]	= $row['akses1'];
		$hasil['akses'][171]	= $row['akses2'];
		$hasil['akses'][172]	= $row['akses3'];
		$hasil['akses'][173]	= $row['akses4'];
		$hasil['akses'][174]	= $row['akses6'];
		$hasil['atasan1']	= $row['atasan1'];
		$hasil['atasan2']	= $row['atasan2'];
		$hasil['atasan3']	= $row['atasan3'];
	}
	db_set_active();
	return $hasil;
}
function cek_user_lokasi(){
	$name		= $GLOBALS['user']->name;
	$data		= atk_user_data($name);		// nama user (username)
	$lokasi		= $data['lokasi'];
	return $lokasi; 						// lokasi (int)
}
function cek_akses_name_atk($rid) {
	db_set_active('default');
	$db_data = db_query("SELECT * FROM role WHERE rid = $rid");
	while($row = db_fetch_array($db_data)) {
		$nama 	= $row['name'];
	}
	db_set_active();
	return $nama;
}
function cek_akses_atk($username) {
	$uid	= atk_usernametouid($username);	
	
	db_set_active('default');
	$db_data = db_query("SELECT uid,rid FROM users_roles WHERE uid = $uid && rid IN (169,170,171,172,173,174)"); // atk SA, atk issuer, scm approve, Man Apprv, gudang, atk Admin 
	while($row = db_fetch_array($db_data)) {
		$uidx[] 	= cek_akses_name_atk($row['rid']);
	}
	db_set_active();
	
	if(isset($uidx)){
		$hasil = implode($uidx, '<br>');
	}
	return $hasil;
}
function atk_user_view() {
	$data = atk_user_view_data();
	$output = theme_table($data['judul'], $data['isi']);
	$hasil = "<a href='" .base_path(). "atk/online/user/set'>Add User ATK</a> | <a href='" .base_path(). "atk/online/master'>[back]</a><br><br>";
	return $hasil.$output;
}
function atk_user_roles(){
	$hasil['nama']		= $GLOBALS['user']->name;
	$hasil['uid'] 		= $GLOBALS['user']->uid;
	$hasil['sa']		= $GLOBALS['user']->roles[169];
	$hasil['issuer']	= $GLOBALS['user']->roles[170];
	$hasil['scmApp']	= $GLOBALS['user']->roles[171];
	$hasil['mgrApp']	= $GLOBALS['user']->roles[172];
	$hasil['gudang']	= $GLOBALS['user']->roles[173];
	$hasil['admin']		= $GLOBALS['user']->roles[174];
	
	return $hasil;
}
function user_name($string) {			// autofill 
	$string1 = ucfirst($string);
	$string2 = strtoupper($string);
	$hitstr  = strlen($string);
	if($hitstr >= 3){
		db_set_active('pep');
		$db_data = db_query("SELECT a.person_id, a.name, b.person_id, b.position_id, b.employee_no, b.fullname, c.position_id, c.name
								FROM top_person AS a
									LEFT JOIN org_employee AS b ON b.person_id = a.person_id
									LEFT JOIN org_position AS c ON c.position_id = b.position_id 
								WHERE a.name LIKE '%" .$string. "%' OR b.fullname LIKE '%" .$string. "%' OR c.name LIKE '%" .$string. "%' OR a.name LIKE '%" .$string1. "%' OR b.fullname LIKE '%" .$string1. "%' OR c.name LIKE '%" .$string1. "%' OR a.name LIKE '%" .$string2. "%' OR b.fullname LIKE '%" .$string2. "%' OR c.name LIKE '%" .$string2. "%' OR b.employee_no LIKE '%" .$string. "%'
								ORDER BY b.fullname");
		while($row = mysql_fetch_array($db_data)) {
			$username			= $row[1];
			$nopek				= $row[4];
			$fullname 			= $row[5];
			$stat 				= $row[7];		
			$data[$username] = $fullname .' - '. $nopek .' - '. $stat;		
		}
		db_set_active();
	}else{
		$data['null'] = '';
	}

	print drupal_to_js($data);
	exit();
}
function cek_lokasi_atk($id = NULL) {
	$site_options 	= atk_plant_list();
	if($id==''){
		return $site_options;		
	}elseif($id>=0){
		return $site_options[$id];
	}
}
function cekusername($username, $key = NULL) {
	$db_data = db_query("SELECT uid,name FROM users WHERE name = '$username'");
	while($row = db_fetch_array($db_data)) {
		$hasil = $row['name'];	
	}
	if($key){
		db_set_active('pep');
		$db_data = db_query("SELECT * FROM atk_user WHERE username = '$username'");
		while($row = db_fetch_array($db_data)) {
			$hasil 	= 'error'; // ada data yg sama
		}
		db_set_active();		
	}
	return $hasil;
}
function atk_user_set() {
	$form['#attributes'] = array('enctype' => 'multipart/form-data');
	$user_lokasi		= cek_user_lokasi();
	$data_roles			= atk_user_roles();
	$user_roles			= $data_roles['admin'];
	
	if($user_roles){
		$lokasi 		= cek_lokasi_atk($user_lokasi);
		$site_options[$user_lokasi] = $lokasi;
		$akses			= array(170 => t('User (Wajib Mengisikan Atasan)'), 171 => t('SCM Approval'), 172 => t('Atasan Approval'), 173 => t('Gudang'));
	}else{
		$site_options 	= cek_lokasi_atk();
		$akses			= array(170 => t('User (Wajib Mengisikan Atasan)'), 171 => t('SCM Approval'), 172 => t('Atasan Approval'), 173 => t('Gudang'), 169 => t('Super Admin'), 174 => t('Admin'));
	}
	
	if(isset($_GET['id'])){
		$id				= $_GET['id'];
		$data 			= atk_user_data($id);
		$username 		= $data['username'];
		$fullname 		= $data['fullname'];
		$lokasi 		= $data['lokasi'];
		$aksesx	 		= $data['akses'];
		$atasan1 		= $data['atasan1'];
		$atasan2 		= $data['atasan2'];
		$atasan3 		= $data['atasan3'];
		$dis			= 'disabled';		
		$form['edit_id'] = array(
				'#type' => 'hidden',
				'#default_value' => $id
		);
	}
	$form['username'] = array(
		  	'#title' => t('Username'),
		    '#type' => 'textfield',	    
		    '#size' => 70,	
		    '#maxlength' => 60, 
		    '#weight' => 0, 
		    '#autocomplete_path' => 'atk/online/user/name' ,
		    '#default_value' =>  $username,
		    '#required' => TRUE,	
			'#disabled' => $dis
	);
	$form['fullname'] = array(
		  	'#title' => t('Full Name'),
		    '#type' => 'textfield',	    
		    '#size' => 30,	
		    '#maxlength' => 100, 
		    '#weight' => 1, 
		    '#default_value' =>  $fullname,
		    '#required' => False,
			'#disabled' => 'disabled'
	);	  
	$form['lokasi'] = array(
			'#title' => t('Lokasi'),
			'#type' => 'select',
			'#options' => $site_options,
			'#default_value' =>  variable_get('lokasi', $lokasi),
			'#weight' => 2,
			'#required' => TRUE,
	);
	$form['akses'] = array(
			'#type' => 'checkboxes',
			'#title' => t('Hak Akses'),
			'#weight' => 3,
			'#default_value' => variable_get(1,$aksesx),
			'#options' => $akses
	);
	$form['atasan1'] = array(
		  	'#title' => t('Atasan 1'),
		    '#type' => 'textfield',	    
		    '#size' => 70,	
		    '#maxlength' => 150, 
		    '#weight' => 4,
		    '#autocomplete_path' => 'atk/online/user/name' ,
		    '#default_value' =>  $atasan1,
		    '#required' => FALSE,	  
	);	
	$form['atasan2'] = array(
		  	'#title' => t('Atasan 2'),
		    '#type' => 'textfield',	    
		    '#size' => 70,	
		    '#maxlength' => 150, 
		    '#weight' => 5,
		    '#autocomplete_path' => 'atk/online/user/name' ,
		    '#default_value' =>  $atasan2,
		    '#required' => FALSE,	  
	);	
	$form['atasan3'] = array(
		  	'#title' => t('Atasan 3'),
		    '#type' => 'textfield',	    
		    '#size' => 70,	
		    '#maxlength' => 150, 
		    '#weight' => 6,
		    '#autocomplete_path' => 'atk/online/user/name' ,
		    '#default_value' =>  $atasan3,
		    '#required' => FALSE,	  
	);	
	$form['submit'] = array(
			'#type' => 'submit',
			'#value' => 'Save',
			'#weight' => 98,
	);
	$form['atk_stock_markup'] = array(
			'#value' => t('<a href="' .base_path(). 'atk/online/user/view"><input type="button" value="Cancel" /></a>'),
			'#weight' => 99,
	);
	return $form;
}
function cek_fullname($username){
	db_set_active('pep');
	$db_data = db_query("SELECT * FROM top_person WHERE name = '$username'");
	while($row = db_fetch_array($db_data)) {
		$person_id 	= $row['person_id'];
	}
	db_set_active();
	db_set_active('pep');
	$db_data = db_query("SELECT * FROM org_employee WHERE person_id = $person_id");
	while($row = db_fetch_array($db_data)) {
		$fullname 	= $row['fullname'];
	}
	db_set_active();
	return $fullname;
}
function atk_user_set_submit($form, &$form_state) {
	$edit_id 	= $form_state['edit_id'];
	$username	= $form_state['username'];
	$cekusername=cekusername($username);
	if(!$cekusername){		
		drupal_set_message("Gagal simpan, username TIDAK TERDAFTAR !",'error');
		drupal_goto('atk/online/user/set');
	}
	$cekusername1=cekusername($username,1);
	if(($cekusername1=='error') && (!$edit_id)){		
		drupal_set_message("Gagal simpan, username SUDAH ADA !",'error');
		drupal_goto('atk/online/user/set');
	}
	
	$fullname	= cek_fullname($username);
	$lokasi		= $form_state['lokasi'];
	$akses		= $form_state['akses'];			// array()
	
	$ceknopek	= get_nopek_org($username);
	$cekrole	= $akses[170];
	$parent		= $form_state['atasan1'];
	if($ceknopek && $cekrole && !$parent){		
		drupal_set_message("Untuk User Pekerja HARUS MEMILIKI ATASAN !",'error');
		if($edit_id){
			drupal_goto('atk/online/user/set/', 'id=' . $username);
		}else{
			drupal_goto('atk/online/user/set');
		}
	}
	
	foreach ($akses as $key => $value){
		if($value==0){
			$hapus[] = $key;
		}else{
			$adduserrole = atk_role_save($username,$value);
		}
		switch ($key){
			case 169:
				$akses5 = $value;
				break;	
			case 170:
				$akses1 = $value;
				break;	
			case 171:
				$akses2 = $value;
				break;	
			case 172:
				$akses3 = $value;
				break;	
			case 173:
				$akses4 = $value;
				break;	
			case 174:
				$akses6 = $value;
				break;			
		}
	}
	if(isset($hapus)){
		foreach ($hapus as $key => $rid){
			if($edit_id){
				$deleteusersrole = atk_role_delete($username,$rid);
			}
		}
	}
	$atasan1	= $form_state['atasan1'];
	$atasan2	= $form_state['atasan2'];
	$atasan3	= $form_state['atasan3'];
	
	$data_roles			= atk_user_roles();	
	$user_roles			= $data_roles['admin'];	
	
	if($edit_id){
		
		if($user_roles){
			db_set_active('pep');
			$hasil_update = db_query("UPDATE atk_user SET
					fullname	= '$fullname',
					lokasi		= $lokasi,
					akses1		= $akses1,
					akses2		= $akses2,				
					akses3		= $akses3,			
					akses4		= $akses4,
					atasan1		= '$atasan1',
					atasan2		= '$atasan2',
					atasan3		= '$atasan3'
					WHERE username = '$edit_id'"); // akses3		= $akses3, => 172 : ----
		}else{
			db_set_active('pep');
			$hasil_update = db_query("UPDATE atk_user SET
					fullname	= '$fullname',
					lokasi		= $lokasi,
					akses1		= $akses1,
					akses2		= $akses2,
					akses3		= $akses3,
					akses4		= $akses4,
					akses5		= $akses5,
					akses6		= $akses6,
					atasan1		= '$atasan1',
					atasan2		= '$atasan2',
					atasan3		= '$atasan3'
					WHERE username = '$edit_id'"); // akses3		= $akses3, => 172 : ----
		}
		
		if($hasil_update){
			drupal_set_message('user UPDATE Success ...');
		}else{
			drupal_set_message('SAVE EDIT user FAILED !', 'error');
		}
		$ket['fullname'] = $fullname;
		$page = 'Edit User';
	}else{
		db_set_active('pep');
		$hasil = db_query("INSERT INTO atk_user (username,fullname,lokasi,akses1,akses2,akses3,akses4,akses5,akses6,atasan1,atasan2,atasan3) VALUES ('%s','%s',%d,%d,%d,%d,%d,%d,%d,'%s','%s','%s')", $username,$fullname,$lokasi,$akses1,$akses2,$akses3,$akses4,$akses5,$akses6,$atasan1,$atasan2,$atasan3);
		if($hasil){
			drupal_set_message('user SAVE Success ...');
		}else{
			drupal_set_message('SAVE user FAILED !', 'error');
		}
		$ket['username'] = $username;
		$page = 'Insert User';
	}
	db_set_active();
	$loks = atk_plant_data($lokasi); $loksKet = $loks['description'];
	$ket['lokasi'] = $loksKet; $ket['akses1'] = $akses1; $ket['akses2'] = $akses2; $ket['akses3'] = $akses3;
	$ket['akses4'] = $akses4; $ket['akses5'] = $akses5; $ket['akses6'] = $akses6; $ket['atasan1'] = $atasan1; $ket['atasan2'] = $atasan2; $ket['atasan3'] = $atasan3;
	$hasil = atk_logs($page,$ket);
	drupal_goto('atk/online/user/view');
}
function atk_usernametouid($username){
	db_set_active('default');
	$db_data = db_query("SELECT * FROM users WHERE name = '$username'");
	while($row = db_fetch_array($db_data)) {
		$uid 	= $row['uid'];
	}
	db_set_active();
	return $uid;
}
function atk_user_delete() {
	$id 		= $_GET['id'];
	db_set_active('pep');
	$db_query 	= "DELETE FROM atk_user WHERE username = '$id'";
	$result 	= db_query($db_query);
	if($result){
		drupal_set_message('Data User DELETE Success ...','error');
	}
	db_set_active();
	$page 		= 'Delete User';
	$hasil = atk_logs($page,$id);
	drupal_goto('atk/online/user/view');
}
function atk_role_delete($username,$rid) {
	$uid	= atk_usernametouid($username);	
	db_set_active('default');
	$db_query 	= "DELETE FROM users_roles WHERE uid = $uid && rid = $rid";	
	$result 	= db_query($db_query); // mysql_query($db_query)
	if($result){
// 		drupal_set_message('Data User Role DELETE Success ...','error');
	}	
	db_set_active();
}
function atk_cekadauidrid($uid,$rid){
	db_set_active('default');
	$db_data 		= db_query("SELECT * FROM users_roles WHERE uid = $uid && rid = $rid");
	while($row = db_fetch_array($db_data)) {
		$uidx 		= $row['uid'];
	}
	db_set_active();
	return $uidx;
}
function atk_role_save($username,$rid) {
	$uid	= atk_usernametouid($username);
	$cekada	= atk_cekadauidrid($uid,$rid);	
	if(!$cekada){
		db_set_active('default');
		$hasil 		= db_query("INSERT INTO users_roles (uid,rid) VALUES (%d,%d)", $uid,$rid);
		if($hasil){
// 			drupal_set_message('user role SAVE Success ...');
		}else{
// 			drupal_set_message('SAVE user role FAILED !', 'error');
		}
		db_set_active();
	}
}
// END USER ========================================================================