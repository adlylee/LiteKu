<?php

namespace Plugins\Triase_Igd;

use Systems\SiteModule;

class Site extends SiteModule
{

    public function routes()
    {
        $this->route('triase_igd/display', 'getDisplay');
        $this->route('triase_igd/triage', 'getInputTriage');
        $this->route('triase_igd/savetriage', 'postHasilTriage');
    }

    public function getDisplay()
    {
        $title = 'Display Antrian Poliklinik';
        $logo  = $this->settings->get('settings.logo');
        $display = $this->_getDisplay();

        $_username = $this->core->getUserInfo('fullname', null, true);
        $__username = $this->core->getUserInfo('username');
        if ($this->core->getUserInfo('username') !== '') {
            $__username = 'Tamu';
        }
        $tanggal       = getDayIndonesia(date('Y-m-d')) . ', ' . dateIndonesia(date('Y-m-d'));
        $username      = !empty($_username) ? $_username : $__username;

        $content = $this->draw('display.html', [
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

    public function _getDisplay()
    {
        $rows = $this->db('reg_periksa')->join('pasien', 'reg_periksa.no_rkm_medis=pasien.no_rkm_medis')->where('kd_poli', 'IGDK')->where('tgl_registrasi', '2021-11-22')->where('stts', 'Belum')->toArray();
        $result = [];
        foreach ($rows as $row) {
            $triage = $this->db('triase_igd')->where('no_rawat', $row['no_rawat'])->where('status', 1)->oneArray();
            $row['warna'] = $triage['warna_triase'];
            $row['bed'] = $triage['bed'];
            $row['nm_pasien'] = substr($row['nm_pasien'], 0, 4) . '***';
            $result[] = $row;
        }
        return $result;
    }

    public function getInputTriage()
    {
        $title = 'Triage IGD';
        $logo  = $this->settings->get('settings.logo');

        $_username = $this->core->getUserInfo('fullname', null, true);
        $__username = $this->core->getUserInfo('username');
        if ($this->core->getUserInfo('username') !== '') {
            $__username = 'Tamu';
        }
        $tanggal       = getDayIndonesia(date('Y-m-d')) . ', ' . dateIndonesia(date('Y-m-d'));
        $username      = !empty($_username) ? $_username : $__username;
        $petugas       = $this->db('petugas')->select(['nama' => 'petugas.nama', 'nip' => 'petugas.nip'])->join('pegawai', 'pegawai.nik = petugas.nip')->where('petugas.status', '1')->where('pegawai.bidang', 'Instalasi Gawat Darurat (Ibnu Sina)')->toArray();
        $dokter        = $this->db('dokter')->where('kd_sps', 'S0016')->where('status', '1')->toArray();

        $content = $this->draw('triage.html', [
            'title' => $title,
            'logo' => $logo,
            'powered' => 'Powered by <a href="https://basoro.org/">KhanzaLITE</a>',
            'username' => $username,
            'tanggal' => $tanggal,
            'petugas' => $petugas,
            'dokter' => $dokter,
        ]);

        $assign = [
            'title' => $this->settings->get('settings.nama_instansi'),
            'desc' => $this->settings->get('settings.alamat'),
            'content' => $content
        ];

        $this->setTemplate("canvas.html");

        $this->tpl->set('page', ['title' => $assign['title'], 'desc' => $assign['desc'], 'content' => $assign['content']]);
    }

    public function postHasilTriage()
    {
        switch ($_POST['antar']) {
            case 'Petugas Kesehatan':
                $_POST['diantar'] = $_POST['petkes'];
                break;
            case 'Lainnya':
                $_POST['diantar'] = $_POST['antarlain'];
                break;

            default:
                $_POST['diantar'] = $_POST['antar'];
                break;
        }

        $triage = [];
        if (isset($_POST['airway'])) {
            $triage['airway'] = $_POST['airway'];
        }
        if (isset($_POST['breathing'])) {
            $triage['breathing'] = $_POST['breathing'];
        }
        if (isset($_POST['circulation'])) {
            $triage['circulation'] = $_POST['circulation'];
        }
        if (isset($_POST['disability'])) {
            $triage['disability'] = $_POST['disability'];
        }

        if (!isset($_POST['trauma'])) {
            $_POST['trauma'] = 'Non Trauma';
        }
        if (!isset($_POST['diantar'])) {
            $_POST['diantar'] = 'Keluarga';
        }
        if (!isset($_POST['transport'])) {
            $_POST['transport'] = 'Kendaraan Pribadi';
        }

        $alergi = [];
        if (isset($_POST['alergi'])) {
            $alergi['obat'] = $_POST['alergiobat'];
            $alergi['makanan'] = $_POST['alergimakanan'];
            $alergi['lainnya'] = $_POST['alergilain'];
        }

        $vaksin = [];
        if (isset($_POST['vaksin'])) {
            $vaksin['imun'] = $_POST['imundasar'];
            $vaksin['covid'] = $_POST['vaksincovid'];
            $vaksin['lainnya'] = $_POST['imunlain'];
        }

        $kolom = 'no_rkm_medis';
        if (strlen($_POST['no_kartu'] == '13')) {
            $kolom = 'no_peserta';
        }
        if (strlen($_POST['no_kartu'] == '16')) {
            $kolom = 'no_ktp';
        }
        $pasien = $this->db('pasien')->where($kolom, $_POST['no_kartu'])->oneArray();
        if ($pasien) {
            $_POST['no_kartu'] = $pasien['no_rkm_medis'];
        }

        $this->db('mlite_triase_igd')->save([
            'no_kartu' => $_POST['no_kartu'],
            'kd_petugas' => $_POST['petugas'],
            'kd_dokter' => $_POST['dokter'],
            'triage' => $_POST['triage'],
            'cara_bayar' => $_POST['cara_bayar'],
            'diantar' => $_POST['diantar'],
            'transport' => $_POST['transport'],
            'keluhan' => $_POST['keluhan'],
            'riwayat' => $_POST['riwayat'],
            'trauma' => $_POST['trauma'],
            'jns_trauma' => $_POST['jns_trauma'],
            'rujukan' => $_POST['rujukan'],
            'diagnosa' => $_POST['diagnosa'],
            'pengantar' => $_POST['pengantar'],
            'no_kartu' => $_POST['no_kartu'],
            'tensi' => $_POST['tensi'],
            'respirasi' => $_POST['respirasi'],
            'nadi' => $_POST['nadi'],
            'suhu' => $_POST['suhu'],
            'berat' => $_POST['berat'],
            'spo2' => $_POST['spo2'],
            'tinggi' => $_POST['tinggi'],
            'lk' => $_POST['lk'],
            'lila' => $_POST['lila'],
            'bed' => $_POST['bed'],
            'tanggal' => date('Y-m-d'),
            'jam' => date('H:i:s'),
            'alergi' => json_encode($alergi),
            'vaksin' => json_encode($vaksin),
            'tindakan_triage' => json_encode($triage),
            'created_at' => date('Y-m-d H:i:s'),

        ]);
        $che = $this->db('mlite_triase_igd')->lastInsertId();
        if ($che) {
            $response = array(
                'response' => array(
                    'text' => 'Berhasil Simpan'
                ),
                'metadata' => array(
                    'message' => 'Created',
                    'code' => 201
                )
            );
        } else {
            $response = array(
                'response' => array(
                    'text' => 'Gagal Simpan'
                ),
                'metadata' => array(
                    'message' => 'Ok',
                    'code' => 200
                )
            );
        }
        echo json_encode($response);
        exit();
    }
}
