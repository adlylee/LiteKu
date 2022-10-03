<?php

namespace Plugins\Anjungan;

use Systems\SiteModule;
use Systems\Lib\BpjsService;
use Systems\Lib\QRCode;

class Site extends SiteModule
{

  public function init()
  {
    $this->consid = $this->settings->get('settings.BpjsConsID');
    $this->secretkey = $this->settings->get('settings.BpjsSecretKey');
    $this->user_key = $this->settings->get('settings.BpjsUserKey');
    $this->api_url = $this->settings->get('settings.BpjsApiUrl');
    $this->vclaim_version = $this->settings->get('settings.vClaimVersion');
  }

  public function routes()
  {
    $this->route('anjungan', 'getIndex');
    $this->route('anjungan/pasien', 'getDisplayAPM');
    $this->route('anjungan/farmasi', 'getDisplayFarmasi');
    $this->route('anjungan/loket', 'getDisplayAntrianLoket');
    $this->route('anjungan/poli', 'getDisplayAntrianPoli');
    $this->route('anjungan/poli/(:str)', 'getDisplayAntrianPoliKode');
    $this->route('anjungan/poli/(:str)/(:str)', 'getDisplayAntrianPoliKode');
    $this->route('anjungan/display/poli/(:str)', 'getDisplayAntrianPoliDisplay');
    $this->route('anjungan/display/poli/(:str)/(:str)', 'getDisplayAntrianPoliDisplay');
    $this->route('anjungan/laboratorium', 'getDisplayAntrianLaboratorium');
    $this->route('anjungan/apotek', 'getDisplayAntrianApotek');
    $this->route('anjungan/ajax', 'getAjax');
    $this->route('anjungan/panggilantrian', 'getPanggilAntrian');
    $this->route('anjungan/panggilselesai', 'getPanggilSelesai');
    $this->route('anjungan/simpannorm', 'getSimpanNoRM');
    $this->route('anjungan/setpanggil', 'getSetPanggil');
    $this->route('anjungan/presensi', 'getPresensi');
    $this->route('anjungan/presensi/upload', 'getUpload');
    $this->route('anjungan/bed', 'getDisplayBed');
    $this->route('anjungan/sep', 'getSepMandiri');
    $this->route('anjungan/sep/cek', 'getSepMandiriCek');
    $this->route('anjungan/sep/(:int)/(:int)/(:str)', 'getSepMandiriNokaNorm');
    $this->route('anjungan/sep/bikin/(:str)/(:int)/(:str)', 'getSepMandiriBikin');
    $this->route('anjungan/sep/savesep', 'postSaveSep');
    $this->route('anjungan/sep/cetaksep/(:str)', 'getCetakSEP');
    $this->route('anjungan/daftar/cek','getDaftarMandiriCek');
    $this->route('anjungan/daftar/reg','postRegDaftar');
    $this->route('anjungan/baru/cek','getBaruCek');
    $this->route('anjungan/baru/(:int)','getBaruData');
    $this->route('anjungan/baru/bikin/(:str)','getBaruBikin');
    $this->route('anjungan/baru/savesep','postSaveSepBaru');
    $this->route('anjungan/check','postCheckFungsi');
    $this->route('anjungan/reset','getResetAnjungan');
  }

  public function getIndex()
  {
    echo $this->draw('index.html', ['test' => 'Opo iki']);
    exit();
  }

  public function getDisplayAPM()
  {
    $title = 'Display Antrian Poliklinik';
    $logo  = $this->settings->get('settings.logo');
    $poliklinik = $this->db('poliklinik')->toArray();
    $penjab = $this->db('penjab')->toArray();

    $_username = $this->core->getUserInfo('fullname', null, true);
    $__username = $this->core->getUserInfo('username');
    if ($this->core->getUserInfo('username') !== '') {
      $__username = 'Tamu';
    }
    $tanggal       = getDayIndonesia(date('Y-m-d')) . ', ' . dateIndonesia(date('Y-m-d'));
    $username      = !empty($_username) ? $_username : $__username;

    $content = $this->draw('display.antrian.html', [
      'title' => $title,
      'logo' => $logo,
      'powered' => 'Powered by <a href="https://basoro.org/">KhanzaLITE</a>',
      'username' => $username,
      'tanggal' => $tanggal,
      'running_text' => $this->settings->get('anjungan.text_anjungan'),
      'poliklinik' => $poliklinik,
      'penjab' => $penjab
    ]);

    $assign = [
      'title' => $this->settings->get('settings.nama_instansi'),
      'desc' => $this->settings->get('settings.alamat'),
      'content' => $content
    ];

    $this->setTemplate("canvas.html");

    $this->tpl->set('page', ['title' => $assign['title'], 'desc' => $assign['desc'], 'content' => $assign['content']]);
  }

  public function getDisplayFarmasi()
  {
    $title = 'Display Antrian Poliklinik';
    $logo  = $this->settings->get('settings.logo');
    $poliklinik = $this->db('poliklinik')->toArray();
    $penjab = $this->db('penjab')->toArray();

    $_username = $this->core->getUserInfo('fullname', null, true);
    $__username = $this->core->getUserInfo('username');
    if ($this->core->getUserInfo('username') !== '') {
      $__username = 'Tamu';
    }
    $tanggal       = getDayIndonesia(date('Y-m-d')) . ', ' . dateIndonesia(date('Y-m-d'));
    $username      = !empty($_username) ? $_username : $__username;

    $content = $this->draw('display.antrian_farmasi.html', [
      'title' => $title,
      'logo' => $logo,
      'powered' => 'Powered by <a href="https://basoro.org/">KhanzaLITE</a>',
      'username' => $username,
      'tanggal' => $tanggal,
      'running_text' => $this->settings->get('anjungan.text_anjungan'),
      'poliklinik' => $poliklinik,
      'penjab' => $penjab
    ]);

    $assign = [
      'title' => $this->settings->get('settings.nama_instansi'),
      'desc' => $this->settings->get('settings.alamat'),
      'content' => $content
    ];

    $this->setTemplate("canvas.html");

    $this->tpl->set('page', ['title' => $assign['title'], 'desc' => $assign['desc'], 'content' => $assign['content']]);
  }

  public function getDisplayAntrianPoli()
  {
    $title = 'Display Antrian Poliklinik';
    $logo  = $this->settings->get('settings.logo');
    $display = $this->_resultDisplayAntrianPoli();

    $_username = $this->core->getUserInfo('fullname', null, true);
    $__username = $this->core->getUserInfo('username');
    if ($this->core->getUserInfo('username') !== '') {
      $__username = 'Tamu';
    }
    $tanggal       = getDayIndonesia(date('Y-m-d')) . ', ' . dateIndonesia(date('Y-m-d'));
    $username      = !empty($_username) ? $_username : $__username;

    $content = $this->draw('display.antrian.poli.html', [
      'title' => $title,
      'logo' => $logo,
      'powered' => 'Powered by <a href="https://basoro.org/">KhanzaLITE</a>',
      'username' => $username,
      'tanggal' => $tanggal,
      'running_text' => $this->settings->get('anjungan.text_poli'),
      'display' => $display
    ]);

    $assign = [
      'title' => $this->settings->get('settings.nama_instansi'),
      'desc' => $this->settings->get('settings.alamat'),
      'content' => $content
    ];

    $this->setTemplate("canvas.html");

    $this->tpl->set('page', ['title' => $assign['title'], 'desc' => $assign['desc'], 'content' => $assign['content']]);
  }

  public function _resultDisplayAntrianPoli()
  {
    $date = date('Y-m-d');
    $tentukan_hari = date('D', strtotime(date('Y-m-d')));
    $day = array(
      'Sun' => 'AKHAD',
      'Mon' => 'SENIN',
      'Tue' => 'SELASA',
      'Wed' => 'RABU',
      'Thu' => 'KAMIS',
      'Fri' => 'JUMAT',
      'Sat' => 'SABTU'
    );
    $hari = $day[$tentukan_hari];

    $poliklinik = str_replace(",", "','", $this->settings->get('anjungan.display_poli'));
    $query = $this->db()->pdo()->prepare("SELECT a.kd_dokter, a.kd_poli, b.nm_poli, c.nm_dokter, a.jam_mulai, a.jam_selesai FROM jadwal a, poliklinik b, dokter c WHERE a.kd_poli = b.kd_poli AND a.kd_dokter = c.kd_dokter AND a.hari_kerja = '$hari'  AND a.kd_poli IN ('$poliklinik')");
    $query->execute();
    $rows = $query->fetchAll(\PDO::FETCH_ASSOC);;

    $result = [];
    if (count($rows)) {
      foreach ($rows as $row) {
        $row['dalam_pemeriksaan'] = $this->db('reg_periksa')
          ->select('no_reg')
          ->select('nm_pasien')
          ->join('pasien', 'pasien.no_rkm_medis = reg_periksa.no_rkm_medis')
          ->where('tgl_registrasi', $date)
          ->where('stts', 'Berkas Diterima')
          ->where('kd_poli', $row['kd_poli'])
          ->where('kd_dokter', $row['kd_dokter'])
          ->limit(1)
          ->oneArray();
        $row['dalam_antrian'] = $this->db('reg_periksa')
          ->select(['jumlah' => 'COUNT(DISTINCT reg_periksa.no_rawat)'])
          ->join('poliklinik', 'poliklinik.kd_poli = reg_periksa.kd_poli')
          ->where('reg_periksa.tgl_registrasi', date('Y-m-d'))
          ->where('reg_periksa.kd_poli', $row['kd_poli'])
          ->where('reg_periksa.kd_dokter', $row['kd_dokter'])
          ->oneArray();
        $row['sudah_dilayani'] = $this->db('reg_periksa')
          ->select(['count' => 'COUNT(DISTINCT reg_periksa.no_rawat)'])
          ->join('poliklinik', 'poliklinik.kd_poli = reg_periksa.kd_poli')
          ->where('reg_periksa.tgl_registrasi', date('Y-m-d'))
          ->where('reg_periksa.kd_poli', $row['kd_poli'])
          ->where('reg_periksa.kd_dokter', $row['kd_dokter'])
          ->where('reg_periksa.stts', 'Sudah')
          ->oneArray();
        $row['sudah_dilayani']['jumlah'] = 0;
        if (!empty($row['sudah_dilayani'])) {
          $row['sudah_dilayani']['jumlah'] = $row['sudah_dilayani']['count'];
        }
        $row['selanjutnya'] = $this->db('reg_periksa')
          ->select('reg_periksa.no_reg')
          //->select(['no_urut_reg' => 'ifnull(MAX(CONVERT(RIGHT(reg_periksa.no_reg,3),signed)),0)'])
          ->select('pasien.nm_pasien')
          ->join('pasien', 'pasien.no_rkm_medis = reg_periksa.no_rkm_medis')
          ->where('reg_periksa.tgl_registrasi', $date)
          ->where('reg_periksa.stts', 'Belum')
          ->where('reg_periksa.kd_poli', $row['kd_poli'])
          ->where('reg_periksa.kd_dokter', $row['kd_dokter'])
          ->asc('reg_periksa.no_reg')
          ->toArray();
        $row['get_no_reg'] = $this->db('reg_periksa')
          ->select(['max' => 'ifnull(MAX(CONVERT(RIGHT(no_reg,3),signed)),0)'])
          ->where('tgl_registrasi', $date)
          ->where('kd_poli', $row['kd_poli'])
          ->where('kd_dokter', $row['kd_dokter'])
          ->oneArray();
        $row['diff'] = (strtotime($row['jam_selesai']) - strtotime($row['jam_mulai'])) / 60;
        $row['interval'] = 0;
        if ($row['diff'] == 0) {
          $row['interval'] = round($row['diff'] / $row['get_no_reg']['max']);
        }
        if ($row['interval'] > 10) {
          $interval = 10;
        } else {
          $interval = $row['interval'];
        }
        foreach ($row['selanjutnya'] as $value) {
          //$minutes = $value['no_reg'] * $interval;
          //$row['jam_mulai'] = date('H:i',strtotime('+10 minutes',strtotime($row['jam_mulai'])));
        }

        $result[] = $row;
      }
    }

    return $result;
  }

  public function getDisplayAntrianPoliKode()
  {
    $title = 'Display Antrian Poliklinik';
    $logo  = $this->settings->get('settings.logo');
    $slug = parseURL();
    $vidio = $this->settings->get('anjungan.vidio');
    $_GET['vid'] = '';
    if (isset($_GET['vid']) && $_GET['vid'] != '') {
      $vidio = $_GET['vid'];
    }

    $date = date('Y-m-d');
    $tentukan_hari = date('D', strtotime(date('Y-m-d')));
    $day = array(
      'Sun' => 'AKHAD',
      'Mon' => 'SENIN',
      'Tue' => 'SELASA',
      'Wed' => 'RABU',
      'Thu' => 'KAMIS',
      'Fri' => 'JUMAT',
      'Sat' => 'SABTU'
    );
    $hari = $day[$tentukan_hari];

    $running_text = $this->settings->get('anjungan.text_poli');
    $jadwal = $this->db('jadwal')->join('dokter', 'dokter.kd_dokter = jadwal.kd_dokter')->join('poliklinik', 'poliklinik.kd_poli = jadwal.kd_poli')->where('hari_kerja', $hari)->toArray();
    $_username = $this->core->getUserInfo('fullname', null, true);
    $__username = $this->core->getUserInfo('username');
    if ($this->core->getUserInfo('username') !== '') {
      $__username = 'Tamu';
    }
    $tanggal       = getDayIndonesia(date('Y-m-d')) . ', ' . dateIndonesia(date('Y-m-d'));
    $username      = !empty($_username) ? $_username : $__username;

    $content = $this->draw('display.antrian.poli.kode.html', [
      'title' => $title,
      'logo' => $logo,
      'powered' => 'Powered by <a href="https://basoro.org/">KhanzaLITE</a>',
      'username' => $username,
      'tanggal' => $tanggal,
      'vidio' => $vidio,
      'running_text' => $running_text,
      'jadwal' => $jadwal,
      'slug' => $slug
    ]);

    $assign = [
      'title' => $this->settings->get('settings.nama_instansi'),
      'desc' => $this->settings->get('settings.alamat'),
      'content' => $content
    ];

    $this->setTemplate("canvas.html");

    $this->tpl->set('page', ['title' => $assign['title'], 'desc' => $assign['desc'], 'content' => $assign['content']]);
  }

  public function getDisplayAntrianPoliDisplay()
  {
    $title = 'Display Antrian Poliklinik';
    $logo  = $this->settings->get('settings.logo');
    $display = $this->_resultDisplayAntrianPoliKodeDisplay();
    $slug = parseURL();

    $_username = $this->core->getUserInfo('fullname', null, true);
    $__username = $this->core->getUserInfo('username');
    if ($this->core->getUserInfo('username') !== '') {
      $__username = 'Tamu';
    }
    $tanggal       = getDayIndonesia(date('Y-m-d')) . ', ' . dateIndonesia(date('Y-m-d'));
    $username      = !empty($_username) ? $_username : $__username;

    $content = $this->draw('display.antrian.poli.display.html', [
      'title' => $title,
      'logo' => $logo,
      'powered' => 'Powered by <a href="https://basoro.org/">KhanzaLITE</a>',
      'username' => $username,
      'tanggal' => $tanggal,
      'vidio' => $this->settings->get('anjungan.vidio'),
      'running_text' => $this->settings->get('anjungan.text_poli'),
      'slug' => $slug,
      'display' => $display
    ]);

    $assign = [
      'title' => $this->settings->get('settings.nama_instansi'),
      'desc' => $this->settings->get('settings.alamat'),
      'content' => $content
    ];

    $this->setTemplate("canvas.html");

    $this->tpl->set('page', ['title' => $assign['title'], 'desc' => $assign['desc'], 'content' => $assign['content']]);
  }

  public function _resultDisplayAntrianPoliKodeDisplay()
  {
    $slug = parseURL();

    $date = date('Y-m-d');
    $tentukan_hari = date('D', strtotime(date('Y-m-d')));
    $day = array(
      'Sun' => 'AKHAD',
      'Mon' => 'SENIN',
      'Tue' => 'SELASA',
      'Wed' => 'RABU',
      'Thu' => 'KAMIS',
      'Fri' => 'JUMAT',
      'Sat' => 'SABTU'
    );
    $hari = $day[$tentukan_hari];

    $poliklinik = $slug[3];
    $query = $this->db()->pdo()->prepare("SELECT a.kd_dokter, a.kd_poli, b.nm_poli, c.nm_dokter, a.jam_mulai, a.jam_selesai FROM jadwal a, poliklinik b, dokter c WHERE a.kd_poli = b.kd_poli AND a.kd_dokter = c.kd_dokter AND a.hari_kerja = '$hari' AND a.kd_poli = '$poliklinik'");
    if (!isset($slug[4]) && $slug[3] == 'all') {
      $query = $this->db()->pdo()->prepare("SELECT a.kd_dokter, a.kd_poli, b.nm_poli, c.nm_dokter, a.jam_mulai, a.jam_selesai FROM jadwal a, poliklinik b, dokter c WHERE a.kd_poli = b.kd_poli AND a.kd_dokter = c.kd_dokter AND a.hari_kerja = '$hari'");
    }
    if (isset($slug[4]) && $slug[4] != '') {
      $dokter = $slug[4];
      $query = $this->db()->pdo()->prepare("SELECT a.kd_dokter, a.kd_poli, b.nm_poli, c.nm_dokter, a.jam_mulai, a.jam_selesai FROM jadwal a, poliklinik b, dokter c WHERE a.kd_poli = b.kd_poli AND a.kd_dokter = c.kd_dokter AND a.hari_kerja = '$hari' AND a.kd_poli = '$poliklinik' AND a.kd_dokter = '$dokter'");
    }
    $query->execute();
    $rows = $query->fetchAll(\PDO::FETCH_ASSOC);;

    $result = [];
    if (count($rows)) {
      foreach ($rows as $row) {
        $row['dalam_pemeriksaan'] = $this->db('reg_periksa')
          ->select('no_reg')
          ->select('nm_pasien')
          ->join('pasien', 'pasien.no_rkm_medis = reg_periksa.no_rkm_medis')
          ->where('tgl_registrasi', $date)
          ->where('stts', 'Berkas Diterima')
          ->where('kd_poli', $row['kd_poli'])
          ->where('kd_dokter', $row['kd_dokter'])
          ->limit(1)
          ->oneArray();
        $row['dalam_antrian'] = $this->db('reg_periksa')
          ->select(['jumlah' => 'COUNT(DISTINCT reg_periksa.no_rawat)'])
          ->join('poliklinik', 'poliklinik.kd_poli = reg_periksa.kd_poli')
          ->where('reg_periksa.tgl_registrasi', date('Y-m-d'))
          ->where('reg_periksa.kd_poli', $row['kd_poli'])
          ->where('reg_periksa.kd_dokter', $row['kd_dokter'])
          ->oneArray();
        $row['sudah_dilayani'] = $this->db('reg_periksa')
          ->select(['count' => 'COUNT(DISTINCT reg_periksa.no_rawat)'])
          ->join('poliklinik', 'poliklinik.kd_poli = reg_periksa.kd_poli')
          ->where('reg_periksa.tgl_registrasi', date('Y-m-d'))
          ->where('reg_periksa.kd_poli', $row['kd_poli'])
          ->where('reg_periksa.kd_dokter', $row['kd_dokter'])
          ->where('reg_periksa.stts', 'Sudah')
          ->oneArray();
        $row['sudah_dilayani']['jumlah'] = 0;
        if (!empty($row['sudah_dilayani'])) {
          $row['sudah_dilayani']['jumlah'] = $row['sudah_dilayani']['count'];
        }
        $row['selanjutnya'] = $this->db('reg_periksa')
          ->select('reg_periksa.no_reg')
          //->select(['no_urut_reg' => 'ifnull(MAX(CONVERT(RIGHT(reg_periksa.no_reg,3),signed)),0)'])
          ->select('pasien.nm_pasien')
          ->join('pasien', 'pasien.no_rkm_medis = reg_periksa.no_rkm_medis')
          ->where('reg_periksa.tgl_registrasi', $date)
          ->where('reg_periksa.stts', 'Belum')
          ->where('reg_periksa.kd_poli', $row['kd_poli'])
          ->where('reg_periksa.kd_dokter', $row['kd_dokter'])
          ->asc('reg_periksa.no_reg')
          ->toArray();
        $row['get_no_reg'] = $this->db('reg_periksa')
          ->select(['max' => 'ifnull(MAX(CONVERT(RIGHT(no_reg,3),signed)),0)'])
          ->where('tgl_registrasi', $date)
          ->where('kd_poli', $row['kd_poli'])
          ->where('kd_dokter', $row['kd_dokter'])
          ->oneArray();
        $row['diff'] = (strtotime($row['jam_selesai']) - strtotime($row['jam_mulai'])) / 60;
        $row['interval'] = 0;
        if ($row['diff'] == 0) {
          $row['interval'] = round($row['diff'] / $row['get_no_reg']['max']);
        }
        if ($row['interval'] > 10) {
          $interval = 10;
        } else {
          $interval = $row['interval'];
        }
        foreach ($row['selanjutnya'] as $value) {
          //$minutes = $value['no_reg'] * $interval;
          //$row['jam_mulai'] = date('H:i',strtotime('+10 minutes',strtotime($row['jam_mulai'])));
        }

        $result[] = $row;
      }
    }

    return $result;
  }

  public function getDisplayAntrianLoket()
  {
    $title = 'Display Antrian Loket';
    $logo  = $this->settings->get('settings.logo');
    $display = '';

    $_username = $this->core->getUserInfo('fullname', null, true);
    $__username = $this->core->getUserInfo('username');
    if ($this->core->getUserInfo('username') !== '') {
      $__username = 'Tamu';
    }
    $tanggal       = getDayIndonesia(date('Y-m-d')) . ', ' . dateIndonesia(date('Y-m-d'));
    $username      = !empty($_username) ? $_username : $__username;

    $show = isset($_GET['show']) ? $_GET['show'] : "";
    switch ($show) {
      default:
        $display = 'Depan';
        $content = $this->draw('display.antrian.loket.html', [
          'title' => $title,
          'logo' => $logo,
          'powered' => 'Powered by <a href="https://basoro.org/">KhanzaLITE</a>',
          'username' => $username,
          'tanggal' => $tanggal,
          'show' => $show,
          'vidio' => $this->settings->get('anjungan.vidio'),
          'running_text' => $this->settings->get('anjungan.text_loket'),
          'display' => $display
        ]);
        break;
      case "panggil_loket":
        $display = 'Panggil Loket';

        $_username = $this->core->getUserInfo('fullname', null, true);
        $__username = $this->core->getUserInfo('username');
        if ($this->core->getUserInfo('username') !== '') {
          $__username = 'Tamu';
        }
        $tanggal       = getDayIndonesia(date('Y-m-d')) . ', ' . dateIndonesia(date('Y-m-d'));
        $username      = !empty($_username) ? $_username : $__username;

        $setting_antrian_loket = str_replace(",", "','", $this->settings->get('anjungan.antrian_loket'));
        $loket = explode(",", $this->settings->get('anjungan.antrian_loket'));
        $get_antrian = $this->db('mlite_antrian_loket')->select('noantrian')->where('type', 'Loket')->where('postdate', date('Y-m-d'))->desc('start_time')->oneArray();
        $noantrian = 0;
        if (!empty($get_antrian['noantrian'])) {
          $noantrian = $get_antrian['noantrian'];
        }

        $antriloket = $this->settings->get('anjungan.panggil_loket_nomor');
        $tcounter = $antriloket;
        $_tcounter = 1;
        if (!empty($tcounter)) {
          $_tcounter = $tcounter + 1;
        }
        if (isset($_GET['loket'])) {
          $this->db('mlite_antrian_loket')
            ->where('type', 'Loket')
            ->where('noantrian', $tcounter)
            ->where('postdate', date('Y-m-d'))
            ->save(['end_time' => date('H:i:s')]);
          $this->db('mlite_settings')->where('module', 'anjungan')->where('field', 'panggil_loket')->save(['value' => $_GET['loket']]);
          $this->db('mlite_settings')->where('module', 'anjungan')->where('field', 'panggil_loket_nomor')->save(['value' => $_tcounter]);
        }
        if (isset($_GET['antrian'])) {
          $this->db('mlite_settings')->where('module', 'anjungan')->where('field', 'panggil_loket')->save(['value' => $_GET['reset']]);
          $this->db('mlite_settings')->where('module', 'anjungan')->where('field', 'panggil_loket_nomor')->save(['value' => $_GET['antrian']]);
        }
        if (isset($_GET['no_rkm_medis'])) {
          $this->db('mlite_antrian_loket')->where('noantrian', $_GET['noantrian'])->where('postdate', date('Y-m-d'))->save(['no_rkm_medis' => $_GET['no_rkm_medis']]);
        }
        $hitung_antrian = $this->db('mlite_antrian_loket')
          ->where('type', 'Loket')
          ->like('postdate', date('Y-m-d'))
          ->toArray();
        $counter = strlen($tcounter);
        $xcounter = [];
        for ($i = 0; $i < $counter; $i++) {
          $xcounter[] = '<audio id="suarabel' . $i . '" src="{?=url()?}/plugins/anjungan/suara/' . substr($tcounter, $i, 1) . '.wav" ></audio>';
        };

        $content = $this->draw('display.antrian.loket.html', [
          'title' => $title,
          'logo' => $logo,
          'powered' => 'Powered by <a href="https://basoro.org/">KhanzaLITE</a>',
          'username' => $username,
          'tanggal' => $tanggal,
          'show' => $show,
          'loket' => $loket,
          'namaloket' => 'a',
          'panggil_loket' => 'panggil_loket',
          'antrian' => $tcounter,
          'hitung_antrian' => $hitung_antrian,
          'xcounter' => $xcounter,
          'noantrian' => $noantrian,
          'display' => $display
        ]);
        break;
      case "panggil_cs":
        $display = 'Panggil CS';
        $loket = explode(",", $this->settings->get('anjungan.antrian_cs'));
        $get_antrian = $this->db('mlite_antrian_loket')->select('noantrian')->where('type', 'CS')->where('postdate', date('Y-m-d'))->desc('start_time')->oneArray();
        $noantrian = 0;
        if (!empty($get_antrian['noantrian'])) {
          $noantrian = $get_antrian['noantrian'];
        }

        $antriloket = $this->settings->get('anjungan.panggil_cs_nomor');
        $tcounter = $antriloket;
        $_tcounter = 1;
        if (!empty($tcounter)) {
          $_tcounter = $tcounter + 1;
        }
        if (isset($_GET['loket'])) {
          $this->db('mlite_antrian_loket')
            ->where('type', 'CS')
            ->where('noantrian', $tcounter)
            ->where('postdate', date('Y-m-d'))
            ->save(['end_time' => date('H:i:s')]);
          $this->db('mlite_settings')->where('module', 'anjungan')->where('field', 'panggil_cs')->save(['value' => $_GET['loket']]);
          $this->db('mlite_settings')->where('module', 'anjungan')->where('field', 'panggil_cs_nomor')->save(['value' => $_tcounter]);
        }
        if (isset($_GET['antrian'])) {
          $this->db('mlite_settings')->where('module', 'anjungan')->where('field', 'panggil_cs')->save(['value' => $_GET['reset']]);
          $this->db('mlite_settings')->where('module', 'anjungan')->where('field', 'panggil_cs_nomor')->save(['value' => $_GET['antrian']]);
        }
        $hitung_antrian = $this->db('mlite_antrian_loket')
          ->where('type', 'CS')
          ->like('postdate', date('Y-m-d'))
          ->toArray();
        $counter = strlen($tcounter);
        $xcounter = [];
        for ($i = 0; $i < $counter; $i++) {
          $xcounter[] = '<audio id="suarabel' . $i . '" src="{?=url()?}/plugins/anjungan/suara/' . substr($tcounter, $i, 1) . '.wav" ></audio>';
        };

        $content = $this->draw('display.antrian.loket.html', [
          'title' => $title,
          'logo' => $logo,
          'powered' => 'Powered by <a href="https://basoro.org/">KhanzaLITE</a>',
          'username' => $username,
          'tanggal' => $tanggal,
          'show' => $show,
          'loket' => $loket,
          'namaloket' => 'b',
          'panggil_loket' => 'panggil_cs',
          'antrian' => $tcounter,
          'hitung_antrian' => $hitung_antrian,
          'xcounter' => $xcounter,
          'noantrian' => $noantrian,
          'display' => $display
        ]);
        break;
      case "panggil_igd":
        $display = 'Panggil IGD';

        $_username = $this->core->getUserInfo('fullname', null, true);
        $__username = $this->core->getUserInfo('username');
        if ($this->core->getUserInfo('username') !== '') {
          $__username = 'Tamu';
        }
        $tanggal       = getDayIndonesia(date('Y-m-d')) . ', ' . dateIndonesia(date('Y-m-d'));
        $username      = !empty($_username) ? $_username : $__username;

        $setting_antrian_loket = str_replace(",", "','", $this->settings->get('anjungan.antrian_loket'));
        $loket = explode(",", $this->settings->get('anjungan.antrian_loket'));
        $get_antrian = $this->db('mlite_antrian_loket')->select('noantrian')->where('type', 'Igd')->where('postdate', date('Y-m-d'))->desc('start_time')->oneArray();
        $noantrian = 0;
        if (!empty($get_antrian['noantrian'])) {
          $noantrian = $get_antrian['noantrian'];
        }

        $antriloket = $this->settings->get('anjungan.panggil_loket_nomor');
        $tcounter = $antriloket;
        $_tcounter = 1;
        if (!empty($tcounter)) {
          $_tcounter = $tcounter + 1;
        }
        if (isset($_GET['loket'])) {
          $this->db('mlite_antrian_loket')
            ->where('type', 'Igd')
            ->where('noantrian', $tcounter)
            ->where('postdate', date('Y-m-d'))
            ->save(['end_time' => date('H:i:s')]);
          $this->db('mlite_settings')->where('module', 'anjungan')->where('field', 'panggil_loket')->save(['value' => $_GET['loket']]);
          $this->db('mlite_settings')->where('module', 'anjungan')->where('field', 'panggil_loket_nomor')->save(['value' => $_tcounter]);
        }
        if (isset($_GET['antrian'])) {
          $this->db('mlite_settings')->where('module', 'anjungan')->where('field', 'panggil_loket')->save(['value' => $_GET['reset']]);
          $this->db('mlite_settings')->where('module', 'anjungan')->where('field', 'panggil_loket_nomor')->save(['value' => $_GET['antrian']]);
        }
        if (isset($_GET['no_rkm_medis'])) {
          $this->db('mlite_antrian_loket')->where('noantrian', $_GET['noantrian'])->where('postdate', date('Y-m-d'))->save(['no_rkm_medis' => $_GET['no_rkm_medis']]);
        }
        $hitung_antrian = $this->db('mlite_antrian_loket')
          ->where('type', 'Igd')
          ->like('postdate', date('Y-m-d'))
          ->toArray();
        $counter = strlen($tcounter);
        $xcounter = [];
        for ($i = 0; $i < $counter; $i++) {
          $xcounter[] = '<audio id="suarabel' . $i . '" src="{?=url()?}/plugins/anjungan/suara/' . substr($tcounter, $i, 1) . '.wav" ></audio>';
        };

        $content = $this->draw('display.antrian.loket.html', [
          'title' => $title,
          'logo' => $logo,
          'powered' => 'Powered by <a href="https://basoro.org/">KhanzaLITE</a>',
          'username' => $username,
          'tanggal' => $tanggal,
          'show' => $show,
          'loket' => $loket,
          'namaloket' => 'c',
          'panggil_loket' => 'panggil_igd',
          'antrian' => $tcounter,
          'hitung_antrian' => $hitung_antrian,
          'xcounter' => $xcounter,
          'noantrian' => $noantrian,
          'display' => $display
        ]);
        break;
    }

    $assign = [
      'title' => $this->settings->get('settings.nama_instansi'),
      'desc' => $this->settings->get('settings.alamat'),
      'content' => $content
    ];

    $this->setTemplate("canvas.html");

    $this->tpl->set('page', ['title' => $assign['title'], 'desc' => $assign['desc'], 'content' => $assign['content']]);

    //exit();
  }

  public function getDisplayAntrianLaboratorium()
  {
    $logo  = $this->settings->get('settings.logo');
    $title = 'Display Antrian Laboratorium';
    $display = $this->_resultDisplayAntrianLaboratorium();

    $_username = $this->core->getUserInfo('fullname', null, true);
    $__username = $this->core->getUserInfo('username');
    if ($this->core->getUserInfo('username') !== '') {
      $__username = 'Tamu';
    }
    $tanggal       = getDayIndonesia(date('Y-m-d')) . ', ' . dateIndonesia(date('Y-m-d'));
    $username      = !empty($_username) ? $_username : $__username;

    $content = $this->draw('display.antrian.laboratorium.html', [
      'logo' => $logo,
      'title' => $title,
      'powered' => 'Powered by <a href="https://basoro.org/">KhanzaLITE</a>',
      'username' => $username,
      'tanggal' => $tanggal,
      'running_text' => $this->settings->get('anjungan.text_laboratorium'),
      'display' => $display
    ]);

    $assign = [
      'title' => $this->settings->get('settings.nama_instansi'),
      'desc' => $this->settings->get('settings.alamat'),
      'content' => $content
    ];

    $this->setTemplate("canvas.html");

    $this->tpl->set('page', ['title' => $assign['title'], 'desc' => $assign['desc'], 'content' => $assign['content']]);

    //exit();
  }

  public function _resultDisplayAntrianLaboratorium()
  {
    $date = date('Y-m-d');
    $tentukan_hari = date('D', strtotime(date('Y-m-d')));
    $day = array(
      'Sun' => 'AKHAD',
      'Mon' => 'SENIN',
      'Tue' => 'SELASA',
      'Wed' => 'RABU',
      'Thu' => 'KAMIS',
      'Fri' => 'JUMAT',
      'Sat' => 'SABTU'
    );
    $hari = $day[$tentukan_hari];

    $poliklinik = $this->settings('settings', 'laboratorium');
    $rows = $this->db('reg_periksa')
      ->join('pasien', 'pasien.no_rkm_medis=reg_periksa.no_rkm_medis')
      ->where('tgl_registrasi', date('Y-m-d'))
      ->where('kd_poli', $poliklinik)
      ->asc('no_reg')
      ->toArray();

    return $rows;
  }

  public function getDisplayAntrianApotek()
  {
    $logo  = $this->settings->get('settings.logo');
    $title = 'Display Antrian Laboratorium';
    $display = $this->_resultDisplayAntrianApotek();

    $date = date('Y-m-d');
    $tentukan_hari = date('D', strtotime(date('Y-m-d')));
    $day = array(
      'Sun' => 'AKHAD',
      'Mon' => 'SENIN',
      'Tue' => 'SELASA',
      'Wed' => 'RABU',
      'Thu' => 'KAMIS',
      'Fri' => 'JUMAT',
      'Sat' => 'SABTU'
    );
    $hari = $day[$tentukan_hari];

    $jadwal = $this->db('jadwal')->join('dokter', 'dokter.kd_dokter = jadwal.kd_dokter')->join('poliklinik', 'poliklinik.kd_poli = jadwal.kd_poli')->where('hari_kerja', $hari)->toArray();

    $_username = $this->core->getUserInfo('fullname', null, true);
    $__username = $this->core->getUserInfo('username');
    if ($this->core->getUserInfo('username') !== '') {
      $__username = 'Tamu';
    }
    $tanggal       = getDayIndonesia(date('Y-m-d')) . ', ' . dateIndonesia(date('Y-m-d'));
    $username      = !empty($_username) ? $_username : $__username;

    $content = $this->draw('display.antrian.apotek.html', [
      'logo' => $logo,
      'title' => $title,
      'powered' => 'Powered by <a href="https://basoro.org/">KhanzaLITE</a>',
      'username' => $username,
      'tanggal' => $tanggal,
      'running_text' => $jadwal,
      'display' => $display
    ]);

    $assign = [
      'title' => $this->settings->get('settings.nama_instansi'),
      'desc' => $this->settings->get('settings.alamat'),
      'content' => $content
    ];

    $this->setTemplate("canvas.html");

    $this->tpl->set('page', ['title' => $assign['title'], 'desc' => $assign['desc'], 'content' => $assign['content']]);
  }

  public function _resultDisplayAntrianApotek()
  {
    $query = $this->db('reg_periksa')
      ->join('pasien', 'pasien.no_rkm_medis=reg_periksa.no_rkm_medis')
      ->join('resep_obat', 'resep_obat.no_rawat=reg_periksa.no_rawat')
      ->where('tgl_registrasi', date('Y-m-d'))
      ->where('reg_periksa.kd_poli', '<>', 'IGDK')
      ->where('stts', 'Sudah')
      ->asc('resep_obat.jam_peresepan')
      ->toArray();
    $rows = [];
    foreach ($query as $row) {
      $row['status_resep'] = 'Sudah';
      if ($row['jam'] == $row['jam_peresepan']) {
        $row['status_resep'] = 'Belum';
      }
      $rows[] = $row;
    }

    return $rows;
  }

  public function getPanggilAntrian()
  {
    $res = [];

    $date = date('Y-m-d');
    $sql = $this->db()->pdo()->prepare("SELECT * FROM mlite_antrian_loket WHERE status = 1 AND postdate = '$date' ORDER BY noantrian ASC");

    if ($sql) {
      //$data  = $query->fetch_object();
      $sql->execute();
      $data = $sql->fetchAll(\PDO::FETCH_OBJ);;
      //print_r($data);
      // code...
      switch (strtolower($data[0]->type)) {
        case 'loket':
          $kode = 'a';
          break;
        case 'cs':
          $kode = 'b';
          break;
        case 'igd':
          $kode = 'c';
          break;
        default:
          $kode = 'ahhay';
          break;
      }

      //$terbilang = Terbilang::convert($data->noantrian);
      $terbilang = strtolower(terbilang($data[0]->noantrian));
      $loket = strtolower(terbilang($data[0]->loket));
      $text = "antrian $kode $terbilang counter $loket";

      $res = [
        'id' => $data[0]->kd,
        'status' => true,
        'type' => $data[0]->type,
        'kode' => $kode,
        'noantrian' => $data[0]->noantrian,
        'loket' => $data[0]->loket,
        'panggil' => explode(" ", $text)
      ];
    } else {
      $res = [
        'status' => false
      ];
    }

    die(json_encode($res));

    exit();
  }

  public function getPanggilSelesai()
  {
    if (!isset($_GET['id']) || $_GET['id'] == '') die(json_encode(array('status' => false)));
    $kode  = $_GET['id'];
    $query = $this->db('mlite_antrian_loket')->where('kd', $kode)->update('status', 2);
    if ($query) {
      $res = [
        'status' => true,
        'message' => 'Berhasil update',
      ];
    } else {
      $res = [
        'status' => false,
        'message' => 'Gagal update',
      ];
    }

    die(json_encode($res));
    exit();
  }

  public function getSetPanggil()
  {
    if (!isset($_GET['type']) || $_GET['type'] == '') die(json_encode(array('status' => false, 'message' => 'Gagal Type')));
    $type = 'CS';
    if ($_GET['type'] == 'loket') {
      $type = 'Loket';
    }
    $noantrian  = $_GET['noantrian'];
    $loket  = $_GET['loket'];
    $date = date('Y-m-d');
    $query = $this->db('mlite_antrian_loket')->where('type', $type)->where('noantrian', $noantrian)->where('postdate', $date)->update(['status' => 1, 'loket' => $loket]);
    if ($query) {
      $res = [
        'status' => true,
        'message' => 'Berhasil update' . $date,
      ];
    } else {
      $res = [
        'status' => false,
        'message' => 'Gagal update',
      ];
    }

    die(json_encode($res));
    exit();
  }

  public function getSimpanNoRM()
  {
    if (!isset($_GET['no_rkm_medis']) || $_GET['no_rkm_medis'] == '') die(json_encode(array('status' => false, 'message' => 'Gagal Type')));
    if (!isset($_GET['type']) || $_GET['type'] == '') die(json_encode(array('status' => false, 'message' => 'Gagal Type')));
    $type = 'CS';
    if ($_GET['type'] == 'loket') {
      $type = 'Loket';
    }

    $noantrian  = $_GET['noantrian'];
    $no_rkm_medis = $_GET['no_rkm_medis'];
    $query = $this->db('mlite_antrian_loket')->where('noantrian', $noantrian)->where('type', $type)->where('postdate', date('Y-m-d'))->update('no_rkm_medis', $no_rkm_medis);
    if ($query) {
      $res = [
        'status' => true,
        'message' => 'Berhasil',
      ];
    } else {
      $res = [
        'status' => false,
        'message' => 'Gagal',
      ];
    }

    die(json_encode($res));
    exit();
  }

  public function getAjax()
  {
    $show = isset($_GET['show']) ? $_GET['show'] : "";
    switch ($show) {
      default:
        break;
      case "tampilloket":
        $result = $this->db('mlite_antrian_loket')->select('noantrian')->where('type', 'Loket')->where('postdate', date('Y-m-d'))->desc('start_time')->oneArray();
        $noantrian = $result['noantrian'];
        if ($noantrian > 0) {
          $next_antrian = $noantrian + 1;
        } else {
          $next_antrian = 1;
        }
        echo '<div id="nomernya" align="center">';
        echo '<h1 class="display-1">';
        echo 'A' . $next_antrian;
        echo '</h1>';
        echo '[' . date('Y-m-d') . ']';
        echo '</div>';
        echo '<br>';
        break;
      case "printloket":
        $result = $this->db('mlite_antrian_loket')->select('noantrian')->where('type', 'Loket')->where('postdate', date('Y-m-d'))->desc('start_time')->oneArray();
        $noantrian = $result['noantrian'];
        if ($noantrian > 0) {
          $next_antrian = $noantrian + 1;
        } else {
          $next_antrian = 1;
        }
        echo '<div id="nomernya" align="center">';
        echo '<h1 class="display-1">';
        echo 'A' . $next_antrian;
        echo '</h1>';
        echo '[' . date('Y-m-d') . ']';
        echo '</div>';
        echo '<br>';
?>
        <script>
          $(document).ready(function() {
            $("#btnKRM").on('click', function() {
              $("#formloket").submit(function(e) {
                e.preventDefault();
                e.stopImmediatePropagation();
                $.ajax({
                  url: "<?php echo url() . '/anjungan/ajax?show=simpanloket&noantrian=' . $next_antrian; ?>",
                  type: "POST",
                  data: $(this).serialize(),
                  success: function(data) {
                    setTimeout('$("#loading").hide()', 1000);
                    //window.location.href = "{?=url('anjungan/pasien')?}";
                  }
                });
                return false;
              });
            });
          })
        </script>
      <?php
        break;
      case "simpanloket":
        $this->db('mlite_antrian_loket')
          ->save([
            'kd' => NULL,
            'type' => 'Loket',
            'noantrian' => $_GET['noantrian'],
            'postdate' => date('Y-m-d'),
            'start_time' => date('H:i:s'),
            'end_time' => '00:00:00'
          ]);
        //redirect(url('anjungan/pasien'));
        break;
      case "tampilcs":
        $result = $this->db('mlite_antrian_loket')->select('noantrian')->where('type', 'CS')->where('postdate', date('Y-m-d'))->desc('start_time')->oneArray();
        $noantrian = $result['noantrian'];
        if ($noantrian > 0) {
          $next_antrian = $noantrian + 1;
        } else {
          $next_antrian = 1;
        }
        echo '<div id="nomernya" align="center">';
        echo '<h1 class="display-1">';
        echo 'B' . $next_antrian;
        echo '</h1>';
        echo '[' . date('Y-m-d') . ']';
        echo '</div>';
        echo '<br>';
        break;
      case "printcs":
        $result = $this->db('mlite_antrian_loket')->select('noantrian')->where('type', 'CS')->where('postdate', date('Y-m-d'))->desc('start_time')->oneArray();
        $noantrian = $result['noantrian'];
        if ($noantrian > 0) {
          $next_antrian = $noantrian + 1;
        } else {
          $next_antrian = 1;
        }
        echo '<div id="nomernya" align="center">';
        echo '<h1 class="display-1">';
        echo 'B' . $next_antrian;
        echo '</h1>';
        echo '[' . date('Y-m-d') . ']';
        echo '</div>';
        echo '<br>';
      ?>
        <script>
          $(document).ready(function() {
            $("#btnKRMCS").on('click', function() {
              $("#formcs").submit(function(e) {
                e.preventDefault();
                e.stopImmediatePropagation();
                $.ajax({
                  url: "<?php echo url() . '/anjungan/ajax?show=simpancs&noantrian=' . $next_antrian; ?>",
                  type: "POST",
                  data: $(this).serialize(),
                  success: function(data) {
                    setTimeout('$("#loading").hide()', 1000);
                    //window.location.href = "{?=url('anjungan/pasien')?}";
                  }
                });
                return false;
              });
            });
          })
        </script>
      <?php
        break;
      case "simpancs":
        $this->db('mlite_antrian_loket')
          ->save([
            'kd' => NULL,
            'type' => 'CS',
            'noantrian' => $_GET['noantrian'],
            'postdate' => date('Y-m-d'),
            'start_time' => date('H:i:s'),
            'end_time' => '00:00:00'
          ]);
        //redirect(url('anjungan/pasien'));
        break;
      case "tampiligd":
        $result = $this->db('mlite_antrian_loket')->select('noantrian')->where('type', 'Igd')->where('postdate', date('Y-m-d'))->desc('start_time')->oneArray();
        $noantrian = $result['noantrian'];
        if ($noantrian > 0) {
          $next_antrian = $noantrian + 1;
        } else {
          $next_antrian = 1;
        }
        echo '<div id="nomernya" align="center">';
        echo '<h1 class="display-1">';
        echo 'C' . $next_antrian;
        echo '</h1>';
        echo '[' . date('Y-m-d') . ']';
        echo '</div>';
        echo '<br>';
        break;
      case "printIgd":
        $result = $this->db('mlite_antrian_loket')->select('noantrian')->where('type', 'Igd')->where('postdate', date('Y-m-d'))->desc('start_time')->oneArray();
        $noantrian = $result['noantrian'];
        if ($noantrian > 0) {
          $next_antrian = $noantrian + 1;
        } else {
          $next_antrian = 1;
        }
        echo '<div id="nomernya" align="center">';
        echo '<h1 class="display-1">';
        echo 'C' . $next_antrian;
        echo '</h1>';
        echo '[' . date('Y-m-d') . ']';
        echo '</div>';
        echo '<br>';
      ?>
        <script>
          $(document).ready(function() {
            $("#btnKRMIgd").on('click', function() {
              $("#formIgd").submit(function(e) {
                e.preventDefault();
                e.stopImmediatePropagation();
                $.ajax({
                  url: "<?php echo url() . '/anjungan/ajax?show=simpanigd&noantrian=' . $next_antrian; ?>",
                  type: "POST",
                  data: $(this).serialize(),
                  success: function(data) {
                    setTimeout('$("#loading").hide()', 1000);
                    //window.location.href = "{?=url('anjungan/pasien')?}";
                  }
                });
                return false;
              });
            });
          })
        </script>
<?php
        break;
      case "simpanigd":
        $this->db('mlite_antrian_loket')
          ->save([
            'kd' => NULL,
            'type' => 'Igd',
            'noantrian' => $_GET['noantrian'],
            'postdate' => date('Y-m-d'),
            'start_time' => date('H:i:s'),
            'end_time' => '00:00:00'
          ]);
        //redirect(url('anjungan/pasien'));
        break;
      case "loket":
        //$antrian = $this->db('antriloket')->oneArray();
        //echo $antrian['loket'];
        echo $this->settings->get('anjungan.panggil_loket');
        break;
      case "antriloket":
        //$antrian = $this->db('antriloket')->oneArray();
        //$antrian = $antrian['antrian'] - 1;
        $antrian = $this->settings->get('anjungan.panggil_loket_nomor') - 1;
        if ($antrian == '-1') {
          echo '0';
        } else {
          echo $antrian;
        }
        break;
      case "cs":
        //$antrian = $this->db('antrics')->oneArray();
        //echo $antrian['loket'];
        echo $this->settings->get('anjungan.panggil_cs');
        break;
      case "antrics":
        //$antrian = $this->db('antrics')->oneArray();
        //$antrian = $antrian['antrian'] - 1;
        $antrian = $this->settings->get('anjungan.panggil_cs_nomor') - 1;
        if ($antrian == '-1') {
          echo '0';
        } else {
          echo $antrian;
        }
        break;
      case "igd":
        //$antrian = $this->db('antriloket')->oneArray();
        //echo $antrian['loket'];
        echo $this->settings->get('anjungan.panggil_loket');
        break;
      case "antriigd":
        //$antrian = $this->db('antriloket')->oneArray();
        //$antrian = $antrian['antrian'] - 1;
        $antrian = $this->settings->get('anjungan.panggil_loket_nomor') - 1;
        if ($antrian == '-1') {
          echo '0';
        } else {
          echo $antrian;
        }
        break;
      case "get-skdp":
        if (!empty($_POST['no_rkm_medis'])) {
          $data = array();
          $query = $this->db('skdp_bpjs')
            ->join('dokter', 'dokter.kd_dokter = skdp_bpjs.kd_dokter')
            ->join('booking_registrasi', 'booking_registrasi.tanggal_periksa = skdp_bpjs.tanggal_datang')
            ->join('poliklinik', 'poliklinik.kd_poli = booking_registrasi.kd_poli')
            ->join('pasien', 'pasien.no_rkm_medis = skdp_bpjs.no_rkm_medis')
            ->where('skdp_bpjs.no_rkm_medis', $_POST['no_rkm_medis'])
            ->where('booking_registrasi.kd_poli', $_POST['kd_poli'])
            ->desc('skdp_bpjs.tanggal_datang')
            ->oneArray();
          if (!empty($query)) {
            $data['status'] = 'ok';
            $data['result'] = $query;
          } else {
            $data['status'] = 'err';
            $data['result'] = '';
          }
          echo json_encode($data);
        }
        break;

      case "get-daftar":
        if (!empty($_POST['no_rkm_medis_daftar'])) {
          $data = array();
          $query = $this->db('pasien')
            ->where('no_rkm_medis', $_POST['no_rkm_medis_daftar'])
            ->oneArray();
          if (!empty($query)) {
            $data['status'] = 'ok';
            $data['result'] = $query;
          } else {
            $data['status'] = 'err';
            $data['result'] = '';
          }
          echo json_encode($data);
        }
        break;

      case "get-poli":
        if (!empty($_POST['no_rkm_medis'])) {
          $data = array();
          if ($this->db('reg_periksa')->where('no_rkm_medis', $_POST['no_rkm_medis'])->where('tgl_registrasi', $_POST['tgl_registrasi'])->oneArray()) {
            $data['status'] = 'exist';
            $data['result'] = '';
            echo json_encode($data);
          } else {
            $tanggal = $_POST['tgl_registrasi'];
            $tentukan_hari = date('D', strtotime($tanggal));
            $day = array('Sun' => 'AKHAD', 'Mon' => 'SENIN', 'Tue' => 'SELASA', 'Wed' => 'RABU', 'Thu' => 'KAMIS', 'Fri' => 'JUMAT', 'Sat' => 'SABTU');
            $hari = $day[$tentukan_hari];
            $query = $this->db('jadwal')
              ->select(['kd_poli' => 'jadwal.kd_poli'])
              ->select(['nm_poli' => 'poliklinik.nm_poli'])
              ->select(['jam_mulai' => 'jadwal.jam_mulai'])
              ->select(['jam_selesai' => 'jadwal.jam_selesai'])
              ->join('poliklinik', 'poliklinik.kd_poli = jadwal.kd_poli')
              ->join('dokter', 'dokter.kd_dokter = jadwal.kd_dokter')
              ->where('jadwal.hari_kerja', $hari)
              ->where('jadwal.kd_poli','!=','IGDK')
              ->toArray();
            if (!empty($query)) {
              $data['status'] = 'ok';
              $data['result'] = $query;
            } else {
              $data['status'] = 'err';
              $data['result'] = '';
            }
            echo json_encode($data);
          }
        }
        break;
      case "get-dokter":
        if (!empty($_POST['kd_poli'])) {
          $tanggal = $_POST['tgl_registrasi'];
          $tentukan_hari = date('D', strtotime($tanggal));
          $day = array('Sun' => 'AKHAD', 'Mon' => 'SENIN', 'Tue' => 'SELASA', 'Wed' => 'RABU', 'Thu' => 'KAMIS', 'Fri' => 'JUMAT', 'Sat' => 'SABTU');
          $hari = $day[$tentukan_hari];
          $data = array();
          $result = $this->db('jadwal')
            ->select(['kd_dokter' => 'jadwal.kd_dokter'])
            ->select(['nm_dokter' => 'dokter.nm_dokter'])
            ->select(['kuota' => 'jadwal.kuota'])
            ->join('poliklinik', 'poliklinik.kd_poli = jadwal.kd_poli')
            ->join('dokter', 'dokter.kd_dokter = jadwal.kd_dokter')
            ->where('jadwal.kd_poli', $_POST['kd_poli'])
            ->like('jadwal.hari_kerja', $hari)
            ->oneArray();
          $check_kuota = $this->db('reg_periksa')
            ->select(['count' => 'COUNT(DISTINCT no_rawat)'])
            ->where('kd_poli', $_POST['kd_poli'])
            ->where('tgl_registrasi', $_POST['tgl_registrasi'])
            ->oneArray();
          $curr_count = $check_kuota['count'];
          $curr_kuota = $result['kuota'];
          $online = $curr_kuota / 2;
          if ($curr_count > $online) {
            $data['status'] = 'limit';
          } else {
            $query = $this->db('jadwal')
              ->select(['kd_dokter' => 'jadwal.kd_dokter'])
              ->select(['nm_dokter' => 'dokter.nm_dokter'])
              ->join('poliklinik', 'poliklinik.kd_poli = jadwal.kd_poli')
              ->join('dokter', 'dokter.kd_dokter = jadwal.kd_dokter')
              ->where('jadwal.kd_poli', $_POST['kd_poli'])
              ->like('jadwal.hari_kerja', $hari)
              ->toArray();
            if (!empty($query)) {
              $data['status'] = 'ok';
              $data['result'] = $query;
            } else {
              $data['status'] = 'err';
              $data['result'] = '';
            }
            echo json_encode($data);
          }
        }
        break;
      case "get-namapoli":
        //$_POST['kd_poli'] = 'INT';
        if (!empty($_POST['kd_poli'])) {
          $data = array();
          $result = $this->db('poliklinik')->where('kd_poli', $_POST['kd_poli'])->oneArray();
          if (!empty($result)) {
            $data['status'] = 'ok';
            $data['result'] = $result;
          } else {
            $data['status'] = 'err';
            $data['result'] = '';
          }
          echo json_encode($data);
        }
        break;
      case "get-namadokter":
        //$_POST['kd_dokter'] = 'DR001';
        if (!empty($_POST['kd_dokter'])) {
          $data = array();
          $result = $this->db('dokter')->where('kd_dokter', $_POST['kd_dokter'])->oneArray();
          if (!empty($result)) {
            $data['status'] = 'ok';
            $data['result'] = $result;
          } else {
            $data['status'] = 'err';
            $data['result'] = '';
          }
          echo json_encode($data);
        }
        break;
      case "post-registrasi":
        if (!empty($_POST['no_rkm_medis'])) {
          $data = array();
          $date = date('Y-m-d');

          $_POST['no_reg']     = $this->core->setNoReg($_POST['kd_dokter'], $_POST['kd_poli']);
          $_POST['hubunganpj'] = $this->core->getPasienInfo('keluarga', $_POST['no_rkm_medis']);
          $_POST['almt_pj']    = $this->core->getPasienInfo('alamat', $_POST['no_rkm_medis']);
          $_POST['p_jawab']    = $this->core->getPasienInfo('namakeluarga', $_POST['no_rkm_medis']);
          $_POST['stts']       = 'Belum';

          $cek_stts_daftar = $this->db('reg_periksa')->where('no_rkm_medis', $_POST['no_rkm_medis'])->count();
          $_POST['stts_daftar'] = 'Baru';
          if ($cek_stts_daftar > 0) {
            $_POST['stts_daftar'] = 'Lama';
          }

          $biaya_reg = $this->db('poliklinik')->where('kd_poli', $_POST['kd_poli'])->oneArray();
          $_POST['biaya_reg'] = $biaya_reg['registrasi'];
          if ($_POST['stts_daftar'] == 'Lama') {
            $_POST['biaya_reg'] = $biaya_reg['registrasilama'];
          }

          $cek_status_poli = $this->db('reg_periksa')->where('no_rkm_medis', $_POST['no_rkm_medis'])->where('kd_poli', $_POST['kd_poli'])->count();
          $_POST['status_poli'] = 'Baru';
          if ($cek_status_poli > 0) {
            $_POST['status_poli'] = 'Lama';
          }

          $tanggal = new \DateTime($this->core->getPasienInfo('tgl_lahir', $_POST['no_rkm_medis']));
          $today = new \DateTime($date);
          $y = $today->diff($tanggal)->y;
          $m = $today->diff($tanggal)->m;
          $d = $today->diff($tanggal)->d;

          $umur = "0";
          $sttsumur = "Th";
          if ($y > 0) {
            $umur = $y;
            $sttsumur = "Th";
          } else if ($y == 0) {
            if ($m > 0) {
              $umur = $m;
              $sttsumur = "Bl";
            } else if ($m == 0) {
              $umur = $d;
              $sttsumur = "Hr";
            }
          }
          $_POST['umurdaftar'] = $umur;
          $_POST['sttsumur'] = $sttsumur;
          $_POST['status_lanjut']   = 'Ralan';
          $_POST['kd_pj']           = $this->settings->get('anjungan.carabayar_umum');
          $_POST['status_bayar']    = 'Belum Bayar';
          $_POST['no_rawat'] = $this->core->setNoRawat($date);
          $_POST['jam_reg'] = date('H:i:s');

          $query = $this->db('reg_periksa')->save($_POST);

          $result = $this->db('reg_periksa')
            ->select('reg_periksa.no_rkm_medis')
            ->select('pasien.nm_pasien')
            ->select('pasien.alamat')
            ->select('reg_periksa.tgl_registrasi')
            ->select('reg_periksa.jam_reg')
            ->select('reg_periksa.no_rawat')
            ->select('reg_periksa.no_reg')
            ->select('poliklinik.nm_poli')
            ->select('dokter.nm_dokter')
            ->select('reg_periksa.status_lanjut')
            ->select('penjab.png_jawab')
            ->join('poliklinik', 'poliklinik.kd_poli = reg_periksa.kd_poli')
            ->join('dokter', 'dokter.kd_dokter = reg_periksa.kd_dokter')
            ->join('penjab', 'penjab.kd_pj = reg_periksa.kd_pj')
            ->join('pasien', 'pasien.no_rkm_medis = reg_periksa.no_rkm_medis')
            ->where('reg_periksa.tgl_registrasi', $_POST['tgl_registrasi'])
            ->where('reg_periksa.no_rkm_medis', $_POST['no_rkm_medis'])
            ->oneArray();

          if (!empty($result)) {
            $data['status'] = 'ok';
            $data['result'] = $result;
          } else {
            $data['status'] = 'err';
            $data['result'] = '';
          }
          echo json_encode($data);
        }
        break;
    }
    exit();
  }

  public function getPresensi()
  {

    $title = 'Presensi Pegawai';
    $logo  = $this->settings->get('settings.logo');

    $tanggal       = getDayIndonesia(date('Y-m-d')) . ', ' . dateIndonesia(date('Y-m-d'));

    $content = $this->draw('presensi.html', [
      'title' => $title,
      'notify' => $this->core->getNotify(),
      'logo' => $logo,
      'powered' => 'Powered by <a href="https://basoro.org/">KhanzaLITE</a>',
      'tanggal' => $tanggal,
      'running_text' => $this->settings->get('anjungan.text_poli'),
      'jam_jaga' => $this->db('jam_jaga')->group('jam_masuk')->toArray()
    ]);

    $assign = [
      'title' => $this->settings->get('settings.nama_instansi'),
      'desc' => $this->settings->get('settings.alamat'),
      'content' => $content
    ];

    $this->setTemplate("canvas.html");

    $this->tpl->set('page', ['title' => $assign['title'], 'desc' => $assign['desc'], 'content' => $assign['content']]);
  }

  public function getGeolocation()
  {

    $idpeg = $this->db('barcode')->where('barcode', $this->core->getUserInfo('username', null, true))->oneArray();

    if (isset($_GET['lat'], $_GET['lng'])) {
      if (!$this->db('mlite_geolocation_presensi')->where('id', $idpeg['id'])->where('tanggal', date('Y-m-d'))->oneArray()) {
        $this->db('mlite_geolocation_presensi')
          ->save([
            'id' => $idpeg['id'],
            'tanggal' => date('Y-m-d'),
            'latitude' => $_GET['lat'],
            'longitude' => $_GET['lng']
          ]);
      }
    }

    exit();
  }

  public function getUpload()
  {
    if ($photo = isset_or($_FILES['webcam']['tmp_name'], false)) {
      $img = new \Systems\Lib\Image;
      if ($img->load($photo)) {
        if ($img->getInfos('width') < $img->getInfos('height')) {
          $img->crop(0, 0, $img->getInfos('width'), $img->getInfos('width'));
        } else {
          $img->crop(0, 0, $img->getInfos('height'), $img->getInfos('height'));
        }

        if ($img->getInfos('width') > 512) {
          $img->resize(512, 512);
        }
        $gambar = uniqid('photo') . "." . $img->getInfos('type');
      }

      if (isset($img) && $img->getInfos('width')) {

        $img->save(WEBAPPS_PATH . "/presensi/" . $gambar);

        $urlnya         = WEBAPPS_URL . '/presensi/' . $gambar;
        $barcode        = $_GET['barcode'];

        $idpeg          = $this->db('barcode')->where('barcode', $barcode)->oneArray();
        $jam_jaga       = $this->db('jam_jaga')->join('pegawai', 'pegawai.departemen = jam_jaga.dep_id')->where('pegawai.id', $idpeg['id'])->where('jam_jaga.shift', $_GET['shift'])->oneArray();
        $jadwal_pegawai = $this->db('jadwal_pegawai')->where('id', $idpeg['id'])->where('h' . date('j'), $_GET['shift'])->oneArray();

        $set_keterlambatan  = $this->db('set_keterlambatan')->toArray();
        $toleransi      = $set_keterlambatan['toleransi'];
        $terlambat1     = $set_keterlambatan['terlambat1'];
        $terlambat2     = $set_keterlambatan['terlambat2'];

        $valid = $this->db('rekap_presensi')->where('id', $idpeg['id'])->where('shift', $jam_jaga['shift'])->like('jam_datang', '%' . date('Y-m-d') . '%')->oneArray();

        if ($valid) {
          $this->notify('failure', 'Anda sudah presensi untuk tanggal ' . date('Y-m-d'));
          //}elseif((!empty($idpeg['id']))&&(!empty($jam_jaga['shift']))&&($jadwal_pegawai)&&(!$valid)) {
        } elseif ((!empty($idpeg['id']))) {
          $cek = $this->db('temporary_presensi')->where('id', $idpeg['id'])->oneArray();

          if (!$cek) {
            if (empty($urlnya)) {
              $this->notify('failure', 'Pilih shift dulu...!!!!');
            } else {

              $status = 'Tepat Waktu';

              if ((strtotime(date('Y-m-d H:i:s')) - strtotime(date('Y-m-d') . ' ' . $jam_jaga['jam_masuk'])) > ($toleransi * 60)) {
                $status = 'Terlambat Toleransi';
              }
              if ((strtotime(date('Y-m-d H:i:s')) - strtotime(date('Y-m-d') . ' ' . $jam_jaga['jam_masuk'])) > ($terlambat1 * 60)) {
                $status = 'Terlambat I';
              }
              if ((strtotime(date('Y-m-d H:i:s')) - strtotime(date('Y-m-d') . ' ' . $jam_jaga['jam_masuk'])) > ($terlambat2 * 60)) {
                $status = 'Terlambat II';
              }

              if (strtotime(date('Y-m-d H:i:s')) - (date('Y-m-d') . ' ' . $jam_jaga['jam_masuk']) > ($toleransi * 60)) {
                $awal  = new \DateTime(date('Y-m-d') . ' ' . $jam_jaga['jam_masuk']);
                $akhir = new \DateTime();
                $diff = $akhir->diff($awal, true); // to make the difference to be always positive.
                $keterlambatan = $diff->format('%H:%I:%S');
              }

              $insert = $this->db('temporary_presensi')
                ->save([
                  'id' => $idpeg['id'],
                  'shift' => $jam_jaga['shift'],
                  'jam_datang' => date('Y-m-d H:i:s'),
                  'jam_pulang' => NULL,
                  'status' => $status,
                  'keterlambatan' => $keterlambatan,
                  'durasi' => '',
                  'photo' => $urlnya
                ]);

              if ($insert) {
                $this->notify('success', 'Presensi Masuk jam ' . $jam_jaga['jam_masuk'] . ' ' . $status . ' ' . $keterlambatan);
              }
            }
          } elseif ($cek) {

            $status = $cek['status'];
            if ((strtotime(date('Y-m-d H:i:s')) - strtotime(date('Y-m-d') . ' ' . $jam_jaga['jam_pulang'])) < 0) {
              $status = $cek['status'] . ' & PSW';
            }

            $awal  = new \DateTime($cek['jam_datang']);
            $akhir = new \DateTime();
            $diff = $akhir->diff($awal, true); // to make the difference to be always positive.
            $durasi = $diff->format('%H:%I:%S');

            $ubah = $this->db('temporary_presensi')
              ->where('id', $idpeg['id'])
              ->save([
                'jam_pulang' => date('Y-m-d H:i:s'),
                'status' => $status,
                'durasi' => $durasi
              ]);

            if ($ubah) {
              $presensi = $this->db('temporary_presensi')->where('id', $cek['id'])->oneArray();
              $insert = $this->db('rekap_presensi')
                ->save([
                  'id' => $presensi['id'],
                  'shift' => $presensi['shift'],
                  'jam_datang' => $presensi['jam_datang'],
                  'jam_pulang' => $presensi['jam_pulang'],
                  'status' => $presensi['status'],
                  'keterlambatan' => $presensi['keterlambatan'],
                  'durasi' => $presensi['durasi'],
                  'keterangan' => '-',
                  'photo' => $presensi['photo']
                ]);
              if ($insert) {
                $this->notify('success', 'Presensi pulang telah disimpan');
                $this->db('temporary_presensi')->where('id', $cek['id'])->delete();
              }
            }
          }
        } else {
          $this->notify('failure', 'ID Pegawai atau jadwal shift tidak sesuai. Silahkan pilih berdasarkan shift departemen anda!');
        }
      }
    }
    //echo 'Upload';
    exit();
  }

  public function getDisplayBed()
  {
    $title = 'Display Bed Management';
    $logo  = $this->settings->get('settings.logo');
    $display = $this->_resultDisplayBed();

    $_username = $this->core->getUserInfo('fullname', null, true);
    $__username = $this->core->getUserInfo('username');
    if ($this->core->getUserInfo('username') !== '') {
      $__username = 'Tamu';
    }
    $tanggal       = getDayIndonesia(date('Y-m-d')) . ', ' . dateIndonesia(date('Y-m-d'));
    $username      = !empty($_username) ? $_username : $__username;

    $content = $this->draw('display.bed.html', [
      'title' => $title,
      'logo' => $logo,
      'powered' => 'Powered by <a href="https://basoro.org/">KhanzaLITE</a>',
      'username' => $username,
      'tanggal' => $tanggal,
      'running_text' => $this->settings->get('anjungan.text_poli'),
      'display' => $display
    ]);

    $assign = [
      'title' => $this->settings->get('settings.nama_instansi'),
      'desc' => $this->settings->get('settings.alamat'),
      'content' => $content
    ];

    $this->setTemplate("canvas.html");

    $this->tpl->set('page', ['title' => $assign['title'], 'desc' => $assign['desc'], 'content' => $assign['content']]);
  }

  public function _resultDisplayBed()
  {
    $query = $this->db()->pdo()->prepare("SELECT a.nm_bangsal, b.kelas , a.kd_bangsal FROM bangsal a, kamar b WHERE a.kd_bangsal = b.kd_bangsal AND b.statusdata = '1' GROUP BY b.kd_bangsal , b.kelas");
    $query->execute();
    $rows = $query->fetchAll(\PDO::FETCH_ASSOC);;

    $result = [];
    if (count($rows)) {
      foreach ($rows as $row) {
        $row['kosong'] = $this->db('kamar')
          ->select(['jumlah' => 'COUNT(kamar.status)'])
          ->join('bangsal', 'bangsal.kd_bangsal = kamar.kd_bangsal')
          ->where('bangsal.kd_bangsal', $row['kd_bangsal'])
          ->where('kamar.kelas', $row['kelas'])
          ->where('kamar.status', 'KOSONG')
          ->where('kamar.statusdata', '1')
          ->group(array('kamar.kd_bangsal', 'kamar.kelas'))
          ->oneArray();
        $row['isi'] = $this->db('kamar')
          ->select(['jumlah' => 'COUNT(kamar.status)'])
          ->join('bangsal', 'bangsal.kd_bangsal = kamar.kd_bangsal')
          ->where('bangsal.kd_bangsal', $row['kd_bangsal'])
          ->where('kamar.kelas', $row['kelas'])
          ->where('kamar.status', 'ISI')
          ->where('kamar.statusdata', '1')
          ->group(array('kamar.kd_bangsal', 'kamar.kelas'))
          ->oneArray();
        $result[] = $row;
      }
    }

    return $result;
  }

  public function getSepMandiri()
  {
    $notif = "";
    if (isset($_SESSION['message'])) {
      $notif = $_SESSION['message'];
      unset($_SESSION['message']);
    }
    $title = 'Display SEP Mandiri';
    $logo  = $this->settings->get('settings.logo');

    $_username = $this->core->getUserInfo('fullname', null, true);
    $__username = $this->core->getUserInfo('username');
    if ($this->core->getUserInfo('username') !== '') {
      $__username = 'Tamu';
    }
    $tanggal       = getDayIndonesia(date('Y-m-d')) . ', ' . dateIndonesia(date('Y-m-d'));
    $username      = !empty($_username) ? $_username : $__username;

    $content = $this->draw('sep.mandiri.html', [
      'notif' => $notif,
      'title' => $title,
      'logo' => $logo,
      'powered' => 'Powered by <a href="https://basoro.org/">KhanzaLITE</a>',
      'username' => $username,
      'tanggal' => $tanggal,
      'running_text' => $this->settings->get('anjungan.text_anjungan'),
    ]);

    $assign = [
      'title' => $this->settings->get('settings.nama_instansi'),
      'desc' => $this->settings->get('settings.alamat'),
      'content' => $content
    ];

    $this->setTemplate("canvas.html");

    $this->tpl->set('page', ['title' => $assign['title'], 'desc' => $assign['desc'], 'content' => $assign['content']]);
  }

  public function getSepMandiriCek()
  {
    session_start();
    $date = date('Y-m-d');
    $status = "pagi";
    if (isset($_POST['cekrm']) && isset($_POST['no_rkm_medis']) && $_POST['no_rkm_medis'] != '') {
      if (date('H:i:s') <= '14:00:00' ) {
        $status = 'sore';
      }
      $pasien = $this->db('pasien')->where('no_rkm_medis', $_POST['no_rkm_medis'])->oneArray();
      $checkSEP = $this->db('bridging_sep')->where('nomr',$_POST['no_rkm_medis'])->where('tglsep',$date)->oneArray();
      $reg = $this->db('reg_periksa')->join('poliklinik','poliklinik.kd_poli = reg_periksa.kd_poli')->where('no_rkm_medis',$_POST['no_rkm_medis'])->where('tgl_registrasi',$date)->oneArray();
      if($pasien && $reg){
        if (date("H:i:s") <= '15:00:00') {
          if (strpos($reg['nm_poli'], 'SORE') !== false) {
            $_SESSION['message'] = "Anda Terdaftar Untuk Hari Ini Pada Poli Sore. Silahkan Datang Lagi Pada Sore Hari. Atau silakan ke loket pendaftaran jika ingin pindah ke poli pagi";
            redirect(url('anjungan/sep'));
          } else {
            if ($checkSEP) {
              redirect(url('anjungan/sep/cetaksep/' . $checkSEP['no_sep']));
            } else {
              redirect(url('anjungan/sep/' . $pasien['no_peserta'] . '/' . $_POST['no_rkm_medis'] . '/' . $status));
            }
          }
        } else {
          if (strpos($reg['nm_poli'], 'SORE') !== false) {
            if ($checkSEP) {
              redirect(url('anjungan/sep/cetaksep/' . $checkSEP['no_sep']));
            } else {
              redirect(url('anjungan/sep/' . $pasien['no_peserta'] . '/' . $_POST['no_rkm_medis'] . '/' . $status));
            }
          } else {
            $_SESSION['message'] = "Anda Terdaftar Untuk Hari Ini Pada Poli Pagi. Silahkan Bertanya Kepada Satpam Ataupun Resepsionis";
            redirect(url('anjungan/sep'));
          }
        }
      }else{
        $_SESSION['message'] = "Anda Belum Terdaftar Untuk Hari Ini. Silahkan Menggunakan Menu Pendaftaran";
        redirect(url('anjungan/sep'));
      }
    } else {
      redirect(url('anjungan/sep'));
    }
    exit();
  }

  public function getSepMandiriNokaNorm()
  {
    date_default_timezone_set('UTC');
    $tStamp = strval(time() - strtotime("1970-01-01 00:00:00"));
    $key = $this->consid . $this->secretkey . $tStamp;

    date_default_timezone_set($this->settings->get('settings.timezone'));
    $slug = parseURL();
    $sep_response = '';
    if (count($slug) == 5 && $slug[0] == 'anjungan' && $slug[1] == 'sep') {

      $url = "Rujukan/List/Peserta/" . $slug[2];

      $url = $this->api_url . '' . $url;
      $output = BpjsService::get($url, NULL, $this->consid, $this->secretkey, $this->user_key, $tStamp);
      $json = json_decode($output, true);
      //var_dump($json);

      if($json['metaData']['code'] == 201){
        $url = "Rujukan/RS/List/Peserta/" . $slug[2];

        $url = $this->api_url . '' . $url;
        $output = BpjsService::get($url, NULL, $this->consid, $this->secretkey, $this->user_key, $tStamp);
        $json = json_decode($output, true);
      }

      $code = $json['metaData']['code'];
      $message = $json['metaData']['message'];
      $stringDecrypt = stringDecrypt($key, $json['response']);
      $decompress = '""';

      if (!empty($json)) :
        if ($code != "200") {
          $sep_response = $message;
        } else {
          if (!empty($stringDecrypt)) {
            $decompress = decompress($stringDecrypt);
            $sep_response = json_decode($decompress, true);
          } else {
            $sep_response = "Sambungan ke server BPJS sedang ada gangguan. Silahkan ulangi lagi dengan menekan tombol REFRESH";
          }
        }
      else :
        $sep_response = "Sambungan ke server BPJS sedang ada gangguan. Silahkan ulangi lagi dengan menekan tombol REFRESH";
      endif;
    }

    $title = 'Display SEP Mandiri';
    $logo  = $this->settings->get('settings.logo');

    $_username = $this->core->getUserInfo('fullname', null, true);
    $__username = $this->core->getUserInfo('username');
    if ($this->core->getUserInfo('username') !== '') {
      $__username = 'Tamu';
    }
    $tanggal       = getDayIndonesia(date('Y-m-d')) . ', ' . dateIndonesia(date('Y-m-d'));
    $username      = !empty($_username) ? $_username : $__username;

    $content = $this->draw('sep.mandiri.noka.norm.html', [
      'title' => $title,
      'logo' => $logo,
      'powered' => 'Powered by <a href="https://basoro.org/">KhanzaLITE</a>',
      'username' => $username,
      'tanggal' => $tanggal,
      'running_text' => $this->settings->get('anjungan.text_anjungan'),
      'no_rkm_medis' => $slug[3],
      'status' => $slug[4],
      'sep_response' => $sep_response
    ]);

    $assign = [
      'title' => $this->settings->get('settings.nama_instansi'),
      'desc' => $this->settings->get('settings.alamat'),
      'content' => $content
    ];

    $this->setTemplate("canvas.html");

    $this->tpl->set('page', ['title' => $assign['title'], 'desc' => $assign['desc'], 'content' => $assign['content']]);
  }

  public function getSepMandiriBikin()
  {
    date_default_timezone_set('UTC');
    $tStamp = strval(time() - strtotime("1970-01-01 00:00:00"));
    $key = $this->consid . $this->secretkey . $tStamp;

    date_default_timezone_set($this->settings->get('settings.timezone'));
    $slug = parseURL();

    $title = 'Display SEP Mandiri';
    $title_header = 'Anjungan Cetak Surat Eligibilitas Peserta BPJS';
    if ($slug[5] == 'reg' ) {
      $title_header = 'Pendaftaran Poli Sore';
    }
    $logo  = $this->settings->get('settings.logo');
    $kode_ppk  = $this->settings->get('settings.ppk_bpjs');
    $nama_ppk  = $this->settings->get('settings.nama_instansi');

    $_username = $this->core->getUserInfo('fullname', null, true);
    $__username = $this->core->getUserInfo('username');
    if ($this->core->getUserInfo('username') !== '') {
      $__username = 'Tamu';
    }
    $tanggal       = getDayIndonesia(date('Y-m-d')) . ', ' . dateIndonesia(date('Y-m-d'));
    $username      = !empty($_username) ? $_username : $__username;

    $date = date('Y-m-d');

    $url = 'Rujukan/' . $slug[3];
    $url = $this->api_url . '' . $url;
    $output = BpjsService::get($url, NULL, $this->consid, $this->secretkey, $this->user_key, $tStamp);
    $json = json_decode($output, true);
    //var_dump($json);
    $asal_rujuk = '';
    if($json['metaData']['code'] == 201){
      $url = 'Rujukan/RS/' . $slug[3];

      $url = $this->api_url . '' . $url;
      $output = BpjsService::get($url, NULL, $this->consid, $this->secretkey, $this->user_key, $tStamp);
      $json = json_decode($output, true);
      $asal_rujuk = '2';
    }

    $code = $json['metaData']['code'];
    $message = $json['metaData']['message'];
    $stringDecrypt = stringDecrypt($key, $json['response']);
    $decompress = '""';
    //$rujukan = [];
    if ($code == "200") {
      $decompress = decompress($stringDecrypt);
      $rujukan = json_decode($decompress, true);
    }

    $reg_periksa = $this->db('reg_periksa')
      ->join('pasien', 'pasien.no_rkm_medis=reg_periksa.no_rkm_medis')
      ->join('poliklinik', 'poliklinik.kd_poli=reg_periksa.kd_poli')
      ->where('reg_periksa.tgl_registrasi', $date)
      ->where('reg_periksa.no_rkm_medis', $slug[4])
      ->oneArray();

    if (empty($reg_periksa)) {
      $pasien = $this->db('pasien')->where('no_rkm_medis', $slug[4])->oneArray();
      $stts_poli = ($slug[5] == 'reg') ? 'sore' : $slug[5] ;
      $poli = $this->db('maping_poli_bpjs')->join('poliklinik','maping_poli_bpjs.kd_poli_rs = poliklinik.kd_poli')->where('kd_poli_bpjs',$rujukan['rujukan']['poliRujukan']['kode'])->like('nm_poli','%'.$stts_poli)->oneArray();
      $tentukan_hari = date('D', strtotime(date('Y-m-d')));
      $day = array(
        'Sun' => 'AKHAD',
        'Mon' => 'SENIN',
        'Tue' => 'SELASA',
        'Wed' => 'RABU',
        'Thu' => 'KAMIS',
        'Fri' => 'JUMAT',
        'Sat' => 'SABTU'
      );
      $hari = $day[$tentukan_hari];
      $dokter = $this->db('jadwal')->join('maping_dokter_dpjpvclaim','maping_dokter_dpjpvclaim.kd_dokter = jadwal.kd_dokter')
      ->where('kd_poli',$poli['kd_poli_rs'])->where('hari_kerja',$hari)->oneArray();
      $reg_periksa = [
        'no_rkm_medis' => $pasien['no_rkm_medis'],
        'nm_pasien' => $pasien['nm_pasien'],
        'jk' => $pasien['jk'],
        'tgl_lahir' => $pasien['tgl_lahir'],
        'no_tlp' => $pasien['no_tlp'],
        'no_peserta' => $pasien['no_peserta'],
        'nm_poli' => $poli['nm_poli_bpjs'],
        'no_rawat' => '',
        'kd_poli_rs' => $poli['kd_poli_rs'],
        'stts_daftar_poli' => $slug[5]
      ];
      $dpjp = [
        'kd_dokter_bpjs' => $dokter['kd_dokter_bpjs'],
        'nm_dokter_bpjs' => $dokter['nm_dokter_bpjs']
      ];
    }else{
      $dpjp = $this->db('maping_dokter_dpjpvclaim')->where('kd_dokter', $reg_periksa['kd_dokter'])->oneArray();
    }
    $no_surat_kontrol_bpjs = "";
    $reg_periksa['asalRujuk'] = $asal_rujuk;
    $checkRujukanBaru = $this->db('bridging_sep')->where('no_rujukan',$slug[3])->desc('tglsep')->oneArray();
    if(!$checkRujukanBaru){
      $no_surat_kontrol_bpjs = "";
    } else {
      $date1 = new \DateTime($checkRujukanBaru['tglrujukan']);
      $date2 = new \DateTime(date('Y-m-d'));
      $interval = $date2->diff($date1);
      $interval = $interval->days;
      if($interval >= '89'){
        echo '<script type ="text/JavaScript">';
        echo 'alert("Masa Berlaku Rujukan Telah Habis")';
        echo '</script>';
      } else {
        $checkSurkon = $this->db('bridging_surat_kontrol_bpjs')->where('no_sep',$checkRujukanBaru['no_sep'])->oneArray();
        $checkSurkonUsed = $this->db('bridging_sep')->where('noskdp',$checkSurkon['no_surat'])->oneArray();
        if($checkSurkon && !$checkSurkonUsed){
          $no_surat_kontrol_bpjs = $checkSurkon['no_surat'];
        } else {
          $skdp_bpjs = $this->createKontrol($slug[3], $rujukan['rujukan']['poliRujukan']['kode'], $dpjp['kd_dokter_bpjs']);
          $no_surat_kontrol_bpjs = $skdp_bpjs;
        }
      }
    }

    $content = $this->draw('sep.mandiri.bikin.html', [
      'title' => $title,
      'title_header' => $title_header,
      'logo' => $logo,
      'powered' => 'Powered by <a href="https://basoro.org/">KhanzaLITE</a>',
      'username' => $username,
      'tanggal' => $tanggal,
      'running_text' => $this->settings->get('anjungan.text_anjungan'),
      'kode_ppk' => $kode_ppk,
      'nama_ppk' => $nama_ppk,
      'reg_periksa' => $reg_periksa,
      'skdp_bpjs' => $no_surat_kontrol_bpjs,
      'rujukan' => $rujukan,
      'dpjp' => $dpjp
    ]);

    $assign = [
      'title' => $this->settings->get('settings.nama_instansi'),
      'desc' => $this->settings->get('settings.alamat'),
      'content' => $content
    ];

    $this->setTemplate("canvas.html");

    $this->tpl->set('page', ['title' => $assign['title'], 'desc' => $assign['desc'], 'content' => $assign['content']]);
  }

  public function postSaveSEP()
  {
    date_default_timezone_set('UTC');
    $tStamp = strval(time() - strtotime("1970-01-01 00:00:00"));
    $key = $this->consid . $this->secretkey . $tStamp;

    $_POST['kdppkpelayanan'] = $this->settings->get('settings.ppk_bpjs');
    $_POST['nmppkpelayanan'] = $this->settings->get('settings.nama_instansi');
    $_POST['sep_user']  = 'SEP Mandiri';
    $dpjpKontrol = "";
    $dpjpKontrol = $this->db('bridging_surat_kontrol_bpjs')
      ->join('bridging_sep', 'bridging_sep.no_sep=bridging_surat_kontrol_bpjs.no_sep')
      ->where('bridging_surat_kontrol_bpjs.no_surat', $_POST['noskdp'])
      ->oneArray();


    if ($dpjpKontrol['kd_dokter_bpjs'] == "") {
      $dpjpKontrol['kddpjp'] = "";
    }

    $data = [
      'request' => [
        't_sep' => [
          'noKartu' => $_POST['no_kartu'],
          'tglSep' => $_POST['tglsep'],
          'ppkPelayanan' => $_POST['kdppkpelayanan'],
          'jnsPelayanan' => $_POST['jnspelayanan'],
          'klsRawat' => [
            'klsRawatHak' => $_POST['klsrawat'],
            'klsRawatNaik' => '',
            'pembiayaan' => '',
            'penanggungJawab' => ''
          ],
          'noMR' => $_POST['nomr'],
          'rujukan' => [
            'asalRujukan' => $_POST['asal_rujukan'],
            'tglRujukan' => $_POST['tglrujukan'],
            'noRujukan' => $_POST['norujukan'],
            'ppkRujukan' => $_POST['kdppkrujukan']
          ],
          'catatan' => $_POST['catatan'],
          'diagAwal' => $_POST['diagawal'],
          'poli' => [
            'tujuan' => $_POST['kdpolitujuan'],
            'eksekutif' => $_POST['eksekutif']
          ],
          'cob' => [
            'cob' => $_POST['cob']
          ],
          'katarak' => [
            'katarak' => $_POST['katarak']
          ],
          'jaminan' => [
            'lakaLantas' => $_POST['lakalantas'],
            'noLP' => '',
            'penjamin' => [
              'tglKejadian' => $_POST['tglkkl'],
              'keterangan' => $_POST['keterangankkl'],
              'suplesi' => [
                'suplesi' => $_POST['suplesi'],
                'noSepSuplesi' => $_POST['no_sep_suplesi'],
                'lokasiLaka' => [
                  'kdPropinsi' => $_POST['kdprop'],
                  'kdKabupaten' => $_POST['kdkab'],
                  'kdKecamatan' => $_POST['kdkec']
                ]
              ]
            ]
          ],
          'tujuanKunj' => $_POST['tujuanKunj'],
          'flagProcedure' => '',
          'kdPenunjang' => '',
          'assesmentPel' => $_POST['assesmentPel'],
          'skdp' => [
            'noSurat' => $_POST['noskdp'],
            'kodeDPJP' => $dpjpKontrol['kddpjp'],
          ],
          'dpjpLayan' => $_POST['kddpjp'],
          'noTelp' => $_POST['notelep'],
          'user' => $_POST['sep_user']
        ]
      ]
    ];

    $data = json_encode($data);

    //echo $data;
    $url = $this->api_url . 'SEP/2.0/insert';
    $output = BpjsService::post($url, $data, $this->consid, $this->secretkey, $this->user_key, $tStamp);
    $data = json_decode($output, true);
    if ($data == NULL) {

      echo 'Koneksi ke server BPJS terputus. Silahkan ulangi beberapa saat lagi!';
    } else if ($data['metaData']['code'] == 200) {

      $code = $data['metaData']['code'];
      $message = $data['metaData']['message'];

      $stringDecrypt = stringDecrypt($key, $data['response']);
      $decompress = '""';
      if (!empty($stringDecrypt)) {
        $decompress = decompress($stringDecrypt);
      }

      $data = '{
            "metaData": {
              "code": "' . $code . '",
              "message": "' . $message . '"
            },
            "response": ' . $decompress . '}';

      $data = json_decode($data, true);

      $_POST['sep_no_sep'] = $data['response']['sep']['noSep'];
      // $_POST['sep_no_sep'] = '1708UjiCoba';
      if($_POST['no_rawat'] == ''){
        date_default_timezone_set($this->settings->get('settings.timezone'));
        $poli = $this->db('maping_poli_bpjs')->join('poliklinik','maping_poli_bpjs.kd_poli_rs = poliklinik.kd_poli')->where('kd_poli_rs',$_POST['kdpolirs'])->oneArray();
        $dpjp = $this->db('maping_dokter_dpjpvclaim')->where('kd_dokter_bpjs', $_POST['kddpjp'])->oneArray();
        $cek_stts_daftar = $this->db('reg_periksa')->where('no_rkm_medis', $_POST['nomr'])->count();
        $_POST['stts_daftar'] = 'Baru';
        if($cek_stts_daftar > 0) {
          $_POST['stts_daftar'] = 'Lama';
        }

        $biaya_reg = $this->db('poliklinik')->where('kd_poli', $poli['kd_poli_rs'])->oneArray();
        $_POST['biaya_reg'] = $biaya_reg['registrasi'];
        if($_POST['stts_daftar'] == 'Lama') {
          $_POST['biaya_reg'] = $biaya_reg['registrasilama'];
        }

        $cek_status_poli = $this->db('reg_periksa')->where('no_rkm_medis', $_POST['nomr'])->where('kd_poli', $poli['kd_poli_rs'])->count();
        $_POST['status_poli'] = 'Baru';
        if($cek_status_poli > 0) {
          $_POST['status_poli'] = 'Lama';
        }

        $tanggal = new \DateTime($_POST['tanggal_lahir']);
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
        $_POST['no_rawat'] = $this->getMaxid();
        $this->db('reg_periksa')->save([
          'no_reg' => $this->getMaxAntrian($poli['kd_poli_rs'],$dpjp['kd_dokter']),
          'no_rawat' => $_POST['no_rawat'],
          'tgl_registrasi' => date('Y-m-d'),
          'jam_reg' => date('H:i:s'),
          'kd_dokter' => $dpjp['kd_dokter'],
          'no_rkm_medis' => $_POST['nomr'],
          'kd_poli' => $poli['kd_poli_rs'],
          'p_jawab' => 'SAUDARA',
          'almt_pj' => 'ALAMAT',
          'hubunganpj' => 'SAUDARA',
          'biaya_reg' => $_POST['biaya_reg'],
          'stts' => 'Belum',
          'stts_daftar' => $_POST['stts_daftar'],
          'status_lanjut' => 'Ralan',
          'kd_pj' => 'BPJ',
          'umurdaftar' => $umur,
          'sttsumur' => $sttsumur,
          'status_bayar' => 'Belum Bayar',
          'status_poli' => $_POST['status_poli']
        ]);
        $simpan_reg = $this->db('reg_periksa')->where('no_rawat',$_POST['no_rawat'])->oneArray();
        if($simpan_reg){
          $simpan_sep = $this->db('bridging_sep')->save([
            'no_sep' => $_POST['sep_no_sep'],
            'no_rawat' => $_POST['no_rawat'],
            'tglsep' => $_POST['tglsep'],
            'tglrujukan' => $_POST['tglrujukan'],
            'no_rujukan' => $_POST['norujukan'],
            'kdppkrujukan' => $_POST['kdppkrujukan'],
            'nmppkrujukan' => $_POST['nmppkrujukan'],
            'kdppkpelayanan' => $_POST['kdppkpelayanan'],
            'nmppkpelayanan' => $_POST['nmppkpelayanan'],
            'jnspelayanan' => $_POST['jnspelayanan'],
            'catatan' => $_POST['catatan'],
            'diagawal' => $_POST['diagawal'],
            'nmdiagnosaawal' => $_POST['nmdiagnosaawal'],
            'kdpolitujuan' => $_POST['kdpolitujuan'],
            'nmpolitujuan' => $_POST['nmpolitujuan'],
            'klsrawat' => $_POST['klsrawat'],
            // 'klsnaik' => '',
            // 'pembiayaan' => '',
            // 'pjnaikkelas' => '',
            'lakalantas' => $_POST['lakalantas'],
            'user' => $_POST['sep_user'],
            'nomr' => $_POST['nomr'],
            'nama_pasien' => $_POST['nama_pasien'],
            'tanggal_lahir' => $_POST['tanggal_lahir'],
            'peserta' => $_POST['peserta'],
            'jkel' => $_POST['jenis_kelamin'],
            'no_kartu' => $_POST['no_kartu'],
            'tglpulang' => $_POST['tglpulang'],
            'asal_rujukan' => $_POST['asal_rujukan'],
            'eksekutif' => $_POST['eksekutif'],
            'cob' => $_POST['cob'],
            'penjamin' => '',
            'notelep' => $_POST['notelep'],
            'katarak' => $_POST['katarak'],
            'tglkkl' => $_POST['tglkkl'],
            'keterangankkl' => $_POST['keterangankkl'],
            'suplesi' => $_POST['suplesi'],
            'no_sep_suplesi' => $_POST['no_sep_suplesi'],
            'kdprop' => $_POST['kdprop'],
            'nmprop' => $_POST['nmprop'],
            'kdkab' => $_POST['kdkab'],
            'nmkab' => $_POST['nmkab'],
            'kdkec' => $_POST['kdkec'],
            'nmkec' => $_POST['nmkec'],
            'noskdp' => $_POST['noskdp'],
            'kddpjp' => $_POST['kddpjp'],
            'nmdpdjp' => $_POST['nmdpdjp'],
            // 'tujuankunjungan' => $_POST['tujuanKunj'],
            // 'flagprosedur' => $_POST['flagProcedure'],
            // 'penunjang' => $_POST['kdPenunjang'],
            // 'asesmenpelayanan' => $_POST['assesmentPel'],
            // 'kddpjplayanan' => $_POST['kddpjp'],
            // 'nmdpjplayanan' => $_POST['nmdpdjp']
          ]);
        }
      }else{
        $simpan_sep = $this->db('bridging_sep')->save([
          'no_sep' => $_POST['sep_no_sep'],
          'no_rawat' => $_POST['no_rawat'],
          'tglsep' => $_POST['tglsep'],
          'tglrujukan' => $_POST['tglrujukan'],
          'no_rujukan' => $_POST['norujukan'],
          'kdppkrujukan' => $_POST['kdppkrujukan'],
          'nmppkrujukan' => $_POST['nmppkrujukan'],
          'kdppkpelayanan' => $_POST['kdppkpelayanan'],
          'nmppkpelayanan' => $_POST['nmppkpelayanan'],
          'jnspelayanan' => $_POST['jnspelayanan'],
          'catatan' => $_POST['catatan'],
          'diagawal' => $_POST['diagawal'],
          'nmdiagnosaawal' => $_POST['nmdiagnosaawal'],
          'kdpolitujuan' => $_POST['kdpolitujuan'],
          'nmpolitujuan' => $_POST['nmpolitujuan'],
          'klsrawat' => $_POST['klsrawat'],
          // 'klsnaik' => '',
          // 'pembiayaan' => '',
          // 'pjnaikkelas' => '',
          'lakalantas' => $_POST['lakalantas'],
          'user' => $_POST['sep_user'],
          'nomr' => $_POST['nomr'],
          'nama_pasien' => $_POST['nama_pasien'],
          'tanggal_lahir' => $_POST['tanggal_lahir'],
          'peserta' => $_POST['peserta'],
          'jkel' => $_POST['jenis_kelamin'],
          'no_kartu' => $_POST['no_kartu'],
          'tglpulang' => $_POST['tglpulang'],
          'asal_rujukan' => $_POST['asal_rujukan'],
          'eksekutif' => $_POST['eksekutif'],
          'cob' => $_POST['cob'],
          'penjamin' => '',
          'notelep' => $_POST['notelep'],
          'katarak' => $_POST['katarak'],
          'tglkkl' => $_POST['tglkkl'],
          'keterangankkl' => $_POST['keterangankkl'],
          'suplesi' => $_POST['suplesi'],
          'no_sep_suplesi' => $_POST['no_sep_suplesi'],
          'kdprop' => $_POST['kdprop'],
          'nmprop' => $_POST['nmprop'],
          'kdkab' => $_POST['kdkab'],
          'nmkab' => $_POST['nmkab'],
          'kdkec' => $_POST['kdkec'],
          'nmkec' => $_POST['nmkec'],
          'noskdp' => $_POST['noskdp'],
          'kddpjp' => $_POST['kddpjp'],
          'nmdpdjp' => $_POST['nmdpdjp'],
          // 'tujuankunjungan' => $_POST['tujuanKunj'],
          // 'flagprosedur' => $_POST['flagProcedure'],
          // 'penunjang' => $_POST['kdPenunjang'],
          // 'asesmenpelayanan' => $_POST['assesmentPel'],
          // 'kddpjplayanan' => $_POST['kddpjp'],
          // 'nmdpjplayanan' => $_POST['nmdpdjp']
        ]);
      }
      if ($simpan_sep) {
        if ($_POST['prolanis_prb'] !== '') {
          $simpan_prb = $this->db('bpjs_prb')->save([
            'no_sep' => $_POST['sep_no_sep'],
            'prb' => $_POST['prolanis_prb']
          ]);
        }
      } else {
        $simpan_sep = $this->db('bridging_sep_internal')->save([
          'no_sep' => $_POST['sep_no_sep'],
          'no_rawat' => $_POST['no_rawat'],
          'tglsep' => $_POST['tglsep'],
          'tglrujukan' => $_POST['tglrujukan'],
          'no_rujukan' => $_POST['norujukan'],
          'kdppkrujukan' => $_POST['kdppkrujukan'],
          'nmppkrujukan' => $_POST['nmppkrujukan'],
          'kdppkpelayanan' => $_POST['kdppkpelayanan'],
          'nmppkpelayanan' => $_POST['nmppkpelayanan'],
          'jnspelayanan' => $_POST['jnspelayanan'],
          'catatan' => $_POST['catatan'],
          'diagawal' => $_POST['diagawal'],
          'nmdiagnosaawal' => $_POST['nmdiagnosaawal'],
          'kdpolitujuan' => $_POST['kdpolitujuan'],
          'nmpolitujuan' => $_POST['nmpolitujuan'],
          'klsrawat' => $_POST['klsrawat'],
          'klsnaik' => $_POST['klsnaik'],
          'pembiayaan' => $_POST['pembiayaan'],
          'pjnaikkelas' => $_POST['pjnaikkelas'],
          'lakalantas' => $_POST['lakalantas'],
          'user' => $_POST['sep_user'],
          'nomr' => $_POST['nomr'],
          'nama_pasien' => $_POST['nama_pasien'],
          'tanggal_lahir' => $_POST['tanggal_lahir'],
          'peserta' => $_POST['peserta'],
          'jkel' => $_POST['jenis_kelamin'],
          'no_kartu' => $_POST['no_kartu'],
          'tglpulang' => $_POST['tglpulang'],
          'asal_rujukan' => $_POST['asal_rujukan'],
          'eksekutif' => $_POST['eksekutif'],
          'cob' => $_POST['cob'],
          'notelep' => $_POST['notelep'],
          'katarak' => $_POST['katarak'],
          'tglkkl' => $_POST['tglkkl'],
          'keterangankkl' => $_POST['keterangankkl'],
          'suplesi' => $_POST['suplesi'],
          'no_sep_suplesi' => $_POST['no_sep_suplesi'],
          'kdprop' => $_POST['kdprop'],
          'nmprop' => $_POST['nmprop'],
          'kdkab' => $_POST['kdkab'],
          'nmkab' => $_POST['nmkab'],
          'kdkec' => $_POST['kdkec'],
          'nmkec' => $_POST['nmkec'],
          'noskdp' => $_POST['noskdp'],
          'kddpjp' => $_POST['kddpjp'],
          'nmdpdjp' => $_POST['nmdpdjp'],
          'tujuankunjungan' => $_POST['tujuanKunj'],
          'flagprosedur' => $_POST['flagProcedure'],
          'penunjang' => $_POST['kdPenunjang'],
          'asesmenpelayanan' => $_POST['assesmentPel'],
          'kddpjplayanan' => $_POST['kddpjppelayanan'],
          'nmdpjplayanan' => $_POST['nmdpjppelayanan']
        ]);
      }
      $cek_sep = $this->db('bridging_sep')->where('no_sep', $_POST['sep_no_sep'])->oneArray();

      if ($cek_sep > 0) {
        echo $_POST['sep_no_sep'];
      } else {
        $cek_sep_internal = $this->db('bridging_sep_internal')->where('no_sep', $_POST['sep_no_sep'])->oneArray();
        if ($cek_sep_internal > 0) {
          echo $_POST['sep_no_sep'];
        } else {
          echo $data['metaData']['message'];
        }
      }
    } else {

      echo $data['metaData']['message'];
    }
    exit();
  }

  public function getCetakSEP()
  {
    $slug = parseURL();
    $no_sep = $slug[3];
    $settings = $this->settings('settings');
    $this->tpl->set('settings', $this->tpl->noParse_array(htmlspecialchars_array($settings)));
    $data_sep = $this->db('bridging_sep')->where('no_sep', $no_sep)->oneArray();
    if (!$data_sep) {
      $data_sep = $this->db('bridging_sep_internal')->where('no_sep', $no_sep)->oneArray();
    }
    $batas_rujukan = strtotime('+87 days', strtotime($data_sep['tglrujukan']));
    $_POST['no_rawat'] = '';
    $_POST['no_rawat'] = $_POST['no_rawat'];
    if($_POST['no_rawat'] == ''){
      $_POST['no_rawat'] = $data_sep['no_rawat'];
    }

    $qr = QRCode::getMinimumQRCode($data_sep['no_sep'], QR_ERROR_CORRECT_LEVEL_L);
    //$qr=QRCode::getMinimumQRCode('Petugas: '.$this->core->getUserInfo('fullname', null, true).'; Lokasi: '.UPLOADS.'/invoices/'.$result['kd_billing'].'.pdf',QR_ERROR_CORRECT_LEVEL_L);
    $im = $qr->createImage(4, 4);
    imagepng($im, BASE_DIR . '/tmp/qrcode.png');
    imagedestroy($im);

    $image = "/tmp/qrcode.png";

    $data_sep['qrCode'] = url($image);
    $data_sep['batas_rujukan'] = date('Y-m-d', $batas_rujukan);
    $potensi_prb = $this->db('bpjs_prb')->where('no_sep', $no_sep)->oneArray();
    $data_sep['potensi_prb'] = $potensi_prb['prb'];

    echo $this->draw('cetak.sep.html', ['data_sep' => $data_sep]);
    $mutasi_berkas = $this->db('mutasi_berkas')->where('no_rawat',$_POST['no_rawat'])->oneArray();
    if(!$mutasi_berkas){
      $this->db('mutasi_berkas')->save([
        'no_rawat' => $_POST['no_rawat'],
        'status' => 'Sudah Dikirim',
        'dikirim' => date('Y-m-d H:i:s'),
        'diterima' => '0000-00-00 00:00:00',
        'kembali' => '0000-00-00 00:00:00',
        'tidakada' => '0000-00-00 00:00:00',
        'ranap' => '0000-00-00 00:00:00'
      ]);
      $mutasi_dikirim = $this->db('mutasi_berkas')->where('no_rawat',$_POST['no_rawat'])->where('status','Sudah Dikirim')->oneArray();
      if ($mutasi_dikirim) {
        $this->db('mutasi_berkas')->where('no_rawat', $_POST['no_rawat'])->save([
          'status' => 'Sudah Diterima',
          'diterima' => $this->randMinutes($mutasi_dikirim['dikirim']),
        ]);
        $mutasi_diterima = $this->db('mutasi_berkas')->where('no_rawat',$_POST['no_rawat'])->where('status','Sudah Diterima')->oneArray();
        if ($mutasi_diterima) {
          $this->db('pemeriksaan_ralan')->save([
            'no_rawat' => $_POST['no_rawat'],
            'tgl_perawatan' => substr($this->randMinutes($mutasi_diterima['diterima']),0,10),
            'jam_rawat' => substr($this->randMinutes($mutasi_diterima['diterima']),11),
            'tensi' => '-',
            'kesadaran' => 'Compos Mentis',
            'rtl' => '-',
            'penilaian' => '-',
          ]);
          $pemeriksaan = $this->db('pemeriksaan_ralan')->where('no_rawat',$_POST['no_rawat'])->oneArray();
          if ($pemeriksaan) {
            $this->db('mlite_antrian_loket')->save([
              'type' => 'Loket',
              'noantrian' => '1',
              'no_rkm_medis' => $data_sep['nomr'],
              'postdate' => substr($this->randMinutes($mutasi_dikirim['dikirim']),0,10),
              'start_time' => substr($this->randMinutesAwal($mutasi_dikirim['dikirim']),11),
              'end_time' => substr($this->randMinutesAkhir($mutasi_dikirim['dikirim']),11),
              'status' => '1',
              'loket' => '1'
            ]);
          }
        }
      }
    }
    exit();
  }

  public function randMinutes($date1){
    $format = 'Y-m-d H:i:s';
    $date = \DateTime::createFromFormat($format, $date1);
    $time = $date->format('H:i:s');
    $date = $date->format('Y-m-d');
    list($h, $m, $s) = explode(":", $time);
    $seconds = $s + ($m * 60) + ($h * 3600);
    $min = 5 * 60;
    $max = 15 * 60;
    $seconds += rand($min, $max); //set desired min and max values

    // now back to time format
    $hours = floor($seconds / 3600);
    $mins = floor($seconds / 60 % 60);
    $secs = floor($seconds % 60);

    $timeFormat = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);
    $timeFormat = $date.' '.$timeFormat;
    return $timeFormat;
  }

  public function randMinutesAwal($date1){
    $format = 'Y-m-d H:i:s';
    $date = \DateTime::createFromFormat($format, $date1);
    $time = $date->format('H:i:s');
    $date = $date->format('Y-m-d');
    list($h, $m, $s) = explode(":", $time);
    $seconds = $s + ($m * 60) + ($h * 3600);
    $min = 3 * 60;
    $max = 7 * 60;
    $seconds -= rand($min, $max); //set desired min and max values

    // now back to time format
    $hours = floor($seconds / 3600);
    $mins = floor($seconds / 60 % 60);
    $secs = floor($seconds % 60);

    $timeFormat = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);
    $timeFormat = $date.' '.$timeFormat;
    return $timeFormat;
  }

  public function randMinutesAkhir($date1){
    $format = 'Y-m-d H:i:s';
    $date = \DateTime::createFromFormat($format, $date1);
    $time = $date->format('H:i:s');
    $date = $date->format('Y-m-d');
    list($h, $m, $s) = explode(":", $time);
    $seconds = $s + ($m * 60) + ($h * 3600);
    $min = 7 * 60;
    $max = 10 * 60;
    $seconds -= rand($min, $max); //set desired min and max values

    // now back to time format
    $hours = floor($seconds / 3600);
    $mins = floor($seconds / 60 % 60);
    $secs = floor($seconds % 60);

    $timeFormat = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);
    $timeFormat = $date.' '.$timeFormat;
    return $timeFormat;
  }

  public function postCheckFungsi(){
    $date = date('Y-m-d');
    $cari_rujukan = $this->db('bridging_sep')->where('no_rujukan',$_POST['rujukan'])->where('kdpolitujuan', $_POST['kd_poli'])->desc('tglsep')->oneArray();
    $dpjp = $this->db('maping_dokter_dpjpvclaim')->where('kd_dokter', $_POST['kd_dokter'])->oneArray();
    $nmPoli = $this->db('maping_poli_bpjs')->where('kd_poli_bpjs', $_POST['kd_poli'])->oneArray();

    date_default_timezone_set('UTC');
    $tStamp = strval(time() - strtotime('1970-01-01 00:00:00'));
    $key = $this->consid . $this->secretkey . $tStamp;
    $_POST['kdppkpelayanan'] = $this->settings->get('settings.ppk_bpjs');
    $_POST['nmppkpelayanan'] = $this->settings->get('settings.nama_instansi');
    $_POST['sep_user']  = 'SEP Mandiri';

    $data = [
      'request' => [
        'noSEP' => $cari_rujukan['no_sep'],
        'kodeDokter' => $_POST['kd_dokter'],
        'poliKontrol' => $_POST['kd_poli'],
        'tglRencanaKontrol' => $date,
        'user' => $_POST['sep_user']
      ]
    ];

    $data = json_encode($data);
    // echo $data;
    $url = $this->api_url . 'RencanaKontrol/insert';
    $output = BpjsService::post($url, $data, $this->consid, $this->secretkey, $this->user_key, $tStamp);
    $data = json_decode($output, true);

    //$noKontrol = $data['metaData']['message'];
    $noKontrol = '';
    if ($data == NULL) {

      echo 'Koneksi ke server BPJS terputus. Silahkan ulangi beberapa saat lagi!';
    } else if ($data['metaData']['code'] == 200) {
      $stringDecrypt = stringDecrypt($key, $data['response']);
      $decompress = '""';
      //$rujukan = [];
      $decompress = decompress($stringDecrypt);
      $rujukan = json_decode($decompress, true);
      //var_dump($rujukan);
      $noKontrol = $rujukan['noSuratKontrol'];

      $simpanKontrol = $this->db('bridging_surat_kontrol_bpjs')->save([
        'no_sep' => $cari_rujukan['no_sep'],
        'tgl_surat' => $cari_rujukan['tglsep'],
        'no_surat' => $noKontrol,
        'tgl_rencana' => $date,
        'kd_dokter_bpjs' => $_POST['kd_dokter'],
        'nm_dokter_bpjs' => $dpjp['nm_dokter_bpjs'],
        'kd_poli_bpjs' => $_POST['kd_poli'],
        'nm_poli_bpjs' => $nmPoli['nm_poli_bpjs']
      ]);
      if ($simpanKontrol) {
        $noKontrol = $noKontrol;
      }
    }
    echo $noKontrol;
    exit();
  }

  public function createKontrol($rujukan, $poli, $dokter)
  {
    $date = date('Y-m-d');
    $cari_rujukan = $this->db('bridging_sep')->where('no_rujukan', $rujukan)->where('kdpolitujuan', $poli)->desc('tglsep')->oneArray();
    $dpjp = $this->db('maping_dokter_dpjpvclaim')->where('kd_dokter_bpjs', $dokter)->oneArray();
    $nmPoli = $this->db('maping_poli_bpjs')->where('kd_poli_bpjs', $poli)->oneArray();

    date_default_timezone_set('UTC');
    $tStamp = strval(time() - strtotime('1970-01-01 00:00:00'));
    $key = $this->consid . $this->secretkey . $tStamp;
    $_POST['kdppkpelayanan'] = $this->settings->get('settings.ppk_bpjs');
    $_POST['nmppkpelayanan'] = $this->settings->get('settings.nama_instansi');
    $_POST['sep_user']  = 'SEP Mandiri';

    $data = [
      'request' => [
        'noSEP' => $cari_rujukan['no_sep'],
        'kodeDokter' => $dokter,
        'poliKontrol' => $poli,
        'tglRencanaKontrol' => $date,
        'user' => $_POST['sep_user']
      ]
    ];

    $data = json_encode($data);
    // echo $data;
    $url = $this->api_url . 'RencanaKontrol/insert';
    $output = BpjsService::post($url, $data, $this->consid, $this->secretkey, $this->user_key, $tStamp);
    $data = json_decode($output, true);

    //$noKontrol = $data['metaData']['message'];
    $noKontrol = '';
    if ($data == NULL) {

      echo 'Koneksi ke server BPJS terputus. Silahkan ulangi beberapa saat lagi!';
    } else if ($data['metaData']['code'] == 200) {
      $stringDecrypt = stringDecrypt($key, $data['response']);
      $decompress = '""';
      //$rujukan = [];
      $decompress = decompress($stringDecrypt);
      $rujukan = json_decode($decompress, true);
      //var_dump($rujukan);
      $noKontrol = $rujukan['noSuratKontrol'];

      $this->db('bridging_surat_kontrol_bpjs')->save([
        'no_sep' => $cari_rujukan['no_sep'],
        'tgl_surat' => $cari_rujukan['tglsep'],
        'no_surat' => $noKontrol,
        'tgl_rencana' => $date,
        'kd_dokter_bpjs' => $dokter,
        'nm_dokter_bpjs' => $dpjp['nm_dokter_bpjs'],
        'kd_poli_bpjs' => $poli,
        'nm_poli_bpjs' => $nmPoli['nm_poli_bpjs']
      ]);
      $simpanKontrol = $this->db('bridging_surat_kontrol_bpjs')->where('no_surat',$noKontrol)->oneArray();
      if ($simpanKontrol) {
        $noKontrol = $noKontrol;
      }
    }
    return $noKontrol;
    //exit();
  }

  public function getDaftarMandiriCek()
  {
    session_start();
    $date = date('Y-m-d');
    $status = 'cetak';
    if (isset($_POST['cekrm']) && isset($_POST['no_rkm_medis']) && $_POST['no_rkm_medis'] != '') {
      $pasien = $this->db('pasien')->where('no_rkm_medis', $_POST['no_rkm_medis'])->oneArray();
      $reg = $this->db('reg_periksa')->where('no_rkm_medis',$_POST['no_rkm_medis'])->where('tgl_registrasi',$date)->oneArray();

      if (date('H:i:s') <= '14:00:00' && $_POST['status'] == 'sore') {
        $status = 'reg';
      } else if (date('H:i:s') >= '14:00:00' && $_POST['status'] == 'pagi'){
        $status = 'palsu';
      }

      if($reg){
        $_SESSION['message'] = "Anda Sudah Terdaftar Untuk Hari Ini. Silahkan Menggunakan Menu Cetak SEP";
        redirect(url('anjungan/sep'));
      }else if(!$pasien){
        $_SESSION['message'] = "Anda Belum Terdaftar Dirumah Sakit ini. Silahkan Menggunakan Menu Pendaftaran Baru";
        redirect(url('anjungan/sep'));
      }else{
        if ($status == 'reg') {
          redirect(url('anjungan/sep/' . $pasien['no_peserta'] . '/' . $_POST['no_rkm_medis'] . '/' . $status));
        } else if($status == 'palsu'){
          $_SESSION['message'] = "Anda Tidak Bisa Mendaftar Ke Poli Pagi Hari Ini. Silahkan Daftar Ke Poli Sore Jika Ingin Periksa Hari Ini . Atau Datang Lagi Besok Jika Ingin Periksa Ke Poli Pagi";
          redirect(url('anjungan/sep'));
        } else {
          redirect(url('anjungan/sep/' . $pasien['no_peserta'] . '/' . $_POST['no_rkm_medis'] . '/' . $_POST['status']));
        }
      }
    } else {
      redirect(url('anjungan/sep'));
    }
    exit();
  }

  public function postRegDaftar()
  {
    $slug = parseURL();
    date_default_timezone_set($this->settings->get('settings.timezone'));
    $poli = $this->db('maping_poli_bpjs')->join('poliklinik','maping_poli_bpjs.kd_poli_rs = poliklinik.kd_poli')->where('kd_poli_rs',$_POST['kdpolirs'])->oneArray();
    $dpjp = $this->db('maping_dokter_dpjpvclaim')->where('kd_dokter_bpjs', $_POST['kddpjp'])->oneArray();
    $cek_stts_daftar = $this->db('reg_periksa')->where('no_rkm_medis', $_POST['nomr'])->count();
    $_POST['stts_daftar'] = 'Baru';
    if($cek_stts_daftar > 0) {
      $_POST['stts_daftar'] = 'Lama';
    }

    $biaya_reg = $this->db('poliklinik')->where('kd_poli', $poli['kd_poli_rs'])->oneArray();
    $_POST['biaya_reg'] = $biaya_reg['registrasi'];
    if($_POST['stts_daftar'] == 'Lama') {
      $_POST['biaya_reg'] = $biaya_reg['registrasilama'];
    }

    $cek_status_poli = $this->db('reg_periksa')->where('no_rkm_medis', $_POST['nomr'])->where('kd_poli', $poli['kd_poli_rs'])->count();
    $_POST['status_poli'] = 'Baru';
    if($cek_status_poli > 0) {
      $_POST['status_poli'] = 'Lama';
    }

    $tanggal = new \DateTime($_POST['tanggal_lahir']);
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
    $_POST['no_rawat'] = $this->getMaxid();
    $this->db('reg_periksa')->save([
      'no_reg' => $this->getMaxAntrian($poli['kd_poli_rs'],$dpjp['kd_dokter']),
      'no_rawat' => $_POST['no_rawat'],
      'tgl_registrasi' => date('Y-m-d'),
      'jam_reg' => date('H:i:s'),
      'kd_dokter' => $dpjp['kd_dokter'],
      'no_rkm_medis' => $_POST['nomr'],
      'kd_poli' => $poli['kd_poli_rs'],
      'p_jawab' => 'SAUDARA',
      'almt_pj' => 'ALAMAT',
      'hubunganpj' => 'SAUDARA',
      'biaya_reg' => $_POST['biaya_reg'],
      'stts' => 'Belum',
      'stts_daftar' => $_POST['stts_daftar'],
      'status_lanjut' => 'Ralan',
      'kd_pj' => 'BPJ',
      'umurdaftar' => $umur,
      'sttsumur' => $sttsumur,
      'status_bayar' => 'Belum Bayar',
      'status_poli' => $_POST['status_poli']
    ]);
    $cek_regist = $this->db('reg_periksa')->where('no_rawat',$_POST['no_rawat'])->where('no_rkm_medis',$_POST['nomr'])->oneArray();
    if ($cek_regist) {
      echo '200';
    } else {
      echo '201';
    }
    exit();
  }

  public function getBaruCek()
  {
    session_start();
    $date = date('Y-m-d');
    if (isset($_POST['ceknik']) && isset($_POST['nik']) && $_POST['nik'] != '') {
      $pasien = $this->db('pasien')->where('no_ktp', $_POST['nik'])->oneArray();
      $reg = $this->db('reg_periksa')->where('no_rkm_medis',$pasien['no_rkm_medis'])->where('tgl_registrasi',$date)->oneArray();
      if($reg){
        $_SESSION['message'] = "Anda Sudah Terdaftar Untuk Hari Ini. Silahkan Menggunakan Menu Cetak SEP";
        redirect(url('anjungan/sep'));
      }else if($pasien){
        $_SESSION['message'] = "Anda Sudah Terdaftar Dirumah Sakit Dengan No Rekam Medis : ".$pasien['no_rkm_medis'].". Silahkan Menggunakan Menu Pendaftaran";
        redirect(url('anjungan/sep'));
      }else{
        $_SESSION['kode'] = $_POST['nik'];
        redirect(url('anjungan/baru/' . $_POST['nik']));
      }
    } else {
      redirect(url('anjungan/sep'));
    }
    exit();
  }

  public function getBaruData()
  {
    date_default_timezone_set('UTC');
    $tStamp = strval(time() - strtotime("1970-01-01 00:00:00"));
    $key = $this->consid . $this->secretkey . $tStamp;

    date_default_timezone_set($this->settings->get('settings.timezone'));
    $slug = parseURL();

    $date = date('Y-m-d');

    $url = 'Peserta/nik/' . $slug[2] .'/tglSEP/'.$date;

    $url = $this->api_url . '' . $url;
    $output = BpjsService::get($url, NULL, $this->consid, $this->secretkey, $this->user_key, $tStamp);
    $json = json_decode($output, true);
    //var_dump($json);
    $code = $json['metaData']['code'];
    $message = $json['metaData']['message'];
    $stringDecrypt = stringDecrypt($key, $json['response']);
    $decompress = '""';
    //$rujukan = [];
    if ($code == "200") {
      $decompress = decompress($stringDecrypt);
      $peserta = json_decode($decompress, true);
    }
    date_default_timezone_set('UTC');
    $tStamp = strval(time() - strtotime("1970-01-01 00:00:00"));
    $key = $this->consid . $this->secretkey . $tStamp;

    $url = "Rujukan/List/Peserta/" . $peserta['peserta']['noKartu'];
    $url = $this->api_url . '' . $url;
    $output = BpjsService::get($url, NULL, $this->consid, $this->secretkey, $this->user_key, $tStamp);
    $json = json_decode($output, true);


    if($json['metaData']['code'] == 201){
      $url = "Rujukan/RS/List/Peserta/" . $peserta['peserta']['noKartu'];

      $url = $this->api_url . '' . $url;
      $output = BpjsService::get($url, NULL, $this->consid, $this->secretkey, $this->user_key, $tStamp);
      $json = json_decode($output, true);
    }

    $code = $json['metaData']['code'];
    $message = $json['metaData']['message'];
    // var_dump($json);
    $stringDecrypt = stringDecrypt($key, $json['response']);
    $decompress = '""';
    //$rujukan = [];
    if (!empty($json)) :
      if ($code != "200") {
        $sep_response = $message;
      } else {
        if (!empty($stringDecrypt)) {
          $decompress = decompress($stringDecrypt);
          $sep_response = json_decode($decompress, true);
        } else {
          $sep_response = "Sambungan ke server BPJS sedang ada gangguan. Silahkan ulangi lagi dengan menekan tombol REFRESH";
        }
      }
    else :
      $sep_response = "Sambungan ke server BPJS sedang ada gangguan. Silahkan ulangi lagi dengan menekan tombol REFRESH";
    endif;

    $title = 'Display SEP Mandiri';
    $logo  = $this->settings->get('settings.logo');

    $_username = $this->core->getUserInfo('fullname', null, true);
    $__username = $this->core->getUserInfo('username');
    if ($this->core->getUserInfo('username') !== '') {
      $__username = 'Tamu';
    }
    $tanggal       = getDayIndonesia(date('Y-m-d')) . ', ' . dateIndonesia(date('Y-m-d'));
    $username      = !empty($_username) ? $_username : $__username;

    $content = $this->draw('sep.mandiri.noka.norm.html', [
      'title' => $title,
      'logo' => $logo,
      'powered' => 'Powered by <a href="https://basoro.org/">KhanzaLITE</a>',
      'username' => $username,
      'tanggal' => $tanggal,
      'running_text' => $this->settings->get('anjungan.text_anjungan'),
      'no_rkm_medis' => '',
      'sep_response' => $sep_response
    ]);

    $assign = [
      'title' => $this->settings->get('settings.nama_instansi'),
      'desc' => $this->settings->get('settings.alamat'),
      'content' => $content
    ];

    $this->setTemplate("canvas.html");

    $this->tpl->set('page', ['title' => $assign['title'], 'desc' => $assign['desc'], 'content' => $assign['content']]);
  }

  public function getBaruBikin()
  {
    date_default_timezone_set('UTC');
    $tStamp = strval(time() - strtotime("1970-01-01 00:00:00"));
    $key = $this->consid . $this->secretkey . $tStamp;

    date_default_timezone_set($this->settings->get('settings.timezone'));
    $slug = parseURL();

    $title = 'Display SEP Mandiri';
    $logo  = $this->settings->get('settings.logo');
    $kode_ppk  = $this->settings->get('settings.ppk_bpjs');
    $nama_ppk  = $this->settings->get('settings.nama_instansi');

    $_username = $this->core->getUserInfo('fullname', null, true);
    $__username = $this->core->getUserInfo('username');
    if ($this->core->getUserInfo('username') !== '') {
      $__username = 'Tamu';
    }
    $tanggal       = getDayIndonesia(date('Y-m-d')) . ', ' . dateIndonesia(date('Y-m-d'));
    $username      = !empty($_username) ? $_username : $__username;

    $date = date('Y-m-d');

    $url = 'Rujukan/' . $slug[3];

    $url = $this->api_url . '' . $url;
    $output = BpjsService::get($url, NULL, $this->consid, $this->secretkey, $this->user_key, $tStamp);
    $json = json_decode($output, true);
    $asal_rujuk = '';
    //var_dump($json);
    if($json['metaData']['code'] == 201){
      $url = 'Rujukan/RS/' . $slug[3];

      $url = $this->api_url . '' . $url;
      $output = BpjsService::get($url, NULL, $this->consid, $this->secretkey, $this->user_key, $tStamp);
      $json = json_decode($output, true);
      $asal_rujuk = '2';
    }

    $code = $json['metaData']['code'];
    $message = $json['metaData']['message'];
    $stringDecrypt = stringDecrypt($key, $json['response']);
    $decompress = '""';
    //$rujukan = [];
    if ($code == "200") {
      $decompress = decompress($stringDecrypt);
      $rujukan = json_decode($decompress, true);
    }

      $poli = $this->db('maping_poli_bpjs')->where('kd_poli_bpjs',$rujukan['rujukan']['poliRujukan']['kode'])->oneArray();
      $tentukan_hari = date('D', strtotime(date('Y-m-d')));
      $day = array(
        'Sun' => 'AKHAD',
        'Mon' => 'SENIN',
        'Tue' => 'SELASA',
        'Wed' => 'RABU',
        'Thu' => 'KAMIS',
        'Fri' => 'JUMAT',
        'Sat' => 'SABTU'
      );
      $hari = $day[$tentukan_hari];
      $dokter = $this->db('jadwal')->join('maping_dokter_dpjpvclaim','maping_dokter_dpjpvclaim.kd_dokter = jadwal.kd_dokter')->where('kd_poli',$poli['kd_poli_rs'])->where('hari_kerja',$hari)->oneArray();
      $reg_periksa = [
        'no_peserta' => '',
        'nm_pasien' => '',
        'no_rkm_medis' => '',
        'jk' => '',
        'tgl_lahir' => '',
        'no_tlp' => '',
        'no_rawat' => '',
        'nm_poli' => $poli['nm_poli_bpjs'],
        'asalRujuk' => $asal_rujuk
      ];

      $dpjp = [
        'kd_dokter_bpjs' => $dokter['kd_dokter_bpjs'],
        'nm_dokter_bpjs' => $dokter['nm_dokter_bpjs']
      ];

    $content = $this->draw('sep.mandiri.bikin.html', [
      'title' => $title,
      'logo' => $logo,
      'powered' => 'Powered by <a href="https://basoro.org/">KhanzaLITE</a>',
      'username' => $username,
      'tanggal' => $tanggal,
      'running_text' => $this->settings->get('anjungan.text_anjungan'),
      'kode_ppk' => $kode_ppk,
      'nama_ppk' => $nama_ppk,
      'reg_periksa' => $reg_periksa,
      'skdp_bpjs' => '',
      'rujukan' => $rujukan,
      'dpjp' => $dpjp
    ]);

    $assign = [
      'title' => $this->settings->get('settings.nama_instansi'),
      'desc' => $this->settings->get('settings.alamat'),
      'content' => $content
    ];

    $this->setTemplate("canvas.html");

    $this->tpl->set('page', ['title' => $assign['title'], 'desc' => $assign['desc'], 'content' => $assign['content']]);
  }

  public function postSaveSepBaru()
  {
    date_default_timezone_set('UTC');
    $tStamp = strval(time() - strtotime("1970-01-01 00:00:00"));
    $key = $this->consid . $this->secretkey . $tStamp;

    $_POST['kdppkpelayanan'] = $this->settings->get('settings.ppk_bpjs');
    $_POST['nmppkpelayanan'] = $this->settings->get('settings.nama_instansi');
    $_POST['sep_user']  = 'SEP Mandiri';
    $dpjpKontrol = "";
    $dpjpKontrol = $this->db('bridging_surat_kontrol_bpjs')
      ->join('bridging_sep', 'bridging_sep.no_sep=bridging_surat_kontrol_bpjs.no_sep')
      ->where('bridging_surat_kontrol_bpjs.no_surat', $_POST['noskdp'])
      ->oneArray();


    if ($dpjpKontrol['kddpjp'] == "") {
      $dpjpKontrol['kddpjp'] = $_POST['kddpjp'];
    }
    $nik = "";
    if (isset($_SESSION['kode'])) {
      $nik = $_SESSION['kode'];
      unset($_SESSION['kode']);
    }
    $_POST['nomr'] = $this->core->setNoRM();
    $_POST['no_rawat'] = $this->getMaxid();
    $umurPasien = $this->hitungUmur($_POST['tanggal_lahir']);
    date_default_timezone_set($this->settings->get('settings.timezone'));
    $simpanPasien = $this->db('pasien')->save([
      'no_rkm_medis' => $_POST['nomr'],
      'nm_pasien' => $_POST['nama_pasien'],
      'no_ktp' => $nik,
      'jk' => $_POST['jenis_kelamin'],
      'tmp_lahir' => '-',
      'tgl_lahir' => $_POST['tanggal_lahir'],
      'nm_ibu' => '-',
      'alamat' => '-',
      'gol_darah' => '-',
      'pekerjaan' => '-',
      'stts_nikah' => 'BELUM MENIKAH',
      'agama' => 'ISLAM',
      'tgl_daftar' => date('Y-m-d'),
      'no_tlp' => '000000000000',
      'umur' => $umurPasien,
      'pnd' => '-',
      'keluarga' => 'SAUDARA',
      'namakeluarga' => '',
      'kd_pj' => 'BPJ',
      'no_peserta' => $_POST['no_kartu'],
      'kd_kel' => '1',
      'kd_kec' => '1',
      'kd_kab' => '1',
      'pekerjaanpj' => '',
      'alamatpj' => 'ALAMAT',
      'kelurahanpj' => 'KELURAHAN',
      'kecamatanpj' => 'KECAMATAN',
      'kabupatenpj' => 'KABUPATEN',
      'perusahaan_pasien' => '-',
      'suku_bangsa' => '1',
      'bahasa_pasien' => '1',
      'cacat_fisik' => '1',
      'email' => '',
      'nip' => '',
      'kd_prop' => '1',
      'propinsipj' => 'PROPINSI'
    ]);

    $data = [
      'request' => [
        't_sep' => [
          'noKartu' => $_POST['no_kartu'],
          'tglSep' => $_POST['tglsep'],
          'ppkPelayanan' => $_POST['kdppkpelayanan'],
          'jnsPelayanan' => $_POST['jnspelayanan'],
          'klsRawat' => [
            'klsRawatHak' => $_POST['klsrawat'],
            'klsRawatNaik' => '',
            'pembiayaan' => '',
            'penanggungJawab' => ''
          ],
          'noMR' => $_POST['nomr'],
          'rujukan' => [
            'asalRujukan' => $_POST['asal_rujukan'],
            'tglRujukan' => $_POST['tglrujukan'],
            'noRujukan' => $_POST['norujukan'],
            'ppkRujukan' => $_POST['kdppkrujukan']
          ],
          'catatan' => $_POST['catatan'],
          'diagAwal' => $_POST['diagawal'],
          'poli' => [
            'tujuan' => $_POST['kdpolitujuan'],
            'eksekutif' => $_POST['eksekutif']
          ],
          'cob' => [
            'cob' => $_POST['cob']
          ],
          'katarak' => [
            'katarak' => $_POST['katarak']
          ],
          'jaminan' => [
            'lakaLantas' => $_POST['lakalantas'],
            'noLP' => '',
            'penjamin' => [
              'tglKejadian' => $_POST['tglkkl'],
              'keterangan' => $_POST['keterangankkl'],
              'suplesi' => [
                'suplesi' => $_POST['suplesi'],
                'noSepSuplesi' => $_POST['no_sep_suplesi'],
                'lokasiLaka' => [
                  'kdPropinsi' => $_POST['kdprop'],
                  'kdKabupaten' => $_POST['kdkab'],
                  'kdKecamatan' => $_POST['kdkec']
                ]
              ]
            ]
          ],
          'tujuanKunj' => $_POST['tujuanKunj'],
          'flagProcedure' => $_POST['flagProcedure'],
          'kdPenunjang' => $_POST['kdPenunjang'],
          'assesmentPel' => $_POST['assesmentPel'],
          'skdp' => [
            'noSurat' => $_POST['noskdp'],
            'kodeDPJP' => $dpjpKontrol['kddpjp']
          ],
          'dpjpLayan' => $_POST['kddpjp'],
          'noTelp' => $_POST['notelep'],
          'user' => $_POST['sep_user']
        ]
      ]
    ];

    $data = json_encode($data);
    $cariPasien = $this->db('pasien')->where('nm_pasien',$_POST['nama_pasien'])->where('no_rkm_medis',$_POST['nomr'])->oneArray();
    // echo $data;
    if($cariPasien){
    $url = $this->api_url . 'SEP/2.0/insert';
    $output = BpjsService::post($url, $data, $this->consid, $this->secretkey, $this->user_key, $tStamp);
    $data = json_decode($output, true);

      if ($data == NULL) {

        echo 'Koneksi ke server BPJS terputus. Silahkan ulangi beberapa saat lagi!';
      } else if ($data['metaData']['code'] == 200) {

        $code = $data['metaData']['code'];
        $message = $data['metaData']['message'];

        $stringDecrypt = stringDecrypt($key, $data['response']);
        $decompress = '""';
        if (!empty($stringDecrypt)) {
          $decompress = decompress($stringDecrypt);
        }

        $data = '{
              "metaData": {
                "code": "' . $code . '",
                "message": "' . $message . '"
              },
              "response": ' . $decompress . '}';

        $data = json_decode($data, true);

        $_POST['sep_no_sep'] = $data['response']['sep']['noSep'];

        $poli = $this->db('maping_poli_bpjs')->where('kd_poli_bpjs',$_POST['kdpolitujuan'])->oneArray();
        $dpjp = $this->db('maping_dokter_dpjpvclaim')->where('kd_dokter_bpjs', $_POST['kddpjp'])->oneArray();

        $biaya_reg = $this->db('poliklinik')->where('kd_poli', $poli['kd_poli_rs'])->oneArray();
        $_POST['biaya_reg'] = $biaya_reg['registrasi'];
        $_POST['stts_daftar'] = 'Baru';
        $_POST['status_poli'] = 'Baru';

        date_default_timezone_set($this->settings->get('settings.timezone'));
        $tanggal = new \DateTime($_POST['tanggal_lahir']);
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

        $simpan_reg = $this->db('reg_periksa')->save([
          'no_reg' => $this->getMaxAntrian($poli['kd_poli_rs'],$dpjp['kd_dokter']),
          'no_rawat' => $_POST['no_rawat'],
          'tgl_registrasi' => date('Y-m-d'),
          'jam_reg' => date('H:i:s'),
          'kd_dokter' => $dpjp['kd_dokter'],
          'no_rkm_medis' => $_POST['nomr'],
          'kd_poli' => $poli['kd_poli_rs'],
          'p_jawab' => 'SAUDARA',
          'almt_pj' => 'ALAMAT',
          'hubunganpj' => 'SAUDARA',
          'biaya_reg' => $_POST['biaya_reg'],
          'stts' => 'Belum',
          'stts_daftar' => $_POST['stts_daftar'],
          'status_lanjut' => 'Ralan',
          'kd_pj' => 'BPJ',
          'umurdaftar' => $umur,
          'sttsumur' => $sttsumur,
          'status_bayar' => 'Belum Bayar',
          'status_poli' => $_POST['status_poli']
        ]);
        if($simpan_reg){
          $simpan_sep = $this->db('bridging_sep')->save([
            'no_sep' => $_POST['sep_no_sep'],
            'no_rawat' => $_POST['no_rawat'],
            'tglsep' => $_POST['tglsep'],
            'tglrujukan' => $_POST['tglrujukan'],
            'no_rujukan' => $_POST['norujukan'],
            'kdppkrujukan' => $_POST['kdppkrujukan'],
            'nmppkrujukan' => $_POST['nmppkrujukan'],
            'kdppkpelayanan' => $_POST['kdppkpelayanan'],
            'nmppkpelayanan' => $_POST['nmppkpelayanan'],
            'jnspelayanan' => $_POST['jnspelayanan'],
            'catatan' => $_POST['catatan'],
            'diagawal' => $_POST['diagawal'],
            'nmdiagnosaawal' => $_POST['nmdiagnosaawal'],
            'kdpolitujuan' => $_POST['kdpolitujuan'],
            'nmpolitujuan' => $_POST['nmpolitujuan'],
            'klsrawat' => $_POST['klsrawat'],
            // 'klsnaik' => '',
            // 'pembiayaan' => '',
            // 'pjnaikkelas' => '',
            'lakalantas' => $_POST['lakalantas'],
            'user' => $_POST['sep_user'],
            'nomr' => $_POST['nomr'],
            'nama_pasien' => $_POST['nama_pasien'],
            'tanggal_lahir' => $_POST['tanggal_lahir'],
            'peserta' => $_POST['peserta'],
            'jkel' => $_POST['jenis_kelamin'],
            'no_kartu' => $_POST['no_kartu'],
            'tglpulang' => $_POST['tglpulang'],
            'asal_rujukan' => $_POST['asal_rujukan'],
            'eksekutif' => $_POST['eksekutif'],
            'cob' => $_POST['cob'],
            'penjamin' => '',
            'notelep' => $_POST['notelep'],
            'katarak' => $_POST['katarak'],
            'tglkkl' => $_POST['tglkkl'],
            'keterangankkl' => $_POST['keterangankkl'],
            'suplesi' => $_POST['suplesi'],
            'no_sep_suplesi' => $_POST['no_sep_suplesi'],
            'kdprop' => $_POST['kdprop'],
            'nmprop' => $_POST['nmprop'],
            'kdkab' => $_POST['kdkab'],
            'nmkab' => $_POST['nmkab'],
            'kdkec' => $_POST['kdkec'],
            'nmkec' => $_POST['nmkec'],
            'noskdp' => $_POST['noskdp'],
            'kddpjp' => $_POST['kddpjp'],
            'nmdpdjp' => $_POST['nmdpdjp'],
            // 'tujuankunjungan' => $_POST['tujuanKunj'],
            // 'flagprosedur' => $_POST['flagProcedure'],
            // 'penunjang' => $_POST['kdPenunjang'],
            // 'asesmenpelayanan' => $_POST['assesmentPel'],
            // 'kddpjplayanan' => $_POST['kddpjp'],
            // 'nmdpjplayanan' => $_POST['nmdpdjp']
          ]);
        }
        if ($simpan_sep) {
          if ($_POST['prolanis_prb'] !== '') {
            $simpan_prb = $this->db('bpjs_prb')->save([
              'no_sep' => $_POST['sep_no_sep'],
              'prb' => $_POST['prolanis_prb']
            ]);
          }
        }
        $cek_sep = $this->db('bridging_sep')->where('no_sep', $_POST['sep_no_sep'])->oneArray();

        if ($cek_sep > 0) {
          echo $_POST['sep_no_sep'];
        } else {
          $cek_sep_internal = $this->db('bridging_sep_internal')->where('no_sep', $_POST['sep_no_sep'])->oneArray();
          if ($cek_sep_internal > 0) {
            echo $_POST['sep_no_sep'];
          } else {
            echo $data['metaData']['message'];
          }
        }
      } else {
        echo $data['metaData']['message'];
      }
    }else{
      echo "Gagal Mendaftarkan";
    }
    exit();
  }

  public function getMaxid()
  {
    date_default_timezone_set($this->settings->get('settings.timezone'));
    $max_id = $this->db('reg_periksa')->select(['no_rawat' => 'ifnull(MAX(CONVERT(RIGHT(no_rawat,6),signed)),0)'])->where('tgl_registrasi', date('Y-m-d'))->oneArray();
    if(empty($max_id['no_rawat'])) {
      $max_id['no_rawat'] = '000000';
    }
    $_next_no_rawat = sprintf('%06s', ($max_id['no_rawat'] + 1));
    $next_no_rawat = date('Y/m/d').'/'.$_next_no_rawat;
    return $next_no_rawat;
  }

  public function getMaxAntrian($poli,$dokter)
  {
    date_default_timezone_set($this->settings->get('settings.timezone'));
    $max_id = $this->db('reg_periksa')->select(['no_reg' => 'ifnull(MAX(CONVERT(RIGHT(no_reg,3),signed)),0)'])->where('kd_poli', $poli)->where('tgl_registrasi', date('Y-m-d'))->desc('no_reg')->limit(1)->oneArray();
    if($this->settings->get('settings.dokter_ralan_per_dokter') == 'true') {
      $max_id = $this->db('reg_periksa')->select(['no_reg' => 'ifnull(MAX(CONVERT(RIGHT(no_reg,3),signed)),0)'])->where('kd_poli', $poli)->where('kd_dokter', $dokter)->where('tgl_registrasi', date('Y-m-d'))->desc('no_reg')->limit(1)->oneArray();
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

    $jadwal_dokter = $this->db('jadwal')->where('kd_poli', $poli)->where('kd_dokter', $dokter)->where('hari_kerja', $hari)->oneArray();
    $jadwal_poli = $this->db('jadwal')->where('kd_poli', $poli)->where('hari_kerja', $hari)->toArray();
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
    return $_next_no_reg;
  }

  public function hitungUmur($tanggal_lahir)
  {
      $birthDate = new \DateTime($tanggal_lahir);
      $today = new \DateTime("today");
      $umur = "0 Th 0 Bl 0 Hr";
      if ($birthDate < $today) {
        $y = $today->diff($birthDate)->y;
        $m = $today->diff($birthDate)->m;
        $d = $today->diff($birthDate)->d;
        $umur =  $y." Th ".$m." Bl ".$d." Hr";
      }
      return $umur;
  }

  public function getResetAnjungan(){
    date_default_timezone_set($this->settings->get('settings.timezone'));
    $date = date('Y-m-d');
    $checkAnjungan = $this->db('mlite_antrian_loket')->where('postdate',$date)->oneArray();
    if($checkAnjungan){
      $this->db('mlite_antrian_loket')->where('postdate',$date)->delete();
      echo 'Berhasil Reset Antrian Anjungan<br>';
      $checkAnjungan = $this->db('mlite_antrian_loket')->where('postdate',$date)->oneArray();
      if(!$checkAnjungan){
        $this->db('mlite_antrian_loket')->save([
          'type' => 'Loket',
          'noantrian' => '1',
          'postdate' => $date,
          'start_time' => date('H:i:s')
        ]);
      }
      echo 'Berhasil Memperbarui Antrian Anjungan';
    }
  }
}
