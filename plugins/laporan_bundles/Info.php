<?php
return [
    'name'          =>  'Laporan Bundles HAIs',
    'description'   =>  'Data Laporan Bundles HAIs KhanzaLITE.',
    'author'        =>  'Basoro',
    'version'       =>  '1.1',
    'compatibility' =>  '2021',
    'icon'          =>  'list-ul',
    'install'       =>  function () use ($core) {
        $core->db()->pdo()->exec("CREATE TABLE IF NOT EXISTS `bundles_hais` (
            `id` int(11) NOT NULL,
            `tanggal` date NOT NULL,
            `no_rawat` varchar(17) NOT NULL,
            `kd_kamar` varchar(15) DEFAULT NULL,
            `hand_vap` varchar(10) DEFAULT NULL,
            `tehniksteril_vap` varchar(10) DEFAULT NULL,
            `apd_vap` varchar(10) DEFAULT NULL,
            `sedasi_vap` varchar(10) DEFAULT NULL,
            `hand_iadp` varchar(10) DEFAULT NULL,
            `area_iadp` varchar(10) DEFAULT NULL,
            `tehniksteril_iadp` varchar(10) DEFAULT NULL,
            `alcohol_iadp` varchar(10) DEFAULT NULL,
            `apd_iadp` varchar(10) DEFAULT NULL,
            `hand_vena` varchar(10) DEFAULT NULL,
            `kaji_vena` varchar(10) DEFAULT NULL,
            `tehnik_vena` varchar(10) DEFAULT NULL,
            `petugas_vena` varchar(10) DEFAULT NULL,
            `desinfeksi_vena` varchar(10) DEFAULT NULL,
            `kaji_isk` varchar(10) DEFAULT NULL,
            `petugas_isk` varchar(10) DEFAULT NULL,
            `tangan_isk` varchar(10) DEFAULT NULL,
            `tehniksteril_isk` varchar(10) DEFAULT NULL,
            `hand_mainvap` varchar(10) DEFAULT NULL,
            `oral_mainvap` varchar(10) DEFAULT NULL,
            `manage_mainvap` varchar(10) DEFAULT NULL,
            `sedasi_mainvap` varchar(10) DEFAULT NULL,
            `kepala_mainvap` varchar(10) DEFAULT NULL,
            `hand_mainiadp` varchar(10) DEFAULT NULL,
            `desinfeksi_mainiadp` varchar(10) DEFAULT NULL,
            `perawatan_mainiadp` varchar(10) DEFAULT NULL,
            `dreasing_mainiadp` varchar(10) DEFAULT NULL,
            `infus_mainiadp` varchar(10) DEFAULT NULL,
            `hand_mainvena` varchar(10) DEFAULT NULL,
            `perawatan_mainvena` varchar(10) DEFAULT NULL,
            `kaji_mainvena` varchar(10) DEFAULT NULL,
            `administrasi_mainvena` varchar(10) DEFAULT NULL,
            `edukasi_mainvena` varchar(10) DEFAULT NULL,
            `hand_mainisk` varchar(10) DEFAULT NULL,
            `kateter_mainisk` varchar(10) DEFAULT NULL,
            `baglantai_mainisk` varchar(10) DEFAULT NULL,
            `bagrendah_mainisk` varchar(10) DEFAULT NULL,
            `posisiselang_mainisk` varchar(10) DEFAULT NULL,
            `lepas_mainisk` varchar(10) DEFAULT NULL,
            `mandi_idopre` varchar(10) DEFAULT NULL,
            `cukur_idopre` varchar(10) DEFAULT NULL,
            `guladarah_idopre` varchar(10) DEFAULT NULL,
            `antibiotik_idopre` varchar(10) DEFAULT NULL,
            `hand_idointra` varchar(10) DEFAULT NULL,
            `steril_idointra` varchar(10) DEFAULT NULL,
            `antiseptic_idointra` varchar(10) DEFAULT NULL,
            `tehnik_idointra` varchar(10) DEFAULT NULL,
            `mobile_idointra` varchar(10) DEFAULT NULL,
            `suhu_idointra` varchar(10) DEFAULT NULL,
            `luka_idopost` varchar(10) DEFAULT NULL,
            `rawat_idopost` varchar(10) DEFAULT NULL,
            `apd_idopost` varchar(10) DEFAULT NULL,
            `kaji_idopost` varchar(10) DEFAULT NULL
          ) ENGINE=InnoDB DEFAULT CHARSET=latin1;");

    $core->db()->pdo()->exec("ALTER TABLE `bundles_hais`
    ADD PRIMARY KEY (`id`),
    ADD UNIQUE KEY `no_rawat` (`no_rawat`),
    ADD UNIQUE KEY `kd_kamar` (`kd_kamar`);");

    $core->db()->pdo()->exec("ALTER TABLE `bundles_hais`
    ADD CONSTRAINT `reg_periksa` FOREIGN KEY (`no_rawat`) REFERENCES `reg_periksa` (`no_rawat`),
    ADD CONSTRAINT `kamar` FOREIGN KEY (`kd_kamar`) REFERENCES `kamar` (`kd_kamar`);");
},
'uninstall'     =>  function () use ($core) {
}
];
