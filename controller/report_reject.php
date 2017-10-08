<?php 
function atk_report_reject_form(){
	$periode_awal 	= date("Y-m-d",mktime(0,0,0,date('m')-1,date('d'),date('Y')));
	$periode_akhir 	= date("Y-m-d",mktime(0,0,0,date('m'),date('d'),date('Y')));
	$form['#attributes'] = array('enctype' => 'multipart/form-data');
	$form['atk_report_reservation_back'] = array(
			'#value' => t("<a href='" .base_path(). "atk/online/report'>[back]</a><br><br>"),
			'#weight' => 0,
	);
	$form['reservasi'] = array(
			'#type' => 'fieldset',
			'#title' => t('Reservation Report Reject'),
			'#weight' => 1,
			'#attributes' => array('class' => 'reservasi'),
			'#collapsible' => TRUE,
			'#collapsed' => FALSE,
	);
	$form['reservasi']['awal_reservasi'] = array(
			'#type' => 'textfield',
			'#title' => t('Tanggal Awal Reservasi'),
			'#attributes' => array('readonly'=>'readonly','class' => 'jscalendar'),
			'#jscalendar_ifFormat' => '%Y-%m-%d',
			'#jscalendar_showsTime' => 'false',
			'#default_value' => $periode_awal,
			'#required' => TRUE,
			'#weight' => 2
	);
	$form['reservasi']['akhir_reservasi'] = array(
			'#type' => 'textfield',
			'#title' => t('Tanggal Akhir Reservasi'),
			'#attributes' => array('readonly'=>'readonly','class' => 'jscalendar'),
			'#jscalendar_ifFormat' => '%Y-%m-%d',
			'#jscalendar_showsTime' => 'false',
			'#default_value' => $periode_akhir,
			'#required' => TRUE,
			'#weight' => 3
	);
	$form['submit'] = array(
			'#type' => 'submit',
			'#value' => 'View',
			'#weight' => 98,
	);
	return $form;
}

function atk_report_reject_form_submit($form, &$form_state) {
	$x		= $form_state['awal_reservasi'];
	$y		= $form_state['akhir_reservasi'];
	$tgl1	= substr($x,8,2); $bln1	= substr($x,5,2); $thn1	= substr($x,0,4);
	$tgl2	= substr($y,8,2); $bln2	= substr($y,5,2); $thn2	= substr($y,0,4);
	$awal_reservasi		= mktime(0,0,0, $bln1, $tgl1, $thn1);
	$akhir_reservasi	= mktime(0,0,0, $bln2, $tgl2, $thn2);
	drupal_goto('atk/online/report/bulanan/reject/' . $awal_reservasi. '/' . $akhir_reservasi);
}
function atk_report_bulanan_reject() {				// ============================= 1
	if(arg(5) && arg(6)){
		$awal 	= arg(5);
		$akhir 	= arg(6);
	}else{
		drupal_set_message('Please select Start/End Date !', 'error');
		drupal_goto('atk/online/report/reject');
	}

	$data = atk_report_bulanan_data($awal,$akhir);
	$back = "<a href='" .base_path(). "atk/online/report/reject'>[back]</a> | <a href='" .base_path(). "atk/online/report/bulanan/reject/toexcel/" .$awal. "/" .$akhir. "'>[to Excel]</a><br><br>";
	$output = theme('table', $data['judul'], $data['isi']);
	return $back.$output;
}

function atk_report_bulanan_data($awal,$akhir) {
	$judul = array(array('data'=>t('No.'),),
			array('data'=>t('No Reservasi'),),
			array('data'=>t('Create Time'),),
			array('data'=>t('Status'),),	
			array('data'=>t('Lokasi'),),	
			array('data'=>t('Fungsi'),),		
			array('data'=>t('Request Name'),),	
			array('data'=>t('Material Code'),),
			array('data'=>t('Item Stock'),)
	);
	db_set_active('pep'); //a.statusApproval = 5 OR a.statusApproval = 6 OR (
	$db_data = db_query("SELECT a.reservasiNo, a.statusApproval, a.requestBy, a.createTime, b.idStock, b.materialCode, b.requestQty, b.acceptQty FROM pep.atk_reservation AS a
							LEFT JOIN pep.atk_reservation_detil AS b ON b.reservasiNo = a.reservasiNo WHERE a.createTime >= $awal AND a.createTime <= $akhir ORDER BY a.reservasiNo ASC");

	$nores = '';
	while($row = db_fetch_array($db_data)) {
		$fungsi		= get_fungsi_user($row['requestBy']);
		$mat		= atk_material_data_res($row['materialCode']);
		$material	= $mat['description'];
		$waktu		= date("d-m-Y", $row['createTime']);
		$req		= atk_user_data($row['requestBy']);
		if($row['statusApproval'] == 5){
			$stat = 'Reject by Atasan';
		}elseif($row['statusApproval'] == 6){
			$stat = 'Reject by SCM';
		}
		
		if($row['statusApproval'] == 5 || $row['statusApproval'] == 6){
			if($row['reservasiNo'] != $nores){
				$idpl	= $req['lokasi']; $loka = atk_plant_data($idpl); $lokasi = $loka['description'];
				$isi[] 	= array(++$xyz, $row['reservasiNo'], $waktu, $stat, $lokasi, $fungsi['fungsi'], $req['fullname'], $row['materialCode'], $material);
			}else{
				$isi[] 	= array('', '', '', '', '', '', '', $row['materialCode'], $material);
			}
		}
		$nores	= $row['reservasiNo'];
	}
	db_set_active();

	$hasil['judul'] = $judul;
	$hasil['isi'] 	= $isi;
	return $hasil;
}
function atk_report_bulanan_reject_toexcel(){		// ============================= 7
	if(arg(6) && arg(7)){
		$awal 	= arg(6);
		$akhir 	= arg(7);
	}else{
		drupal_set_message('Please select Start/End Date !', 'error');
		drupal_goto('atk/online/report/form');
	}
	$data 		= atk_report_bulanan_data($awal,$akhir);
	$judul		= $data['judul'];
	$isi		= $data['isi'];

	$workbook = new Spreadsheet_Excel_Writer();
	$workbook->send('report_rejected.xls');
	$worksheet =& $workbook->addWorksheet('report_rejected');
	$worksheet->freezePanes(array(1, 0));
	$format =& $workbook->addFormat(array('Size' => 10,
			'Align' => 'center',
			'Bold' => 1,
			'Color' => 'white',
			'Pattern' => 1,
			'BgColor' => 'white',
			'FgColor' => 'grey'));
	$worksheet->setColumn(0,0,5);
	$worksheet->setColumn(1,1,15);
	$worksheet->setColumn(2,2,10);
	$worksheet->setColumn(3,3,20);
	$worksheet->setColumn(4,4,15);
	$worksheet->setColumn(5,5,15);
	$worksheet->setColumn(6,6,15);
	$worksheet->setColumn(7,7,15);
	$worksheet->setColumn(8,8,15);

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
?>