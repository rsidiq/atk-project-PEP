<?php 
// LIST ALL RESERVASI ============================================================================
function atk_listall_view_data() {
	// ===== paging =====
	$aw = isset($_GET['aw']); 	// $aw = mulai record yg akan ditampilkan
	$ak = 15;		// $ak = banyaknya baris yg akan ditampilkan
	if(!$aw){
		$aw = 0;
	}
	
	if(isset($_GET['cari'])){
		$cariTx = $_GET['cari'];
		$cariNoRes = "&& a.reservasiNo LIKE '%$cariTx%'";
	}
	if(isset($_GET['lok'])){
		$lok = $_GET['lok'];
	}else{
		$lok = 1;
	}
	
	$js = '<script type="text/javascript">
			    function pg(sel) {
					window.location = "' .base_path(). 'atk/online/master/listall/?aw=" + sel.value;
			    }
			    function lok(sel) {
					window.location = "' .base_path(). 'atk/online/master/listall/?lok=" + sel.value;
			    }
			</script>';
	// ===== paging =====
	
	$name 		= $GLOBALS['user']->name; 
	$usr_data	= atk_user_data($name); 
	$lokasi_usr = $usr_data['lokasi']; // lokasi user in numerik
	$lok_user	= atk_plant_data($lokasi_usr);
	$lok_teks	= $lok_user['description'];
	
	// ============ SEARCH ===================
	$cari	="<script type='text/javascript'>
 			    function search_action(event) {
 					event = event || window.event;
					if(event.keyCode == 13){
						location.href='".base_path()."atk/online/master/listall/?cari='+document.getElementById('cari').value;
					}
 			    }
 			</script>";
	$cari	.= '<input type="text" name="cari" id="cari" value="' .$cariTx. '" size="25" maxlength="30" onkeypress="search_action(event)" autofocus />';
	$cari	.= '<input type="button" value="Find" onclick="location.href=\''.base_path().'atk/online/master/listall/?cari=\'+document.getElementById(\'cari\').value" /><br><br>';
	// ============ END SEARCH ===================
	
	$stat = array(1=>'New Reservation', 2=>'Approved by Atasan', 3=>'Approved by SCM', 5=>'Rejected by Atasan', 6=>'Rejected by SCM', 7=>'Good Issued');
	$judul = array(array('data'=>t('No.'),),
			array('data'=>t('No. Reservasi'),),
			array('data'=>t('Status'),),
			array('data'=>t('Fungsi'),),
			array('data'=>t('Input'),),
			array('data'=>t('Penanggung Jawab'),),
			array('data'=>t('Atasan Approver'),),
			array('data'=>t('SCM Approver'),),
			array('data'=>t('Good Issued - Taken'),)
	);
	db_set_active('pep');
// 	$db_data = db_query("SELECT * FROM atk_reservation ORDER BY statusApproval ASC");
	$db_data = db_query("SELECT DISTINCT a.reservasiNo, b.idPlant, a.input, a.issuer, a.statusApproval, a.requestBy, a.mgrApproval, a.scmApproval, a.nameClose FROM pep.atk_reservation AS a
							LEFT JOIN pep.atk_reservation_detil AS b ON b.reservasiNo = a.reservasiNo WHERE b.idPlant=$lokasi_usr $cariNoRes ORDER BY a.statusApproval ASC,a.reservasiNo DESC LIMIT $aw,$ak");
	
	// ===== paging =====
	$tes		= db_query("SELECT COUNT(DISTINCT reservasiNo) FROM atk_reservation_detil WHERE idPlant=$lokasi_usr");
	$hitbrs		= db_fetch_array($tes);$hitbrs	= $hitbrs['COUNT(DISTINCT reservasiNo)']; 
	$sisa 		= $hitbrs % $ak;
	
	if($sisa > 0){ // $totpg = ganjil (ada sisa)
		$totpg 		= (($hitbrs - $sisa) / $ak);
	}
	
	// Lokasi Option select
	$lks 	= atk_plant_list(); // data list Lokasi/Plant => array
	$lkOpt	= "<select name='plant' id='plant' onchange='lok(this);'>";
	foreach ($lks as $id => $desc){
		if($id == $lok){
			$pilok = 'selected';
		}else{
			$pilok = '';
		}
		$lkOpt .= "<option " .$pilok. " value='" .$id. "'>" .$desc. "</option>";
	}
	$lkOpt	.= "</select><br>";
	// END Lokasi Option select 
	
// 	$ling = '';
	$ling	= "<select name='category' id='category' onchange='pg(this);'>";
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
		$inputusr	= atk_user_data($row['input']);
		$req		= atk_user_data($row['requestBy']);
		$atasan		= atk_user_data($row['mgrApproval']);
		$scm		= atk_user_data($row['scmApproval']);
		$gdgusr		= atk_user_data($row['issuer']);
		$fung		= get_fungsi_user($row['requestBy']);
		$isi[] 		= array(++$xyz, $row['reservasiNo'], $stat[$row['statusApproval']], $fung['fungsi'], $inputusr['fullname'], $req['fullname'], $atasan['fullname'], $scm['fullname'], $gdgusr['fullname'] .' - '. $row['nameClose']);
	}
	db_set_active();
	
	// view paging : 
	$pagess 	= 'Pages : ' . $ling;

	$hasil['judul'] = $judul;
	$hasil['isi'] 	= $isi;
	$hasil['cari']	= $cari;
	$hasil['lkOpt']	= $lkOpt;
	$hasil['lokasi']= $lok_teks;
	$hasil['pagess']= $pagess;
	$hasil['js']= $js;
	return $hasil;
}
function atk_listall_view() {
	$usr	= atk_user_roles(); 
  	$sa		= $usr['sa'];
  	
	$data 	= atk_listall_view_data();
	$output = theme_table($data['judul'], $data['isi']);
	if($sa){
		$lokasi = 'Lokasi : ' . $data['lkOpt'];
	}else{
		$lokasi = 'Lokasi : ' . $data['lokasi'];
	}
	$pagess = '<br>' . $data['pagess'];
	$js		= $data['js'];
	$back = "<a href='" .base_path(). "atk/online/master/listall/toexcel'>to Excel</a> | <a href='" .base_path(). "atk/online/master'>[back]</a><br><br>";
	return $js.$back.$data['cari'].$lokasi.$pagess.$output;
}
function atk_material_data_res($id) {
	// ambil data
	db_set_active('pep');
	$db_data = db_query("SELECT * FROM atk_material WHERE materialCode = '$id'");
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
function atk_listall_data_reservasi() {
	$judul = array(array('data'=>t('No.'),),
			array('data'=>t('No Reservasi'),),
			array('data'=>t('Create Time'),),
			array('data'=>t('Status'),),		
			array('data'=>t('Fungsi'),),		
			array('data'=>t('Request Name'),),	
			array('data'=>t('Material Code'),),
			array('data'=>t('Item Stock'),),
			array('data'=>t('Request Qty'),),
			array('data'=>t('Accept'),)
	);
	db_set_active('pep');
	$db_data = db_query("SELECT a.reservasiNo, a.statusApproval, a.requestBy, a.createTime, b.idStock, b.materialCode, b.requestQty, b.acceptQty FROM pep.atk_reservation AS a
							LEFT JOIN pep.atk_reservation_detil AS b ON b.reservasiNo = a.reservasiNo WHERE a.statusApproval = 3 OR a.statusApproval = 7 ORDER BY a.reservasiNo ASC");

	$nores = '';
	while($row = db_fetch_array($db_data)) {
		$fungsi		= get_fungsi_user($row['requestBy']);
		$mat		= atk_material_data_res($row['materialCode']);
		$material	= $mat['description'];
		$waktu		= date("d-m-Y", $row['createTime']);
		$req		= atk_user_data($row['requestBy']);
		if($row['statusApproval'] == 3){
			$stat = 'Approved by SCM';
		}else{
			$stat = 'Good Issued';
		}
		
		if($row['reservasiNo'] != $nores){
			$isi[] 	= array(++$xyz, $row['reservasiNo'], $waktu, $stat, $fungsi['fungsi'], $req['fullname'], $row['materialCode'], $material, $row['requestQty'], $row['acceptQty']);
		}else{
			$isi[] 	= array('', '', '', '', '', '', $row['materialCode'], $material, $row['requestQty'], $row['acceptQty']);
		}
		$nores	= $row['reservasiNo'];
	}
	db_set_active();

	$hasil['judul'] = $judul;
	$hasil['isi'] 	= $isi;
	return $hasil;
}
function atk_listall_data_reservasi_toexcel(){		// ============================= 7
	$data 		= atk_listall_data_reservasi();
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
	$worksheet->setColumn(1,1,20);
	$worksheet->setColumn(2,2,20);
	$worksheet->setColumn(3,3,20);
	$worksheet->setColumn(4,4,30);
	$worksheet->setColumn(5,5,15);
	$worksheet->setColumn(6,6,15);
	$worksheet->setColumn(7,7,50);

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