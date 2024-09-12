<?php

namespace Plugins\Laporan_Bundles;

use Plugins\Master\Src\Kamar;
use Systems\AdminModule;

class Admin extends AdminModule
{

  public function navigation()
  {
    return [
      'Manage' => 'manage',
      'Bulanan Bundles HAIs' => 'laporanbundles',
      'Bundles Per Kamar/Bangsal' => 'bundlesbangsal',
      'Bundles IGD' => 'bundlesigd',
      'Bundles Kamar' => 'bundlestanggal'

    ];
  }

  public function getManage()
  {
    $sub_modules = [
      ['name' => 'Bulanan Bundles', 'url' => url([ADMIN, 'laporan_bundles', 'laporanbundles']), 'icon' => 'list-alt', 'desc' => 'Laporan Bundles HAIs'],
      ['name' => 'Bundles Per Bangsal', 'url' => url([ADMIN, 'laporan_bundles', 'bundlesbangsal']), 'icon' => 'list-alt', 'desc' => 'Bundles Per Kamar/Bangsal'],
      ['name' => 'Laporan Bundles IGD', 'url' => url([ADMIN, 'laporan_bundles', 'bundlesigd']), 'icon' => 'list-alt', 'desc' => 'Bundles IGD'],
      ['name' => 'Laporan Bundles Kamar', 'url' => url([ADMIN, 'laporan_bundles', 'bundlestanggal']), 'icon' => 'list-alt', 'desc' => 'Bundles Pertanggal']

    ];
    return $this->draw('manage.html', ['sub_modules' => $sub_modules]);
  }

  public function getLaporanBundles()
  {

    $this->_addHeaderFiles();
    $this->core->addCSS(url('https://cdn.datatables.net/buttons/2.2.3/css/buttons.dataTables.min.css'));
    $this->core->addJS(url('https://cdn.datatables.net/buttons/2.2.3/js/dataTables.buttons.min.js'), 'footer');
    $this->core->addJS(url('https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js'), 'footer');
    $this->core->addJS(url('https://cdn.datatables.net/buttons/1.3.1/js/buttons.html5.min.js'), 'footer');
    $this->core->addJS(url('https://cdn.datatables.net/plug-ins/1.12.1/api/sum().js'), 'footer');
    $this->core->addJS(url('https://cdn.datatables.net/datetime/1.1.2/js/dataTables.dateTime.min.js'), 'footer');

    $sql = "SELECT bundles_hais.tanggal FROM bundles_hais WHERE DATE_FORMAT(bundles_hais.tanggal, '%Y-%m') = DATE_FORMAT(CURDATE(), '%Y-%m') GROUP BY bundles_hais.tanggal";
    $stmt = $this->db()->pdo()->prepare($sql);
    $stmt->execute();
    $rows = $stmt->fetchAll();

    $this->assign['list'] = [];
    foreach ($rows as $row) {
      $row['jumlah'] = $this->db('bundles_hais')
          ->select([
          'jml_no_rawat' => 'count(bundles_hais.no_rawat)',

          'jml_hand_vap' => 'sum(bundles_hais.hand_vap = 1)',
          'jml_tehniksteril_vap' => 'sum(bundles_hais.tehniksteril_vap = 1)',
          'jml_apd_vap'  => 'sum(bundles_hais.apd_vap = 1)',
          'jml_sedasi_vap' => 'sum(bundles_hais.sedasi_vap = 1)',
          'tdk_hand_vap' => 'sum(bundles_hais.hand_vap = 0)',
          'tdk_apd_vap'  => 'sum(bundles_hais.apd_vap = 0)',
          'tdk_tehniksteril_vap' => 'sum(bundles_hais.tehniksteril_vap = 0)',
          'tdk_sedasi_vap' => 'sum(bundles_hais.sedasi_vap = 0)',

          'jml_hand_iadp' => 'sum(bundles_hais.hand_iadp = 1)',
          'jml_area_iadp' => 'sum(bundles_hais.area_iadp = 1)',
          'jml_tehniksteril_iadp' => 'sum(bundles_hais.tehniksteril_iadp = 1)',
          'jml_alcohol_iadp'  => 'sum(bundles_hais.alcohol_iadp = 1)',
          'jml_apd_iadp' => 'sum(bundles_hais.apd_iadp = 0)',
          'tdk_hand_iadp' => 'sum(bundles_hais.hand_iadp = 0)',
          'tdk_area_iadp' => 'sum(bundles_hais.area_iadp = 0)',
          'tdk_tehniksteril_iadp' => 'sum(bundles_hais.tehniksteril_iadp = 0)',
          'tdk_alcohol_iadp'  => 'sum(bundles_hais.alcohol_iadp = 0)',
          'tdk_apd_iadp' => 'sum(bundles_hais.apd_iadp = 0)',

          'jml_hand_vena' => 'sum(bundles_hais.hand_vena = 1)',
          'jml_kaji_vena'  => 'sum(bundles_hais.kaji_vena = 1)',
          'jml_tehnik_vena'  => 'sum(bundles_hais.tehnik_vena = 1)',
          'jml_petugas_vena' => 'sum(bundles_hais.petugas_vena = 1)',
          'jml_desinfeksi_vena' => 'sum(bundles_hais.desinfeksi_vena = 1)',
          'tdk_hand_vena' => 'sum(bundles_hais.hand_vena = 0)',
          'tdk_kaji_vena'  => 'sum(bundles_hais.kaji_vena = 0)',
          'tdk_tehnik_vena'  => 'sum(bundles_hais.tehnik_vena = 0)',
          'tdk_petugas_vena' => 'sum(bundles_hais.petugas_vena = 0)',
          'tdk_desinfeksi_vena' => 'sum(bundles_hais.desinfeksi_vena = 0)',

          'jml_kaji_isk' => 'sum(bundles_hais.kaji_isk = 1)',
          'jml_petugas_isk'  => 'sum(bundles_hais.petugas_isk = 1)',
          'jml_tangan_isk' => 'sum(bundles_hais.tangan_isk = 1)',
          'jml_tehniksteril_isk' => 'sum(bundles_hais.tehniksteril_isk = 1)',
          'tdk_kaji_isk' => 'sum(bundles_hais.kaji_isk = 0)',
          'tdk_petugas_isk'  => 'sum(bundles_hais.petugas_isk = 0)',
          'tdk_tangan_isk' => 'sum(bundles_hais.tangan_isk = 0)',
          'tdk_tehniksteril_isk' => 'sum(bundles_hais.tehniksteril_isk = 0)',

          'jml_hand_mainvap'  => 'sum(bundles_hais.hand_mainvap = 1)',
          'jml_oral_mainvap' => 'sum(bundles_hais.oral_mainvap = 1)',
          'jml_manage_mainvap' => 'sum(bundles_hais.manage_mainvap= 1)',
          'jml_sedasi_mainvap'   => 'sum(bundles_hais.sedasi_mainvap= 1)',
          'jml_kepala_mainvap'  => 'sum(bundles_hais.kepala_mainvap= 1)',
          'tdk_hand_mainvap'  => 'sum(bundles_hais.hand_mainvap = 0)',
          'tdk_oral_mainvap' => 'sum(bundles_hais.oral_mainvap = 0)',
          'tdk_manage_mainvap' => 'sum(bundles_hais.manage_mainvap= 0)',
          'tdk_sedasi_mainvap'   => 'sum(bundles_hais.sedasi_mainvap= 0)',
          'tdk_kepala_mainvap'  => 'sum(bundles_hais.kepala_mainvap= 0)',

          'jml_hand_mainiadp' => 'sum(bundles_hais.hand_mainiadp= 1)',
          'jml_desinfeksi_mainiadp'  => 'sum(bundles_hais.desinfeksi_mainiadp= 1)',
          'jml_perawatan_mainiadp' => 'sum(bundles_hais.perawatan_mainiadp= 1)',
          'jml_dreasing_mainiadp'  => 'sum(bundles_hais.dreasing_mainiadp= 1)',
          'jml_infus_mainiadp'  => 'sum(bundles_hais.infus_mainiadp= 1)',
          'tdk_hand_mainiadp' => 'sum(bundles_hais.hand_mainiadp= 0)',
          'tdk_desinfeksi_mainiadp'  => 'sum(bundles_hais.desinfeksi_mainiadp= 0)',
          'tdk_perawatan_mainiadp' => 'sum(bundles_hais.perawatan_mainiadp= 0)',
          'tdk_dreasing_mainiadp'  => 'sum(bundles_hais.dreasing_mainiadp= 0)',
          'tdk_infus_mainiadp'  => 'sum(bundles_hais.infus_mainiadp= 0)',

          'jml_hand_mainvena'  => 'sum(bundles_hais.hand_mainvena= 1)',
          'jml_perawatan_mainvena' => 'sum(bundles_hais.perawatan_mainvena= 1)',
          'jml_kaji_mainvena' => 'sum(bundles_hais.kaji_mainvena= 1)',
          'jml_administrasi_mainvena' => 'sum(bundles_hais.administrasi_mainvena= 1)',
          'jml_edukasi_mainvena' => 'sum(bundles_hais.edukasi_mainvena= 1)',
          'tdk_hand_mainvena'  => 'sum(bundles_hais.hand_mainvena= 0)',
          'tdk_perawatan_mainvena' => 'sum(bundles_hais.perawatan_mainvena= 0)',
          'tdk_kaji_mainvena' => 'sum(bundles_hais.kaji_mainvena= 0)',
          'tdk_administrasi_mainvena' => 'sum(bundles_hais.administrasi_mainvena= 0)',
          'tdk_edukasi_mainvena' => 'sum(bundles_hais.edukasi_mainvena= 0)',

          'jml_hand_mainisk' => 'sum(bundles_hais.hand_mainisk= 1)',
          'jml_kateter_mainisk' => 'sum(bundles_hais.kateter_mainisk= 1)',
          'jml_baglantai_mainisk' => 'sum(bundles_hais.baglantai_mainisk= 1)',
          'jml_bagrendah_mainisk' => 'sum(bundles_hais.bagrendah_mainisk= 1)',
          'jml_posisiselang_mainisk'  => 'sum(bundles_hais.posisiselang_mainisk= 1)',
          'jml_lepas_mainisk'   => 'sum(bundles_hais.lepas_mainisk= 1)',
          'tdk_hand_mainisk' => 'sum(bundles_hais.hand_mainisk= 0)',
          'tdk_kateter_mainisk' => 'sum(bundles_hais.kateter_mainisk= 0)',
          'tdk_baglantai_mainisk' => 'sum(bundles_hais.baglantai_mainisk= 0)',
          'tdk_bagrendah_mainisk' => 'sum(bundles_hais.bagrendah_mainisk= 0)',
          'tdk_posisiselang_mainisk'  => 'sum(bundles_hais.posisiselang_mainisk= 0)',
          'tdk_lepas_mainisk'   => 'sum(bundles_hais.lepas_mainisk= 0)',

          'jml_mandi_idopre'  => 'sum(bundles_hais.mandi_idopre= 1)',
          'jml_cukur_idopre' => 'sum(bundles_hais.cukur_idopre= 1)',
          'jml_guladarah_idopre' => 'sum(bundles_hais.guladarah_idopre= 1)',
          'jml_antibiotik_idopre'  => 'sum(bundles_hais.antibiotik_idopre= 1)',
          'tdk_mandi_idopre'  => 'sum(bundles_hais.mandi_idopre= 0)',
          'tdk_cukur_idopre' => 'sum(bundles_hais.cukur_idopre= 0)',
          'tdk_guladarah_idopre' => 'sum(bundles_hais.guladarah_idopre= 0)',
          'tdk_antibiotik_idopre'  => 'sum(bundles_hais.antibiotik_idopre= 0)',

          'jml_hand_idointra' => 'sum(bundles_hais.hand_idointra= 1)',
          'jml_steril_idointra' => 'sum(bundles_hais.steril_idointra= 1)',
          'jml_antiseptic_idointra' => 'sum(bundles_hais.antiseptic_idointra= 1)',
          'jml_tehnik_idointra'   => 'sum(bundles_hais.tehnik_idointra= 1)',
          'jml_mobile_idointra' => 'sum(bundles_hais.mobile_idointra= 1)',
          'jml_suhu_idointra'  => 'sum(bundles_hais.suhu_idointra= 1)',
          'tdk_hand_idointra' => 'sum(bundles_hais.hand_idointra= 0)',
          'tdk_steril_idointra' => 'sum(bundles_hais.steril_idointra= 0)',
          'tdk_antiseptic_idointra' => 'sum(bundles_hais.antiseptic_idointra= 0)',
          'tdk_tehnik_idointra'   => 'sum(bundles_hais.tehnik_idointra= 0)',
          'tdk_mobile_idointra' => 'sum(bundles_hais.mobile_idointra= 0)',
          'tdk_suhu_idointra'  => 'sum(bundles_hais.suhu_idointra= 0)',

          'jml_luka_idopost'   => 'sum(bundles_hais.luka_idopost= 1)',
          'jml_rawat_idopost'  => 'sum(bundles_hais.rawat_idopost= 1)',
          'jml_apd_idopost' => 'sum(bundles_hais.apd_idopost= 1)',
          'jml_kaji_idopost' => 'sum(bundles_hais.kaji_idopost= 1)',
          'tdk_luka_idopost'   => 'sum(bundles_hais.luka_idopost= 0)',
          'tdk_rawat_idopost'  => 'sum(bundles_hais.rawat_idopost= 0)',
          'tdk_apd_idopost' => 'sum(bundles_hais.apd_idopost= 0)',
          'tdk_kaji_idopost' => 'sum(bundles_hais.kaji_idopost= 0)',
          'tanggal' => 'bundles_hais.tanggal'

        ])
        ->where('bundles_hais.tanggal', $row['tanggal'])
        ->toArray();
      $this->assign['list'][] = $row;
    }
    return $this->draw('lapbundles.html', ['lapbundles' => $this->assign]);
  }

  public function postLaporanBundles()
  {
     $this->_addHeaderFiles();
    $this->core->addCSS(url('https://cdn.datatables.net/buttons/2.2.3/css/buttons.dataTables.min.css'));
    $this->core->addJS(url('https://cdn.datatables.net/buttons/2.2.3/js/dataTables.buttons.min.js'), 'footer');
    $this->core->addJS(url('https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js'), 'footer');
    $this->core->addJS(url('https://cdn.datatables.net/buttons/1.3.1/js/buttons.html5.min.js'), 'footer');
    $this->core->addJS(url('https://cdn.datatables.net/plug-ins/1.12.1/api/sum().js'), 'footer');
    $this->core->addJS(url('https://cdn.datatables.net/datetime/1.1.2/js/dataTables.dateTime.min.js'), 'footer');


    if (isset($_POST['submit'])) {

      $date1 = $_POST['date1'];
      $date2 = $_POST['date2'];

      if (!empty($date1) && !empty($date2)) {
        $sql = "SELECT COUNT(no_rawat) as jml_no_rawat, bundles_hais.tanggal
            FROM bundles_hais 
            WHERE bundles_hais.tanggal BETWEEN '$date1' AND '$date2' 
            GROUP BY bundles_hais.tanggal";

        $stmt = $this->db()->pdo()->prepare($sql);
        $stmt->execute();
        $rows = $stmt->fetchAll();

        $this->assign['list'] = [];
        foreach ($rows as $row) {
          $row['jumlah'] = $this->db('bundles_hais')
            ->select([
              'jml_no_rawat' => 'count(bundles_hais.no_rawat)',

              'jml_hand_vap' => 'sum(bundles_hais.hand_vap = 1)',
              'jml_tehniksteril_vap' => 'sum(bundles_hais.tehniksteril_vap = 1)',
              'jml_apd_vap'  => 'sum(bundles_hais.apd_vap = 1)',
              'jml_sedasi_vap' => 'sum(bundles_hais.sedasi_vap = 1)',
              'tdk_hand_vap' => 'sum(bundles_hais.hand_vap = 0)',
              'tdk_apd_vap'  => 'sum(bundles_hais.apd_vap = 0)',
              'tdk_tehniksteril_vap' => 'sum(bundles_hais.tehniksteril_vap = 0)',
              'tdk_sedasi_vap' => 'sum(bundles_hais.sedasi_vap = 0)',

              'jml_hand_iadp' => 'sum(bundles_hais.hand_iadp = 1)',
              'jml_area_iadp' => 'sum(bundles_hais.area_iadp = 1)',
              'jml_tehniksteril_iadp' => 'sum(bundles_hais.tehniksteril_iadp = 1)',
              'jml_alcohol_iadp'  => 'sum(bundles_hais.alcohol_iadp = 1)',
              'jml_apd_iadp' => 'sum(bundles_hais.apd_iadp = 0)',
              'tdk_hand_iadp' => 'sum(bundles_hais.hand_iadp = 0)',
              'tdk_area_iadp' => 'sum(bundles_hais.area_iadp = 0)',
              'tdk_tehniksteril_iadp' => 'sum(bundles_hais.tehniksteril_iadp = 0)',
              'tdk_alcohol_iadp'  => 'sum(bundles_hais.alcohol_iadp = 0)',
              'tdk_apd_iadp' => 'sum(bundles_hais.apd_iadp = 0)',

              'jml_hand_vena' => 'sum(bundles_hais.hand_vena = 1)',
              'jml_kaji_vena'  => 'sum(bundles_hais.kaji_vena = 1)',
              'jml_tehnik_vena'  => 'sum(bundles_hais.tehnik_vena = 1)',
              'jml_petugas_vena' => 'sum(bundles_hais.petugas_vena = 1)',
              'jml_desinfeksi_vena' => 'sum(bundles_hais.desinfeksi_vena = 1)',
              'tdk_hand_vena' => 'sum(bundles_hais.hand_vena = 0)',
              'tdk_kaji_vena'  => 'sum(bundles_hais.kaji_vena = 0)',
              'tdk_tehnik_vena'  => 'sum(bundles_hais.tehnik_vena = 0)',
              'tdk_petugas_vena' => 'sum(bundles_hais.petugas_vena = 0)',
              'tdk_desinfeksi_vena' => 'sum(bundles_hais.desinfeksi_vena = 0)',

              'jml_kaji_isk' => 'sum(bundles_hais.kaji_isk = 1)',
              'jml_petugas_isk'  => 'sum(bundles_hais.petugas_isk = 1)',
              'jml_tangan_isk' => 'sum(bundles_hais.tangan_isk = 1)',
              'jml_tehniksteril_isk' => 'sum(bundles_hais.tehniksteril_isk = 1)',
              'tdk_kaji_isk' => 'sum(bundles_hais.kaji_isk = 0)',
              'tdk_petugas_isk'  => 'sum(bundles_hais.petugas_isk = 0)',
              'tdk_tangan_isk' => 'sum(bundles_hais.tangan_isk = 0)',
              'tdk_tehniksteril_isk' => 'sum(bundles_hais.tehniksteril_isk = 0)',

              'jml_hand_mainvap'  => 'sum(bundles_hais.hand_mainvap = 1)',
              'jml_oral_mainvap' => 'sum(bundles_hais.oral_mainvap = 1)',
              'jml_manage_mainvap' => 'sum(bundles_hais.manage_mainvap= 1)',
              'jml_sedasi_mainvap'   => 'sum(bundles_hais.sedasi_mainvap= 1)',
              'jml_kepala_mainvap'  => 'sum(bundles_hais.kepala_mainvap= 1)',
              'tdk_hand_mainvap'  => 'sum(bundles_hais.hand_mainvap = 0)',
              'tdk_oral_mainvap' => 'sum(bundles_hais.oral_mainvap = 0)',
              'tdk_manage_mainvap' => 'sum(bundles_hais.manage_mainvap= 0)',
              'tdk_sedasi_mainvap'   => 'sum(bundles_hais.sedasi_mainvap= 0)',
              'tdk_kepala_mainvap'  => 'sum(bundles_hais.kepala_mainvap= 0)',

              'jml_hand_mainiadp' => 'sum(bundles_hais.hand_mainiadp= 1)',
              'jml_desinfeksi_mainiadp'  => 'sum(bundles_hais.desinfeksi_mainiadp= 1)',
              'jml_perawatan_mainiadp' => 'sum(bundles_hais.perawatan_mainiadp= 1)',
              'jml_dreasing_mainiadp'  => 'sum(bundles_hais.dreasing_mainiadp= 1)',
              'jml_infus_mainiadp'  => 'sum(bundles_hais.infus_mainiadp= 1)',
              'tdk_hand_mainiadp' => 'sum(bundles_hais.hand_mainiadp= 0)',
              'tdk_desinfeksi_mainiadp'  => 'sum(bundles_hais.desinfeksi_mainiadp= 0)',
              'tdk_perawatan_mainiadp' => 'sum(bundles_hais.perawatan_mainiadp= 0)',
              'tdk_dreasing_mainiadp'  => 'sum(bundles_hais.dreasing_mainiadp= 0)',
              'tdk_infus_mainiadp'  => 'sum(bundles_hais.infus_mainiadp= 0)',

              'jml_hand_mainvena'  => 'sum(bundles_hais.hand_mainvena= 1)',
              'jml_perawatan_mainvena' => 'sum(bundles_hais.perawatan_mainvena= 1)',
              'jml_kaji_mainvena' => 'sum(bundles_hais.kaji_mainvena= 1)',
              'jml_administrasi_mainvena' => 'sum(bundles_hais.administrasi_mainvena= 1)',
              'jml_edukasi_mainvena' => 'sum(bundles_hais.edukasi_mainvena= 1)',
              'tdk_hand_mainvena'  => 'sum(bundles_hais.hand_mainvena= 0)',
              'tdk_perawatan_mainvena' => 'sum(bundles_hais.perawatan_mainvena= 0)',
              'tdk_kaji_mainvena' => 'sum(bundles_hais.kaji_mainvena= 0)',
              'tdk_administrasi_mainvena' => 'sum(bundles_hais.administrasi_mainvena= 0)',
              'tdk_edukasi_mainvena' => 'sum(bundles_hais.edukasi_mainvena= 0)',

              'jml_hand_mainisk' => 'sum(bundles_hais.hand_mainisk= 1)',
              'jml_kateter_mainisk' => 'sum(bundles_hais.kateter_mainisk= 1)',
              'jml_baglantai_mainisk' => 'sum(bundles_hais.baglantai_mainisk= 1)',
              'jml_bagrendah_mainisk' => 'sum(bundles_hais.bagrendah_mainisk= 1)',
              'jml_posisiselang_mainisk'  => 'sum(bundles_hais.posisiselang_mainisk= 1)',
              'jml_lepas_mainisk'   => 'sum(bundles_hais.lepas_mainisk= 1)',
              'tdk_hand_mainisk' => 'sum(bundles_hais.hand_mainisk= 0)',
              'tdk_kateter_mainisk' => 'sum(bundles_hais.kateter_mainisk= 0)',
              'tdk_baglantai_mainisk' => 'sum(bundles_hais.baglantai_mainisk= 0)',
              'tdk_bagrendah_mainisk' => 'sum(bundles_hais.bagrendah_mainisk= 0)',
              'tdk_posisiselang_mainisk'  => 'sum(bundles_hais.posisiselang_mainisk= 0)',
              'tdk_lepas_mainisk'   => 'sum(bundles_hais.lepas_mainisk= 0)',

              'jml_mandi_idopre'  => 'sum(bundles_hais.mandi_idopre= 1)',
              'jml_cukur_idopre' => 'sum(bundles_hais.cukur_idopre= 1)',
              'jml_guladarah_idopre' => 'sum(bundles_hais.guladarah_idopre= 1)',
              'jml_antibiotik_idopre'  => 'sum(bundles_hais.antibiotik_idopre= 1)',
              'tdk_mandi_idopre'  => 'sum(bundles_hais.mandi_idopre= 0)',
              'tdk_cukur_idopre' => 'sum(bundles_hais.cukur_idopre= 0)',
              'tdk_guladarah_idopre' => 'sum(bundles_hais.guladarah_idopre= 0)',
              'tdk_antibiotik_idopre'  => 'sum(bundles_hais.antibiotik_idopre= 0)',

              'jml_hand_idointra' => 'sum(bundles_hais.hand_idointra= 1)',
              'jml_steril_idointra' => 'sum(bundles_hais.steril_idointra= 1)',
              'jml_antiseptic_idointra' => 'sum(bundles_hais.antiseptic_idointra= 1)',
              'jml_tehnik_idointra'   => 'sum(bundles_hais.tehnik_idointra= 1)',
              'jml_mobile_idointra' => 'sum(bundles_hais.mobile_idointra= 1)',
              'jml_suhu_idointra'  => 'sum(bundles_hais.suhu_idointra= 1)',
              'tdk_hand_idointra' => 'sum(bundles_hais.hand_idointra= 0)',
              'tdk_steril_idointra' => 'sum(bundles_hais.steril_idointra= 0)',
              'tdk_antiseptic_idointra' => 'sum(bundles_hais.antiseptic_idointra= 0)',
              'tdk_tehnik_idointra'   => 'sum(bundles_hais.tehnik_idointra= 0)',
              'tdk_mobile_idointra' => 'sum(bundles_hais.mobile_idointra= 0)',
              'tdk_suhu_idointra'  => 'sum(bundles_hais.suhu_idointra= 0)',

              'jml_luka_idopost'   => 'sum(bundles_hais.luka_idopost= 1)',
              'jml_rawat_idopost'  => 'sum(bundles_hais.rawat_idopost= 1)',
              'jml_apd_idopost' => 'sum(bundles_hais.apd_idopost= 1)',
              'jml_kaji_idopost' => 'sum(bundles_hais.kaji_idopost= 1)',
              'tdk_luka_idopost'   => 'sum(bundles_hais.luka_idopost= 0)',
              'tdk_rawat_idopost'  => 'sum(bundles_hais.rawat_idopost= 0)',
              'tdk_apd_idopost' => 'sum(bundles_hais.apd_idopost= 0)',
              'tdk_kaji_idopost' => 'sum(bundles_hais.kaji_idopost= 0)',
              'tanggal' => 'bundles_hais.tanggal'

            ])
            ->where('bundles_hais.tanggal', $row['tanggal'])
            ->toArray();
          $this->assign['list'][] = $row;
        }
      } else {
        $this->getLaporanBundles();
      }
    }
    $this->core->addCSS(url('assets/css/dataTables.bootstrap.min.css'));
    $this->core->addCSS(url([ADMIN, 'laporan_bundles', 'css']));
    $this->core->addJS(url('assets/jscripts/jquery.dataTables.min.js'));
    $this->core->addJS(url('assets/jscripts/dataTables.bootstrap.min.js'));
    return $this->draw('lapbundles.html', ['lapbundles' => $this->assign]);
  }

  public function getBundlesBangsal()
  {
    $this->_addHeaderFiles();

    $this->core->addCSS(url('https://cdn.datatables.net/buttons/2.2.3/css/buttons.dataTables.min.css'));
    $this->core->addJS(url('https://cdn.datatables.net/buttons/2.2.3/js/dataTables.buttons.min.js'), 'footer');
    $this->core->addJS(url('https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js'), 'footer');
    $this->core->addJS(url('https://cdn.datatables.net/buttons/1.3.1/js/buttons.html5.min.js'), 'footer');
    $this->core->addJS(url('https://cdn.datatables.net/plug-ins/1.12.1/api/sum().js'), 'footer');

    $rows =  $this->db('bangsal')->where('bangsal.status', '1')->toArray();

    $this->assign['list'] = [];
    foreach ($rows as $row) {
      $kd_bangsal = $row['kd_bangsal'];
      $row['jumlah'] = $this->db('bundles_hais')
        ->select([
              'jml_no_rawat' => 'count(bundles_hais.no_rawat)',

              'jml_hand_vap' => 'sum(bundles_hais.hand_vap = 1)',
              'jml_tehniksteril_vap' => 'sum(bundles_hais.tehniksteril_vap = 1)',
              'jml_apd_vap'  => 'sum(bundles_hais.apd_vap = 1)',
              'jml_sedasi_vap' => 'sum(bundles_hais.sedasi_vap = 1)',
              'tdk_hand_vap' => 'sum(bundles_hais.hand_vap = 0)',
              'tdk_apd_vap'  => 'sum(bundles_hais.apd_vap = 0)',
              'tdk_tehniksteril_vap' => 'sum(bundles_hais.tehniksteril_vap = 0)',
              'tdk_sedasi_vap' => 'sum(bundles_hais.sedasi_vap = 0)',

              'jml_hand_iadp' => 'sum(bundles_hais.hand_iadp = 1)',
              'jml_area_iadp' => 'sum(bundles_hais.area_iadp = 1)',
              'jml_tehniksteril_iadp' => 'sum(bundles_hais.tehniksteril_iadp = 1)',
              'jml_alcohol_iadp'  => 'sum(bundles_hais.alcohol_iadp = 1)',
              'jml_apd_iadp' => 'sum(bundles_hais.apd_iadp = 0)',
              'tdk_hand_iadp' => 'sum(bundles_hais.hand_iadp = 0)',
              'tdk_area_iadp' => 'sum(bundles_hais.area_iadp = 0)',
              'tdk_tehniksteril_iadp' => 'sum(bundles_hais.tehniksteril_iadp = 0)',
              'tdk_alcohol_iadp'  => 'sum(bundles_hais.alcohol_iadp = 0)',
              'tdk_apd_iadp' => 'sum(bundles_hais.apd_iadp = 0)',

              'jml_hand_vena' => 'sum(bundles_hais.hand_vena = 1)',
              'jml_kaji_vena'  => 'sum(bundles_hais.kaji_vena = 1)',
              'jml_tehnik_vena'  => 'sum(bundles_hais.tehnik_vena = 1)',
              'jml_petugas_vena' => 'sum(bundles_hais.petugas_vena = 1)',
              'jml_desinfeksi_vena' => 'sum(bundles_hais.desinfeksi_vena = 1)',
              'tdk_hand_vena' => 'sum(bundles_hais.hand_vena = 0)',
              'tdk_kaji_vena'  => 'sum(bundles_hais.kaji_vena = 0)',
              'tdk_tehnik_vena'  => 'sum(bundles_hais.tehnik_vena = 0)',
              'tdk_petugas_vena' => 'sum(bundles_hais.petugas_vena = 0)',
              'tdk_desinfeksi_vena' => 'sum(bundles_hais.desinfeksi_vena = 0)',

              'jml_kaji_isk' => 'sum(bundles_hais.kaji_isk = 1)',
              'jml_petugas_isk'  => 'sum(bundles_hais.petugas_isk = 1)',
              'jml_tangan_isk' => 'sum(bundles_hais.tangan_isk = 1)',
              'jml_tehniksteril_isk' => 'sum(bundles_hais.tehniksteril_isk = 1)',
              'tdk_kaji_isk' => 'sum(bundles_hais.kaji_isk = 0)',
              'tdk_petugas_isk'  => 'sum(bundles_hais.petugas_isk = 0)',
              'tdk_tangan_isk' => 'sum(bundles_hais.tangan_isk = 0)',
              'tdk_tehniksteril_isk' => 'sum(bundles_hais.tehniksteril_isk = 0)',

              'jml_hand_mainvap'  => 'sum(bundles_hais.hand_mainvap = 1)',
              'jml_oral_mainvap' => 'sum(bundles_hais.oral_mainvap = 1)',
              'jml_manage_mainvap' => 'sum(bundles_hais.manage_mainvap= 1)',
              'jml_sedasi_mainvap'   => 'sum(bundles_hais.sedasi_mainvap= 1)',
              'jml_kepala_mainvap'  => 'sum(bundles_hais.kepala_mainvap= 1)',
              'tdk_hand_mainvap'  => 'sum(bundles_hais.hand_mainvap = 0)',
              'tdk_oral_mainvap' => 'sum(bundles_hais.oral_mainvap = 0)',
              'tdk_manage_mainvap' => 'sum(bundles_hais.manage_mainvap= 0)',
              'tdk_sedasi_mainvap'   => 'sum(bundles_hais.sedasi_mainvap= 0)',
              'tdk_kepala_mainvap'  => 'sum(bundles_hais.kepala_mainvap= 0)',

              'jml_hand_mainiadp' => 'sum(bundles_hais.hand_mainiadp= 1)',
              'jml_desinfeksi_mainiadp'  => 'sum(bundles_hais.desinfeksi_mainiadp= 1)',
              'jml_perawatan_mainiadp' => 'sum(bundles_hais.perawatan_mainiadp= 1)',
              'jml_dreasing_mainiadp'  => 'sum(bundles_hais.dreasing_mainiadp= 1)',
              'jml_infus_mainiadp'  => 'sum(bundles_hais.infus_mainiadp= 1)',
              'tdk_hand_mainiadp' => 'sum(bundles_hais.hand_mainiadp= 0)',
              'tdk_desinfeksi_mainiadp'  => 'sum(bundles_hais.desinfeksi_mainiadp= 0)',
              'tdk_perawatan_mainiadp' => 'sum(bundles_hais.perawatan_mainiadp= 0)',
              'tdk_dreasing_mainiadp'  => 'sum(bundles_hais.dreasing_mainiadp= 0)',
              'tdk_infus_mainiadp'  => 'sum(bundles_hais.infus_mainiadp= 0)',

              'jml_hand_mainvena'  => 'sum(bundles_hais.hand_mainvena= 1)',
              'jml_perawatan_mainvena' => 'sum(bundles_hais.perawatan_mainvena= 1)',
              'jml_kaji_mainvena' => 'sum(bundles_hais.kaji_mainvena= 1)',
              'jml_administrasi_mainvena' => 'sum(bundles_hais.administrasi_mainvena= 1)',
              'jml_edukasi_mainvena' => 'sum(bundles_hais.edukasi_mainvena= 1)',
              'tdk_hand_mainvena'  => 'sum(bundles_hais.hand_mainvena= 1)',
              'tdk_perawatan_mainvena' => 'sum(bundles_hais.perawatan_mainvena= 1)',
              'tdk_kaji_mainvena' => 'sum(bundles_hais.kaji_mainvena= 1)',
              'tdk_administrasi_mainvena' => 'sum(bundles_hais.administrasi_mainvena= 1)',
              'tdk_edukasi_mainvena' => 'sum(bundles_hais.edukasi_mainvena= 1)',

              'jml_hand_mainisk' => 'sum(bundles_hais.hand_mainisk= 1)',
              'jml_kateter_mainisk' => 'sum(bundles_hais.kateter_mainisk= 1)',
              'jml_baglantai_mainisk' => 'sum(bundles_hais.baglantai_mainisk= 1)',
              'jml_bagrendah_mainisk' => 'sum(bundles_hais.bagrendah_mainisk= 1)',
              'jml_posisiselang_mainisk'  => 'sum(bundles_hais.posisiselang_mainisk= 1)',
              'jml_lepas_mainisk'   => 'sum(bundles_hais.lepas_mainisk= 1)',
              'tdk_hand_mainisk' => 'sum(bundles_hais.hand_mainisk= 0)',
              'tdk_kateter_mainisk' => 'sum(bundles_hais.kateter_mainisk= 0)',
              'tdk_baglantai_mainisk' => 'sum(bundles_hais.baglantai_mainisk= 0)',
              'tdk_bagrendah_mainisk' => 'sum(bundles_hais.bagrendah_mainisk= 0)',
              'tdk_posisiselang_mainisk'  => 'sum(bundles_hais.posisiselang_mainisk= 0)',
              'tdk_lepas_mainisk'   => 'sum(bundles_hais.lepas_mainisk= 0)',

              'jml_mandi_idopre'  => 'sum(bundles_hais.mandi_idopre= 1)',
              'jml_cukur_idopre' => 'sum(bundles_hais.cukur_idopre= 1)',
              'jml_guladarah_idopre' => 'sum(bundles_hais.guladarah_idopre= 1)',
              'jml_antibiotik_idopre'  => 'sum(bundles_hais.antibiotik_idopre= 1)',
              'tdk_mandi_idopre'  => 'sum(bundles_hais.mandi_idopre= 0)',
              'tdk_cukur_idopre' => 'sum(bundles_hais.cukur_idopre= 0)',
              'tdk_guladarah_idopre' => 'sum(bundles_hais.guladarah_idopre= 0)',
              'tdk_antibiotik_idopre'  => 'sum(bundles_hais.antibiotik_idopre= 0)',

              'jml_hand_idointra' => 'sum(bundles_hais.hand_idointra= 1)',
              'jml_steril_idointra' => 'sum(bundles_hais.steril_idointra= 1)',
              'jml_antiseptic_idointra' => 'sum(bundles_hais.antiseptic_idointra= 1)',
              'jml_tehnik_idointra'   => 'sum(bundles_hais.tehnik_idointra= 1)',
              'jml_mobile_idointra' => 'sum(bundles_hais.mobile_idointra= 1)',
              'jml_suhu_idointra'  => 'sum(bundles_hais.suhu_idointra= 1)',
              'tdk_hand_idointra' => 'sum(bundles_hais.hand_idointra= 0)',
              'tdk_steril_idointra' => 'sum(bundles_hais.steril_idointra= 0)',
              'tdk_antiseptic_idointra' => 'sum(bundles_hais.antiseptic_idointra= 0)',
              'tdk_tehnik_idointra'   => 'sum(bundles_hais.tehnik_idointra= 0)',
              'tdk_mobile_idointra' => 'sum(bundles_hais.mobile_idointra= 0)',
              'tdk_suhu_idointra'  => 'sum(bundles_hais.suhu_idointra= 0)',

              'jml_luka_idopost'   => 'sum(bundles_hais.luka_idopost= 1)',
              'jml_rawat_idopost'  => 'sum(bundles_hais.rawat_idopost= 1)',
              'jml_apd_idopost' => 'sum(bundles_hais.apd_idopost= 1)',
              'jml_kaji_idopost' => 'sum(bundles_hais.kaji_idopost= 1)',
              'tdk_luka_idopost'   => 'sum(bundles_hais.luka_idopost= 0)',
              'tdk_rawat_idopost'  => 'sum(bundles_hais.rawat_idopost= 0)',
              'tdk_apd_idopost' => 'sum(bundles_hais.apd_idopost= 0)',
              'tdk_kaji_idopost' => 'sum(bundles_hais.kaji_idopost= 0)'

        ])
        ->join('kamar', 'kamar.kd_kamar=bundles_hais.kd_kamar', 'LEFT')
        ->join('bangsal', 'bangsal.kd_bangsal=kamar.kd_bangsal', 'LEFT')
        ->where('bangsal.kd_bangsal', $row['kd_bangsal'])
        ->toArray();

      $this->assign['list'][] = $row;
    }
    $this->core->addCSS(url('assets/css/dataTables.bootstrap.min.css'));
    $this->core->addCSS(url([ADMIN, 'laporan_bundles', 'css']));
    $this->core->addJS(url('assets/jscripts/jquery.dataTables.min.js'));
    $this->core->addJS(url('assets/jscripts/dataTables.bootstrap.min.js'));
    return $this->draw('lapbundles_kamar.html', ['bundleskamar' => $this->assign]);
  }

  

  public function postBundlesBangsal()
  {
    $this->_addHeaderFiles();
    $this->core->addCSS(url('https://cdn.datatables.net/buttons/2.2.3/css/buttons.dataTables.min.css'));
    $this->core->addJS(url('https://cdn.datatables.net/buttons/2.2.3/js/dataTables.buttons.min.js'), 'footer');
    $this->core->addJS(url('https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js'), 'footer');
    $this->core->addJS(url('https://cdn.datatables.net/buttons/1.3.1/js/buttons.html5.min.js'), 'footer');
    $this->core->addJS(url('https://cdn.datatables.net/plug-ins/1.12.1/api/sum().js'), 'footer');
    $this->core->addJS(url('https://cdn.datatables.net/datetime/1.1.2/js/dataTables.dateTime.min.js'), 'footer');


    if (isset($_POST['submit'])) {

      $date1 = $_POST['date1'];
      $date2 = $_POST['date2'];

      if (!empty($date1) && !empty($date2)) {
            $sql = "SELECT COUNT(no_rawat) as jml_no_rawat, bundles_hais.tanggal, bangsal.nm_bangsal, bangsal.kd_bangsal
            FROM bundles_hais, kamar, bangsal 
            WHERE bundles_hais.kd_kamar=kamar.kd_kamar 
            AND bangsal.kd_bangsal=kamar.kd_bangsal
            AND bundles_hais.tanggal BETWEEN '$date1' AND '$date2' 
            GROUP BY bundles_hais.tanggal";

        $stmt = $this->db()->pdo()->prepare($sql);
        $stmt->execute();
        $rows = $stmt->fetchAll();

        $this->assign['list'] = [];
        foreach ($rows as $row) {
           $kd_bangsal= $row['kd_bangsal'];
            $row['jumlah'] = $this->db('bundles_hais')
            ->select([
              'jml_no_rawat' => 'count(bundles_hais.no_rawat)',

              'jml_hand_vap' => 'sum(bundles_hais.hand_vap = 1)',
              'jml_tehniksteril_vap' => 'sum(bundles_hais.tehniksteril_vap = 1)',
              'jml_apd_vap'  => 'sum(bundles_hais.apd_vap = 1)',
              'jml_sedasi_vap' => 'sum(bundles_hais.sedasi_vap = 1)',
              'tdk_hand_vap' => 'sum(bundles_hais.hand_vap = 0)',
              'tdk_apd_vap'  => 'sum(bundles_hais.apd_vap = 0)',
              'tdk_tehniksteril_vap' => 'sum(bundles_hais.tehniksteril_vap = 0)',
              'tdk_sedasi_vap' => 'sum(bundles_hais.sedasi_vap = 0)',

              'jml_hand_iadp' => 'sum(bundles_hais.hand_iadp = 1)',
              'jml_area_iadp' => 'sum(bundles_hais.area_iadp = 1)',
              'jml_tehniksteril_iadp' => 'sum(bundles_hais.tehniksteril_iadp = 1)',
              'jml_alcohol_iadp'  => 'sum(bundles_hais.alcohol_iadp = 1)',
              'jml_apd_iadp' => 'sum(bundles_hais.apd_iadp = 0)',
              'tdk_hand_iadp' => 'sum(bundles_hais.hand_iadp = 0)',
              'tdk_area_iadp' => 'sum(bundles_hais.area_iadp = 0)',
              'tdk_tehniksteril_iadp' => 'sum(bundles_hais.tehniksteril_iadp = 0)',
              'tdk_alcohol_iadp'  => 'sum(bundles_hais.alcohol_iadp = 0)',
              'tdk_apd_iadp' => 'sum(bundles_hais.apd_iadp = 0)',

              'jml_hand_vena' => 'sum(bundles_hais.hand_vena = 1)',
              'jml_kaji_vena'  => 'sum(bundles_hais.kaji_vena = 1)',
              'jml_tehnik_vena'  => 'sum(bundles_hais.tehnik_vena = 1)',
              'jml_petugas_vena' => 'sum(bundles_hais.petugas_vena = 1)',
              'jml_desinfeksi_vena' => 'sum(bundles_hais.desinfeksi_vena = 1)',
              'tdk_hand_vena' => 'sum(bundles_hais.hand_vena = 0)',
              'tdk_kaji_vena'  => 'sum(bundles_hais.kaji_vena = 0)',
              'tdk_tehnik_vena'  => 'sum(bundles_hais.tehnik_vena = 0)',
              'tdk_petugas_vena' => 'sum(bundles_hais.petugas_vena = 0)',
              'tdk_desinfeksi_vena' => 'sum(bundles_hais.desinfeksi_vena = 0)',

              'jml_kaji_isk' => 'sum(bundles_hais.kaji_isk = 1)',
              'jml_petugas_isk'  => 'sum(bundles_hais.petugas_isk = 1)',
              'jml_tangan_isk' => 'sum(bundles_hais.tangan_isk = 1)',
              'jml_tehniksteril_isk' => 'sum(bundles_hais.tehniksteril_isk = 1)',
              'tdk_kaji_isk' => 'sum(bundles_hais.kaji_isk = 0)',
              'tdk_petugas_isk'  => 'sum(bundles_hais.petugas_isk = 0)',
              'tdk_tangan_isk' => 'sum(bundles_hais.tangan_isk = 0)',
              'tdk_tehniksteril_isk' => 'sum(bundles_hais.tehniksteril_isk = 0)',

              'jml_hand_mainvap'  => 'sum(bundles_hais.hand_mainvap = 1)',
              'jml_oral_mainvap' => 'sum(bundles_hais.oral_mainvap = 1)',
              'jml_manage_mainvap' => 'sum(bundles_hais.manage_mainvap= 1)',
              'jml_sedasi_mainvap'   => 'sum(bundles_hais.sedasi_mainvap= 1)',
              'jml_kepala_mainvap'  => 'sum(bundles_hais.kepala_mainvap= 1)',
              'tdk_hand_mainvap'  => 'sum(bundles_hais.hand_mainvap = 0)',
              'tdk_oral_mainvap' => 'sum(bundles_hais.oral_mainvap = 0)',
              'tdk_manage_mainvap' => 'sum(bundles_hais.manage_mainvap= 0)',
              'tdk_sedasi_mainvap'   => 'sum(bundles_hais.sedasi_mainvap= 0)',
              'tdk_kepala_mainvap'  => 'sum(bundles_hais.kepala_mainvap= 0)',

              'jml_hand_mainiadp' => 'sum(bundles_hais.hand_mainiadp= 1)',
              'jml_desinfeksi_mainiadp'  => 'sum(bundles_hais.desinfeksi_mainiadp= 1)',
              'jml_perawatan_mainiadp' => 'sum(bundles_hais.perawatan_mainiadp= 1)',
              'jml_dreasing_mainiadp'  => 'sum(bundles_hais.dreasing_mainiadp= 1)',
              'jml_infus_mainiadp'  => 'sum(bundles_hais.infus_mainiadp= 1)',
              'tdk_hand_mainiadp' => 'sum(bundles_hais.hand_mainiadp= 0)',
              'tdk_desinfeksi_mainiadp'  => 'sum(bundles_hais.desinfeksi_mainiadp= 0)',
              'tdk_perawatan_mainiadp' => 'sum(bundles_hais.perawatan_mainiadp= 0)',
              'tdk_dreasing_mainiadp'  => 'sum(bundles_hais.dreasing_mainiadp= 0)',
              'tdk_infus_mainiadp'  => 'sum(bundles_hais.infus_mainiadp= 0)',

              'jml_hand_mainvena'  => 'sum(bundles_hais.hand_mainvena= 1)',
              'jml_perawatan_mainvena' => 'sum(bundles_hais.perawatan_mainvena= 1)',
              'jml_kaji_mainvena' => 'sum(bundles_hais.kaji_mainvena= 1)',
              'jml_administrasi_mainvena' => 'sum(bundles_hais.administrasi_mainvena= 1)',
              'jml_edukasi_mainvena' => 'sum(bundles_hais.edukasi_mainvena= 1)',
              'tdk_hand_mainvena'  => 'sum(bundles_hais.hand_mainvena= 1)',
              'tdk_perawatan_mainvena' => 'sum(bundles_hais.perawatan_mainvena= 1)',
              'tdk_kaji_mainvena' => 'sum(bundles_hais.kaji_mainvena= 1)',
              'tdk_administrasi_mainvena' => 'sum(bundles_hais.administrasi_mainvena= 1)',
              'tdk_edukasi_mainvena' => 'sum(bundles_hais.edukasi_mainvena= 1)',

              'jml_hand_mainisk' => 'sum(bundles_hais.hand_mainisk= 1)',
              'jml_kateter_mainisk' => 'sum(bundles_hais.kateter_mainisk= 1)',
              'jml_baglantai_mainisk' => 'sum(bundles_hais.baglantai_mainisk= 1)',
              'jml_bagrendah_mainisk' => 'sum(bundles_hais.bagrendah_mainisk= 1)',
              'jml_posisiselang_mainisk'  => 'sum(bundles_hais.posisiselang_mainisk= 1)',
              'jml_lepas_mainisk'   => 'sum(bundles_hais.lepas_mainisk= 1)',
              'tdk_hand_mainisk' => 'sum(bundles_hais.hand_mainisk= 0)',
              'tdk_kateter_mainisk' => 'sum(bundles_hais.kateter_mainisk= 0)',
              'tdk_baglantai_mainisk' => 'sum(bundles_hais.baglantai_mainisk= 0)',
              'tdk_bagrendah_mainisk' => 'sum(bundles_hais.bagrendah_mainisk= 0)',
              'tdk_posisiselang_mainisk'  => 'sum(bundles_hais.posisiselang_mainisk= 0)',
              'tdk_lepas_mainisk'   => 'sum(bundles_hais.lepas_mainisk= 0)',

              'jml_mandi_idopre'  => 'sum(bundles_hais.mandi_idopre= 1)',
              'jml_cukur_idopre' => 'sum(bundles_hais.cukur_idopre= 1)',
              'jml_guladarah_idopre' => 'sum(bundles_hais.guladarah_idopre= 1)',
              'jml_antibiotik_idopre'  => 'sum(bundles_hais.antibiotik_idopre= 1)',
              'tdk_mandi_idopre'  => 'sum(bundles_hais.mandi_idopre= 0)',
              'tdk_cukur_idopre' => 'sum(bundles_hais.cukur_idopre= 0)',
              'tdk_guladarah_idopre' => 'sum(bundles_hais.guladarah_idopre= 0)',
              'tdk_antibiotik_idopre'  => 'sum(bundles_hais.antibiotik_idopre= 0)',

              'jml_hand_idointra' => 'sum(bundles_hais.hand_idointra= 1)',
              'jml_steril_idointra' => 'sum(bundles_hais.steril_idointra= 1)',
              'jml_antiseptic_idointra' => 'sum(bundles_hais.antiseptic_idointra= 1)',
              'jml_tehnik_idointra'   => 'sum(bundles_hais.tehnik_idointra= 1)',
              'jml_mobile_idointra' => 'sum(bundles_hais.mobile_idointra= 1)',
              'jml_suhu_idointra'  => 'sum(bundles_hais.suhu_idointra= 1)',
              'tdk_hand_idointra' => 'sum(bundles_hais.hand_idointra= 0)',
              'tdk_steril_idointra' => 'sum(bundles_hais.steril_idointra= 0)',
              'tdk_antiseptic_idointra' => 'sum(bundles_hais.antiseptic_idointra= 0)',
              'tdk_tehnik_idointra'   => 'sum(bundles_hais.tehnik_idointra= 0)',
              'tdk_mobile_idointra' => 'sum(bundles_hais.mobile_idointra= 0)',
              'tdk_suhu_idointra'  => 'sum(bundles_hais.suhu_idointra= 0)',

              'jml_luka_idopost'   => 'sum(bundles_hais.luka_idopost= 1)',
              'jml_rawat_idopost'  => 'sum(bundles_hais.rawat_idopost= 1)',
              'jml_apd_idopost' => 'sum(bundles_hais.apd_idopost= 1)',
              'jml_kaji_idopost' => 'sum(bundles_hais.kaji_idopost= 1)',
              'tdk_luka_idopost'   => 'sum(bundles_hais.luka_idopost= 0)',
              'tdk_rawat_idopost'  => 'sum(bundles_hais.rawat_idopost= 0)',
              'tdk_apd_idopost' => 'sum(bundles_hais.apd_idopost= 0)',
              'tdk_kaji_idopost' => 'sum(bundles_hais.kaji_idopost= 0)',
              'tanggal' => 'bundles_hais.tanggal'

            ])
            ->where('bundles_hais.tanggal', $row['tanggal'])
            ->toArray();
          $this->assign['list'][] = $row;
        }
      } else {
        $this->getBundlesKamar();
      }
    }
    $this->core->addCSS(url('assets/css/dataTables.bootstrap.min.css'));
    $this->core->addCSS(url([ADMIN, 'laporan_bundles', 'css']));
    $this->core->addJS(url('assets/jscripts/jquery.dataTables.min.js'));
    $this->core->addJS(url('assets/jscripts/dataTables.bootstrap.min.js'));
    return $this->draw('lapbundles_kamar.html', ['bundleskamar' => $this->assign]);
  }

   public function getBundlesIGD()
  {

    $this->_addHeaderFiles();
    $this->core->addCSS(url('https://cdn.datatables.net/buttons/2.2.3/css/buttons.dataTables.min.css'));
    $this->core->addJS(url('https://cdn.datatables.net/buttons/2.2.3/js/dataTables.buttons.min.js'), 'footer');
    $this->core->addJS(url('https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js'), 'footer');
    $this->core->addJS(url('https://cdn.datatables.net/buttons/1.3.1/js/buttons.html5.min.js'), 'footer');
    $this->core->addJS(url('https://cdn.datatables.net/plug-ins/1.12.1/api/sum().js'), 'footer');
    $this->core->addJS(url('https://cdn.datatables.net/datetime/1.1.2/js/dataTables.dateTime.min.js'), 'footer');


    $sql = "SELECT bundles_hais.tanggal 
            FROM bundles_hais 
            WHERE DATE_FORMAT(bundles_hais.tanggal, '%Y-%m') = DATE_FORMAT(CURDATE(), '%Y-%m') 
            AND (kd_kamar = 'IGDK' OR kd_kamar = 'IGD01') 
            GROUP BY bundles_hais.tanggal";
    $stmt = $this->db()->pdo()->prepare($sql);
    $stmt->execute();
    $rows = $stmt->fetchAll();

    $this->assign['list'] = [];
    foreach ($rows as $row) {
      $row['jumlah'] = $this->db('bundles_hais')
        ->select([
          'jml_no_rawat' => 'count(bundles_hais.no_rawat)',

          'jml_hand_vap' => 'sum(bundles_hais.hand_vap = 1)',
          'jml_tehniksteril_vap' => 'sum(bundles_hais.tehniksteril_vap = 1)',
          'jml_apd_vap'  => 'sum(bundles_hais.apd_vap = 1)',
          'jml_sedasi_vap' => 'sum(bundles_hais.sedasi_vap = 1)',
          'tdk_hand_vap' => 'sum(bundles_hais.hand_vap = 0)',
          'tdk_apd_vap'  => 'sum(bundles_hais.apd_vap = 0)',
          'tdk_tehniksteril_vap' => 'sum(bundles_hais.tehniksteril_vap = 0)',
          'tdk_sedasi_vap' => 'sum(bundles_hais.sedasi_vap = 0)',

          'jml_hand_iadp' => 'sum(bundles_hais.hand_iadp = 1)',
          'jml_area_iadp' => 'sum(bundles_hais.area_iadp = 1)',
          'jml_tehniksteril_iadp' => 'sum(bundles_hais.tehniksteril_iadp = 1)',
          'jml_alcohol_iadp'  => 'sum(bundles_hais.alcohol_iadp = 1)',
          'jml_apd_iadp' => 'sum(bundles_hais.apd_iadp = 1)',
          'tdk_hand_iadp' => 'sum(bundles_hais.hand_iadp = 0)',
          'tdk_area_iadp' => 'sum(bundles_hais.area_iadp = 0)',
          'tdk_tehniksteril_iadp' => 'sum(bundles_hais.tehniksteril_iadp = 0)',
          'tdk_alcohol_iadp'  => 'sum(bundles_hais.alcohol_iadp = 0)',
          'tdk_apd_iadp' => 'sum(bundles_hais.apd_iadp = 0)',

          'jml_hand_vena' => 'sum(bundles_hais.hand_vena = 1)',
          'jml_kaji_vena'  => 'sum(bundles_hais.kaji_vena = 1)',
          'jml_tehnik_vena'  => 'sum(bundles_hais.tehnik_vena = 1)',
          'jml_petugas_vena' => 'sum(bundles_hais.petugas_vena = 1)',
          'jml_desinfeksi_vena' => 'sum(bundles_hais.desinfeksi_vena = 1)',
          'tdk_hand_vena' => 'sum(bundles_hais.hand_vena = 0)',
          'tdk_kaji_vena'  => 'sum(bundles_hais.kaji_vena = 0)',
          'tdk_tehnik_vena'  => 'sum(bundles_hais.tehnik_vena = 0)',
          'tdk_petugas_vena' => 'sum(bundles_hais.petugas_vena = 0)',
          'tdk_desinfeksi_vena' => 'sum(bundles_hais.desinfeksi_vena = 0)',

          'jml_kaji_isk' => 'sum(bundles_hais.kaji_isk = 1)',
          'jml_petugas_isk'  => 'sum(bundles_hais.petugas_isk = 1)',
          'jml_tangan_isk' => 'sum(bundles_hais.tangan_isk = 1)',
          'jml_tehniksteril_isk' => 'sum(bundles_hais.tehniksteril_isk = 1)',
          'tdk_kaji_isk' => 'sum(bundles_hais.kaji_isk = 0)',
          'tdk_petugas_isk'  => 'sum(bundles_hais.petugas_isk = 0)',
          'tdk_tangan_isk' => 'sum(bundles_hais.tangan_isk = 0)',
          'tdk_tehniksteril_isk' => 'sum(bundles_hais.tehniksteril_isk = 0)',

          'jml_hand_mainvap'  => 'sum(bundles_hais.hand_mainvap = 1)',
          'jml_oral_mainvap' => 'sum(bundles_hais.oral_mainvap = 1)',
          'jml_manage_mainvap' => 'sum(bundles_hais.manage_mainvap= 1)',
          'jml_sedasi_mainvap'   => 'sum(bundles_hais.sedasi_mainvap= 1)',
          'jml_kepala_mainvap'  => 'sum(bundles_hais.kepala_mainvap= 1)',
          'tdk_hand_mainvap'  => 'sum(bundles_hais.hand_mainvap = 0)',
          'tdk_oral_mainvap' => 'sum(bundles_hais.oral_mainvap = 0)',
          'tdk_manage_mainvap' => 'sum(bundles_hais.manage_mainvap= 0)',
          'tdk_sedasi_mainvap'   => 'sum(bundles_hais.sedasi_mainvap= 0)',
          'tdk_kepala_mainvap'  => 'sum(bundles_hais.kepala_mainvap= 0)',

          'jml_hand_mainiadp' => 'sum(bundles_hais.hand_mainiadp= 1)',
          'jml_desinfeksi_mainiadp'  => 'sum(bundles_hais.desinfeksi_mainiadp= 1)',
          'jml_perawatan_mainiadp' => 'sum(bundles_hais.perawatan_mainiadp= 1)',
          'jml_dreasing_mainiadp'  => 'sum(bundles_hais.dreasing_mainiadp= 1)',
          'jml_infus_mainiadp'  => 'sum(bundles_hais.infus_mainiadp= 1)',
          'tdk_hand_mainiadp' => 'sum(bundles_hais.hand_mainiadp= 0)',
          'tdk_desinfeksi_mainiadp'  => 'sum(bundles_hais.desinfeksi_mainiadp= 0)',
          'tdk_perawatan_mainiadp' => 'sum(bundles_hais.perawatan_mainiadp= 0)',
          'tdk_dreasing_mainiadp'  => 'sum(bundles_hais.dreasing_mainiadp= 0)',
          'tdk_infus_mainiadp'  => 'sum(bundles_hais.infus_mainiadp= 0)',

          'jml_hand_mainvena'  => 'sum(bundles_hais.hand_mainvena= 1)',
          'jml_perawatan_mainvena' => 'sum(bundles_hais.perawatan_mainvena= 1)',
          'jml_kaji_mainvena' => 'sum(bundles_hais.kaji_mainvena= 1)',
          'jml_administrasi_mainvena' => 'sum(bundles_hais.administrasi_mainvena= 1)',
          'jml_edukasi_mainvena' => 'sum(bundles_hais.edukasi_mainvena= 1)',
          'tdk_hand_mainvena'  => 'sum(bundles_hais.hand_mainvena= 0)',
          'tdk_perawatan_mainvena' => 'sum(bundles_hais.perawatan_mainvena= 0)',
          'tdk_kaji_mainvena' => 'sum(bundles_hais.kaji_mainvena= 0)',
          'tdk_administrasi_mainvena' => 'sum(bundles_hais.administrasi_mainvena= 0)',
          'tdk_edukasi_mainvena' => 'sum(bundles_hais.edukasi_mainvena= 0)',

          'jml_hand_mainisk' => 'sum(bundles_hais.hand_mainisk= 1)',
          'jml_kateter_mainisk' => 'sum(bundles_hais.kateter_mainisk= 1)',
          'jml_baglantai_mainisk' => 'sum(bundles_hais.baglantai_mainisk= 1)',
          'jml_bagrendah_mainisk' => 'sum(bundles_hais.bagrendah_mainisk= 1)',
          'jml_posisiselang_mainisk'  => 'sum(bundles_hais.posisiselang_mainisk= 1)',
          'jml_lepas_mainisk'   => 'sum(bundles_hais.lepas_mainisk= 1)',
          'tdk_hand_mainisk' => 'sum(bundles_hais.hand_mainisk= 0)',
          'tdk_kateter_mainisk' => 'sum(bundles_hais.kateter_mainisk= 0)',
          'tdk_baglantai_mainisk' => 'sum(bundles_hais.baglantai_mainisk= 0)',
          'tdk_bagrendah_mainisk' => 'sum(bundles_hais.bagrendah_mainisk= 0)',
          'tdk_posisiselang_mainisk'  => 'sum(bundles_hais.posisiselang_mainisk= 0)',
          'tdk_lepas_mainisk'   => 'sum(bundles_hais.lepas_mainisk= 0)',

          'jml_mandi_idopre'  => 'sum(bundles_hais.mandi_idopre= 1)',
          'jml_cukur_idopre' => 'sum(bundles_hais.cukur_idopre= 1)',
          'jml_guladarah_idopre' => 'sum(bundles_hais.guladarah_idopre= 1)',
          'jml_antibiotik_idopre'  => 'sum(bundles_hais.antibiotik_idopre= 1)',
          'tdk_mandi_idopre'  => 'sum(bundles_hais.mandi_idopre= 0)',
          'tdk_cukur_idopre' => 'sum(bundles_hais.cukur_idopre= 0)',
          'tdk_guladarah_idopre' => 'sum(bundles_hais.guladarah_idopre= 0)',
          'tdk_antibiotik_idopre'  => 'sum(bundles_hais.antibiotik_idopre= 0)',

          'jml_hand_idointra' => 'sum(bundles_hais.hand_idointra= 1)',
          'jml_steril_idointra' => 'sum(bundles_hais.steril_idointra= 1)',
          'jml_antiseptic_idointra' => 'sum(bundles_hais.antiseptic_idointra= 1)',
          'jml_tehnik_idointra'   => 'sum(bundles_hais.tehnik_idointra= 1)',
          'jml_mobile_idointra' => 'sum(bundles_hais.mobile_idointra= 1)',
          'jml_suhu_idointra'  => 'sum(bundles_hais.suhu_idointra= 1)',
          'tdk_hand_idointra' => 'sum(bundles_hais.hand_idointra= 0)',
          'tdk_steril_idointra' => 'sum(bundles_hais.steril_idointra= 0)',
          'tdk_antiseptic_idointra' => 'sum(bundles_hais.antiseptic_idointra= 0)',
          'tdk_tehnik_idointra'   => 'sum(bundles_hais.tehnik_idointra= 0)',
          'tdk_mobile_idointra' => 'sum(bundles_hais.mobile_idointra= 0)',
          'tdk_suhu_idointra'  => 'sum(bundles_hais.suhu_idointra= 0)',

          'jml_luka_idopost'   => 'sum(bundles_hais.luka_idopost= 1)',
          'jml_rawat_idopost'  => 'sum(bundles_hais.rawat_idopost= 1)',
          'jml_apd_idopost' => 'sum(bundles_hais.apd_idopost= 1)',
          'jml_kaji_idopost' => 'sum(bundles_hais.kaji_idopost= 1)',
          'tdk_luka_idopost'   => 'sum(bundles_hais.luka_idopost= 0)',
          'tdk_rawat_idopost'  => 'sum(bundles_hais.rawat_idopost= 0)',
          'tdk_apd_idopost' => 'sum(bundles_hais.apd_idopost= 0)',
          'tdk_kaji_idopost' => 'sum(bundles_hais.kaji_idopost= 0)',
          'tanggal' => 'bundles_hais.tanggal'

        ])
        ->where('bundles_hais.tanggal', $row['tanggal'])
        ->where('bundles_hais.kd_kamar', 'IGDK')
        ->orWhere('bundles_hais.kd_kamar', 'IGD01')
        ->toArray();
      $this->assign['list'][] = $row;
    }
    return $this->draw('lapbundles_igd.html', ['lapbundles_igd' => $this->assign]);
  }

   public function postBundlesIGD()
  {
    $this->_addHeaderFiles();
    $this->core->addCSS(url('https://cdn.datatables.net/buttons/2.2.3/css/buttons.dataTables.min.css'));
    $this->core->addJS(url('https://cdn.datatables.net/buttons/2.2.3/js/dataTables.buttons.min.js'), 'footer');
    $this->core->addJS(url('https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js'), 'footer');
    $this->core->addJS(url('https://cdn.datatables.net/buttons/1.3.1/js/buttons.html5.min.js'), 'footer');
    $this->core->addJS(url('https://cdn.datatables.net/plug-ins/1.12.1/api/sum().js'), 'footer');
    $this->core->addJS(url('https://cdn.datatables.net/datetime/1.1.2/js/dataTables.dateTime.min.js'), 'footer');


    if (isset($_POST['submit'])) {

      $date1 = $_POST['date1'];
      $date2 = $_POST['date2'];

      if (!empty($date1) && !empty($date2)) {
        $sql = "SELECT COUNT(no_rawat) as jml_no_rawat, tanggal
            FROM bundles_hais 
            WHERE (kd_kamar = 'IGDK' OR kd_kamar = 'IGD01') 
            AND tanggal BETWEEN '$date1' AND '$date2' 
            GROUP BY tanggal";

        $stmt = $this->db()->pdo()->prepare($sql);
        $stmt->execute();
        $rows = $stmt->fetchAll();

        $this->assign['list'] = [];
        foreach ($rows as $row) {
          $row['jumlah'] = $this->db('bundles_hais')
            ->select([
              'jml_no_rawat' => 'count(bundles_hais.no_rawat)',

              'jml_hand_vap' => 'sum(bundles_hais.hand_vap = 1)',
              'jml_tehniksteril_vap' => 'sum(bundles_hais.tehniksteril_vap = 1)',
              'jml_apd_vap'  => 'sum(bundles_hais.apd_vap = 1)',
              'jml_sedasi_vap' => 'sum(bundles_hais.sedasi_vap = 1)',
              'tdk_hand_vap' => 'sum(bundles_hais.hand_vap = 0)',
              'tdk_apd_vap'  => 'sum(bundles_hais.apd_vap = 0)',
              'tdk_tehniksteril_vap' => 'sum(bundles_hais.tehniksteril_vap = 0)',
              'tdk_sedasi_vap' => 'sum(bundles_hais.sedasi_vap = 0)',

              'jml_hand_iadp' => 'sum(bundles_hais.hand_iadp = 1)',
              'jml_area_iadp' => 'sum(bundles_hais.area_iadp = 1)',
              'jml_tehniksteril_iadp' => 'sum(bundles_hais.tehniksteril_iadp = 1)',
              'jml_alcohol_iadp'  => 'sum(bundles_hais.alcohol_iadp = 1)',
              'jml_apd_iadp' => 'sum(bundles_hais.apd_iadp = 0)',
              'tdk_hand_iadp' => 'sum(bundles_hais.hand_iadp = 0)',
              'tdk_area_iadp' => 'sum(bundles_hais.area_iadp = 0)',
              'tdk_tehniksteril_iadp' => 'sum(bundles_hais.tehniksteril_iadp = 0)',
              'tdk_alcohol_iadp'  => 'sum(bundles_hais.alcohol_iadp = 0)',
              'tdk_apd_iadp' => 'sum(bundles_hais.apd_iadp = 0)',

              'jml_hand_vena' => 'sum(bundles_hais.hand_vena = 1)',
              'jml_kaji_vena'  => 'sum(bundles_hais.kaji_vena = 1)',
              'jml_tehnik_vena'  => 'sum(bundles_hais.tehnik_vena = 1)',
              'jml_petugas_vena' => 'sum(bundles_hais.petugas_vena = 1)',
              'jml_desinfeksi_vena' => 'sum(bundles_hais.desinfeksi_vena = 1)',
              'tdk_hand_vena' => 'sum(bundles_hais.hand_vena = 0)',
              'tdk_kaji_vena'  => 'sum(bundles_hais.kaji_vena = 0)',
              'tdk_tehnik_vena'  => 'sum(bundles_hais.tehnik_vena = 0)',
              'tdk_petugas_vena' => 'sum(bundles_hais.petugas_vena = 0)',
              'tdk_desinfeksi_vena' => 'sum(bundles_hais.desinfeksi_vena = 0)',

              'jml_kaji_isk' => 'sum(bundles_hais.kaji_isk = 1)',
              'jml_petugas_isk'  => 'sum(bundles_hais.petugas_isk = 1)',
              'jml_tangan_isk' => 'sum(bundles_hais.tangan_isk = 1)',
              'jml_tehniksteril_isk' => 'sum(bundles_hais.tehniksteril_isk = 1)',
              'tdk_kaji_isk' => 'sum(bundles_hais.kaji_isk = 0)',
              'tdk_petugas_isk'  => 'sum(bundles_hais.petugas_isk = 0)',
              'tdk_tangan_isk' => 'sum(bundles_hais.tangan_isk = 0)',
              'tdk_tehniksteril_isk' => 'sum(bundles_hais.tehniksteril_isk = 0)',

              'jml_hand_mainvap'  => 'sum(bundles_hais.hand_mainvap = 1)',
              'jml_oral_mainvap' => 'sum(bundles_hais.oral_mainvap = 1)',
              'jml_manage_mainvap' => 'sum(bundles_hais.manage_mainvap= 1)',
              'jml_sedasi_mainvap'   => 'sum(bundles_hais.sedasi_mainvap= 1)',
              'jml_kepala_mainvap'  => 'sum(bundles_hais.kepala_mainvap= 1)',
              'tdk_hand_mainvap'  => 'sum(bundles_hais.hand_mainvap = 0)',
              'tdk_oral_mainvap' => 'sum(bundles_hais.oral_mainvap = 0)',
              'tdk_manage_mainvap' => 'sum(bundles_hais.manage_mainvap= 0)',
              'tdk_sedasi_mainvap'   => 'sum(bundles_hais.sedasi_mainvap= 0)',
              'tdk_kepala_mainvap'  => 'sum(bundles_hais.kepala_mainvap= 0)',

              'jml_hand_mainiadp' => 'sum(bundles_hais.hand_mainiadp= 1)',
              'jml_desinfeksi_mainiadp'  => 'sum(bundles_hais.desinfeksi_mainiadp= 1)',
              'jml_perawatan_mainiadp' => 'sum(bundles_hais.perawatan_mainiadp= 1)',
              'jml_dreasing_mainiadp'  => 'sum(bundles_hais.dreasing_mainiadp= 1)',
              'jml_infus_mainiadp'  => 'sum(bundles_hais.infus_mainiadp= 1)',
              'tdk_hand_mainiadp' => 'sum(bundles_hais.hand_mainiadp= 0)',
              'tdk_desinfeksi_mainiadp'  => 'sum(bundles_hais.desinfeksi_mainiadp= 0)',
              'tdk_perawatan_mainiadp' => 'sum(bundles_hais.perawatan_mainiadp= 0)',
              'tdk_dreasing_mainiadp'  => 'sum(bundles_hais.dreasing_mainiadp= 0)',
              'tdk_infus_mainiadp'  => 'sum(bundles_hais.infus_mainiadp= 0)',

              'jml_hand_mainvena'  => 'sum(bundles_hais.hand_mainvena= 1)',
              'jml_perawatan_mainvena' => 'sum(bundles_hais.perawatan_mainvena= 1)',
              'jml_kaji_mainvena' => 'sum(bundles_hais.kaji_mainvena= 1)',
              'jml_administrasi_mainvena' => 'sum(bundles_hais.administrasi_mainvena= 1)',
              'jml_edukasi_mainvena' => 'sum(bundles_hais.edukasi_mainvena= 1)',
              'tdk_hand_mainvena'  => 'sum(bundles_hais.hand_mainvena= 0)',
              'tdk_perawatan_mainvena' => 'sum(bundles_hais.perawatan_mainvena= 0)',
              'tdk_kaji_mainvena' => 'sum(bundles_hais.kaji_mainvena= 0)',
              'tdk_administrasi_mainvena' => 'sum(bundles_hais.administrasi_mainvena= 0)',
              'tdk_edukasi_mainvena' => 'sum(bundles_hais.edukasi_mainvena= 0)',

              'jml_hand_mainisk' => 'sum(bundles_hais.hand_mainisk= 1)',
              'jml_kateter_mainisk' => 'sum(bundles_hais.kateter_mainisk= 1)',
              'jml_baglantai_mainisk' => 'sum(bundles_hais.baglantai_mainisk= 1)',
              'jml_bagrendah_mainisk' => 'sum(bundles_hais.bagrendah_mainisk= 1)',
              'jml_posisiselang_mainisk'  => 'sum(bundles_hais.posisiselang_mainisk= 1)',
              'jml_lepas_mainisk'   => 'sum(bundles_hais.lepas_mainisk= 1)',
              'tdk_hand_mainisk' => 'sum(bundles_hais.hand_mainisk= 0)',
              'tdk_kateter_mainisk' => 'sum(bundles_hais.kateter_mainisk= 0)',
              'tdk_baglantai_mainisk' => 'sum(bundles_hais.baglantai_mainisk= 0)',
              'tdk_bagrendah_mainisk' => 'sum(bundles_hais.bagrendah_mainisk= 0)',
              'tdk_posisiselang_mainisk'  => 'sum(bundles_hais.posisiselang_mainisk= 0)',
              'tdk_lepas_mainisk'   => 'sum(bundles_hais.lepas_mainisk= 0)',

              'jml_mandi_idopre'  => 'sum(bundles_hais.mandi_idopre= 1)',
              'jml_cukur_idopre' => 'sum(bundles_hais.cukur_idopre= 1)',
              'jml_guladarah_idopre' => 'sum(bundles_hais.guladarah_idopre= 1)',
              'jml_antibiotik_idopre'  => 'sum(bundles_hais.antibiotik_idopre= 1)',
              'tdk_mandi_idopre'  => 'sum(bundles_hais.mandi_idopre= 0)',
              'tdk_cukur_idopre' => 'sum(bundles_hais.cukur_idopre= 0)',
              'tdk_guladarah_idopre' => 'sum(bundles_hais.guladarah_idopre= 0)',
              'tdk_antibiotik_idopre'  => 'sum(bundles_hais.antibiotik_idopre= 0)',

              'jml_hand_idointra' => 'sum(bundles_hais.hand_idointra= 1)',
              'jml_steril_idointra' => 'sum(bundles_hais.steril_idointra= 1)',
              'jml_antiseptic_idointra' => 'sum(bundles_hais.antiseptic_idointra= 1)',
              'jml_tehnik_idointra'   => 'sum(bundles_hais.tehnik_idointra= 1)',
              'jml_mobile_idointra' => 'sum(bundles_hais.mobile_idointra= 1)',
              'jml_suhu_idointra'  => 'sum(bundles_hais.suhu_idointra= 1)',
              'tdk_hand_idointra' => 'sum(bundles_hais.hand_idointra= 0)',
              'tdk_steril_idointra' => 'sum(bundles_hais.steril_idointra= 0)',
              'tdk_antiseptic_idointra' => 'sum(bundles_hais.antiseptic_idointra= 0)',
              'tdk_tehnik_idointra'   => 'sum(bundles_hais.tehnik_idointra= 0)',
              'tdk_mobile_idointra' => 'sum(bundles_hais.mobile_idointra= 0)',
              'tdk_suhu_idointra'  => 'sum(bundles_hais.suhu_idointra= 0)',

              'jml_luka_idopost'   => 'sum(bundles_hais.luka_idopost= 1)',
              'jml_rawat_idopost'  => 'sum(bundles_hais.rawat_idopost= 1)',
              'jml_apd_idopost' => 'sum(bundles_hais.apd_idopost= 1)',
              'jml_kaji_idopost' => 'sum(bundles_hais.kaji_idopost= 1)',
              'tdk_luka_idopost'   => 'sum(bundles_hais.luka_idopost= 0)',
              'tdk_rawat_idopost'  => 'sum(bundles_hais.rawat_idopost= 0)',
              'tdk_apd_idopost' => 'sum(bundles_hais.apd_idopost= 0)',
              'tdk_kaji_idopost' => 'sum(bundles_hais.kaji_idopost= 0)',
              'tanggal' => 'bundles_hais.tanggal'

            ])
            ->where('bundles_hais.tanggal', $row['tanggal'])
            ->where('bundles_hais.kd_kamar', 'IGDK')
            ->orWhere('bundles_hais.kd_kamar', 'IGD01')
            ->toArray();
          $this->assign['list'][] = $row;
        }
      } else {
        $this->getBundlesIGD();
      }
    }
    $this->core->addCSS(url('assets/css/dataTables.bootstrap.min.css'));
    $this->core->addCSS(url([ADMIN, 'laporan_bundles', 'css']));
    $this->core->addJS(url('assets/jscripts/jquery.dataTables.min.js'));
    $this->core->addJS(url('assets/jscripts/dataTables.bootstrap.min.js'));
    return $this->draw('lapbundles_igd.html', ['lapbundles_igd' => $this->assign]);
  }

  public function getBundlesTanggal($page = 1)
  {
      $this->_addHeaderFiles();
      $this->core->addCSS(url('https://cdn.datatables.net/buttons/2.2.3/css/buttons.dataTables.min.css'));
      $this->core->addJS(url('https://cdn.datatables.net/buttons/2.2.3/js/dataTables.buttons.min.js'), 'footer');
      $this->core->addJS(url('https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js'), 'footer');
      $this->core->addJS(url('https://cdn.datatables.net/buttons/1.3.1/js/buttons.html5.min.js'), 'footer');
      $this->core->addJS(url('https://cdn.datatables.net/plug-ins/1.12.1/api/sum().js'), 'footer');
      
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

      $ruang = '';
      if (isset($_GET['ruang'])) {
          $ruang = $_GET['ruang'];
      }

          $totalRecords = $this->db('bundles_hais')
              ->join('kamar', 'kamar.kd_kamar = bundles_hais.kd_kamar')
              ->join('bangsal', 'bangsal.kd_bangsal = kamar.kd_bangsal')
              ->where('tanggal', '>=', $tgl_kunjungan . ' 00:00:00')
              ->where('tanggal', '<=', $tgl_kunjungan_akhir . ' 23:59:59')
              ->like('nm_bangsal', '%' . $ruang . '%')
              ->like('bundles_hais.no_rawat', '%' . $phrase . '%')
              ->asc('tanggal')
              ->toArray();

      $pagination = new \Systems\Lib\Pagination($page, count($totalRecords), 10, url([ADMIN, 'laporan_bundles', 'bundlesbangsal', '%d?awal=' . $tgl_kunjungan . '&akhir=' . $tgl_kunjungan_akhir . '&ruang=' . $ruang . '&s=' . $phrase]));
      $this->assign['pagination'] = $pagination->nav('pagination', '5');
      $this->assign['totalRecords'] = $totalRecords;

      // list
      $offset = $pagination->offset();

          $rows = $this->db('bundles_hais')
              ->select([
                'no_rawat' => 'bundles_hais.no_rawat',
                'nm_bangsal' => 'bangsal.nm_bangsal',
                'tanggal'   => 'bundles_hais.tanggal',
                'jml_hand_vap' => 'bundles_hais.hand_vap = 1',
                'jml_tehniksteril_vap' => 'bundles_hais.tehniksteril_vap = 1',
                'jml_apd_vap'  => 'bundles_hais.apd_vap = 1',
                'jml_sedasi_vap' => 'bundles_hais.sedasi_vap = 1',
                'tdk_hand_vap' => 'bundles_hais.hand_vap = 0',
                'tdk_apd_vap'  => 'bundles_hais.apd_vap = 0',
                'tdk_tehniksteril_vap' => 'bundles_hais.tehniksteril_vap = 0',
                'tdk_sedasi_vap' => 'bundles_hais.sedasi_vap = 0',

                'jml_hand_iadp' => 'bundles_hais.hand_iadp = 1',
                'jml_area_iadp' => 'bundles_hais.area_iadp = 1',
                'jml_tehniksteril_iadp' => 'bundles_hais.tehniksteril_iadp = 1',
                'jml_alcohol_iadp'  => 'bundles_hais.alcohol_iadp = 1',
                'jml_apd_iadp' => 'bundles_hais.apd_iadp = 0',
                'tdk_hand_iadp' => 'bundles_hais.hand_iadp = 0',
                'tdk_area_iadp' => 'bundles_hais.area_iadp = 0',
                'tdk_tehniksteril_iadp' => 'bundles_hais.tehniksteril_iadp = 0',
                'tdk_alcohol_iadp'  => 'bundles_hais.alcohol_iadp = 0',
                'tdk_apd_iadp' => 'bundles_hais.apd_iadp = 0',

                'jml_hand_vena' => 'bundles_hais.hand_vena = 1',
                'jml_kaji_vena'  => 'bundles_hais.kaji_vena = 1',
                'jml_tehnik_vena'  => 'bundles_hais.tehnik_vena = 1',
                'jml_petugas_vena' => 'bundles_hais.petugas_vena = 1',
                'jml_desinfeksi_vena' => 'bundles_hais.desinfeksi_vena = 1',
                'tdk_hand_vena' => 'bundles_hais.hand_vena = 0',
                'tdk_kaji_vena'  => 'bundles_hais.kaji_vena = 0',
                'tdk_tehnik_vena'  => 'bundles_hais.tehnik_vena = 0',
                'tdk_petugas_vena' => 'bundles_hais.petugas_vena = 0',
                'tdk_desinfeksi_vena' => 'bundles_hais.desinfeksi_vena = 0',

                'jml_kaji_isk' => 'bundles_hais.kaji_isk = 1',
                'jml_petugas_isk'  => 'bundles_hais.petugas_isk = 1',
                'jml_tangan_isk' => 'bundles_hais.tangan_isk = 1',
                'jml_tehniksteril_isk' => 'bundles_hais.tehniksteril_isk = 1',
                'tdk_kaji_isk' => 'bundles_hais.kaji_isk = 0',
                'tdk_petugas_isk'  => 'bundles_hais.petugas_isk = 0',
                'tdk_tangan_isk' => 'bundles_hais.tangan_isk = 0',
                'tdk_tehniksteril_isk' => 'bundles_hais.tehniksteril_isk = 0',

                'jml_hand_mainvap'  => 'bundles_hais.hand_mainvap = 1',
                'jml_oral_mainvap' => 'bundles_hais.oral_mainvap = 1',
                'jml_manage_mainvap' => 'bundles_hais.manage_mainvap= 1',
                'jml_sedasi_mainvap'   => 'bundles_hais.sedasi_mainvap= 1',
                'jml_kepala_mainvap'  => 'bundles_hais.kepala_mainvap= 1',
                'tdk_hand_mainvap'  => 'bundles_hais.hand_mainvap = 0',
                'tdk_oral_mainvap' => 'bundles_hais.oral_mainvap = 0',
                'tdk_manage_mainvap' => 'bundles_hais.manage_mainvap= 0',
                'tdk_sedasi_mainvap'   => 'bundles_hais.sedasi_mainvap= 0',
                'tdk_kepala_mainvap'  => 'bundles_hais.kepala_mainvap= 0',

                'jml_hand_mainiadp' => 'bundles_hais.hand_mainiadp= 1',
                'jml_desinfeksi_mainiadp'  => 'bundles_hais.desinfeksi_mainiadp= 1',
                'jml_perawatan_mainiadp' => 'bundles_hais.perawatan_mainiadp= 1',
                'jml_dreasing_mainiadp'  => 'bundles_hais.dreasing_mainiadp= 1',
                'jml_infus_mainiadp'  => 'bundles_hais.infus_mainiadp= 1',
                'tdk_hand_mainiadp' => 'bundles_hais.hand_mainiadp= 0',
                'tdk_desinfeksi_mainiadp'  => 'bundles_hais.desinfeksi_mainiadp= 0',
                'tdk_perawatan_mainiadp' => 'bundles_hais.perawatan_mainiadp= 0',
                'tdk_dreasing_mainiadp'  => 'bundles_hais.dreasing_mainiadp= 0',
                'tdk_infus_mainiadp'  => 'bundles_hais.infus_mainiadp= 0',

                'jml_hand_mainvena'  => 'bundles_hais.hand_mainvena= 1',
                'jml_perawatan_mainvena' => 'bundles_hais.perawatan_mainvena= 1',
                'jml_kaji_mainvena' => 'bundles_hais.kaji_mainvena= 1',
                'jml_administrasi_mainvena' => 'bundles_hais.administrasi_mainvena= 1',
                'jml_edukasi_mainvena' => 'bundles_hais.edukasi_mainvena= 1',
                'tdk_hand_mainvena'  => 'bundles_hais.hand_mainvena= 0',
                'tdk_perawatan_mainvena' => 'bundles_hais.perawatan_mainvena= 0',
                'tdk_kaji_mainvena' => 'bundles_hais.kaji_mainvena= 0',
                'tdk_administrasi_mainvena' => 'bundles_hais.administrasi_mainvena= 0',
                'tdk_edukasi_mainvena' => 'bundles_hais.edukasi_mainvena= 0',

                'jml_hand_mainisk' => 'bundles_hais.hand_mainisk= 1',
                'jml_kateter_mainisk' => 'bundles_hais.kateter_mainisk= 1',
                'jml_baglantai_mainisk' => 'bundles_hais.baglantai_mainisk= 1',
                'jml_bagrendah_mainisk' => 'bundles_hais.bagrendah_mainisk= 1',
                'jml_posisiselang_mainisk'  => 'bundles_hais.posisiselang_mainisk= 1',
                'jml_lepas_mainisk'   => 'bundles_hais.lepas_mainisk= 1',
                'tdk_hand_mainisk' => 'bundles_hais.hand_mainisk= 0',
                'tdk_kateter_mainisk' => 'bundles_hais.kateter_mainisk= 0',
                'tdk_baglantai_mainisk' => 'bundles_hais.baglantai_mainisk= 0',
                'tdk_bagrendah_mainisk' => 'bundles_hais.bagrendah_mainisk= 0',
                'tdk_posisiselang_mainisk'  => 'bundles_hais.posisiselang_mainisk= 0',
                'tdk_lepas_mainisk'   => 'bundles_hais.lepas_mainisk= 0',

                'jml_mandi_idopre'  => 'bundles_hais.mandi_idopre= 1',
                'jml_cukur_idopre' => 'bundles_hais.cukur_idopre= 1',
                'jml_guladarah_idopre' => 'bundles_hais.guladarah_idopre= 1',
                'jml_antibiotik_idopre'  => 'bundles_hais.antibiotik_idopre= 1',
                'tdk_mandi_idopre'  => 'bundles_hais.mandi_idopre= 0',
                'tdk_cukur_idopre' => 'bundles_hais.cukur_idopre= 0',
                'tdk_guladarah_idopre' => 'bundles_hais.guladarah_idopre= 0',
                'tdk_antibiotik_idopre'  => 'bundles_hais.antibiotik_idopre= 0',

                'jml_hand_idointra' => 'bundles_hais.hand_idointra= 1',
                'jml_steril_idointra' => 'bundles_hais.steril_idointra= 1',
                'jml_antiseptic_idointra' => 'bundles_hais.antiseptic_idointra= 1',
                'jml_tehnik_idointra'   => 'bundles_hais.tehnik_idointra= 1',
                'jml_mobile_idointra' => 'bundles_hais.mobile_idointra= 1',
                'jml_suhu_idointra'  => 'bundles_hais.suhu_idointra= 1',
                'tdk_hand_idointra' => 'bundles_hais.hand_idointra= 0',
                'tdk_steril_idointra' => 'bundles_hais.steril_idointra= 0',
                'tdk_antiseptic_idointra' => 'bundles_hais.antiseptic_idointra= 0',
                'tdk_tehnik_idointra'   => 'bundles_hais.tehnik_idointra= 0',
                'tdk_mobile_idointra' => 'bundles_hais.mobile_idointra= 0',
                'tdk_suhu_idointra'  => 'bundles_hais.suhu_idointra= 0',

                'jml_luka_idopost'   => 'bundles_hais.luka_idopost= 1',
                'jml_rawat_idopost'  => 'bundles_hais.rawat_idopost= 1',
                'jml_apd_idopost' => 'bundles_hais.apd_idopost= 1',
                'jml_kaji_idopost' => 'bundles_hais.kaji_idopost= 1',
                'tdk_luka_idopost'   => 'bundles_hais.luka_idopost= 0',
                'tdk_rawat_idopost'  => 'bundles_hais.rawat_idopost= 0',
                'tdk_apd_idopost' => 'bundles_hais.apd_idopost= 0',
                'tdk_kaji_idopost' => 'bundles_hais.kaji_idopost= 0'

              ])
              ->join('kamar', 'kamar.kd_kamar = bundles_hais.kd_kamar')
              ->join('bangsal', 'bangsal.kd_bangsal = kamar.kd_bangsal')
              ->where('tanggal', '>=', $tgl_kunjungan . ' 00:00:00')
              ->where('tanggal', '<=', $tgl_kunjungan_akhir . ' 23:59:59')
              ->like('nm_bangsal', '%' . $ruang . '%')
              ->like('bundles_hais.no_rawat', '%' . $phrase . '%')
              ->asc('tanggal')
              ->offset($offset)
              ->limit($perpage)
              ->toArray();

      $this->assign['list'] = [];
      if (count($rows)) {
          foreach ($rows as $row) {
              $row = htmlspecialchars_array($row);

              $day = array(
                  'Sun' => 'AKHAD',
                  'Mon' => 'SENIN',
                  'Tue' => 'SELASA',
                  'Wed' => 'RABU',
                  'Thu' => 'KAMIS',
                  'Fri' => 'JUMAT',
                  'Sat' => 'SABTU'
              );

            

              $this->assign['list'][] = $row;
          }
      }

      $this->assign['bangsal'] = $this->db('bangsal')->where('status', '1')->toArray();
      return $this->draw('lap_bundlesbangsal.html', ['rekap' => $this->assign]);
  }

  public function getCSS()
  {
    header('Content-type: text/css');
    echo $this->draw(MODULES . '/laporan_bundles/css/admin/laporan_bundles.css');
    exit();
  }

  public function getJavascript()
  {
    header('Content-type: text/javascript');
    echo $this->draw(MODULES . '/laporan_bundles/js/admin/laporan_bundles.js');
    exit();
  }

  private function _addHeaderFiles()
  {
    // CSS
    $this->core->addCSS(url('assets/css/dataTables.bootstrap.min.css'));
    $this->core->addCSS(url('assets/css/jquery-ui.css'));
    $this->core->addCSS(url('assets/jscripts/lightbox/lightbox.min.css'));
    $this->core->addCSS(url('assets/css/bootstrap-datetimepicker.css'));


    // JS
    $this->core->addJS(url('assets/jscripts/jquery.dataTables.min.js'), 'footer');
    $this->core->addJS(url('assets/jscripts/dataTables.bootstrap.min.js'), 'footer');

    $this->core->addJS(url('assets/jscripts/jquery-ui.js'), 'footer');
    $this->core->addJS(url('assets/jscripts/lightbox/lightbox.min.js'), 'footer');
    $this->core->addJS(url('assets/jscripts/moment-with-locales.js'));
    $this->core->addJS(url('assets/jscripts/bootstrap-datetimepicker.js'));

    // MODULE SCRIPTS
    $this->core->addCSS(url([ADMIN, 'laporan_bundles', 'css']));
    $this->core->addJS(url([ADMIN, 'laporan_bundles', 'javascript']), 'footer');
  }
}