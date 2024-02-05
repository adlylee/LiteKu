<?php

namespace Plugins\Vedika;

use Systems\SiteModule;

class Site extends SiteModule
{

    public function init()
    {
        $this->mlite['notify']         = $this->core->getNotify();
        $this->mlite['logo']           = $this->settings->get('settings.logo');
        $this->mlite['nama_instansi']  = $this->settings->get('settings.nama_instansi');
        $this->mlite['path']           = url();
        $this->mlite['version']        = $this->core->settings->get('settings.version');
        $this->mlite['token']          = '  ';
        if ($this->_loginCheck()) {
            $this->mlite['vedika_user']    = $_SESSION['vedika_user'];
            $this->mlite['vedika_token']   = $_SESSION['vedika_token'];
        }
        $this->mlite['slug']           = parseURL();
    }

    public function routes()
    {
        $this->route('veda', 'getIndex');
        $this->route('veda/pengajuan/ralan', 'getIndexPengajuanRalan');
        $this->route('veda/pengajuan/ralan/(:int)', 'getIndexPengajuanRalan');
        $this->route('veda/pengajuan/ranap', 'getIndexPengajuanRanap');
        $this->route('veda/pengajuan/ranap/(:int)', 'getIndexPengajuanRanap');
        $this->route('veda/perbaikan', 'getIndexPerbaikan');
        $this->route('veda/perbaikan/(:int)', 'getIndexPerbaikan');
        $this->route('veda/perbaikan/excel', 'getPerbaikanExport');
        $this->route('veda/ralan', 'getIndexRalan');
        $this->route('veda/ranap', 'getIndexRanap');
        $this->route('veda/css', 'getCss');
        $this->route('veda/javascript', 'getJavascript');
        $this->route('veda/pdf/(:str)', 'getPDF');
        $this->route('veda/createpdf/(:str)', 'getCreatePDF');
        $this->route('veda/downloadpdf/(:str)', 'getDownloadPDF');
        $this->route('veda/catatan/(:str)', 'getCatatan');
      	$this->route('veda/tarik','getDataSep');
        $this->route('veda/delfeed/(:str)/(:str)', 'getDelFeed');
        $this->route('veda/logout', function () {
            $this->logout();
        });
    }

    public function getIndex()
    {
        if ($this->_loginCheck()) {
            $page = [
                'title' => 'VEDA',
                'desc' => 'Dashboard Verifikasi Digital Klaim BPJS',
                'content' => $this->_getManage()
            ];
        } else {
            if (isset($_POST['login'])) {
                if ($this->_login($_POST['username'], $_POST['password'])) {
                    if (count($arrayURL = parseURL()) > 1) {
                        $url = array_merge(['veda'], $arrayURL);
                        redirect(url($url));
                    }
                }
                redirect(url(['veda', '']));
            }
            $page = [
                'title' => 'VEDA',
                'desc' => 'Dashboard Verifikasi Digital Klaim BPJS',
                'content' => $this->draw('login.html')
            ];
        }

        $this->setTemplate('fullpage.html');
        $this->tpl->set('page', $page);

    }

    public function getIndexPengajuanRalan()
    {
      if ($this->_loginCheck()) {
        if(isset($_POST['perbaiki'])) {
          $simpan_status = $this->db('mlite_vedika')
          ->where('nosep', $_POST['nosep'])
          ->save([
            'status' => 'Perbaiki'
          ]);
          if($simpan_status) {
            $this->db('mlite_vedika_feedback')->save([
              'id' => NULL,
              'nosep' => $_POST['nosep'],
              'tanggal' => date('Y-m-d'),
              'catatan' => $_POST['catatan'],
              'username' => $_SESSION['vedika_user']
            ]);
          }
        }
        $page = [
            'title' => 'VEDA',
            'desc' => 'Dashboard Verifikasi Digital Klaim BPJS',
            'content' => $this->_getIndexPengajuanRalan($page = 1)
        ];
      } else {
        $page = [
            'title' => 'VEDA',
            'desc' => 'Dashboard Verifikasi Digital Klaim BPJS',
            'content' => $this->draw('login.html', ['mlite' => $this->mlite])
        ];
      }
      $this->setTemplate('fullpage.html');
      $this->tpl->set('page', $page);
    }

    public function _getIndexPengajuanRalan($page = 1)
    {
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

      $slug = parseURL();
      if (count($slug) == 4 && $slug[0] == 'veda' && $slug[1] == 'pengajuan') {
        $page = $slug[3];
      }
      // pagination
      $totalRecords = $this->db()->pdo()->prepare("SELECT no_rawat FROM mlite_vedika WHERE status = 'Pengajuan' AND jenis = '2' AND (no_rkm_medis LIKE ? OR no_rawat LIKE ? OR nosep LIKE ?) AND tgl_registrasi BETWEEN '$start_date' AND '$end_date'");
      $totalRecords->execute(['%'.$phrase.'%', '%'.$phrase.'%', '%'.$phrase.'%']);
      $totalRecords = $totalRecords->fetchAll();

      $pagination = new \Systems\Lib\Pagination($page, count($totalRecords), $perpage, url(['veda', 'pengajuan', 'ralan', '%d?s='.$phrase.'&start_date='.$start_date.'&end_date='.$end_date]));
      $this->assign['pagination'] = $pagination->nav('pagination','5');
      $this->assign['totalRecords'] = $totalRecords;

      $offset = $pagination->offset();
      $query = $this->db()->pdo()->prepare("SELECT * FROM mlite_vedika WHERE status = 'Pengajuan' AND jenis = '2' AND (no_rkm_medis LIKE ? OR no_rawat LIKE ? OR nosep LIKE ?) AND tgl_registrasi BETWEEN '$start_date' AND '$end_date' ORDER BY nosep LIMIT $perpage OFFSET $offset");
      $query->execute(['%'.$phrase.'%', '%'.$phrase.'%', '%'.$phrase.'%']);
      $rows = $query->fetchAll();
      $this->assign['list'] = [];
      if (count($rows)) {
          foreach ($rows as $row) {
              $berkas_digital = $this->db('berkas_digital_perawatan')
                ->join('master_berkas_digital', 'master_berkas_digital.kode=berkas_digital_perawatan.kode')
                ->where('berkas_digital_perawatan.no_rawat', $row['no_rawat'])
                ->asc('master_berkas_digital.nama')
                ->toArray();
              $galleri_pasien = $this->db('mlite_pasien_galleries_items')
                ->join('mlite_pasien_galleries', 'mlite_pasien_galleries.id = mlite_pasien_galleries_items.gallery')
                ->where('mlite_pasien_galleries.slug', $row['no_rkm_medis'])
                ->toArray();

              $berkas_digital_pasien = array();
              if (count($galleri_pasien)) {
                  foreach ($galleri_pasien as $galleri) {
                      $galleri['src'] = unserialize($galleri['src']);

                      if (!isset($galleri['src']['sm'])) {
                          $galleri['src']['sm'] = isset($galleri['src']['xs']) ? $galleri['src']['xs'] : $galleri['src']['lg'];
                      }

                      $berkas_digital_pasien[] = $galleri;
                  }
              }

              $row = htmlspecialchars_array($row);
              $row['nm_pasien'] = $this->core->getPasienInfo('nm_pasien', $row['no_rkm_medis']);
              $row['almt_pj'] = $this->core->getPasienInfo('alamat', $row['no_rkm_medis']);
              $row['jk'] = $this->core->getPasienInfo('jk', $row['no_rkm_medis']);
              $row['umurdaftar'] = $this->core->getRegPeriksaInfo('umurdaftar', $row['no_rawat']);
              $row['sttsumur'] = $this->core->getRegPeriksaInfo('sttsumur', $row['no_rawat']);
              $row['tgl_registrasi'] = $this->core->getRegPeriksaInfo('tgl_registrasi', $row['no_rawat']);
              $row['status_lanjut'] = $this->core->getRegPeriksaInfo('status_lanjut', $row['no_rawat']);
              $row['png_jawab'] = $this->core->getPenjabInfo('png_jawab', $this->core->getRegPeriksaInfo('kd_pj', $row['no_rawat']));
              $row['jam_reg'] = $this->core->getRegPeriksaInfo('jam_reg', $row['no_rawat']);
              $row['nm_dokter'] = $this->core->getDokterInfo('nm_dokter', $this->core->getRegPeriksaInfo('kd_dokter', $row['no_rawat']));
              $row['nm_poli'] = $this->core->getPoliklinikInfo('nm_poli', $this->core->getRegPeriksaInfo('kd_poli', $row['no_rawat']));
              $row['no_sep'] = $this->_getSEPInfo('no_sep', $row['no_rawat']);
              $row['no_peserta'] = $this->_getSEPInfo('no_kartu', $row['no_rawat']);
              $row['no_rujukan'] = $this->_getSEPInfo('no_rujukan', $row['no_rawat']);
              $row['kd_penyakit'] = $this->_getDiagnosa('kd_penyakit', $row['no_rawat'], $row['status_lanjut']);
              $row['nm_penyakit'] = $this->_getDiagnosa('nm_penyakit', $row['no_rawat'], $row['status_lanjut']);
              $row['berkas_digital'] = $berkas_digital;
              $row['berkas_digital_pasien'] = $berkas_digital_pasien;
              $row['sepURL'] = url(['veda', 'sep', $row['no_sep']]);
              $row['pdfURL'] = url(['veda', 'pdf', $this->convertNorawat($row['no_rawat'])]);
              $row['downloadURL'] = url(['veda', 'downloadpdf', $this->convertNorawat($row['no_rawat'])]);
              $row['catatanURL'] = url(['veda', 'catatan', $this->_getSEPInfo('no_sep', $row['no_rawat'])]);
              $row['status_pengajuan'] = $this->db('mlite_vedika')->where('nosep', $this->_getSEPInfo('no_sep', $row['no_rawat']))->desc('id')->limit(1)->toArray();
              $row['resumeURL']  = url(['veda', 'resume', $this->convertNorawat($row['no_rawat'])]);
              $row['billingURL'] = url(['veda', 'billing', $this->convertNorawat($row['no_rawat'])]);
              $this->assign['list'][] = $row;
          }
      }

      $this->assign['searchUrl'] =  url(['veda', 'pengajuan', 'ralan', $page]);
      return $this->draw('pengajuan_ralan.html', ['vedika' => $this->assign]);
    }

    public function getIndexPengajuanRanap()
    {
      if ($this->_loginCheck()) {
        if(isset($_POST['perbaiki'])) {
          $simpan_status = $this->db('mlite_vedika')
          ->where('nosep', $_POST['nosep'])
          ->save([
            'status' => 'Perbaiki'
          ]);
          if($simpan_status) {
            $this->db('mlite_vedika_feedback')->save([
              'id' => NULL,
              'nosep' => $_POST['nosep'],
              'tanggal' => date('Y-m-d'),
              'catatan' => $_POST['catatan'],
              'username' => $_SESSION['vedika_user']
            ]);
          }
        }
        $page = [
            'title' => 'VEDA',
            'desc' => 'Dashboard Verifikasi Digital Klaim BPJS',
            'content' => $this->_getIndexPengajuanRanap($page = 1)
        ];
      } else {
        $page = [
            'title' => 'VEDA',
            'desc' => 'Dashboard Verifikasi Digital Klaim BPJS',
            'content' => $this->draw('login.html', ['mlite' => $this->mlite])
        ];
      }
      $this->setTemplate('fullpage.html');
      $this->tpl->set('page', $page);
    }

    public function _getIndexPengajuanRanap($page = 1)
    {
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

      $slug = parseURL();
      if (count($slug) == 4 && $slug[0] == 'veda' && $slug[1] == 'pengajuan') {
        $page = $slug[3];
      }
      // pagination
      $totalRecords = $this->db()->pdo()->prepare("SELECT no_rawat FROM mlite_vedika WHERE status = 'Pengajuan' AND jenis = '1' AND (no_rkm_medis LIKE ? OR no_rawat LIKE ? OR nosep LIKE ?) AND no_rawat IN (SELECT no_rawat FROM kamar_inap WHERE tgl_keluar BETWEEN '$start_date' AND '$end_date')");
      $totalRecords->execute(['%'.$phrase.'%', '%'.$phrase.'%', '%'.$phrase.'%']);
      $totalRecords = $totalRecords->fetchAll();

      $pagination = new \Systems\Lib\Pagination($page, count($totalRecords), $perpage, url(['veda', 'pengajuan', 'ranap', '%d?s='.$phrase.'&start_date='.$start_date.'&end_date='.$end_date]));
      $this->assign['pagination'] = $pagination->nav('pagination','5');
      $this->assign['totalRecords'] = $totalRecords;

      $offset = $pagination->offset();
      $query = $this->db()->pdo()->prepare("SELECT * FROM mlite_vedika WHERE status = 'Pengajuan' AND jenis = '1' AND (no_rkm_medis LIKE ? OR no_rawat LIKE ? OR nosep LIKE ?) AND no_rawat IN (SELECT no_rawat FROM kamar_inap WHERE tgl_keluar BETWEEN '$start_date' AND '$end_date') ORDER BY nosep LIMIT $perpage OFFSET $offset");
      $query->execute(['%'.$phrase.'%', '%'.$phrase.'%', '%'.$phrase.'%']);
      $rows = $query->fetchAll();
      $this->assign['list'] = [];
      if (count($rows)) {
          foreach ($rows as $row) {
              $berkas_digital = $this->db('berkas_digital_perawatan')
                ->join('master_berkas_digital', 'master_berkas_digital.kode=berkas_digital_perawatan.kode')
                ->where('berkas_digital_perawatan.no_rawat', $row['no_rawat'])
                ->asc('master_berkas_digital.nama')
                ->toArray();
              $galleri_pasien = $this->db('mlite_pasien_galleries_items')
                ->join('mlite_pasien_galleries', 'mlite_pasien_galleries.id = mlite_pasien_galleries_items.gallery')
                ->where('mlite_pasien_galleries.slug', $row['no_rkm_medis'])
                ->toArray();

              $berkas_digital_pasien = array();
              if (count($galleri_pasien)) {
                  foreach ($galleri_pasien as $galleri) {
                      $galleri['src'] = unserialize($galleri['src']);

                      if (!isset($galleri['src']['sm'])) {
                          $galleri['src']['sm'] = isset($galleri['src']['xs']) ? $galleri['src']['xs'] : $galleri['src']['lg'];
                      }

                      $berkas_digital_pasien[] = $galleri;
                  }
              }

              $row = htmlspecialchars_array($row);
              $row['nm_pasien'] = $this->core->getPasienInfo('nm_pasien', $row['no_rkm_medis']);
              $row['almt_pj'] = $this->core->getPasienInfo('alamat', $row['no_rkm_medis']);
              $row['jk'] = $this->core->getPasienInfo('jk', $row['no_rkm_medis']);
              $row['umurdaftar'] = $this->core->getRegPeriksaInfo('umurdaftar', $row['no_rawat']);
              $row['sttsumur'] = $this->core->getRegPeriksaInfo('sttsumur', $row['no_rawat']);
              $row['tgl_registrasi'] = $this->core->getRegPeriksaInfo('tgl_registrasi', $row['no_rawat']);
              $row['tgl_masuk'] = $this->core->getKamarInapInfo('tgl_masuk', $row['no_rawat']);
              $row['jam_masuk'] = $this->core->getKamarInapInfo('jam_masuk', $row['no_rawat']);
              $row['tgl_keluar'] = $this->core->getKamarInapInfo('tgl_keluar', $row['no_rawat']);
              $row['jam_keluar'] = $this->core->getKamarInapInfo('jam_keluar', $row['no_rawat']);
              $row['kd_kamar'] = $this->core->getKamarInapInfo('kd_kamar', $row['no_rawat']);
              $row['status_lanjut'] = $this->core->getRegPeriksaInfo('status_lanjut', $row['no_rawat']);
              $row['png_jawab'] = $this->core->getPenjabInfo('png_jawab', $this->core->getRegPeriksaInfo('kd_pj', $row['no_rawat']));
              $row['jam_reg'] = $this->core->getRegPeriksaInfo('jam_reg', $row['no_rawat']);
              $row['nm_dokter'] = $this->core->getDokterInfo('nm_dokter', $this->core->getRegPeriksaInfo('kd_dokter', $row['no_rawat']));
              $row['nm_poli'] = $this->core->getPoliklinikInfo('nm_poli', $this->core->getRegPeriksaInfo('kd_poli', $row['no_rawat']));
              $row['no_sep'] = $this->_getSEPInfo('no_sep', $row['no_rawat']);
              $row['no_peserta'] = $this->_getSEPInfo('no_kartu', $row['no_rawat']);
              $row['no_rujukan'] = $this->_getSEPInfo('no_rujukan', $row['no_rawat']);
              $row['kd_penyakit'] = $this->_getDiagnosa('kd_penyakit', $row['no_rawat'], $row['status_lanjut']);
              $row['nm_penyakit'] = $this->_getDiagnosa('nm_penyakit', $row['no_rawat'], $row['status_lanjut']);
              $row['berkas_digital'] = $berkas_digital;
              $row['berkas_digital_pasien'] = $berkas_digital_pasien;
              $row['sepURL'] = url(['veda', 'sep', $row['no_sep']]);
              $row['pdfURL'] = url(['veda', 'pdf', $this->convertNorawat($row['no_rawat'])]);
              $row['downloadURL'] = url(['veda', 'downloadpdf', $this->convertNorawat($row['no_rawat'])]);
              $row['catatanURL'] = url(['veda', 'catatan', $this->_getSEPInfo('no_sep', $row['no_rawat'])]);
              $row['status_pengajuan'] = $this->db('mlite_vedika')->where('nosep', $this->_getSEPInfo('no_sep', $row['no_rawat']))->desc('id')->limit(1)->toArray();
              $row['resumeURL']  = url(['veda', 'resume', $this->convertNorawat($row['no_rawat'])]);
              $row['billingURL'] = url(['veda', 'billing', $this->convertNorawat($row['no_rawat'])]);
              $this->assign['list'][] = $row;
          }
      }

      $this->assign['searchUrl'] =  url(['veda', 'pengajuan', 'ralan', $page]);
      return $this->draw('pengajuan_ranap.html', ['vedika' => $this->assign]);
    }

    public function getIndexPerbaikan()
    {
      if ($this->_loginCheck()) {
        if(isset($_POST['perbaiki'])) {
          $simpan_status = $this->db('mlite_vedika')
          ->where('nosep', $_POST['nosep'])
          ->save([
            'status' => 'Perbaiki'
          ]);
          if($simpan_status) {
            $this->db('mlite_vedika_feedback')->save([
              'id' => NULL,
              'nosep' => $_POST['nosep'],
              'tanggal' => date('Y-m-d'),
              'catatan' => $_POST['catatan'],
              'username' => $_SESSION['vedika_user']
            ]);
          }
        }
        $page = [
            'title' => 'VEDA',
            'desc' => 'Dashboard Verifikasi Digital Klaim BPJS',
            'content' => $this->_getIndexPerbaikan($page = 1)
        ];
      } else {
        $page = [
            'title' => 'VEDA',
            'desc' => 'Dashboard Verifikasi Digital Klaim BPJS',
            'content' => $this->draw('login.html', ['mlite' => $this->mlite])
        ];
      }
      $this->setTemplate('fullpage.html');
      $this->tpl->set('page', $page);
    }

    public function _getIndexPerbaikan($page = 1)
    {
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

      $slug = parseURL();
      if (count($slug) == 3 && $slug[0] == 'veda' && $slug[1] == 'perbaikan') {
        $page = $slug[2];
      }
      // pagination
      $totalRecords = $this->db()->pdo()->prepare("SELECT no_rawat FROM mlite_vedika WHERE status = 'Perbaiki' AND (no_rkm_medis LIKE ? OR no_rawat LIKE ? OR nosep LIKE ?) AND tgl_registrasi BETWEEN '$start_date' AND '$end_date'");
      $totalRecords->execute(['%'.$phrase.'%', '%'.$phrase.'%', '%'.$phrase.'%']);
      $totalRecords = $totalRecords->fetchAll();

      $pagination = new \Systems\Lib\Pagination($page, count($totalRecords), $perpage, url(['veda', 'perbaikan', '%d?s='.$phrase.'&start_date='.$start_date.'&end_date='.$end_date]));
      $this->assign['pagination'] = $pagination->nav('pagination','5');
      $this->assign['totalRecords'] = $totalRecords;

      $offset = $pagination->offset();
      $query = $this->db()->pdo()->prepare("SELECT * FROM mlite_vedika WHERE status = 'Perbaiki' AND (no_rkm_medis LIKE ? OR no_rawat LIKE ? OR nosep LIKE ?) AND tgl_registrasi BETWEEN '$start_date' AND '$end_date' ORDER BY nosep LIMIT $perpage OFFSET $offset");
      $query->execute(['%'.$phrase.'%', '%'.$phrase.'%', '%'.$phrase.'%']);
      $rows = $query->fetchAll();
      $this->assign['list'] = [];
      if (count($rows)) {
          foreach ($rows as $row) {
              $berkas_digital = $this->db('berkas_digital_perawatan')
                ->join('master_berkas_digital', 'master_berkas_digital.kode=berkas_digital_perawatan.kode')
                ->where('berkas_digital_perawatan.no_rawat', $row['no_rawat'])
                ->asc('master_berkas_digital.nama')
                ->toArray();
              $galleri_pasien = $this->db('mlite_pasien_galleries_items')
                ->join('mlite_pasien_galleries', 'mlite_pasien_galleries.id = mlite_pasien_galleries_items.gallery')
                ->where('mlite_pasien_galleries.slug', $row['no_rkm_medis'])
                ->toArray();

              $berkas_digital_pasien = array();
              if (count($galleri_pasien)) {
                  foreach ($galleri_pasien as $galleri) {
                      $galleri['src'] = unserialize($galleri['src']);

                      if (!isset($galleri['src']['sm'])) {
                          $galleri['src']['sm'] = isset($galleri['src']['xs']) ? $galleri['src']['xs'] : $galleri['src']['lg'];
                      }

                      $berkas_digital_pasien[] = $galleri;
                  }
              }

              $row = htmlspecialchars_array($row);
              $row['nm_pasien'] = $this->core->getPasienInfo('nm_pasien', $row['no_rkm_medis']);
              $row['almt_pj'] = $this->core->getPasienInfo('alamat', $row['no_rkm_medis']);
              $row['jk'] = $this->core->getPasienInfo('jk', $row['no_rkm_medis']);
              $row['umurdaftar'] = $this->core->getRegPeriksaInfo('umurdaftar', $row['no_rawat']);
              $row['sttsumur'] = $this->core->getRegPeriksaInfo('sttsumur', $row['no_rawat']);
              $row['tgl_registrasi'] = $this->core->getRegPeriksaInfo('tgl_registrasi', $row['no_rawat']);
              $row['status_lanjut'] = $this->core->getRegPeriksaInfo('status_lanjut', $row['no_rawat']);
              $row['png_jawab'] = $this->core->getPenjabInfo('png_jawab', $this->core->getRegPeriksaInfo('kd_pj', $row['no_rawat']));
              $row['jam_reg'] = $this->core->getRegPeriksaInfo('jam_reg', $row['no_rawat']);
              $row['nm_dokter'] = $this->core->getDokterInfo('nm_dokter', $this->core->getRegPeriksaInfo('kd_dokter', $row['no_rawat']));
              $row['nm_poli'] = $this->core->getPoliklinikInfo('nm_poli', $this->core->getRegPeriksaInfo('kd_poli', $row['no_rawat']));
              $row['no_sep'] = $this->_getSEPInfo('no_sep', $row['no_rawat']);
              $row['no_peserta'] = $this->_getSEPInfo('no_kartu', $row['no_rawat']);
              $row['no_rujukan'] = $this->_getSEPInfo('no_rujukan', $row['no_rawat']);
              $row['kd_penyakit'] = $this->_getDiagnosa('kd_penyakit', $row['no_rawat'], $row['status_lanjut']);
              $row['nm_penyakit'] = $this->_getDiagnosa('nm_penyakit', $row['no_rawat'], $row['status_lanjut']);
              $row['berkas_digital'] = $berkas_digital;
              $row['berkas_digital_pasien'] = $berkas_digital_pasien;
              $row['sepURL'] = url(['veda', 'sep', $row['no_sep']]);
              $row['pdfURL'] = url(['veda', 'pdf', $this->convertNorawat($row['no_rawat'])]);
              $row['downloadURL'] = url(['veda', 'downloadpdf', $this->convertNorawat($row['no_rawat'])]);
              $row['catatanURL'] = url(['veda', 'catatan', $this->_getSEPInfo('no_sep', $row['no_rawat'])]);
              $row['status_pengajuan'] = $this->db('mlite_vedika')->where('nosep', $this->_getSEPInfo('no_sep', $row['no_rawat']))->desc('id')->limit(1)->toArray();
              $row['resumeURL']  = url(['veda', 'resume', $this->convertNorawat($row['no_rawat'])]);
              $row['billingURL'] = url(['veda', 'billing', $this->convertNorawat($row['no_rawat'])]);
              $this->assign['list'][] = $row;
          }
      }

      $this->assign['searchUrl'] =  url(['veda', 'perbaikan', $page]);
      return $this->draw('perbaikan.html', ['vedika' => $this->assign]);
    }

    public function getPerbaikanExport()
    {
      $start_date = $_GET['start_date'];
      $end_date = $_GET['end_date'];
      $rows = $this->db('mlite_vedika')->where('status', 'Perbaiki')->where('tgl_registrasi','>=',$start_date)->where('tgl_registrasi','<=', $end_date)->toArray();
      if(isset($_GET['jenis']) && $_GET['jenis'] == 1) {
        $rows = $this->db('mlite_vedika')->where('status', 'Perbaiki')->where('tgl_registrasi','>=',$start_date)->where('tgl_registrasi','<=', $end_date)->where('jenis', 1)->toArray();
      }
      if(isset($_GET['jenis']) && $_GET['jenis'] == 2) {
        $rows = $this->db('mlite_vedika')->where('status', 'Perbaiki')->where('tgl_registrasi','>=',$start_date)->where('tgl_registrasi','<=', $end_date)->where('jenis', 2)->toArray();
      }
      $i = 1;
      foreach ($rows as $row) {
        $row['status_lanjut'] = 'Ralan';
        if($row['jenis'] == 1) {
          $row['status_lanjut'] = 'Ranap';
        }
        $row['no'] = $i++;
        $row['tgl_masuk'] = $this->core->getRegPeriksaInfo('tgl_registrasi', $row['no_rawat']);
        $row['tgl_keluar'] = $this->core->getRegPeriksaInfo('tgl_registrasi', $row['no_rawat']);
        if($row['jenis'] == 1) {
          $row['tgl_masuk'] = $this->core->getKamarInapInfo('tgl_masuk', $row['no_rawat']);
          $row['tgl_keluar'] = $this->core->getKamarInapInfo('tgl_keluar', $row['no_rawat']);
        }
        $row['nm_pasien'] = $this->core->getPasienInfo('nm_pasien', $row['no_rkm_medis']);
        $row['no_peserta'] = $this->core->getPasienInfo('no_peserta', $row['no_rkm_medis']);
        $row['kd_penyakit'] = $this->_getDiagnosa('kd_penyakit', $row['no_rawat'], $row['status_lanjut']);
        $row['kd_prosedur'] = $this->_getProsedur('kode', $row['no_rawat'], $row['status_lanjut']);

        $get_feedback_bpjs = $this->db()->pdo()->prepare("SELECT * FROM mlite_vedika_feedback WHERE nosep = '{$row['nosep']}' AND username IN (SELECT username FROM mlite_users_vedika) ORDER BY id DESC LIMIT 1");
        $get_feedback_bpjs->execute();
        $get_feedback_bpjs = $get_feedback_bpjs->fetch();
        $row['konfirmasi_bpjs'] = $get_feedback_bpjs['catatan'];

        $get_feedback_rs = $this->db()->pdo()->prepare("SELECT * FROM mlite_vedika_feedback WHERE nosep = '{$row['nosep']}' AND username IN (SELECT username FROM mlite_users) ORDER BY id DESC LIMIT 1");
        $get_feedback_rs->execute();
        $get_feedback_rs = $get_feedback_rs->fetch();
        $row['konfirmasi_rs'] = $get_feedback_rs['catatan'];

        $display[] = $row;
      }
      $content = $this->draw('perbaikan_excel.html', [
        'powered' => 'Powered by <a href="https://mlite.id/">mLITE</a>',
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

    public function getIndexRalan()
    {
      if ($this->_loginCheck()) {
        if(isset($_POST['setuju'])) {
          $this->db('mlite_vedika')->save([
            'id' => NULL,
            'tanggal' => date('Y-m-d'),
            'no_rkm_medis' => $_POST['no_rkm_medis'],
            'no_rawat' => $_POST['no_rawat'],
            'nosep' => $_POST['nosep'],
            'catatan' => $_POST['catatan'],
            'status' => 'Setuju',
            'username' => $_SESSION['vedika_user']
          ]);
        }

        if(isset($_POST['perbaiki'])) {
          $this->db('mlite_vedika')->save([
            'id' => NULL,
            'tanggal' => date('Y-m-d'),
            'no_rkm_medis' => $_POST['no_rkm_medis'],
            'no_rawat' => $_POST['no_rawat'],
            'nosep' => $_POST['nosep'],
            'catatan' => $_POST['catatan'],
            'status' => 'Perbaiki',
            'username' => $_SESSION['vedika_user']
          ]);
        }
        $page = [
            'title' => 'VEDA',
            'desc' => 'Dashboard Verifikasi Digital Klaim BPJS',
            'content' => $this->_getManageRalan($page = 1)
        ];
      } else {
        $page = [
            'title' => 'VEDA',
            'desc' => 'Dashboard Verifikasi Digital Klaim BPJS',
            'content' => $this->draw('login.html', ['mlite' => $this->mlite])
        ];
      }
      $this->setTemplate('fullpage.html');
      $this->tpl->set('page', $page);
    }

    public function getIndexRanap()
    {
      if($this->_loginCheck()) {
        if(isset($_POST['setuju'])) {
          $this->db('mlite_vedika')->save([
            'id' => NULL,
            'tanggal' => date('Y-m-d'),
            'no_rkm_medis' => $_POST['no_rkm_medis'],
            'no_rawat' => $_POST['no_rawat'],
            'nosep' => $_POST['nosep'],
            'catatan' => $_POST['catatan'],
            'status' => 'Setuju',
            'username' => $_SESSION['vedika_user']
          ]);
        }

        if(isset($_POST['perbaiki'])) {
          $this->db('mlite_vedika')->save([
            'id' => NULL,
            'tanggal' => date('Y-m-d'),
            'no_rkm_medis' => $_POST['no_rkm_medis'],
            'no_rawat' => $_POST['no_rawat'],
            'nosep' => $_POST['nosep'],
            'catatan' => $_POST['catatan'],
            'status' => 'Perbaiki',
            'username' => $_SESSION['vedika_user']
          ]);
        }
        $page = [
            'title' => 'VEDA',
            'desc' => 'Dashboard Verifikasi Digital Klaim BPJS',
            'content' => $this->_getManageRanap($page = 1)
        ];
      } else {
        $page = [
            'title' => 'VEDA',
            'desc' => 'Dashboard Verifikasi Digital Klaim BPJS',
            'content' => $this->draw('login.html', ['mlite' => $this->mlite])
        ];
      }

        $this->setTemplate('fullpage.html');
        $this->tpl->set('page', $page);
    }

    public function _getManage()
    {
      //$this->_addHeaderFiles();
      $this->core->addCSS(url('assets/css/bootstrap-datetimepicker.css'));
      $this->core->addJS(url('assets/jscripts/moment-with-locales.js'));
      $this->core->addJS(url('assets/jscripts/bootstrap-datetimepicker.js'));

	    $date = $this->settings->get('vedika.verifikasi');
      if(isset($_GET['periode']) && $_GET['periode'] !=''){
        $date = $_GET['periode'];
      }

      $PengajuanRalan = $this->db()->pdo()->prepare("SELECT no_rawat FROM mlite_vedika WHERE status = 'Pengajuan' AND jenis = '2' AND tgl_registrasi LIKE '{$date}%'");
      $PengajuanRalan->execute();
      $PengajuanRalan = $PengajuanRalan->fetchAll();
      $stat['pengajuan_ralan'] = count($PengajuanRalan);

      $PengajuanRanap = $this->db()->pdo()->prepare("SELECT no_rawat FROM mlite_vedika WHERE status = 'Pengajuan' AND jenis = '1' AND no_rawat IN (SELECT no_rawat FROM kamar_inap WHERE tgl_keluar LIKE '{$date}%')");
      $PengajuanRanap->execute();
      $PengajuanRanap = $PengajuanRanap->fetchAll();
      $stat['pengajuan_ranap'] = count($PengajuanRanap);

      $PerbaikanRalan = $this->db()->pdo()->prepare("SELECT no_rawat FROM mlite_vedika WHERE status = 'Perbaiki' AND jenis = '2' AND tgl_registrasi LIKE '{$date}%'");
      $PerbaikanRalan->execute();
      $PerbaikanRalan = $PerbaikanRalan->fetchAll();
      $stats['PerbaikanRalan'] = count($PerbaikanRalan);

      $PerbaikanRanap = $this->db()->pdo()->prepare("SELECT no_rawat FROM mlite_vedika WHERE status = 'Perbaiki' AND jenis = '1' AND tgl_registrasi LIKE '{$date}%'");
      $PerbaikanRanap->execute();
      $PerbaikanRanap = $PerbaikanRanap->fetchAll();
      $stats['PerbaikanRanap'] = count($PerbaikanRanap);

      $stat['perbaiki'] = $stats['PerbaikanRalan'] + $stats['PerbaikanRanap'];

      return $this->draw('index.html', ['stat' => $stat, 'periode' => $date]);
    }

    public function _getManageRalan($page = 1)
    {
      $this->_addHeaderFiles();
      $perpage = '1';
      $phrase = '';
      if(isset($_GET['s']))
        $phrase = $_GET['s'];

      // pagination
      $totalRecords = $this->db()->pdo()->prepare("SELECT reg_periksa.no_rawat FROM reg_periksa, pasien, mlite_vedika WHERE reg_periksa.no_rkm_medis = pasien.no_rkm_medis AND (reg_periksa.no_rkm_medis LIKE ? OR reg_periksa.no_rawat LIKE ? OR pasien.nm_pasien LIKE ?) AND reg_periksa.status_lanjut = 'Ralan' AND mlite_vedika.status = 'Pengajuan'");
      $totalRecords->execute(['%'.$phrase.'%', '%'.$phrase.'%', '%'.$phrase.'%']);
      $totalRecords = $totalRecords->fetchAll();

      $pagination = new \Systems\Lib\Pagination($page, count($totalRecords), $perpage, url([ADMIN, 'vedika', 'index', '%d?s='.$phrase]));
      $this->assign['pagination'] = $pagination->nav('pagination','5');
      $this->assign['totalRecords'] = $totalRecords;

      $offset = $pagination->offset();
      $query = $this->db()->pdo()->prepare("SELECT reg_periksa.*, pasien.*, dokter.nm_dokter, poliklinik.nm_poli, penjab.png_jawab FROM reg_periksa, pasien, dokter, poliklinik, penjab, mlite_vedika WHERE reg_periksa.no_rkm_medis = pasien.no_rkm_medis AND reg_periksa.kd_dokter = dokter.kd_dokter AND reg_periksa.kd_poli = poliklinik.kd_poli AND reg_periksa.kd_pj = penjab.kd_pj AND (reg_periksa.no_rkm_medis LIKE ? OR reg_periksa.no_rawat LIKE ? OR pasien.nm_pasien LIKE ?) AND mlite_vedika.status = 'Pengajuan' AND reg_periksa.status_lanjut = 'Ralan' LIMIT $perpage OFFSET $offset");
      $query->execute(['%'.$phrase.'%', '%'.$phrase.'%', '%'.$phrase.'%']);
      $rows = $query->fetchAll();

      $this->assign['list'] = [];
      if (count($rows)) {
          foreach ($rows as $row) {
              $berkas_digital = $this->db('berkas_digital_perawatan')
                ->join('master_berkas_digital', 'master_berkas_digital.kode=berkas_digital_perawatan.kode')
                ->where('berkas_digital_perawatan.no_rawat', $row['no_rawat'])
                ->asc('master_berkas_digital.nama')
                ->toArray();
              $galleri_pasien = $this->db('mlite_pasien_galleries_items')
                ->join('mlite_pasien_galleries', 'mlite_pasien_galleries.id = mlite_pasien_galleries_items.gallery')
                ->where('mlite_pasien_galleries.slug', $row['no_rkm_medis'])
                ->toArray();

              $berkas_digital_pasien = array();
              if (count($galleri_pasien)) {
                  foreach ($galleri_pasien as $galleri) {
                      $galleri['src'] = unserialize($galleri['src']);

                      if (!isset($galleri['src']['sm'])) {
                          $galleri['src']['sm'] = isset($galleri['src']['xs']) ? $galleri['src']['xs'] : $galleri['src']['lg'];
                      }

                      $berkas_digital_pasien[] = $galleri;
                  }
              }

              $row = htmlspecialchars_array($row);
              $row['no_sep'] = $this->_getSEPInfo('no_sep', $row['no_rawat']);
              $row['no_peserta'] = $this->_getSEPInfo('no_kartu', $row['no_rawat']);
              $row['no_rujukan'] = $this->_getSEPInfo('no_rujukan', $row['no_rawat']);
              $row['kd_penyakit'] = $this->_getDiagnosa('kd_penyakit', $row['no_rawat'], $row['status_lanjut']);
              $row['nm_penyakit'] = $this->_getDiagnosa('nm_penyakit', $row['no_rawat'], $row['status_lanjut']);
              $row['berkas_digital'] = $berkas_digital;
              $row['berkas_digital_pasien'] = $berkas_digital_pasien;
              $row['sepURL'] = url(['veda', 'sep', $row['no_sep']]);
              $row['pdfURL'] = url(['veda', 'pdf', $this->convertNorawat($row['no_rawat'])]);
              $row['downloadURL'] = url(['veda', 'downloadpdf', $this->convertNorawat($row['no_rawat'])]);
              $row['catatanURL'] = url(['veda', 'catatan', $this->_getSEPInfo('no_sep', $row['no_rawat'])]);
              $row['status_pengajuan'] = $this->db('mlite_vedika')->where('nosep', $this->_getSEPInfo('no_sep', $row['no_rawat']))->desc('id')->limit(1)->toArray();
              $row['resumeURL']  = url(['veda', 'resume', $this->convertNorawat($row['no_rawat'])]);
              $row['billingURL'] = url(['veda', 'billing', $this->convertNorawat($row['no_rawat'])]);
              $this->assign['list'][] = $row;
          }
      }

      $this->assign['searchUrl'] =  url(['veda', 'ralan', $page.'?start_date='.$start_date.'&end_date='.$end_date]);
      return $this->draw('manage_ralan.html', ['vedika' => $this->assign]);

    }

    public function _getManageRanap($page = 1)
    {
      $this->_addHeaderFiles();

      $query = $this->db()->pdo()->prepare("SELECT reg_periksa.*, pasien.*, dokter.nm_dokter, poliklinik.nm_poli, penjab.png_jawab FROM reg_periksa, pasien, dokter, poliklinik, penjab, mlite_vedika WHERE reg_periksa.no_rkm_medis = pasien.no_rkm_medis AND reg_periksa.kd_dokter = dokter.kd_dokter AND reg_periksa.kd_poli = poliklinik.kd_poli AND reg_periksa.kd_pj = penjab.kd_pj AND reg_periksa.status_lanjut = 'Ranap' AND reg_periksa.no_rawat = mlite_vedika.no_rawat AND mlite_vedika.status = 'Pengajuan'");
      $query->execute();
      $rows = $query->fetchAll();

      $this->assign['list'] = [];
      if (count($rows)) {
          foreach ($rows as $row) {
              $berkas_digital = $this->db('berkas_digital_perawatan')
                ->join('master_berkas_digital', 'master_berkas_digital.kode=berkas_digital_perawatan.kode')
                ->where('berkas_digital_perawatan.no_rawat', $row['no_rawat'])
                ->asc('master_berkas_digital.nama')
                ->toArray();
              $galleri_pasien = $this->db('mlite_pasien_galleries_items')
                ->join('mlite_pasien_galleries', 'mlite_pasien_galleries.id = mlite_pasien_galleries_items.gallery')
                ->where('mlite_pasien_galleries.slug', $row['no_rkm_medis'])
                ->toArray();

              $berkas_digital_pasien = array();
              if (count($galleri_pasien)) {
                  foreach ($galleri_pasien as $galleri) {
                      $galleri['src'] = unserialize($galleri['src']);

                      if (!isset($galleri['src']['sm'])) {
                          $galleri['src']['sm'] = isset($galleri['src']['xs']) ? $galleri['src']['xs'] : $galleri['src']['lg'];
                      }

                      $berkas_digital_pasien[] = $galleri;
                  }
              }

              $row = htmlspecialchars_array($row);
              $row['no_sep'] = $this->_getSEPInfo('no_sep', $row['no_rawat']);
              $row['no_peserta'] = $this->_getSEPInfo('no_kartu', $row['no_rawat']);
              $row['no_rujukan'] = $this->_getSEPInfo('no_rujukan', $row['no_rawat']);
              $row['kd_penyakit'] = $this->_getDiagnosa('kd_penyakit', $row['no_rawat'], $row['status_lanjut']);
              $row['nm_penyakit'] = $this->_getDiagnosa('nm_penyakit', $row['no_rawat'], $row['status_lanjut']);
              $row['berkas_digital'] = $berkas_digital;
              $row['berkas_digital_pasien'] = $berkas_digital_pasien;
              $row['sepURL'] = url(['veda', 'sep', $row['no_sep']]);
              $row['pdfURL'] = url(['veda', 'pdf', $this->convertNorawat($row['no_rawat'])]);
              $row['downloadURL'] = url(['veda', 'downloadpdf', $this->convertNorawat($row['no_rawat'])]);
              $row['catatanURL'] = url(['veda', 'catatan', $this->convertNorawat($row['no_rawat'])]);
              $row['resumeURL']  = url(['veda', 'resume', $this->convertNorawat($row['no_rawat'])]);
              $row['billingURL'] = url(['veda', 'billing', $this->convertNorawat($row['no_rawat'])]);
              $this->assign['list'][] = $row;
          }
      }

      $this->assign['searchUrl'] =  url(['veda', 'ranap', $page.'?start_date='.$start_date.'&end_date='.$end_date]);
      return $this->draw('manage_ranap.html', ['vedika' => $this->assign]);

    }

    public function getPDF($id)
    {
      if ($this->_loginCheck()) {

        /*$berkas_digital = $this->db('berkas_digital_perawatan')
          ->join('master_berkas_digital', 'master_berkas_digital.kode=berkas_digital_perawatan.kode')
          ->where('berkas_digital_perawatan.no_rawat', $this->revertNorawat($id))
          ->asc('master_berkas_digital.nama')
          ->toArray();*/
        $berkas_digital = array();
        $berkas_digital_check = $this->db('berkas_digital_perawatan')
          ->join('master_berkas_digital', 'master_berkas_digital.kode=berkas_digital_perawatan.kode')
          ->where('berkas_digital_perawatan.no_rawat', $this->revertNorawat($id))
          ->asc('master_berkas_digital.nama')
          ->toArray();
        foreach ($berkas_digital_check as $low) {
          $filename = WEBAPPS_URL.'/berkasrawat/'.$low['lokasi_file'];
          $file_headers = @get_headers($filename);
          if($file_headers[0] == 'HTTP/1.1 200 OK'){
              $berkas_digital[] = $low;
          } else {
          }
        }

        $galleri_pasien = $this->db('mlite_pasien_galleries_items')
          ->join('mlite_pasien_galleries', 'mlite_pasien_galleries.id = mlite_pasien_galleries_items.gallery')
          ->where('mlite_pasien_galleries.slug', $this->getRegPeriksaInfo('no_rkm_medis', $this->revertNorawat($id)))
          ->toArray();

        $berkas_digital_pasien = array();
        if (count($galleri_pasien)) {
            foreach ($galleri_pasien as $galleri) {
                $galleri['src'] = unserialize($galleri['src']);

                if (!isset($galleri['src']['sm'])) {
                    $galleri['src']['sm'] = isset($galleri['src']['xs']) ? $galleri['src']['xs'] : $galleri['src']['lg'];
                }

                $berkas_digital_pasien[] = $galleri;
            }
        }

        $no_rawat = $this->revertNorawat($id);
        $query = $this->db()->pdo()->prepare("select no,nm_perawatan,pemisah,if(biaya=0,'',biaya),if(jumlah=0,'',jumlah),if(tambahan=0,'',tambahan),if(totalbiaya=0,'',totalbiaya),totalbiaya from billing where no_rawat='$no_rawat'");
        $query->execute();
        $rows = $query->fetchAll();
        $total=0;
        foreach ($rows as $key => $value) {
          $total = $total+$value['7'];
        }
        $total = $total;
        $this->tpl->set('total', $total);

        $settings = $this->settings('settings');
        $this->tpl->set('settings', $this->tpl->noParse_array(htmlspecialchars_array($settings)));

        $this->tpl->set('billing', $rows);

        $print_sep = array();
        if(!empty($this->_getSEPInfo('no_sep', $no_rawat))) {
          $print_sep['bridging_sep'] = $this->db('bridging_sep')->where('no_sep', $this->_getSEPInfo('no_sep', $no_rawat))->oneArray();
          $print_sep['bpjs_prb'] = $this->db('bpjs_prb')->where('no_sep', $this->_getSEPInfo('no_sep', $no_rawat))->oneArray();
          $batas_rujukan = $this->db('bridging_sep')->select('DATE_ADD(tglrujukan , INTERVAL 85 DAY) AS batas_rujukan')->where('no_sep', $id)->oneArray();
          $print_sep['batas_rujukan'] = $batas_rujukan['batas_rujukan'];
        }

        $print_sep['logoURL'] = url(MODULES.'/pendaftaran/img/bpjslogo.png');
        $this->tpl->set('print_sep', $print_sep);

        $cek_spri = $this->db('bridging_surat_pri_bpjs')->where('no_rawat', $this->revertNorawat($id))->oneArray();
        $this->tpl->set('cek_spri', $cek_spri);

        $print_spri = array();
        if (!empty($this->_getSPRIInfo('no_surat', $no_rawat))) {
          $print_spri['bridging_surat_pri_bpjs'] = $this->db('bridging_surat_pri_bpjs')->where('no_surat', $this->_getSPRIInfo('no_surat', $no_rawat))->oneArray();
        }
        $print_spri['nama_instansi'] = $this->settings->get('settings.nama_instansi');
        $print_spri['logoURL'] = url(MODULES . '/vclaim/img/bpjslogo.png');
        $this->tpl->set('print_spri', $print_spri);

        $resume_pasien = $this->db('resume_pasien')
          ->join('dokter', 'dokter.kd_dokter = resume_pasien.kd_dokter')
          ->where('no_rawat', $this->revertNorawat($id))
          ->oneArray();
        if(!$this->db('resume_pasien')->where('no_rawat', $this->revertNorawat($id))->oneArray()) {
          $resume_pasien = $this->db('resume_pasien_ranap')
            ->join('dokter', 'dokter.kd_dokter = resume_pasien_ranap.kd_dokter')
            ->where('no_rawat', $this->revertNorawat($id))
            ->oneArray();
        }
        $this->tpl->set('resume_pasien', $resume_pasien);

        $pasien = $this->db('pasien')
          ->join('kecamatan', 'kecamatan.kd_kec = pasien.kd_kec')
          ->join('kabupaten', 'kabupaten.kd_kab = pasien.kd_kab')
          ->where('no_rkm_medis', $this->getRegPeriksaInfo('no_rkm_medis', $this->revertNorawat($id)))
          ->oneArray();
        $reg_periksa = $this->db('reg_periksa')
          ->join('dokter', 'dokter.kd_dokter = reg_periksa.kd_dokter')
          ->join('poliklinik', 'poliklinik.kd_poli = reg_periksa.kd_poli')
          ->join('penjab', 'penjab.kd_pj = reg_periksa.kd_pj')
          ->where('stts', '<>', 'Batal')
          ->where('no_rawat', $this->revertNorawat($id))
          ->oneArray();
        $rujukan_internal = $this->db('rujukan_internal_poli')
          ->join('poliklinik', 'poliklinik.kd_poli = rujukan_internal_poli.kd_poli')
          ->join('dokter', 'dokter.kd_dokter = rujukan_internal_poli.kd_dokter')
          ->where('no_rawat', $this->revertNorawat($id))
          ->oneArray();
        $rows_dpjp_ranap = $this->db('dpjp_ranap')
          ->join('dokter', 'dokter.kd_dokter = dpjp_ranap.kd_dokter')
          ->where('no_rawat', $this->revertNorawat($id))
          ->toArray();
        $dpjp_i = 1;
        $dpjp_ranap = [];
        foreach ($rows_dpjp_ranap as $row) {
          $row['nomor'] = $dpjp_i++;
          $dpjp_ranap[] = $row;
        }
        $diagnosa_pasien = $this->db('diagnosa_pasien')
          ->join('penyakit', 'penyakit.kd_penyakit = diagnosa_pasien.kd_penyakit')
          ->where('no_rawat', $this->revertNorawat($id))
          ->toArray();
        $prosedur_pasien = $this->db('prosedur_pasien')
          ->join('icd9', 'icd9.kode = prosedur_pasien.kode')
          ->where('no_rawat', $this->revertNorawat($id))
          ->toArray();
        $pemeriksaan_ralan = $this->db('pemeriksaan_ralan')
          ->where('no_rawat', $this->revertNorawat($id))
          ->asc('tgl_perawatan')
          ->asc('jam_rawat')
          ->toArray();
        $pemeriksaan_ranap = $this->db('pemeriksaan_ranap')
          ->where('no_rawat', $this->revertNorawat($id))
          ->asc('tgl_perawatan')
          ->asc('jam_rawat')
          ->toArray();
        $rawat_jl_dr = $this->db('rawat_jl_dr')
          ->join('jns_perawatan', 'rawat_jl_dr.kd_jenis_prw=jns_perawatan.kd_jenis_prw')
          ->join('dokter', 'rawat_jl_dr.kd_dokter=dokter.kd_dokter')
          ->where('no_rawat', $this->revertNorawat($id))
          ->toArray();
        $rawat_jl_pr = $this->db('rawat_jl_pr')
          ->join('jns_perawatan', 'rawat_jl_pr.kd_jenis_prw=jns_perawatan.kd_jenis_prw')
          ->join('petugas', 'rawat_jl_pr.nip=petugas.nip')
          ->where('no_rawat', $this->revertNorawat($id))
          ->toArray();
        $rawat_jl_drpr = $this->db('rawat_jl_drpr')
          ->join('jns_perawatan', 'rawat_jl_drpr.kd_jenis_prw=jns_perawatan.kd_jenis_prw')
          ->join('dokter', 'rawat_jl_drpr.kd_dokter=dokter.kd_dokter')
          ->join('petugas', 'rawat_jl_drpr.nip=petugas.nip')
          ->where('no_rawat', $this->revertNorawat($id))
          ->toArray();
        $rawat_inap_dr = $this->db('rawat_inap_dr')
          ->join('jns_perawatan_inap', 'rawat_inap_dr.kd_jenis_prw=jns_perawatan_inap.kd_jenis_prw')
          ->join('dokter', 'rawat_inap_dr.kd_dokter=dokter.kd_dokter')
          ->where('no_rawat', $this->revertNorawat($id))
          ->toArray();
        $rawat_inap_pr = $this->db('rawat_inap_pr')
          ->join('jns_perawatan_inap', 'rawat_inap_pr.kd_jenis_prw=jns_perawatan_inap.kd_jenis_prw')
          ->join('petugas', 'rawat_inap_pr.nip=petugas.nip')
          ->where('no_rawat', $this->revertNorawat($id))
          ->toArray();
        $rawat_inap_drpr = $this->db('rawat_inap_drpr')
          ->join('jns_perawatan_inap', 'rawat_inap_drpr.kd_jenis_prw=jns_perawatan_inap.kd_jenis_prw')
          ->join('dokter', 'rawat_inap_drpr.kd_dokter=dokter.kd_dokter')
          ->join('petugas', 'rawat_inap_drpr.nip=petugas.nip')
          ->where('no_rawat', $this->revertNorawat($id))
          ->toArray();
        $kamar_inap = $this->db('kamar_inap')
          ->join('kamar', 'kamar_inap.kd_kamar=kamar.kd_kamar')
          ->join('bangsal', 'kamar.kd_bangsal=bangsal.kd_bangsal')
          ->where('no_rawat', $this->revertNorawat($id))
          ->toArray();
        $operasi = $this->db('operasi')
          ->join('paket_operasi', 'operasi.kode_paket=paket_operasi.kode_paket')
          ->where('no_rawat', $this->revertNorawat($id))
          ->toArray();
        $tindakan_radiologi = $this->db('periksa_radiologi')
          ->join('jns_perawatan_radiologi', 'periksa_radiologi.kd_jenis_prw=jns_perawatan_radiologi.kd_jenis_prw')
          ->join('dokter', 'periksa_radiologi.kd_dokter=dokter.kd_dokter')
          ->join('petugas', 'periksa_radiologi.nip=petugas.nip')
          ->where('no_rawat', $this->revertNorawat($id))
          ->toArray();
        $hasil_radiologi = $this->db('hasil_radiologi')
          ->where('no_rawat', $this->revertNorawat($id))
          ->toArray();
        $klinis_radiologi = $this->db('diagnosa_pasien_klinis')
          ->join('permintaan_radiologi', 'permintaan_radiologi.noorder=diagnosa_pasien_klinis.noorder')
          ->where('no_rawat', $this->revertNorawat($id))
          ->toArray();
        $saran_rad = $this->db('saran_kesan_rad')
          ->where('no_rawat', $this->revertNorawat($id))
          ->toArray();

        $pemeriksaan_laboratorium = [];
        $rows_pemeriksaan_laboratorium = $this->db('periksa_lab')
          ->join('jns_perawatan_lab', 'jns_perawatan_lab.kd_jenis_prw=periksa_lab.kd_jenis_prw')
          ->where('no_rawat', $this->revertNorawat($id))
          ->toArray();
        foreach ($rows_pemeriksaan_laboratorium as $value) {
          $value['detail_periksa_lab'] = $this->db('detail_periksa_lab')
            ->join('template_laboratorium', 'template_laboratorium.id_template=detail_periksa_lab.id_template')
            ->where('detail_periksa_lab.no_rawat', $value['no_rawat'])
            ->where('detail_periksa_lab.jam', $value['jam'])
            ->where('detail_periksa_lab.tgl_periksa', $value['tgl_periksa'])
            ->where('detail_periksa_lab.kd_jenis_prw', $value['kd_jenis_prw'])
            ->toArray();
          $pemeriksaan_laboratorium[] = $value;
        }
        $pemberian_obat = $this->db('detail_pemberian_obat')
          ->join('databarang', 'detail_pemberian_obat.kode_brng=databarang.kode_brng')
          ->where('no_rawat', $this->revertNorawat($id))
          ->toArray();
        $obat_operasi = $this->db('beri_obat_operasi')
          ->join('obatbhp_ok', 'beri_obat_operasi.kd_obat=obatbhp_ok.kd_obat')
          ->where('no_rawat', $this->revertNorawat($id))
          ->toArray();
        $resep_pulang = $this->db('resep_pulang')
          ->join('databarang', 'resep_pulang.kode_brng=databarang.kode_brng')
          ->where('no_rawat', $this->revertNorawat($id))
          ->toArray();
        $laporan_operasi = $this->db('laporan_operasi')
          ->where('no_rawat', $this->revertNorawat($id))
          ->oneArray();

        $rujukan_internal_poli_detail = $this->db('rujukan_internal_poli_detail')
          ->where('no_rawat', $this->revertNorawat($id))
          ->oneArray();
        $this->tpl->set('rujukan_internal_poli_detail', $rujukan_internal_poli_detail);
        
        $shk_bayi = $this->db('shk_bayi')
          ->where('no_rawat', $this->revertNorawat($id))
          ->oneArray();

        $lap_op = []; 
        $mlite_lap_op = $this->db('mlite_lap_op')
            ->join('dokter', 'dokter.kd_dokter=mlite_lap_op.kd_dokter')
            ->where('mlite_lap_op.no_rawat', $this->revertNorawat($id))
            ->isNull('deleted_at')
            ->desc('tanggal_op')
            ->toArray();

        foreach ($mlite_lap_op as $value) {
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
                ->where('operasi.no_rawat', $value['no_rawat'])
                ->where('operasi.tgl_operasi', $value['tanggal_op'])
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

            $value['jadwal_operasi'] = $jadwal_operasi;
            $lap_op[] = $value;
        } 

        $balance_cairan = [];
        $mlite_balance_cairan = $this->db('mlite_balance_cairan')
          ->where('no_rawat', $this->revertNorawat($id))
          ->isNull('deleted_at')
          ->group('tanggal')
          ->toArray();
          foreach ($mlite_balance_cairan as $value) {
             $value['hasil_bc'] = $this->db('mlite_balance_cairan')
                ->where('no_rawat', $value['no_rawat'])
                ->where('tanggal', $value['tanggal'])
                ->toArray();

              $value['total']  = $this->db('mlite_balance_cairan')
                ->select([
                  'no_rawat'    => 'no_rawat',
                  'tanggal'     => 'tanggal',
                  'total_intake'=> 'COALESCE(SUM(total_in), 0)',
                  'total_output'=> 'COALESCE(SUM(total_out), 0)',
                  'hasil_bc'    => 'COALESCE(SUM(total_in), 0) - COALESCE(SUM(total_out), 0)'])
                ->where('no_rawat', $value['no_rawat']) 
                ->where('tanggal', $value['tanggal'])
                ->toArray();
            $balance_cairan[] = $value;
          }

        $tindak_ventilator = $this->db('mlite_ventilator')
          ->where('mlite_ventilator.no_rawat', $this->revertNorawat($id))
          ->isNull('deleted_at')
          ->toArray(); 
          $ventilator = [];
          foreach ($tindak_ventilator as $value) {
          $value['nm_dokter'] = $this->db('dokter')
                ->where('kd_dokter', $value['kd_dokter'])
                ->toArray();

          $date1 = new \DateTime($value['intubasi']);
          $date2 = new \DateTime($value['ekstubasi']);

          $diff = $date2->diff($date1);

          $minutes = $diff->h + ($diff->days*24);
          $value['range'] = $minutes .' Jam';

          $ventilator[] = $value;
         } 

        $ekstrapiramidal = $this->db('mlite_ekstrapiramidal')
            ->where('no_rawat', $this->revertNorawat($id))
            ->isNull('deleted_at')
            ->toArray();

        $formatHasil = [];
        foreach ($ekstrapiramidal as &$value) {
            $hasil = json_decode($value['hasil'], true);

            $hasilPiramidal = [];

            foreach ($hasil as $key => $result) {
                $question = '';
                $answer = '';

                switch ($key) {
                                   case 'piramidal1':
                            $question = 'Perlambatan atau kelemahan yang nyata, ada kesan kesulitan dalam menjalankan tugas rutin';
                            break;
                        case 'piramidal2':
                            $question = 'Kesulitan dalam berjalan dan menjaga keseimbangan';
                            break;
                        case 'piramidal3':
                            $question = 'Kesulitan dalam menelan atau berbicara';
                            break;
                        case 'piramidal4':
                            $question = 'Kekakuan, postur tubuh kaku';
                            break;
                        case 'piramidal5':
                            $question = 'Kram atau nyeri pada anggota gerak, tulang belakang, dan atau leher';
                            break;
                        case 'piramidal6':
                            $question = 'Gelisah, nervous, tidak bisa diam';
                            break;
                        case 'piramidal7':
                            $question = 'Tremor, gemetar';
                            break;
                        case 'piramidal8':
                            $question = 'Krisis okulogirik atau postur tubuh yang abnormal yang dipertahankan';
                            break;
                        case 'piramidal9':
                            $question = 'Banyak ludah';
                            break;
                        case 'piramidal10':
                            $question = 'Gerakan-gerakan yang involunter yang abnormal (diskinesia) dari anggota gerak atau badan';
                            break;
                        case 'piramidal11':
                            $question = 'Gerakan-gerakan yang involunter yang abnormal (diskinesia) dari lidah, rahang, bibir atau muka';
                            break;
                        case 'piramidal12':
                            $question = 'Pusing pada saat berdiri (khususnya pada pagi hari)';
                            break;
                }

                switch ($result) {
                    case '1':
                        $answer = 'Tidak Ada';
                        break;
                    case '2':
                        $answer = 'Ringan';
                        break;
                    case '3':
                        $answer = 'Sedang';
                        break;
                    case '4':
                        $answer = 'Berat';
                        break;
                    default:
                        $answer = '';
                        break;
                }

                $hasilPiramidal[] = [
                    'Pertanyaan' => $question,
                    'Jawaban' => $answer,
                ];
            }

            $value['formatHasil'] = $hasilPiramidal;
            $formatHasil[] = $value;
        }

        $pasien_mati = $this->db('pasien_mati')
          ->where('no_rkm_medis', $this->core->getRegPeriksaInfo('no_rkm_medis', $this->revertNorawat($id)))
          ->oneArray();
        if ($pasien_mati) {
            $pasien_mati['tgl_lahir'] = date('d/m/Y', strtotime($pasien['tgl_lahir']));;
            $ruangan =  $this->db('kamar_inap')
              ->select(['ruang' => 'concat(kamar.kd_kamar," ",bangsal.nm_bangsal)',
                        'stts_pulang' => 'kamar_inap.stts_pulang'])
              ->join('kamar', 'kamar.kd_kamar=kamar_inap.kd_kamar')
              ->join('bangsal', 'bangsal.kd_bangsal=kamar.kd_bangsal')
              ->where('kamar_inap.no_rawat', $this->revertNorawat($id))
              ->desc('kamar_inap.tgl_masuk')
              ->limit(1)
              ->oneArray();
            $pasien_mati['ruangan'] = $ruangan['ruang'];
            $pasien_mati['stts'] = $ruangan['stts_pulang'];
            $pasien_mati['bulantahun_kematian'] = date('m/Y', strtotime($pasien_mati['tanggal']));
            $nm_inisial = $pasien['nm_pasien'];
            $pasien_mati['inisial'] = substr($nm_inisial, 0, 1);
            $ttl = date('d - m - Y', strtotime($pasien['tgl_lahir'])); $pasien_mati['ttl'] = 'tanggal ' . date('d', strtotime($pasien['tgl_lahir'])) . ' bulan ' . date('m', strtotime($pasien['tgl_lahir'])) . ' tahun ' . date('Y', strtotime($pasien['tgl_lahir']));
            $pasien_mati['waktu_meninggal'] = date('d/m/Y', strtotime($pasien_mati['tanggal']));
            $pasien_mati['umur_meninggal'] = $pasien['umur'];
            $pasien_mati['tglpemulasaran'] = date('d/m/Y', strtotime($pasien_mati['tgl_pemulasaran']));

            if ($reg_periksa['stts'] != 'DOA') {

              $lama_inap = $this->db('kamar_inap')->select(['lama' => 'SUM(lama)'])->where('no_rawat',  $this->revertNorawat($id))->oneArray();

              $tgl_igd = new \DateTime($reg_periksa['tgl_registrasi'] . ' ' . $reg_periksa['jam_reg']);
              $tgl_meninggal = new \DateTime($pasien_mati['tanggal'] . ' ' . $pasien_mati['jam']);

              $diff = $tgl_meninggal->diff($tgl_igd);
              $totalMinutes = $diff->h * 60 + $diff->i + ($diff->days * 24 * 60);

              $jam = floor($totalMinutes / 60);
              $menit = $totalMinutes % 60;
                if ($totalMinutes <= 46 * 60) {
                    // Jika kurang dari atau sama dengan 46 jam, tampilkan dalam jam dan menit
                    $pasien_mati['lama_dirawat'] = $jam . ' Jam ' . $menit . ' Menit';
                } else {
                    // Jika lebih dari 46 jam, tampilkan dalam hari
                    $lamainap = $lama_inap['lama'];
                    $pasien_mati['lama_dirawat'] = $lamainap. ' Hari';
                }
            } else {
                $pasien_mati['lama_dirawat'] = 'DOA';
            }

            $nmdok =  $this->db('dokter')->where('kd_dokter', $pasien_mati['kd_dokter'])->oneArray();
            $pasien_mati['nm_dokter'] = $nmdok['nm_dokter'];
        }

        $this->tpl->set('pasien', $pasien);
        $this->tpl->set('reg_periksa', $reg_periksa);
        $this->tpl->set('rujukan_internal', $rujukan_internal);
        $this->tpl->set('dpjp_ranap', $dpjp_ranap);
        $this->tpl->set('diagnosa_pasien', $diagnosa_pasien);
        $this->tpl->set('prosedur_pasien', $prosedur_pasien);
        $this->tpl->set('pemeriksaan_ralan', $pemeriksaan_ralan);
        $this->tpl->set('pemeriksaan_ranap', $pemeriksaan_ranap);
        $this->tpl->set('rawat_jl_dr', $rawat_jl_dr);
        $this->tpl->set('rawat_jl_pr', $rawat_jl_pr);
        $this->tpl->set('rawat_jl_drpr', $rawat_jl_drpr);
        $this->tpl->set('rawat_inap_dr', $rawat_inap_dr);
        $this->tpl->set('rawat_inap_pr', $rawat_inap_pr);
        $this->tpl->set('rawat_inap_drpr', $rawat_inap_drpr);
        $this->tpl->set('kamar_inap', $kamar_inap);
        $this->tpl->set('operasi', $operasi);
        $this->tpl->set('tindakan_radiologi', $tindakan_radiologi);
        $this->tpl->set('hasil_radiologi', $hasil_radiologi);
        $this->tpl->set('klinis_radiologi', $klinis_radiologi);
    	$this->tpl->set('saran_rad', $saran_rad);
        $this->tpl->set('pemeriksaan_laboratorium', $pemeriksaan_laboratorium);
        $this->tpl->set('pemberian_obat', $pemberian_obat);
        $this->tpl->set('obat_operasi', $obat_operasi);
        $this->tpl->set('resep_pulang', $resep_pulang);
        $this->tpl->set('laporan_operasi', $laporan_operasi);
        $this->tpl->set('berkas_digital', $berkas_digital);
        $this->tpl->set('berkas_digital_pasien', $berkas_digital_pasien);
        $this->tpl->set('shk_bayi', $shk_bayi);
        $this->tpl->set('lap_op', $lap_op);
        $this->tpl->set('balance_cairan', $balance_cairan);
        $this->tpl->set('ventilator', $ventilator);
        $this->tpl->set('ekstrapiramidal', $ekstrapiramidal);
        $this->tpl->set('pasien_mati', $pasien_mati);
        $this->tpl->set('hasil_radiologi', $this->db('hasil_radiologi')->where('no_rawat', $this->revertNorawat($id))->toArray());
        $this->tpl->set('gambar_radiologi', $this->db('gambar_radiologi')->where('no_rawat', $this->revertNorawat($id))->toArray());
        $this->tpl->set('vedika', htmlspecialchars_array($this->settings('vedika')));
        echo $this->tpl->draw(MODULES.'/vedika/view/pdf.html', true);
        exit();
      } else {
        redirect(url(['veda', '']));
      }
    }
  
  	public function getDataSep(){
      $sep = $this->db('temp_individu')->where('status','Belum')->limit(25)->toArray();
      if ($sep) {
        foreach ($sep as $value) {
          # code...
          $send = $this->getKirimDataCenterCrontab($value['no_sep'],$value['no_rawat']);
          echo $send.' | ';
          if ($send == 'Ok') {
            $this->db('temp_individu')->where('no_rawat',$value['no_rawat'])->save([
              'status' => 'Sukses',
            ]);
          } else {
            $this->db('temp_individu')->where('no_rawat',$value['no_rawat'])->save([
              'status' => $send,
            ]);
          }
        }
      } else {
        $begin = new \DateTime('2023-02-15');
        $end = new \DateTime('2023-02-19');

        $interval = \DateInterval::createFromDateString('1 day');
        $period = new \DatePeriod($begin, $interval, $end);
        foreach ($period as $dt) {
          $start_date = $dt->format("Y-m-d");
          // if(isset($_GET['startdate']) && $_GET['startdate'] !='')
          //   $start_date = $_GET['startdate'];
          $sep = $this->db('bridging_sep')->where('tglsep',$start_date)->toArray();
          foreach ($sep as $value) {
            $carisep = $this->db('temp_individu')->where('no_rawat',$value['no_rawat'])->oneArray();
            if (!$carisep) {
              $this->db('temp_individu')->save([
                'no_rawat' => $value['no_rawat'],
                'no_sep' => $value['no_sep'],
                'tglsep' => $value['tglsep'],
                'status' => 'Belum',
              ]);
            }
          }
          $sep = $this->db('temp_individu')->where('status','Belum')->limit(2)->toArray();
          foreach ($sep as $value) {
            # code...
            $send = $this->getKirimDataCenterCrontab($value['no_sep'],$value['no_rawat']);
            echo $send.' | ';
            if ($send == 'Ok') {
              $this->db('temp_individu')->where('no_rawat',$value['no_rawat'])->save([
                'status' => 'Sukses',
              ]);
            } else {
              $this->db('temp_individu')->where('no_rawat',$value['no_rawat'])->save([
                'status' => $send,
              ]);
            }
          }
        }
      }
      exit();
    }

    public function getKirimDataCenterCrontab($nosep,$norawat)
    {
      $status = 'Not Ok';
      $cntr   = 0;
      $imgTime = time() . $cntr++;
      $no_rawat = convertNorawat($norawat);
      $berkas_digital_perawatan = $this->db('berkas_digital_perawatan')->where('no_rawat', $norawat)->where('kode', '030')->oneArray();
      if(!$berkas_digital_perawatan) {

        $request = '{
                      "metadata": {
                        "method":"claim_print"
                      },
                      "data": {
                        "nomor_sep":"'.$nosep.'"
                      }
                    }';

        $msg = $this->Request($request);
        // print_r($msg);
        if($msg['metadata']['message']=="Ok"){
            $pdf = base64_decode($msg['data']);
            file_put_contents(WEBAPPS_PATH.'/berkasrawat/pages/upload/'.$no_rawat.'_'.$imgTime,$pdf);
            $status = 'Ok';
            $image = WEBAPPS_PATH.'/berkasrawat/pages/upload/' . $no_rawat . '_' . $imgTime;
            $imagick = new \Imagick();
            $imagick->readImage($image);
            $imagick->writeImages($image.'.jpg', false);
            unlink($image);
            $query = $this->db('berkas_digital_perawatan')->save(['no_rawat' => $norawat, 'kode' => '030', 'lokasi_file' => 'pages/upload/' . $no_rawat . '_' . $imgTime . '.jpg']);
        } else {
          // echo json_encode($msg, true);
          $status = $msg['metadata']['message'];
        }

      } else {
        $delete = $this->db('berkas_digital_perawatan')->where('no_rawat', $norawat)->where('kode', '030')->delete();
        if ($delete) {
          $request = '{
            "metadata": {
              "method":"claim_print"
            },
            "data": {
              "nomor_sep":"'.$nosep.'"
            }
          }';

          $msg = $this->Request($request);
          // print_r($msg);
          if($msg['metadata']['message']=="Ok"){
            $pdf = base64_decode($msg['data']);
            file_put_contents(WEBAPPS_PATH.'/berkasrawat/pages/upload/'.$no_rawat.'_'.$imgTime,$pdf);
            $status = 'Ok';
            $image = WEBAPPS_PATH.'/berkasrawat/pages/upload/' . $no_rawat . '_' . $imgTime;
            $imagick = new \Imagick();
            $imagick->readImage($image);
            $imagick->writeImages($image.'.jpg', false);
            unlink($image);
            $query = $this->db('berkas_digital_perawatan')->save(['no_rawat' => $norawat, 'kode' => '030', 'lokasi_file' => 'pages/upload/' . $no_rawat . '_' . $imgTime . '.jpg']);
          } else {
            $status = $msg['metadata']['message'];
          }

        }
      }
      return $status;
      exit();
    }
  
    private function Request($request)
    {
      $json = $this->mc_encrypt($request, $this->settings->get('vedika.eklaim_key'));
      $header = array("Content-Type: application/x-www-form-urlencoded");
      $ch = curl_init();
      curl_setopt($ch, CURLOPT_URL, $this->settings->get('vedika.eklaim_url'));
      curl_setopt($ch, CURLOPT_HEADER, 0);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
      curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
      curl_setopt($ch, CURLOPT_POST, 1);
      curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
      $response = curl_exec($ch);
      $first = strpos($response, "\n") + 1;
      $last = strrpos($response, "\n") - 1;
      $hasilresponse = substr($response, $first, strlen($response) - $first - $last);
      $hasildecrypt = $this->mc_decrypt($hasilresponse, $this->settings->get('vedika.eklaim_key'));
      //echo $hasildecrypt;
      $msg = json_decode($hasildecrypt, true);
      return $msg;
    }
  
    private function mc_encrypt($data, $strkey)
    {
      $key = hex2bin($strkey);
      if (mb_strlen($key, "8bit") !== 32) {
        throw new Exception("Needs a 256-bit key!");
      }
  
      $iv_size = openssl_cipher_iv_length("aes-256-cbc");
      $iv = openssl_random_pseudo_bytes($iv_size);
      $encrypted = openssl_encrypt($data, "aes-256-cbc", $key, OPENSSL_RAW_DATA, $iv);
      $signature = mb_substr(hash_hmac("sha256", $encrypted, $key, true), 0, 10, "8bit");
      $encoded = chunk_split(base64_encode($signature . $iv . $encrypted));
      return $encoded;
    }
  
    private function mc_decrypt($str, $strkey)
    {
      $key = hex2bin($strkey);
      if (mb_strlen($key, "8bit") !== 32) {
        throw new Exception("Needs a 256-bit key!");
      }
  
      $iv_size = openssl_cipher_iv_length("aes-256-cbc");
      $decoded = base64_decode($str);
      $signature = mb_substr($decoded, 0, 10, "8bit");
      $iv = mb_substr($decoded, 10, $iv_size, "8bit");
      $encrypted = mb_substr($decoded, $iv_size + 10, NULL, "8bit");
      $calc_signature = mb_substr(hash_hmac("sha256", $encrypted, $key, true), 0, 10, "8bit");
      if (!$this->mc_compare($signature, $calc_signature)) {
        return "SIGNATURE_NOT_MATCH";
      }
  
      $decrypted = openssl_decrypt($encrypted, "aes-256-cbc", $key, OPENSSL_RAW_DATA, $iv);
      return $decrypted;
    }
  
    private function mc_compare($a, $b)
    {
      if (strlen($a) !== strlen($b)) {
        return false;
      }
  
      $result = 0;
  
      for ($i = 0; $i < strlen($a); $i++) {
        $result |= ord($a[$i]) ^ ord($b[$i]);
      }
  
      return $result == 0;
    }

    public function getCreatePDF($id)
    {
        $berkas_digital = $this->db('berkas_digital_perawatan')
          ->join('master_berkas_digital', 'master_berkas_digital.kode=berkas_digital_perawatan.kode')
          ->where('berkas_digital_perawatan.no_rawat', $this->revertNorawat($id))
          ->asc('master_berkas_digital.nama')
          ->toArray();

        $galleri_pasien = $this->db('mlite_pasien_galleries_items')
          ->join('mlite_pasien_galleries', 'mlite_pasien_galleries.id = mlite_pasien_galleries_items.gallery')
          ->where('mlite_pasien_galleries.slug', $this->getRegPeriksaInfo('no_rkm_medis', $this->revertNorawat($id)))
          ->toArray();

        $berkas_digital_pasien = array();
        if (count($galleri_pasien)) {
            foreach ($galleri_pasien as $galleri) {
                $galleri['src'] = unserialize($galleri['src']);

                if (!isset($galleri['src']['sm'])) {
                    $galleri['src']['sm'] = isset($galleri['src']['xs']) ? $galleri['src']['xs'] : $galleri['src']['lg'];
                }

                $berkas_digital_pasien[] = $galleri;
            }
        }

        $no_rawat = $this->revertNorawat($id);
        $query = $this->db()->pdo()->prepare("select no,nm_perawatan,pemisah,if(biaya=0,'',biaya),if(jumlah=0,'',jumlah),if(tambahan=0,'',tambahan),if(totalbiaya=0,'',totalbiaya),totalbiaya from billing where no_rawat='$no_rawat'");
        $query->execute();
        $rows = $query->fetchAll();
        $total=0;
        foreach ($rows as $key => $value) {
          $total = $total+$value['7'];
        }
        $total = $total;
        $this->tpl->set('total', $total);

        $settings = $this->settings('settings');
        $this->tpl->set('settings', $this->tpl->noParse_array(htmlspecialchars_array($settings)));

        $this->tpl->set('billing', $rows);

        $print_sep = array();
        if(!empty($this->_getSEPInfo('no_sep', $no_rawat))) {
          $print_sep['bridging_sep'] = $this->db('bridging_sep')->where('no_sep', $this->_getSEPInfo('no_sep', $no_rawat))->oneArray();
          $print_sep['bpjs_prb'] = $this->db('bpjs_prb')->where('no_sep', $this->_getSEPInfo('no_sep', $no_rawat))->oneArray();
          $batas_rujukan = $this->db('bridging_sep')->select('DATE_ADD(tglrujukan , INTERVAL 85 DAY) AS batas_rujukan')->where('no_sep', $id)->oneArray();
          $print_sep['batas_rujukan'] = $batas_rujukan['batas_rujukan'];
        }

        $print_sep['logoURL'] = url(MODULES.'/pendaftaran/img/bpjslogo.png');
        $this->tpl->set('print_sep', $print_sep);

        $resume_pasien = $this->db('resume_pasien')
          ->join('dokter', 'dokter.kd_dokter = resume_pasien.kd_dokter')
          ->where('no_rawat', $this->revertNorawat($id))
          ->oneArray();
        $this->tpl->set('resume_pasien', $resume_pasien);

        $pasien = $this->db('pasien')
          ->join('kecamatan', 'kecamatan.kd_kec = pasien.kd_kec')
          ->join('kabupaten', 'kabupaten.kd_kab = pasien.kd_kab')
          ->where('no_rkm_medis', $this->getRegPeriksaInfo('no_rkm_medis', $this->revertNorawat($id)))
          ->oneArray();
        $reg_periksa = $this->db('reg_periksa')
          ->join('dokter', 'dokter.kd_dokter = reg_periksa.kd_dokter')
          ->join('poliklinik', 'poliklinik.kd_poli = reg_periksa.kd_poli')
          ->join('penjab', 'penjab.kd_pj = reg_periksa.kd_pj')
          ->where('stts', '<>', 'Batal')
          ->where('no_rawat', $this->revertNorawat($id))
          ->oneArray();
        $rujukan_internal = $this->db('rujukan_internal_poli')
          ->join('poliklinik', 'poliklinik.kd_poli = rujukan_internal_poli.kd_poli')
          ->join('dokter', 'dokter.kd_dokter = rujukan_internal_poli.kd_dokter')
          ->where('no_rawat', $this->revertNorawat($id))
          ->oneArray();
        $rows_dpjp_ranap = $this->db('dpjp_ranap')
          ->join('dokter', 'dokter.kd_dokter = dpjp_ranap.kd_dokter')
          ->where('no_rawat', $this->revertNorawat($id))
          ->toArray();
        $dpjp_i = 1;
        $dpjp_ranap = [];
        foreach ($rows_dpjp_ranap as $row) {
          $row['nomor'] = $dpjp_i++;
          $dpjp_ranap[] = $row;
        }
        $diagnosa_pasien = $this->db('diagnosa_pasien')
          ->join('penyakit', 'penyakit.kd_penyakit = diagnosa_pasien.kd_penyakit')
          ->where('no_rawat', $this->revertNorawat($id))
          ->toArray();
        $prosedur_pasien = $this->db('prosedur_pasien')
          ->join('icd9', 'icd9.kode = prosedur_pasien.kode')
          ->where('no_rawat', $this->revertNorawat($id))
          ->toArray();
        $pemeriksaan_ralan = $this->db('pemeriksaan_ralan')
          ->where('no_rawat', $this->revertNorawat($id))
          ->asc('tgl_perawatan')
          ->asc('jam_rawat')
          ->toArray();
        $pemeriksaan_ranap = $this->db('pemeriksaan_ranap')
          ->where('no_rawat', $this->revertNorawat($id))
          ->asc('tgl_perawatan')
          ->asc('jam_rawat')
          ->toArray();
        $rawat_jl_dr = $this->db('rawat_jl_dr')
          ->join('jns_perawatan', 'rawat_jl_dr.kd_jenis_prw=jns_perawatan.kd_jenis_prw')
          ->join('dokter', 'rawat_jl_dr.kd_dokter=dokter.kd_dokter')
          ->where('no_rawat', $this->revertNorawat($id))
          ->toArray();
        $rawat_jl_pr = $this->db('rawat_jl_pr')
          ->join('jns_perawatan', 'rawat_jl_pr.kd_jenis_prw=jns_perawatan.kd_jenis_prw')
          ->join('petugas', 'rawat_jl_pr.nip=petugas.nip')
          ->where('no_rawat', $this->revertNorawat($id))
          ->toArray();
        $rawat_jl_drpr = $this->db('rawat_jl_drpr')
          ->join('jns_perawatan', 'rawat_jl_drpr.kd_jenis_prw=jns_perawatan.kd_jenis_prw')
          ->join('dokter', 'rawat_jl_drpr.kd_dokter=dokter.kd_dokter')
          ->join('petugas', 'rawat_jl_drpr.nip=petugas.nip')
          ->where('no_rawat', $this->revertNorawat($id))
          ->toArray();
        $rawat_inap_dr = $this->db('rawat_inap_dr')
          ->join('jns_perawatan_inap', 'rawat_inap_dr.kd_jenis_prw=jns_perawatan_inap.kd_jenis_prw')
          ->join('dokter', 'rawat_inap_dr.kd_dokter=dokter.kd_dokter')
          ->where('no_rawat', $this->revertNorawat($id))
          ->toArray();
        $rawat_inap_pr = $this->db('rawat_inap_pr')
          ->join('jns_perawatan_inap', 'rawat_inap_pr.kd_jenis_prw=jns_perawatan_inap.kd_jenis_prw')
          ->join('petugas', 'rawat_inap_pr.nip=petugas.nip')
          ->where('no_rawat', $this->revertNorawat($id))
          ->toArray();
        $rawat_inap_drpr = $this->db('rawat_inap_drpr')
          ->join('jns_perawatan_inap', 'rawat_inap_drpr.kd_jenis_prw=jns_perawatan_inap.kd_jenis_prw')
          ->join('dokter', 'rawat_inap_drpr.kd_dokter=dokter.kd_dokter')
          ->join('petugas', 'rawat_inap_drpr.nip=petugas.nip')
          ->where('no_rawat', $this->revertNorawat($id))
          ->toArray();
        $kamar_inap = $this->db('kamar_inap')
          ->join('kamar', 'kamar_inap.kd_kamar=kamar.kd_kamar')
          ->join('bangsal', 'kamar.kd_bangsal=bangsal.kd_bangsal')
          ->where('no_rawat', $this->revertNorawat($id))
          ->toArray();
        $operasi = $this->db('operasi')
          ->join('paket_operasi', 'operasi.kode_paket=paket_operasi.kode_paket')
          ->where('no_rawat', $this->revertNorawat($id))
          ->toArray();
        $tindakan_radiologi = $this->db('periksa_radiologi')
          ->join('jns_perawatan_radiologi', 'periksa_radiologi.kd_jenis_prw=jns_perawatan_radiologi.kd_jenis_prw')
          ->join('dokter', 'periksa_radiologi.kd_dokter=dokter.kd_dokter')
          ->join('petugas', 'periksa_radiologi.nip=petugas.nip')
          ->where('no_rawat', $this->revertNorawat($id))
          ->toArray();
        $hasil_radiologi = $this->db('hasil_radiologi')
          ->where('no_rawat', $this->revertNorawat($id))
          ->toArray();
        $pemeriksaan_laboratorium = [];
        $rows_pemeriksaan_laboratorium = $this->db('periksa_lab')
          ->join('jns_perawatan_lab', 'jns_perawatan_lab.kd_jenis_prw=periksa_lab.kd_jenis_prw')
          ->where('no_rawat', $this->revertNorawat($id))
          ->toArray();
        foreach ($rows_pemeriksaan_laboratorium as $value) {
          $value['detail_periksa_lab'] = $this->db('detail_periksa_lab')
            ->join('template_laboratorium', 'template_laboratorium.id_template=detail_periksa_lab.id_template')
            ->where('detail_periksa_lab.no_rawat', $value['no_rawat'])
            ->where('detail_periksa_lab.kd_jenis_prw', $value['kd_jenis_prw'])
            ->toArray();
          $pemeriksaan_laboratorium[] = $value;
        }
        $pemberian_obat = $this->db('detail_pemberian_obat')
          ->join('databarang', 'detail_pemberian_obat.kode_brng=databarang.kode_brng')
          ->where('no_rawat', $this->revertNorawat($id))
          ->toArray();
        $obat_operasi = $this->db('beri_obat_operasi')
          ->join('obatbhp_ok', 'beri_obat_operasi.kd_obat=obatbhp_ok.kd_obat')
          ->where('no_rawat', $this->revertNorawat($id))
          ->toArray();
        $resep_pulang = $this->db('resep_pulang')
          ->join('databarang', 'resep_pulang.kode_brng=databarang.kode_brng')
          ->where('no_rawat', $this->revertNorawat($id))
          ->toArray();
        $laporan_operasi = $this->db('laporan_operasi')
          ->where('no_rawat', $this->revertNorawat($id))
          ->oneArray();

        $this->tpl->set('pasien', $pasien);
        $this->tpl->set('reg_periksa', $reg_periksa);
        $this->tpl->set('rujukan_internal', $rujukan_internal);
        $this->tpl->set('dpjp_ranap', $dpjp_ranap);
        $this->tpl->set('diagnosa_pasien', $diagnosa_pasien);
        $this->tpl->set('prosedur_pasien', $prosedur_pasien);
        $this->tpl->set('pemeriksaan_ralan', $pemeriksaan_ralan);
        $this->tpl->set('pemeriksaan_ranap', $pemeriksaan_ranap);
        $this->tpl->set('rawat_jl_dr', $rawat_jl_dr);
        $this->tpl->set('rawat_jl_pr', $rawat_jl_pr);
        $this->tpl->set('rawat_jl_drpr', $rawat_jl_drpr);
        $this->tpl->set('rawat_inap_dr', $rawat_inap_dr);
        $this->tpl->set('rawat_inap_pr', $rawat_inap_pr);
        $this->tpl->set('rawat_inap_drpr', $rawat_inap_drpr);
        $this->tpl->set('kamar_inap', $kamar_inap);
        $this->tpl->set('operasi', $operasi);
        $this->tpl->set('tindakan_radiologi', $tindakan_radiologi);
        $this->tpl->set('hasil_radiologi', $hasil_radiologi);
        $this->tpl->set('pemeriksaan_laboratorium', $pemeriksaan_laboratorium);
        $this->tpl->set('pemberian_obat', $pemberian_obat);
        $this->tpl->set('obat_operasi', $obat_operasi);
        $this->tpl->set('resep_pulang', $resep_pulang);
        $this->tpl->set('laporan_operasi', $laporan_operasi);

        $this->tpl->set('berkas_digital', $berkas_digital);
        $this->tpl->set('berkas_digital_pasien', $berkas_digital_pasien);
        $this->tpl->set('hasil_radiologi', $this->db('hasil_radiologi')->where('no_rawat', $this->revertNorawat($id))->oneArray());
        $this->tpl->set('gambar_radiologi', $this->db('gambar_radiologi')->where('no_rawat', $this->revertNorawat($id))->toArray());
        $this->tpl->set('vedika', htmlspecialchars_array($this->settings('vedika')));
        echo $this->tpl->draw(MODULES.'/vedika/view/pdf.html', true);
        exit();
    }

    public function getCatatan($id)
    {
      $set_status = $this->db('bridging_sep')->where('no_sep', $id)->oneArray();
      $vedika = $this->db('mlite_vedika')->where('nosep', $id)->asc('id')->toArray();
      $rows_vedika_feedback = $this->db('mlite_vedika_feedback')->where('nosep', $id)->asc('id')->toArray();
      foreach($rows_vedika_feedback as $row) {
        $users_vedika = $this->db('mlite_users_vedika')->where('username', $row['username'])->oneArray();
        $users_login = $this->db('mlite_users')->where('username', $row['username'])->oneArray();
        if($users_vedika) {
          $row['fullname'] = $users_vedika['fullname'];
          $row['logo'] = url().'/assets/img/avatar-bpjs.png';
          $row['deleteUrl'] = url(['veda', 'delfeed', $row['nosep'], $row['username']]);
        } else {
          $row['fullname'] = $users_login['fullname'];
          $row['logo'] = url().'/'.$this->settings->get('settings.logo');
          $row['deleteUrl'] = '';
        }
        $vedika_feedback[] = $row;
      }
      $this->tpl->set('nama_instansi', $this->settings->get('settings.nama_instansi'));
      $this->tpl->set('set_status', $set_status);
      $this->tpl->set('vedika', $vedika);
      $this->tpl->set('vedika_feedback', $vedika_feedback);
      echo $this->tpl->draw(MODULES.'/vedika/view/catatan.html', true);
      exit();
    }

  	public function getDelFeed($id,$user)
    {
  		$delete = $this->db('mlite_vedika_feedback')->where('nosep', $id)->where('username',$user)->delete();
   		if($delete)
          header('Location: '.$_SERVER['REQUEST_URI']);
    }

    public function getDownloadPDF($id)
    {
      $apikey = 'c811af07-d551-40ec-8e87-9abbf03abe16';
      $value = url().'/veda/createpdf/'.$id; // can aso be a url, starting with http..

      $bridging_sep = $this->db('bridging_sep')->where('no_rawat', $this->revertNorawat($id))->oneArray();

      // Convert the HTML string to a PDF using those parameters.  Note if you have a very long HTML string use POST rather than get.  See example #5
      $result = file_get_contents("http://url2pdf.basoro.id/?apikey=" . urlencode($apikey) . "&url=" . urlencode($value));

      // Save to root folder in website
      //file_put_contents('mypdf-1.pdf', $result);

      // Output headers so that the file is downloaded rather than displayed
      // Remember that header() must be called before any actual output is sent
      header('Content-Description: File Transfer');
      header('Content-Type: application/pdf');
      header('Expires: 0');
      header('Cache-Control: must-revalidate');
      header('Pragma: public');
      header('Content-Length: ' . strlen($result));

      // Make the file a downloadable attachment - comment this out to show it directly inside the
      // web browser.  Note that you can give the file any name you want, e.g. alias-name.pdf below:
      header('Content-Disposition: attachment; filename=' . 'e-vedika-'.$bridging_sep['tglsep'].'-'.$bridging_sep['no_sep'].'.pdf' );

      // Stream PDF to user
      echo $result;
      exit();
    }

    private function _getSEPInfo($field, $no_rawat)
    {
        $row = $this->db('bridging_sep')->where('no_rawat', $no_rawat)->oneArray();
        return $row[$field];
    }

  	private function _getSPRIInfo($field, $no_rawat)
    {
      $row = $this->db('bridging_surat_pri_bpjs')->where('no_rawat', $no_rawat)->oneArray();
      return $row[$field];
    }

    private function _getDiagnosa($field, $no_rawat, $status_lanjut)
    {
        $row = $this->db('diagnosa_pasien')->join('penyakit', 'penyakit.kd_penyakit = diagnosa_pasien.kd_penyakit')->where('diagnosa_pasien.no_rawat', $no_rawat)->where('diagnosa_pasien.prioritas', 1)->where('diagnosa_pasien.status', $status_lanjut)->oneArray();
        return $row[$field];
    }

    private function _getProsedur($field, $no_rawat, $status_lanjut)
    {
        $row = $this->db('prosedur_pasien')->join('icd9', 'icd9.kode = prosedur_pasien.kode')->where('prosedur_pasien.no_rawat', $no_rawat)->where('prosedur_pasien.prioritas', 1)->where('prosedur_pasien.status', $status_lanjut)->oneArray();
        return $row[$field];
    }

    public function getRegPeriksaInfo($field, $no_rawat)
    {
        $row = $this->db('reg_periksa')->where('no_rawat', $no_rawat)->oneArray();
        return $row[$field];
    }

    public function convertNorawat($text)
    {
        setlocale(LC_ALL, 'en_EN');
        $text = str_replace('/', '', trim($text));
        return $text;
    }

    public function revertNorawat($text)
    {
        setlocale(LC_ALL, 'en_EN');
        $tahun = substr($text, 0, 4);
        $bulan = substr($text, 4, 2);
        $tanggal = substr($text, 6, 2);
        $nomor = substr($text, 8, 6);
        $result = $tahun.'/'.$bulan.'/'.$tanggal.'/'.$nomor;
        return $result;
    }

    private function _login($username, $password)
    {
        // Check attempt
        $attempt = $this->db('mlite_login_attempts')->where('ip', $_SERVER['REMOTE_ADDR'])->oneArray();

        // Create attempt if does not exist
        if (!$attempt) {
            $this->db('mlite_login_attempts')->save(['ip' => $_SERVER['REMOTE_ADDR'], 'attempts' => 0]);
            $attempt = ['ip' => $_SERVER['REMOTE_ADDR'], 'attempts' => 0, 'expires' => 0];
        } else {
            $attempt['attempts'] = intval($attempt['attempts']);
            $attempt['expires'] = intval($attempt['expires']);
        }

        //$row_username = $this->settings->get('vedika.username');
        //$row_password = $this->settings->get('vedika.password');
        $users_vedika = $this->db('mlite_users_vedika')->where('username', $username)->oneArray();
        $row_username = $users_vedika['username'];
        $row_password = $users_vedika['password'];

        if ($row_username == $username && $row_password == $password) {
            // Reset fail attempts for this IP
            $this->db('mlite_login_attempts')->where('ip', $_SERVER['REMOTE_ADDR'])->save(['attempts' => 0]);

            $_SESSION['vedika_user']       = $row_username;
            $_SESSION['vedika_token']      = bin2hex(openssl_random_pseudo_bytes(6));
            $_SESSION['vedika_userAgent']  = $_SERVER['HTTP_USER_AGENT'];
            $_SESSION['vedika_IPaddress']  = $_SERVER['REMOTE_ADDR'];

            return true;
        } else {
            // Increase attempt
            $this->db('mlite_login_attempts')->where('ip', $_SERVER['REMOTE_ADDR'])->save(['attempts' => $attempt['attempts']+1]);
            $attempt['attempts'] += 1;

            // ... and block if reached maximum attempts
            if ($attempt['attempts'] % 3 == 0) {
                $this->db('mlite_login_attempts')->where('ip', $_SERVER['REMOTE_ADDR'])->save(['expires' => strtotime("+10 minutes")]);
                $attempt['expires'] = strtotime("+10 minutes");

                $this->core->setNotify('failure', sprintf('Batas maksimum login tercapai. Tunggu %s menit untuk coba lagi.', ceil(($attempt['expires']-time())/60)));
            } else {
                $this->core->setNotify('failure', 'Username atau password salah!');
            }

            return false;
        }
    }

    private function _loginCheck()
    {
        if (isset($_SESSION['vedika_user']) && isset($_SESSION['vedika_token']) && isset($_SESSION['vedika_userAgent']) && isset($_SESSION['vedika_IPaddress'])) {
            if ($_SESSION['vedika_IPaddress'] != $_SERVER['REMOTE_ADDR']) {
                return false;
            }
            if ($_SESSION['vedika_userAgent'] != $_SERVER['HTTP_USER_AGENT']) {
                return false;
            }

            if (empty(parseURL(1))) {
                redirect(url('veda'));
            } elseif (!isset($_GET['t']) || ($_SESSION['vedika_token'] != @$_GET['t'])) {
                return false;
            }

            return true;
        }

        return false;
    }

    private function logout()
    {
        unset($_SESSION['vedika_user']);
        unset($_SESSION['vedika_token']);
        unset($_SESSION['vedika_userAgent']);
        unset($_SESSION['vedika_IPaddress']);

        redirect(url('veda'));
    }

    public function getJavascript()
    {
        header('Content-type: text/javascript');
        echo $this->draw(MODULES.'/vedika/js/scripts.js');
        exit();
    }

    public function getCss()
    {
        header('Content-type: text/css');
        echo $this->draw(MODULES.'/vedika/css/styles.css');
        exit();
    }

    private function _addHeaderFiles()
    {
        // CSS
        $this->core->addCSS(url('assets/css/jquery-ui.css'));
        $this->core->addCSS(url('assets/css/jquery.timepicker.css'));

        // JS
        $this->core->addJS(url('assets/jscripts/jquery-ui.js'), 'footer');
        $this->core->addJS(url('assets/jscripts/jquery.timepicker.js'), 'footer');

        // MODULE SCRIPTS
        $this->core->addCSS(url(['veda', 'css']));
        $this->core->addJS(url(['veda', 'javascript']), 'footer');
    }

}
