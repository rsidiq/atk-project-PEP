<?php 
function atk_report_form(){
	$periode_awal 	= date("Y-m-d",mktime(0,0,0,date('m'),date('d')-7,date('Y')));
	$periode_akhir 	= date("Y-m-d",mktime(0,0,0,date('m'),date('d')+1,date('Y')));
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
function atk_report_form_submit($form, &$form_state) {
	$x		= $form_state['awal_reservasi'];
	$y		= $form_state['akhir_reservasi'];
	$tgl1	= substr($x,8,2); $bln1	= substr($x,5,2); $thn1	= substr($x,0,4);
	$tgl2	= substr($y,8,2); $bln2	= substr($y,5,2); $thn2	= substr($y,0,4);
	$awal_reservasi		= mktime(0,0,0, $bln1, $tgl1, $thn1);
	$akhir_reservasi	= mktime(0,0,0, $bln2, $tgl2, $thn2);
	drupal_goto('atk/online/report/bulanan/' . $awal_reservasi. '/' . $akhir_reservasi);	
}
function atk_report_bulan() {				// ============================= 1
	if(arg(4) && arg(5)){	
		$awal 	= arg(4);
		$akhir 	= arg(5);
	}else{
		drupal_set_message('Please select Start/End Date !', 'error');
		drupal_goto('atk/online/report/form');
	}
	
	$data = atk_report_bulan_data($awal,$akhir);
	$back = "<a href='" .base_path(). "atk/online/report/form'>[back]</a> | <a href='" .base_path(). "atk/online/report/bulanan/toexcel/" .$awal. "/" .$akhir. "'>[to Excel]</a><br><br>";
	$output = theme('table', $data['judul'], $data['isi']);
	$output .= theme('pager', NULL, 10);
	return $back.$output;
}

function atk_report_bulan_data($awal,$akhir) {			// tampilan final DI SINI <<=	// ============================= 2
	$judul = array(array('data'=>t('No.'),),
			array('data'=>t('Fungsi'),),
			array('data'=>t('Kategori'),),
			array('data'=>t('Jml. Barang'),),
			array('data'=>t('Tot. Harga'),)
	);

	$dataTot	= atk_report_bulan_sort($awal,$akhir);
	foreach ($dataTot as $fungsi => $data){
		$jmlAllHrg = 0; $jmlAllBrg = 0;
		$isi[] 	= array(++$xyz . '.', $fungsi);
		foreach ($data as $cat => $data2){
			$isi[] 	= array('','',atk_category_name($cat),$data2['totBrg'],$data2['cur'] .' '. number_format($data2['totHrg']));
			$jmlAllHrg	+= $data2['totHrg'];
			$jmlAllBrg	+= $data2['totBrg'];
		}
		$isi[] 	= array('','','Total',$jmlAllBrg,$data2['cur'] .' '. number_format($jmlAllHrg));
	}

	$hasil['judul'] = $judul;
	$hasil['isi'] 	= $isi;
	return $hasil;
}

function atk_report_bulan_sort($awal,$akhir) {			// kelompokkan item sesuai fungsi yg sama, dan jumlahkan tot.harga	// ============================= 3
	$dataDetil	= atk_report_main($awal,$akhir);
	foreach ($dataDetil as $idRevDtl => $dataIsi){
		$fungsi = $dataIsi['fungsi'];
		$cat 	= $dataIsi['cat'];
		$prc 	= $dataIsi['prc'];
		$accept = $dataIsi['accept'];
		$totHrg	= $accept * $prc; // diterima * harga
		//===========================
		$request 	= $dataIsi['request'];
		$cur 		= $dataIsi['cur'];
		$hslAkh[$fungsi][$cat]['totHrg']	+= $totHrg;
		$hslAkh[$fungsi][$cat]['totBrg']	+= $accept;
		$hslAkh[$fungsi][$cat]['cur']		= $cur;
	}

	return $hslAkh;
}

function atk_report_main($awal,$akhir){					// ============================= 4
	db_set_active('pep');
	$db_data = db_query("SELECT * FROM atk_reservation WHERE nopekScmApproval != '' && createTime >= $awal && createTime <= $akhir ORDER BY createTime DESC"); // tambahkan filter, nopekScmApproval != '' (#3)
	while($row = db_fetch_array($db_data)) {
// 		$waktu 		= date("d-m-Y H:i:s", $row['createTime']);
		$fungsi			= get_fungsi_user($row['requestBy']); // tambahkan filter disini, jika fungsi '' kosong, tdk perlu diambil datanya. (#1)
		if($fungsi){
			$resNo					= $row['reservasiNo'];
			$hasil[$resNo]['user'] 	= $row['requestBy'];
			$hasil[$resNo]['fungsi']= $fungsi['fungsi'];
			
			$data						= get_data_reservasi($row['reservasiNo']);	
			foreach ($data as $idRevDtl => $dataIsi){
				foreach ($dataIsi as $ket => $dataDetil){ // string 'ATKR-160114-000003:accept:35' (length=28)
					$hasil[$resNo][$ket]	= $dataDetil;
				}
			}
		}
	}
	db_set_active();
	
	return $hasil;
}

function get_data_reservasi($resNo){		// requestQty	acceptQty // ============================= 5
	db_set_active('pep');
	$db_data = db_query("SELECT * FROM atk_reservation_detil WHERE reservasiNo = '$resNo'");
	while($row = db_fetch_array($db_data)) {		
		$hasil[$row['id']]['accept']	= $row['acceptQty'];
		$hasil[$row['id']]['request']	= $row['requestQty'];
		
		$data_res		= get_cat_matcod($row['materialCode']); // tambahkan filter, jika category '' kosong, data selanjutnya tdk perlu disimpan. (#2)
		$hasil[$row['id']]['cat']	= $data_res['cat'];
		$hasil[$row['id']]['prc']	= $data_res['prc'];
		$hasil[$row['id']]['cur']	= $data_res['cur'];
	}
	db_set_active();

	return $hasil;
}

function get_cat_matcod($matcode) {			// ============================= 6
	db_set_active('pep');
	$db_data = db_query("SELECT * FROM atk_material WHERE materialCode = '$matcode'");
	while($row = db_fetch_array($db_data)) {
		$hasil['cat'] 	= $row['category'];
		$hasil['prc'] 	= $row['price'];
		$hasil['cur'] 	= $row['currency'];
	}
	db_set_active();
	return $hasil;
}

function atk_report_bulan_toexcel(){		// ============================= 7
	if(arg(5) && arg(6)){
		$awal 	= arg(5);
		$akhir 	= arg(6);
	}else{
		drupal_set_message('Please select Start/End Date !', 'error');
		drupal_goto('atk/online/report/form');
	}
	$data 		= atk_report_bulan_data($awal,$akhir);
	$judul		= $data['judul'];
	$isi		= $data['isi'];

	$workbook = new Spreadsheet_Excel_Writer();
	$workbook->send('report_reservation.xls');
	$worksheet =& $workbook->addWorksheet('report_reservation_status');
	$worksheet->freezePanes(array(1, 0));
	$format =& $workbook->addFormat(array('Size' => 10,
			'Align' => 'center',
			'Bold' => 1,
			'Color' => 'white',
			'Pattern' => 1,
			'BgColor' => 'white',
			'FgColor' => 'grey'));
	$worksheet->setColumn(0,0,5);
	$worksheet->setColumn(1,1,30);
	$worksheet->setColumn(2,2,30);
	$worksheet->setColumn(3,3,10);
	$worksheet->setColumn(4,4,20);

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