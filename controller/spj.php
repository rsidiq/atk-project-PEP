<?php 
// SPJ ============================================================================
function atk_spj() {  
	$tampilkan 	= atk_stock_kritis();
	$form['nopek'] = array(
		  	'#title' => t('No Pekerja'),
		    '#type' => 'textfield',	    
		    '#size' => 20,	
		    '#maxlength' => 10, 
		    '#default_value' =>  $nopek,
		    '#weight' => 0,
		    '#required' => TRUE, 	
	);	
	$form['submit'] = array(
			'#type' => 'submit',
			'#value' => 'Load Pekerja',
			'#weight' => 99,
	);
	return $form;
}
function cek_usernameterdaftar_atk($username) {
	db_set_active('pep');
	$db_data = db_query("SELECT * FROM atk_user WHERE username = '$username'");
	while($row = db_fetch_array($db_data)) {
		$hasil 	= $row['username'];
	};
	db_set_active();
	return $hasil;
}
function cek_pensiun_atk($username) {
	$uid 		= atk_usernametouid($username);
	db_set_active('pep');
	$db_data 	= db_query("SELECT * FROM org_employee WHERE person_id = $uid && status != 3");
	while($row 	= db_fetch_array($db_data)) {
		$hasil 	= $row['fullname']; // employee_no
	};
	db_set_active();
	return $hasil;
}
function atk_spj_validate($form, $form_state) {
	$nopek 			= $form_state['nopek'];
	$hasil 			= cek_nopek_atk($nopek);	
	$username		= get_username_org($hasil['nopek']);
	$cek_terdaftar	= cek_usernameterdaftar_atk($username);
	$cek_pensiun	= cek_pensiun_atk($username);
	if((!$hasil['nopek']) || (!$cek_pensiun)){
		drupal_set_message("NoPek tidak terdaftar / Pensiun",'error');
		drupal_goto('atk/online/spj');
	}
}
function atk_spj_submit($form, &$form_state) {
	$nopek 			= $form_state['nopek'];
	drupal_goto('atk/online/spj/view', 'nopek=' .$nopek);
}
function atk_spj_view() {
	$nopek 			= $_GET['nopek'];	
	$data 			= cek_nopek_atk($nopek);
	
	$hasil = "Nomor Pekerja	: " .$data['nopek']. "<br>";
	$hasil .= "Nama Pekerja	: " .$data['nama']. "<br>";
	$hasil .= "Jabatan		: " .$data['jabatan']. "<br>";
	$hasil .= "Fungsi		: " .$data['fungsi']. "<br><br>";
	
	$hasil .= '<a href="' .base_path(). 'atk/online/set/?nopek=' .$data['nopek']. '"><input type="button" value="Set Pekerja" onclick="window.location.href=\'' .base_path(). 'atk/online/set/?nopek=' .$data['nopek']. '\';" /></a>  ';
	$hasil .= '<a href="' .base_path(). 'atk/online/spj"><input type="button" value="Back" onclick="window.location.href=\'' .base_path(). 'atk/online/spj \';" /></a>';
	return $hasil;
}

function atk_set_to_ses_pekerja() {
	// simpan ke $_SESSION
	$nopek 						= $_GET['nopek'];
	$_SESSION['reservation'] 	= $nopek;
	drupal_goto('atk/online/reservation', 'nopek=' .$nopek);
}
// END SPJ ========================================================================