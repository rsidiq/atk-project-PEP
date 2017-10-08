<?php 
// GR ============================================================================
function atk_upload_gr(){
	$set = arg(5);
	$txtJudul 	= 'Upload Stock Data';
	drupal_add_js("$(document).ready(function() { $('div#app-title h2').text('" .$txtJudul."'); })", "inline");
	if(isset($set)){
		$hasil = update_disabled_users();
		foreach ($hasil as $uid){
			db_set_active('default');
			$hasil_update = db_query("UPDATE users SET status = 0 WHERE uid = $uid");
			var_dump($uid);
			db_set_active();
		}
		drupal_goto('atk/online/master/upload/gr');
	}
	$lokasi = base_path().file_directory_path()."/atk/gr/gr_tpl.xlsx";
	$form['#attributes'] = array('enctype' => 'multipart/form-data');
	$form['gr'] = array(
			'#type' => 'fieldset',
			'#title' => t('Upload GR'),
			'#weight' => 1,
			'#collapsible' => FALSE,
			'#collapsed' => TRUE,
	);
	$form['gr']['gr_file'] = array(
			'#type' => 'file',
			'#title' => '<a href="' .$lokasi. '">Download Template GR</a><p>'.'Select your file',
			'#size' => 30,
	);
	$form['gr']['submit'] = array(
			'#type' => 'submit',
			'#value' => 'Import',
	);
	return $form;
}
function update_disabled_users(){
	db_set_active('default');
	$db_data = db_query("SELECT * FROM _users_delete");
	while($row = db_fetch_array($db_data)) {
		$hasil[] 	= $row['uid'];
	}
	db_set_active();
	return $hasil;
}
function atk_upload_gr_submit($form, &$form_state) {
	$check_file = file_check_upload('gr_file');
	$mime1 = 'application/vnd.ms-excel';
	$mime2 = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
	$isimime = $check_file->filemime;
	if($isimime == $mime2) {
		$isi_sblm 		= file_directory_path().DIRECTORY_SEPARATOR."atk/gr" .DIRECTORY_SEPARATOR;
		if ($isimime == $mime1) {
			$nama_file 	= 'dataExcel.xls';			
		}else{
			$nama_file 	= 'dataExcel.xlsx';
		}
		if($isi_sblm . $nama_file){
			unlink($isi_sblm . $nama_file);
		}
		
		//Save File
		$path 		= file_directory_path().DIRECTORY_SEPARATOR."atk/gr" .DIRECTORY_SEPARATOR. $nama_file;
		$file 		= file_save_upload('gr_file',$path);
		// END Simpan File
		if ($file === 0) {
			drupal_set_message("Gagal simpan file !",'error');
		}else{
			$hasil = gr_from_excel_simpan($nama_file); // <= simpan ke database
		}
		drupal_goto('atk/online/master/upload/gr');
	}
	drupal_set_message("Gagal simpan, Only Type : *.xlsx ",'error');
	return FALSE;
}
function gr_from_excel_simpan($nama_file) {
	$lokasi 		= file_directory_path()."/atk/gr/" .$nama_file;
	$objReader 		= PHPExcel_IOFactory::createReader('Excel2007');
	$objPHPExcel 	= $objReader->load($lokasi);
	$objWorksheet 	= $objPHPExcel->setActiveSheetIndex(0);
	$totalRow 		= $objWorksheet->getHighestRow();
	$sheetCount 	= $objPHPExcel->getSheetCount();
	// 	$sheetNames = $objPHPExcel->getSheetNames();

	// Cek AA1
	$cek_template	= trim($objPHPExcel->setActiveSheetIndex(0)->getCell('AA1')->getValue());
	// Cek ke-Asli-an Template : AA1 => 'ASLI'
	if($cek_template != 'ASLI'){
		drupal_set_message("Format Data TIDAK SAMA !<br>Gunakan template yg disediakan.",'error');
		drupal_goto('atk/online/master/upload/gr');
	}
	
	$gagal 	= 0;
	for($x=2;$x<=$totalRow;$x++){
		$kimaps						= $objPHPExcel->setActiveSheetIndex(0)->getCell('B'.$x)->getValue();
		if($kimaps){
			$data[$kimaps]['kimaps']	= trim($kimaps); 																		// atk_material -> materialCode
			$data[$kimaps]['plant']		= trim($objPHPExcel->setActiveSheetIndex(0)->getCell('C'.$x)->getCalculatedValue()); 	// atk_plant
			$data[$kimaps]['wh']		= trim($objPHPExcel->setActiveSheetIndex(0)->getCell('D'.$x)->getValue()); 				// atk_warehouse
			$data[$kimaps]['qty']		= $objPHPExcel->setActiveSheetIndex(0)->getCell('E'.$x)->getValue(); 					// atk_stock (idStock,idMaterial,idWarehouse,qty,rsvQty)
			$data[$kimaps]['harga']		= $objPHPExcel->setActiveSheetIndex(0)->getCell('F'.$x)->getValue(); 					// price : atk_material$data[$kimaps]['desc']		= $objPHPExcel->setActiveSheetIndex(0)->getCell('H'.$x)->getValue();				
			
			// ====================== CEK : jika ada Simpan ; tidak ada, batalkan semua ===================================================
			$cek_kimap			= get_material_code($kimaps);
			if(!$cek_kimap){ // jika TDK ada, -> simpan ke array
				$gagal 			= 1;
				$kimap_gagal[]	= $kimaps;
			}
			// ====================== END CEK : jika ada Simpan ; tidak ada, batalkan semua ================================================
		}
	}
	
	// ====================== CEK : jika ada Simpan ; tidak ada, batalkan semua ===================================================
	if($gagal == 0){
		// loop array data yg ada
		foreach ($data as $key => $value){
			$plant		= $value['plant'];
			$wh			= $value['wh'];
			$qty		= $value['qty'];
			$harga		= $value['harga'];
			
			// cek stok ADA/BLM : $idMaterial && $idWarehouse			
			$planttoid 	= planttoid($plant);
			$waretoid 	= waretoid($wh, $planttoid); // $warehouse, $idPlant  // description idPlant			
			$data_mat	= get_material_kimap($key); // $key => KIMAPS
			$mat_id		= $data_mat['id'];
			$cek_stok 	= cek_stok($mat_id, $waretoid); // $mattoid,$idWarehouse
			$stok_id	= $cek_stok['idStock'];
			$tmbhQty	= $qty + $cek_stok['qty'];
			if($harga > 0){
				$harga		= $value['harga'];
			}else{
				$harga		= $cek_stok['price'];
			}
			
			if(isset($cek_stok)){
				db_set_active('pep');
				$hasil_update = db_query("UPDATE atk_stock SET
						qty				= $tmbhQty,
						price			= $harga
						WHERE idStock 	= $stok_id");
				db_set_active();				
			}else{
				// cek keberadaan GUDANG / WH 
				// simpan_warehouse($warehouse,$idPlant)
				$idWH = cek_wh($wh);
				if(!$idWH){
					$simpan = simpan_warehouse($wh,$planttoid);
				}
				$idWH = cek_wh($wh);				
				// idMaterial	idWarehouse	qty price : <<= harus disimpen
				db_set_active('pep');
				$hasil = db_query("INSERT INTO atk_stock (idMaterial,idWarehouse,qty,price) VALUES (%d,%d,%d,%d)", $mat_id,$idWH,$qty,$harga);
				db_set_active();
			}
		}
		// END - loop array data yg ada
		
		drupal_set_message("File Save to Database, Complete !");
		drupal_goto('atk/online/master/upload/gr');
	}else{
		// tampilkan no KIMAPS yg tdk terdaftar (loop array)
		$hasil = "<p><strong>LIST KIMAPS yg TDK TERDAFTAR :</strong></p>";
		$hasil .= "<p><a href='" .base_path(). "atk/online/master/upload/gr'>[back]</a></p>";
		
		foreach ($kimap_gagal as $key => $value){
			$hasil .= ++$xyz .'. '. $value . '<br>';
		}
		$hasil .= '<script>alert("Ada kimaps yg belum TERDAFTAR");</script>';
		drupal_set_message("Ada KIMAPS yg TDK TERDAFTAR, Proses Simpan GAGAL !","error");
		return $hasil;
	}	
	// ====================== END CEK : jika ada Simpan ; tidak ada, batalkan semua ================================================
}

function cek_wh($warehouse) {
	// ambil data
	db_set_active('pep');
	$db_data = db_query("SELECT * FROM atk_warehouse WHERE description = '$warehouse'");
	while($row = db_fetch_array($db_data)) {
		$hasil 	= $row['idWarehouse'];
	}
	db_set_active();
	// End ambil data
	return $hasil;
}

function get_material_code($materialCode) {
	// ambil data
	db_set_active('pep');
	$db_data = db_query("SELECT * FROM atk_material WHERE materialCode = '$materialCode'");
	while($row = db_fetch_array($db_data)) {
		$hasil 	= $row['materialCode'];
	}
	db_set_active();
	// End ambil data
	return $hasil;
}

// ========================================================== END REVISI ======================================================

function cek_stok($mattoid,$idWarehouse){// $idMaterial,$idWarehouse
	// 1. cek idMaterial && idWarehouse ada ? 
	// 		jika ya 	=> update
	// 		jika tdk 	=> simpan
	db_set_active('pep');
	$db_data = db_query("SELECT * FROM atk_stock WHERE idMaterial = $mattoid && idWarehouse = $idWarehouse");
	while($row = db_fetch_array($db_data)) {
		$hasil['idStock'] 	= $row['idStock'];
		$hasil['qty'] 		= $row['qty'];
		$hasil['rsvQty'] 	= $row['rsvQty'];
		$hasil['price'] 	= $row['price'];
	}
	db_set_active();
	return $hasil;
}

function simpan_warehouse($warehouse,$idPlant){
	if($idPlant > 0){
		db_set_active('pep');
		$hasil = db_query("INSERT INTO atk_warehouse (idPlant,description,isActive) VALUES (%d,'%s',%d)", $idPlant,$warehouse,1);
		db_set_active();
			
// 		drupal_set_message("Add Warehouse, Done !");
		$page = 'Insert atk_category using Excel';
		$ket['Description'] = $cat;
		$hasil = atk_logs($page,$ket);
	}
}

function waretoid($warehouse,$idPlant){ // idPlant	description
	db_set_active('pep');
	$db_data = db_query("SELECT * FROM atk_warehouse WHERE idPlant = $idPlant && description = '$warehouse'");
	while($row = db_fetch_array($db_data)) {
		$hasil 	= $row['idWarehouse'];
	}
	db_set_active();
	return $hasil;
}

function planttoid($plant){
	db_set_active('pep');
	$db_data = db_query("SELECT * FROM atk_plant WHERE description = '$plant'");
	while($row = db_fetch_array($db_data)) {
		$hasil 	= $row['idPlant'];
	}
	db_set_active();
	return $hasil;
}

function get_material_data($description) {
	// ambil data
	db_set_active('pep');
	$db_data = db_query("SELECT * FROM atk_material WHERE description = '$description'");
	while($row = db_fetch_array($db_data)) {
		$hasil['materialCode'] 	= $row['materialCode'];
		$hasil['id'] 			= $row['id'];
		$hasil['category']		= $row['category'];
	}
	db_set_active();
	// End ambil data
	return $hasil;
}

function get_material_kimap($kimap) {
	// ambil data
	db_set_active('pep');
	$db_data = db_query("SELECT * FROM atk_material WHERE materialCode = '$kimap'");
	while($row = db_fetch_array($db_data)) {
		$hasil['description'] 	= $row['description'];
		$hasil['id'] 			= $row['id'];
		$hasil['category']		= $row['category'];
		$hasil['materialCode']		= $row['materialCode'];
	}
	db_set_active();
	// End ambil data
	return $hasil;
}

function atk_cat_id($cat) {
	$hasil = 0;
	db_set_active('pep');
	$db_data = db_query("SELECT * FROM atk_category WHERE description = '$cat'");
	while($row = db_fetch_array($db_data)) {
		$hasil 	= $row['idCategory'];
	}
	db_set_active();	
	return $hasil;
}
// END GR ========================================================================