<?php

namespace Plugins\Farmasi;

use Systems\AdminModule;

class Admin extends AdminModule
{

    public function navigation()
    {
        return [
            'Kelola' => 'manage',
            'Obat & BHP' => 'index',
            'Stok Opname' => 'opname',
            'Pengaturan' => 'settings',
        ];
    }

    public function getManage()
    {
      $sub_modules = [
        ['name' => 'Obat & BHP', 'url' => url([ADMIN, 'farmasi', 'index']), 'icon' => 'medkit', 'desc' => 'Data obat dan barang habis pakai'],
        ['name' => 'Stok Opname', 'url' => url([ADMIN, 'farmasi', 'opname']), 'icon' => 'medkit', 'desc' => 'Tambah stok opname'],
        ['name' => 'Pengaturan', 'url' => url([ADMIN, 'farmasi', 'settings']), 'icon' => 'medkit', 'desc' => 'Pengaturan farmasi dan depo'],
        ['name' => 'Laporan Obat ', 'url' => url([ADMIN, 'farmasi', 'lapobat']), 'icon' => 'medkit', 'desc' => 'Laporan Pemberian Obat'],
      ];
      return $this->draw('manage.html', ['sub_modules' => $sub_modules]);
    }

    public function getIndex($status = '1')
    {
        $this->_addHeaderFiles();
        $databarang['title'] = 'Kelola Databarang';
        $databarang['bangsal']  = $this->db('bangsal')->toArray();
        $databarang['list'] = $this->_databarangList($status);
        return $this->draw('index.html', ['databarang' => $databarang, 'tab' => $status]);
    }

    private function _databarangList($status)
    {
        $result = [];

        foreach ($this->db('databarang')->where('status', $status)->toArray() as $row) {
            $row['delURL']  = url([ADMIN, 'farmasi', 'delete', $row['kode_brng']]);
            $row['restoreURL']  = url([ADMIN, 'farmasi', 'restore', $row['kode_brng']]);
            $row['gudangbarang'] = $this->db('gudangbarang')->join('bangsal', 'bangsal.kd_bangsal=gudangbarang.kd_bangsal')->where('kode_brng', $row['kode_brng'])->toArray();
            $result[] = $row;
        }
        return $result;
    }

    public function getDelete($id)
    {
        if ($this->core->db('databarang')->where('kode_brng', $id)->update('status', '0')) {
            $this->notify('success', 'Hapus sukses');
        } else {
            $this->notify('failure', 'Hapus gagal');
        }
        redirect(url([ADMIN, 'farmasi', 'index']));
    }

    public function getRestore($id)
    {
        if ($this->core->db('databarang')->where('kode_brng', $id)->update('status', '1')) {
            $this->notify('success', 'Restore sukses');
        } else {
            $this->notify('failure', 'Restore gagal');
        }
        redirect(url([ADMIN, 'farmasi', 'index']));
    }

    public function postSetStok()
    {
      if($this->db('gudangbarang')->where('kode_brng', $_POST['kode_brng'])->where('kd_bangsal', $_POST['kd_bangsal'])->oneArray()) {

        $get_gudangbarang = $this->db('gudangbarang')->where('kode_brng', $_POST['kode_brng'])->where('kd_bangsal', $this->settings->get('farmasi.gudang'))->oneArray();
        $gudangbarang = $this->db('gudangbarang')->where('kode_brng', $_POST['kode_brng'])->where('kd_bangsal', $_POST['kd_bangsal'])->oneArray();

        if($_POST['kd_bangsal'] == $this->settings->get('farmasi.gudang')) {
          $query = $this->db('riwayat_barang_medis')
            ->save([
              'kode_brng' => $_POST['kode_brng'],
              'stok_awal' => $get_gudangbarang['stok'],
              'masuk' => $_POST['stok'],
              'keluar' => '0',
              'stok_akhir' => $stok + $_POST['stok'],
              'posisi' => 'Pengadaan',
              'tanggal' => date('Y-m-d'),
              'jam' => date('H:i:s'),
              'petugas' => $this->core->getUserInfo('fullname', null, true),
              'kd_bangsal' => $this->settings->get('farmasi.gudang'),
              'status' => 'Simpan',
              'no_batch' => '0',
              'no_faktur' => '0'
            ]);
            if($query2) {
              $this->db('gudangbarang')
                ->where('kode_brng', $_POST['kode_brng'])
                ->where('kd_bangsal', $this->settings->get('farmasi.gudang'))
                ->save([
                  'stok' => $get_gudangbarang['stok'] + $_POST['stok']
              ]);
            }
        } else {

          $query = $this->db('riwayat_barang_medis')
            ->save([
              'kode_brng' => $_POST['kode_brng'],
              'stok_awal' => $get_gudangbarang['stok'],
              'masuk' => '0',
              'keluar' => $_POST['stok'],
              'stok_akhir' => $get_gudangbarang['stok'] - $_POST['stok'],
              'posisi' => 'Mutasi',
              'tanggal' => date('Y-m-d'),
              'jam' => date('H:i:s'),
              'petugas' => $this->core->getUserInfo('fullname', null, true),
              'kd_bangsal' => $this->settings->get('farmasi.gudang'),
              'status' => 'Simpan',
              'no_batch' => '0',
              'no_faktur' => '0'
            ]);

          $query2 = $this->db('riwayat_barang_medis')
            ->save([
              'kode_brng' => $_POST['kode_brng'],
              'stok_awal' => $gudangbarang['stok'],
              'masuk' => $_POST['stok'],
              'keluar' => '0',
              'stok_akhir' => $gudangbarang['stok'] + $_POST['stok'],
              'posisi' => 'Mutasi',
              'tanggal' => date('Y-m-d'),
              'jam' => date('H:i:s'),
              'petugas' => $this->core->getUserInfo('fullname', null, true),
              'kd_bangsal' => $_POST['kd_bangsal'],
              'status' => 'Simpan',
              'no_batch' => '0',
              'no_faktur' => '0'
            ]);
        }

        if($query) {
          $this->db('gudangbarang')
            ->where('kode_brng', $_POST['kode_brng'])
            ->where('kd_bangsal', $this->settings->get('farmasi.gudang'))
            ->save([
              'stok' => $get_gudangbarang['stok'] - $_POST['stok']
          ]);
        }
        if($query2) {
          $this->db('gudangbarang')
            ->where('kode_brng', $_POST['kode_brng'])
            ->where('kd_bangsal', $_POST['kd_bangsal'])
            ->save([
              'stok' => $gudangbarang['stok'] + $_POST['stok']
          ]);
        }
      } else {

        $get_gudangbarang = $this->db('gudangbarang')->where('kode_brng', $_POST['kode_brng'])->where('kd_bangsal', $this->settings->get('farmasi.gudang'))->oneArray();
        $stok = '0';
        if($get_gudangbarang) {
          $stok = $get_gudangbarang['stok'];
        }
        if($_POST['kd_bangsal'] == $this->settings->get('farmasi.gudang')) {
          $query = $this->db('riwayat_barang_medis')
            ->save([
              'kode_brng' => $_POST['kode_brng'],
              'stok_awal' => '0',
              'masuk' => $_POST['stok'],
              'keluar' => '0',
              'stok_akhir' => $_POST['stok'],
              'posisi' => 'Pengadaan',
              'tanggal' => date('Y-m-d'),
              'jam' => date('H:i:s'),
              'petugas' => $this->core->getUserInfo('fullname', null, true),
              'kd_bangsal' => $this->settings->get('farmasi.gudang'),
              'status' => 'Simpan',
              'no_batch' => '0',
              'no_faktur' => '0'
            ]);
            if($query) {
              $this->db('gudangbarang')->save([
                'kode_brng' => $_POST['kode_brng'],
                'kd_bangsal' => $this->settings->get('farmasi.gudang'),
                'stok' => $_POST['stok'],
                'no_batch' => '0',
                'no_faktur' => '0'
              ]);
            }

        } else {

          $query = $this->db('riwayat_barang_medis')
            ->save([
              'kode_brng' => $_POST['kode_brng'],
              'stok_awal' => $stok,
              'masuk' => '0',
              'keluar' => $_POST['stok'],
              'stok_akhir' => $stok - $_POST['stok'],
              'posisi' => 'Mutasi',
              'tanggal' => date('Y-m-d'),
              'jam' => date('H:i:s'),
              'petugas' => $this->core->getUserInfo('fullname', null, true),
              'kd_bangsal' => $this->settings->get('farmasi.gudang'),
              'status' => 'Simpan',
              'no_batch' => '0',
              'no_faktur' => '0'
            ]);

          $query2 = $this->db('riwayat_barang_medis')
            ->save([
              'kode_brng' => $_POST['kode_brng'],
              'stok_awal' => '0',
              'masuk' => $_POST['stok'],
              'keluar' => '0',
              'stok_akhir' => $_POST['stok'],
              'posisi' => 'Mutasi',
              'tanggal' => date('Y-m-d'),
              'jam' => date('H:i:s'),
              'petugas' => $this->core->getUserInfo('fullname', null, true),
              'kd_bangsal' => $_POST['kd_bangsal'],
              'status' => 'Simpan',
              'no_batch' => '0',
              'no_faktur' => '0'
            ]);
          if($query) {
            $this->db('gudangbarang')
              ->where('kode_brng', $_POST['kode_brng'])
              ->where('kd_bangsal', $this->settings->get('farmasi.gudang'))
              ->save([
                'stok' => $get_gudangbarang['stok'] - $_POST['stok']
            ]);
          }
          if($query2) {
            $this->db('gudangbarang')->save([
              'kode_brng' => $_POST['kode_brng'],
              'kd_bangsal' => $_POST['kd_bangsal'],
              'stok' => $_POST['stok'],
              'no_batch' => '0',
              'no_faktur' => '0'
            ]);
          }
        }
      }

      exit();
    }

    /* End Databarang Section */

    public function getOpname()
    {
      $this->_addHeaderFiles();
      return $this->draw('opname.html');
    }

    public function postOpnameAll()
    {
      $gudangbarang = $this->db('gudangbarang')
        ->join('databarang', 'databarang.kode_brng=gudangbarang.kode_brng')
        ->join('bangsal', 'bangsal.kd_bangsal=gudangbarang.kd_bangsal')
        ->where('databarang.status', '1')
        ->toJson();
      echo $gudangbarang;
      exit();
    }

    public function postOpnameUpdate()
    {
      $kode_brng =$_POST['kode_brng'];
      $real = $_POST['real'];
      $stok = $_POST['stok'];
      $kd_bangsal = $_POST['kd_bangsal'];
      $tanggal = $_POST['tanggal'];
      $h_beli = $_POST['h_beli'];
      $keterangan = $_POST['keterangan'];
      $no_batch = $_POST['no_batch'];
      $no_faktur = $_POST['no_faktur'];
      for($count = 0; $count < count($kode_brng); $count++){
       $query = "UPDATE gudangbarang SET stok=? WHERE kode_brng=? AND kd_bangsal=?";
       $opname = $this->db()->pdo()->prepare($query);
       $opname->execute([$real[$count], $kode_brng[$count], $kd_bangsal[$count]]);

       $selisih = $real[$count] - $stok[$count];
       $nomihilang = $selisih * $h_beli[$count];
       $lebih = 0;
       $nomilebih = 0;
       if($selisih < 0) {
         $selisih = 0;
         $nomihilang = 0;
         $lebih = $stok[$count] - $real[$count];
         $nomilebih = $lebih * $h_beli[$count];
       }

       $query2 = "INSERT INTO `opname` (`kode_brng`, `h_beli`, `tanggal`, `stok`, `real`, `selisih`, `nomihilang`, `lebih`, `nomilebih`, `keterangan`, `kd_bangsal`, `no_batch`, `no_faktur`) VALUES ('$kode_brng[$count]', '$h_beli[$count]', '$tanggal[$count]', '$real[$count]', '$stok[$count]', '$selisih', '$nomihilang', '$lebih', '$nomilebih', '$keterangan[$count]', '$kd_bangsal[$count]', '$no_batch[$count]', '$no_faktur[$count]')";
       $opname2 = $this->db()->pdo()->prepare($query2);
       $opname2->execute();

      }
      exit();
    }

 public function getLapObat()
 {
   $this->_addHeaderFiles();
   $date = date('Y-m-d');
    $rows = $this->db('detail_pemberian_obat')
        ->select([
          'jml' => 'sum(detail_pemberian_obat.jml)',
          'tipe'=> 'kategori_barang.nama',
          'depo' => 'bangsal.nm_bangsal'
        ])
        ->join('databarang', 'databarang.kode_brng = detail_pemberian_obat.kode_brng')
        ->join('kategori_barang', 'kategori_barang.kode = databarang.kode_kategori')
        ->join('reg_periksa', 'reg_periksa.no_rawat = detail_pemberian_obat.no_rawat')
        ->join('bangsal', 'bangsal.kd_bangsal = detail_pemberian_obat.kd_bangsal')
        ->where('detail_pemberian_obat.tgl_perawatan', $date)
        ->group('kategori_barang.kode')
        ->asc('kategori_barang.nama')
        ->toArray();

        $this->assign['list'] = [];
        if (count($rows)) {
            foreach ($rows as $row) {
                $row = htmlspecialchars_array($row);

                $this->assign['list'][] = $row;
            }
        }

   return $this->draw('lap_pemberian_obat.html',[
        'lap_obat' => $this->assign]);
 }

  public function postLapObat()
    {
      $this->_addHeaderFiles();

      if (isset($_POST['submit'])) {

        $date1 = $_POST['tanggal'];
        $date2 = $_POST['tanggal_akhir'];
        if ($_POST['bangsal'] == 'B0001') {
                            $status = "Ranap";
                        } else {
                            $status = "Ralan";
                        };
        $penjab = $_POST['penjab'];

        if (!empty($date1) && !empty($date2)) {
          $sql = "SELECT 
          SUM(detail_pemberian_obat.jml) AS jml, 
          MAX(detail_pemberian_obat.tgl_perawatan) AS max_date,
          MIN(detail_pemberian_obat.tgl_perawatan) AS min_date,
            kategori_barang.nama AS tipe, 
            bangsal.kd_bangsal, bangsal.nm_bangsal AS depo, kategori_barang.kode
          FROM   
              detail_pemberian_obat 
          JOIN databarang 
          JOIN kategori_barang 
          JOIN reg_periksa 
          JOIN bangsal ON detail_pemberian_obat.kode_brng = databarang.kode_brng 
          AND databarang.kode_kategori = kategori_barang.kode 
          AND detail_pemberian_obat.no_rawat = reg_periksa.no_rawat 
          AND detail_pemberian_obat.kd_bangsal = bangsal.kd_bangsal 
          WHERE detail_pemberian_obat.tgl_perawatan BETWEEN '$date1' AND '$date2' 
          AND detail_pemberian_obat.status = '$status' 
          AND detail_pemberian_obat.kd_bangsal = '{$_POST['bangsal']}' 
          AND reg_periksa.kd_pj IN ($penjab) GROUP BY kategori_barang.kode ORDER BY kategori_barang.nama ASC";

          $stmt = $this->db()->pdo()->prepare($sql);
          $stmt->execute();
          $rows = $stmt->fetchAll();

          $this->assign['list'] = [];
          foreach ($rows as $row) {
             $row['penjab'] = $penjab;

            $this->assign['list'][] = $row;
          }
        } else {
          $this->getLapObat();
        }
      }
      return $this->draw('lap_pemberian_obat.html', ['lap_obat' => $this->assign]);
    }

    public function getLihatObat()
    {
      $this->_addHeaderFiles(); 

      $kode = $_GET['kode'];
      $bangsal = $_GET['bangsal'];
      $date1 = $_GET['date1'];
      $date2 = $_GET['date2'];
      if ($_GET['bangsal'] == 'B0001') {
          $status = "Ranap";
      } else {
          $status = "Ralan";
      };
      $penjab = $_GET['penjab'];
       $sql = "SELECT SUM(detail_pemberian_obat.jml) AS jumlah,
                  kategori_barang.nama AS tipe,
                  databarang.nama_brng AS nama_obat,
                  detail_pemberian_obat.kode_brng AS kode_obat,
                  resep_obat.kd_dokter AS kd_dokter
              FROM detail_pemberian_obat
              JOIN databarang ON detail_pemberian_obat.kode_brng = databarang.kode_brng
              JOIN kategori_barang ON databarang.kode_kategori = kategori_barang.kode
              JOIN reg_periksa ON detail_pemberian_obat.no_rawat = reg_periksa.no_rawat
              JOIN resep_obat ON detail_pemberian_obat.no_rawat = resep_obat.no_rawat
              JOIN bangsal ON detail_pemberian_obat.kd_bangsal = bangsal.kd_bangsal
              WHERE
                  detail_pemberian_obat.tgl_perawatan BETWEEN '$date1' AND '$date2'
                  AND detail_pemberian_obat.status = '$status'
                  AND detail_pemberian_obat.kd_bangsal = '$bangsal'
                  AND kategori_barang.kode= '$kode'
                  AND reg_periksa.kd_pj IN ($penjab)
              GROUP BY 
                kategori_barang.kode, databarang.kode_brng
              ORDER BY
                kategori_barang.nama ASC, databarang.nama_brng ASC";

            $stmt = $this->db()->pdo()->prepare($sql);
            $stmt->execute();
            $rows = $stmt->fetchAll();

            $this->assign['list'] = [];
            foreach ($rows as $row) {
              $dokter = $this->db('dokter')->where('kd_dokter', $row['kd_dokter'])->oneArray();
              $row['nm_dokter'] = $dokter['nm_dokter'];
      
          $this->assign['list'][] = $row;
          }
      return $this->draw('display_listobat.html',['obat' => $this->assign]);
    }

    /* Settings Farmasi Section */
    public function getSettings()
    {
        $this->assign['title'] = 'Pengaturan Modul Farmasi';
        $this->assign['bangsal'] = $this->db('bangsal')->toArray();
        $this->assign['farmasi'] = htmlspecialchars_array($this->settings('farmasi'));
        return $this->draw('settings.html', ['settings' => $this->assign]);
    }

    public function postSaveSettings()
    {
        foreach ($_POST['farmasi'] as $key => $val) {
            $this->settings('farmasi', $key, $val);
        }
        $this->notify('success', 'Pengaturan telah disimpan');
        redirect(url([ADMIN, 'farmasi', 'settings']));
    }
    /* End Settings Farmasi Section */

    public function getCSS()
    {
        header('Content-type: text/css');
        echo $this->draw(MODULES.'/farmasi/css/admin/farmasi.css');
        exit();
    }

    public function getJavascript()
    {
        header('Content-type: text/javascript');
        echo $this->draw(MODULES.'/farmasi/js/admin/farmasi.js');
        exit();
    }

    private function _addHeaderFiles()
    {
        // CSS
        $this->core->addCSS(url('assets/css/dataTables.bootstrap.min.css'));

        // JS
        $this->core->addJS(url('assets/jscripts/jquery.dataTables.min.js'), 'footer');
        $this->core->addJS(url('assets/jscripts/dataTables.bootstrap.min.js'), 'footer');

        $this->core->addCSS(url('assets/css/bootstrap-datetimepicker.css'));
        $this->core->addJS(url('assets/jscripts/moment-with-locales.js'));
        $this->core->addJS(url('assets/jscripts/bootstrap-datetimepicker.js'));

        // MODULE SCRIPTS
        $this->core->addCSS(url([ADMIN, 'farmasi', 'css']));
        $this->core->addJS(url([ADMIN, 'farmasi', 'javascript']), 'footer');
    }

}