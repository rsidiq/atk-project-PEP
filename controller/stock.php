<?php 
// STOCK ============================================================================
function atk_stock_view_data() {
// 	$name 		= $GLOBALS['user']->name; 
// 	$usr_data	= atk_user_data($name); 
// 	$lokasi_usr = $usr_data['lokasi'];	
	
	$cat = array(1=>'Active', 2=>'Disabled');
	$judul = array(array('data'=>t('No.'),),
			array('data'=>t('Material'),),
			array('data'=>t('Warehouse'),),
			array('data'=>t('Lokasi'),),
			array('data'=>t('Qty'),),
			array('data'=>t(''),),
			array('data'=>t('Edit'),),
			array('data'=>t('Delete'),)
	);
	db_set_active('pep');
// 	$db_data = db_query("SELECT * FROM atk_stock ORDER BY idWarehouse DESC, qty ASC");
	$db_data = db_query("SELECT a.idStock, a.idMaterial, a.idWarehouse, a.qty, b.idPlant FROM pep.atk_stock AS a
					LEFT JOIN pep.atk_warehouse AS b ON b.idWarehouse = a.idWarehouse ORDER BY b.idPlant ASC,a.idWarehouse,a.qty ASC"); // WHERE b.idPlant=$lokasi_usr
	
	while($row = db_fetch_array($db_data)) {
		$edit 	= "<a href='" .base_path(). "atk/online/stock/set/?id=" .$row['idStock']. "'>edit</a>";		
		if($row['qty'] > 0){
			$delete	= "<a href='#' title='masih ada stok' onclick='alert(\"TIDAK dpt dihapus, STOCK msh ada !\")'>hapus</a>";
		}else{
			$delete	= "<a href='" .base_path(). "atk/online/stock/delete/?id=" .$row['idStock']. "' onclick='if(confirm(\"are you sure ?\") != true){ return false }'>hapus</a>";
		}
		
		$usr	= atk_user_roles(); 	// cek data login
		$un		= $usr['nama'];
		$sa		= $usr['sa']; 			// is Super Admin ??? 	(1)
		$usr_atk= atk_user_data($un); 	// cek data ATK
		$lokasi	= $usr_atk['lokasi'];	// cek lokasi user ATK	(2) -> idPlant(lokasi) -> user login
		$idW	= atk_warehouse_data($row['idWarehouse']);
		$idPlant= $idW['idPlant'];		// idPlant(lokasi) -> item(barang)
		$sat	= atk_material_data($row['idMaterial']);
		$satuan	= $sat['satuan'];
		
		if($sa){						// cek, jika bukan SA, -> tampilkan sebatas wilayah nya saja;	
			$isi[] 	= array(++$xyz, atk_material_data_select($row['idMaterial']), atk_warehouse_data_select($row['idWarehouse']), atk_cek_plant($row['idWarehouse']), $row['qty'], $satuan, $edit, $delete);			
		}elseif($lokasi == $idPlant){
			$isi[] 	= array(++$xyz, atk_material_data_select($row['idMaterial']), atk_warehouse_data_select($row['idWarehouse']), atk_cek_plant($row['idWarehouse']), $row['qty'], $satuan, $edit, $delete);
		}
	}
	db_set_active();

	$hasil['judul'] = $judul;
	$hasil['isi'] 	= $isi;
	return $hasil;
}
function atk_warehouse_data_select($id = null) {
	db_set_active('pep');
	if($id){
		$db_data = db_query("SELECT * FROM atk_warehouse WHERE idWarehouse = $id");
	}else{
		$db_data = db_query("SELECT * FROM atk_warehouse WHERE isActive = 1 ORDER BY description ASC");
	}	
	while($row = db_fetch_array($db_data)) {		
		if($id){
			$hasil = $row['description'];
		}else{
			$hasil[$row['idWarehouse']] = $row['description'] .' - '. atk_cek_plant($row['idWarehouse']);
		}		
	}
	db_set_active();
	return $hasil;
}
function atk_material_data_select($id = null) {
	db_set_active('pep');
	if($id){
		$db_data = db_query("SELECT * FROM atk_material WHERE id = $id");
	}else{
		$db_data = db_query("SELECT * FROM atk_material WHERE isActive = 1 ORDER BY description ASC");
	}	
	while($row = db_fetch_array($db_data)) {
		if($id){
			$hasil = $row['description'];
		}else{
			$hasil[$row['id']] = $row['description'];
		}
	}
	db_set_active();
	return $hasil;
}
function atk_stock_data($id) {
	db_set_active('pep');
	$db_data = db_query("SELECT * FROM atk_stock WHERE idstock = $id");
	while($row = db_fetch_array($db_data)) {
		$hasil['idMaterial'] 	= $row['idMaterial'];
		$hasil['idWarehouse']	= $row['idWarehouse'];
		$hasil['qty']			= $row['qty'];
		$hasil['rsvQty']		= $row['rsvQty'];
	}
	db_set_active();
	return $hasil;
}
function atk_stock_view() {
	$data = atk_stock_view_data();
	$output = theme_table($data['judul'], $data['isi']);
	$hasil = "<a href='" .base_path(). "atk/online/stock/set'>Set Initial Stock</a> | <a href='" .base_path(). "atk/online/data_stok/toexcel'>Data Stock to Excel</a> |  <a href='" .base_path(). "atk/online/master'>[back]</a><br><br>";
	return $hasil.$output;
}
function atk_stock_view_data2() {
	$cat = array(1=>'Active', 2=>'Disabled');
	$judul = array(array('data'=>t('No.'),),
			array('data'=>t('No. Material'),),
			array('data'=>t('Material'),),
			array('data'=>t('Warehouse'),),
			array('data'=>t('Lokasi'),),
			array('data'=>t('Qty'),),
			array('data'=>t(''),)
	);
	db_set_active('pep');
	$db_data = db_query("SELECT a.idStock, a.idMaterial, a.idWarehouse, a.qty, b.idPlant FROM pep.atk_stock AS a
					LEFT JOIN pep.atk_warehouse AS b ON b.idWarehouse = a.idWarehouse ORDER BY b.idPlant ASC,a.idWarehouse,a.qty ASC"); // WHERE b.idPlant=$lokasi_usr

	while($row = db_fetch_array($db_data)) {
		$usr	= atk_user_roles(); 	// cek data login
		$un		= $usr['nama'];
		$sa		= $usr['sa']; 			// is Super Admin ??? 	(1)
		$usr_atk= atk_user_data($un); 	// cek data ATK
		$lokasi	= $usr_atk['lokasi'];	// cek lokasi user ATK	(2) -> idPlant(lokasi) -> user login
		$idW	= atk_warehouse_data($row['idWarehouse']);
		$idPlant= $idW['idPlant'];		// idPlant(lokasi) -> item(barang)
		$sat	= atk_material_data($row['idMaterial']);
		$satuan	= $sat['satuan'];
		$matCode= $sat['materialCode'];

		if($lokasi == $idPlant){
			$isi[] 	= array(++$xyz, $matCode, atk_material_data_select($row['idMaterial']), atk_warehouse_data_select($row['idWarehouse']), atk_cek_plant($row['idWarehouse']), $row['qty'], $satuan);
		}
	}
	db_set_active();

	$hasil['judul'] = $judul;
	$hasil['isi'] 	= $isi;
	return $hasil;
}
function atk_stock_view_toexcel(){		// ============================= 7
	$data 		= atk_stock_view_data2();
	$judul		= $data['judul'];
	$isi		= $data['isi'];

	$workbook = new Spreadsheet_Excel_Writer();
	$workbook->send('data_stock.xls');
	$worksheet =& $workbook->addWorksheet('Data_Stock');
	$worksheet->freezePanes(array(1, 0));
	$format =& $workbook->addFormat(array('Size' => 10,
			'Align' => 'center',
			'Bold' => 1,
			'Color' => 'white',
			'Pattern' => 1,
			'BgColor' => 'white',
			'FgColor' => 'grey'));
	$worksheet->setColumn(0,0,5);
	$worksheet->setColumn(1,1,10);
	$worksheet->setColumn(2,2,50);
	$worksheet->setColumn(3,3,10);
	$worksheet->setColumn(4,4,10);
	$worksheet->setColumn(5,5,10);

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
function atk_warehouse_same_plant($idPlant) {
	db_set_active('pep');
	$db_data = db_query("SELECT * FROM atk_warehouse WHERE idPlant = $idPlant");
	while($row = db_fetch_array($db_data)) {
		$hasil[$row['idWarehouse']] = $row['description'] .' - '. atk_cek_plant($row['idWarehouse']);
	}
	db_set_active();
	return $hasil;
}
function atk_stock_set() {
	$form['#attributes'] = array('enctype' => 'multipart/form-data');
	$user_lokasi		= cek_user_lokasi(); // hasil plant (int)
	$data_roles			= atk_user_roles();
	$user_roles			= $data_roles['admin'];
	$data_plant			= atk_plant_data($user_lokasi);
	$nama_lokasi		= $data_plant['description'];
	
	if($user_roles){
		$wh_option			= atk_warehouse_same_plant($user_lokasi);
	}else{
		$wh_option			= atk_warehouse_data_select();
	}
	
	if(!isset($wh_option)){
		$form['atk_stock1_markup'] = array(
				'#value' => t('Belum ada warehouse utk lokasi '.$nama_lokasi),
				'#weight' => 98,
		);
		$form['atk_stock2_markup'] = array(
				'#value' => t('<p><a href="' .base_path(). 'atk/online/stock/view"><input type="button" value="Cancel" /></a>'),
				'#weight' => 99,
		);
		return $form;
	}
	
	if(isset($_GET['id'])){
		$id				= $_GET['id'];
		$data 			= atk_stock_data($id);
		$idMaterial 	= $data['idMaterial'];
		$idWarehouse 	= $data['idWarehouse'];
		$qty 			= $data['qty'];
		$disabled		= 'disabled';
		
		$form['edit_id'] = array(
				'#type' => 'hidden',
				'#default_value' => $id
		);
	}
	$form['idMaterial'] = array(
			'#title' => t('ID Material'),
			'#type' => 'select',
			'#options' => atk_material_data_select(),
			'#default_value' =>  variable_get('idMaterial', $idMaterial),
			'#weight' => 1,
			'#disabled' => $disabled,
			'#required' => TRUE,
	);
	$form['idWarehouse'] = array(
			'#title' => t('ID Warehouse'),
			'#type' => 'select',
			'#options' => $wh_option,
			'#default_value' =>  variable_get('idWarehouse', $idWarehouse),
			'#weight' => 2,
			'#disabled' => $disabled,
			'#required' => TRUE,
	);
	$form['qty'] = array(
			'#type' => 'textfield',
			'#title' => t('Quantity'),
			'#size' => 10,
			'#maxlength' => 5,
			'#weight' => 3,
			'#default_value' =>  $qty,
			'#required' => TRUE,
	);
	$form['submit'] = array(
			'#type' => 'submit',
			'#value' => 'Save',
			'#weight' => 98,
	);
	$form['atk_stock_markup'] = array(
			'#value' => t('<a href="' .base_path(). 'atk/online/stock/view"><input type="button" value="Cancel" /></a>'),
			'#weight' => 99,
	);
	return $form;
}
function atk_stock_set_submit($form, &$form_state) {
	$edit_id 	= $form_state['edit_id'];
	$idMaterial	= $form_state['idMaterial'];
	$idWarehouse= $form_state['idWarehouse'];
	$qty		= $form_state['qty'];
	$nilSblm1	= atk_stock_data($edit_id); $nilSblm = $nilSblm1['qty'];
	db_set_active('pep');
	if($edit_id){
		$hasil_update = db_query("UPDATE atk_stock SET
				idMaterial 	= $idMaterial,
				idWarehouse = $idWarehouse,
				qty			= $qty
				WHERE idStock = $edit_id");// update (rsvQty		= $qty) di hapus !
		if($hasil_update){
			drupal_set_message('Stock UPDATE Success ...');
		}else{
			drupal_set_message('SAVE EDIT Stock FAILED !', 'error');
		}
		$idstket = atk_idstk_to_description($edit_id) .' ('. $edit_id .')';
		$ket['idStock'] = $idstket;
		$page = 'Edit Stock';
	}else{
		$cek_item_sama = cek_item_sama($idMaterial,$idWarehouse);
		if(!$cek_item_sama){
			db_set_active('pep');
			$hasil = db_query("INSERT INTO atk_stock (idMaterial,idWarehouse,qty,rsvQty) VALUES (%d,%d,%d,%d)", $idMaterial,$idWarehouse,$qty,0);
			if($hasil){
				drupal_set_message('Stock SAVE Success ...');
			}else{
				drupal_set_message('SAVE stock FAILED !', 'error');
			}
			$page = 'Insert Stock';
		}else{
			drupal_set_message('material tsb sdh ada pada lokasi yg sama !', 'error');
		}
	}
	db_set_active();
	$idm = atk_material_data($idMaterial); $idmket = $idm['description'] .' ('. $idMaterial .')';
	$idw = atk_warehouse_data($idWarehouse); $idwket = $idw['description'] .' ('. $idWarehouse .')';
	$ket['idMaterial'] = $idmket ; $ket['idWarehouse'] = $idwket; $ket['qty'] = $qty ;$ket['qtySblm'] = $nilSblm;
	$hasil = atk_logs($page,$ket);
	drupal_goto('atk/online/stock/view');
}
function atk_stock_delete() {
	$id 		= $_GET['id'];
	db_set_active('pep');
	$db_query 	= "DELETE FROM atk_stock WHERE idStock = $id";
	$result 	= mysql_query($db_query);
	if($result){
		drupal_set_message('Data Stock DELETE Success ...','error');
	}
	db_set_active();
	$page 		= 'Delete Stock';
	$ket 		= 'idStock : ' .atk_idstk_to_description($id);
	$hasil = atk_logs($page,$ket);
	drupal_goto('atk/online/stock/view');
}
function atk_stock_cek($idStock) { // atk_stock idStock, qty
	db_set_active('pep');
	$db_data = db_query("SELECT * FROM atk_stock WHERE idStock = $idStock");
	while($row = db_fetch_array($db_data)) {
		$hasil = $row['qty'];
	}
	db_set_active();
	return $hasil;
}
function cek_item_sama($idMaterial,$idWarehouse) { // cek $idMaterial,$idWarehouse -> ada ?
	db_set_active('pep');
	$db_data = db_query("SELECT * FROM atk_stock WHERE idMaterial = $idMaterial && idWarehouse = $idWarehouse");
	while($row = db_fetch_array($db_data)) {
		$hasil = $row['qty'];
	}
	db_set_active();
	return $hasil;
}
// END STOCK ========================================================================