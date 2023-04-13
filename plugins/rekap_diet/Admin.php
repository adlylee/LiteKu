<?php

namespace Plugins\Rekap_Diet;

use Systems\AdminModule;
use Systems\Lib\Fpdf\PDF_MC_Table;

class Admin extends AdminModule
{   
      public function navigation()
    {
        return [
            'Kelola'   => 'manage',
        ];
    }

    public function getManage()
    {
        $sub_modules = [
            ['name' => 'Rekap Diet Pasien', 'url' => url([ADMIN, 'rekap_diet', 'rekap_dietpasien']), 'icon' => 'cubes', 'desc' => 'Rekap Diet Pasien'],
            ['name' => 'Tambah Item Diet Baru', 'url' => url([ADMIN, 'rekap_diet', 'itemdiet']), 'icon' => 'plus-square', 'desc' => 'Tambah Item Diet baru'],
          ];
        return $this->draw('manage.html', ['sub_modules' => $sub_modules]);
    }

    public function getRekap_Dietpasien($page = 1)
    {
        $this->_addHeaderFiles();
        $perpage = '10';
        $phrase = '';
        if (isset($_GET['s']))
            $phrase = $_GET['s'];

        $tgl_kunjungan = date('Y-m-d');
        $tgl_kunjungan_akhir = date('Y-m-d');
        // $status_periksa = '';
        // $status_bayar = '';

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

       // $username = $this->core->getUserInfo('username', null, true);

       
         $totalRecords = $this->db('detail_beri_diet')
                ->join('diet', 'diet.kd_diet = detail_beri_diet.kd_diet')
                ->join('reg_periksa', 'reg_periksa.no_rawat=detail_beri_diet.no_rawat')
                ->join('pasien', 'pasien.no_rkm_medis=reg_periksa.no_rkm_medis')
                ->join('kamar', 'kamar.kd_kamar=detail_beri_diet.kd_kamar')
                ->join('bangsal', 'bangsal.kd_bangsal=kamar.kd_bangsal')
                ->where('tanggal', '>=', $tgl_kunjungan.' 00:00:00')
                ->where('tanggal', '<=', $tgl_kunjungan_akhir.' 23:59:59')
                ->like('nm_bangsal', '%' . $ruang . '%')
                ->like('detail_beri_diet.no_rawat', '%' . $phrase . '%')
                ->asc('tanggal')
                ->toArray();
      
        $pagination = new \Systems\Lib\Pagination($page, count($totalRecords), 10, url([ADMIN, 'rekap_diet', 'rekap_dietpasien', '%d?awal=' . $tgl_kunjungan . '&akhir=' . $tgl_kunjungan_akhir . '&ruang=' . $ruang . '&s=' . $phrase]));
        $this->assign['pagination'] = $pagination->nav('pagination', '5');
        $this->assign['totalRecords'] = $totalRecords;

        // list
        $offset = $pagination->offset();

            $rows = $this->db('detail_beri_diet')
                ->select([
                    'no_rawat' => 'detail_beri_diet.no_rawat',
                    // 'no_rkm_medis' => 'reg_periksa.no_rkm_medis',
                    // 'nm_pasien' => 'pasien.nm_pasien',
                    'kd_kamar' => 'detail_beri_diet.kd_kamar',
                    'nm_bangsal' => 'bangsal.nm_bangsal',
                    'tanggal' => 'detail_beri_diet.tanggal',
                    'waktu' => 'detail_beri_diet.waktu',
                    'nama_diet' => 'diet.nama_diet',
                    // 'kd_penyakit' => 'diagnosa_pasien.kd_penyakit',
                    // 'nm_penyakit' => 'penyakit.nm_penyakit'
                ])
                ->join('diet', 'diet.kd_diet = detail_beri_diet.kd_diet')
                ->join('reg_periksa', 'reg_periksa.no_rawat=detail_beri_diet.no_rawat')
                ->join('pasien', 'pasien.no_rkm_medis=reg_periksa.no_rkm_medis')
                ->join('kamar', 'kamar.kd_kamar=detail_beri_diet.kd_kamar')
                ->join('bangsal', 'bangsal.kd_bangsal=kamar.kd_bangsal')
                ->where('tanggal', '>=', $tgl_kunjungan.' 00:00:00')
                ->where('tanggal', '<=', $tgl_kunjungan_akhir.' 23:59:59')
                ->like('nm_bangsal', '%' . $ruang . '%')
                ->like('detail_beri_diet.no_rawat', '%' . $phrase . '%')
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


                $pasien = $this->db('reg_periksa')
                  ->join('pasien', 'pasien.no_rkm_medis=reg_periksa.no_rkm_medis')
                  ->where('no_rawat', $row['no_rawat'])
                  ->oneArray();
                $row['nm_pasien'] = $pasien['nm_pasien'];
                $row['no_rkm_medis'] = $pasien['no_rkm_medis'];

                $row['diagnosa'] = $this->db('diagnosa_pasien')
                  ->select(['kd_penyakit' => 'diagnosa_pasien.kd_penyakit',
                    'nm_penyakit' => 'penyakit.nm_penyakit'])
                  ->join('penyakit', 'penyakit.kd_penyakit=diagnosa_pasien.kd_penyakit')
                  ->where('no_rawat', $row['no_rawat'])
                  ->asc('prioritas')
                  ->toArray();
                $this->assign['list'][] = $row;
            }
        }

        
        $this->assign['tahun'] = array('', '2020', '2021', '2022');
        $this->assign['bulan'] = array('', '01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12');
        $this->assign['tanggal'] = array('', '01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12', '13', '14', '15', '16', '17', '18', '19', '20', '21', '22', '23', '24', '25', '26', '27', '28', '29', '30', '31');
        $this->assign['bangsal'] = $this->db('bangsal')->where('status', '1')->toArray();
        return $this->draw('rekap_dietpasien.html', ['rekap' => $this->assign]);
    }

     public function getItemdiet()
    {
      $this->_addHeaderFiles();
      $rows = $this->db('diet')->toArray();

        $this->assign['list'] = [];
        if (count($rows)) {
            foreach ($rows as $row) {
                $row = htmlspecialchars_array($row);
                //$row['delURL']  = url([ADMIN, 'rekap_diet', 'delete', $row['kd_diet']]);
                $row['delURL']  = url([ADMIN, 'rekap_diet', 'hapusitemdiet', $row['kd_diet']]);
                $this->assign['list'][] = $row;
            }
        }

      return $this->draw('item_dietbaru.html',['itemdiet' => $this->assign]);
    }

    public function anyForm()
    {
    
      $this->_addHeaderFiles();
      if (isset($_POST['kd_diet'])){
        $diet = $this->db('diet')->where('kd_diet', $_POST['kd_diet'])->oneArray();
        echo $this->draw('form_itemdiet.html', [
          'diet' => $diet,
        ]);
      } else {
        $diet = [
          'kd_diet' => '',
          'nama_diet' => ''
        ];
        echo $this->draw('form_itemdiet.html', [
          'diet' => $diet,
        ]);
       }
      exit();
    }

    public function postItemDietSave()
  {
  
    $kd_diet =  $_POST['kd_diet'];

    $location = url([ADMIN, 'rekap_diet', 'itemdiet']);
    
    if (!$this->db('diet')->where('kd_diet', $_POST['kd_diet'])->oneArray()) {
      $query = $this->db('diet')
        ->save([
            'kd_diet' => $kd_diet,
            'nama_diet' => $_POST['nama_diet']
          ]);
    } else {
      $query = $this->db('diet')
        ->where('kd_diet', $_POST['kd_diet'])
        ->save([
            'nama_diet' => $_POST['nama_diet']
          ]);
    }  

    if ($query) {
        $this->notify('success','Berhasil Simpan');
    } else {
      $this->notify('failure','Gagal Simpan');
    }

    redirect($location);
   }

    public function getHapusItemDiet($kd_diet)
  {
      if ($this->db('diet')->where('kd_diet', $kd_diet)->delete()) {
            $this->notify('success', 'Data berhasil dihapus.');
        } else {
            $this->notify('failure', 'Gagal dihapus.');
        }
   

    redirect(url([ADMIN, 'rekap_diet', 'itemdiet']));
  }
  
   public function getKodeDiet()
    {
     $last_kd_diet = $this->db()->pdo()->prepare("SELECT ifnull(MAX(CONVERT(RIGHT(kd_diet,3),signed)),0) FROM diet WHERE kd_diet LIKE '%D%'");
      $last_kd_diet->execute();
      $last_kd_diet = $last_kd_diet->fetch();
      if(empty($last_kd_diet[0])) {
        $last_kd_diet[0] = '000';
      }
      $next_kd_diet = sprintf('%03s', ($last_kd_diet[0] + 1));
      $next_kd_diet = 'D' .''. $next_kd_diet;

      echo $next_kd_diet;
      exit();
    }

    public function getJavascript()
    {
        header('Content-type: text/javascript');
        echo $this->draw(MODULES . '/rekap_diet/js/admin/rekap_diet.js');
        exit();
    }

    private function _addHeaderFiles()
    {
        // CSS
        $this->core->addCSS(url('assets/css/jquery-ui.css'));
        $this->core->addCSS(url('assets/jscripts/lightbox/lightbox.min.css'));
        $this->core->addCSS(url('assets/css/bootstrap-datetimepicker.css'));
        $this->core->addCSS(url('assets/css/dataTables.bootstrap.min.css'));

        // JS
        $this->core->addJS(url('assets/jscripts/jquery.dataTables.min.js'), 'footer');
        $this->core->addJS(url('assets/jscripts/dataTables.bootstrap.min.js'), 'footer');
        $this->core->addJS(url('assets/jscripts/jquery-ui.js'), 'footer');
        $this->core->addJS(url('assets/jscripts/lightbox/lightbox.min.js'), 'footer');
        $this->core->addJS(url('assets/jscripts/moment-with-locales.js'));
        $this->core->addJS(url('assets/jscripts/bootstrap-datetimepicker.js'));

        // MODULE SCRIPTS
        $this->core->addJS(url([ADMIN, 'rekap_diet', 'javascript']), 'footer');
    }
}