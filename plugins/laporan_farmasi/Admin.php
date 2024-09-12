<?php

namespace Plugins\Laporan_Farmasi;

use Systems\AdminModule;

class Admin extends AdminModule
{

    public function navigation()
    {
        return [
            'Kelola' => 'manage',
            'Data Resep Elektronik' => 'dataeresep',
            'Laporan Obat Harian' => 'obatharian',
            'Laporan Ralan Ranap' => 'lapralanranap',
            'Permintaan Resep Dokter' => 'permintaanresep',
            'Rekam Pemberian Obat' => 'rekamobat',
            'Monitoring Obat' => 'monitoringobat',
            'Stok Opname Gudang' => 'stokopname_gudang',
            'Obat Expired' => 'obatexp',
            'Laporan Pemberian Obat' => 'lapobat',
            'Laporan Stok Opname' => 'lap_stokopname',
            'Layananan Kefarmasian' => 'lappelfar',
            'Mutasi Obat' => 'mutasiobat',
            'Input Stok Opname' => 'input_stokopname',
            'Cari Data Obat' => 'data_obat'
        ];
    }

    public function getManage()
    {
      $sub_modules = [
        ['name' => 'Data Resep Elektronik', 'url' => url([ADMIN, 'laporan_farmasi', 'dataeresep']), 'icon' => 'file', 'desc' => 'Data Resep Elektronik'],
        ['name' => 'Laporan Obat Harian ', 'url' => url([ADMIN, 'laporan_farmasi', 'obatharian']), 'icon' => 'file', 'desc' => 'Laporan Obat Harian'],
        ['name' => 'Laporan Ralan Ranap ', 'url' => url([ADMIN, 'laporan_farmasi', 'lapralanranap']), 'icon' => 'file', 'desc' => 'Laporan Ralan Ranap'],
        ['name' => 'Permintaan Resep Dokter', 'url' => url([ADMIN, 'laporan_farmasi', 'permintaanresep']), 'icon' => 'file', 'desc' => 'Permintaan Resep Dokter'],
        ['name' => 'Rekam Pemberian Obat', 'url' => url([ADMIN, 'laporan_farmasi', 'rekamobat']), 'icon' => 'file', 'desc' => 'Rekam Pemberian Obat'],
        ['name' => 'Monitoring Obat', 'url' => url([ADMIN, 'laporan_farmasi', 'monitoringobat']), 'icon' => 'file', 'desc' => 'Monitoring Obat'],
        ['name' => 'Stok Opname Gudang', 'url' => url([ADMIN, 'laporan_farmasi', 'stokopname_gudang']), 'icon' => 'file', 'desc' => 'Stok Opname Gudang'],
        ['name' => 'Obat Expired', 'url' => url([ADMIN, 'laporan_farmasi', 'obatexp']), 'icon' => 'file', 'desc' => 'Obat Expired'],
        ['name' => 'Laporan Pemberian Obat', 'url' => url([ADMIN, 'laporan_farmasi', 'lapobat']), 'icon' => 'file', 'desc' => 'Laporan Pemberian Obat'],
        ['name' => 'Laporan Stok Opname', 'url' => url([ADMIN, 'laporan_farmasi', 'lap_stokopname']), 'icon' => 'file', 'desc' => 'Laporan Stok Opname'],
        ['name' => 'Layananan Kefarmasian', 'url' => url([ADMIN, 'laporan_farmasi', 'lappelfar']), 'icon' => 'file', 'desc' => 'Laporan Layananan Kefarmasian'],
        ['name' => 'Mutasi Obat', 'url' => url([ADMIN, 'laporan_farmasi', 'mutasiobat']), 'icon' => 'file', 'desc' => 'Mutasi Obat'],
        ['name' => 'Input Stok Opname', 'url' => url([ADMIN, 'laporan_farmasi', 'input_stokopname']), 'icon' => 'file', 'desc' => 'Input Stok Opname'],
        ['name' => 'Cari Data Obat', 'url' => url([ADMIN, 'laporan_farmasi', 'data_obat']), 'icon' => 'file', 'desc' => 'Cari Data Obat'],
      ];
      return $this->draw('manage.html', ['sub_modules' => $sub_modules]);
    }

    public function getDataEresep()
    {
        $this->_addHeaderFiles();
        $date = date('Y-m-d');
        // $rows = $this->db('reg_periksa')
        //   ->join('resep_obat', 'resep_obat.no_rawat = reg_periksa.no_rawat')
        //   ->join('pasien', 'pasien.no_rkm_medis = reg_periksa.no_rkm_medis')
        //   ->join('penjab', 'penjab.kd_pj = reg_periksa.kd_pj')
        //   ->join('resep_dokter', 'resep_obat.no_resep = resep_dokter.no_resep')
        //   ->join('databarang', 'resep_dokter.kode_brng = databarang.kode_brng')
        //   ->select([
        //     'no_rkm_medis' => 'reg_periksa.no_rkm_medis',
        //     'stts' => 'reg_periksa.stts',
        //     'status_bayar' => 'reg_periksa.status_bayar',
        //     'nm_pasien' => 'pasien.nm_pasien',
        //     'alamat' => 'pasien.alamat',
        //     'no_resep' => 'resep_obat.no_resep',
        //     'png_jawab' => 'penjab.png_jawab',
        //     'resep_dokter' => 'group_concat(distinct concat(databarang.nama_brng, \' [ \', resep_dokter.jml, \' ] \', resep_dokter.aturan_pakai) SEPARATOR \'<br>\')'
        //   ])
        //   ->where('reg_periksa.kd_poli', '<>', 'IGD01')
        //   ->where('reg_periksa.kd_poli', '<>', 'PL049')
        //   ->where('reg_periksa.status_lanjut', 'Ralan')
        //   ->where('reg_periksa.tgl_registrasi', $date)
        //   ->group('resep_dokter.no_resep')
        //   ->toArray();
        $sql = "SELECT 
          reg_periksa.no_rkm_medis AS no_rkm_medis,
          reg_periksa.stts AS stts,
          reg_periksa.status_bayar AS status_bayar,
          pasien.nm_pasien AS nm_pasien,
          pasien.alamat AS alamat,
          resep_obat.no_resep AS no_resep,
          penjab.png_jawab AS png_jawab,
          GROUP_CONCAT(
              DISTINCT CONCAT(databarang.nama_brng, ' [ ', resep_dokter.jml, ' ] ', resep_dokter.aturan_pakai) 
              SEPARATOR '<br>'
          ) AS resep_dokter
          FROM 
              reg_periksa
          JOIN resep_obat ON resep_obat.no_rawat = reg_periksa.no_rawat
          JOIN pasien ON pasien.no_rkm_medis = reg_periksa.no_rkm_medis
          JOIN penjab ON penjab.kd_pj = reg_periksa.kd_pj
          JOIN resep_dokter ON resep_obat.no_resep = resep_dokter.no_resep
          JOIN databarang ON resep_dokter.kode_brng = databarang.kode_brng
          WHERE (reg_periksa.kd_poli != 'IGDK' OR reg_periksa.kd_poli  !='IGD01')
              AND (reg_periksa.kd_poli != 'U0027' OR reg_periksa.kd_poli != 'PL049')
              AND reg_periksa.status_lanjut = 'Ralan'
              AND reg_periksa.tgl_registrasi = '$date'
          GROUP BY resep_dokter.no_resep";

        $stmt = $this->db()->pdo()->prepare($sql);
        $stmt->execute();
        $rows = $stmt->fetchAll();

        $this->assign['list'] = [];
        if (count($rows)) {
            foreach ($rows as $row) {

                $this->assign['list'][] = $row;
          }
        } 

      return $this->draw('data_eresep.html', ['eresep' => $this->assign]);
    }

    public function postDataEresep()
    {
      $this->_addHeaderFiles();

      if (isset($_POST['submit'])) {

          $date1 = $_POST['date1'];
          $date2 = $_POST['date2'];

          if (!empty($date1) && !empty($date2)) {
                $sql = "SELECT c.nm_pasien, a.no_rkm_medis, c.alamat, d.png_jawab, a.stts, a.status_bayar, b.no_resep, 
                        GROUP_CONCAT(f.nama_brng, ' [', e.jml, '] - ', e.aturan_pakai SEPARATOR '<br>') AS resep_dokter 
                        FROM reg_periksa a, resep_obat b,  pasien c, penjab d, resep_dokter e, databarang f
                        WHERE  a.no_rawat = b.no_rawat AND a.no_rkm_medis = c.no_rkm_medis AND a.kd_pj = d.kd_pj AND b.no_resep = e.no_resep AND e.kode_brng = f.kode_brng 
                        AND (a.kd_poli != 'IGDK' OR a.kd_poli  !='IGD01')  AND (a.kd_poli != 'U0027' OR a.kd_poli  !='PL049')  AND a.status_lanjut = 'Ralan'
                        AND a.tgl_registrasi BETWEEN '$date1' AND '$date2' GROUP BY e.no_resep";
              
          $stmt = $this->db()->pdo()->prepare($sql);
          $stmt->execute();
          $rows = $stmt->fetchAll();

            $this->assign['list'] = [];
            foreach ($rows as $row) {

              $this->assign['list'][] = $row;
            }
          } else {
            $this->getDataEresep();
          }
      }
      return $this->draw('data_eresep.html', ['eresep' => $this->assign]);
    }

    public function getObatHarian()
    {
      $this->_addHeaderFiles();
      return $this->draw('laporan_obat_harian.html');
    }
    
    public function postObatHarian()
    {
      $this->_addHeaderFiles();

      if (isset($_POST['submit'])) {

        $date = $_POST['date1'];
        if (!empty($date)){
          $sql = "SELECT d.nama_brng, a.kode_brng, c.nm_pasien, b.no_rkm_medis, a.no_rawat, a.jml, e.nm_dokter, a.biaya_obat, f.png_jawab 
          FROM detail_pemberian_obat a, reg_periksa b, pasien c, databarang d, dokter e, penjab f 
          WHERE a.no_rawat = b.no_rawat AND b.no_rkm_medis = c.no_rkm_medis AND a.kode_brng = d.kode_brng AND b.kd_dokter = e.kd_dokter AND b.kd_pj = f.kd_pj 
          AND a.tgl_perawatan = '$date' 
          ORDER BY a.no_rawat ASC";
            
        $stmt = $this->db()->pdo()->prepare($sql);
        $stmt->execute();
        $rows = $stmt->fetchAll();
 
          $this->assign['list'] = [];
          foreach ($rows as $row) {
            $total = $row['jml'] * $row['biaya_obat'];
            $row['total'] = $total;

            $this->assign['list'][] = $row;
            }
          } else {
            $this->getObatHarian();
          }
      }

      return $this->draw('laporan_obat_harian.html', ['obat_harian' => $this->assign]);
    }

    
    public function getLapRalanRanap()
    {
      $this->_addHeaderFiles();
      return $this->draw('laporan_ralan_ranap.html');
    }

    public function postLapRalanRanap()
    {
      $this->_addHeaderFiles();

      if (isset($_POST['submit'])) {

      $date1 = $_POST['date1'];
      $date2 = $_POST['date2'];

      if (!empty($date1) && !empty($date2)) {
        $sql = "SELECT nama.nama_brng, nama.kode_brng, (
          SELECT COUNT(jml) FROM detail_pemberian_obat WHERE kode_brng = nama.kode_brng AND status='Ralan' AND tgl_perawatan BETWEEN '$date1' AND '$date2') AS ralan, 
          (SELECT COUNT(jml) FROM detail_pemberian_obat WHERE  kode_brng = nama.kode_brng AND status='Ranap' AND tgl_perawatan BETWEEN '$date1' AND '$date2') AS ranap, 
          (SELECT COUNT(jml) FROM detail_pemberian_obat WHERE kode_brng = nama.kode_brng AND tgl_perawatan BETWEEN '$date1' AND '$date2') AS total 
          FROM (SELECT DISTINCT nama_brng, kode_brng FROM databarang WHERE kode_brng IN(SELECT kode_brng FROM detail_pemberian_obat)) AS nama ORDER BY nama.nama_brng ASC";
          
        $stmt = $this->db()->pdo()->prepare($sql);
        $stmt->execute();
        $rows = $stmt->fetchAll();
          $this->assign['list'] = [];
          foreach ($rows as $row) {

            $this->assign['list'][] = $row;
          }
        } else {
          $this->getLapRalanRanap();
        }
      }

      return $this->draw('laporan_ralan_ranap.html', ['ralan_ranap' => $this->assign]);

    }

    public function getPermintaanResep()
    {
      $this->_addHeaderFiles();
      return $this->draw('permintaan_resep_dokter.html');
    }

    public function postPermintaanResep()
   {
      $this->_addHeaderFiles();

      if (isset($_POST['submit'])) {

        $date1 = $_POST['tanggal'];
        $date2 = $_POST['tanggal_akhir'];
        $status = $_POST['status'];

          if (isset($_POST['tanggal'])){ $date1 = $_POST['tanggal']; }
          if (isset($_POST['tanggal_akhir'])){ $date2 = $_POST['tanggal_akhir']; }

            if($status == 'Rajal') {
              $sql = "SELECT a.no_resep,a.tgl_perawatan,a.no_rawat,b.no_rkm_medis,c.nm_pasien,d.nm_dokter,a.tgl_peresepan, e.nm_poli as ruang,a.status as stts,
                  if (a.jam_peresepan=a.jam,'Belum Terlayani','Sudah Terlayani') as status FROM resep_obat as a,reg_periksa as b,
                  pasien as c,dokter as d,poliklinik as e where a.tgl_peresepan >='$date1' and a.tgl_peresepan <= '$date2'
                  AND a.no_rawat=b.no_rawat AND b.no_rkm_medis=c.no_rkm_medis and b.kd_poli=e.kd_poli and b.kd_poli NOT IN ('IGDK','U0027','U0019','IGD01','PL049','PL051')
                  AND a.kd_dokter = d.kd_dokter";
            }
            if($status == 'Ranap') {
              $sql = "SELECT a.no_resep,a.tgl_peresepan, a.no_rawat,b.no_rkm_medis, c.nm_pasien, if (a.jam_peresepan=a.jam,'Belum Terlayani','Sudah Terlayani') as status,
                  a.status, f.nm_bangsal as ruang, g.nm_dokter FROM resep_obat as a, reg_periksa as b, pasien as c,kamar_inap as d,
                  kamar as e,bangsal as f, dokter as g where a.tgl_peresepan >='$date1'  and a.tgl_peresepan <= '$date2'
                  AND a.status = 'ranap' AND a.no_rawat = b.no_rawat AND b.no_rawat = d.no_rawat AND a.kd_dokter = g.kd_dokter AND
                  d.kd_kamar = e.kd_kamar AND e.kd_bangsal = f.kd_bangsal AND b.no_rkm_medis = c.no_rkm_medis";
            }
            if($status == 'IGDK') {
              $sql = "SELECT a.no_resep,a.tgl_perawatan,a.no_rawat,b.no_rkm_medis,c.nm_pasien,d.nm_dokter,a.tgl_peresepan, e.nm_poli as ruang,a.status as stts,
                if (a.jam_peresepan=a.jam,'Belum Terlayani','Sudah Terlayani') as status FROM resep_obat as a,reg_periksa as b,
                pasien as c,dokter as d,poliklinik as e where a.tgl_peresepan >='$date1' and a.tgl_peresepan <= '$date2'
                AND a.no_rawat=b.no_rawat AND b.no_rkm_medis=c.no_rkm_medis and b.kd_poli=e.kd_poli
                AND e.kd_poli = 'IGDK' AND a.kd_dokter = d.kd_dokter GROUP BY a.no_rawat";
            }
            if($status == 'U0019') {
              $sql = "SELECT a.no_resep,a.tgl_perawatan,a.no_rawat,b.no_rkm_medis,c.nm_pasien,d.nm_dokter,a.tgl_peresepan, e.nm_poli as ruang,a.status as stts,
                if (a.jam_peresepan=a.jam,'Belum Terlayani','Sudah Terlayani') as status FROM resep_obat as a,reg_periksa as b,
                pasien as c,dokter as d,poliklinik as e where a.tgl_peresepan >='$date1' and a.tgl_peresepan <= '$date2'
                AND a.no_rawat=b.no_rawat AND b.no_rkm_medis=c.no_rkm_medis and b.kd_poli=e.kd_poli
                AND e.kd_poli = 'U0019' AND a.kd_dokter = d.kd_dokter";
            }
            
          $stmt = $this->db()->pdo()->prepare($sql);
          $stmt->execute();
          $rows = $stmt->fetchAll();
             
            $this->assign['list'] = [];
            foreach ($rows as $row) {
              $this->assign['list'][] = $row;
            }
          } else {
            $this->getPermintaanResep();
          }

      return $this->draw('permintaan_resep_dokter.html', ['permintaan_resep' => $this->assign]);
    }

    public function getRekamObat()
    {
      $this->_addHeaderFiles();
      return $this->draw('rekam_obat.html');
    }  

    public function getMonitoringObat()
    {
      $this->_addHeaderFiles();
      return $this->draw('monitoring_obat.html');
    }

    public function getStokOpname_Gudang()
    {
      $this->_addHeaderFiles();
      $rows = $this->db('databarang')
      ->select([
        'nama_brng' => 'databarang.nama_brng',
        'kode_brng'=> 'opname.kode_brng',
        'stok_akhir' => 'max(opname.real)',
        'tgl_update' => 'max(opname.tanggal)'
      ])
      ->join('opname', 'opname.kode_brng = databarang.kode_brng')
      ->where('opname.kd_bangsal', 'B0002')
      ->group('opname.kode_brng')
      ->toArray();

      $this->assign['list'] = [];
      if (count($rows)) {
          foreach ($rows as $row) {
              $row = htmlspecialchars_array($row);

              $this->assign['list'][] = $row;
          }
      }

      return $this->draw('stok_opname_gudang.html', ['stok_opname_gudang' => $this->assign]);
    }

    public function getObatExp()
    {
      $this->_addHeaderFiles();
      $rows = $this->db('databarang')
      ->select([
        'nama_brng' => 'databarang.nama_brng',
        'kode_brng'=> 'riwayat_barang_medis.kode_brng',
        'stok_akhir' => 'riwayat_barang_medis.stok_akhir',
        'tgl_update' => 'max(riwayat_barang_medis.tanggal)',
        'expire' => 'databarang.expire'
      ])
      ->join('riwayat_barang_medis', 'riwayat_barang_medis.kode_brng = databarang.kode_brng')
      ->where('databarang.status', '1')
      ->group('riwayat_barang_medis.kode_brng')
      ->toArray();

      $this->assign['list'] = [];
      if (count($rows)) {
          foreach ($rows as $row) {
              $row = htmlspecialchars_array($row);

              $this->assign['list'][] = $row;
          }
      }
      return $this->draw('obat_expired.html', ['obatexp' => $this->assign]);
    }

    public function postObatExp()
    {
      $this->_addHeaderFiles();

      if (isset($_POST['submit'])) {

          $date = $_POST['tanggal'];
          $bangsal = $_POST['bangsal'];

          $sql = "SELECT a.nama_brng, b.kode_brng, b.stok_akhir, MAX(b.tanggal) AS tgl_update, a.expire FROM databarang a, riwayat_barang_medis b WHERE a.kode_brng = b.kode_brng AND a.status = '1'";
            if (!empty($date)) {
             $sql .= " AND b.tanggal = '$date' AND b.jam = (SELECT MAX(jam) FROM riwayat_barang_medis GROUP BY kode_brng LIMIT 1)";
            }
            if (!empty($bangsal)) {
             $sql .= " AND b.kd_bangsal = '$bangsal'";
             }
             $sql .= " GROUP BY b.kode_brng";

            $stmt = $this->db()->pdo()->prepare($sql);
            $stmt->execute();
            $rows = $stmt->fetchAll();

            $this->assign['list'] = [];
            foreach ($rows as $row) {

              $this->assign['list'][] = $row;
            }
        } else {
          $this->getObatExp();
        } 
        
      return $this->draw('obat_expired.html', ['obatexp' => $this->assign]);
    }

    public function getLapObat()
    {
      $this->_addHeaderFiles();
       $date = date('Y-m-d');
       $rows = $this->db('detail_pemberian_obat')
           ->select([
             'jml' => 'sum(detail_pemberian_obat.jml)',
             'tipe'=> 'kategori_barang.nama',
             'depo' => 'bangsal.nm_bangsal',
             'kode' => 'kategori_barang.kode'
           ])
           ->join('databarang', 'databarang.kode_brng = detail_pemberian_obat.kode_brng')
           ->join('kategori_barang', 'kategori_barang.kode = databarang.kode_kategori')
           ->join('reg_periksa', 'reg_periksa.no_rawat = detail_pemberian_obat.no_rawat')
           ->join('bangsal', 'bangsal.kd_bangsal = detail_pemberian_obat.kd_bangsal')
           ->where('detail_pemberian_obat.tgl_perawatan', $date)
           ->where('detail_pemberian_obat.status', 'Ralan')
           ->where('detail_pemberian_obat.kd_bangsal', 'B0014')
           ->where('reg_periksa.kd_pj', 'A02')
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
 
      return $this->draw('lap_pemberian_obat.html',['lap_obat' => $this->assign]);
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
               bangsal.kd_bangsal, bangsal.nm_bangsal AS depo, kategori_barang.kode AS kode
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
              WHERE detail_pemberian_obat.tgl_perawatan BETWEEN '$date1' AND '$date2'
              AND detail_pemberian_obat.status = '$status'
              AND detail_pemberian_obat.kd_bangsal = '$bangsal'
              AND kategori_barang.kode= '$kode'
              AND reg_periksa.kd_pj IN ($penjab)
              GROUP BY kategori_barang.kode, databarang.kode_brng
              ORDER BY kategori_barang.nama ASC, databarang.nama_brng ASC";
 
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

    public function getLap_StokOpname()
    {
      $this->_addHeaderFiles();
      return $this->draw('laporan_stok_opname.html');
    }

    public function postLap_StokOpname()
    { 
      $this->_addHeaderFiles();

      if (isset($_POST['submit'])) {

          $date1 = $_POST['tanggal'];
          $date2 = $_POST['tanggal_akhir'];
          $yesterday = date('Y/m/d', strtotime($date1. '-1 days'));
          $two_days_before = date('Y/m/d', strtotime($date1. '-4 days'));
          $kode = $_POST['status'];

          if (!empty($date1) && !empty($date2)) {
            $sql = "SELECT databarang.nama_brng , kodesatuan.satuan , databarang.kode_brng , databarang.ralan
                    FROM databarang 
                    JOIN kodesatuan ON databarang.kode_sat = kodesatuan.kode_sat
                    WHERE databarang.kode_kategori IN ($kode) 
                    GROUP BY databarang.kode_brng";

            $stmt = $this->db()->pdo()->prepare($sql);
            $stmt->execute();
            $rows = $stmt->fetchAll();

            $this->assign['list'] = [];
            foreach ($rows as $row) {
              $satuan = $row['satuan'];
              $stok = $this->db('riwayat_barang_medis')
                ->select([
                  'stok_awal' => 'SUM(riwayat_barang_medis.stok_awal)',
                  'stok_masuk'=> 'SUM(riwayat_barang_medis.masuk)'
                ])
               ->where('riwayat_barang_medis.kode_brng', $satuan)
               ->where('riwayat_barang_medis.posisi', 'Opname')
               ->where('tanggal', '>=', $two_days_before.' 00:00:00')
              ->where('tanggal', '<=', $yesterday.' 23:59:59')
               ->group('riwayat_barang_medis.kode_brng')
               ->oneArray();

              $stok = $stok['stok_awal'] + $stok['stok_masuk'];
              $row['stok'] = $stok;

              $kode_brng = $row['kode_brng'];
              $query1 = "SELECT SUM(riwayat_barang_medis.masuk) FROM riwayat_barang_medis WHERE riwayat_barang_medis.kode_brng = '$kode_brng' AND riwayat_barang_medis.posisi IN ('Pengadaan','Penerimaan','Pengambilan Medis') AND riwayat_barang_medis.tanggal BETWEEN '$date1' AND '$date2' GROUP BY riwayat_barang_medis.kode_brng";
              $stmt1 = $this->db()->pdo()->prepare($query1);
              $stmt1->execute();
              $masuk = $stmt1->fetchColumn();
              $row['masuk'] = $masuk;

              $sedia = $stok +  $row['masuk'];
              $row['sedia'] = $sedia;

              $query_keluar = "SELECT SUM(riwayat_barang_medis.keluar) FROM riwayat_barang_medis WHERE riwayat_barang_medis.kode_brng = '$kode_brng' AND riwayat_barang_medis.posisi IN ('Penjualan','Stok Keluar','Pemberian Obat','Resep Pulang') AND riwayat_barang_medis.tanggal BETWEEN '$date1' AND '$date2' GROUP BY riwayat_barang_medis.kode_brng";
              $stmt1 = $this->db()->pdo()->prepare($query_keluar);
              $stmt1->execute();
              $keluar = $stmt1->fetchColumn();
              $row['keluar'] = $keluar;

              $total = $sedia - $row['keluar'];
              $row['total'] = $total;

              $harga_satuan = number_format($row['ralan'], 0, ',', '.');
              $row['harga_satuan'] = $harga_satuan;

              $harga_total = $total * $row['ralan'];
              $row['harga_total'] = number_format($harga_total, 0, ',', '.');

              $this->assign['list'][] = $row;
            }
          } else {
            $this->getLap_StokOpname();
          }
        }
      return $this->draw('laporan_stok_opname.html', ['lap_stok_opname' => $this->assign]);
    }

  
    public function getLapPelFar()
    {
      $this->_addHeaderFiles();
      return $this->draw('lappelfar.html');
    }

    public function postLapPelFar()
    {
      $this->_addHeaderFiles();

      if (isset($_POST['submit'])) {

          $date1 = $_POST['date1'];
          $date2 = $_POST['date2'];

          if (!empty($date1) && !empty($date2)) {
                $sql = "SELECT nama.nama_brng, nama.kode_brng, nama.ralan as harga_ralan,
                          (SELECT satuan FROM kodesatuan WHERE kode_sat = nama.kode_sat) as kodesat,
                          (SELECT SUM(stok_akhir) FROM riwayat_barang_medis WHERE kode_brng = nama.kode_brng AND kd_bangsal IN ('B0001','B0014','B0018') AND status = 'Simpan' AND tanggal BETWEEN '$date1' AND '$date2') AS opname,
                          (SELECT SUM(masuk) FROM riwayat_barang_medis WHERE kode_brng = nama.kode_brng AND kd_bangsal IN ('B0001','B0014','B0018') AND posisi IN ('Mutasi','Pengadaan','Penerimaan','Pengambilan Medis','Retur Jual') AND status = 'Simpan' AND tanggal BETWEEN '$date1' AND '$date2') AS masuk,
                          (SELECT SUM(jml) FROM detail_pemberian_obat WHERE kode_brng = nama.kode_brng AND status='Ralan' AND tgl_perawatan BETWEEN '$date1' AND '$date2') AS ralan,
                          (SELECT SUM(jml) FROM detail_pemberian_obat WHERE kode_brng = nama.kode_brng AND status='Ranap' AND tgl_perawatan BETWEEN '$date1' AND '$date2') AS ranap,
                          (SELECT SUM(jml) FROM detail_pemberian_obat WHERE kode_brng = nama.kode_brng AND tgl_perawatan BETWEEN '$date1' AND '$date2') AS total
                            FROM (SELECT DISTINCT nama_brng, kode_brng , kode_sat ,ralan FROM databarang WHERE kode_brng IN(SELECT kode_brng FROM detail_pemberian_obat)) AS nama ORDER BY nama.nama_brng ASC";
              
                $stmt = $this->db()->pdo()->prepare($sql);
                $stmt->execute();
                $rows = $stmt->fetchAll();
             
            $this->assign['list'] = [];
            foreach ($rows as $row) {
              $tot_harga = $row['total'] * $row['harga_ralan'];
              $row['tot_harga'] = $tot_harga;

              $this->assign['list'][] = $row;
            }
          } else {
            $this->getLapPelFar();
          }
        }

      return $this->draw('lappelfar.html', ['lappelfar' => $this->assign]);
    }

    public function getMutasiObat()
    {
        $this->_addHeaderFiles();
        $this->assign['obat'] = $this->db('databarang')->toArray();
        $date = date('Y-m-d');

        $sql = "SELECT a.nama_brng, b.kode_brng, b.jml , c.nm_bangsal 
          FROM databarang a, mutasibarang b , bangsal c 
          WHERE a.kode_brng = b.kode_brng AND b.kd_bangsalke = c.kd_bangsal 
          AND b.kd_bangsaldari = 'B0002' AND date(b.tanggal) = CURDATE() 
          ORDER BY b.tanggal ASC";
        $stmt = $this->db()->pdo()->prepare($sql);
        $stmt->execute();
        $rows = $stmt->fetchAll();
                   
        $this->assign['list'] = [];
        if (count($rows)) {
            foreach ($rows as $row) {

                $this->assign['list'][] = $row;
          }
        } 

      return $this->draw('mutasi_obat.html', ['mutasi' => $this->assign]);
    }

    public function postObat()
    {
      if (isset($_POST["query"])) {
          $output = '';
          $key = "%" . $_POST["query"] . "%";
          $rows = $this->db('databarang')->like('nama_brng', $key)->limit(10)->toArray();
          $output = '';
          if (count($rows)) {
            foreach ($rows as $row) {
              $output .= '<li data-id="' . $row['kode_brng'] . '" class="list-group-item link-class">' . $row["nama_brng"] . '</li>';
            }
          }
          echo $output;
        }  
      exit();
    }

    public function postBangsalList()
    {
      if (isset($_POST["query"])) {
        $output = '';
        $key = "%" . $_POST["query"] . "%";
        $rows = $this->db('bangsal')->like('nm_bangsal', $key)->where('status', '1')->limit(10)->toArray();
        $output = '';
        if (count($rows)) {
          foreach ($rows as $row) {
            $output .= '<li data-id="' . $row['kd_bangsal'] . '" class="list-group-item link-class">' . $row["nm_bangsal"] . '</li>';
            
          }
        }
        echo $output;
      }

      exit();
    }

    public function postSaveMutasi()
    {
      
      $kode_brng = $_POST['kode_brng'];
      $dari = 'B0002';
      $ke = $_POST['kd_bangsal'];
      $ket = $_POST['ket'];
      $jml = $_POST['jumlah'];
      $date = date("Y-m-d H:i:s");
      $dt = date("Y-m-d");
      $tm = date("H:i:s");
      $user = $this->core->getUserInfo('username', null, true);
      $hbeli = $this->db('databarang')->select('h_beli')->where('kode_brng', $kode_brng)->oneArray();
      $harga = $hbeli['h_beli'];
      
      $query = $this->db('mutasibarang')->save([
                'kode_brng' => $kode_brng,
                'jml' => $jml,
                'harga' => $harga,
                'kd_bangsaldari' => $dari,
                'kd_bangsalke' =>  $ke,
                'tanggal' => $date,
                'keterangan' => $ket
                ]);
      
      if ($query) {
          $stok_dari = $this->db('gudangbarang')->select('stok')->where('kode_brng', $kode_brng)->where('kd_bangsal', 'B0002')->oneArray();
          $stok_dr = $stok_dari['stok'];  
          $stok_ke = $this->db('gudangbarang')->select('stok')->where('kode_brng', $kode_brng)->where('kd_bangsal', $ke)->oneArray();
          $stokke = $stok_ke['stok'];
          if ($stok_dari) {
              $stok_akhir_dari = $stok_dr - $jml;
              $this->db('gudangbarang')->where('kode_brng', $kode_brng)->where('kd_bangsal', $dari)->update(['stok' => $stok_akhir_dari]);
              $this->db('riwayat_barang_medis')->save([
                        'kode_brng' => $kode_brng,
                        'stok_awal' => $stok_dr,
                        'masuk' => '0',
                        'keluar' => $jml,
                        'stok_akhir' =>  $stok_akhir_dari,
                        'posisi' => 'Mutasi',
                        'tanggal' =>  $dt,
                        'jam' => $tm,
                        'petugas' => $user,
                        'kd_bangsal' => 'B0002',
                        'status' =>  'Simpan'
                      ]);
            }else{
              $stok_akhir_dari = '0' - $jml; 
              $this->db('gudangbarang')->save([
                        'kode_brng' => $kode_brng,
                        'kd_bangsal' => 'B0002',
                        'stok' => $stok_akhir_dari
                      ]);
              $this->db('riwayat_barang_medis')->save([
                        'kode_brng' => $kode_brng,
                        'stok_awal' => '0',
                        'masuk' => '0',
                        'keluar' => $jml,
                        'stok_akhir' =>  $stok_akhir_dari,
                        'posisi' => 'Mutasi',
                        'tanggal' =>  $dt,
                        'jam' => $tm,
                        'petugas' => $user,
                        'kd_bangsal' => 'B0002',
                        'status' =>  'Simpan'
              ]);
          }
          if ($stok_ke) {
              $stok_akhir_ke = $stokke + $jml;
              $this->db('gudangbarang')->where('kode_brng', $kode_brng)->where('kd_bangsal', $ke)->update(['stok' => $stok_akhir_ke]);
              $this->db('riwayat_barang_medis')->save([
                        'kode_brng' => $kode_brng,
                        'stok_awal' => $stokke,
                        'masuk' => $jml,
                        'keluar' => '0',
                        'stok_akhir' =>  $stok_akhir_ke,
                        'posisi' => 'Mutasi',
                        'tanggal' =>  $dt,
                        'jam' => $tm,
                        'petugas' => $user,
                        'kd_bangsal' => $ke,
                        'status' =>  'Simpan'
                      ]);
          }else{
              $stok_akhir_ke = '0' + $jml;
              $this->db('gudangbarang')->save([
                        'kode_brng' => $kode_brng,
                        'kd_bangsal' => $ke,
                        'stok' => $stok_akhir_ke
                      ]);
              $this->db('riwayat_barang_medis')->save([
                        'kode_brng' => $kode_brng,
                        'stok_awal' => '0',
                        'masuk' => $jml,
                        'keluar' => '0',
                        'stok_akhir' =>  $stok_akhir_ke,
                        'posisi' => 'Mutasi',
                        'tanggal' =>  $dt,
                        'jam' => $tm,
                        'petugas' => $user,
                        'kd_bangsal' => $ke,
                        'status' =>  'Simpan'
                      ]);
          }
              
          $this->notify('success', 'Data mutasi berhasil disimpan.');
        } else {
            $this->notify('failure', 'Gagal menyimpan data mutasi.');
        }
      
      redirect(url([ADMIN, 'laporan_farmasi', 'mutasiobat']));
    }

    public function getInput_StokOpname()
    {
        $this->_addHeaderFiles();
        $rows = $this->db('databarang')
        ->select([
          'nama_brng' => 'databarang.nama_brng',
          'kode_brng'=> 'gudangbarang.kode_brng',
          'stok' => 'gudangbarang.stok',
          'h_beli' => 'databarang.h_beli',
          'nm_bangsal' => 'bangsal.nm_bangsal'
        ])
        ->join('gudangbarang', 'gudangbarang.kode_brng = databarang.kode_brng')
        ->join('bangsal', 'bangsal.kd_bangsal = gudangbarang.kd_bangsal')
        ->asc('databarang.nama_brng')
        ->toArray();
  
        $this->assign['list'] = [];
        if (count($rows)) {
            foreach ($rows as $row) {
                $row = htmlspecialchars_array($row);
  
                $this->assign['list'][] = $row;
            }
        }
  
        return $this->draw('input_stok_opname.html', ['stok_opname' => $this->assign]);
    }

    public function postSaveStokOpname()
    {
      
      if ($_POST['kode_brng'] != "") {
            $kode_brng = $_POST['kode_brng'];
            $ket = $_POST['ket'];
            $stokreal = $_POST['stok_real'];
            $dt = date("Y-m-d");
            $tm = date("H:i:s");
            $user = $this->core->getUserInfo('username', null, true);
            $h_beli = $this->db('databarang')->select('h_beli')->where('kode_brng', $kode_brng)->oneArray();
            $hbeli = $h_beli['h_beli'];
            
            $stok_dari = $this->db('gudangbarang')->select('stok')->where('kode_brng', $kode_brng)->where('kd_bangsal', 'B0002')->oneArray();
            $stok_dr = $stok_dari['stok'];  
           
            if ($stok_dr) {
                $kurang = $stok_dr - $stokreal;
                $nominal = $kurang * $hbeli;
                $hasillebih = $kurang * (-1);
                $nominallebih = $nominal * (-1);
                if ($kurang > 0) {
                    $selisih = $kurang;
                    $nomihilang = $nominal;
                    $lebih = 0;
                    $nomilebih = 0;
                }else{
                    $selisih = 0;
                    $nomihilang = 0;
                    $lebih = $hasillebih;
                    $nomilebih = $nominal;
                }
                
                $query = $this->db('opname')->save([
                  'kode_brng' => $kode_brng,
                  'h_beli' => $hbeli,
                  'tanggal' => $dt,
                  'stok' => $stok_dr,
                  '`real`'  =>  $stokreal,
                  'selisih' => $selisih,
                  'nomihilang' => $nomihilang,
                  'keterangan' =>  $ket,
                  'kd_bangsal' => 'B0002',
                  'lebih' => $lebih,
                  'nomilebih' => $nominallebih
                  ]);

                if ($query) {
                    $this->db('gudangbarang')->where('kode_brng', $kode_brng)->where('kd_bangsal', 'B0002')->update(['stok' => $stokreal]);
                    $this->db('riwayat_barang_medis')->save([
                      'kode_brng' => $kode_brng,
                      'stok_awal' => $stok_dr,
                      'masuk' => $stokreal,
                      'keluar' => '0',
                      'stok_akhir' =>  $stokreal,
                      'posisi' => 'Opname',
                      'tanggal' =>  $dt,
                      'jam' => $tm,
                      'petugas' => $user,
                      'kd_bangsal' => 'B0002',
                      'status' =>  'Simpan'
                    ]);
                  }
            }else{
                $kurang = '0' - $stokreal;
                $nominal =  $kurang * $hbeli;
                $hasillebih = $kurang * (-1);
                $nominallebih = $nominal * (-1);
                if ($kurang > 0) {
                    $selisih = $kurang;
                    $nomihilang = $nominal;
                    $lebih = '0';
                    $nomilebih = '0';
                }else{
                    $selisih = '0';
                    $nomihilang = '0';
                    $lebih = $hasillebih;
                    $nomilebih = $nominallebih;
                }
    
                $query = $this->db('opname')->save([
                  'kode_brng' => $kode_brng,
                  'h_beli' => $hbeli,
                  'tanggal' => $dt,
                  'stok' => '0',
                  '`real`'  =>  $stokreal,
                  'selisih' => $selisih,
                  'nomihilang' => $nomihilang,
                  'keterangan' =>  $ket,
                  'kd_bangsal' => 'B0002',
                  'lebih' => $lebih,
                  'nomilebih' => $nomilebih
                  ]);

                if ($query) {
                    $this->db('gudangbarang')->where('kode_brng', $kode_brng)->where('kd_bangsal', 'B0002')->update(['stok' => $stokreal]);
                    $this->db('riwayat_barang_medis')->save([
                      'kode_brng' => $kode_brng,
                      'stok_awal' => '0',
                      'masuk' => $stokreal,
                      'keluar' => '0',
                      'stok_akhir' =>  $stokreal,
                      'posisi' => 'Opname',
                      'tanggal' =>  $dt,
                      'jam' => $tm,
                      'petugas' => $user,
                      'kd_bangsal' => 'B0002',
                      'status' =>  'Simpan'
                    ]);
                }
            }
            
            $this->notify('success', 'Input stok opname berhasil disimpan.');
        } else {
        $this->notify('failure', 'Gagal menyimpan stok opname.');
      }
      
      redirect(url([ADMIN, 'laporan_farmasi', 'input_stokopname']));
    }

    public function postObatList()
    {
      if (isset($_POST["query"])) {
          $output = '';
          $key = "%" . $_POST["query"] . "%";
          $rows = $this->db('databarang')->like('nama_brng', $key)->limit(10)->toArray();
          $output = '';
          if (count($rows)) {
            foreach ($rows as $row) {
              $output .= '<li data-id="' . $row['kode_brng'] . '" class="list-group-item link-class">' . $row["nama_brng"] . '</li>';
            }
          }
          echo $output;
        }  
      exit();
    }

    public function getData_Obat($page = 1)
    {
        $this->_addHeaderFiles();
        $perpage = '10';
        $phrase = isset($_GET['s']) ? $_GET['s'] : '';
        
        if (!$phrase) {
            return $this->draw('data_obat.html', ['data_obat' => $this->assign]);
        }
        
        $totalRecords = $this->db('databarang')
            ->like('nama_brng', '%' . $phrase . '%')
            ->asc('nama_brng')
            ->toArray();
    
        $pagination = new \Systems\Lib\Pagination($page, count($totalRecords), 10, url([ADMIN, 'laporan_farmasi', 'data_obat', '&s=' . $phrase]));
        $this->assign['pagination'] = $pagination->nav('pagination', '5');
        $this->assign['totalRecords'] = $totalRecords;
    
        $offset = $pagination->offset();
        $rows = $this->db('databarang')
            ->like('nama_brng', '%' . $phrase . '%')
            ->asc('nama_brng')
            ->offset($offset)
            ->limit($perpage)
            ->toArray();
        
        $this->assign['list'] = [];
        if (count($rows)) {
            foreach ($rows as $row) {
              $satuan = $this->db('kodesatuan')->where('kode_sat', $row['kode_sat'])->oneArray();
              $row['satuan'] = $satuan['satuan'];

              $harga_beli = number_format($row['h_beli'], 0, ',', '.');
              $row['harga_beli'] = $harga_beli;

              $harga_ralan = number_format($row['ralan'], 0, ',', '.');
              $row['harga_ralan'] = $harga_ralan;
            
              $stokgudang = $this->db('gudangbarang')->where('kode_brng', $row['kode_brng'])->where('kd_bangsal', 'B0002')->oneArray();
              $row['stok_gudang'] = $stokgudang['stok'];
            
              $stokrajal = $this->db('gudangbarang')->where('kode_brng', $row['kode_brng'])->where('kd_bangsal', 'B0014')->oneArray();
              $rajal_stok = $stokrajal['stok'];
              $rajal = number_format($rajal_stok, 2, ',', '.');
              $row['stok_rajal'] = $rajal;
            
              $stokranap = $this->db('gudangbarang')->where('kode_brng', $row['kode_brng'])->where('kd_bangsal', 'B0001')->oneArray();
              $ranap_stok = $stokranap['stok'];
              $ranap = number_format($ranap_stok, 2, ',', '.');
              $row['stok_ranap'] = $ranap;

              $stokigd = $this->db('gudangbarang')->where('kode_brng', $row['kode_brng'])->where('kd_bangsal', 'B0018')->oneArray();
              $igd_stok = $stokigd['stok'];
              $igd = number_format($igd_stok, 2, ',', '.');
              $row['stok_igd'] = $igd;

            
              $total = $row['stok_gudang'] + $row['stok_rajal'] + $row['stok_ranap'] + $row['stok_igd'] ;
              // $row['total_stok'] = $total;
              $totalstok = number_format($total, 2, ',', '.');
              $row['total_stok'] = $totalstok;
          
                $this->assign['list'][] = $row;
            }
        }
    
        return $this->draw('data_obat.html', ['data_obat' => $this->assign]);
    }

    public function getCSS()
    {
        header('Content-type: text/css');
        echo $this->draw(MODULES.'/laporan_farmasi/css/admin/laporan_farmasi.css');
        exit();
    }

    public function getJavascript()
    {
        header('Content-type: text/javascript');
        echo $this->draw(MODULES.'/laporan_farmasi/js/admin/laporan_farmasi.js');
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
        $this->core->addCSS(url([ADMIN, 'laporan_farmasi', 'css']));
        $this->core->addJS(url([ADMIN, 'laporan_farmasi', 'javascript']), 'footer');
    }

}