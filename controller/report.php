<?php 
function atk_report_liststock(){
	drupal_add_js(drupal_get_path('module','atk').'/js/script.js');
	$plant_grup 	= arg(4);
	$warehouse_grup	= arg(5);
	$plant_pil		= atk_plant_data_select();
// 	$warehouse_pil	= atk_warehouse_data_select();
	$category_pil	= atk_category_data();
	$form['#attributes'] = array('enctype' => 'multipart/form-data');
	$form['atk_report_back'] = array(
			'#value' => t("<a href='" .base_path(). "atk/online/report'>[back]</a><br><br>"),
			'#weight' => 0,
	);
	if(!empty($plant_grup)) {
		db_set_active('pep');
		$db_data = db_query("SELECT * FROM atk_warehouse WHERE idPlant = $plant_grup && isActive = 1");
		while($row = db_fetch_array($db_data)) {
			$warehouse_pil[$row['idWarehouse']] = $row['description'] .' - '. atk_cek_plant($row['idWarehouse']);
		}
		if(empty($warehouse_pil)){
			$warehouse_pil[] = 'Data tdk ada';
		}
		db_set_active();
	}
	if(!$plant_grup){
		$plant_pil[0]	= '--select--';
		$plant_grup		= 0;
	}
	$form['plant'] = array(
			'#title' => t('Plant'),
			'#type' => 'select',
			'#options' => $plant_pil,
			'#default_value' =>  variable_get('plant', $plant_grup),
			'#weight' => 1,
			'#required' => TRUE,
	);
	if(!$warehouse_grup){
		$warehouse_pil[0]	= '--select--';
		$warehouse			= 0;
	}
	if(!empty($plant_grup)) {
		$warehouse_pil[0]	= '--select--';
		$form['warehouse'] = array(
				'#title' => t('Warehouse'),
				'#type' => 'select',
				'#options' => $warehouse_pil,
				'#default_value' =>  variable_get('warehouse', $warehouse),
				'#weight' => 2,
				'#required' => TRUE,
		);
	}
	if(!$warehouse_grup){
		$category_pil[0]	= '--select--';
		$category			= 0;
	}
	$form['category'] = array(
				'#title' => t('Material Category'),
				'#type' => 'select',
				'#options' => $category_pil,
				'#default_value' =>  variable_get('category', $category),
				'#weight' => 3,
				'#required' => TRUE,
	);	
	$form['submit'] = array(
				'#type' => 'submit',
				'#value' => 'View',
				'#weight' => 98,
	);
	return $form;
}
function atk_report_liststock_submit($form, &$form_state) {
	$plant 		= $form_state['plant'];
	$warehouse 	= $form_state['warehouse'];
	$category 	= $form_state['category'];
	if(($plant) && ($warehouse) && ($category)){
		drupal_goto('atk/online/report/liststock/view', 'pl=' .$plant. '&wh=' .$warehouse. '&cat=' .$category);
	}
	if(($plant) && ($warehouse)){
		drupal_goto('atk/online/report/liststock/' .$plant. '/' .$warehouse);
	}
	if($plant){
		drupal_goto('atk/online/report/liststock/' .$plant);
	}
	return $hasil;
}
function atk_report_reservation(){
	$periode_awal 	= date("Y-m-d",mktime(0,0,0,date('m'),date('d')-7,date('Y')));
	$periode_akhir 	= date("Y-m-d",mktime(0,0,0,date('m'),date('d')+1,date('Y')));
	$plant_pil		= atk_plant_data_select();
	$warehouse_pil	= atk_warehouse_data_select();
	$category_pil	= atk_category_data();
	$form['#attributes'] = array('enctype' => 'multipart/form-data');
	$form['atk_report_reservation_back'] = array(
			'#value' => t("<a href='" .base_path(). "atk/online/report'>[back]</a><br><br>"),
			'#weight' => 0,
	);
	$form['reservasi'] = array(
			'#type' => 'fieldset',
			'#title' => t('Reservation Periode'),
			'#weight' => 1,
			'#attributes' => array('class' => 'reservasi'),
			'#collapsible' => TRUE,
			'#collapsed' => TRUE,
	);
	$form['reservasi']['awal_reservasi'] = array(
			'#type' => 'textfield',
			'#title' => t('dari'),
			'#attributes' => array('readonly'=>'readonly','class' => 'jscalendar'),
			'#jscalendar_ifFormat' => '%Y-%m-%d',
			'#jscalendar_showsTime' => 'false',
			'#default_value' => $periode_awal,
			'#required' => TRUE,
			'#weight' => 2
	);
	$form['reservasi']['akhir_reservasi'] = array(
			'#type' => 'textfield',
			'#title' => t('s.d'),
			'#attributes' => array('readonly'=>'readonly','class' => 'jscalendar'),
			'#jscalendar_ifFormat' => '%Y-%m-%d',
			'#jscalendar_showsTime' => 'false',
			'#default_value' => $periode_akhir,
			'#required' => TRUE,
			'#weight' => 3
	);
	$form['issue'] = array(
			'#type' => 'fieldset',
			'#title' => t('Issue Periode'),
			'#weight' => 4,
			'#collapsible' => TRUE,
			'#collapsed' => TRUE,
	);
	$form['issue']['awal_issue'] = array(
			'#type' => 'textfield',
			'#title' => t('dari'),
			'#attributes' => array('readonly'=>'readonly','class' => 'jscalendar'),
			'#jscalendar_ifFormat' => '%Y-%m-%d',
			'#jscalendar_showsTime' => 'false',
			'#default_value' => $periode_awal,
			'#required' => TRUE,
			'#weight' => 5
	);
	$form['issue']['akhir_issue'] = array(
			'#type' => 'textfield',
			'#title' => t('s.d'),
			'#attributes' => array('readonly'=>'readonly','class' => 'jscalendar'),
			'#jscalendar_ifFormat' => '%Y-%m-%d',
			'#jscalendar_showsTime' => 'false',
			'#default_value' => $periode_akhir,
			'#required' => TRUE,
			'#weight' => 6
	);
	$form['category'] = array(
				'#title' => t('Material Category'),
				'#type' => 'select',
				'#options' => $category_pil,
				'#default_value' =>  variable_get('category', $category),
				'#weight' => 7,
				'#required' => TRUE,
	);	
	$form['plant'] = array(
			'#title' => t('Plant'),
			'#type' => 'select',
			'#options' => $plant_pil,
			'#default_value' =>  variable_get('plant', $plant),
			'#weight' => 8,
			'#required' => TRUE,
	);
	$form['res_no'] = array(
			'#title' => t('Reservation Number'),
			'#type' => 'textfield',
			'#size' => 20,
			'#maxlength' => 10,
			'#default_value' =>  $res_no,
			'#weight' => 9,
			'#required' => FALSE,
	);
	$form['submit'] = array(
				'#type' => 'submit',
				'#value' => 'View',
				'#weight' => 98,
	);
	return $form;
}
function atk_report_reservation_submit($form, &$form_state) {
	$awal_reservasi	= $form_state['awal_reservasi'];
	$akhir_reservasi= $form_state['akhir_reservasi'];
	$plant 			= $form_state['plant'];	
	drupal_goto('atk/online/report/reservation/view', 'awalr=' .$awal_reservasi. '&akhirr=' .$akhir_reservasi);	
}
function atk_report_reservation_view(){
	$awal_reservasi	= $_GET['awalr'];
	$akhir_reservasi= $_GET['akhirr'];
	$hasil			= $awal_reservasi .' : '. $akhir_reservasi;
	return $hasil;
}
function atk_report_liststock_excel() {
	$pl	=	$_GET['pl']; $wh	=	$_GET['wh']; $cat	=	$_GET['cat'];
	$data 	= atk_report_view_data($pl,$wh,$cat);
	$judul	= $data['judul'];
	$isi	= $data['isi'];

	$workbook = new Spreadsheet_Excel_Writer();
	$workbook->send('report_liststock.xls');
	$worksheet =& $workbook->addWorksheet('report_liststock');
	$worksheet->freezePanes(array(1, 0));
	$format =& $workbook->addFormat(array('Size' => 10,
			'Align' => 'center',
			'Bold' => 1,
			'Color' => 'white',
			'Pattern' => 1,
			'BgColor' => 'white',
			'FgColor' => 'grey'));
	$worksheet->setColumn(0,0,5);
	$worksheet->setColumn(1,1,50);
	$worksheet->setColumn(2,2,5);
	$worksheet->setColumn(3,3,5);
	$worksheet->setColumn(4,4,25);

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
function atk_report_view_data($pl,$wh,$cat) {
	$judul = array(array('data'=>t('No.'),),
			array('data'=>t('Material'),),
			array('data'=>t('Qty Available'),),
			array('data'=>t('Satuan'),),
			array('data'=>t('Warehouse'),),
			array('data'=>t('Plant'),)
	);
	db_set_active('pep');
	$db_data = db_query("SELECT * FROM atk_stock WHERE idWarehouse = $wh ORDER BY idStock ASC");
	while($row = db_fetch_array($db_data)) {
		$cek_cat= cek_cattoid($row['idMaterial']);
		$cek_pln= cek_planttoid($row['idWarehouse']);
		$sat 	= atk_material_data($row['idMaterial']);
		if(($cek_cat==$cat) && ($cek_pln==$pl)){
			$isi[] 	= array(++$xyz, atk_material_data_select($row['idMaterial']),$row['qty'], $sat['satuan'], atk_cek_plant($row['idWarehouse']), atk_warehouse_data_select($row['idWarehouse']));
		}		
	}
	db_set_active();
	$hasil['judul'] = $judul;
	$hasil['isi'] 	= $isi;
	return $hasil;
}
function cek_planttoid($idWarehouse) {
	db_set_active('pep');
	$db_data = db_query("SELECT * FROM atk_warehouse WHERE idWarehouse = $idWarehouse");
	while($row = db_fetch_array($db_data)) {
		$hasil 	= $row['idPlant'];
	};
	db_set_active();
	return $hasil;
}
function cek_cattoid($idMaterial) {
	db_set_active('pep');
	$db_data = db_query("SELECT * FROM atk_material WHERE id = $idMaterial");
	while($row = db_fetch_array($db_data)) {
		$hasil 	= $row['category'];
	};
	db_set_active();
	return $hasil;
}
function atk_report_liststock_view() {
	$pl	=	$_GET['pl']; $wh	=	$_GET['wh']; $cat	=	$_GET['cat'];
	$data = atk_report_view_data($pl,$wh,$cat);
	$output = theme_table($data['judul'], $data['isi']);
	$hasil = "<a href='" .base_path(). "atk/online/report/liststock/excel/?pl=$pl&wh=$wh&cat=$cat'>to Excel</a> | ";
	$hasil .= "<a href='" .base_path(). "atk/online/report/liststock'>[back]</a><br><br>";
	return $hasil.$output;
}
// Report Logs ============================================================================
function atk_report_logs_data() {
	// ===== paging =====
	$aw = arg(4); 	// $aw = mulai record yg akan ditampilkan
	$ak = 20;		// $ak = banyaknya baris yg akan ditampilkan
	if(!$aw){
		$aw = 0;
	}
	
	$js = '<script type="text/javascript">
			    function getval(sel) {
					window.location = "' .base_path(). 'atk/online/report/logs/" + sel.value;
			    }
			</script>';
	// ===== paging =====
	
	$judul = array(array('data'=>t('No.'),),
			array('data'=>t('Timestamp'),),
			array('data'=>t('User'),),
			array('data'=>t('Page'),),
			array('data'=>t('Keterangan'),)
	);
	db_set_active('pep');
	$db_data = db_query("SELECT * FROM atk_logs ORDER BY timeStamp DESC LIMIT $aw,$ak");
	
	// ===== paging =====
	$tes		= db_query("SELECT COUNT(*) FROM atk_logs");
	$hitbrs		= db_fetch_array($tes);$hitbrs	= $hitbrs['COUNT(*)'];
	$sisa 		= $hitbrs % $ak;
	
	if($sisa > 0){ // $totpg = ganjil (ada sisa)
		$totpg 		= (($hitbrs - $sisa) / $ak);
	}
	
	// 	$ling = '';
	$ling	= "<select name='category' id='category' onchange='getval(this);'>";
	for($lup=0;$lup<=$totpg;$lup++){
		$hit = $lup * $ak;
		$pg = $lup + 1;
		if($hit == $aw){
			$pil = 'selected';
		}else{
			$pil = '';
		}
		// 		$ling .= '<a href="' .base_path(). 'atk/online/master/listall/' .$hit. '"> ' .$pg. ' </a> ';
		$ling .= "<option " .$pil. " value='" .$hit. "'>" .$pg. "</option>";
	}
	$ling	.= "</select><br>";
	// ===== paging =====
	
	while($row = db_fetch_array($db_data)) {
		$waktu 	= date("d-m-Y H:i:s", $row['timeStamp']);
		$isi[] 	= array(++$xyz, $waktu, $row['name'], $row['page'], $row['keterangan']);
	}
	db_set_active();
	
	// view paging :
	$pagess 	= 'Pages : ' . $ling;
	
	$hasil['judul'] = $judul;
	$hasil['isi'] 	= $isi;
	$hasil['pagess']= $pagess;
	$hasil['js']= $js;
	return $hasil;
}
function atk_report_logs_view() {
	$data = atk_report_logs_data();
// 	$output = theme_table($data['judul'], $data['isi']);
	$back 	= "<a href='" .base_path(). "atk/online/report'>[back]</a><br><br>";
	$output = theme('table', $data['judul'], $data['isi']);
	$pagess = '<br>' . $data['pagess'];
	$js		= $data['js'];
	return $js.$back.$pagess.$output;
}
// END Report Logs ========================================================================