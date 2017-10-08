<?php 
// MATERIAL ============================================================================
function atk_material_view_data() {
	$cat = array(1=>'Active', 2=>'Disabled');
	$judul = array(array('data'=>t('No.'),),
			array('data'=>t('KIMAP'),),
			array('data'=>t('Material'),),
			array('data'=>t('Min'),),
			array('data'=>t('Satuan'),),
			array('data'=>t('Kategori'),),
			array('data'=>t('Image'),),
			array('data'=>t('Status'),),
			array('data'=>t('Edit'),),
			array('data'=>t('Delete'),)
	);
	
	db_set_active('pep');
	$db_data = db_query("SELECT * FROM atk_material ORDER BY description ASC");
	while($row = db_fetch_array($db_data)) {
		$cek_stok_qty 	= cek_stok_qty($row['id']);
		$edit 			= "<a href='" .base_path(). "atk/online/material/set/?id=" .$row['id']. "'>edit</a>";
		if(!$cek_stok_qty){
			$delete		= "<a href='" .base_path(). "atk/online/material/delete/?id=" .$row['id']. "' onclick='if(confirm(\"are you sure ?\") != true){ return false }'>hapus</a>";
		}else{
			$delete		= "<a href='#' title='masih ada stok' onclick='alert(\"TIDAK dpt dihapus, STOCK msh ada !\")'>hapus</a>";
		}
		if($row['fileName']){
			$view_image	= "<a class='fancybox-effects-d' href='" .base_path(). "files/portal/atk/" .$row['fileName']. "' title='" .$row['description']. "'>view</a>";
		}else{
			$view_image	= "no image";
		}		
		$isi[] 	= array(++$xyz, $row['materialCode'], $row['description'], $row['min'], $row['satuan'], atk_category_name($row['category']), $view_image, $cat[$row['isActive']], $edit, $delete);
	}
	db_set_active();

	$hasil['judul'] = $judul;
	$hasil['isi'] 	= $isi;
	return $hasil;
}
function cek_stok_qty($idMat) {
	db_set_active('pep');
	$db_data = db_query("SELECT * FROM atk_stock WHERE idMaterial = $idMat");
	while($row = db_fetch_array($db_data)) {
		$hasil = $row['qty'];
	}
	db_set_active();
	return $hasil;
}
function atk_material_view() {
	drupal_add_js(drupal_get_path('module', 'atk') . "/js/jquery-1.8.2.min.js");
	drupal_add_js(drupal_get_path('module', 'atk') . "/js/jquery.fancybox.js");
	drupal_add_css(drupal_get_path('module', 'atk').'/css/jquery.fancybox.css');
	$jss = "<script type='text/javascript'>
		$(document).ready(function() {
			$('.fancybox-effects-d').fancybox({
				padding: 0,
				openEffect : 'elastic',
				openSpeed  : 150,
				closeEffect : 'elastic',
				closeSpeed  : 150,
				closeClick : true,
				helpers : {
				overlay : null
				}
			});
		});
	</script>";	
	$data = atk_material_view_data();
	$output = theme_table($data['judul'], $data['isi']);
	$hasil = "<a href='" .base_path(). "atk/online/material/set'>Add Material</a> | <a href='" .base_path(). "atk/online/master'>[back]</a><br><br>";
	return $jss.$hasil.$output;
}
function atk_material_data($id) {
	// ambil data
	db_set_active('pep');
	$db_data = db_query("SELECT * FROM atk_material WHERE id = $id");
	while($row = db_fetch_array($db_data)) {
		$hasil['materialCode'] 	= $row['materialCode'];
		$hasil['description'] 	= $row['description'];
		$hasil['satuan'] 	= $row['satuan'];
		$hasil['min'] 		= $row['min'];
		$hasil['max'] 		= $row['max'];
		$hasil['isActive']	= $row['isActive'];
		$hasil['category']	= $row['category'];
		$hasil['fileName']	= $row['fileName'];
	}
	db_set_active();
	// End ambil data
	return $hasil;
}
function atk_category_data() {
	// ambil data
	db_set_active('pep');
	$db_data = db_query("SELECT idCategory, description FROM atk_category WHERE isActive = 1 ORDER BY description ASC");
	while($row = db_fetch_array($db_data)) {
		$hasil[$row['idCategory']] = $row['description'];
	}
	db_set_active();
	// End ambil data
	return $hasil;
}
function atk_material_set() {
	$status_pil 	= array(1=>'Active', 2=>'Disabled');
	$form['#attributes'] = array('enctype' => 'multipart/form-data');
	if(isset($_GET['id'])){
		$id 		= $_GET['id'];
		$data 		= atk_material_data($id);
		$materialcode 	= $data['materialCode'];
		$description 	= $data['description'];
		$satuan 		= $data['satuan'];
		$min 			= $data['min'];
		$max 			= $data['max'];
		$status 		= $data['isActive'];
		$category 		= $data['category'];
		$fileName		= $data['fileName'];
		$form['edit_id'] = array(
				'#type' => 'hidden',
				'#default_value' => $id
		);
	}
	$cek_stok_qty 	= cek_stok_qty($id);
	if($cek_stok_qty){
		$disable = 'disabled';
	}
	$form['materialcode'] = array(
			'#type' => 'textfield',
			'#title' => t('KIMAP'),
			'#size' => 20,
			'#maxlength' => 10,
			'#weight' => 1,
			'#default_value' =>  $materialcode,
			'#required' => TRUE,
	);
	$form['description'] = array(
			'#type' => 'textfield',
			'#title' => t('Description'),
			'#size' => 40,
			'#maxlength' => 240,
			'#weight' => 2,
			'#default_value' =>  $description,
			'#required' => TRUE,
	);
	$form['satuan'] = array(
			'#type' => 'textfield',
			'#title' => t('Satuan'),
			'#size' => 20,
			'#maxlength' => 10,
			'#weight' => 3,
			'#default_value' =>  $satuan,
			'#required' => TRUE,
	);
	$form['min'] = array(
			'#type' => 'textfield',
			'#title' => t('Minimum'),
			'#size' => 5,
			'#maxlength' => 3,
			'#weight' => 4,
			'#default_value' =>  $min,
			'#required' => TRUE,
	);
	$form['status'] = array(
			'#title' => t('Status'),
			'#type' => 'select',
			'#options' => $status_pil,
			'#default_value' =>  variable_get('status', $status),
			'#disabled'	=> $disable,
			'#weight' => 6,
			'#required' => TRUE,
	);
	$form['category'] = array(
			'#title' => t('Category'),
			'#type' => 'select',
			'#options' => atk_category_data(),
			'#default_value' =>  variable_get('category', $category),
			'#weight' => 7,
			'#required' => TRUE,
	);	  
	if(!$fileName){
		$form['file_image'] = array(
				'#title' => 'File Image',
				'#type' => 'file',
				'#weight' => 8,
				'#required' => FALSE,
		);
	}else{
		$form['file_image'] = array(
				'#type' => 'hidden',
				'#default_value' => $fileName
		);
	}
	$form['submit'] = array(
			'#type' => 'submit',
			'#value' => 'Save',
			'#weight' => 98,
	);
	$form['atk_category_markup'] = array(
			'#value' => t('<a href="' .base_path(). 'atk/online/material/view"><input type="button" value="Cancel" /></a>'),
			'#weight' => 99,
	);
	return $form;
}
function atk_material_set_submit($form, &$form_state) {
	$edit_id 	= $form_state['edit_id'];
	$materialcode 	= $form_state['materialcode'];
	$description 	= $form_state['description'];
	$satuan 	= $form_state['satuan'];
	$min 		= $form_state['min'];
	$status 	= $form_state['status'];
	$category 	= $form_state['category'];
	$fileName	= $form_state['file_image'];

	// cek apakah KIMAP ada yg sama. => skip penyimpanan
	$cek_material	= get_material_kimap($materialcode);
	if(($cek_material) && (!isset($edit_id))){
		drupal_set_message('Penambahan Material GAGAL. KIMAP sudah digunakan', 'error');
		drupal_goto('atk/online/material/view');
	}
	
	// Simpan File Image	
	$check_file = file_check_upload('file_image');
	$nm_ext = explode('.',$check_file->filename);
	$nm_ext = max($nm_ext);
	$nama_file	= '_' .$materialcode. '_' .$category. '_.' .$nm_ext;
	// cek nama file yg sama
	$lokasi = 'files/portal/atk/'.$nama_file;
	if(is_file($lokasi)){
		$nama_file	= '_' .$materialcode. '_' .$category. '_' .date('His'). '_.' .$nm_ext;
	}
	// End cek nama file yg sama
	if($check_file) {
		$file = file_save_upload($check_file, 'files/portal/atk/'.$nama_file);
		if ($file === 0) {
			drupal_set_message("Gagal simpan file: di dir > ".'files/portal/atk/'.$nama_file,'error');
		}
	}
	if($fileName){
		$nama_file = $fileName;
	}
	// End Simpan File Image
	
	db_set_active('pep');
	if($edit_id){
		$hasil_update = db_query("UPDATE atk_material SET
				materialcode 	= '$materialcode',
				description 	= '$description',
				satuan 			= '$satuan',
				min 			= $min,
				isActive		= $status,
				category		= $category,
				fileName		= '$nama_file'
					WHERE id = $edit_id");
		if($hasil_update){
			drupal_set_message('Material UPDATE Success ...');
		}else{
			drupal_set_message('SAVE EDIT Material FAILED !', 'error');
		}
		$ket['idMaterial'] = $edit_id;
		$page = 'Edit Material';
	}else{
		$hasil = db_query("INSERT INTO atk_material (materialcode,description,satuan,min,isActive,category,fileName) 
				VALUES ('%s','%s','%s',%d,%d,%d,'%s')", 
				$materialcode,$description,$satuan,$min,$status,$category,$nama_file);
		if($hasil){
			drupal_set_message('Material SAVE Success ...');
		}else{
			drupal_set_message('SAVE Material FAILED !', 'error');
		}
		$page = 'Insert Material';
	}
	db_set_active();
	$ket['materialcode'] = $materialcode ; $ket['description'] = $description; $ket['satuan'] = $satuan;
	$ket['min'] = $min ; $ket['max'] = $max; $ket['isActive'] = $status; $ket['category'] = $category; $ket['fileName'] = $nama_file;
	$hasil = atk_logs($page,$ket);
	drupal_goto('atk/online/material/view');
}
function atk_material_delete() {
	$id 		= $_GET['id'];	
	// cek data + hapus file
	db_set_active('pep');
	$db_data = db_query("SELECT * FROM atk_material WHERE id = $id");
	while($row = db_fetch_array($db_data)) {
		$filename = $row['fileName'];
	}
	$cek_file = 'files/portal/atk/'.$filename;
	if(is_file($cek_file)){
		unlink($cek_file);		
	}
	db_set_active();
	// End cek data + hapus file
	
	db_set_active('pep');	
	$db_query 	= "DELETE FROM atk_material WHERE id = $id";
	$result 	= mysql_query($db_query);
	if($result){
		drupal_set_message('Data Material DELETE Success ...','error');
	}
	db_set_active();
	
	db_set_active('pep');
	$db_query 	= "DELETE FROM atk_stock WHERE idMaterial = $id";
	$result 	= mysql_query($db_query);
	if($result){
		drupal_set_message('Data stock DELETE Success ...','error');
	}
	db_set_active();
	drupal_goto('atk/online/material/view');
}
// END MATERIAL ========================================================================
?>