<?php

namespace Plugins\Data_NonMedis;

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
            ['name' => 'Data Non Medis', 'url' => url([ADMIN, 'data_nonmedis', 'itemnonmedis']), 'icon' => 'plus-square', 'desc' => 'Data Non Medis']
          ];
        return $this->draw('manage.html', ['sub_modules' => $sub_modules]);
    }

    

     public function getItemnonmedis()
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

    $date = date('Y-m-d');
    $username = $this->core->getUserInfo('username', null, true);
    //$sql = "SELECT * FROM `ipsrs_sirkulasi` WHERE tgl_upload IN (SELECT MAX(tgl_upload) FROM `ipsrs_sirkulasi`)";
    $sql ="SELECT * FROM ipsrs_sirkulasi WHERE nip = '$username' AND tgl_upload = (SELECT MAX(tgl_upload) FROM ipsrs_sirkulasi WHERE nip = '$username')";
          
    $stmt = $this->db()->pdo()->prepare($sql);
    $stmt->execute();
    $rows = $stmt->fetchAll();

        $this->assign['list'] = [];
        if (count($rows)) {
            foreach ($rows as $row) {
                $row = htmlspecialchars_array($row);
                $this->assign['list'][] = $row;
            }
        }

      return $this->draw('item_datanonmedis.html',['itemnonmedis' => $this->assign]);
    }

    public function anyForm()
    {
    
      $this->_addHeaderFiles();

      echo $this->draw('form_uploadexcel.html');
      exit();
    }

  public function postUploadFileXl(){
  if(isset($_FILES['xls_file']['tmp_name'])){
      $file_type = $_FILES['xls_file']['name'];
      $FileType = strtolower(pathinfo($file_type,PATHINFO_EXTENSION));
      $target = basename($_FILES['xls_file']['name']) ;
      if ($FileType != "xls" && $FileType != "xlsx") {
        echo "<script>alert('Salah File Bro!! ini bukan ".$FileType."');history.go(-1);</script>";
      } else {
        include(BASE_DIR. "/vendor/php-excel-reader-master/src/PHPExcelReader/SpreadsheetReader.php"); 
        move_uploaded_file($_FILES['xls_file']['tmp_name'], $target);

        // beri permisi agar file xls dapat di baca
            chmod($_FILES['xls_file']['name'],0777);

        // mengambil isi file xls   
        $data = new \PHPExcelReader\SpreadsheetReader($file_type, false);
        // menghitung jumlah baris data yang ada
        $jumlah_baris = $data->rowcount($sheet_index=0);
        $berhasil = 0;
        $sukses = false;

        $kosong = 0;

        // jumlah default data yang berhasil di import
        for ($i=5; $i<=$jumlah_baris; $i++){
          $tahun = $data->val($i, 1);
          $bidang = $data->val($i, 2);
          $kode_rek = $data->val($i, 3);
          $nama     = $data->val($i, 4);
          $satuan   = $data->val($i, 5);
          $harga    = $data->val($i, 6);
          $spek     = $data->val($i, 7);
          $stok_awal  = $data->val($i, 8);
          $stok_masuk = $data->val($i, 9);
          $stok_keluar = $data->val($i, 10);
          $stok_akhir  = $data->val($i, 11);
          $saldo_awal  = $data->val($i, 12);
          $saldo_masuk = $data->val($i, 13);
          $saldo_keluar = $data->val($i, 14);
          $saldo_akhir  = $data->val($i, 15);

          // menangkap data dan memasukkan ke variabel sesuai dengan kolumnya masing-masing

          if($tahun != "" && $bidang != "" && $kode_rek != "" && $nama != "" && $satuan != "" && $harga != ""  && $spek != "" 
                 && $stok_awal != "" && $stok_masuk != "" && $stok_keluar != "" && $stok_akhir != "" 
                 && $saldo_awal != "" && $saldo_masuk != "" && $saldo_keluar != "" && $saldo_akhir != "")
            {
                $kosong++; // Tambah 1 variabel $kosong
            }
            else
          {

                 $this->db('ipsrs_sirkulasi')->save([
                  'tahun'      => $tahun,
                  'bidang'     => $bidang,
                  'kode_rek'   => $kode_rek,
                  'nama'       => $nama,
                  'satuan'     => $satuan, 
                  'harga'      => $harga, 
                  'spek'       => $spek,
                  'stok_awal'  => $stok_awal,
                  'stok_masuk'  => $stok_masuk, 
                  'stok_keluar' => $stok_keluar,
                  'stok_akhir'  => $stok_akhir,
                  'saldo_awal'  => $saldo_awal,
                  'saldo_masuk' => $saldo_masuk, 
                  'saldo_keluar'=> $saldo_keluar, 
                  'saldo_akhir' => $saldo_akhir,
                  'tgl_upload' => date('Y-m-d H:i'),
                  'nip' => $this->core->getUserInfo('username', null, true)
                ]);
                $berhasil++;
                }
            $sukses = true;
              // input data ke database 
        }
          if ($sukses == true) {
            $this->notify('success', 'Upload telah berhasil disimpan');
          }
      }
        // hapus kembali file .xls yang di upload tadi
        unlink($_FILES['xls_file']['name']);
    }
    redirect(url([ADMIN, 'data_nonmedis', 'itemnonmedis']));
  }

    public function getCSS()
    {
      header('Content-type: text/css');
      echo $this->draw(MODULES . '/data_nonmedis/css/admin/data_nonmedis.css');
      exit();
    }

    public function getJavascript()
    {
        header('Content-type: text/javascript');
        echo $this->draw(MODULES . '/data_nonmedis/js/admin/data_nonmedis.js');
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
        $this->core->addCSS(url([ADMIN, 'data_nonmedis', 'css']));
        $this->core->addJS(url([ADMIN, 'data_nonmedis', 'javascript']), 'footer');
    }
}