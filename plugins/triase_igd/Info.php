<?php
return [
  'name'          =>  'Triase IGD',
  'description'   =>  'Modul Triase IGD',
  'author'        =>  'Basoro.ID',
  'version'       =>  '0.1',
  'compatibility' =>  '2021',
  'icon'          =>  'user-o',
  'install'       =>  function () use ($core) {

    $core->db()->pdo()->exec("CREATE TABLE IF NOT EXISTS `triase_igd` (
          `id` int NOT NULL AUTO_INCREMENT,
          `no_rawat` varchar(20) NOT NULL,
          `warna_triase` varchar(10) NOT NULL,
          `bed` varchar(5) NOT NULL,
          `created_at` varchar(40) NOT NULL,
          `status` varchar(2) NOT NULL,
          PRIMARY KEY (id)
        ) ENGINE=InnoDB DEFAULT CHARSET=latin1;");
  },
  'uninstall'     =>  function () use ($core) {
  }
];
