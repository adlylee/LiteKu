<?php
namespace Plugins\Rawat_Jalan;

use Systems\AdminModule;
use Plugins\Icd\DB_ICD;
use Systems\Lib\Fpdf\PDF_MC_Table;
use Systems\Lib\BpjsService;

class Admin extends AdminModule
{
    private $_uploads = WEBAPPS_PATH.'/berkasrawat/pages/upload';
    public function navigation()
    {
        return [
            'Kelola'              => 'index',
            'Rawat Jalan'         => 'manage',
            'Booking Registrasi'  => 'booking',
            'Booking Periksa'     => 'bookingperiksa',
            'Jadwal Dokter'       => 'jadwal',
            'Rujukan Internal Poli'=> 'rujukaninternal'
        ];
    }

    public function getIndex()
    {
      $sub_modules = [
        ['name' => 'Rawat Jalan', 'url' => url([ADMIN, 'rawat_jalan', 'manage']), 'icon' => 'wheelchair', 'desc' => 'Pendaftaran pasien rawat jalan'],
        ['name' => 'Booking Registrasi', 'url' => url([ADMIN, 'rawat_jalan', 'booking']), 'icon' => 'file-o', 'desc' => 'Pendaftaran pasien booking rawat jalan'],
        ['name' => 'Booking Periksa', 'url' => url([ADMIN, 'rawat_jalan', 'bookingperiksa']), 'icon' => 'file-o', 'desc' => 'Booking periksa pasien rawat jalan via Online'],
        ['name' => 'Jadwal Dokter', 'url' => url([ADMIN, 'rawat_jalan', 'jadwal']), 'icon' => 'user-md', 'desc' => 'Jadwal dokter rawat jalan'],
        ['name' => 'Rujukan Internal Poli', 'url' => url([ADMIN, 'rawat_jalan', 'rujukaninternal']), 'icon' => 'file-text-o', 'desc' => 'Rujukan Internal Poli'],
      ];
      return $this->draw('index.html', ['sub_modules' => $sub_modules]);
    }

    public function anyManage()
    {
        $tgl_kunjungan = date('Y-m-d');
        $tgl_kunjungan_akhir = date('Y-m-d');
        $status_periksa = '';
        $status_bayar = '';

        if(isset($_POST['periode_rawat_jalan'])) {
          $tgl_kunjungan = $_POST['periode_rawat_jalan'];
        }
        if(isset($_POST['periode_rawat_jalan_akhir'])) {
          $tgl_kunjungan_akhir = $_POST['periode_rawat_jalan_akhir'];
        }
        if(isset($_POST['status_periksa'])) {
          $status_periksa = $_POST['status_periksa'];
        }
        if(isset($_POST['status_bayar'])) {
          $status_bayar = $_POST['status_bayar'];
        }
        $cek_vclaim = $this->db('mlite_modules')->where('dir', 'vclaim')->oneArray();
        $master_berkas_digital = $this->db('master_berkas_digital')->toArray();
        $maping_dokter_dpjpvclaim = $this->db('maping_dokter_dpjpvclaim')->toArray();
        $maping_poli_bpjs = $this->db('maping_poli_bpjs')->toArray();
        $responsivevoice =  $this->settings->get('settings.responsivevoice');
        $this->_Display($tgl_kunjungan, $tgl_kunjungan_akhir, $status_periksa, $status_bayar);
        return $this->draw('manage.html',
          [
            'rawat_jalan' => $this->assign,
            'cek_vclaim' => $cek_vclaim,
            'master_berkas_digital' => $master_berkas_digital,
            'maping_dokter_dpjpvclaim' => $maping_dokter_dpjpvclaim,
            'maping_poli_bpjs' => $maping_poli_bpjs,
            'responsivevoice' => $responsivevoice,
            'admin_mode' => $this->settings->get('settings.admin_mode')
          ]
        );
    }

    public function anyDisplay()
    {
        $tgl_kunjungan = date('Y-m-d');
        $tgl_kunjungan_akhir = date('Y-m-d');
        $status_periksa = '';
        $status_bayar = '';

        if(isset($_POST['periode_rawat_jalan'])) {
          $tgl_kunjungan = $_POST['periode_rawat_jalan'];
        }
        if(isset($_POST['periode_rawat_jalan_akhir'])) {
          $tgl_kunjungan_akhir = $_POST['periode_rawat_jalan_akhir'];
        }
        if(isset($_POST['status_periksa'])) {
          $status_periksa = $_POST['status_periksa'];
        }
        if(isset($_POST['status_bayar'])) {
          $status_bayar = $_POST['status_bayar'];
        }
        $cek_vclaim = $this->db('mlite_modules')->where('dir', 'vclaim')->oneArray();
        $responsivevoice =  $this->settings->get('settings.responsivevoice');
        $this->_Display($tgl_kunjungan, $tgl_kunjungan_akhir, $status_periksa, $status_bayar);
        echo $this->draw('display.html', ['rawat_jalan' => $this->assign, 'cek_vclaim' => $cek_vclaim, 'responsivevoice' => $responsivevoice, 'admin_mode' => $this->settings->get('settings.admin_mode')]);
        exit();
    }

    public function _Display($tgl_kunjungan, $tgl_kunjungan_akhir, $status_periksa='', $status_bayar='')
    {

        if($this->settings->get('settings.responsivevoice') == 'true') {
          $this->core->addJS(url('assets/jscripts/responsivevoice.js'));
        }
        $this->_addHeaderFiles();
        $username = $this->core->getUserInfo('username', null, true);
        $this->assign['poliklinik'] = $this->db('poliklinik')->where('status', '1')->where('kd_poli', '<>', $this->settings->get('settings.igd'))->toArray();
        $this->assign['dokter']   = $this->db('dokter')->where('status', '1')->toArray();
        $this->assign['penjab']   = $this->db('penjab')->where('status', '1')->toArray();
        $this->assign['no_rawat'] = '';
        $this->assign['no_reg']     = '';
        $this->assign['tgl_registrasi']= date('Y-m-d');
        $this->assign['jam_reg']= date('H:i:s');

        $poliklinik = str_replace(",","','", $this->core->getUserInfo('cap', null, true));
        $igd = $this->settings('settings', 'igd');
        $sql = "SELECT reg_periksa.*,
            pasien.*,
            dokter.*,
            poliklinik.*,
            penjab.*
          FROM reg_periksa, pasien, dokter, poliklinik, penjab
          WHERE reg_periksa.no_rkm_medis = pasien.no_rkm_medis
          AND reg_periksa.kd_poli != '$igd'
          AND reg_periksa.tgl_registrasi BETWEEN '$tgl_kunjungan' AND '$tgl_kunjungan_akhir'
          AND reg_periksa.kd_dokter = dokter.kd_dokter
          AND reg_periksa.kd_poli = poliklinik.kd_poli
          AND reg_periksa.kd_pj = penjab.kd_pj";

        if ($username != '197307171998032008') {
          if (!in_array($this->core->getUserInfo('role'), ['admin','apoteker','laboratorium','radiologi','manajemen', 'ok'],true)) {
            $sql .= " AND reg_periksa.kd_poli IN ('$poliklinik')";
          }
        }
        if($status_periksa == 'belum') {
          $sql .= " AND reg_periksa.stts = 'Belum'";
        }
        if($status_periksa == 'selesai') {
          $sql .= " AND reg_periksa.stts = 'Sudah'";
        }
        if($status_periksa == 'lunas') {
          $sql .= " AND reg_periksa.status_bayar = 'Sudah Bayar'";
        }

        $stmt = $this->db()->pdo()->prepare($sql);
        $stmt->execute();
        $rows = $stmt->fetchAll();

        $this->assign['list'] = [];
        foreach ($rows as $row) {
          $cek_kronis = $this->db('mlite_veronisa')->where('no_rawat', $row['no_rawat'])->oneArray();
          $row['kronis'] = $cek_kronis['no_rawat'];
          $this->assign['list'][] = $row;
        }

        if (isset($_POST['no_rawat'])){
          $this->assign['reg_periksa'] = $this->db('reg_periksa')
            ->join('pasien', 'pasien.no_rkm_medis=reg_periksa.no_rkm_medis')
            ->join('poliklinik', 'poliklinik.kd_poli=reg_periksa.kd_poli')
            ->join('dokter', 'dokter.kd_dokter=reg_periksa.kd_dokter')
            ->join('penjab', 'penjab.kd_pj=reg_periksa.kd_pj')
            ->where('no_rawat', $_POST['no_rawat'])
            ->oneArray();
        } else {
          $this->assign['reg_periksa'] = [
            'no_rkm_medis' => '',
            'nm_pasien' => '',
            'no_reg' => '',
            'no_rawat' => '',
            'tgl_registrasi' => '',
            'jam_reg' => '',
            'kd_dokter' => '',
            'no_rm' => '',
            'kd_poli' => '',
            'p_jawab' => '',
            'almt_pj' => '',
            'hubunganpj' => '',
            'biaya_reg' => '',
            'stts' => '',
            'stts_daftar' => '',
            'status_lanjut' => '',
            'kd_pj' => '',
            'umurdaftar' => '',
            'sttsumur' => '',
            'status_bayar' => '',
            'status_poli' => '',
            'nm_pasien' => '',
            'tgl_lahir' => '',
            'jk' => '',
            'alamat' => '',
            'no_tlp' => '',
            'pekerjaan' => ''
          ];
        }
    }

    public function anyForm()
    {

      $this->assign['poliklinik'] = $this->db('poliklinik')->where('status', '1')->toArray();
      $this->assign['dokter'] = $this->db('dokter')->where('status', '1')->toArray();
      $this->assign['penjab'] = $this->db('penjab')->where('status', '1')->toArray();
      $this->assign['no_rawat'] = '';
      $this->assign['no_reg']     = '';
      $this->assign['tgl_registrasi']= date('Y-m-d');
      $this->assign['jam_reg']= date('H:i:s');
      if (isset($_POST['no_rawat'])){
        $this->assign['reg_periksa'] = $this->db('reg_periksa')
          ->join('pasien', 'pasien.no_rkm_medis=reg_periksa.no_rkm_medis')
          ->join('poliklinik', 'poliklinik.kd_poli=reg_periksa.kd_poli')
          ->join('dokter', 'dokter.kd_dokter=reg_periksa.kd_dokter')
          ->join('penjab', 'penjab.kd_pj=reg_periksa.kd_pj')
          ->where('no_rawat', $_POST['no_rawat'])
          ->oneArray();
        echo $this->draw('form.html', [
          'rawat_jalan' => $this->assign
        ]);
      } else {
        $this->assign['reg_periksa'] = [
          'no_rkm_medis' => '',
          'nm_pasien' => '',
          'no_reg' => '',
          'no_rawat' => '',
          'tgl_registrasi' => '',
          'jam_reg' => '',
          'kd_dokter' => '',
          'no_rm' => '',
          'kd_poli' => '',
          'p_jawab' => '',
          'almt_pj' => '',
          'hubunganpj' => '',
          'biaya_reg' => '',
          'stts' => '',
          'stts_daftar' => '',
          'status_lanjut' => '',
          'kd_pj' => '',
          'umurdaftar' => '',
          'sttsumur' => '',
          'status_bayar' => '',
          'status_poli' => '',
          'nm_pasien' => '',
          'tgl_lahir' => '',
          'jk' => '',
          'alamat' => '',
          'no_tlp' => '',
          'pekerjaan' => ''
        ];
        echo $this->draw('form.html', [
          'rawat_jalan' => $this->assign
        ]);
      }
      exit();
    }

    public function anyStatusDaftar()
    {
      if(isset($_POST['no_rkm_medis'])) {
        $rawat = $this->db('reg_periksa')
          ->where('no_rkm_medis', $_POST['no_rkm_medis'])
          ->where('status_bayar', 'Belum Bayar')
          ->limit(1)
          ->oneArray();
          if($rawat) {
            $stts_daftar = "Transaksi tanggal ".date('Y-m-d', strtotime($rawat['tgl_registrasi']))." belum diselesaikan" ;
            $stts_daftar_hidden = $stts_daftar;
            if($this->settings->get('settings.cekstatusbayar') == 'false'){
              $stts_daftar_hidden = 'Lama';
            }
            $bg_status = 'has-error';
          } else {
            $result = $this->db('reg_periksa')->where('no_rkm_medis', $_POST['no_rkm_medis'])->oneArray();
            if($result >= 1) {
              $stts_daftar = 'Lama';
              $bg_status = 'has-info';
              $stts_daftar_hidden = $stts_daftar;
            } else {
              $stts_daftar = 'Baru';
              $bg_status = 'has-success';
              $stts_daftar_hidden = $stts_daftar;
            }
          }
        echo $this->draw('stts.daftar.html', ['stts_daftar' => $stts_daftar, 'stts_daftar_hidden' => $stts_daftar_hidden, 'bg_status' =>$bg_status]);
      } else {
        $rawat = $this->db('reg_periksa')
          ->where('no_rawat', $_POST['no_rawat'])
          ->oneArray();
        echo $this->draw('stts.daftar.html', ['stts_daftar' => $rawat['stts_daftar']]);
      }
      exit();
    }

    public function postSave()
    {
      if ($_POST['tgl_registrasi'] > date('Y-m-d')) {
        $this->db('booking_registrasi')->save([
          'tanggal_booking' => date('Y-m-d'),
          'jam_booking' => date('H:i:s'),
          'no_rkm_medis' => $_POST['no_rkm_medis'],
          'tanggal_periksa' => $_POST['tgl_registrasi'],
          'kd_dokter' => $_POST['kd_dokter'],
          'kd_poli' => $_POST['kd_poli'],
          'no_reg' => $this->core->setNoReg($_POST['kd_dokter'], $_POST['kd_poli']),
          'kd_pj' => $_POST['kd_pj'],
          'limit_reg' => '0',
          'waktu_kunjungan' => $_POST['jam_reg'],
          'status' => 'Belum'
        ]);
      } else if (!$this->db('reg_periksa')->where('no_rawat', $_POST['no_rawat'])->oneArray()) {

        $_POST['status_lanjut'] = 'Ralan';
        $_POST['stts'] = 'Belum';
        $_POST['status_bayar'] = 'Belum Bayar';
        $_POST['p_jawab'] = '-';
        $_POST['almt_pj'] = '-';
        $_POST['hubunganpj'] = '-';

        $poliklinik = $this->db('poliklinik')->where('kd_poli', $_POST['kd_poli'])->oneArray();

        $_POST['biaya_reg'] = $poliklinik['registrasi'];

        $pasien = $this->db('pasien')->where('no_rkm_medis', $_POST['no_rkm_medis'])->oneArray();

      	$birthDate = new \DateTime($pasien['tgl_lahir']);
      	$today = new \DateTime("today");
      	$umur_daftar = "0";
        $status_umur = 'Hr';
        if ($birthDate < $today) {
        	$y = $today->diff($birthDate)->y;
        	$m = $today->diff($birthDate)->m;
        	$d = $today->diff($birthDate)->d;
          $umur_daftar = $d;
          $status_umur = "Hr";
          if($y !='0'){
            $umur_daftar = $y;
            $status_umur = "Th";
          }
          if($y =='0' && $m !='0'){
            $umur_daftar = $m;
            $status_umur = "Bl";
          }
        }

        $_POST['umurdaftar'] = $umur_daftar;
        $_POST['sttsumur'] = $status_umur;
        $_POST['status_poli'] = 'Lama';

        $query = $this->db('reg_periksa')->save($_POST);
      } else {
        $query = $this->db('reg_periksa')->where('no_rawat', $_POST['no_rawat'])->save([
          'kd_poli' => $_POST['kd_poli'],
          'kd_dokter' => $_POST['kd_dokter'],
          'kd_pj' => $_POST['kd_pj']
        ]);
      }

      if($query) {
        $data['status'] = 'success';
        echo json_encode($data);
      } else {
        $data['status'] = 'error';
        echo json_encode($data);
      }

      exit();
    }

    public function anyBooking($page = 1)
    {

      $this->core->addCSS(url('assets/css/jquery-ui.css'));
      $this->core->addCSS(url('assets/css/jquery.timepicker.css'));

      // JS
      $this->core->addJS(url('assets/jscripts/jquery-ui.js'), 'footer');
      $this->core->addJS(url('assets/jscripts/jquery.timepicker.js'), 'footer');

      $waapitoken =  $this->settings->get('settings.waapitoken');
      $waapiphonenumber =  $this->settings->get('settings.waapiphonenumber');
      $nama_instansi =  $this->settings->get('settings.nama_instansi');

      if (isset($_POST['valid'])) {
          if (isset($_POST['no_rkm_medis']) && !empty($_POST['no_rkm_medis'])) {
              foreach ($_POST['no_rkm_medis'] as $item) {

                  $row = $this->db('booking_registrasi')->where('no_rkm_medis', $item)->where('tanggal_periksa', date('Y-m-d'))->oneArray();

                  $cek_stts_daftar = $this->db('reg_periksa')->where('no_rkm_medis', $item)->count();
                  $_POST['stts_daftar'] = 'Baru';
                  if($cek_stts_daftar > 0) {
                    $_POST['stts_daftar'] = 'Lama';
                  }

                  $biaya_reg = $this->db('poliklinik')->where('kd_poli', $row['kd_poli'])->oneArray();
                  $_POST['biaya_reg'] = $biaya_reg['registrasi'];
                  if($_POST['stts_daftar'] == 'Lama') {
                    $_POST['biaya_reg'] = $biaya_reg['registrasilama'];
                  }

                  $cek_status_poli = $this->db('reg_periksa')->where('no_rkm_medis', $item)->where('kd_poli', $row['kd_poli'])->count();
                  $_POST['status_poli'] = 'Baru';
                  if($cek_status_poli > 0) {
                    $_POST['status_poli'] = 'Lama';
                  }

                  // set umur
                  $tanggal = new \DateTime($this->getPasienInfo('tgl_lahir', $item));
                  $today = new \DateTime(date('Y-m-d'));
                  $y = $today->diff($tanggal)->y;
                  $m = $today->diff($tanggal)->m;
                  $d = $today->diff($tanggal)->d;

                  $umur="0";
                  $sttsumur="Th";
                  if($y>0){
                      $umur=$y;
                      $sttsumur="Th";
                  }else if($y==0){
                      if($m>0){
                          $umur=$m;
                          $sttsumur="Bl";
                      }else if($m==0){
                          $umur=$d;
                          $sttsumur="Hr";
                      }
                  }

                  if($row['status'] == 'Belum') {
                    $insert = $this->db('reg_periksa')
                      ->save([
                        'no_reg' => $row['no_reg'],
                        'no_rawat' => $this->setNoRawat(),
                        'tgl_registrasi' => date('Y-m-d'),
                        'jam_reg' => date('H:i:s'),
                        'kd_dokter' => $row['kd_dokter'],
                        'no_rkm_medis' => $item,
                        'kd_poli' => $row['kd_poli'],
                        'p_jawab' => $this->getPasienInfo('namakeluarga', $item),
                        'almt_pj' => $this->getPasienInfo('alamatpj', $item),
                        'hubunganpj' => $this->getPasienInfo('keluarga', $item),
                        'biaya_reg' => $_POST['biaya_reg'],
                        'stts' => 'Belum',
                        'stts_daftar' => $_POST['stts_daftar'],
                        'status_lanjut' => 'Ralan',
                        'kd_pj' => $row['kd_pj'],
                        'umurdaftar' => $umur,
                        'sttsumur' => $sttsumur,
                        'status_bayar' => 'Belum Bayar',
                        'status_poli' => $_POST['status_poli']
                      ]);

                      if ($insert) {
                          $this->db('booking_registrasi')->where('no_rkm_medis', $item)->where('tanggal_periksa', date('Y-m-d'))->update('status', 'Terdaftar');
                          $this->notify('success', 'Validasi sukses');
                      } else {
                          $this->notify('failure', 'Validasi gagal');
                      }
                  }
              }

              redirect(url([ADMIN, 'rawat_jalan', 'booking']));
          }
      }

      $this->_addHeaderFiles();
      $start_date = date('Y-m-d');
      if(isset($_GET['start_date']) && $_GET['start_date'] !='')
        $start_date = $_GET['start_date'];
      $end_date = date('Y-m-d');
      if(isset($_GET['end_date']) && $_GET['end_date'] !='')
        $end_date = $_GET['end_date'];
      $perpage = '10';
      $phrase = '';
      if(isset($_GET['s']))
        $phrase = $_GET['s'];

      // pagination
      $totalRecords = $this->db()->pdo()->prepare("SELECT booking_registrasi.no_rkm_medis FROM booking_registrasi, pasien WHERE booking_registrasi.no_rkm_medis = pasien.no_rkm_medis AND (booking_registrasi.no_rkm_medis LIKE ? OR pasien.nm_pasien LIKE ?) AND booking_registrasi.tanggal_periksa BETWEEN '$start_date' AND '$end_date'");
      $totalRecords->execute(['%'.$phrase.'%', '%'.$phrase.'%']);
      $totalRecords = $totalRecords->fetchAll();

      $pagination = new \Systems\Lib\Pagination($page, count($totalRecords), $perpage, url([ADMIN, 'rawat_jalan', 'booking', '%d?s='.$phrase.'&start_date='.$start_date.'&end_date='.$end_date]));
      $this->assign['pagination'] = $pagination->nav('pagination','5');
      $this->assign['totalRecords'] = $totalRecords;

      $offset = $pagination->offset();
      $query = $this->db()->pdo()->prepare("SELECT booking_registrasi.*, pasien.nm_pasien, pasien.alamat, pasien.no_tlp, dokter.nm_dokter, poliklinik.nm_poli, penjab.png_jawab, pasien.no_peserta FROM booking_registrasi, pasien, dokter, poliklinik, penjab WHERE booking_registrasi.no_rkm_medis = pasien.no_rkm_medis AND booking_registrasi.kd_dokter = dokter.kd_dokter AND booking_registrasi.kd_poli = poliklinik.kd_poli AND booking_registrasi.kd_pj = penjab.kd_pj AND (booking_registrasi.no_rkm_medis LIKE ? OR pasien.nm_pasien LIKE ?) AND booking_registrasi.tanggal_periksa BETWEEN '$start_date' AND '$end_date' LIMIT $perpage OFFSET $offset");
      $query->execute(['%'.$phrase.'%', '%'.$phrase.'%']);
      $rows = $query->fetchAll();

      $this->assign['list'] = [];
      if (count($rows)) {
          foreach ($rows as $row) {
              $row = htmlspecialchars_array($row);
              $this->assign['list'][] = $row;
          }
      }

      $this->assign['searchUrl'] =  url([ADMIN, 'rawat_jalan', 'booking', $page.'?s='.$phrase.'&start_date='.$start_date.'&end_date='.$end_date]);
      return $this->draw('booking.html', ['booking' => $this->assign, 'waapitoken' => $waapitoken, 'waapiphonenumber' => $waapiphonenumber, 'nama_instansi' => $nama_instansi]);

    }

    public function getBookingPeriksa()
    {
        $date = date('Y-m-d');
        $text = 'Booking Periksa';

        // CSS
        $this->core->addCSS(url('assets/css/jquery-ui.css'));
        $this->core->addCSS(url('assets/css/dataTables.bootstrap.min.css'));
        // JS
        $this->core->addJS(url('assets/jscripts/jquery-ui.js'), 'footer');
        $this->core->addJS(url('assets/jscripts/jquery.dataTables.min.js'), 'footer');
        $this->core->addJS(url('assets/jscripts/dataTables.bootstrap.min.js'), 'footer');

        return $this->draw('booking.periksa.html',
          [
            'text' => $text,
            'waapitoken' => $this->settings->get('settings.waapitoken'),
            'waapiphonenumber' => $this->settings->get('settings.waapiphonenumber'),
            'nama_instansi' => $this->settings->get('settings.nama_instansi'),
            'booking' => $this->db('booking_periksa')
              ->select([
                'no_booking' => 'booking_periksa.no_booking',
                'tanggal' => 'booking_periksa.tanggal',
                'nama' => 'booking_periksa.nama',
                'no_telp' => 'booking_periksa.no_telp',
                'alamat' => 'booking_periksa.alamat',
                'email' => 'booking_periksa.email',
                'nm_poli' => 'poliklinik.nm_poli',
                'status' => 'booking_periksa.status',
                'tanggal_booking' => 'booking_periksa.tanggal_booking'
              ])
              ->join('poliklinik', 'poliklinik.kd_poli = booking_periksa.kd_poli')
              //->where('tambahan_pesan', 'jkn_mobile_v2')
              ->toArray()
          ]
        );
    }

    public function postSaveBookingPeriksa()
    {
      $this->db('booking_periksa')->where('no_booking', $_POST['no_booking'])->save(['status' => $_POST['status']]);
      $this->db('booking_periksa_balasan')
      ->save([
        'no_booking' => $_POST['no_booking'],
        'balasan' => $_POST['message']
      ]);
      exit();
    }

    public function anyKontrol()
    {
      $rows = $this->db('booking_registrasi')
        ->join('poliklinik', 'poliklinik.kd_poli=booking_registrasi.kd_poli')
        ->join('dokter', 'dokter.kd_dokter=booking_registrasi.kd_dokter')
        ->join('penjab', 'penjab.kd_pj=booking_registrasi.kd_pj')
        ->where('no_rkm_medis', $_POST['no_rkm_medis'])
        ->toArray();
      $i = 1;
      $result = [];
      foreach ($rows as $row) {
        $row['nomor'] = $i++;
        $result[] = $row;
      }
      echo $this->draw('kontrol.html', ['booking_registrasi' => $result]);
      exit();
    }

    public function postSaveKontrol()
    {

      $query = $this->db('skdp_bpjs')->save([
        'tahun' => date('Y'),
        'no_rkm_medis' => $_POST['no_rkm_medis'],
        'diagnosa' => $_POST['diagnosa'],
        'terapi' => $_POST['terapi'],
        'alasan1' => $_POST['alasan1'],
        'alasan2' => '',
        'rtl1' => $_POST['rtl1'],
        'rtl2' => '',
        'tanggal_datang' => $_POST['tanggal_datang'],
        'tanggal_rujukan' => $_POST['tanggal_rujukan'],
        'no_antrian' => $this->core->setNoSKDP(),
        'kd_dokter' => $_POST['dokter'],
        'status' => 'Menunggu'
      ]);

      if ($query) {
        $this->db('booking_registrasi')
          ->save([
            'tanggal_booking' => date('Y-m-d'),
            'jam_booking' => date('H:i:s'),
            'no_rkm_medis' => $_POST['no_rkm_medis'],
            'tanggal_periksa' => $_POST['tanggal_datang'],
            'kd_dokter' => $_POST['dokter'],
            'kd_poli' => $_POST['poli'],
            'no_reg' => $this->core->setNoBooking($this->core->getUserInfo('username', null, true), $_POST['tanggal_datang']),
            'kd_pj' => $this->core->getRegPeriksaInfo('kd_pj', $_POST['no_rawat']),
            'limit_reg' => 0,
            'waktu_kunjungan' => $_POST['tanggal_datang'].' '.date('H:i:s'),
            'status' => 'Belum'
          ]);
      }

      exit();
    }

    public function postHapusKontrol()
    {
      $this->db('booking_registrasi')->where('kd_dokter', $_POST['kd_dokter'])->where('no_rkm_medis', $_POST['no_rkm_medis'])->where('tanggal_periksa', $_POST['tanggal_periksa'])->where('status', 'Belum')->delete();
      $this->db('skdp_bpjs')->where('kd_dokter', $_POST['kd_dokter'])->where('no_rkm_medis', $_POST['no_rkm_medis'])->where('tanggal_datang', $_POST['tanggal_periksa'])->where('status', 'Menunggu')->delete();
      exit();
    }

    public function getJadwal()
    {
        // JS
        $this->core->addJS(url('assets/jscripts/jquery-ui.js'), 'footer');
        $this->core->addJS(url('assets/jscripts/jquery.timepicker.js'), 'footer');
        $this->_addHeaderFiles();
        $rows = $this->db('jadwal')->join('dokter', 'dokter.kd_dokter = jadwal.kd_dokter')->join('poliklinik', 'poliklinik.kd_poli = jadwal.kd_poli')->toArray();
        $this->assign['jadwal'] = [];
        foreach ($rows as $row) {
            $row['delURL'] = url([ADMIN, 'rawat_jalan', 'jadwaldel', $row['kd_dokter'], $row['hari_kerja']]);
            $row['editURL'] = url([ADMIN, 'rawat_jalan', 'jadwaledit', $row['kd_dokter'], $row['hari_kerja']]);
            $this->assign['jadwal'][] = $row;
        }

        return $this->draw('jadwal.html', ['pendaftaran' => $this->assign]);
    }

    public function getJadwalDel($kd_dokter, $hari_kerja)
    {
        if ($pendaftaran = $this->db('jadwal')->where('kd_dokter', $kd_dokter)->where('hari_kerja', $hari_kerja)->oneArray()) {
            if ($this->db('jadwal')->where('kd_dokter', $kd_dokter)->where('hari_kerja', $hari_kerja)->delete()) {
                $this->notify('success', 'Hapus sukses');
            } else {
                $this->notify('failure', 'Hapus gagal');
            }
        }
        redirect(url([ADMIN, 'rawat_jalan', 'jadwal']));
    }

    public function getJadwalAdd()
    {
        $this->core->addCSS(url('assets/css/jquery-ui.css'));
        $this->core->addCSS(url('assets/css/jquery.timepicker.css'));

        // JS
        $this->core->addJS(url('assets/jscripts/jquery-ui.js'), 'footer');
        $this->core->addJS(url('assets/jscripts/jquery.timepicker.js'), 'footer');
        $this->_addHeaderFiles();
        if (!empty($redirectData = getRedirectData())) {
            $this->assign['form'] = filter_var_array($redirectData, FILTER_SANITIZE_STRING);
        } else {
            $this->assign['form'] = [
              'kd_dokter' => '',
              'hari_kerja' => '',
              'jam_mulai' => '',
              'jam_selesai' => '',
              'kd_poli' => '',
              'kuota' => ''
            ];
        }
        $this->assign['title'] = 'Tambah Jadwal Dokter';
        $this->assign['dokter'] = $this->db('dokter')->toArray();
        $this->assign['poliklinik'] = $this->db('poliklinik')->toArray();
        $this->assign['hari_kerja'] = $this->getEnum('jadwal', 'hari_kerja');
        $this->assign['postUrl'] = url([ADMIN, 'rawat_jalan', 'jadwalsave', $this->assign['form']['kd_dokter'], $this->assign['form']['hari_kerja']]);
        return $this->draw('jadwal.form.html', ['pendaftaran' => $this->assign]);
    }

    public function getJadwalEdit($id, $hari_kerja)
    {
        $this->core->addCSS(url('assets/css/jquery-ui.css'));
        $this->core->addCSS(url('assets/css/jquery.timepicker.css'));

        // JS
        $this->core->addJS(url('assets/jscripts/jquery-ui.js'), 'footer');
        $this->core->addJS(url('assets/jscripts/jquery.timepicker.js'), 'footer');
        $this->_addHeaderFiles();
        $row = $this->db('jadwal')->where('kd_dokter', $id)->where('hari_kerja', $hari_kerja)->oneArray();
        if (!empty($row)) {
            $this->assign['form'] = $row;
            $this->assign['title'] = 'Edit Jadwal';
            $this->assign['hari_kerja'] = $this->getEnum('jadwal', 'hari_kerja');
            $this->assign['dokter'] = $this->db('dokter')->toArray();
            $this->assign['poliklinik'] = $this->db('poliklinik')->toArray();

            $this->assign['postUrl'] = url([ADMIN, 'rawat_jalan', 'jadwalsave', $this->assign['form']['kd_dokter'], $this->assign['form']['hari_kerja']]);
            return $this->draw('jadwal.form.html', ['pendaftaran' => $this->assign]);
        } else {
            redirect(url([ADMIN, 'rawat_jalan', 'jadwal']));
        }
    }

    public function postJadwalSave($id = null, $hari_kerja = null)
    {
        $errors = 0;

        if (!$id) {
            $location = url([ADMIN, 'rawat_jalan', 'jadwal']);
        } else {
            $location = url([ADMIN, 'rawat_jalan', 'jadwaledit', $_POST['kd_dokter'], $_POST['hari_kerja']]);
        }

        if (checkEmptyFields(['kd_dokter', 'hari_kerja', 'kd_poli'], $_POST)) {
            $this->notify('failure', 'Isian kosong');
            redirect($location, $_POST);
        }

        if (!$errors) {
            unset($_POST['save']);

            if (!$id) {    // new
                $query = $this->db('jadwal')->save($_POST);
            } else {        // edit
                $query = $this->db('jadwal')->where('kd_dokter', $id)->where('hari_kerja', $hari_kerja)->save($_POST);
            }

            if ($query) {
                $this->notify('success', 'Simpan sukes');
            } else {
                $this->notify('failure', 'Simpan gagal');
            }

            redirect($location);
        }

        redirect($location, $_POST);
    }

    public function postStatusRawat()
    {
      $datetime = date('Y-m-d H:i:s');
      $cek = $this->db('mutasi_berkas')->where('no_rawat', $_POST['no_rawat'])->oneArray();
      if($_POST['stts'] == 'Berkas Dikirim') {
          if(!$this->db('mutasi_berkas')->where('no_rawat', $_POST['no_rawat'])->oneArray()) {
            $this->db('mutasi_berkas')->save([
              'no_rawat' => $_POST['no_rawat'],
              'status' => 'Sudah Dikirim',
              'dikirim' => $datetime,
              'diterima' => '0000-00-00 00:00:00',
              'kembali' => '0000-00-00 00:00:00',
              'tidakada' => '0000-00-00 00:00:00',
              'ranap' => '0000-00-00 00:00:00'
            ]);
          }
      } else if ($_POST['stts'] == 'Berkas Diterima') {
          if(!$this->db('mutasi_berkas')->where('no_rawat', $_POST['no_rawat'])->oneArray()) {
            $this->db('mutasi_berkas')->save([
              'no_rawat' => $_POST['no_rawat'],
              'status' => 'Sudah Diterima',
              'dikirim' => $datetime,
              'diterima' => $datetime,
              'kembali' => '0000-00-00 00:00:00',
              'tidakada' => '0000-00-00 00:00:00',
              'ranap' => '0000-00-00 00:00:00'
            ]);
          } else {
            $this->db('mutasi_berkas')->where('no_rawat', $_POST['no_rawat'])->save([
              'status' => 'Sudah Diterima',
              'diterima' => $datetime
            ]);
          }
          $this->db('reg_periksa')->where('no_rawat', $_POST['no_rawat'])->save($_POST);
      } else {
          $this->db('reg_periksa')->where('no_rawat', $_POST['no_rawat'])->save($_POST);
      }
      exit();
    }

    public function postStatusLanjut()
    {
      $this->db('reg_periksa')->where('no_rawat', $_POST['no_rawat'])->save([
        'status_lanjut' => 'Ranap'
      ]);
      exit();
    }

    public function anyPasien()
    {
      if(isset($_POST['cari'])) {
        $pasien = $this->db('pasien')
          ->like('no_rkm_medis', '%'.$_POST['cari'].'%')
          ->orLike('nm_pasien', '%'.$_POST['cari'].'%')
          ->asc('no_rkm_medis')
          ->limit(5)
          ->toArray();
      }
      echo $this->draw('pasien.html', ['pasien' => $pasien]);
      exit();
    }

    public function getAntrian()
    {
      $settings = $this->settings('settings');
      $this->tpl->set('settings', $this->tpl->noParse_array(htmlspecialchars_array($settings)));
      $rawat_jalan = $this->db('reg_periksa')
        ->join('pasien', 'pasien.no_rkm_medis=reg_periksa.no_rkm_medis')
        ->join('poliklinik', 'poliklinik.kd_poli=reg_periksa.kd_poli')
        ->join('dokter', 'dokter.kd_dokter=reg_periksa.kd_dokter')
        ->join('penjab', 'penjab.kd_pj=reg_periksa.kd_pj')
        ->where('no_rawat', $_GET['no_rawat'])
        ->oneArray();
      echo $this->draw('antrian.html', ['rawat_jalan' => $rawat_jalan]);
      exit();
    }

    public function postHapus()
    {
      $this->db('reg_periksa')->where('no_rawat', $_POST['no_rawat'])->delete();
      exit();
    }

    public function postSaveDetail()
    {
      if($_POST['kat'] == 'tindakan') {
        $jns_perawatan = $this->db('jns_perawatan')->where('kd_jenis_prw', $_POST['kd_jenis_prw'])->oneArray();
        if($_POST['provider'] == 'rawat_jl_dr') {
          $this->db('rawat_jl_dr')->save([
            'no_rawat' => $_POST['no_rawat'],
            'kd_jenis_prw' => $_POST['kd_jenis_prw'],
            'kd_dokter' => $_POST['kode_provider'],
            'tgl_perawatan' => $_POST['tgl_perawatan'],
            'jam_rawat' => $_POST['jam_rawat'],
            'material' => $jns_perawatan['material'],
            'bhp' => $jns_perawatan['bhp'],
            'tarif_tindakandr' => $jns_perawatan['tarif_tindakandr'],
            'kso' => $jns_perawatan['kso'],
            'menejemen' => $jns_perawatan['menejemen'],
            'biaya_rawat' => $jns_perawatan['total_byrdr'],
            'stts_bayar' => 'Belum'
          ]);
        }
        if($_POST['provider'] == 'rawat_jl_pr') {
          $this->db('rawat_jl_pr')->save([
            'no_rawat' => $_POST['no_rawat'],
            'kd_jenis_prw' => $_POST['kd_jenis_prw'],
            'nip' => $_POST['kode_provider2'],
            'tgl_perawatan' => $_POST['tgl_perawatan'],
            'jam_rawat' => $_POST['jam_rawat'],
            'material' => $jns_perawatan['material'],
            'bhp' => $jns_perawatan['bhp'],
            'tarif_tindakanpr' => $jns_perawatan['tarif_tindakanpr'],
            'kso' => $jns_perawatan['kso'],
            'menejemen' => $jns_perawatan['menejemen'],
            'biaya_rawat' => $jns_perawatan['total_byrdr'],
            'stts_bayar' => 'Belum'
          ]);
        }
        if($_POST['provider'] == 'rawat_jl_drpr') {
          $this->db('rawat_jl_drpr')->save([
            'no_rawat' => $_POST['no_rawat'],
            'kd_jenis_prw' => $_POST['kd_jenis_prw'],
            'kd_dokter' => $_POST['kode_provider'],
            'nip' => $_POST['kode_provider2'],
            'tgl_perawatan' => $_POST['tgl_perawatan'],
            'jam_rawat' => $_POST['jam_rawat'],
            'material' => $jns_perawatan['material'],
            'bhp' => $jns_perawatan['bhp'],
            'tarif_tindakandr' => $jns_perawatan['tarif_tindakandr'],
            'tarif_tindakanpr' => $jns_perawatan['tarif_tindakanpr'],
            'kso' => $jns_perawatan['kso'],
            'menejemen' => $jns_perawatan['menejemen'],
            'biaya_rawat' => $jns_perawatan['total_byrdr'],
            'stts_bayar' => 'Belum'
          ]);
        }
      }
      if($_POST['kat'] == 'laboratorium') {
        $cek_lab = $this->db('permintaan_lab')->where('no_rawat', $_POST['no_rawat'])->where('tgl_permintaan', date('Y-m-d'))->oneArray();
        if(!$cek_lab) {
          $max_id = $this->db('permintaan_lab')->select(['noorder' => 'ifnull(MAX(CONVERT(RIGHT(noorder,4),signed)),0)'])->where('tgl_permintaan', date('Y-m-d'))->oneArray();
          if(empty($max_id['noorder'])) {
            $max_id['noorder'] = '0000';
          }
          $_next_noorder = sprintf('%04s', ($max_id['noorder'] + 1));
          $noorder = 'PL'.date('Ymd').''.$_next_noorder;

          $permintaan_lab = $this->db('permintaan_lab')
            ->save([
              'noorder' => $noorder,
              'no_rawat' => $_POST['no_rawat'],
              'tgl_permintaan' => $_POST['tgl_perawatan'],
              'jam_permintaan' => $_POST['jam_rawat'],
              'tgl_sampel' => '0000-00-00',
              'jam_sampel' => '00:00:00',
              'tgl_hasil' => '0000-00-00',
              'jam_hasil' => '00:00:00',
              'dokter_perujuk' => $_POST['kode_perujuk'],
              'status' => 'ralan'
            ]);
          $this->db('permintaan_pemeriksaan_lab')
            ->save([
              'noorder' => $noorder,
              'kd_jenis_prw' => $_POST['kd_jenis_prw'],
              'stts_bayar' => 'Belum'
            ]);

        } else {
          $noorder = $cek_lab['noorder'];
          $this->db('permintaan_pemeriksaan_lab')
            ->save([
              'noorder' => $noorder,
              'kd_jenis_prw' => $_POST['kd_jenis_prw'],
              'stts_bayar' => 'Belum'
            ]);
        }
      }

      if($_POST['kat'] == 'radiologi') {
        $cek_rad = $this->db('permintaan_radiologi')->where('no_rawat', $_POST['no_rawat'])->where('tgl_permintaan', date('Y-m-d'))->oneArray();
        if(!$cek_rad) {
          $max_id = $this->db('permintaan_radiologi')->select(['noorder' => 'ifnull(MAX(CONVERT(RIGHT(noorder,4),signed)),0)'])->where('tgl_permintaan', date('Y-m-d'))->oneArray();
          if(empty($max_id['noorder'])) {
            $max_id['noorder'] = '0000';
          }
          $_next_noorder = sprintf('%04s', ($max_id['noorder'] + 1));
          $noorder = 'PR'.date('Ymd').''.$_next_noorder;

          $permintaan_rad = $this->db('permintaan_radiologi')
            ->save([
              'noorder' => $noorder,
              'no_rawat' => $_POST['no_rawat'],
              'tgl_permintaan' => $_POST['tgl_perawatan'],
              'jam_permintaan' => $_POST['jam_rawat'],
              'tgl_sampel' => '0000-00-00',
              'jam_sampel' => '00:00:00',
              'tgl_hasil' => '0000-00-00',
              'jam_hasil' => '00:00:00',
              'dokter_perujuk' => $_POST['kode_perujuk'],
              'status' => 'ralan'
            ]);
          $this->db('permintaan_pemeriksaan_radiologi')
            ->save([
              'noorder' => $noorder,
              'kd_jenis_prw' => $_POST['kd_jenis_prw'],
              'stts_bayar' => 'Belum'
            ]);
            $this->db('diagnosa_pasien_klinis')
            ->save([
              'noorder' => $noorder,
              'klinis' => $_POST['diagnosa_klinis']
            ]);

        } else {
          $noorder = $cek_rad['noorder'];
          $this->db('permintaan_pemeriksaan_radiologi')
            ->save([
              'noorder' => $noorder,
              'kd_jenis_prw' => $_POST['kd_jenis_prw'],
              'stts_bayar' => 'Belum'
            ]);
             $this->db('diagnosa_pasien_klinis')
            ->save([
              'noorder' => $noorder,
              'klinis' => $_POST['diagnosa_klinis']
            ]);
        }
      }
      exit();
    }

    public function postHapusDetail()
    {
      if($_POST['provider'] == 'rawat_jl_dr') {
        $this->db('rawat_jl_dr')
        ->where('no_rawat', $_POST['no_rawat'])
        ->where('kd_jenis_prw', $_POST['kd_jenis_prw'])
        ->where('tgl_perawatan', $_POST['tgl_perawatan'])
        ->where('jam_rawat', $_POST['jam_rawat'])
        ->delete();
      }
      if($_POST['provider'] == 'rawat_jl_pr') {
        $this->db('rawat_jl_pr')
        ->where('no_rawat', $_POST['no_rawat'])
        ->where('kd_jenis_prw', $_POST['kd_jenis_prw'])
        ->where('tgl_perawatan', $_POST['tgl_perawatan'])
        ->where('jam_rawat', $_POST['jam_rawat'])
        ->delete();
      }
      if($_POST['provider'] == 'rawat_jl_drpr') {
        $this->db('rawat_jl_drpr')
        ->where('no_rawat', $_POST['no_rawat'])
        ->where('kd_jenis_prw', $_POST['kd_jenis_prw'])
        ->where('tgl_perawatan', $_POST['tgl_perawatan'])
        ->where('jam_rawat', $_POST['jam_rawat'])
        ->delete();
      }

      exit();
    }

      public function postHapusLab()
    {

        $this->db('permintaan_pemeriksaan_lab')
        ->where('noorder', $_POST['noorder'])
        ->where('kd_jenis_prw', $_POST['kd_jenis_prw'])
        ->delete();

        $ceklab = $this->db('permintaan_pemeriksaan_lab')->where('noorder', $_POST['noorder'])->oneArray();
        if (empty($ceklab) ){
        $this->db('permintaan_lab')
        ->where('noorder', $_POST['noorder'])
        ->where('no_rawat', $_POST['no_rawat'])
        ->where('tgl_permintaan', $_POST['tgl_permintaan'])
        ->where('jam_permintaan', $_POST['jam_permintaan'])
        ->delete();
        }
      exit();
    }

         public function postHapusRad()
    {
        $this->db('permintaan_pemeriksaan_radiologi')
        ->where('noorder', $_POST['noorder'])
        ->where('kd_jenis_prw', $_POST['kd_jenis_prw'])
        ->delete();

        $cekrad = $this->db('permintaan_pemeriksaan_radiologi')->where('noorder', $_POST['noorder'])->oneArray();
        if (empty($cekrad) ){
        $this->db('permintaan_radiologi')
        ->where('noorder', $_POST['noorder'])
        ->where('no_rawat', $_POST['no_rawat'])
        ->where('tgl_permintaan', $_POST['tgl_permintaan'])
        ->where('jam_permintaan', $_POST['jam_permintaan'])
        ->delete();

         $this->db('diagnosa_pasien_klinis')
        ->where('noorder', $_POST['noorder'])
        ->where('klinis', $_POST['diagnosa_klinis'])
        ->delete();
        }
      exit();
    }

    public function anyRincian()
    {
      $rows_rawat_jl_dr = $this->db('rawat_jl_dr')->where('no_rawat', $_POST['no_rawat'])->toArray();
      $rows_rawat_jl_pr = $this->db('rawat_jl_pr')->where('no_rawat', $_POST['no_rawat'])->toArray();
      $rows_rawat_jl_drpr = $this->db('rawat_jl_drpr')->where('no_rawat', $_POST['no_rawat'])->toArray();

      $jumlah_total = 0;
      $rawat_jl_dr = [];
      $rawat_jl_pr = [];
      $rawat_jl_drpr = [];
      $i = 1;

      if($rows_rawat_jl_dr) {
        foreach ($rows_rawat_jl_dr as $row) {
          $jns_perawatan = $this->db('jns_perawatan')->where('kd_jenis_prw', $row['kd_jenis_prw'])->oneArray();
          $row['nm_perawatan'] = $jns_perawatan['nm_perawatan'];
          $jumlah_total = $jumlah_total + $row['biaya_rawat'];
          $row['provider'] = 'rawat_jl_dr';
          $rawat_jl_dr[] = $row;
        }
      }

      if($rows_rawat_jl_pr) {
        foreach ($rows_rawat_jl_pr as $row) {
          $jns_perawatan = $this->db('jns_perawatan')->where('kd_jenis_prw', $row['kd_jenis_prw'])->oneArray();
          $row['nm_perawatan'] = $jns_perawatan['nm_perawatan'];
          $jumlah_total = $jumlah_total + $row['biaya_rawat'];
          $row['provider'] = 'rawat_jl_pr';
          $rawat_jl_pr[] = $row;
        }
      }

      if($rows_rawat_jl_drpr) {
        foreach ($rows_rawat_jl_drpr as $row) {
          $jns_perawatan = $this->db('jns_perawatan')->where('kd_jenis_prw', $row['kd_jenis_prw'])->oneArray();
          $row['nm_perawatan'] = $jns_perawatan['nm_perawatan'];
          $jumlah_total = $jumlah_total + $row['biaya_rawat'];
          $row['provider'] = 'rawat_jl_drpr';
          $rawat_jl_drpr[] = $row;
        }
      }

       $rows_laboratorium = $this->db('permintaan_lab')->join('permintaan_pemeriksaan_lab', 'permintaan_pemeriksaan_lab.noorder=permintaan_lab.noorder')->where('no_rawat', $_POST['no_rawat'])->toArray();
      $jumlah_total_lab = 0;
      $laboratorium = [];

      if($rows_laboratorium) {
        foreach ($rows_laboratorium as $row) {
          $jns_perawatan = $this->db('jns_perawatan_lab')->where('kd_jenis_prw', $row['kd_jenis_prw'])->oneArray();
          $row['nm_perawatan'] = $jns_perawatan['nm_perawatan'];
          $row['kelas'] = $jns_perawatan['kelas'];
          $row['total_byr'] = $jns_perawatan['total_byr'];
          $jumlah_total_lab += $jns_perawatan['total_byr'];
          $laboratorium[] = $row;
        }
      }

      $rows_radiologi = $this->db('permintaan_radiologi')->join('permintaan_pemeriksaan_radiologi', 'permintaan_pemeriksaan_radiologi.noorder=permintaan_radiologi.noorder')->where('no_rawat', $_POST['no_rawat'])->toArray();
      $jumlah_total_rad = 0;
      $radiologi = [];

      if($rows_radiologi) {
        foreach ($rows_radiologi as $row) {
          $jns_perawatan = $this->db('jns_perawatan_radiologi')->where('kd_jenis_prw', $row['kd_jenis_prw'])->oneArray();
          $row['nm_perawatan'] = $jns_perawatan['nm_perawatan'];
          $row['kelas'] = $jns_perawatan['kelas'];
          $row['total_byr'] = $jns_perawatan['total_byr'];
          $jumlah_total_rad += $jns_perawatan['total_byr'];

          $klinis = $this->db('diagnosa_pasien_klinis')->where('noorder', $row['noorder'])->oneArray();
          $row['diagnosa_klinis'] = $klinis['klinis'];
          $radiologi[] = $row;
        }
      }

      echo $this->draw('rincian.html', [
        'rawat_jl_dr' => $rawat_jl_dr, 
        'rawat_jl_pr' => $rawat_jl_pr, 
        'rawat_jl_drpr' => $rawat_jl_drpr, 
        'laboratorium' => $laboratorium,
        'radiologi' => $radiologi,
        'jumlah_total' => $jumlah_total, 
        'jumlah_total_lab' => $jumlah_total_lab,
        'jumlah_total_rad' => $jumlah_total_rad,
        'no_rawat' => $_POST['no_rawat']]);
      exit();
    }

    public function anySoap()
    {

      $prosedurs = $this->db('prosedur_pasien')
         ->where('no_rawat', $_POST['no_rawat'])
         ->asc('prioritas')
         ->toArray();
       $prosedur = [];
       foreach ($prosedurs as $row) {
         $icd9 = $this->db('icd9')->where('kode', $row['kode'])->oneArray();
         $row['nama'] = $icd9['deskripsi_panjang'];
         $prosedur[] = $row;
       }
       $diagnosas = $this->db('diagnosa_pasien')
         ->where('no_rawat', $_POST['no_rawat'])
         ->asc('prioritas')
         ->toArray();
       $diagnosa = [];
       foreach ($diagnosas as $row) {
         $icd10 = $this->db('penyakit')->where('kd_penyakit', $row['kd_penyakit'])->oneArray();
         $row['nama'] = $icd10['nm_penyakit'];
         $diagnosa[] = $row;
       }

      $rows = $this->db('pemeriksaan_ralan')
        ->where('no_rawat', $_POST['no_rawat'])
        ->toArray();
      $i = 1;
      $row['nama_petugas'] = '';
      $row['departemen_petugas'] = '';
      $result = [];
      foreach ($rows as $row) {
        $row['nomor'] = $i++;
        $row['nama_petugas'] = $this->core->getPegawaiInfo('nama',$row['nip']);
        $row['departemen_petugas'] = $this->core->getDepartemenInfo($this->core->getPegawaiInfo('departemen',$row['nip']));
        $result[] = $row;
      }

      $result_ranap = [];

      $check_table = $this->db()->pdo()->query("SHOW TABLES LIKE 'pemeriksaan_ranap'");
      $check_table->execute();
      $check_table = $check_table->fetch();
      if($check_table) {
        $rows_ranap = $this->db('pemeriksaan_ranap')
          ->where('no_rawat', $_POST['no_rawat'])
          ->toArray();
        foreach ($rows_ranap as $row) {
          $row['nomor'] = $i++;
          $row['nama_petugas'] = $this->core->getPegawaiInfo('nama',$row['nip']);
          $row['departemen_petugas'] = $this->core->getDepartemenInfo($this->core->getPegawaiInfo('departemen',$row['nip']));
          $result_ranap[] = $row;
        }
      }

      echo $this->draw('soap.html', ['pemeriksaan' => $result, 'pemeriksaan_ranap' => $result_ranap, 'diagnosa' => $diagnosa, 'prosedur' => $prosedur, 'admin_mode' => $this->settings->get('settings.admin_mode')]);
      exit();
    }

    public function postSaveSOAP()
    {
      $check_db = $this->db()->pdo()->query("SHOW COLUMNS FROM `pemeriksaan_ralan` LIKE 'instruksi'");
      $check_db->execute();
      $check_db = $check_db->fetch();

      if($check_db) {
        $_POST['nip'] = $this->core->getUserInfo('username', null, true);
      } else {
        unset($_POST['instruksi']);
      }

      if(!$this->db('pemeriksaan_ralan')->where('no_rawat', $_POST['no_rawat'])->where('tgl_perawatan', $_POST['tgl_perawatan'])->where('jam_rawat', $_POST['jam_rawat'])->oneArray()) {
        $this->db('pemeriksaan_ralan')->save($_POST);
      } else {
        $this->db('pemeriksaan_ralan')->where('no_rawat', $_POST['no_rawat'])->where('tgl_perawatan', $_POST['tgl_perawatan'])->where('jam_rawat', $_POST['jam_rawat'])->save($_POST);
      }
      exit();
    }

    public function postHapusSOAP()
    {
      $this->db('pemeriksaan_ralan')->where('no_rawat', $_POST['no_rawat'])->where('tgl_perawatan', $_POST['tgl_perawatan'])->where('jam_rawat', $_POST['jam_rawat'])->delete();
      exit();
    }

    public function anyLayanan()
    {
      $layanan = $this->db('jns_perawatan')
        ->where('status', '1')
        ->like('nm_perawatan', '%'.$_POST['layanan'].'%')
        ->limit(10)
        ->toArray();
      echo $this->draw('layanan.html', ['layanan' => $layanan]);
      exit();
    }

    public function anyLaboratorium()
    {
      $laboratorium = $this->db('jns_perawatan_lab')
        ->where('status', '1')
        ->like('nm_perawatan', '%'.$_POST['laboratorium'].'%')
        ->limit(10)
        ->toArray();
      echo $this->draw('laboratorium.html', ['laboratorium' => $laboratorium]);
      exit();
    }

    public function anyRadiologi()
    {
      $radiologi = $this->db('jns_perawatan_radiologi')
        ->where('status', '1')
        ->like('nm_perawatan', '%'.$_POST['radiologi'].'%')
        ->limit(10)
        ->toArray();
      echo $this->draw('radiologi.html', ['radiologi' => $radiologi]);
      exit();
    }

    public function anyBerkasDigital()
    {
      $berkas_digital = $this->db('berkas_digital_perawatan')->where('no_rawat', $_POST['no_rawat'])->toArray();
      echo $this->draw('berkasdigital.html', ['berkas_digital' => $berkas_digital]);
      exit();
    }

    public function postSaveBerkasDigital()
    {

      $dir    = $this->_uploads;
      $cntr   = 0;

      $image = $_FILES['file']['tmp_name'];
      $img = new \Systems\Lib\Image();
      $id = convertNorawat($_POST['no_rawat']);
      if ($img->load($image)) {
          $imgName = time().$cntr++;
          $imgPath = $dir.'/'.$id.'_'.$imgName.'.'.$img->getInfos('type');
          $lokasi_file = 'pages/upload/'.$id.'_'.$imgName.'.'.$img->getInfos('type');
          $img->save($imgPath);
          $query = $this->db('berkas_digital_perawatan')->save(['no_rawat' => $_POST['no_rawat'], 'kode' => $_POST['kode'], 'lokasi_file' => $lokasi_file]);
          if($query) {
            echo '<br><img src="'.WEBAPPS_URL.'/berkasrawat/'.$lokasi_file.'" width="150" />';
          }
      }

      exit();

    }
  
   public function anyJadwalOperasi()
  {

    $i = 1;
    $rows = $this->db('booking_operasi')
      ->where('no_rawat', $_POST['no_rawat'])
      ->toArray();

    $result = [];
    foreach ($rows as $row) {
      // $row = $rows;
      $row['nomor'] = $i++;

      $pasien = $this->db('reg_periksa')
        ->join('pasien', 'pasien.no_rkm_medis=reg_periksa.no_rkm_medis')
        ->where('no_rawat', $row['no_rawat'])
        ->oneArray();

      $row['no_rkm_medis'] = $pasien['no_rkm_medis'];
      $row['nm_pasien'] = $pasien['nm_pasien'];
      $row['umur'] = $pasien['umur'];
      $row['jk'] = $pasien['jk'];
      $row['kd_poli'] = $pasien['kd_poli'];

      $row['diagnosa'] = $this->db('diagnosa_pasien')
        ->select(['nm_penyakit' => 'penyakit.nm_penyakit'])
        ->join('penyakit', 'penyakit.kd_penyakit=diagnosa_pasien.kd_penyakit')
        ->where('no_rawat', $row['no_rawat'])
        ->asc('prioritas')
        ->toArray();

      $poli = $this->db('poliklinik')
       ->where('kd_poli', $row['kd_poli'])
       ->oneArray();
      $row['nm_poli'] = $poli['nm_poli'];

      $dokter = $this->db('dokter')
        ->where('kd_dokter', $row['kd_dokter'])
        ->oneArray();
      $row['nm_dokter'] = $dokter['nm_dokter'];

      $paket_operasi = $this->db('paket_operasi')
        ->where('kode_paket', $row['kode_paket'])
        ->oneArray();
      $row['nm_perawatan'] = $paket_operasi['nm_perawatan'];

      $result[] = $row;
    }

    echo $this->draw('jadwaloperasi.html', ['jadwaloperasi' => $result]);
    exit();
  }

  public function postDokter()
  {

    if (isset($_POST["query"])) {
      $output = '';
      $key = "%" . $_POST["query"] . "%";
      $rows = $this->db('dokter')->like('nm_dokter', $key)->limit(10)->toArray();
      $output = '';
      if (count($rows)) {
        foreach ($rows as $row) {
          $output .= '<li data-id="' . $row['kd_dokter'] . '" class="list-group-item link-class">' . $row["nm_dokter"] . '</li>';
        }
      }
      echo $output;
    }

    exit();
  }

  public function postPaketOperasi()
  {

    if (isset($_POST["query"])) {
      $output = '';
      $key = "%" . $_POST["query"] . "%";
      $rows = $this->db('paket_operasi')->like('nm_perawatan', $key)->limit(10)->toArray();
      $output = '';
      if (count($rows)) {
        foreach ($rows as $row) {
          $output .= '<li data-id="' . $row['kode_paket'] . '" class="list-group-item link-class">' . $row["nm_perawatan"] . '</li>';
        }
      }
      echo $output;
    }

    exit();
  }

  public function postSaveJadwalOperasi()
  {
    $is_edit = $_POST['edit'];
    unset($_POST['edit']);
    if (!$this->db('booking_operasi')->where('no_rawat', $_POST['no_rawat'])->where('tanggal', $_POST['tanggal'])->oneArray()) {
      $this->db('booking_operasi')->save($_POST);
    } else if ($is_edit) {
      $this->db('booking_operasi')->where('no_rawat', $_POST['no_rawat'])->where('tanggal', $_POST['tanggal'])->save($_POST);
    }
    exit();
  }

  public function postHapusJadwalOperasi()
  {
    $this->db('booking_operasi')->where('no_rawat', $_POST['no_rawat'])->where('tanggal', $_POST['tanggal'])->delete();
    exit();
  }
  
    public function postProviderList()
    {

      if(isset($_POST["query"])){
        $output = '';
        $key = "%".$_POST["query"]."%";
        $rows = $this->db('dokter')->like('nm_dokter', $key)->where('status', '1')->limit(10)->toArray();
        $output = '';
        if(count($rows)){
          foreach ($rows as $row) {
            $output .= '<li class="list-group-item link-class">'.$row["kd_dokter"].': '.$row["nm_dokter"].'</li>';
          }
        }
        echo $output;
      }

      exit();

    }

    public function postProviderList2()
    {

      if(isset($_POST["query"])){
        $output = '';
        $key = "%".$_POST["query"]."%";
        $rows = $this->db('petugas')->like('nama', $key)->limit(10)->toArray();
        $output = '';
        if(count($rows)){
          foreach ($rows as $row) {
            $output .= '<li class="list-group-item link-class">'.$row["nip"].': '.$row["nama"].'</li>';
          }
        }
        echo $output;
      }

      exit();

    }

     public function postPerujukList()
    {

      if(isset($_POST["query"])){
        $output = '';
        $key = "%".$_POST["query"]."%";
        $rows = $this->db('dokter')->like('nm_dokter', $key)->where('status', '1')->limit(10)->toArray();
        $output = '';
        if(count($rows)){
          foreach ($rows as $row) {
            $output .= '<li class="list-group-item link-class">'.$row["kd_dokter"].': '.$row["nm_dokter"].'</li>';
          }
        }
        echo $output;
      }

      exit();

    }

    public function postMaxid()
    {
      $max_id = $this->db('reg_periksa')->select(['no_rawat' => 'ifnull(MAX(CONVERT(RIGHT(no_rawat,6),signed)),0)'])->where('tgl_registrasi', date('Y-m-d'))->oneArray();
      if(empty($max_id['no_rawat'])) {
        $max_id['no_rawat'] = '000000';
      }
      $_next_no_rawat = sprintf('%06s', ($max_id['no_rawat'] + 1));
      $next_no_rawat = date('Y/m/d').'/'.$_next_no_rawat;
      echo $next_no_rawat;
      exit();
    }

    public function postMaxAntrian()
    {
      $max_id = $this->db('reg_periksa')->select(['no_reg' => 'ifnull(MAX(CONVERT(RIGHT(no_reg,3),signed)),0)'])->where('kd_poli', $_POST['kd_poli'])->where('tgl_registrasi', date('Y-m-d'))->desc('no_reg')->limit(1)->oneArray();
      if($this->settings->get('settings.dokter_ralan_per_dokter') == 'true') {
        $max_id = $this->db('reg_periksa')->select(['no_reg' => 'ifnull(MAX(CONVERT(RIGHT(no_reg,3),signed)),0)'])->where('kd_poli', $_POST['kd_poli'])->where('kd_dokter', $_POST['kd_dokter'])->where('tgl_registrasi', date('Y-m-d'))->desc('no_reg')->limit(1)->oneArray();
      }
      if(empty($max_id['no_reg'])) {
        $max_id['no_reg'] = '000';
      }
      $_next_no_reg = sprintf('%03s', ($max_id['no_reg'] + 1));

      $date = date('Y-m-d');
      $tentukan_hari=date('D',strtotime(date('Y-m-d')));
      $day = array(
        'Sun' => 'AKHAD',
        'Mon' => 'SENIN',
        'Tue' => 'SELASA',
        'Wed' => 'RABU',
        'Thu' => 'KAMIS',
        'Fri' => 'JUMAT',
        'Sat' => 'SABTU'
      );
      $hari=$day[$tentukan_hari];

      $jadwal_dokter = $this->db('jadwal')->where('kd_poli', $_POST['kd_poli'])->where('kd_dokter', $_POST['kd_dokter'])->where('hari_kerja', $hari)->oneArray();
      $jadwal_poli = $this->db('jadwal')->where('kd_poli', $_POST['kd_poli'])->where('hari_kerja', $hari)->toArray();
      $kuota_poli = 0;
      foreach ($jadwal_poli as $row) {
        $kuota_poli += $row['kuota'];
      }
      if($this->settings->get('settings.dokter_ralan_per_dokter') == 'true' && $this->settings->get('settings.ceklimit') == 'true' && $_next_no_reg > $jadwal_dokter['kuota']) {
        $_next_no_reg = '888888';
      }
      if($this->settings->get('settings.dokter_ralan_per_dokter') == 'false' && $this->settings->get('settings.ceklimit') == 'true' && $_next_no_reg > $kuota_poli) {
        $_next_no_reg = '999999';
      }
      echo $_next_no_reg;
      exit();
    }

    public function getPasienInfo($field, $no_rkm_medis)
    {
        $row = $this->db('pasien')->where('no_rkm_medis', $no_rkm_medis)->oneArray();
        return $row[$field];
    }

    public function getRegPeriksaInfo($field, $no_rawat)
    {
        $row = $this->db('reg_periksa')->where('no_rawat', $no_rawat)->oneArray();
        return $row[$field];
    }

    public function setNoRawat()
    {
        $date = date('Y-m-d');
        $last_no_rawat = $this->db()->pdo()->prepare("SELECT ifnull(MAX(CONVERT(RIGHT(no_rawat,6),signed)),0) FROM reg_periksa WHERE tgl_registrasi = '$date'");
        $last_no_rawat->execute();
        $last_no_rawat = $last_no_rawat->fetch();
        if(empty($last_no_rawat[0])) {
          $last_no_rawat[0] = '000000';
        }
        $next_no_rawat = sprintf('%06s', ($last_no_rawat[0] + 1));
        $next_no_rawat = date('Y/m/d').'/'.$next_no_rawat;

        return $next_no_rawat;
    }

    public function getEnum($table_name, $column_name) {
      $result = $this->db()->pdo()->prepare("SHOW COLUMNS FROM $table_name LIKE '$column_name'");
      $result->execute();
      $result = $result->fetch();
      $result = explode("','",preg_replace("/(enum|set)\('(.+?)'\)/","\\2", $result[1]));
      return $result;
    }

    public function convertNorawat($text)
    {
        setlocale(LC_ALL, 'en_EN');
        $text = str_replace('/', '', trim($text));
        return $text;
    }

    public function postCetak()
    {
      $this->core->db()->pdo()->exec("DELETE FROM `mlite_temporary`");
      $cari = $_POST['cari'];
      $tgl_awal = $_POST['tgl_awal'];
      $tgl_akhir = $_POST['tgl_akhir'];
      $igd = $this->settings->get('settings.igd');
      $this->core->db()->pdo()->exec("INSERT INTO `mlite_temporary` (
        `temp1`,`temp2`,`temp3`,`temp4`,`temp5`,`temp6`,`temp7`,`temp8`,`temp9`,`temp10`,`temp11`,`temp12`,`temp13`,`temp14`,`temp15`,`temp16`,`temp17`,`temp18`,`temp19`
      )
      SELECT *
      FROM `reg_periksa`
      WHERE `kd_poli` <> '$igd'
      AND `tgl_registrasi` BETWEEN '$tgl_awal' AND '$tgl_akhir'
      ");
      exit();
    }

    public function getCetakPdf()
    {
      $tmp = $this->db('mlite_temporary')->toArray();
      $logo = $this->settings->get('settings.logo');

      $pdf = new PDF_MC_Table('L','mm','Legal');
      $pdf->AddPage();
      $pdf->SetAutoPageBreak(true, 10);
      $pdf->SetTopMargin(10);
      $pdf->SetLeftMargin(10);
      $pdf->SetRightMargin(10);

      $pdf->Image('../'.$logo, 10, 8, '18', '18', 'png');
      $pdf->SetFont('Arial', '', 24);
      $pdf->Text(30, 16, $this->settings->get('settings.nama_instansi'));
      $pdf->SetFont('Arial', '', 10);
      $pdf->Text(30, 21, $this->settings->get('settings.alamat').' - '.$this->settings->get('settings.kota'));
      $pdf->Text(30, 25, $this->settings->get('settings.nomor_telepon').' - '.$this->settings->get('settings.email'));
      $pdf->Line(10, 30, 345, 30);
      $pdf->Line(10, 31, 345, 31);
      $pdf->SetFont('Arial', 'B', 13);
      $pdf->Text(10, 40, 'DATA PENDAFTARAN POLIKLINIK');
      $pdf->Ln(34);
      $pdf->SetFont('Arial', 'B', 11);
      $pdf->SetWidths(array(25,35,20,80,25,50,50,50));
      $pdf->Row(array('Tanggal','No. Rawat','No. Reg','Nama Pasien','No. RM','Poliklinik','Dokter','Penjamin'));
      $pdf->SetFont('Arial', '', 10);
      foreach ($tmp as $hasil) {
        $poliklinik = $this->db('poliklinik')->where('kd_poli', $hasil['temp7'])->oneArray();
        $dokter = $this->db('dokter')->where('kd_dokter', $hasil['temp5'])->oneArray();
        $penjab = $this->db('penjab')->where('kd_pj', $hasil['temp15'])->oneArray();
        $pdf->Row(array($hasil['temp3'],$hasil['temp2'],$hasil['temp1'],$this->core->getPasienInfo('nm_pasien', $hasil['temp6']),$hasil['temp6'],$poliklinik['nm_poli'],$dokter['nm_dokter'],$penjab['png_jawab']));
      }
      $pdf->Output('cetak'.date('Y-m-d').'.pdf','I');
    }

    public function anyAutoRegist(){
      $date = date('Y-m-d');
      $no = 1;
      $checkBooking = $this->db('booking_registrasi')->where('tanggal_periksa',$date)->where('status','Belum')->toArray();
      foreach ($checkBooking as $value) {
        # code...
        $poliklinik = $this->db('poliklinik')->where('kd_poli', $value['kd_poli'])->oneArray();

        $pasien = $this->db('pasien')->where('no_rkm_medis', $value['no_rkm_medis'])->oneArray();

        $birthDate = new \DateTime($pasien['tgl_lahir']);
        $today = new \DateTime("today");
        $umur_daftar = "0";
        $status_umur = 'Hr';
        if ($birthDate < $today) {
          $y = $today->diff($birthDate)->y;
          $m = $today->diff($birthDate)->m;
          $d = $today->diff($birthDate)->d;
          $umur_daftar = $d;
          $status_umur = "Hr";
          if($y !='0'){
            $umur_daftar = $y;
            $status_umur = "Th";
          }
          if($y =='0' && $m !='0'){
            $umur_daftar = $m;
            $status_umur = "Bl";
          }
        }

        $_POST['no_reg'] = $value['no_reg'];
        $_POST['no_rawat'] = $this->setNoRawat();
        $_POST['tgl_registrasi'] = $date;
        $_POST['jam_reg'] = '06:00:00';
        $_POST['kd_dokter'] = $value['kd_dokter'];
        $_POST['no_rkm_medis'] = $value['no_rkm_medis'];
        $_POST['kd_poli'] = $value['kd_poli'];
        $_POST['p_jawab'] = '-';
        $_POST['almt_pj'] = '-';
        $_POST['hubunganpj'] = '-';
        $_POST['biaya_reg'] = $poliklinik['registrasi'];
        $_POST['stts'] = 'Belum';
        $cek_stts_daftar = $this->db('reg_periksa')->where('no_rkm_medis', $value['no_rkm_medis'])->count();
        $_POST['stts_daftar'] = 'Baru';
        if($cek_stts_daftar > 0) {
          $_POST['stts_daftar'] = 'Lama';
        }
        $_POST['status_lanjut'] = 'Ralan';
        $_POST['kd_pj'] = $value['kd_pj'];
        $_POST['umurdaftar'] = $umur_daftar;
        $_POST['sttsumur'] = $status_umur;
        $_POST['status_bayar'] = 'Belum Bayar';
        $cek_status_poli = $this->db('reg_periksa')->where('no_rkm_medis',$value['no_rkm_medis'])->where('kd_poli', $value['kd_poli'])->count();
        $_POST['status_poli'] = 'Baru';
        if($cek_status_poli > 0) {
          $_POST['status_poli'] = 'Lama';
        }
        // echo $_POST;
        $query = $this->db('reg_periksa')->save($_POST);
        if ($query) {
          # code...
          // echo json_encode($_POST);
          $this->db('booking_registrasi')->where('no_rkm_medis',$value['no_rkm_medis'])->where('tanggal_periksa',$date)->save(['status' => 'Terdaftar']);
          $updateSkdp = $this->db('skdp_bpjs')->where('no_rkm_medis',$value['no_rkm_medis'])->where('tanggal_datang',$date)->save(['status' => 'Sudah Periksa']);
          if ($updateSkdp) {
            echo $no.'.'.$value['no_rkm_medis'].' Berhasil Didaftarkan';
            echo '<br>';
          }
        }
        $no++;
      }
      exit();
    }
   
        public function anyRujukanInternal()
    {

      $this->_addHeaderFiles();

        $rows = $this->db('rujukan_internal_poli')
          ->join('reg_periksa', 'reg_periksa.no_rawat=rujukan_internal_poli.no_rawat')
          ->join('pasien', 'pasien.no_rkm_medis=reg_periksa.no_rkm_medis')
          ->join('dokter', ' rujukan_internal_poli.kd_dokter=dokter.kd_dokter')
          ->join('dokter as dok' , ' dok.kd_dokter=reg_periksa.kd_dokter')
          ->join('poliklinik', 'poliklinik.kd_poli=rujukan_internal_poli.kd_poli')
          ->join('poliklinik as poli', 'poli.kd_poli=reg_periksa.kd_poli')
          ->join('penjab', 'penjab.kd_pj=reg_periksa.kd_pj')
          ->select(['tgl_registrasi'=> 'reg_periksa.tgl_registrasi',
                    'jam_reg'       => 'reg_periksa.jam_reg',
                    'no_rkm_medis'  => 'reg_periksa.no_rkm_medis',
                    'kd_dokter'       => 'reg_periksa.kd_dokter',
                    'p_jawab'       => 'reg_periksa.p_jawab',
                    'almt_pj'       => 'reg_periksa.almt_pj',
                    'hubunganpj'    => 'reg_periksa.hubunganpj',
                    'stts'          => 'reg_periksa.stts',
                    'status_lanjut' => 'reg_periksa.status_lanjut',
                    'status_bayar'  => 'reg_periksa.status_bayar'])
          ->select(['png_jawab'     => 'penjab.png_jawab'])   
          ->select(['poli_awal'     => 'poli.nm_poli',])   
          ->select(['nm_pasien'     => 'pasien.nm_pasien',
                    'umur'          => 'pasien.umur',
                    'jk'            => 'pasien.jk',
                    'no_tlp'        => 'pasien.no_tlp'])    
          ->select(['dokter_perujuk'=> 'dok.nm_dokter',
                    'dokter_rujukan'=> 'dokter.nm_dokter'])
          ->select(['poli_rujukan'  => 'poliklinik.nm_poli'])
          ->select(['no_rawat'      => 'rujukan_internal_poli.no_rawat',
                    'dokter'        => 'rujukan_internal_poli.kd_dokter',
                    'kdpoli_rujukan'=> 'rujukan_internal_poli.kd_poli'])
          ->where('reg_periksa.tgl_registrasi', date('Y-m-d'))
          ->desc('reg_periksa.jam_reg')
          ->toArray();

          $master_berkas_digital = $this->db('master_berkas_digital')->toArray();

      $this->assign['list'] = [];
      foreach ($rows as $row) {
        $this->assign['list'][] = $row;
      }
      return $this->draw('rujukan.internal.html', ['rujukaninternal' => $this->assign, 'master_berkas_digital' => $master_berkas_digital]);
    }

    public function postRujukanInternal()
    {
      $this->_addHeaderFiles();

      if (isset($_POST['submit'])) {

        $date1 = $_POST['periode_rawat_jalan'];
        $date2 = $_POST['periode_rawat_jalan_akhir'];

        if (!empty($date1) && !empty($date2)) {
          // 'stts'          => 'reg_periksa.stts',
          // 'status_lanjut' => 'reg_periksa.status_lanjut',
          // 'status_bayar'  => 'reg_periksa.status_bayar'])
          $sql = "SELECT a.no_rawat, a.kd_dokter as dokter, a.kd_poli as kdpoli_rujukan,
          b.tgl_registrasi, b.jam_reg, b.no_rkm_medis, b.p_jawab, b.almt_pj, b.hubunganpj, b.stts, b.status_lanjut,  b.status_bayar,
          poli.nm_poli as poli_awal, c.nm_pasien, c.umur, c.jk, c.no_tlp, d.nm_dokter as dokter_rujukan, e.nm_poli as poli_rujukan, f.png_jawab, dok.nm_dokter as dokter_perujuk
          FROM rujukan_internal_poli as a, reg_periksa as b , pasien as c, dokter as d , poliklinik as e, penjab as f , poliklinik as poli, dokter as dok
          WHERE b.no_rawat = a.no_rawat
          AND c.no_rkm_medis=b.no_rkm_medis
          AND a.kd_dokter=d.kd_dokter
          AND dok.kd_dokter=b.kd_dokter
          AND e.kd_poli=a.kd_poli
          AND poli.kd_poli=b.kd_poli
          AND f.kd_pj=b.kd_pj
          AND b.tgl_registrasi BETWEEN '$date1' AND '$date2'
          ORDER BY b.jam_reg DESC";

          $stmt = $this->db()->pdo()->prepare($sql);
          $stmt->execute();
          $rows = $stmt->fetchAll();

          $master_berkas_digital = $this->db('master_berkas_digital')->toArray();

          $this->assign['list'] = [];
          foreach ($rows as $row) {

            $this->assign['list'][] = $row;
          }
        } else {
          $this->anyRujukanInternal();
        }
      }
      return $this->draw('rujukan.internal.html', ['rujukaninternal' => $this->assign, 'master_berkas_digital' => $master_berkas_digital]);
    }
  
    public function postObatKronis()
    {
      if (isset($_POST['no_rawat']) && $_POST['no_rawat'] !='') {
        $reg_periksa = $this->db('reg_periksa')->where('no_rawat', $_POST['no_rawat'])->oneArray();
        $bridging_sep = $this->db('bridging_sep')->where('no_rawat', $_POST['no_rawat'])->oneArray();
        if(!$bridging_sep) {
          $bridging_sep['no_sep'] = '';
        }
        $this->db('mlite_veronisa')->save([
          'id' => NULL,
          'tanggal' => date('Y-m-d'),
          'no_rkm_medis' => $reg_periksa['no_rkm_medis'],
          'no_rawat' => $_POST['no_rawat'],
          'tgl_registrasi' => $reg_periksa['tgl_registrasi'],
          'nosep' => $bridging_sep['no_sep'],
          'status' => 'Belum',
          'username' => $this->core->getUserInfo('username', null, true)
        ]);
      }
      exit();
    }
    
    public function postHapusObatKronis()
    {
      $this->db('mlite_veronisa')->where('no_rawat', $_POST['no_rawat'])->delete();
      exit();
    }
  
    public function anyOrthanc()
    {
     $rows = $this->db('reg_periksa')->where('no_rawat', $_POST['no_rawat'])->toArray();

     $result = [];
     foreach ($rows as $row) {
            $no_rawat = $row['no_rawat'];
            $norm = $row['no_rkm_medis'];
            $tgl_registrasi = $row['tgl_registrasi'];
            $tanggal = str_replace("-", "", $tgl_registrasi);

            $pacs = [];
            $arr = array(
                "Level" => "Study",
                "Expand" => true,
                "Query" => array(
                    "StudyDate" => $tanggal . "-" . $tanggal,
                    "PatientID" => $norm
                )
            );

            $pacs['data'] = json_encode($arr);

            // $url_orthanc = $this->settings->get('orthanc.server');
            $url_orthanc = 'http://103.59.94.14:8042/';
            $urlfind = $url_orthanc . '/tools/find';

            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, $urlfind);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
            curl_setopt($curl, CURLOPT_USERPWD, $this->settings->get('orthanc.username') . ":" . $this->settings->get('orthanc.password'));
            curl_setopt($curl, CURLOPT_TIMEOUT, 30);
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $pacs['data']);
            $response = curl_exec($curl);
            curl_close($curl);

            $patient = json_decode($response, TRUE);

            if (isset($patient[0]["ID"])) {
                foreach ($patient[0]["Series"] as $series) {
                    $urlSeries = $url_orthanc . '/series/' . $series;

                    $curl = curl_init();
                    curl_setopt($curl, CURLOPT_URL, $urlSeries);
                    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
                    curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
                    curl_setopt($curl, CURLOPT_USERPWD, $this->settings->get('orthanc.username') . ":" . $this->settings->get('orthanc.password'));
                    $response = curl_exec($curl);
                    curl_close($curl);

                    $seriesData = json_decode($response, true);

                    $seriesDate = isset($seriesData['MainDicomTags']['SeriesDate']) ? $seriesData['MainDicomTags']['SeriesDate'] : "";
                    $acquisitionDescription = isset($seriesData['MainDicomTags']['AcquisitionDeviceProcessingDescription']) ? $seriesData['MainDicomTags']['AcquisitionDeviceProcessingDescription'] : "";

                    $instance = $seriesData['Instances'][0];
                    $imageURL = $url_orthanc . '/instances/' . $instance . '/preview';
                    $imageURL = $url_orthanc . '/instances/' . $instance . '/preview';

                    $gambar = '';
                    foreach ($seriesData['Instances'] as $instance) {
                        $imageURL = $url_orthanc . '/instances/' . $instance . '/preview';
                        $gambar .= '<a href="' . $url_orthanc . '/web-viewer/app/viewer.html?series=' . $series . '" target="_blank">';
                        $gambar .= '<img src="' . $imageURL . '" alt="Klik Gambar Disini" style="width: 600px;">';
                        $gambar .= '</a><br><br><br>';
                      }

                    $result[] = array(
                        'Tanggal' => date('d-m-Y', strtotime($seriesDate)),
                        'Deskripsi' => $acquisitionDescription,
                        'Gambar' => $gambar
                    );
                }
            }
        }
        echo $this->draw('data.orthanc.html', ['pacs' => $result]);
        exit();
    }

    public function getJavascript()
    {
        header('Content-type: text/javascript');
        echo $this->draw(MODULES.'/rawat_jalan/js/admin/rawat_jalan.js');
        exit();
    }

    private function _addHeaderFiles()
    {
        $this->core->addCSS(url('assets/css/dataTables.bootstrap.min.css'));
        $this->core->addJS(url('assets/jscripts/jquery.dataTables.min.js'));
        $this->core->addJS(url('assets/jscripts/dataTables.bootstrap.min.js'));
        $this->core->addJS(url('assets/jscripts/lightbox/lightbox.min.js'));
        $this->core->addCSS(url('assets/jscripts/lightbox/lightbox.min.css'));
        $this->core->addCSS(url('assets/css/bootstrap-datetimepicker.css'));
        $this->core->addJS(url('assets/jscripts/moment-with-locales.js'));
        $this->core->addJS(url('assets/jscripts/bootstrap-datetimepicker.js'));
        $this->core->addJS(url([ADMIN, 'rawat_jalan', 'javascript']), 'footer');
    }

    protected function data_icd($table)
    {
        return new DB_ICD($table);
    }

}
