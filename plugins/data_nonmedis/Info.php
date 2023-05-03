<?php
return [
  'name'          =>  'Stock Opname Non Medis ',
  'description'   =>  'Modul Stock Opname Non Medis',
  'author'        =>  'Basoro.ID',
  'version'       =>  '1.5',
  'compatibility' =>  '2021',
  'icon'          =>  'book',
  'install'       =>  function () use ($core) {

      $core->db()->pdo()->exec("CREATE TABLE IF NOT EXISTS `ipsrs_sirkulasi` (
            `id` int(15) NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `tahun` varchar(10) DEFAULT NULL,
            `bidang` varchar(50) DEFAULT NULL,
            `kode_rek` varchar(15) DEFAULT NULL,
            `nama` varchar(70) DEFAULT NULL,
            `satuan` varchar(15) DEFAULT NULL,
            `harga` varchar(15) DEFAULT NULL,
            `spek` varchar(15) DEFAULT NULL,
            `stok_awal` varchar(15) DEFAULT NULL,
            `stok_masuk` varchar(15) DEFAULT NULL,
            `stok_keluar` varchar(15) DEFAULT NULL,
            `stok_akhir` varchar(15) DEFAULT NULL,
            `saldo_awal` varchar(15) DEFAULT NULL,
            `saldo_masuk` varchar(15) DEFAULT NULL,
            `saldo_keluar` varchar(15) DEFAULT NULL,
            `saldo_akhir` varchar(15) DEFAULT NULL,
            `tgl_upload` datetime NOT NULL,
            `nip` varchar(20) DEFAULT NULL
          ) ENGINE=InnoDB DEFAULT CHARSET=latin1;");
        },

        'uninstall'     =>  function() use($core)
        {
        }
      ];