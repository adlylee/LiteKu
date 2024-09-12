<?php

namespace Plugins\Rawat_Inap;

use Systems\AdminModule;
use Plugins\Icd\DB_ICD;

class Admin extends AdminModule
{

  private $_uploads = WEBAPPS_PATH . '/berkasrawat/pages/upload';
  public function navigation()
  {
    return [
      'Kelola'      => 'index',
      'Rawat Inap'  => 'manage',
      'Sudah Resume'  => 'manage_listresume',
      'Belum Resume'  => 'manage_listpulang',
      'Resume Siap Klaim'  => 'resume_siapklaim',
      'Resume Batal Klaim' => 'resume_batalset'
    ];
  }
  
  public function getIndex()
  {
    $sub_modules = [
        ['name' => 'List Pasien Rawat Inap', 'url' => url([ADMIN, 'rawat_inap', 'manage']), 'icon' => 'bed', 'desc' => 'Pendaftaran Pasien Rawat Inap'],
        ['name' => 'Sudah Resume', 'url' => url([ADMIN, 'rawat_inap', 'manage_listresume']), 'icon' => 'file-text-o', 'desc' => 'Sudah Resume'],
        ['name' => 'Belum Resume', 'url' => url([ADMIN, 'rawat_inap', 'manage_listpulang']), 'icon' => 'file-o', 'desc' => 'Belum Resume'],
        ['name' => 'Siap Klaim', 'url' => url([ADMIN, 'rawat_inap', 'resume_siapklaim']), 'icon' => 'file-text-o', 'desc' => 'Resume Siap Klaim'],
        ['name' => 'Batal Klaim', 'url' => url([ADMIN, 'rawat_inap', 'resume_batalset']), 'icon' => 'file-o', 'desc' => 'Resume Batal Klaim'],
      ];
      return $this->draw('index.html', ['sub_modules' => $sub_modules]);
  }

  public function anyManage()
  {
    $tgl_masuk = '';
    $tgl_masuk_akhir = '';
    $status_pulang = '';
    $this->assign['stts_pulang'] = [];

    if (isset($_POST['periode_rawat_inap'])) {
      $tgl_masuk = $_POST['periode_rawat_inap'];
    }
    if (isset($_POST['periode_rawat_inap_akhir'])) {
      $tgl_masuk_akhir = $_POST['periode_rawat_inap_akhir'];
    }
    if (isset($_POST['status_pulang'])) {
      $status_pulang = $_POST['status_pulang'];
    }
    $cek_vclaim = $this->db('mlite_modules')->where('dir', 'vclaim')->oneArray();

    $username = $this->core->getUserInfo('username', null, true);

    $master_berkas_digital = $this->db('master_berkas_digital')->toArray();
    $this->_Display($tgl_masuk, $tgl_masuk_akhir, $status_pulang);
    return $this->draw('manage.html', ['rawat_inap' => $this->assign, 'cek_vclaim' => $cek_vclaim, 'master_berkas_digital' => $master_berkas_digital, 'username' => $username]);
  }

  public function anyDisplay()
  {
    $tgl_masuk = '';
    $tgl_masuk_akhir = '';
    $status_pulang = '';
    $this->assign['stts_pulang'] = [];

    if (isset($_POST['periode_rawat_inap'])) {
      $tgl_masuk = $_POST['periode_rawat_inap'];
    }
    if (isset($_POST['periode_rawat_inap_akhir'])) {
      $tgl_masuk_akhir = $_POST['periode_rawat_inap_akhir'];
    }
    if (isset($_POST['status_pulang'])) {
      $status_pulang = $_POST['status_pulang'];
    }
    $cek_vclaim = $this->db('mlite_modules')->where('dir', 'vclaim')->oneArray();

    $username = $this->core->getUserInfo('username', null, true);

    $this->_Display($tgl_masuk, $tgl_masuk_akhir, $status_pulang);
    echo $this->draw('display.html', ['rawat_inap' => $this->assign, 'cek_vclaim' => $cek_vclaim, 'username' => $username]);
    exit();
  }

  public function _Display($tgl_masuk = '', $tgl_masuk_akhir = '', $status_pulang = '')
  {
    $this->_addHeaderFiles();

    $this->assign['kamar'] = $this->db('kamar')->join('bangsal', 'bangsal.kd_bangsal=kamar.kd_bangsal')->where('statusdata', '1')->toArray();
    $this->assign['dokter'] = $this->db('dokter')->where('status', '1')->toArray();
   // $this->assign['penjab']  = $this->db('penjab')->toArray();
    $this->assign['penjab']   = $this->db('penjab')->where('status', '1')->toArray();
    $this->assign['no_rawat'] = '';

    $bangsal = str_replace(",", "','", $this->core->getUserInfo('cap', null, true));

    $sql = "SELECT
            kamar_inap.*,
            reg_periksa.*,
            pasien.*,
            kamar.*,
            bangsal.*,
            penjab.*
          FROM
            kamar_inap,
            reg_periksa,
            pasien,
            kamar,
            bangsal,
            penjab
          WHERE
            kamar_inap.no_rawat=reg_periksa.no_rawat
          AND
            reg_periksa.no_rkm_medis=pasien.no_rkm_medis
          AND
            kamar_inap.kd_kamar=kamar.kd_kamar
          AND
            bangsal.kd_bangsal=kamar.kd_bangsal
          AND
            reg_periksa.kd_pj=penjab.kd_pj";

     $username = $this->core->getUserInfo('username', null, true);
  //  if ((!in_array($this->core->getUserInfo('role'), ['admin', 'apoteker', 'laboratorium', 'radiologi', 'manajemen', 'gizi'],  true)) 
  //  && (!in_array ($this->core->getPegawaiInfo('bidang', $username), ['Mubarak'], true)) ) {
    if (!in_array($this->core->getUserInfo('role'), ['admin', 'apoteker', 'laboratorium', 'radiologi', 'manajemen', 'gizi', 'ppi/mpp', 'ok', 'rekammedis'],  true)){
      $sql .= " AND bangsal.kd_bangsal IN ('$bangsal')";
    }
    if ($status_pulang == '') {
      $sql .= " AND kamar_inap.stts_pulang = '-'";
    }
    if ($status_pulang == 'all' && $tgl_masuk !== '' && $tgl_masuk_akhir !== '') {
      $sql .= " AND kamar_inap.stts_pulang = '-' AND kamar_inap.tgl_masuk BETWEEN '$tgl_masuk' AND '$tgl_masuk_akhir'";
    }
    if ($status_pulang == 'masuk' && $tgl_masuk !== '' && $tgl_masuk_akhir !== '') {
      $sql .= " AND kamar_inap.tgl_masuk BETWEEN '$tgl_masuk' AND '$tgl_masuk_akhir'";
    }
    if ($status_pulang == 'pulang' && $tgl_masuk !== '' && $tgl_masuk_akhir !== '') {
      $sql .= " AND kamar_inap.tgl_keluar BETWEEN '$tgl_masuk' AND '$tgl_masuk_akhir'";
    }

    $stmt = $this->db()->pdo()->prepare($sql);
    $stmt->execute();
    $rows = $stmt->fetchAll();

    $this->assign['list'] = [];
    foreach ($rows as $row) {
      $dpjp_ranap = $this->db('dpjp_ranap')
        ->join('dokter', 'dokter.kd_dokter=dpjp_ranap.kd_dokter')
        ->where('no_rawat', $row['no_rawat'])
        ->toArray();
      $row['dokter'] = $dpjp_ranap;
      $row['con_no_rawat'] = convertNorawat($row['no_rawat']);
      $cek_sep = $this->db('bridging_sep')->where('no_rawat', $row['no_rawat'])->oneArray();
      $row['sep'] = $cek_sep['no_sep'];
      $this->assign['list'][] = $row;
    }

    if (isset($_POST['no_rawat'])) {
      $this->assign['kamar_inap'] = $this->db('kamar_inap')
        ->join('reg_periksa', 'reg_periksa.no_rawat=kamar_inap.no_rawat')
        ->join('pasien', 'pasien.no_rkm_medis=reg_periksa.no_rkm_medis')
        ->join('kamar', 'kamar.kd_kamar=kamar_inap.kd_kamar')
        ->join('dpjp_ranap', 'dpjp_ranap.no_rawat=kamar_inap.no_rawat')
        ->join('dokter', 'dokter.kd_dokter=dpjp_ranap.kd_dokter')
        ->join('penjab', 'penjab.kd_pj=reg_periksa.kd_pj')
        ->where('kamar_inap.no_rawat', $_POST['no_rawat'])
        ->oneArray();
    } else {
      $this->assign['kamar_inap'] = [
        'tgl_masuk' => date('Y-m-d'),
        'jam_masuk' => date('H:i:s'),
        'tgl_keluar' => date('Y-m-d'),
        'jam_keluar' => date('H:i:s'),
        'no_rkm_medis' => '',
        'nm_pasien' => '',
        'no_rawat' => '',
        'kd_dokter' => '',
        'kd_kamar' => '',
        'kd_pj' => '',
        'diagnosa_awal' => '',
        'diagnosa_akhir' => '',
        'stts_pulang' => '',
        'lama' => ''
      ];
    }
  }

  public function anyForm()
  {

    $this->assign['kamar'] = $this->db('kamar')->join('bangsal', 'bangsal.kd_bangsal=kamar.kd_bangsal')->where('statusdata', '1')->toArray();
    $this->assign['dokter'] = $this->db('dokter')->where('status', '1')->toArray();
    $this->assign['penjab'] = $this->db('penjab')->toArray();
    $this->assign['stts_pulang'] = ['Sehat', 'Rujuk', 'APS', '+', 'Meninggal', 'Sembuh', 'Membaik', 'Pulang Paksa', '-', 'Pindah Kamar', 'Status Belum Lengkap', 'Atas Persetujuan Dokter', 'Atas Permintaan Sendiri', 'Lain-lain'];
    $this->assign['no_rawat'] = '';
    if (isset($_POST['no_rawat'])) {
      $this->assign['kamar_inap'] = $this->db('kamar_inap')
        ->join('reg_periksa', 'reg_periksa.no_rawat=kamar_inap.no_rawat')
        ->join('pasien', 'pasien.no_rkm_medis=reg_periksa.no_rkm_medis')
        ->join('kamar', 'kamar.kd_kamar=kamar_inap.kd_kamar')
        ->join('dpjp_ranap', 'dpjp_ranap.no_rawat=kamar_inap.no_rawat')
        ->join('dokter', 'dokter.kd_dokter=dpjp_ranap.kd_dokter')
        ->join('penjab', 'penjab.kd_pj=reg_periksa.kd_pj')
        ->where('kamar_inap.no_rawat', $_POST['no_rawat'])
        ->oneArray();
      echo $this->draw('form.html', [
        'rawat_inap' => $this->assign
      ]);
    } else {
      $this->assign['kamar_inap'] = [
        'tgl_masuk' => date('Y-m-d'),
        'jam_masuk' => date('H:i:s'),
        'tgl_keluar' => date('Y-m-d'),
        'jam_keluar' => date('H:i:s'),
        'no_rkm_medis' => '',
        'nm_pasien' => '',
        'no_rawat' => '',
        'kd_dokter' => '',
        'kd_kamar' => '',
        'kd_pj' => '',
        'diagnosa_awal' => '',
        'diagnosa_akhir' => '',
        'stts_pulang' => '',
        'lama' => ''
      ];
      echo $this->draw('form.html', [
        'rawat_inap' => $this->assign
      ]);
    }
    exit();
  }

  public function anyStatusDaftar()
  {
    if (isset($_POST['no_rkm_medis'])) {
      $rawat = $this->db('reg_periksa')
        ->where('no_rkm_medis', $_POST['no_rkm_medis'])
        ->where('status_bayar', 'Belum Bayar')
        ->limit(1)
        ->oneArray();
      if ($rawat) {
        $stts_daftar = "Transaki tanggal " . date('Y-m-d', strtotime($rawat['tgl_registrasi'])) . " belum diselesaikan";
        $bg_status = 'text-danger';
      } else {
        $result = $this->db('reg_periksa')->where('no_rkm_medis', $_POST['no_rkm_medis'])->oneArray();
        if ($result >= 1) {
          $stts_daftar = 'Lama';
          $bg_status = 'text-info';
        } else {
          $stts_daftar = 'Baru';
          $bg_status = 'text-success';
        }
      }
      echo $this->draw('stts.daftar.html', ['stts_daftar' => $stts_daftar, 'bg_status' => $bg_status]);
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
    $kamar = $this->db('kamar')->where('kd_kamar', $_POST['kd_kamar'])->oneArray();
    $kamar_inap = $this->db('kamar_inap')->save([
      'no_rawat' => $_POST['no_rawat'],
      'kd_kamar' => $_POST['kd_kamar'],
      'trf_kamar' => $kamar['trf_kamar'],
      'lama' => $_POST['lama'],
      'tgl_masuk' => $_POST['tgl_masuk'],
      'jam_masuk' => $_POST['jam_masuk'],
      'ttl_biaya' => $kamar['trf_kamar'] * $_POST['lama'],
      'tgl_keluar' => '0000-00-00',
      'jam_keluar' => '00:00:00',
      'diagnosa_akhir' => '',
      'diagnosa_awal' => $_POST['diagnosa_awal'],
      'stts_pulang' => '-'
    ]);
    if ($kamar_inap) {
      $this->db('dpjp_ranap')->save(['no_rawat' => $_POST['no_rawat'], 'kd_dokter' => $_POST['kd_dokter']]);
    }
    exit();
  }

  public function postSaveKeluar()
  {
    $kamar = $this->db('kamar')->where('kd_kamar', $_POST['kd_kamar'])->oneArray();
    $this->db('kamar_inap')->where('no_rawat', $_POST['no_rawat'])->save([
      'stts_pulang' => $_POST['stts_pulang'],
      'lama' => $_POST['lama'],
      'tgl_keluar' => $_POST['tgl_keluar'],
      'jam_keluar' => $_POST['jam_keluar'],
      'diagnosa_akhir' => $_POST['diagnosa_akhir'],
      'ttl_biaya' => $kamar['trf_kamar'] * $_POST['lama']
    ]);
    exit();
  }

   public function postSetDPJP()
  {
      $no_rawat = $_POST['no_rawat'];
      $jenis_dpjp = $_POST['jenis_dpjp'];
      
      $cek_dpjputama =  $this->db('dpjp_ranap')
          ->where('no_rawat', $no_rawat)
          ->where('jenis_dpjp', 'Utama')
          ->oneArray();

      if ($jenis_dpjp === 'Utama' && $cek_dpjputama) {
          echo 'Maaf, DPJP Utama sudah tersedia..!!';
          exit();
      }

      $this->db('dpjp_ranap')->save(['no_rawat' => $no_rawat, 'kd_dokter' => $_POST['kd_dokter'], 'jenis_dpjp' => $jenis_dpjp]);
      exit();
  }

  public function postHapusDPJP()
  {
    $this->db('dpjp_ranap')->where('no_rawat', $_POST['no_rawat'])->where('kd_dokter', $_POST['kd_dokter'])->delete();
    exit();
  }

  public function anyPasien()
  {
    $cari = $_POST['cari'];
    if (isset($_POST['cari'])) {
      $sql = "SELECT
            pasien.nm_pasien,
            pasien.no_rkm_medis,
            reg_periksa.no_rawat
          FROM
            reg_periksa,
            pasien
          WHERE
            reg_periksa.status_lanjut='Ranap'
          AND
            pasien.no_rkm_medis=reg_periksa.no_rkm_medis
          AND
            (reg_periksa.no_rkm_medis LIKE ? OR reg_periksa.no_rawat LIKE ? OR pasien.nm_pasien LIKE ?)
          LIMIT 10";

      $stmt = $this->db()->pdo()->prepare($sql);
      $stmt->execute(['%' . $cari . '%', '%' . $cari . '%', '%' . $cari . '%']);
      $pasien = $stmt->fetchAll();

      /*$pasien = $this->db('reg_periksa')
          ->join('pasien', 'pasien.no_rkm_medis=reg_periksa.no_rkm_medis')
          ->like('reg_periksa.no_rkm_medis', '%'.$_POST['cari'].'%')
          ->where('status_lanjut', 'Ranap')
          ->asc('reg_periksa.no_rkm_medis')
          ->limit(15)
          ->toArray();*/
    }
    echo $this->draw('pasien.html', ['pasien' => $pasien]);
    exit();
  }

  public function getAntrian()
  {
    $settings = $this->settings('settings');
    $this->tpl->set('settings', $this->tpl->noParse_array(htmlspecialchars_array($settings)));
    $rawat_inap = $this->db('reg_periksa')
      ->join('pasien', 'pasien.no_rkm_medis=reg_periksa.no_rkm_medis')
      ->join('poliklinik', 'poliklinik.kd_poli=reg_periksa.kd_poli')
      ->join('dokter', 'dokter.kd_dokter=reg_periksa.kd_dokter')
      ->join('penjab', 'penjab.kd_pj=reg_periksa.kd_pj')
      ->where('no_rawat', $_GET['no_rawat'])
      ->oneArray();
    echo $this->draw('antrian.html', ['rawat_inap' => $rawat_inap]);
    exit();
  }

  public function postHapus()
  {
    $this->db('kamar_inap')->where('no_rawat', $_POST['no_rawat'])->delete();
    exit();
  }


  public function getBalanceCairan()
  {
    $this->_addHeaderFiles();
    $assign = [];
    $id = isset_or($_GET['id'],'');
    $assign['list'] = [];
    if ($id == '') {
      $assign['no_rawat'] = revertNorawat($_GET['no_rawat']);
      $assign['minum'] = '';
      $assign['makan'] = '';
      $assign['infus'] = '';
      $assign['muntah'] = '';
      $assign['urine'] = '';
      $assign['bab'] = '';
      $assign['no_rawatcon'] = '';
      $balance = $this->db('mlite_balance_cairan')->where('no_rawat',$assign['no_rawat'])->isNull('deleted_at')->toArray();
      foreach ($balance as $value) {
        $value['totalall'] = $value['total_in'] + $value['total_out'];
        $assign['list'][] = $value;
      }
    } else {
      $balance = $this->db('mlite_balance_cairan')->where('id',$id)->oneArray();
      $assign['no_rawat'] = $balance['no_rawat'];
      $assign['no_rawatcon'] = convertNorawat($balance['no_rawat']);
      $assign['minum'] = $balance['minum'];
      $assign['makan'] = $balance['makan'];
      $assign['infus'] = $balance['infus'];
      $assign['muntah'] = $balance['muntah'];
      $assign['urine'] = $balance['urine'];
      $assign['bab'] = $balance['bab'];
      $balance['totalall'] = $balance['total_in'] + $balance['total_out'];
      $assign['list'][] = $balance;
    }
    return $this->draw('display_balancecairan.html',['bc' => $assign,'id'=>$id]);
  }

  public function postBalanceCairanHapus() {
    $_POST = json_decode(file_get_contents('php://input'), true);
    $this->db('mlite_balance_cairan')->where('id',$_POST['id'])->update(['deleted_at' => date('Y-m-d H:i:s')]);
    echo 'Berhasil Dihapus';
    exit();
  }

  public function postBcSave() {
    $_POST['bc_dari'] = date("H",strtotime($_POST['bc_dari']));
    $_POST['bc_ke'] = date("H",strtotime($_POST['bc_ke']));
    $username = $this->core->getUserInfo('fullname', null, true);

    if ($_POST['id'] == '') {
      $total_in = $_POST['infus'] + $_POST['makan'] + $_POST['minum'];
      $this->db('mlite_balance_cairan')->save([
        'no_rawat' => $_POST['no_rawat'],
        'user' => $username,
        'tanggal' => $_POST['tanggal'],
        'bc_ke' => $_POST['bc_dari'].':00 sd '.$_POST['bc_ke'].':00',
        'minum' => $_POST['minum'],
        'makan' => $_POST['makan'],
        'infus' => $_POST['infus'],
        'total_in' => $total_in,
        'created_at' => date("Y-m-d H:i:s"),
        'deleted_at' => null
      ]);
    } else {
      $total_out = $_POST['muntah'] + $_POST['urine'] + $_POST['bab'];
      $this->db('mlite_balance_cairan')->where('id',$_POST['id'])->update([
        'muntah' => $_POST['muntah'],
        'urine' => $_POST['urine'],
        'bab' => $_POST['bab'],
        'total_out' => $total_out,
      ]);
    }
    redirect(url([ADMIN, 'rawat_inap', 'balancecairan?no_rawat='.convertNorawat($_POST['no_rawat'])]));
    exit();
  }

  public function getVentilator()
  {
    $this->_addHeaderFiles();
    $no_rawat = revertNorawat($_GET['no_rawat']);
    $dokterlist = $this->db('dokter')->select(['kd_dokter'=>'dokter.kd_dokter','nm_dokter'=>'dokter.nm_dokter'])->where('status','1')->toArray();
    $list = $this->db('mlite_ventilator')->where('no_rawat', $no_rawat)->isNull('deleted_at')->toArray();
    $view = [];
    foreach ($list as $value) {
      $date1 = new \DateTime($value['intubasi']);
      $date2 = new \DateTime($value['ekstubasi']);

      $diff = $date2->diff($date1);

      $hours = $diff->h;
      $hours = $hours + ($diff->days*24);

      $value['range'] = $hours;
      $value['nm_dokter'] = $this->core->getDokterInfo('nm_dokter',$value['kd_dokter']);
      $view[] = $value;
    }
    return $this->draw('display_ventilator.html', ['list' => $view, 'no_rawat' => $no_rawat,'dokter'=>$dokterlist]);
  }

  public function postVentiSave() {
    $username = $this->core->getUserInfo('fullname', null, true);
    $this->db('mlite_ventilator')->save([
      'no_rawat' => $_POST['no_rawat'],
      'kd_dokter' => $_POST['dokter'],
      'user' => $username,
      'jns_tindakan' => $_POST['jns_tindakan'],
      'intubasi' => $_POST['intubasi'],
      'ekstubasi' => $_POST['ekstubasi'],
      'created_at' => date("Y-m-d H:i:s")
    ]);
    redirect(url([ADMIN, 'rawat_inap', 'ventilator?no_rawat=' . convertNorawat($_POST['no_rawat'])]));
    exit();
  }

  public function postVentilatorHapus()
  {
    $_POST = json_decode(file_get_contents('php://input'), true);
    $this->db('mlite_ventilator')->where('id', $_POST['id'])->update(['deleted_at' => date('Y-m-d H:i:s')]);
    echo 'Berhasil Dihapus';
    exit();
  }

 public function postSaveDetail()
  {
    if ($_POST['kat'] == 'tindakan') {
      $jns_perawatan = $this->db('jns_perawatan_inap')->where('kd_jenis_prw', $_POST['kd_jenis_prw'])->oneArray();
      if ($_POST['provider'] == 'rawat_inap_dr') {
        $this->db('rawat_inap_dr')->save([
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
          'biaya_rawat' => $jns_perawatan['total_byrdr']
        ]);
      }
      if ($_POST['provider'] == 'rawat_inap_pr') {
        $this->db('rawat_inap_pr')->save([
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
          'biaya_rawat' => $jns_perawatan['total_byrdr']
        ]);
      }
      if ($_POST['provider'] == 'rawat_inap_drpr') {
        $this->db('rawat_inap_drpr')->save([
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
          'biaya_rawat' => $jns_perawatan['total_byrdr']
        ]);
      }
       if ($_POST['provider'] == 'rawat_inap_far') {
        $this->db('rawat_inap_pr')->save([
          'no_rawat' => $_POST['no_rawat'],
          'kd_jenis_prw' => $_POST['kd_jenis_prw'],
          'nip' => $_POST['kode_provider3'],
          'tgl_perawatan' => $_POST['tgl_perawatan'],
          'jam_rawat' => $_POST['jam_rawat'],
          'material' => $jns_perawatan['material'],
          'bhp' => $jns_perawatan['bhp'],
          'tarif_tindakanpr' => $jns_perawatan['tarif_tindakanpr'],
          'kso' => $jns_perawatan['kso'],
          'menejemen' => $jns_perawatan['menejemen'],
          'biaya_rawat' => $jns_perawatan['total_byrdr']
        ]);
      }
      if ($_POST['provider'] == 'rawat_inap_gizi') {
        $this->db('rawat_inap_pr')->save([
          'no_rawat' => $_POST['no_rawat'],
          'kd_jenis_prw' => $_POST['kd_jenis_prw'],
          'nip' => $_POST['kode_provider4'],
          'tgl_perawatan' => $_POST['tgl_perawatan'],
          'jam_rawat' => $_POST['jam_rawat'],
          'material' => $jns_perawatan['material'],
          'bhp' => $jns_perawatan['bhp'],
          'tarif_tindakanpr' => $jns_perawatan['tarif_tindakanpr'],
          'kso' => $jns_perawatan['kso'],
          'menejemen' => $jns_perawatan['menejemen'],
          'biaya_rawat' => $jns_perawatan['total_byrdr']
        ]);
      }
    }
    if ($_POST['kat'] == 'obat') {

      $no_resep = $this->core->setNoResep($_POST['tgl_perawatan']);

        $resep_obat = $this->core->mysql('resep_obat')
          ->save([
            'no_resep' => $no_resep,
            'tgl_perawatan' => '0000-00-00',
            'jam' => '00:00:00',
            'no_rawat' => $_POST['no_rawat'],
            'kd_dokter' => $_POST['kode_provider'],
            'tgl_peresepan' => $_POST['tgl_perawatan'],
            'jam_peresepan' => $_POST['jam_rawat'],
            'status' => 'ranap',
            'tgl_penyerahan' => '0000-00-00',
            'jam_penyerahan' => '00:00:00'
          ]);

        $this->core->mysql('resep_dokter')
          ->save([
            'no_resep' => $no_resep,
            'kode_brng' => $_POST['kd_jenis_prw'],
            'jml' => $_POST['jml'],
            'aturan_pakai' => $_POST['aturan_pakai']
          ]);
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
              'status' => 'Ranap'
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
              'status' => 'Ranap'
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
    if ($_POST['provider'] == 'rawat_inap_dr') {
      $this->db('rawat_inap_dr')
        ->where('no_rawat', $_POST['no_rawat'])
        ->where('kd_jenis_prw', $_POST['kd_jenis_prw'])
        ->where('tgl_perawatan', $_POST['tgl_perawatan'])
        ->where('jam_rawat', $_POST['jam_rawat'])
        ->delete();
    }
    if ($_POST['provider'] == 'rawat_inap_pr') {
      $this->db('rawat_inap_pr')
        ->where('no_rawat', $_POST['no_rawat'])
        ->where('kd_jenis_prw', $_POST['kd_jenis_prw'])
        ->where('tgl_perawatan', $_POST['tgl_perawatan'])
        ->where('jam_rawat', $_POST['jam_rawat'])
        ->delete();
    }
    if ($_POST['provider'] == 'rawat_inap_drpr') {
      $this->db('rawat_inap_drpr')
        ->where('no_rawat', $_POST['no_rawat'])
        ->where('kd_jenis_prw', $_POST['kd_jenis_prw'])
        ->where('tgl_perawatan', $_POST['tgl_perawatan'])
        ->where('jam_rawat', $_POST['jam_rawat'])
        ->delete();
    }
    exit();
  }

  public function postHapusResep()
  {
    if (isset($_POST['kd_jenis_prw'])) {
      $this->db('resep_dokter')
        ->where('no_resep', $_POST['no_resep'])
        ->where('kode_brng', $_POST['kd_jenis_prw'])
        ->delete();
    } else {
      $this->db('resep_obat')
        ->where('no_resep', $_POST['no_resep'])
        ->where('no_rawat', $_POST['no_rawat'])
        ->where('tgl_peresepan', $_POST['tgl_peresepan'])
        ->where('jam_peresepan', $_POST['jam_peresepan'])
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
      $rows_rawat_inap_dr = $this->db('rawat_inap_dr')->where('no_rawat', $_POST['no_rawat'])->toArray();
      $rows_rawat_inap_pr = $this->db('rawat_inap_pr')->where('no_rawat', $_POST['no_rawat'])->toArray();
      $rows_rawat_inap_drpr = $this->db('rawat_inap_drpr')->where('no_rawat', $_POST['no_rawat'])->toArray();

      $jumlah_total = 0;
      $rawat_inap_dr = [];
      $rawat_inap_pr = [];
      $rawat_inap_drpr = [];
      $i = 1;

      if($rows_rawat_inap_dr) {
        foreach ($rows_rawat_inap_dr as $row) {
          $jns_perawatan = $this->db('jns_perawatan_inap')->where('kd_jenis_prw', $row['kd_jenis_prw'])->oneArray();
          $row['nm_perawatan'] = $jns_perawatan['nm_perawatan'];
          $jumlah_total = $jumlah_total + $row['biaya_rawat'];
          $row['provider'] = 'rawat_inap_dr';
          $rawat_inap_dr[] = $row;
        }
      }

      if($rows_rawat_inap_pr) {
        foreach ($rows_rawat_inap_pr as $row) {
          $jns_perawatan = $this->db('jns_perawatan_inap')->where('kd_jenis_prw', $row['kd_jenis_prw'])->oneArray();
          $row['nm_perawatan'] = $jns_perawatan['nm_perawatan'];
          $jumlah_total = $jumlah_total + $row['biaya_rawat'];
          $row['provider'] = 'rawat_inap_pr';
          $cek_role = $this->db('mlite_users')->where('username', $row['nip'])->oneArray();
          $row['role'] = $cek_role['role'];
          $rawat_inap_pr[] = $row;
        }
      }

      if($rows_rawat_inap_drpr) {
        foreach ($rows_rawat_inap_drpr as $row) {
          $jns_perawatan = $this->db('jns_perawatan_inap')->where('kd_jenis_prw', $row['kd_jenis_prw'])->oneArray();
          $row['nm_perawatan'] = $jns_perawatan['nm_perawatan'];
          $jumlah_total = $jumlah_total + $row['biaya_rawat'];
          $row['provider'] = 'rawat_inap_drpr';
          $rawat_inap_drpr[] = $row;
        }
      }

      $rows = $this->db('resep_obat')
        ->join('dokter', 'dokter.kd_dokter=resep_obat.kd_dokter')
        ->where('no_rawat', $_POST['no_rawat'])
        ->where('resep_obat.status', 'ranap')
        ->toArray();
      $resep = [];
      $jumlah_total_resep = 0;
      foreach ($rows as $row) {
        $row['nomor'] = $i++;
        $row['resep_dokter'] = $this->db('resep_dokter')->join('databarang', 'databarang.kode_brng=resep_dokter.kode_brng')->where('no_resep', $row['no_resep'])->toArray();
        foreach ($row['resep_dokter'] as $value) {
          $value['dasar'] = $value['jml'] * $value['dasar'];
          $jumlah_total_resep += floatval($value['dasar']);
        }
        $resep[] = $row;
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
        'rawat_inap_dr' => $rawat_inap_dr, 
        'rawat_inap_pr' => $rawat_inap_pr, 
        'rawat_inap_drpr' => $rawat_inap_drpr, 
        'jumlah_total' => $jumlah_total, 
        'jumlah_total_resep' => $jumlah_total_resep, 
        'resep' =>$resep,
        'laboratorium' => $laboratorium,
        'radiologi' => $radiologi,
        'jumlah_total_lab' => $jumlah_total_lab,
        'jumlah_total_rad' => $jumlah_total_rad,
        'no_rawat' => $_POST['no_rawat']]);
      exit();
   }

  public function anySoap()
  {

    $username = $this->core->getUserInfo('username', null, true);
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

    $i = 1;
    $row['nama_petugas'] = '';
    $row['departemen_petugas'] = '';
    $rows = $this->db('pemeriksaan_ralan')
      ->where('no_rawat', $_POST['no_rawat'])
      ->desc('tgl_perawatan')
      ->toArray();
    $result = [];
    foreach ($rows as $row) {
      $row['nomor'] = $i++;
      $row['nama_petugas'] = $this->core->getPegawaiInfo('nama', $row['nip']);
      $row['departemen_petugas'] = $this->core->getDepartemenInfo($this->core->getPegawaiInfo('departemen', $row['nip']));
      $row['bidang'] = $this->core->getPegawaiInfo('bidang', $row['nip']);
      $result[] = $row;
    }

    $rows_ranap = $this->db('pemeriksaan_ranap')
      ->where('no_rawat', $_POST['no_rawat'])
      ->desc('tgl_perawatan')
      ->toArray();
    $result_ranap = [];
    foreach ($rows_ranap as $row) {
      $row['nomor'] = $i++;
      $row['nama_petugas'] = $this->core->getPegawaiInfo('nama', $row['nip']);
      $row['departemen_petugas'] = $this->core->getDepartemenInfo($this->core->getPegawaiInfo('departemen', $row['nip']));
      $row['bidang'] = $this->core->getPegawaiInfo('bidang', $row['nip']);

      $result_ranap[] = $row;
    }

    echo $this->draw('soap.html', ['pemeriksaan' => $result, 'pemeriksaan_ranap' => $result_ranap, 'diagnosa' => $diagnosa, 'prosedur' => $prosedur, 'username' => $username]);
    exit();

  }
  
  function postCariTtv() {
    $rows_ranap = [];
    $username = $this->core->getUserInfo('username', null, true);
    $rows_ranap = $this->db('pemeriksaan_ranap')
      ->select(['tensi','nadi','suhu_tubuh','respirasi'])
      ->where('no_rawat', $_POST['no_rawat'])
      ->desc('tgl_perawatan')
      ->oneArray();
    $keluhan = $this->db('pemeriksaan_ranap')
      ->select(['keluhan'])
      ->where('no_rawat', $_POST['no_rawat'])
      ->where('nip',$username)
      ->desc('tgl_perawatan')
      ->oneArray();
      $rows_ranap['keluhan'] = $keluhan['keluhan'];
      echo json_encode($rows_ranap);
      exit();
  }

  public function postSaveSOAP()
  {
    $check_db = $this->db()->pdo()->query("SHOW COLUMNS FROM `pemeriksaan_ranap` LIKE 'evaluasi'");
    $check_db->execute();
    $check_db = $check_db->fetch();

    if ($check_db) {
      $_POST['nip'] = $this->core->getUserInfo('username', null, true);
      $_POST['verified_at'] = null;
    } else {
      unset($_POST['evaluasi']);
    }

    if (!$this->db('pemeriksaan_ranap')->where('no_rawat', $_POST['no_rawat'])->where('tgl_perawatan', $_POST['tgl_perawatan'])->where('jam_rawat', $_POST['jam_rawat'])->oneArray()) {
      $this->db('pemeriksaan_ranap')->save($_POST);
    } else {
      
      $cariSoap = $this->db('pemeriksaan_ranap')->where('no_rawat', $_POST['no_rawat'])->where('tgl_perawatan', $_POST['tgl_perawatan'])->where('jam_rawat', $_POST['jam_rawat'])->oneArray();
      $noRawat = $cariSoap['no_rawat'];
      $tgl_perawatan = $cariSoap['tgl_perawatan'];
      $jam = $cariSoap['jam_rawat'];
      $suhu_tubuh = $cariSoap['suhu_tubuh'];
      $tensi = $cariSoap['tensi'];
      $nadi = $cariSoap['nadi'];
      $respirasi = $cariSoap['respirasi'];
      $tinggi = $cariSoap['tinggi'];
      $berat = $cariSoap['berat'];
      $spo2 = $cariSoap['spo2'];
      $gcs = $cariSoap['gcs'];
      $keluhan = $cariSoap['keluhan'];
      $pemeriksaan = $cariSoap['pemeriksaan'];
      $alergi = $cariSoap['alergi'];
      $penilaian = $cariSoap['penilaian'];
      $rtl = $cariSoap['rtl'];
      $instruksi = $cariSoap['instruksi'];
      $evaluasi = $cariSoap['evaluasi'];
      $nip = $cariSoap['nip'];
      $value = '{"data":{"no_rawat":"'.$noRawat.'","tgl_perawatan":"'.$tgl_perawatan.'","jam_rawat":"'.$jam.'","suhu_tubuh":"'.$suhu_tubuh.'","tensi":"'.$tensi.'","nadi":"'.$nadi.'","respirasi":"'.$respirasi.'","berat":"'.$berat.'","spo2":"'.$spo2.'","gcs":"'.$gcs.'","keluhan":"'.$keluhan.'","pemeriksaan":"'.$pemeriksaan.'","alergi":"'.$alergi.'","penilaian":"'.$penilaian.'","rtl":"'.$rtl.'","instruksi":"'.$instruksi.'","evaluasi":"'.$evaluasi.'","nip":"'.$nip.'"},"action":"Edit_"}'; 
      $this->db('mlite_log')->save([
          'username' => $this->core->getUserInfo('username', null, true),
          'group_table' => 'pemeriksaan_ranap',
          'value_field' => $value,
          'created_at' => date('Y-m-d H:i:s')
      ]);
      $this->db('pemeriksaan_ranap')->where('no_rawat', $_POST['no_rawat'])->where('tgl_perawatan', $_POST['tgl_perawatan'])->where('jam_rawat', $_POST['jam_rawat'])->save($_POST);
    }
    // echo json_encode($_POST);
    exit();
  }

  public function postHapusSOAP()
  {
    $cariSoap = $this->db('pemeriksaan_ranap')->where('no_rawat', $_POST['no_rawat'])->where('tgl_perawatan', $_POST['tgl_perawatan'])->where('jam_rawat', $_POST['jam_rawat'])->oneArray();
    $noRawat = $cariSoap['no_rawat'];
    $tgl_perawatan = $cariSoap['tgl_perawatan'];
    $jam = $cariSoap['jam_rawat'];
    $suhu_tubuh = $cariSoap['suhu_tubuh'];
    $tensi = $cariSoap['tensi'];
    $nadi = $cariSoap['nadi'];
    $respirasi = $cariSoap['respirasi'];
    $tinggi = $cariSoap['tinggi'];
    $berat = $cariSoap['berat'];
    $spo2 = $cariSoap['spo2'];
    $gcs = $cariSoap['gcs'];
    $keluhan = $cariSoap['keluhan'];
    $pemeriksaan = $cariSoap['pemeriksaan'];
    $alergi = $cariSoap['alergi'];
    $penilaian = $cariSoap['penilaian'];
    $rtl = $cariSoap['rtl'];
    $instruksi = $cariSoap['instruksi'];
    $evaluasi = $cariSoap['evaluasi'];
    $nip = $cariSoap['nip'];
    $value = '{"data":{"no_rawat":"'.$noRawat.'","tgl_perawatan":"'.$tgl_perawatan.'","jam_rawat":"'.$jam.'","suhu_tubuh":"'.$suhu_tubuh.'","tensi":"'.$tensi.'","nadi":"'.$nadi.'","respirasi":"'.$respirasi.'","berat":"'.$berat.'","spo2":"'.$spo2.'","gcs":"'.$gcs.'","keluhan":"'.$keluhan.'","pemeriksaan":"'.$pemeriksaan.'","alergi":"'.$alergi.'","penilaian":"'.$penilaian.'","rtl":"'.$rtl.'","instruksi":"'.$instruksi.'","evaluasi":"'.$evaluasi.'","nip":"'.$nip.'"},"action":"Hapus_"}'; 
    $this->db('mlite_log')->save([
        'username' => $this->core->getUserInfo('username', null, true),
        'group_table' => 'pemeriksaan_ranap',
        'value_field' => $value,
        'created_at' => date('Y-m-d H:i:s')
    ]);
    $this->db('pemeriksaan_ranap')->where('no_rawat', $_POST['no_rawat'])->where('tgl_perawatan', $_POST['tgl_perawatan'])->where('jam_rawat', $_POST['jam_rawat'])->delete();
    exit();
  }

  public function anyLayanan()
    {
      $layanan = $this->db('jns_perawatan_inap')
        ->where('status', '1')
        ->like('nm_perawatan', '%'.$_POST['layanan'].'%')
        ->limit(10)
        ->toArray();
      echo $this->draw('layanan.html', ['layanan' => $layanan]);
      exit();
  }

  public function anyObat()
  {
    $obat = $this->db('databarang')
      ->join('gudangbarang', 'gudangbarang.kode_brng=databarang.kode_brng')
      ->where('status', '1')
      ->where('gudangbarang.kd_bangsal', $this->settings->get('farmasi.deporanap'))
      ->like('databarang.nama_brng', '%' . $_POST['obat'] . '%')
      ->limit(10)
      ->toArray();
    echo $this->draw('obat.html', ['obat' => $obat]);
    exit();
  }

  public function postAturanPakai()
  {

    if (isset($_POST["query"])) {
      $output = '';
      $key = "%" . $_POST["query"] . "%";
      $rows = $this->db('master_aturan_pakai')->like('aturan', $key)->limit(10)->toArray();
      $output = '';
      if (count($rows)) {
        foreach ($rows as $row) {
          $output .= '<li class="list-group-item link-class">' . $row["aturan"] . '</li>';
        }
      }
      echo $output;
    }

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
    $berkas_digital = $this->db('berkas_digital_perawatan')->join('master_berkas_digital', 'master_berkas_digital.kode=berkas_digital_perawatan.kode')->where('no_rawat', $_POST['no_rawat'])->toArray();
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
      $imgName = time() . $cntr++;
      $imgPath = $dir . '/' . $id . '_' . $imgName . '.' . $img->getInfos('type');
      $lokasi_file = 'pages/upload/' . $id . '_' . $imgName . '.' . $img->getInfos('type');
      $img->save($imgPath);
      $query = $this->db('berkas_digital_perawatan')->save(['no_rawat' => $_POST['no_rawat'], 'kode' => $_POST['kode'], 'lokasi_file' => $lokasi_file]);
      if ($query) {
        echo '<br><img src="' . WEBAPPS_URL . '/berkasrawat/' . $lokasi_file . '" width="150" />';
      }
    }

    exit();
  }

  public function getHapusBerkas($no_rawat, $nama_file)
  {
    $berkasPerawatan = $this->db('berkas_digital_perawatan')->where('no_rawat', revertNorawat($no_rawat))->like('lokasi_file', '%'.$nama_file.'%')->oneArray();
    if ($berkasPerawatan) {
      $lokasi_file = $berkasPerawatan['lokasi_file'];
      $fileLoc = WEBAPPS_PATH . '/berkasrawat/' . $lokasi_file;
      if (file_exists($fileLoc)) {
        //unlink($fileLoc);
        $query = $this->db('berkas_digital_perawatan')->where('no_rawat', revertNorawat($no_rawat))->where('lokasi_file', $lokasi_file)->delete();
        $cetakBerkas = json_encode($berkasPerawatan, JSON_UNESCAPED_SLASHES);
        $this->db('mlite_log')->save([
                'username' => $this->core->getUserInfo('username', null, true),
                'group_table' => 'berkasdigital',
                'value_field' => $cetakBerkas,
                'created_at' => date('Y-m-d H:i:s')
            ]);
        // echo $cetakBerkas;

        if ($query) {
          echo 'Hapus berkas sukses';
        } else {
          echo 'Hapus berkas gagal';
        }
      } else {
        echo 'Hapus berkas gagal, berkas tidak ditemukan.';
      }
    } else {
      echo 'Hapus berkas gagal, tidak ada data perawatan.';
    }
    exit();
  }

  public function anyHais()
  {

    $i = 1;
    $rows = $this->db('data_HAIs')
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

      $result[] = $row;
    }

    echo $this->draw('hais.html', ['hais' => $result]);
    exit();
  }

  public function postSaveHAIS()
  {
    $is_edit = $_POST['edit'];
    unset($_POST['edit']);
    if (!$this->db('data_HAIs')->where('no_rawat', $_POST['no_rawat'])->where('tanggal', $_POST['tanggal'])->oneArray()) {
      $this->db('data_HAIs')->save($_POST);
    } else if ($is_edit) {
      $this->db('data_HAIs')->where('no_rawat', $_POST['no_rawat'])->where('tanggal', $_POST['tanggal'])->save($_POST);
    }
    exit();
  }

  public function postHapusHAIS()
  {
    $this->db('data_HAIs')->where('no_rawat', $_POST['no_rawat'])->where('tanggal', $_POST['tanggal'])->delete();
    exit();
  }


  public function anyDietPasien()
  {

    $i = 1;
    $rows = $this->db('detail_beri_diet')
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

      $row['nm_pasien'] = $pasien['nm_pasien'];

      

      $row['diagnosa'] = $this->db('diagnosa_pasien')
        ->select(['nm_penyakit' => 'penyakit.nm_penyakit'])
        ->join('penyakit', 'penyakit.kd_penyakit=diagnosa_pasien.kd_penyakit')
        ->where('no_rawat', $row['no_rawat'])
        ->asc('prioritas')
        ->toArray();

      $diet = $this->db('diet')
        ->where('kd_diet', $row['kd_diet'])
        ->oneArray();
      $row['nama_diet'] = $diet['nama_diet'];

      $result[] = $row;
    }

    echo $this->draw('dietpasien.html', ['dietpasien' => $result]);
    exit();
  }


  public function postDiet()
  {

    if (isset($_POST["query"])) {
      $output = '';
      $key = "%" . $_POST["query"] . "%";
      $rows = $this->db('diet')->like('nama_diet', $key)->limit(10)->toArray();
      $output = '';
      if (count($rows)) {
        foreach ($rows as $row) {
          $output .= '<li data-id="' . $row['kd_diet'] . '" class="list-group-item link-class">' . $row["nama_diet"] . '</li>';
        }
      }
      echo $output;
    }

    exit();
  }

  public function postSaveDietPasien()
  {
    if (!$this->db('detail_beri_diet')->where('no_rawat', $_POST['no_rawat'])->where('tanggal', $_POST['tanggal'])->where('waktu', $_POST['waktu'])->oneArray()) {
      $this->db('detail_beri_diet')->save($_POST);
    } else {
      $this->db('detail_beri_diet')->where('no_rawat', $_POST['no_rawat'])->where('tanggal', $_POST['tanggal'])->where('waktu', $_POST['waktu'])->save($_POST);
    }
    exit();
  }

  public function postHapusDietPasien()
  {
    $this->db('detail_beri_diet')->where('no_rawat', $_POST['no_rawat'])->where('tanggal', $_POST['tanggal'])->where('waktu', $_POST['waktu'])->delete();
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

      $kamar_inap = $this->db('kamar_inap')
        // ->join('pasien', 'pasien.no_rkm_medis=reg_periksa.no_rkm_medis')
        ->where('no_rawat', $row['no_rawat'])
        ->oneArray();
      $row['kd_kamar'] = $kamar_inap['kd_kamar'];

      $row['diagnosa'] = $this->db('diagnosa_pasien')
        ->select(['nm_penyakit' => 'penyakit.nm_penyakit'])
        ->join('penyakit', 'penyakit.kd_penyakit=diagnosa_pasien.kd_penyakit')
        ->where('no_rawat', $row['no_rawat'])
        ->asc('prioritas')
        ->toArray();

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

  public function anyFormKerohanian()
  {
    $this->_addHeaderFiles();
    $i = 1;

    $this->getSelectBootstrap();
    $selectrohani = $this->getInfoJenisRoh();
    $this->anyKerohanian($_POST['no_rawat']);
    echo $this->draw('kerohanian.html', ['kerohanian' => $this->assign,'select33' => $selectrohani]);
    exit();
  }

  public function anyDisplayKerohanian()
  {
    $this->_addHeaderFiles();
    $i = 1;

    $this->getSelectBootstrap();
    $selectrohani = $this->getInfoJenisRoh();
    $this->anyKerohanian($_POST['no_rawat']);
    echo $this->draw('rohani.html', ['kerohanian' => $this->assign,'select33' => $selectrohani]);
    exit();
  }

  public function anyKerohanian($no_rawat)
  {
    $this->_addHeaderFiles();
    $i = 1;

    $rows = $this->db('permintaan_kerohanian')
      ->where('no_rawat', $no_rawat)
      ->toArray();

    $this->assign['list'] = [];
    foreach ($rows as $row) {
      $row['nomor'] = $i++;

      $pasien = $this->db('reg_periksa')
      ->join('pasien', 'pasien.no_rkm_medis=reg_periksa.no_rkm_medis')
      ->where('no_rawat', $row['no_rawat'])
      ->oneArray();

      $row['nm_pasien'] = $pasien['nm_pasien'];

      $kamar_inap = $this->db('kamar_inap')
        ->where('no_rawat', $row['no_rawat'])
        ->oneArray();
      $row['kd_kamar'] = $kamar_inap['kd_kamar'];

      $perujuk = $this->db('petugas')
        ->where('nip', $row['perujuk'])
        ->oneArray();
      $row['nm_perujuk'] = $perujuk['nama'];

      $petugas = $this->db('petugas')
       ->where('nip', $row['petugas'])
       ->oneArray();
      $row['nm_petugas'] = $petugas['nama'];

      $row['ppk'] = $this->db('permintaan_pemeriksaan_kerohanian')
        ->select(['nama_rh' => 'jns_kerohanian.nama_rh'])
        ->join('jns_kerohanian', 'jns_kerohanian.kd_rh=permintaan_pemeriksaan_kerohanian.kd_rh')
        ->where('noorder', $row['noorder'])
        ->toArray();

      $this->assign['list'][] = $row;
    }
  }

  public function postSaveKerohanian()
  {
    $no_rawat = $_POST['no_rawat'];
    $noorder = $_POST['noorder'];
    $_POST['kd_rh'] = implode(',', $_POST['kd_rh']);
    $cek_noraw = $this->db('permintaan_kerohanian')->where('no_rawat',$no_rawat)->where('tgl_permintaan', $_POST['tgl_permintaan'])->oneArray();
    if (!$cek_noraw) {
      $max_id = $this->db('permintaan_kerohanian')->select(['noorder' => 'ifnull(MAX(CONVERT(RIGHT(noorder,4),signed)),0)'])->where('tgl_permintaan', date('Y-m-d'))->oneArray();
      if (empty($max_id['noorder'])) {
        $max_id['noorder'] = '0000';
      }
      $_next_noorder = sprintf('%04s', ($max_id['noorder'] + 1));
      $noorder = 'PRH' . date('Ymd') . '' . $_next_noorder;

      $this->db('permintaan_kerohanian')
        ->save([
          'noorder' => $noorder,
          'no_rawat' => $_POST['no_rawat'],
          'kd_kamar' => $_POST['kd_kamar'],
          'tgl_permintaan' => $_POST['tgl_permintaan'],
          'perujuk' => $this->core->getUserInfo('username', null, true),
          'petugas' => $_POST['nip'],
          'keterangan' => $_POST['keterangan']
        ]);

      $kd_rh = [];
      $kd_rh = explode(',', $_POST['kd_rh']);
      for ($i = 0; $i < count($kd_rh); $i++) {
        $this->db('permintaan_pemeriksaan_kerohanian')
          ->save([
            'noorder' => $noorder,
            'kd_rh' => $kd_rh[$i],
            'stts' => 'Belum'
          ]);
      }

      echo 200;
      // return $no_rawat;
    }
    exit();
  }

  public function postHapusKerohanian()
  {
    $this->db('permintaan_pemeriksaan_kerohanian')
      ->where('noorder', $_POST['noorder'])
      ->delete();

    $cek_noorder = $this->db('permintaan_pemeriksaan_kerohanian')
      ->where('noorder', $_POST['noorder'])
      ->oneArray();

    if (!$cek_noorder) {
      $this->db('permintaan_kerohanian')
        ->where('noorder', $_POST['noorder'])
        ->where('tgl_permintaan', $_POST['tgl_permintaan'])
        ->delete();
    }
    exit();
  }

  public function postPetugas()
  {
    if (isset($_POST["query"])) {
      $output = '';
      $key = "%" . $_POST["query"] . "%";
      $rows = $this->db('petugas')->like('nama', $key)->limit(10)->toArray();
      $output = '';
      if (count($rows)) {
        foreach ($rows as $row) {
          $output .= '<li data-id="' . $row['nip'] . '" class="list-group-item link-class">' . $row["nama"] . '</li>';
        }
      }
      echo $output;
    }
    exit();
  }

  public function postNoRoh()
  {
    $date = $_POST['tgl_permintaan'];
    $last_no_order = $this->db()->pdo()->prepare("SELECT ifnull(MAX(CONVERT(RIGHT(noorder,4),signed)),0) FROM permintaan_kerohanian WHERE tgl_permintaan = '$date'");
    $last_no_order->execute();
    $last_no_order = $last_no_order->fetch();
    if (empty($last_no_order[0])) {
      $last_no_order[0] = '0000';
    }
    $next_no_order = sprintf('%04s', ($last_no_order[0] + 1));
    $next_no_order = 'PRH' . date('Ymd') . '' . $next_no_order;

    echo $next_no_order;
    exit();
  }

  public function getInfoJenisRoh($kd_rh = null)
  {
    $result = [];
    $rows = $this->db()->pdo()->prepare("SELECT kd_rh, nama_rh FROM jns_kerohanian");
    //SELECT `kd_rh`, `nama_rh` FROM `jns_kerohanian`;
    $rows->execute();
    $rows = $rows->fetchAll();

    if (!$kd_rh) {
      $kd_rhArray = [];
    } else {
      $kd_rhArray = explode(',', $kd_rh);
    }

    foreach ($rows as $row) {
      if (empty($kd_rhArray)) {
        $attr = '';
      } else {
        if (in_array($row['kd_rh'], $kd_rhArray)) {
          $attr = 'selected';
        } else {
          $attr = '';
        }
      }
      $result[] = ['kd_rh' => $row['kd_rh'], 'nama_rh' => $row['nama_rh'], 'attr' => $attr];
    }
    return $result;
  }

     public function anyOrthanc()
{
    $rows = $this->db('reg_periksa')->where('no_rawat', $_POST['no_rawat'])->toArray();

    $result = [];
    foreach ($rows as $row) {
        $no_rawat = $row['no_rawat'];

        $tgl1 = $this->db('periksa_radiologi')->where('no_rawat', $row['no_rawat'])->limit(1)->asc('tgl_periksa')->oneArray();
        $tgl2 = $this->db('periksa_radiologi')->where('no_rawat', $row['no_rawat'])->limit(1)->desc('tgl_periksa')->oneArray();

        $pacs = [];
        $norm = $row['no_rkm_medis'];
        $tanggal1 = str_replace("-", "", $tgl1['tgl_periksa']);
        $tanggal2 = str_replace("-", "", $tgl2['tgl_periksa']);

        $arr = array(
            "Level" => "Study",
            "Expand" => true,
            "Query" => array(
                "StudyDate" => $tanggal1 . "-" . $tanggal2,
                "PatientID" => $norm
            )
        );

        $pacs['data'] = json_encode($arr);

        $url_orthanc = $this->settings->get('orthanc.server');
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
            foreach ($patient as $study) {
                foreach ($study["Series"] as $series) {
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

                    $resultItem = array(
                    'Tanggal' => date('d-m-Y', strtotime($seriesDate)),
                    'Deskripsi' => $acquisitionDescription,
                    'Gambar' => '',
                    );

                      foreach ($seriesData['Instances'] as $instance) {
                      $imageURL = $url_orthanc . '/instances/' . $instance . '/rendered/?width=500';
                        // $imageURL = $url_orthanc . '/instances/' . $instance . '/preview';
                        $resultItem['Gambar'] .= '<a href="' . $url_orthanc . '/web-viewer/app/viewer.html?series=' . $series . '" target="_blank">';
                        $resultItem['Gambar'] .= '<img src="' . $imageURL . '" alt="Image" style="width: 600px;">';
                        $resultItem['Gambar'] .= '</a><br><br><br>';
                      }
                    $result[] = $resultItem;
                  }
              }
          }
      }

    echo $this->draw('data.orthanc.html', ['pacs' => $result]);
    exit();
  }

  public function postProviderList()
  {

    if (isset($_POST["query"])) {
      $output = '';
      $key = "%" . $_POST["query"] . "%";
      $rows = $this->db('dokter')->like('nm_dokter', $key)->where('status', '1')->limit(10)->toArray();
      $output = '';
      if (count($rows)) {
        foreach ($rows as $row) {
          $output .= '<li class="list-group-item link-class">' . $row["kd_dokter"] . ': ' . $row["nm_dokter"] . '</li>';
        }
      }
      echo $output;
    }

    exit();
  }

  public function postProviderList2()
  {

    if (isset($_POST["query"])) {
      $output = '';
      $key = "%" . $_POST["query"] . "%";
      $rows = $this->db('petugas')->like('nama', $key)->limit(10)->toArray();
      $output = '';
      if (count($rows)) {
        foreach ($rows as $row) {
          $output .= '<li class="list-group-item link-class">' . $row["nip"] . ': ' . $row["nama"] . '</li>';
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
  
   public function postProviderList3()
  {

    if (isset($_POST["query"])) {
      $output = '';
      $key = "%" . $_POST["query"] . "%";
      $rows = $this->db('mlite_users')->like('fullname', $key)->where('role', 'apoteker')->limit(10)->toArray();
      $output = '';
      if (count($rows)) {
        foreach ($rows as $row) {
          $output .= '<li class="list-group-item link-class">' . $row["username"] . ': ' . $row["fullname"] . '</li>';
        }
      }
      echo $output;
    }

    exit();
  }

  public function postProviderList4()
  {

    if (isset($_POST["query"])) {
      $output = '';
      $key = "%" . $_POST["query"] . "%";
      $rows = $this->db('mlite_users')->like('fullname', $key)->where('role', 'gizi')->limit(10)->toArray();
      $output = '';
      if (count($rows)) {
        foreach ($rows as $row) {
          $output .= '<li class="list-group-item link-class">' . $row["username"] . ': ' . $row["fullname"] . '</li>';
        }
      }
      echo $output;
    }

    exit();
  }
  
  public function postMaxid()
  {
    $max_id = $this->db('reg_periksa')->select(['no_rawat' => 'ifnull(MAX(CONVERT(RIGHT(no_rawat,6),signed)),0)'])->where('tgl_registrasi', date('Y-m-d'))->oneArray();
    if (empty($max_id['no_rawat'])) {
      $max_id['no_rawat'] = '000000';
    }
    $_next_no_rawat = sprintf('%06s', ($max_id['no_rawat'] + 1));
    $next_no_rawat = date('Y/m/d') . '/' . $_next_no_rawat;
    echo $next_no_rawat;
    exit();
  }

  public function postMaxAntrian()
  {
    $max_id = $this->db('reg_periksa')->select(['no_reg' => 'ifnull(MAX(CONVERT(RIGHT(no_reg,3),signed)),0)'])->where('kd_poli', $_POST['kd_poli'])->where('tgl_registrasi', date('Y-m-d'))->desc('no_reg')->limit(1)->oneArray();
    if (empty($max_id['no_reg'])) {
      $max_id['no_reg'] = '000';
    }
    $_next_no_reg = sprintf('%03s', ($max_id['no_reg'] + 1));
    echo $_next_no_reg;
    exit();
  }
  
   public function anyResume()
  {
      
      $no_rawat = $_GET['no_rawat'];
      $rows = $this->db('resume_pasien_ranap')
      ->where('no_rawat', $no_rawat)
      ->toArray();
      $result = [];
      foreach ($rows as $row) {

      $pasien = $this->db('reg_periksa')
        ->join('pasien', 'pasien.no_rkm_medis=reg_periksa.no_rkm_medis')
        ->join('penjab', 'penjab.kd_pj=reg_periksa.kd_pj')
        ->where('no_rawat', $row['no_rawat'])
        ->oneArray();
      $row['no_rkm_medis'] = $pasien['no_rkm_medis'];
      $row['nm_pasien'] = $pasien['nm_pasien'];
      $row['tgl_lahir'] = date('d-m-Y', strtotime($pasien['tgl_lahir']));
      $row['png_jawab'] = $pasien['png_jawab'];
    $row['diagnosa_awal'] = htmlspecialchars($row['diagnosa_awal']);
      $row['pemeriksaan_penunjang'] = htmlspecialchars($row['pemeriksaan_penunjang']);
      $tgl_masuk = $this->db('kamar_inap')
      ->where('no_rawat', $row['no_rawat'])
      ->asc('tgl_masuk')
      ->oneArray();

      if (empty($tgl_masuk)) {
          $row['tgl_masuk'] = date('d-m-Y', strtotime($pasien['tgl_lahir']));
          $bayi = $this->db('pasien_bayi')->where('no_rkm_medis', $pasien['no_rkm_medis'])->oneArray();
          $row['jam_masuk'] =  $bayi['jam_lahir'];
      } else {
          $row['tgl_masuk'] = date('d-m-Y', strtotime($tgl_masuk['tgl_masuk']));
          $row['jam_masuk'] = $tgl_masuk['jam_masuk'];
      }

      $tgl_keluar = $this->db('kamar_inap')
          ->where('no_rawat', $row['no_rawat'])
          ->where('stts_pulang', '<>', 'Pindah Kamar')
          ->oneArray();

      if (empty($tgl_keluar['no_rawat'])) {
          $cek_ranapgabung = $this->db('ranap_gabung')
              ->select('no_rawat')
              ->where('no_rawat2', $row['no_rawat'])
              ->oneArray();

          if (!empty($cek_ranapgabung['no_rawat'])) {
              $tgl_keluar_ranapgabung = $this->db('kamar_inap')
              ->where('no_rawat', $cek_ranapgabung['no_rawat'])
              ->where('stts_pulang', '<>', 'Pindah Kamar')
              ->oneArray();

              $tgl_keluar = $tgl_keluar_ranapgabung;
          }
      }

      $row['tgl_keluar']= !empty($tgl_keluar['tgl_keluar']) ? date('d-m-Y', strtotime($tgl_keluar['tgl_keluar'])) : '';
      $row['jam_keluar'] = !empty($tgl_keluar['jam_keluar']) ? $tgl_keluar['jam_keluar'] : '';

      $kamar_inap = $this->db('kamar_inap')
              ->join('kamar', 'kamar.kd_kamar=kamar_inap.kd_kamar')
              ->join('bangsal', 'bangsal.kd_bangsal=kamar.kd_bangsal')
              ->where('no_rawat', $row['no_rawat'])
              ->where('kamar_inap.stts_pulang', '<>', 'Pindah Kamar')
              ->oneArray();

      if (empty($kamar_inap['no_rawat'])) {
          $cek_ranapgabung = $this->db('ranap_gabung')
              ->select('no_rawat')
              ->where('no_rawat2', $row['no_rawat'])
              ->oneArray();

          if (!empty($cek_ranapgabung['no_rawat'])) {
              $kamar_inap_ranapgabung = $this->db('kamar_inap')
              ->join('kamar', 'kamar.kd_kamar=kamar_inap.kd_kamar')
              ->join('bangsal', 'bangsal.kd_bangsal=kamar.kd_bangsal')
              ->where('no_rawat', $cek_ranapgabung['no_rawat'])
               ->where('kamar_inap.stts_pulang', '<>', 'Pindah Kamar')
              ->oneArray();

              $kamar_inap = $kamar_inap_ranapgabung;
          }
      }

      $row['nm_bangsal']= !empty($kamar_inap['nm_bangsal']) ? $kamar_inap['nm_bangsal'] : '';
      $row['kelas']     = !empty($kamar_inap['kelas']) ? $kamar_inap['kelas'] : '';

      $dpjp_utama = $this->db('dpjp_ranap')
        ->join('dokter', 'dokter.kd_dokter=dpjp_ranap.kd_dokter')
        ->where('dpjp_ranap.no_rawat', $row['no_rawat'])
        ->where('dpjp_ranap.jenis_dpjp', 'Utama')
        ->oneArray();
       $row['dpjp_utama'] = $dpjp_utama['nm_dokter'];

       $cek_dpjp = $this->db('dpjp_ranap')
        ->join('dokter', 'dokter.kd_dokter=dpjp_ranap.kd_dokter')
        ->where('dpjp_ranap.no_rawat', $row['no_rawat'])
        ->where('dpjp_ranap.kd_dokter', $row['kd_dokter'])
        ->oneArray();
       $row['nm_dokter'] = $cek_dpjp['nm_dokter'];
       $row['jenis_dpjp'] = $cek_dpjp['jenis_dpjp'];

      $skdp = $this->db('booking_registrasi')
         ->join('poliklinik', 'poliklinik.kd_poli=booking_registrasi.kd_poli')
         ->join('kamar_inap', 'kamar_inap.tgl_keluar=booking_registrasi.tanggal_booking')
         ->where('booking_registrasi.no_rkm_medis', $row['no_rkm_medis'])
         ->where('booking_registrasi.tanggal_booking',  $tgl_keluar['tgl_keluar'])
        ->oneArray();
      $row['skdp'] = date('d-m-Y', strtotime($skdp['tanggal_periksa']));
      $row['poli'] = $skdp['nm_poli'];
      
      $shk = $this->db('shk_bayi')
        ->where('no_rawat', $row['no_rawat'])
        ->oneArray();
      $row['shk_bayi'] = $shk['keterangan'];
      
      $ket_keadaan = $row['ket_keadaan'];
      $kode_ket_keadaan = explode(',', $ket_keadaan);
      $dpjp_resume_arr = [];
      foreach ($kode_ket_keadaan as $kode) {
          $cek_dpjp = $this->db('dpjp_ranap')
              ->where('no_rawat', $row['no_rawat'])
              ->where('kd_dokter', $kode)
              ->oneArray();
          $cek_dok = $this->db('dokter')
              ->where('kd_dokter', $cek_dpjp['kd_dokter'])
              ->oneArray();
          if ($cek_dpjp) {
              $dpjp_resume_arr[] = $cek_dok['nm_dokter'] . ' (' . $cek_dpjp['jenis_dpjp'] . ')';
          }
      }
      $dpjp_resume_str = implode('<br>', $dpjp_resume_arr);
      $row['dpjp_resume'] = $dpjp_resume_str;

          $result[] = $row;
    }
      echo $this->draw('resume.html', ['resume_pasien' => $result]);
      exit();
  }

  public function anyLapop()
  {
      
      $no_rawat = $_GET['no_rawat'];
      $rows = $this->db('mlite_lap_op')
        ->join('dokter', 'dokter.kd_dokter=mlite_lap_op.kd_dokter')
        ->where('mlite_lap_op.no_rawat', $no_rawat)
        ->desc('tanggal_op')
        ->isNull('deleted_at')
        ->toArray();
      $result = [];
      foreach ($rows as $row) {

      $pasien = $this->db('reg_periksa')
        ->join('pasien', 'pasien.no_rkm_medis=reg_periksa.no_rkm_medis')
        ->join('penjab', 'penjab.kd_pj=reg_periksa.kd_pj')
        ->where('no_rawat', $row['no_rawat'])
        ->oneArray();
      $row['no_rkm_medis'] = $pasien['no_rkm_medis'];
      $row['nm_pasien'] = $pasien['nm_pasien'];
      $row['tgl_lahir'] = date('d-m-Y', strtotime($pasien['tgl_lahir']));
      $row['png_jawab'] = $pasien['png_jawab'];
      $row['hasil_op'] = htmlspecialchars($row['hasil_op']);

      $operasi = $this->db('operasi')
            ->select([ 
                'no_rawat'          => 'operasi.no_rawat',
                'tgl_operasi'       => 'operasi.tgl_operasi',
                'asisten_operator1' => 'operasi.asisten_operator1',
                'perawaat_resusitas'=> 'operasi.perawaat_resusitas',
                'dokter_anestesi'   => 'operasi.dokter_anestesi',
                'jenis_anasthesi'   => 'operasi.jenis_anasthesi',
                'kode_paket'        => 'operasi.kode_paket',
                'kategori'          => 'operasi.kategori',
                'nm_asisten'        => 'petugas.nama',
                'nm_perawat'        => 'perawat.nama',
                'dok_anestesi'      => 'dokter.nm_dokter',
                'nm_paket'          => 'paket_operasi.nm_perawatan'
            ])
            ->join('petugas', 'operasi.asisten_operator1=petugas.nip')
            ->join('petugas as perawat', 'operasi.perawaat_resusitas=perawat.nip')
            ->join('dokter', 'operasi.dokter_anestesi=dokter.kd_dokter')
            ->join('paket_operasi', 'operasi.kode_paket=paket_operasi.kode_paket')
            ->where('operasi.no_rawat', $row['no_rawat'])
            ->where('operasi.tgl_operasi', $row['tanggal_op'])
            ->toArray();

        $jadwal_operasi = [];
        foreach ($operasi as $detail_operasi) {
            $tgl_operasi = substr($detail_operasi['tgl_operasi'], 0, 10);
            $bookingOperasi = $this->db('booking_operasi')
                ->where('no_rawat', $detail_operasi['no_rawat'])
                ->where('kode_paket', $detail_operasi['kode_paket'])
                ->where('tanggal', $tgl_operasi)
                ->oneArray();

            $value['booking_operasi'] = [];
            if (!empty($bookingOperasi)) {
              $detail_operasi['tanggal'] = $bookingOperasi['tanggal'];
              $detail_operasi['jam_mulai'] = $bookingOperasi['jam_mulai'];
              $detail_operasi['jam_selesai'] = $bookingOperasi['jam_selesai'];
              //hitung lama operasi
              $jamMulai = strtotime($bookingOperasi['jam_mulai']);
              $jamAkhir = strtotime($bookingOperasi['jam_selesai']);

              if ($jamMulai !== false && $jamAkhir !== false) {
                  $lamaOperasiDetik = $jamAkhir - $jamMulai;
                  $lamaOperasiJam = floor($lamaOperasiDetik / 3600);
                  $lamaOperasiDetik %= 3600;
                  $lamaOperasiMenit = floor($lamaOperasiDetik / 60);
                  $lamaOperasiDetik %= 60;

                if ($lamaOperasiJam > 0) {
                    $lamaOperasi = $lamaOperasiJam . ' jam ' . $lamaOperasiMenit . ' menit ' . $lamaOperasiDetik . ' detik';
                } else {
                    $lamaOperasi = $lamaOperasiMenit . ' menit ' . $lamaOperasiDetik . ' detik';
                }
              $detail_operasi['lama_operasi'] = $lamaOperasi;
            }
          } 
          $jadwal_operasi[] = $detail_operasi;
        }

        $row['jadwal_operasi'] = $jadwal_operasi;


      $result[] = $row;
    }
      echo $this->draw('lapop.html', ['lap_op' => $result]);
      exit();
  }
  
  public function anyManage_RawatGabung()
  {
    $tgl_masuk = '';
    $tgl_masuk_akhir = '';
    $status_pulang = '';
    $nama_pegawai = '';
    $this->assign['stts_pulang'] = [];

    if (isset($_POST['periode_rawat_gabung'])) {
      $tgl_masuk = $_POST['periode_rawat_gabung'];
    }
    if (isset($_POST['periode_rawat_gabung_akhir'])) {
      $tgl_masuk_akhir = $_POST['periode_rawat_gabung_akhir'];
    }
    if (isset($_POST['status_pulang'])) {
      $status_pulang = $_POST['status_pulang'];
    }
    $cek_vclaim = $this->db('mlite_modules')->where('dir', 'vclaim')->oneArray();

    $username = $this->core->getUserInfo('username', null, true);

    $master_berkas_digital = $this->db('master_berkas_digital')->toArray();
    
    $this->_DisplayRawatGabung($tgl_masuk, $tgl_masuk_akhir, $status_pulang);
    return $this->draw('manage.rawatgabung.html', [
      'rawat_gabung' => $this->assign, 
      'cek_vclaim' => $cek_vclaim, 
      'master_berkas_digital' => $master_berkas_digital, 
      'username' => $username
    ]);
  }

  public function anyDisplayRawatGabung()
  {
    $tgl_masuk = '';
    $tgl_masuk_akhir = '';
    $status_pulang = '';
    $this->assign['stts_pulang'] = [];

    if (isset($_POST['periode_rawat_gabung'])) {
      $tgl_masuk = $_POST['periode_rawat_gabung'];
    }
    if (isset($_POST['periode_rawat_gabung_akhir'])) {
      $tgl_masuk_akhir = $_POST['periode_rawat_gabung_akhir'];
    }
    if (isset($_POST['status_pulang'])) {
      $status_pulang = $_POST['status_pulang'];
    }
    $cek_vclaim = $this->db('mlite_modules')->where('dir', 'vclaim')->oneArray();

    $username = $this->core->getUserInfo('username', null, true);

    $this->_DisplayRawatGabung($tgl_masuk, $tgl_masuk_akhir, $status_pulang);
    echo $this->draw('display.rawatgabung.html', ['rawat_gabung' => $this->assign, 'cek_vclaim' => $cek_vclaim, 'username' => $username]);
    exit();
  }

    public function _DisplayRawatGabung($tgl_masuk = '', $tgl_masuk_akhir = '', $status_pulang = '')
  {
    $this->_addHeaderFiles();

    $this->assign['kamar'] = $this->db('kamar')->join('bangsal', 'bangsal.kd_bangsal=kamar.kd_bangsal')->where('statusdata', '1')->toArray();
    $this->assign['dokter'] = $this->db('dokter')->where('status', '1')->toArray();
    $this->assign['penjab']   = $this->db('penjab')->where('status', '1')->toArray();
    $this->assign['no_rawat'] = '';

    $bangsal = str_replace(",", "','", $this->core->getUserInfo('cap', null, true));

    $sql = "SELECT 
            ranap_gabung.*,
            kamar_inap.*,
            reg_periksa.*,
            pasien.*,
            kamar.*,
            bangsal.*,
            penjab.*
          FROM
            ranap_gabung,
            kamar_inap,
            reg_periksa,
            pasien,
            kamar,
            bangsal,
            penjab
          WHERE
            ranap_gabung.no_rawat2 = reg_periksa.no_rawat
          AND
            ranap_gabung.no_rawat = kamar_inap.no_rawat
          AND
            reg_periksa.no_rkm_medis = pasien.no_rkm_medis
          AND
            kamar_inap.kd_kamar=kamar.kd_kamar
          AND
            bangsal.kd_bangsal=kamar.kd_bangsal
          AND
            reg_periksa.kd_pj = penjab.kd_pj";

     $username = $this->core->getUserInfo('username', null, true);
    if (!in_array($this->core->getUserInfo('role'), ['admin', 'medis','apoteker', 'laboratorium', 'radiologi', 'manajemen', 'gizi', 'ppi/mpp', 'ok', 'paramedis', 'rekammedis'],  true)){
      $sql .= " AND bangsal.kd_bangsal IN ('$bangsal')";
    }
    if ($status_pulang == '') {
      $sql .= " AND kamar_inap.stts_pulang = '-' GROUP BY ranap_gabung.no_rawat";
    }
    if ($status_pulang == 'all' && $tgl_masuk !== '' && $tgl_masuk_akhir !== '') {
      $sql .= " AND kamar_inap.stts_pulang = '-' AND kamar_inap.tgl_masuk BETWEEN '$tgl_masuk' AND '$tgl_masuk_akhir'";
    }
    if ($status_pulang == 'masuk' && $tgl_masuk !== '' && $tgl_masuk_akhir !== '') {
      $sql .= " AND kamar_inap.tgl_masuk BETWEEN '$tgl_masuk' AND '$tgl_masuk_akhir'";
    }
    if ($status_pulang == 'pulang' && $tgl_masuk !== '' && $tgl_masuk_akhir !== '') {
      $sql .= " AND kamar_inap.tgl_keluar BETWEEN '$tgl_masuk' AND '$tgl_masuk_akhir'";
    }

    $stmt = $this->db()->pdo()->prepare($sql);
    $stmt->execute();
    $rows = $stmt->fetchAll();

    $this->assign['list'] = [];
    foreach ($rows as $row) {
      $dpjp_ranap = $this->db('dpjp_ranap')
        ->join('dokter', 'dokter.kd_dokter=dpjp_ranap.kd_dokter')
        ->where('no_rawat', $row['no_rawat'])
        ->toArray();
      $row['dokter'] = $dpjp_ranap;
      $row['con_no_rawat'] = convertNorawat($row['no_rawat']);
      $this->assign['list'][] = $row;
    }

    if (isset($_POST['no_rawat'])) {
      $this->assign['kamar_inap'] = $this->db('kamar_inap')
        ->join('reg_periksa', 'reg_periksa.no_rawat=kamar_inap.no_rawat')
        ->join('pasien', 'pasien.no_rkm_medis=reg_periksa.no_rkm_medis')
        ->join('kamar', 'kamar.kd_kamar=kamar_inap.kd_kamar')
        ->join('dpjp_ranap', 'dpjp_ranap.no_rawat=kamar_inap.no_rawat')
        ->join('dokter', 'dokter.kd_dokter=dpjp_ranap.kd_dokter')
        ->join('penjab', 'penjab.kd_pj=reg_periksa.kd_pj')
        ->where('kamar_inap.no_rawat', $_POST['no_rawat'])
        ->oneArray();
    } else {
      $this->assign['kamar_inap'] = [
        'tgl_masuk' => date('Y-m-d'),
        'jam_masuk' => date('H:i:s'),
        'tgl_keluar' => date('Y-m-d'),
        'jam_keluar' => date('H:i:s'),
        'no_rkm_medis' => '',
        'nm_pasien' => '',
        'no_rawat' => '',
        'kd_dokter' => '',
        'kd_kamar' => '',
        'kd_pj' => '',
        'diagnosa_awal' => '',
        'diagnosa_akhir' => '',
        'stts_pulang' => '',
        'lama' => ''
      ];
    }
  }
  
   public function postShkBayi()
  {
      $no_rawat = $_POST['no_rawat'];
      $keterangan = $_POST['keterangan'];
      $cek_shk = $this->db('shk_bayi')->where('no_rawat', $no_rawat)->toArray();

      if ($cek_shk) {
          $this->db('shk_bayi')->where('no_rawat', $no_rawat)->update(['keterangan' => $keterangan]);
      } else {
          $this->db('shk_bayi')->save(['no_rawat' => $no_rawat, 'keterangan' => $keterangan]);
      }

      exit();
  }

  function postCariShk() {
    $rows_shk = [];
    $ket = $this->db('shk_bayi')
      ->select(['keterangan'])
      ->where('no_rawat', $_POST['no_rawat'])
      ->oneArray();
      $rows_shk['keterangan'] = $ket['keterangan'];
      echo json_encode($rows_shk);
      exit();
  }

  public function postHapusShk()
  {
    $this->db('shk_bayi')->where('no_rawat', $_POST['no_rawat'])->delete();
    exit();
  }
  
  public function anyManage_ListResume()
  {
    $tgl_masuk = '';
    $tgl_masuk_akhir = '';
    $status_pulang = '';
    $nama_pegawai = '';
    $this->assign['stts_pulang'] = [];

    if (isset($_POST['periode_resume'])) {
      $tgl_masuk = $_POST['periode_resume'];
    }
    if (isset($_POST['periode_resume_akhir'])) {
      $tgl_masuk_akhir = $_POST['periode_resume_akhir'];
    }
    if (isset($_POST['status_pulang'])) {
      $status_pulang = $_POST['status_pulang'];
    }

    $username = $this->core->getUserInfo('username', null, true);
    
    $this->_DisplayResume($tgl_masuk, $tgl_masuk_akhir, $status_pulang);
    return $this->draw('manage.resume.html', [
      'listresume' => $this->assign,  
      'username' => $username
    ]);
  }

   public function anyDisplayListResume()
  {
   $tgl_masuk = '';
    $tgl_masuk_akhir = '';
    $status_pulang = '';
    $this->assign['stts_pulang'] = [];

    if (isset($_POST['periode_resume'])) {
      $tgl_masuk = $_POST['periode_resume'];
    }
    if (isset($_POST['periode_resume_akhir'])) {
      $tgl_masuk_akhir = $_POST['periode_resume_akhir'];
    }
    if (isset($_POST['status_pulang'])) {
      $status_pulang = $_POST['status_pulang'];
    }

    $username = $this->core->getUserInfo('username', null, true);

    $this->_DisplayResume($tgl_masuk, $tgl_masuk_akhir, $status_pulang);
    echo $this->draw('display.resume.html', [
      'listresume' => $this->assign,  
      'username' => $username]);
    exit();
  }

   public function _DisplayResume($tgl_masuk = '', $tgl_masuk_akhir = '', $status_pulang = '')
  {
    $this->_addHeaderFiles();
    $this->core->addCSS(url('https://cdn.datatables.net/buttons/2.2.3/css/buttons.dataTables.min.css'));
    $this->core->addJS(url('https://cdn.datatables.net/buttons/2.2.3/js/dataTables.buttons.min.js'), 'footer');
    $this->core->addJS(url('https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js'), 'footer');
    $this->core->addJS(url('https://cdn.datatables.net/buttons/1.3.1/js/buttons.html5.min.js'), 'footer');
    $this->core->addJS(url('https://cdn.datatables.net/buttons/2.3.2/js/buttons.print.min.js'), 'footer');
    $this->core->addJS(url('https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js'), 'footer');
    $this->core->addJS(url('https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js'), 'footer');
    $this->core->addJS(url('https://cdn.datatables.net/buttons/2.3.2/js/buttons.html5.min.js'), 'footer');



    $this->assign['kamar'] = $this->db('kamar')->join('bangsal', 'bangsal.kd_bangsal=kamar.kd_bangsal')->where('statusdata', '1')->toArray();
    $this->assign['dokter'] = $this->db('dokter')->where('status', '1')->toArray();
    $this->assign['penjab']   = $this->db('penjab')->where('status', '1')->toArray();
    $this->assign['no_rawat'] = '';

    $bangsal = str_replace(",", "','", $this->core->getUserInfo('cap', null, true));
    $date = date('Y-m-d');
    $before = date('Y-m-d', strtotime('-1 month'));

    $sql = "SELECT 
        resume_pasien_ranap.no_rawat, resume_pasien_ranap.kd_dokter as dok,
        resume_pasien_ranap.ket_keadaan, resume_pasien_ranap.ket_keluar, resume_pasien_ranap.ket_dilanjutkan, 
        reg_periksa.no_rkm_medis,
        pasien.nm_pasien,
        kamar_inap.jam_masuk, kamar_inap.tgl_masuk, 
        kamar_inap.jam_keluar, kamar_inap.tgl_keluar,
        kamar.kd_kamar, bangsal.nm_bangsal
        FROM resume_pasien_ranap 
        JOIN kamar_inap ON resume_pasien_ranap.no_rawat = kamar_inap.no_rawat 
        JOIN reg_periksa ON kamar_inap.no_rawat = reg_periksa.no_rawat 
        JOIN pasien ON reg_periksa.no_rkm_medis = pasien.no_rkm_medis 
        JOIN kamar ON kamar_inap.kd_kamar = kamar.kd_kamar 
        JOIN bangsal ON kamar.kd_bangsal = bangsal.kd_bangsal 
        WHERE kamar_inap.stts_pulang NOT IN ('-', 'Pindah Kamar')";

     $username = $this->core->getUserInfo('username', null, true);
    if (!in_array($this->core->getUserInfo('role'), ['admin', 'medis','apoteker', 'laboratorium', 'radiologi', 'manajemen', 'gizi', 'ppi/mpp', 'ok', 'rekammedis'],  true)){
      $sql .= " AND bangsal.kd_bangsal IN ('$bangsal')";
    }

    if ($status_pulang == '') {
      $sql .= " AND kamar_inap.tgl_keluar ='$date' ";
    }
    if ($status_pulang == 'masuk' && $tgl_masuk !== '' && $tgl_masuk_akhir !== '') {
      $sql .= " AND kamar_inap.tgl_masuk BETWEEN '$tgl_masuk' AND '$tgl_masuk_akhir'";
    }
    if ($status_pulang == 'pulang' && $tgl_masuk !== '' && $tgl_masuk_akhir !== '') {
      $sql .= " AND kamar_inap.tgl_keluar BETWEEN '$tgl_masuk' AND '$tgl_masuk_akhir'";
    }

    $stmt = $this->db()->pdo()->prepare($sql);
    $stmt->execute();
    $rows = $stmt->fetchAll();

    $this->assign['list'] = [];
    foreach ($rows as $row) {
       $dpjp_ranap = $this->db('dpjp_ranap')
            ->join('dokter', 'dokter.kd_dokter=dpjp_ranap.kd_dokter')
            ->where('no_rawat', $row['no_rawat'])
            ->toArray();
        $row['dokter'] = $dpjp_ranap;

        $namadok = $this->db('dokter')->where('kd_dokter', $row['dok'])->oneArray();
        $row['namadok'] = $namadok['nm_dokter'];

        $ket_keadaan = $row['ket_keadaan'];
        $kode_ket_keadaan = explode(',', $ket_keadaan);
        $dpjp_resume_arr = [];

        foreach ($kode_ket_keadaan as $kode) {
            $cek_dpjpranap = $this->db('dpjp_ranap')
                ->where('no_rawat', $row['no_rawat'])
                ->where('kd_dokter', $kode)
                ->oneArray();

            $cek_dok = $this->db('dokter')
                ->where('kd_dokter', $cek_dpjpranap['kd_dokter'])
                ->oneArray();

            if ($cek_dpjpranap) {
                $dpjp_resume_arr[] = ' <span class="fa fa-minus-square-o"></span> '. $cek_dok['nm_dokter'];
            }

        }
        $dpjp_resume_str = implode('<br>', $dpjp_resume_arr);
        $row['dpjp_resume'] = $dpjp_resume_str;

        $this->assign['list'][] = $row;
    }
  }
  
  public function anyManage_ListPulang()
  {
    $tgl_masuk = '';
    $tgl_masuk_akhir = '';
    $status_pulang = '';
    $nama_pegawai = '';
    $this->assign['stts_pulang'] = [];

    if (isset($_POST['periode_pasien'])) {
      $tgl_masuk = $_POST['periode_pasien'];
    }
    if (isset($_POST['periode_pasien_akhir'])) {
      $tgl_masuk_akhir = $_POST['periode_pasien_akhir'];
    }
    if (isset($_POST['status_pulang'])) {
      $status_pulang = $_POST['status_pulang'];
    }

    $username = $this->core->getUserInfo('username', null, true);
    
    $this->_DisplayPulang($tgl_masuk, $tgl_masuk_akhir, $status_pulang);
    return $this->draw('manage.pulang.html', [
      'listpulang' => $this->assign,  
      'username' => $username
    ]);
  }

     public function anyDisplayListPulang()
  {
    $tgl_masuk = '';
    $tgl_masuk_akhir = '';
    $status_pulang = '';
    $this->assign['stts_pulang'] = [];

    if (isset($_POST['periode_pasien'])) {
      $tgl_masuk = $_POST['periode_pasien'];
    }
    if (isset($_POST['periode_pasien_akhir'])) {
      $tgl_masuk_akhir = $_POST['periode_pasien_akhir'];
    }
    if (isset($_POST['status_pulang'])) {
      $status_pulang = $_POST['status_pulang'];
    }

    $username = $this->core->getUserInfo('username', null, true);

    $this->_DisplayPulang($tgl_masuk, $tgl_masuk_akhir, $status_pulang);
    echo $this->draw('list.pulang.html', [
      'listpulang' => $this->assign,  
      'username' => $username]);
    exit();
  }

   public function _DisplayPulang($tgl_masuk = '', $tgl_masuk_akhir = '', $status_pulang = '')
  {
    $this->_addHeaderFiles();
    $this->core->addCSS(url('https://cdn.datatables.net/buttons/2.2.3/css/buttons.dataTables.min.css'));
    $this->core->addJS(url('https://cdn.datatables.net/buttons/2.2.3/js/dataTables.buttons.min.js'), 'footer');
    $this->core->addJS(url('https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js'), 'footer');
    $this->core->addJS(url('https://cdn.datatables.net/buttons/1.3.1/js/buttons.html5.min.js'), 'footer');
    $this->core->addJS(url('https://cdn.datatables.net/buttons/2.3.2/js/buttons.print.min.js'), 'footer');
    $this->core->addJS(url('https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js'), 'footer');
    $this->core->addJS(url('https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js'), 'footer');
    $this->core->addJS(url('https://cdn.datatables.net/buttons/2.3.2/js/buttons.html5.min.js'), 'footer');
        
    $this->assign['kamar'] = $this->db('kamar')->join('bangsal', 'bangsal.kd_bangsal=kamar.kd_bangsal')->where('statusdata', '1')->toArray();
    $this->assign['dokter'] = $this->db('dokter')->where('status', '1')->toArray();
    $this->assign['penjab']   = $this->db('penjab')->where('status', '1')->toArray();
    $this->assign['no_rawat'] = '';

    $bangsal = str_replace(",", "','", $this->core->getUserInfo('cap', null, true));
    $date = date('Y-m-d');
    $before = date('Y-m-d', strtotime('-1 month'));

    $sql = "SELECT 
            kamar_inap.no_rawat,
            reg_periksa.no_rkm_medis, 
            pasien.nm_pasien, 
            kamar_inap.jam_masuk, 
            kamar_inap.tgl_masuk, 
            kamar_inap.jam_keluar, 
            kamar_inap.tgl_keluar, 
            kamar.kd_kamar, 
            bangsal.nm_bangsal,
            bangsal.kd_bangsal 
          FROM kamar_inap 
          JOIN reg_periksa ON kamar_inap.no_rawat = reg_periksa.no_rawat 
          JOIN pasien ON reg_periksa.no_rkm_medis = pasien.no_rkm_medis 
          JOIN kamar ON kamar_inap.kd_kamar = kamar.kd_kamar 
          JOIN bangsal ON kamar.kd_bangsal = bangsal.kd_bangsal 
          LEFT JOIN resume_pasien_ranap ON kamar_inap.no_rawat = resume_pasien_ranap.no_rawat 
          WHERE kamar_inap.stts_pulang NOT IN ('-', 'Pindah Kamar') 
          AND resume_pasien_ranap.no_rawat IS NULL ";

     $username = $this->core->getUserInfo('username', null, true);
    if (!in_array($this->core->getUserInfo('role'), ['admin', 'medis','apoteker', 'laboratorium', 'radiologi', 'manajemen', 'gizi', 'ppi/mpp', 'ok', 'rekammedis'],  true)){
      $sql .= " AND bangsal.kd_bangsal IN ('$bangsal')";
    }

    if ($status_pulang == '') {
      $sql .= " AND kamar_inap.tgl_keluar ='$date' ";
    }
    if ($status_pulang == 'masuk' && $tgl_masuk !== '' && $tgl_masuk_akhir !== '') {
      $sql .= " AND kamar_inap.tgl_masuk BETWEEN '$tgl_masuk' AND '$tgl_masuk_akhir'";
    }
    if ($status_pulang == 'pulang' && $tgl_masuk !== '' && $tgl_masuk_akhir !== '') {
      $sql .= " AND kamar_inap.tgl_keluar BETWEEN '$tgl_masuk' AND '$tgl_masuk_akhir'";
    }

    $stmt = $this->db()->pdo()->prepare($sql);
    $stmt->execute();
    $rows = $stmt->fetchAll();

    $this->assign['list'] = [];
    foreach ($rows as $row) {
       $dpjp_ranap = $this->db('dpjp_ranap')
            ->join('dokter', 'dokter.kd_dokter=dpjp_ranap.kd_dokter')
            ->where('no_rawat', $row['no_rawat'])
            ->toArray();
        $row['dokter'] = $dpjp_ranap;

        $this->assign['list'][] = $row;
    }
  }
  
  public function postSetSiapKlaim()
  {
    $this->db('resume_pasien_ranap')->where('no_rawat', $_POST['no_rawat'])->update([
      'ket_keluar' => 'Siap Klaim'
    ]);
    exit();
  }

   public function postHapus_SetSiapKlaim()
  {
    $this->db('resume_pasien_ranap')->where('no_rawat', $_POST['no_rawat'])->update([
      'ket_keluar' => NULL
    ]);
    exit();
  }

   public function anyResume_SiapKlaim()
  {
    
    $this->_addHeaderFiles();
    $bangsal = str_replace(",", "','", $this->core->getUserInfo('cap', null, true));
    $date = date('Y-m-d');
    $sql = "SELECT 
      resume_pasien_ranap.no_rawat, 
      resume_pasien_ranap.kd_dokter as dok, 
      resume_pasien_ranap.ket_keadaan, 
      resume_pasien_ranap.ket_keluar, 
      resume_pasien_ranap.ket_dilanjutkan, 
      pasien.nm_pasien, 
      reg_periksa.no_rkm_medis,
      kamar_inap.jam_masuk, 
      kamar_inap.tgl_masuk, 
      kamar_inap.jam_keluar, 
      kamar_inap.tgl_keluar, 
      kamar.kd_kamar, 
      bangsal.nm_bangsal 
    FROM resume_pasien_ranap 
    JOIN kamar_inap ON resume_pasien_ranap.no_rawat = kamar_inap.no_rawat 
    JOIN reg_periksa ON kamar_inap.no_rawat = reg_periksa.no_rawat 
    JOIN pasien ON reg_periksa.no_rkm_medis = pasien.no_rkm_medis 
    JOIN kamar ON kamar_inap.kd_kamar = kamar.kd_kamar 
    JOIN bangsal ON kamar.kd_bangsal = bangsal.kd_bangsal 
    WHERE kamar_inap.stts_pulang NOT IN ('-', 'Pindah Kamar') 
    AND kamar_inap.tgl_keluar ='$date'
    AND (resume_pasien_ranap.ket_keluar NOT IN ('Siap Klaim') OR resume_pasien_ranap.ket_keluar IS NULL)";


    if (!in_array($this->core->getUserInfo('role'), ['admin', 'medis', 'apoteker', 'laboratorium', 'radiologi', 'manajemen', 'gizi', 'ppi/mpp', 'ok'], true)) {
        $sql .= " AND bangsal.kd_bangsal IN ('$bangsal')";
    }

    $stmt = $this->db()->pdo()->prepare($sql);
    $stmt->execute();
    $rows = $stmt->fetchAll();

    $this->assign['list'] = [];
    foreach ($rows as $row) {
        $dpjp_ranap = $this->db('dpjp_ranap')
            ->join('dokter', 'dokter.kd_dokter=dpjp_ranap.kd_dokter')
            ->where('no_rawat', $row['no_rawat'])
            ->toArray();
        $row['dokter'] = $dpjp_ranap;

        $namadok = $this->db('dokter')->where('kd_dokter', $row['dok'])->oneArray();
        $row['namadok'] = $namadok['nm_dokter'];

        $ket_keadaan = $row['ket_keadaan'];
        $kode_ket_keadaan = explode(',', $ket_keadaan);
        $dpjp_resume_arr = [];

        foreach ($kode_ket_keadaan as $kode) {
            $cek_dpjpranap = $this->db('dpjp_ranap')
                ->where('no_rawat', $row['no_rawat'])
                ->where('kd_dokter', $kode)
                ->oneArray();

            $cek_dok = $this->db('dokter')
                ->where('kd_dokter', $cek_dpjpranap['kd_dokter'])
                ->oneArray();

            if ($cek_dpjpranap) {
                $dpjp_resume_arr[] = ' <span class="fa fa-minus-square-o"></span> '. $cek_dok['nm_dokter'];
            }

        }
        $dpjp_resume_str = implode('<br>', $dpjp_resume_arr);
        $row['dpjp_resume'] = $dpjp_resume_str;

        $this->assign['list'][] = $row;
    }
    
    $username = $this->core->getUserInfo('username', null, true);

    return $this->draw('resume.siapklaim.html', ['setklaim' => $this->assign, 'username' => $username]);
  }


    public function postResume_SiapKlaim()
  {

    $this->_addHeaderFiles();

    if (isset($_POST['submit'])) {
        $date1 = $_POST['periode_pulang'];
        $date2 = $_POST['periode_pulang_akhir'];
        $bangsal = str_replace(",", "','", $this->core->getUserInfo('cap', null, true));

        if (!empty($date1) && !empty($date2)) {
  

    $sql = "SELECT 
        resume_pasien_ranap.no_rawat, 
        resume_pasien_ranap.kd_dokter as dok, 
        resume_pasien_ranap.ket_keadaan, 
        resume_pasien_ranap.ket_dilanjutkan, 
        resume_pasien_ranap.ket_keluar,
        pasien.nm_pasien,
        reg_periksa.no_rkm_medis,
        kamar_inap.jam_masuk, kamar_inap.tgl_masuk, 
        kamar_inap.jam_keluar, kamar_inap.tgl_keluar,
        kamar.kd_kamar, bangsal.nm_bangsal
        FROM resume_pasien_ranap 
        JOIN kamar_inap ON resume_pasien_ranap.no_rawat = kamar_inap.no_rawat 
        JOIN reg_periksa ON kamar_inap.no_rawat = reg_periksa.no_rawat 
        JOIN pasien ON reg_periksa.no_rkm_medis = pasien.no_rkm_medis 
        JOIN kamar ON kamar_inap.kd_kamar = kamar.kd_kamar 
        JOIN bangsal ON kamar.kd_bangsal = bangsal.kd_bangsal 
        WHERE kamar_inap.stts_pulang NOT IN ('-', 'Pindah Kamar')
        AND kamar_inap.tgl_keluar BETWEEN '$date1' AND '$date2'
        AND (resume_pasien_ranap.ket_keluar NOT IN ('Siap Klaim') OR resume_pasien_ranap.ket_keluar IS NULL)";

    if (!in_array($this->core->getUserInfo('role'), ['admin', 'medis', 'apoteker', 'laboratorium', 'radiologi', 'manajemen', 'gizi', 'ppi/mpp', 'ok'], true)) {
        $sql .= " AND bangsal.kd_bangsal IN ('$bangsal')";
    }

    $stmt = $this->db()->pdo()->prepare($sql);
    $stmt->execute();
    $rows = $stmt->fetchAll();

    $this->assign['list'] = [];
    foreach ($rows as $row) {
        $dpjp_ranap = $this->db('dpjp_ranap')
            ->join('dokter', 'dokter.kd_dokter=dpjp_ranap.kd_dokter')
            ->where('no_rawat', $row['no_rawat'])
            ->toArray();
        $row['dokter'] = $dpjp_ranap;

        $namadok = $this->db('dokter')->where('kd_dokter', $row['dok'])->oneArray();
        $row['namadok'] = $namadok['nm_dokter'];

        $ket_keadaan = $row['ket_keadaan'];
        $kode_ket_keadaan = explode(',', $ket_keadaan);
        $dpjp_resume_arr = [];

        foreach ($kode_ket_keadaan as $kode) {
            $cek_dpjpranap = $this->db('dpjp_ranap')
                ->where('no_rawat', $row['no_rawat'])
                ->where('kd_dokter', $kode)
                ->oneArray();

            $cek_dok = $this->db('dokter')
                ->where('kd_dokter', $cek_dpjpranap['kd_dokter'])
                ->oneArray();

            if ($cek_dpjpranap) {
                $dpjp_resume_arr[] = ' <span class="fa fa-minus-square-o"></span> '. $cek_dok['nm_dokter'];
            }

        }
        $dpjp_resume_str = implode('<br>', $dpjp_resume_arr);
        $row['dpjp_resume'] = $dpjp_resume_str;

        $this->assign['list'][] = $row;
        }
      }else {
        $this->anyResume_BatalSet();
      }
    }
    
    $username = $this->core->getUserInfo('username', null, true);
    return $this->draw('resume.siapklaim.html', ['setklaim' => $this->assign, 'username' => $username]);
  }

  public function anyResume_BatalSet()
  {
    
    $this->_addHeaderFiles();
    $bangsal = str_replace(",", "','", $this->core->getUserInfo('cap', null, true));
    $date = date('Y-m-d');
    $sql = "SELECT 
        resume_pasien_ranap.no_rawat, 
        resume_pasien_ranap.kd_dokter as dok, 
        resume_pasien_ranap.ket_keadaan, 
        resume_pasien_ranap.ket_dilanjutkan, 
        resume_pasien_ranap.ket_keluar,
        pasien.nm_pasien,
        reg_periksa.no_rkm_medis,
        kamar_inap.jam_masuk, kamar_inap.tgl_masuk, 
        kamar_inap.jam_keluar, kamar_inap.tgl_keluar,
        kamar.kd_kamar, bangsal.nm_bangsal
        FROM resume_pasien_ranap 
        JOIN kamar_inap ON resume_pasien_ranap.no_rawat = kamar_inap.no_rawat 
        JOIN reg_periksa ON kamar_inap.no_rawat = reg_periksa.no_rawat 
        JOIN pasien ON reg_periksa.no_rkm_medis = pasien.no_rkm_medis 
        JOIN kamar ON kamar_inap.kd_kamar = kamar.kd_kamar 
        JOIN bangsal ON kamar.kd_bangsal = bangsal.kd_bangsal 
        WHERE kamar_inap.stts_pulang NOT IN ('-', 'Pindah Kamar')
        AND kamar_inap.tgl_keluar ='$date'
        AND resume_pasien_ranap.ket_keluar ='Siap Klaim'";


    if (!in_array($this->core->getUserInfo('role'), ['admin', 'medis', 'apoteker', 'laboratorium', 'radiologi', 'manajemen', 'gizi', 'ppi/mpp', 'ok'], true)) {
        $sql .= " AND bangsal.kd_bangsal IN ('$bangsal')";
    }

    $stmt = $this->db()->pdo()->prepare($sql);
    $stmt->execute();
    $rows = $stmt->fetchAll();

    $this->assign['list'] = [];
    foreach ($rows as $row) {
        $dpjp_ranap = $this->db('dpjp_ranap')
            ->join('dokter', 'dokter.kd_dokter=dpjp_ranap.kd_dokter')
            ->where('no_rawat', $row['no_rawat'])
            ->toArray();
        $row['dokter'] = $dpjp_ranap;

        $namadok = $this->db('dokter')->where('kd_dokter', $row['dok'])->oneArray();
        $row['namadok'] = $namadok['nm_dokter'];

        $ket_keadaan = $row['ket_keadaan'];
        $kode_ket_keadaan = explode(',', $ket_keadaan);
        $dpjp_resume_arr = [];

        foreach ($kode_ket_keadaan as $kode) {
            $cek_dpjpranap = $this->db('dpjp_ranap')
                ->where('no_rawat', $row['no_rawat'])
                ->where('kd_dokter', $kode)
                ->oneArray();

            $cek_dok = $this->db('dokter')
                ->where('kd_dokter', $cek_dpjpranap['kd_dokter'])
                ->oneArray();

            if ($cek_dpjpranap) {
                $dpjp_resume_arr[] = ' <span class="fa fa-minus-square-o"></span> '. $cek_dok['nm_dokter'];
            }

        }
        $dpjp_resume_str = implode('<br>', $dpjp_resume_arr);
        $row['dpjp_resume'] = $dpjp_resume_str;

        $this->assign['list'][] = $row;
    }
    $username = $this->core->getUserInfo('username', null, true);

      return $this->draw('resume.batalklaim.html', ['setbatal' => $this->assign, 'username' => $username]);
    }


      public function postResume_BatalSet()
    {

    $this->_addHeaderFiles();

    if (isset($_POST['submit'])) {
        $status_pulang = '';
        $date1 = $_POST['periode_pulang'];
        $date2 = $_POST['periode_pulang_akhir'];
        $bangsal = str_replace(",", "','", $this->core->getUserInfo('cap', null, true));

    if (!empty($date1) && !empty($date2)) {

    $sql = "SELECT 
        resume_pasien_ranap.no_rawat, 
        resume_pasien_ranap.kd_dokter as dok, 
        resume_pasien_ranap.ket_keadaan, 
        resume_pasien_ranap.ket_dilanjutkan, 
        resume_pasien_ranap.ket_keluar,
        pasien.nm_pasien,
        reg_periksa.no_rkm_medis,
        kamar_inap.jam_masuk, kamar_inap.tgl_masuk, 
        kamar_inap.jam_keluar, kamar_inap.tgl_keluar,
        kamar.kd_kamar, bangsal.nm_bangsal
        FROM resume_pasien_ranap 
        JOIN kamar_inap ON resume_pasien_ranap.no_rawat = kamar_inap.no_rawat 
        JOIN reg_periksa ON kamar_inap.no_rawat = reg_periksa.no_rawat 
        JOIN pasien ON reg_periksa.no_rkm_medis = pasien.no_rkm_medis 
        JOIN kamar ON kamar_inap.kd_kamar = kamar.kd_kamar 
        JOIN bangsal ON kamar.kd_bangsal = bangsal.kd_bangsal 
        WHERE kamar_inap.stts_pulang NOT IN ('-', 'Pindah Kamar')
        AND kamar_inap.tgl_keluar BETWEEN '$date1' AND '$date2'
        AND resume_pasien_ranap.ket_keluar ='Siap Klaim'";

    if (!in_array($this->core->getUserInfo('role'), ['admin', 'medis', 'apoteker', 'laboratorium', 'radiologi', 'manajemen', 'gizi', 'ppi/mpp', 'ok'], true)) {
        $sql .= " AND bangsal.kd_bangsal IN ('$bangsal')";
    }

    $stmt = $this->db()->pdo()->prepare($sql);
    $stmt->execute();
    $rows = $stmt->fetchAll();

    $this->assign['list'] = [];
    foreach ($rows as $row) {
        $dpjp_ranap = $this->db('dpjp_ranap')
            ->join('dokter', 'dokter.kd_dokter=dpjp_ranap.kd_dokter')
            ->where('no_rawat', $row['no_rawat'])
            ->toArray();
        $row['dokter'] = $dpjp_ranap;

        $namadok = $this->db('dokter')->where('kd_dokter', $row['dok'])->oneArray();
        $row['namadok'] = $namadok['nm_dokter'];

        $ket_keadaan = $row['ket_keadaan'];
        $kode_ket_keadaan = explode(',', $ket_keadaan);
        $dpjp_resume_arr = [];

        foreach ($kode_ket_keadaan as $kode) {
            $cek_dpjpranap = $this->db('dpjp_ranap')
                ->where('no_rawat', $row['no_rawat'])
                ->where('kd_dokter', $kode)
                ->oneArray();

            $cek_dok = $this->db('dokter')
                ->where('kd_dokter', $cek_dpjpranap['kd_dokter'])
                ->oneArray();

            if ($cek_dpjpranap) {
                $dpjp_resume_arr[] = ' <span class="fa fa-minus-square-o"></span> '. $cek_dok['nm_dokter'];
            }

        }
        $dpjp_resume_str = implode('<br>', $dpjp_resume_arr);
        $row['dpjp_resume'] = $dpjp_resume_str;

        $this->assign['list'][] = $row;
        }
      }else {
        $this->anyResume_BatalSet();
      }
    }
    
    $username = $this->core->getUserInfo('username', null, true);
    return $this->draw('resume.batalklaim.html', ['setbatal' => $this->assign, 'username' => $username]);
  }

  public function anySkriningCek()
  {
      $rows = $this->db('evaluasi_awal_mpp')
        ->where('no_rawat', $_POST['no_rawat'])
        ->toArray();

      $result = [];
      foreach ($rows as $row) {
        $pasien = $this->db('reg_periksa')
          ->join('pasien', 'pasien.no_rkm_medis=reg_periksa.no_rkm_medis')
          ->where('no_rawat', $row['no_rawat'])
          ->oneArray();

        $row['no_rkm_medis'] = $pasien['no_rkm_medis'];
        $row['nm_pasien'] = $pasien['nm_pasien'];

        $kamar_inap = $this->db('kamar_inap')
          ->join('kamar', 'kamar.kd_kamar=kamar_inap.kd_kamar')
          ->join('bangsal', 'bangsal.kd_bangsal=kamar.kd_bangsal')
          ->where('no_rawat', $row['no_rawat'])
          ->oneArray();

        $row['kd_kamar'] = $kamar_inap['kd_kamar'];

          $ceklist_skrining = explode(',', $row['skrining_ceklist']);

          $ceklist_skrining = array_filter($ceklist_skrining);

          $row['ceklist_skrining'] = $ceklist_skrining;

        $result[] = $row;
      }

    echo $this->draw('skriningceklist.html', ['skrining' => $result]);
    exit();
     
  }

  public function postSaveSkriningCek()
  {
    $data = isset($_POST['data']) ? $_POST['data'] : array();
    $no_rawat = $_POST['no_rawat'];
    $tanggal = date('Y-m-d');
    $skrining = implode(", ", $data);
    $catatan_skrining = isset($_POST['catatan_skrining']) ? $_POST['catatan_skrining'] : '';

    if (!empty($data)) {
      $jumlahCek = count($data);

      if (!$this->db('evaluasi_awal_mpp')->where('no_rawat', $_POST['no_rawat'])->oneArray()) {
        $query = $this->db('evaluasi_awal_mpp')
          ->save([
            'no_rawat' => $no_rawat,
            'tanggal' => $tanggal,
            'skrining_ceklist' => $skrining,
            'nilai_skrining' => $jumlahCek,
            'catatan_skrining' => ($jumlahCek < 7) ? $catatan_skrining : '',
            'petugas' => $this->core->getUserInfo('username', null, true)
          ]);
      } else {
        $query = $this->db('evaluasi_awal_mpp')
          ->where('no_rawat', $_POST['no_rawat'])
          ->save([
            'tanggal' => $tanggal,
            'skrining_ceklist' => $skrining,
            'nilai_skrining' => $jumlahCek,
            'catatan_skrining' => ($jumlahCek < 7) ? $catatan_skrining : '',
            'petugas' => $this->core->getUserInfo('username', null, true)
          ]);
      }

      if ($query) {
        $this->notify('success', 'Data Berhasil Update');
      } else {
        $this->notify('failure', 'Gagal Update');
      }
    }
  }

  public function postCatatanSkrining()
  {
        $data = isset($_POST['data']) ? $_POST['data'] : array();
        $no_rawat = $_POST['no_rawat'];
        $tanggal = date('Y-m-d');
        $skrining = implode(", ", $data);
        $catatan_skrining = $_POST['catatan_skrining'];
        
      if (!empty($data)) {
      $jumlahCek = count($data);

      if (!$this->db('evaluasi_awal_mpp')->where('no_rawat', $_POST['no_rawat'])->oneArray()) {
        $query = $this->db('evaluasi_awal_mpp')
          ->save([
            'no_rawat' => $no_rawat,
            'tanggal' => $tanggal,
            'skrining_ceklist' => $skrining,
            'nilai_skrining' => $jumlahCek,
            'petugas' => $this->core->getUserInfo('username', null, true),
            'catatan_skrining' => $catatan_skrining
          ]);
      } else {
        $query = $this->db('evaluasi_awal_mpp')
          ->where('no_rawat', $_POST['no_rawat'])
          ->save([
            'tanggal' => $tanggal,
            'skrining_ceklist' => $skrining,
            'nilai_skrining' => $jumlahCek,
            'petugas' => $this->core->getUserInfo('username', null, true),
            'catatan_skrining' => $catatan_skrining
          ]);
      }

      if ($query) {
        $this->notify('success', 'Data Berhasil Update');
      } else {
        $this->notify('failure', 'Gagal Update');
      }
    } else {
      $this->notify('failure', 'Tidak ada data yang dipilih');
    }
    redirect(url([ADMIN, 'rawat_inap', 'skriningcek']));
  }
  
  public function convertNorawat($text)
  {
    setlocale(LC_ALL, 'en_EN');
    $text = str_replace('/', '', trim($text));
    return $text;
  }

  public function getJavascript()
  {
    header('Content-type: text/javascript');
    echo $this->draw(MODULES . '/rawat_inap/js/admin/rawat_inap.js');
    exit();
  }

  public function getSelectBootstrap()
  {

    $this->core->addCSS(url('https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.12.4/css/bootstrap-select.min.css'));
    $this->core->addJS(url('https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.12.4/js/bootstrap-select.min.js'));
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
    $this->core->addJS(url([ADMIN, 'rawat_inap', 'javascript']), 'footer');
    $this->getSelectBootstrap();
    // $this->core->addJS(url([ADMIN, 'rawat_inap', 'selectbootstrap']), 'footer');
    // var_dump(url([ADMIN, 'rawat_inap', 'selectbootstrap'])); die;
  }

  protected function data_icd($table)
  {
    return new DB_ICD($table);
  }
}
