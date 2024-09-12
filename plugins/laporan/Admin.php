<?php

namespace Plugins\Laporan;

use Systems\AdminModule;

class Admin extends AdminModule
{

  public function navigation()
  {
    return [
      'Manage' => 'manage',
      'Bridging SEP' => 'bridgingsep',
    ];
  }

  public function getManage()
  {
    $sub_modules = [
      ['name' => 'Bridging SEP', 'url' => url([ADMIN, 'laporan', 'bridgingsep']), 'icon' => 'cubes', 'desc' => 'Laporan bridging SEP'],
      ['name' => 'Sensus Harian Ranap', 'url' => url([ADMIN, 'laporan', 'sensusranap']), 'icon' => 'cubes', 'desc' => 'Laporan Sensus Harian Ranap'],
      ['name' => 'Pemberian Obat', 'url' => url([ADMIN, 'laporan', 'beriobat']), 'icon' => 'cubes', 'desc' => 'Laporan Pemberian Obat Berdasarkan Kategori'],
      ['name' => 'Riwayat Diagnosa', 'url' => url([ADMIN, 'laporan', 'riwayatdiagnosa']), 'icon' => 'cubes', 'desc' => 'Riwayat Diagnosa & Prosedur Tindakan'],
    ];
    return $this->draw('manage.html', ['sub_modules' => $sub_modules]);
  }

  public function getBridgingSEP()
  {
    $this->_addHeaderFiles();
    $settings = $this->settings('settings');
    $this->tpl->set('settings', $this->tpl->noParse_array(htmlspecialchars_array($settings)));

    $tgl_awal = date('Y-m-d');
    $tgl_akhir = date('Y-m-d');

    if (isset($_GET['tgl_awal'])) {
      $tgl_awal = $_GET['tgl_awal'];
    }
    if (isset($_GET['tgl_akhir'])) {
      $tgl_akhir = $_GET['tgl_akhir'];
    }

    $sql = "SELECT * FROM bridging_sep WHERE (tglsep BETWEEN '$tgl_awal' AND '$tgl_akhir')";

    $stmt = $this->db()->pdo()->prepare($sql);
    $stmt->execute();
    $rows = $stmt->fetchAll();

    $return['list'] = [];
    $i = 1;
    foreach ($rows as $row) {
      $row['nomor'] = $i++;
      $return['list'][] = $row;
    }

    if (isset($_GET['action']) && $_GET['action'] == 'print') {
      echo $this->draw('bridgingsep.print.html', [
        'bridgingsep' => $return,
        'action' => url([ADMIN, 'laporan', 'bridgingsep'])
      ]);
      exit();
    } else {
      return $this->draw('bridgingsep.html', [
        'bridgingsep' => $return,
        'action' => url([ADMIN, 'laporan', 'bridgingsep'])
      ]);
    }
  }

  public function getSensusRanap($kamar = 'B0016')
  {
    $this->_addHeaderFiles();
    if (isset($_GET['bgsl'])) {
      $kamar = $_GET['bgsl'];
    }
    $tgl_hari_ini = date('Y-m-d', strtotime('-1 days'));
    if (isset($_GET['tgl_awal'])) {
      $tgl_hari_ini = $_GET['tgl_awal'];
    }
    $sisa = $this->db('kamar_inap')->join('kamar', 'kamar.kd_kamar=kamar_inap.kd_kamar')->where('kamar.kd_bangsal', $kamar)->where('tgl_keluar', '>', $tgl_hari_ini)->where('tgl_masuk', '<', $tgl_hari_ini)->toArray();
    $sisa2 = $this->db('kamar_inap')->join('kamar', 'kamar.kd_kamar=kamar_inap.kd_kamar')->where('kamar.kd_bangsal', $kamar)->where('tgl_keluar', '0000-00-00')->where('tgl_masuk', '<', $tgl_hari_ini)->toArray();
    $masuk = $this->db('kamar_inap')->join('kamar', 'kamar.kd_kamar=kamar_inap.kd_kamar')->where('kamar.kd_bangsal', $kamar)->where('tgl_masuk', $tgl_hari_ini)->toArray();
    $keluar = $this->db('kamar_inap')->join('kamar', 'kamar.kd_kamar=kamar_inap.kd_kamar')->where('kamar.kd_bangsal', $kamar)->where('stts_pulang', '!=', 'Pindah Kamar')->where('tgl_keluar', $tgl_hari_ini)->toArray();
    $pindah = $this->db('kamar_inap')->join('kamar', 'kamar.kd_kamar=kamar_inap.kd_kamar')->where('kamar.kd_bangsal', $kamar)->where('stts_pulang', 'Pindah Kamar')->where('tgl_keluar', $tgl_hari_ini)->toArray();

    $nmnya = $this->db('bangsal')->where('kd_bangsal', $kamar)->oneArray();
    $viewnmnya = $nmnya['nm_bangsal'];
    // $tgl_hari_ini = date('Y-m-d');
    $in['list'] = [];
    $out['list'] = [];
    $movefrom['list'] = [];
    $moveto['list'] = [];
    $i = 1;
    $o = 1;
    $mf = 1;
    $mt = 1;
    foreach ($masuk as $row) {
      $pindahke = $this->db('kamar_inap')->where('stts_pulang', 'Pindah Kamar')->where('tgl_keluar', $row['tgl_masuk'])->where('jam_keluar', $row['jam_masuk'])->oneArray();
      if ($pindahke) {
        // $pindahke['kamar_inap'] = $pindahke;
        // foreach ($pindahke as $value) {
        $pindahke['nomor'] = $mt++;
        $no_rkm_medis = $this->core->getRegPeriksaInfo('no_rkm_medis', $pindahke['no_rawat']);
        $kd_pj = $this->core->getRegPeriksaInfo('kd_pj', $pindahke['no_rawat']);
        $pindahke['no_rkm_medis'] = $this->core->getPasienInfo('no_rkm_medis', $no_rkm_medis);
        $pindahke['alamat'] = $this->core->getPasienInfo('alamat', $no_rkm_medis);
        $pindahke['nama'] = $this->core->getPasienInfo('nm_pasien', $no_rkm_medis);
        $pindahke['umur'] = $this->core->getPasienInfo('umur', $no_rkm_medis);
        $pindahke['jk'] = $this->core->getPasienInfo('jk', $no_rkm_medis);
        $kelas = $this->db('kamar')->join('bangsal', 'kamar.kd_bangsal=bangsal.kd_bangsal')->select('nm_bangsal')->where('kd_kamar', $pindahke['kd_kamar'])->oneArray();
        $pindahke['nm_bangsal'] = $kelas['nm_bangsal'];
        $rujuk = $this->db('rujuk_masuk')->select('perujuk')->where('no_rawat', $pindahke['no_rawat'])->oneArray();
        $pindahke['rujuk'] = $rujuk['perujuk'];
        $pindahke['kd_pj'] = $this->core->getPenjabInfo('png_jawab', $kd_pj);
        $dpjp = $this->db('dpjp_ranap')->select('kd_dokter')->where('no_rawat', $pindahke['no_rawat'])->toArray();
        $pindahke['dpjp'] = '';
        foreach ($dpjp as $drpindah) {
          $nm_dokter = $this->core->getDokterInfo('nm_dokter', $drpindah['kd_dokter']);
          $pindahke['dpjp'] = $pindahke['dpjp'] . ' ' . $nm_dokter . '<br>';
        }
        $moveto['list'][] = $pindahke;
        // }
      } else {
        $row['nomor'] = $i++;
        $no_rkm_medis = $this->core->getRegPeriksaInfo('no_rkm_medis', $row['no_rawat']);
        $kd_pj = $this->core->getRegPeriksaInfo('kd_pj', $row['no_rawat']);
        $row['no_rkm_medis'] = $this->core->getPasienInfo('no_rkm_medis', $no_rkm_medis);
        $row['alamat'] = $this->core->getPasienInfo('alamat', $no_rkm_medis);
        $row['nama'] = $this->core->getPasienInfo('nm_pasien', $no_rkm_medis);
        $row['umur'] = $this->core->getPasienInfo('umur', $no_rkm_medis);
        $row['jk'] = $this->core->getPasienInfo('jk', $no_rkm_medis);
        $kelas = $this->db('kamar')->select('kelas')->where('kd_kamar', $row['kd_kamar'])->oneArray();
        $row['kelas'] = $kelas['kelas'];
        $rujuk = $this->db('rujuk_masuk')->select('perujuk')->where('no_rawat', $row['no_rawat'])->oneArray();
        $row['rujuk'] = $rujuk['perujuk'];
        $row['kd_pj'] = $this->core->getPenjabInfo('png_jawab', $kd_pj);
        $dpjp = $this->db('dpjp_ranap')->select('kd_dokter')->where('no_rawat', $row['no_rawat'])->toArray();
        $row['dpjp'] = '';
        foreach ($dpjp as $value) {
          $nm_dokter = $this->core->getDokterInfo('nm_dokter', $value['kd_dokter']);
          $row['dpjp'] = $row['dpjp'] . ' ' . $nm_dokter . '<br>';
        }
        $in['list'][] = $row;
      }
    }
    foreach ($keluar as $row) {
      $row['nomor'] = $o++;
      $no_rkm_medis = $this->core->getRegPeriksaInfo('no_rkm_medis', $row['no_rawat']);
      $kd_pj = $this->core->getRegPeriksaInfo('kd_pj', $row['no_rawat']);
      $row['no_rkm_medis'] = $this->core->getPasienInfo('no_rkm_medis', $no_rkm_medis);
      $row['nama'] = $this->core->getPasienInfo('nm_pasien', $no_rkm_medis);
      $row['umur'] = $this->core->getPasienInfo('umur', $no_rkm_medis);
      $row['jk'] = $this->core->getPasienInfo('jk', $no_rkm_medis);
      $kelas = $this->db('kamar')->select('kelas')->where('kd_kamar', $row['kd_kamar'])->oneArray();
      $row['kelas'] = $kelas['kelas'];
      $rujuk = $this->db('rujuk_masuk')->select('perujuk')->where('no_rawat', $row['no_rawat'])->oneArray();
      $row['rujuk'] = $rujuk['perujuk'];
      $row['kd_pj'] = $this->core->getPenjabInfo('png_jawab', $kd_pj);
      $dpjp = $this->db('dpjp_ranap')->select('kd_dokter')->where('no_rawat', $row['no_rawat'])->toArray();
      $row['dpjp'] = '';
      foreach ($dpjp as $value) {
        $nm_dokter = $this->core->getDokterInfo('nm_dokter', $value['kd_dokter']);
        $row['dpjp'] = $row['dpjp'] . ' ' . $nm_dokter . '<br>';
      }
      $out['list'][] = $row;
    }
    foreach ($pindah as $row) {
      $pindahke = $this->db('kamar_inap')->where('no_rawat', $row['no_rawat'])->where('tgl_masuk', $row['tgl_keluar'])->where('jam_masuk', $row['jam_keluar'])->oneArray();
      $row['nomor'] = $mf++;
      $no_rkm_medis = $this->core->getRegPeriksaInfo('no_rkm_medis', $row['no_rawat']);
      $kd_pj = $this->core->getRegPeriksaInfo('kd_pj', $row['no_rawat']);
      $row['no_rkm_medis'] = $this->core->getPasienInfo('no_rkm_medis', $no_rkm_medis);
      $row['nama'] = $this->core->getPasienInfo('nm_pasien', $no_rkm_medis);
      $row['umur'] = $this->core->getPasienInfo('umur', $no_rkm_medis);
      $row['jk'] = $this->core->getPasienInfo('jk', $no_rkm_medis);
      $kelas = $this->db('kamar')->join('bangsal', 'kamar.kd_bangsal=bangsal.kd_bangsal')->select('nm_bangsal')->where('kd_kamar', $pindahke['kd_kamar'])->oneArray();
      $row['nm_bangsal'] = $kelas['nm_bangsal'];
      $rujuk = $this->db('rujuk_masuk')->select('perujuk')->where('no_rawat', $row['no_rawat'])->oneArray();
      $row['rujuk'] = $rujuk['perujuk'];
      $row['kd_pj'] = $this->core->getPenjabInfo('png_jawab', $kd_pj);
      $dpjp = $this->db('dpjp_ranap')->select('kd_dokter')->where('no_rawat', $row['no_rawat'])->toArray();
      $row['dpjp'] = '';
      foreach ($dpjp as $value) {
        $nm_dokter = $this->core->getDokterInfo('nm_dokter', $value['kd_dokter']);
        $row['dpjp'] = $row['dpjp'] . ' ' . $nm_dokter . '<br>';
      }
      $movefrom['list'][] = $row;
    }
    $bangsal['list'] = [];
    $kd_bangsal = $this->db('kamar')->select('kd_bangsal')->where('statusdata', '1')->group('kd_bangsal')->toArray();
    foreach ($kd_bangsal as $value) {
      $nm_bangsal = $this->db('bangsal')->where('kd_bangsal', $value['kd_bangsal'])->oneArray();
      $value['nm_bangsal'] = $nm_bangsal['nm_bangsal'];
      $bangsal['list'][] = $value;
    }
    $jml['list'] = [];
    $jml_kemaren = count($sisa) + count($sisa2);
    $jml_pindah = count($pindah);
    $jml['list']['in'] = count($in['list']);
    $jml['list']['out'] = count($out['list']);
    $jml['list']['stay']  = $jml_kemaren + $jml['list']['out'];
    // $jml['list']['stay']  = $jml_kemaren;
    $jml['list']['left'] = $jml['list']['stay']  + $jml['list']['in'] - $jml['list']['out'];
    $jml['list']['tgl'] = dateIndonesia($tgl_hari_ini);

    return $this->draw('sensusranap.html', ['masuk' => $in, 'keluar' => $out, 'pindahdari' => $movefrom, 'pindahke' => $moveto, 'bangsal' => $bangsal, 'viewnya' => $viewnmnya, 'jml' => $jml]);
  }
  
  public function getBeriObat()
  {
    $this->_addHeaderFiles();
    $settings = $this->settings('settings');
    $this->tpl->set('settings', $this->tpl->noParse_array(htmlspecialchars_array($settings)));

    $tgl_awal = date('Y-m-d');
    $tgl_akhir = date('Y-m-d');

    if (isset($_GET['tgl_awal'])) {
      $tgl_awal = $_GET['tgl_awal'];
    }
    if (isset($_GET['tgl_akhir'])) {
      $tgl_akhir = $_GET['tgl_akhir'];
    }

    $sql = "SELECT * FROM kategori_barang";

    $stmt = $this->db()->pdo()->prepare($sql);
    $stmt->execute();
    $rows = $stmt->fetchAll();

    $return['list'] = [];
    $i = 1;
    foreach ($rows as $row) {
      $row['nomor'] = $i++;
      $sql = "SELECT SUM(jml) as sum FROM detail_pemberian_obat JOIN databarang ON detail_pemberian_obat.kode_brng = databarang.kode_brng WHERE databarang.kode_kategori = '{$row['kode']}' AND detail_pemberian_obat.tgl_perawatan BETWEEN '$tgl_awal' AND '$tgl_akhir'";
      $query = $this->db()->pdo()->prepare($sql);
      $query->execute();
      $jml = $query->fetch();
      $row['jml'] = ($jml['sum'] != '') ? number_format($jml['sum']) : 0;
      $row['tgl_masuk'] = $tgl_awal;
      $row['tgl_keluar'] = $tgl_akhir;
      $return['list'][] = $row;
    }


    return $this->draw('beriobat.html', [
      'beriobat' => $return
    ]);
  }

  public function postListObat()
  {
    $kat = $_POST['kat'];
    $tgl_awal = $_POST['awal'];
    $tgl_akhir = $_POST['akhir'];
    $list = [];
    $sql = "SELECT databarang.nama_brng as nama , SUM(detail_pemberian_obat.jml) as jml FROM detail_pemberian_obat JOIN databarang ON detail_pemberian_obat.kode_brng = databarang.kode_brng WHERE databarang.kode_kategori = '$kat' AND detail_pemberian_obat.tgl_perawatan BETWEEN '$tgl_awal' AND '$tgl_akhir' GROUP BY detail_pemberian_obat.kode_brng ORDER BY jml DESC";
    $query = $this->db()->pdo()->prepare($sql);
    $query->execute();
    $rows = $query->fetchAll();
    $return = ["code" => "200", "message" => "Success", "response" => ["list_data" => $rows]];
    echo json_encode($return);
    exit();
  }

  public function getRiwayatDiagnosa($page = 1)
  {
   $this->_addHeaderFiles();
   $perpage = '10';
   $phrase = '';
   if (isset($_GET['s']))
       $phrase = $_GET['s'];

   $tgl_kunjungan = date('Y-m-d');
   $tgl_kunjungan_akhir = date('Y-m-d');

   if (isset($_GET['awal'])) {
    $tgl_kunjungan = $_GET['awal'];
    }
    if (isset($_GET['akhir'])) {
        $tgl_kunjungan_akhir = $_GET['akhir'];
    }

   $status = '';
   if (isset($_GET['status'])) {
       $status = $_GET['status'];
   }
  
    $totalRecords = $this->db('reg_periksa')
        ->join('diagnosa_pasien', ' diagnosa_pasien.no_rawat = reg_periksa.no_rawat')
        ->join('penyakit', 'diagnosa_pasien.kd_penyakit = penyakit.kd_penyakit')
        ->join('pasien', 'reg_periksa.no_rkm_medis = pasien.no_rkm_medis')
        ->where('reg_periksa.tgl_registrasi', '>=', $tgl_kunjungan . ' 00:00:00')
        ->where('reg_periksa.tgl_registrasi', '<=', $tgl_kunjungan_akhir . ' 23:59:59')
        ->like('diagnosa_pasien.status', '%' . $status . '%')
        ->like('diagnosa_pasien.kd_penyakit', '%' . $phrase . '%')
        ->asc('reg_periksa.tgl_registrasi')
        ->toArray();


  $pagination = new \Systems\Lib\Pagination($page, count($totalRecords), 10, url([ADMIN, 'laporan', 'riwayatdiagnosa', '%d?awal=' . $tgl_kunjungan . '&akhir=' . $tgl_kunjungan_akhir . '&status=' . $status . '&s=' . $phrase]));
  $this->assign['pagination'] = $pagination->nav('pagination', '5');
  $this->assign['totalRecords'] = $totalRecords;

  // list
  $offset = $pagination->offset();
  $sql ="SELECT 
          reg_periksa.tgl_registrasi, 
          diagnosa_pasien.no_rawat,
          reg_periksa.no_rkm_medis,
          pasien.nm_pasien, 
          diagnosa_pasien.kd_penyakit,
          penyakit.nm_penyakit, 
          diagnosa_pasien.status,
          diagnosa_pasien.status_penyakit,
          reg_periksa.umurdaftar,
          reg_periksa.sttsumur, 
          IF(diagnosa_pasien.status = 'Ralan',
              (SELECT nm_poli FROM poliklinik WHERE poliklinik.kd_poli = reg_periksa.kd_poli),
              (SELECT bangsal.nm_bangsal FROM kamar_inap 
              INNER JOIN kamar INNER JOIN bangsal ON kamar_inap.kd_kamar = kamar.kd_kamar AND kamar.kd_bangsal = bangsal.kd_bangsal 
              WHERE kamar_inap.stts_pulang <> 'Pindah Kamar' AND kamar_inap.no_rawat = reg_periksa.no_rawat LIMIT 1)) AS ruangan,
          pasien.no_ktp, pasien.tgl_lahir, pasien.alamat, pasien.jk, 
          IF(diagnosa_pasien.status = 'Ralan',
              (SELECT MAX(reg_periksa.stts) FROM reg_periksa WHERE reg_periksa.no_rawat = diagnosa_pasien.no_rawat),
              (SELECT kamar_inap.stts_pulang FROM kamar_inap WHERE kamar_inap.no_rawat = diagnosa_pasien.no_rawat LIMIT 1)
          ) AS pulang 
        FROM reg_periksa
          INNER JOIN diagnosa_pasien 
          INNER JOIN pasien 
          INNER JOIN penyakit 
          ON reg_periksa.no_rawat = diagnosa_pasien.no_rawat 
          AND reg_periksa.no_rkm_medis = pasien.no_rkm_medis 
          AND diagnosa_pasien.kd_penyakit = penyakit.kd_penyakit 
          LEFT JOIN kamar_inap ON kamar_inap.no_rawat = diagnosa_pasien.no_rawat AND kamar_inap.no_rawat = reg_periksa.no_rawat 
        WHERE 
          reg_periksa.tgl_registrasi BETWEEN '$tgl_kunjungan' AND '$tgl_kunjungan_akhir'
          AND diagnosa_pasien.status = '$status' 
          AND penyakit.kd_penyakit LIKE '%$phrase%'
        GROUP BY diagnosa_pasien.no_rawat, diagnosa_pasien.kd_penyakit 
        ORDER BY reg_periksa.tgl_registrasi, diagnosa_pasien.prioritas 
        LIMIT $perpage OFFSET $offset";

  $stmt = $this->db()->pdo()->prepare($sql);
  $stmt->execute();
  $rows = $stmt->fetchAll();
  if (count($rows)) {
    foreach ($rows as $row) {
        $row = htmlspecialchars_array($row);
        $this->assign['list'][] = $row;
      }
    }
   return $this->draw('riwayat.diagnosa.html', ['riwayat_diagnosa' => $this->assign]);
  }

  public function getRiwayatProsedur($page = 1)
  {
   $this->_addHeaderFiles();
   $perpage = '10';
   $phrase = '';
   if (isset($_GET['s']))
       $phrase = $_GET['s'];

   $tgl_kunjungan = date('Y-m-d');
   $tgl_kunjungan_akhir = date('Y-m-d');

   if (isset($_GET['awal'])) {
    $tgl_kunjungan = $_GET['awal'];
    }
    if (isset($_GET['akhir'])) {
        $tgl_kunjungan_akhir = $_GET['akhir'];
    }

   $status = '';
   if (isset($_GET['status'])) {
       $status = $_GET['status'];
   }
  
    $totalRecords = $this->db('reg_periksa')
        ->join('prosedur_pasien', ' prosedur_pasien.no_rawat = reg_periksa.no_rawat')
        ->join('icd9', 'prosedur_pasien.kode = icd9.kode')
        ->join('pasien', 'reg_periksa.no_rkm_medis = pasien.no_rkm_medis')
        ->where('reg_periksa.tgl_registrasi', '>=', $tgl_kunjungan)
        ->where('reg_periksa.tgl_registrasi', '<=', $tgl_kunjungan_akhir )
        ->like('prosedur_pasien.status', '%' . $status . '%')
        ->like('prosedur_pasien.kode', '%' . $phrase . '%')
        ->asc('reg_periksa.tgl_registrasi')
        ->toArray();


  $pagination = new \Systems\Lib\Pagination($page, count($totalRecords), 10, url([ADMIN, 'laporan', 'riwayatprosedur', '%d?awal=' . $tgl_kunjungan . '&akhir=' . $tgl_kunjungan_akhir . '&status=' . $status . '&s=' . $phrase]));
  $this->assign['pagination'] = $pagination->nav('pagination', '5');
  $this->assign['totalRecords'] = $totalRecords;

  // list
  $offset = $pagination->offset();
  $sql = "SELECT reg_periksa.tgl_registrasi,
                 prosedur_pasien.no_rawat,
                 reg_periksa.no_rkm_medis,
                 pasien.nm_pasien,
                 prosedur_pasien.kode,
                 icd9.deskripsi_panjang, 
                 prosedur_pasien.status 
          FROM reg_periksa 
          INNER JOIN prosedur_pasien 
          INNER JOIN pasien 
          INNER JOIN icd9 
            ON reg_periksa.no_rawat=prosedur_pasien.no_rawat 
            AND reg_periksa.no_rkm_medis=pasien.no_rkm_medis 
            AND prosedur_pasien.kode=icd9.kode 
          WHERE reg_periksa.tgl_registrasi BETWEEN '$tgl_kunjungan' AND '$tgl_kunjungan_akhir' 
          AND prosedur_pasien.status = '$status' 
          AND prosedur_pasien.kode LIKE '%$phrase%'
          ORDER BY reg_periksa.tgl_registrasi,prosedur_pasien.prioritas 
          LIMIT $perpage OFFSET $offset";
  $stmt = $this->db()->pdo()->prepare($sql);
  $stmt->execute();
  $rows = $stmt->fetchAll();
  if (count($rows)) {
    foreach ($rows as $row) {
        $row = htmlspecialchars_array($row);
        $this->assign['list'][] = $row;
      }
    }

   return $this->draw('riwayat.prosedur.html', ['riwayat_prosedur' => $this->assign]);
  }

  public function getCSS()
  {
    header('Content-type: text/css');
    echo $this->draw(MODULES . '/laporan/css/admin/laporan.css');
    exit();
  }

  public function getJavascript()
  {
    header('Content-type: text/javascript');
    echo $this->draw(MODULES . '/laporan/js/admin/laporan.js');
    exit();
  }

  private function _addHeaderFiles()
  {
    // CSS
    $this->core->addCSS(url('assets/css/jquery-ui.css'));
    $this->core->addCSS(url('assets/jscripts/lightbox/lightbox.min.css'));
    $this->core->addCSS(url('assets/css/dataTables.bootstrap.min.css'));
    $this->core->addCSS(url('assets/css/bootstrap-datetimepicker.css'));

    $this->core->addJS(url('assets/jscripts/jquery-ui.js'), 'footer');
    $this->core->addJS(url('assets/jscripts/lightbox/lightbox.min.js'), 'footer');
    $this->core->addJS(url('assets/jscripts/jquery.dataTables.min.js'));
    $this->core->addJS(url('assets/jscripts/dataTables.bootstrap.min.js'));
    $this->core->addJS(url('assets/jscripts/moment-with-locales.js'));
    $this->core->addJS(url('assets/jscripts/bootstrap-datetimepicker.js'));

    // MODULE SCRIPTS
    $this->core->addCSS(url([ADMIN, 'laporan', 'css']));
    $this->core->addJS(url([ADMIN, 'laporan', 'javascript']), 'footer');
  }
}
