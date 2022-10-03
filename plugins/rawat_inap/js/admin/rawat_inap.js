// sembunyikan form dan notif
$("#form_rincian").hide();
$("#form_soap").hide();
$("#form_sep").hide();
$("#form_berkasdigital").hide();
$("#histori_pelayanan").hide();
$("#form_hais").hide();
$("#hais").hide();
$("#form_jadwaloperasi").hide();
$("#form_dietpasien").hide();
$("#form_kerohanian").hide();
$("#notif").hide();
$('#provider').hide();
$('#aturan_pakai').hide();
$("#tanggal_keluar").hide();
$("#diagnosa_keluar").hide();

// tombol buka form diklik
$("#index").on('click', '#bukaform', function(){
  var baseURL = mlite.url + '/' + mlite.admin;
  event.preventDefault();
  $("#form").show().load(baseURL + '/rawat_inap/form?t=' + mlite.token);
  $("#bukaform").val("Tutup Form");
  $("#bukaform").attr("id", "tutupform");
});

// tombol tutup form diklik
$("#index").on('click', '#tutupform', function(){
  event.preventDefault();
  $("#form").hide();
  $("#tutupform").val("Buka Form");
  $("#tutupform").attr("id", "bukaform");
});

// tombol batal diklik
$("#form").on("click", "#batal", function(event){
  $("#pasien").hide();
  $('input:text[name=pasien]').val("");
  $('input:text[name=jk]').val("");
  $('input:text[name=stts_daftar]').val("");
  $('input:text[name=no_tlp]').val("");
  $('input:text[name=no_rawat]').removeAttr("disabled", true);
  $('input:text[name=no_reg]').removeAttr("disabled", true);
  bersih();
});

// tombol  diklik
$("#form").on("click", "#simpan", function(event){
  var baseURL = mlite.url + '/' + mlite.admin;
  event.preventDefault();
  var no_rawat = $('input:text[name=no_rawat]').val();
  var kd_kamar = $('select[name=kd_kamar]').val();
  var kd_dokter = $('select[name=kd_dokter]').val();
  var tgl_masuk = $('#tgl_masuk').val();
  var jam_masuk = $('#jam_masuk').val();
  var lama = $('input:text[name=lama]').val();
  var diagnosa_awal = $('input:text[name=diagnosa_awal]').val();

  var url = baseURL + '/rawat_inap/save?t=' + mlite.token;

  if(kd_kamar == '' || diagnosa_awal == '' || tgl_masuk == ''|| jam_masuk == ''|| lama == '') {
    bootbox.alert("Isian ada yang kosong!");
  } else {
    $.post(url,{
      no_rawat: no_rawat,
      kd_kamar: kd_kamar,
      kd_dokter: kd_dokter,
      tgl_masuk: tgl_masuk,
      jam_masuk: jam_masuk,
      lama: lama,
      diagnosa_awal: diagnosa_awal
    } ,function(data) {
      $("#display").show().load(baseURL + '/rawat_inap/display?t=' + mlite.token);
      bersih();
      $("#status_pendaftaran").hide();
      $('#notif').html("<div class=\"alert alert-success alert-dismissible fade in\" role=\"alert\" style=\"border-radius:0px;margin-top:-15px;\">"+
      "Data pasien telah disimpan!"+
      "<button type=\"button\" class=\"close\" data-dismiss=\"alert\" aria-label=\"Close\">&times;</button>"+
      "</div>").show();
    });
  }
});

// tombol  diklik
$("#form").on("click", "#simpan_keluar", function(event){
  var baseURL = mlite.url + '/' + mlite.admin;
  event.preventDefault();
  var no_rawat = $('input:text[name=no_rawat]').val();
  var kd_kamar = $('select[name=kd_kamar]').val();
  var tgl_keluar = $('#tgl_keluar').val();
  var jam_keluar = $('#jam_keluar').val();
  var lama = $('input:text[name=lama]').val();
  var diagnosa_akhir = $('input:text[name=diagnosa_akhir]').val();
  var stts_pulang = $('select[name=stts_pulang]').val();

  var url = baseURL + '/rawat_inap/savekeluar?t=' + mlite.token;

  if(stts_pulang == '' || diagnosa_akhir == '' || tgl_keluar == ''|| jam_keluar == ''|| lama == '') {
    bootbox.alert("Isian ada yang kosong!");
  } else {
    $.post(url,{
      no_rawat: no_rawat,
      kd_kamar: kd_kamar,
      tgl_keluar: tgl_keluar,
      jam_keluar: jam_keluar,
      lama: lama,
      diagnosa_akhir: diagnosa_akhir,
      stts_pulang: stts_pulang
    } ,function(data) {
      $("#display").show().load(baseURL + '/rawat_inap/display?t=' + mlite.token);
      bersih();
      $("#status_pendaftaran").hide();
      $('#notif').html("<div class=\"alert alert-success alert-dismissible fade in\" role=\"alert\" style=\"border-radius:0px;margin-top:-15px;\">"+
      "Data pasien telah disimpan!"+
      "<button type=\"button\" class=\"close\" data-dismiss=\"alert\" aria-label=\"Close\">&times;</button>"+
      "</div>").show();
    });
  }
});

$("#display").on("click",".riwayat_perawatan", function(event){
  var baseURL = mlite.url + '/' + mlite.admin;
  event.preventDefault();
  var no_rkm_medis = $(this).attr("data-no_rkm_medis");
  window.open(baseURL + '/pasien/riwayatperawatan/' + no_rkm_medis + '?t=' + mlite.token);
});

// ketika baris data diklik
$("#display").on("click", ".edit", function(event){
  var baseURL = mlite.url + '/' + mlite.admin;
  event.preventDefault();
  var url = baseURL + '/rawat_inap/form?t=' + mlite.token;
  var no_rawat = $(this).attr("data-no_rawat");
  $.post(url, {no_rawat: no_rawat} ,function(data) {
    // tampilkan data
    $("#form").html(data).show();
    $("#stts_daftar").hide();
    $("#cari_pasien").hide();
    $("#tanggal_keluar").show();
    $("#diagnosa_masuk").hide();
    $("#diagnosa_keluar").show();
    //var url    				= baseURL + '/rawat_inap/statusdaftar?t=' + mlite.token;

    //$.post(url, {no_rawat: no_rawat} ,function(data) {
    //  $("#stts_daftar").html(data).show();
    //});
  });
});

// ketika baris data diklik
$("#display").on("click", ".set_dpjp___", function(event){
  var baseURL = mlite.url + '/' + mlite.admin;
  event.preventDefault();
  var url = baseURL + '/rawat_inap/setdpjp?t=' + mlite.token;
  var no_rawat = $(this).attr("data-no_rawat");
  var kd_dokter = 'D0000063';

  $.post(url, {no_rawat: no_rawat, kd_dokter: kd_dokter} ,function(data) {
    // tampilkan data
    $("#display").show().load(baseURL + '/rawat_inap/display?t=' + mlite.token);
    $('#notif').html("<div class=\"alert alert-success alert-dismissible fade in\" role=\"alert\" style=\"border-radius:0px;margin-top:-15px;\">"+
    "Data pasien telah disimpan!"+
    "<button type=\"button\" class=\"close\" data-dismiss=\"alert\" aria-label=\"Close\">&times;</button>"+
    "</div>").show();
  });
});

// ketika tombol hapus ditekan
$("#form").on("click","#hapus", function(event){
  var baseURL = mlite.url + '/' + mlite.admin;
  event.preventDefault();
  var url = baseURL + '/rawat_inap/hapus?t=' + mlite.token;
  //var no_rawat = $(this).attr("data-no_rawat");
  var no_rawat = $('input:text[name=no_rawat]').val();
  var tgl_masuk = $('input:text[name=tgl_masuk]').val();
  var jam_masuk = $('input:text[name=jam_masuk]').val();

  // tampilkan dialog konfirmasi
  bootbox.confirm("Apakah Anda yakin ingin menghapus data ini?", function(result){
    // ketika ditekan tombol ok
    if (result){
      // mengirimkan perintah penghapusan
      $.post(url, {
        no_rawat: no_rawat,
        tgl_masuk: tgl_masuk,
        jam_masuk: jam_masuk
      } ,function(data) {
        // sembunyikan form, tampilkan data yang sudah di perbaharui, tampilkan notif
        $("#display").load(baseURL + '/rawat_inap/display?t=' + mlite.token);
        bersih();
        $('#notif').html("<div class=\"alert alert-danger alert-dismissible fade in\" role=\"alert\" style=\"border-radius:0px;margin-top:-15px;\">"+
        "Data pasien telah dihapus!"+
        "<button type=\"button\" class=\"close\" data-dismiss=\"alert\" aria-label=\"Close\">&times;</button>"+
        "</div>").show();
      });
    }
  });
});

$("#display").on("click", ".sep", function(event){
  var baseURL = mlite.url + '/' + mlite.admin;
  event.preventDefault();

  var no_rawat = $(this).attr("data-no_rawat");
  var no_rkm_medis = $(this).attr("data-no_rkm_medis");
  var nm_pasien = $(this).attr("data-nm_pasien");
  var tgl_registrasi = $(this).attr("data-tgl_registrasi");
  var no_peserta = $(this).attr("data-no_peserta");

  var url = baseURL + '/vclaim/bynokartu/' + no_peserta + '/{?=date('Y-m-d')?}?t=' + mlite.token;

  $.get(url,function(data) {
    var data = JSON.parse(data);
    var json_obj = [data];
    if(!json_obj[0]) {
      alert('Koneksi ke server BPJS terputus. Silahkan ulangi lagi!');
    } else if(json_obj[0].metaData.code == 200) {
      $('.nama_peserta').text(json_obj[0].response.peserta.nama);
      $('#no_kartu_peserta').text(json_obj[0].response.peserta.noKartu);
      $('#no_mr_peserta').text(no_rkm_medis);
      $('#nik_peserta').text(json_obj[0].response.peserta.nik);
      $('#tgl_lahir_peserta').text(json_obj[0].response.peserta.tglLahir);
      $('#status_peserta').text(json_obj[0].response.peserta.statusPeserta.keterangan);
      $('#jenis_peserta').text(json_obj[0].response.peserta.jenisPeserta.keterangan);
      $('.prolainis_peserta').text(json_obj[0].response.peserta.informasi.prolanisPRB);

      var jenis_kelamin = 'Laki-Laki';
      if(json_obj[0].response.peserta.sex == 'P') {
        var jenis_kelamin = 'Perempuan';
      }

      $('input:text[name=sep_jenis_kelamin_nama]').val(jenis_kelamin);
      $('input:text[name=sep_jenis_kelamin_kode]').val(json_obj[0].response.peserta.sex);
      $('input:text[name=sep_tanggal_lahir]').val(json_obj[0].response.peserta.tglLahir);
      $('input:text[name=sep_jenis_peserta]').val(json_obj[0].response.peserta.jenisPeserta.keterangan);
      $('input:text[name=sep_no_kartu]').val(json_obj[0].response.peserta.noKartu);
      $('input:text[name=sep_norm]').val(json_obj[0].response.peserta.mr.noMR);
      $('input:text[name=sep_eksekutif_kode]').val("0");
      $('input:text[name=sep_eksekutif_nama]').val("Tidak");
      $('input:text[name=sep_kunjungan_kode]').val("0");
      $('input:text[name=sep_kunjungan_nama]').val("Normal");
      $('input:text[name=sep_cob_kode]').val("0");
      $('input:text[name=sep_cob_nama]').val("Tidak");
      $('input:text[name=sep_katarak_kode]').val("0");
      $('input:text[name=sep_katarak_nama]').val("Tidak");
      $('input:text[name=sep_status_kecelakaan_kode]').val("0");
      $('input:text[name=sep_status_kecelakaan_nama]').val("Tidak");
      $('input:text[name=sep_penjamin_kecelakaan_kode]').val("0");
      $('input:text[name=sep_penjamin_kecelakaan_nama]').val("Tidak");
      $('input:text[name=sep_suplesi_kode]').val("0");
      $('input:text[name=sep_suplesi_nama]').val("Tidak");
      $('input:text[name=sep_kelas_kode]').val(json_obj[0].response.peserta.hakKelas.kode);
      $('input:text[name=sep_kelas_nama]').val(json_obj[0].response.peserta.hakKelas.keterangan);
      $('input:text[name=sep_nomor_telepon]').val(json_obj[0].response.peserta.mr.noTelepon);

    } else {
      alert(json_obj[0].metaData.message);
    }
  });

  $('input:text[name=sep_no_rawat]').val(no_rawat);
  $('input:text[name=no_rkm_medis]').val(no_rkm_medis);
  $('input:text[name=nm_pasien]').val(nm_pasien);
  $('input:text[name=tgl_registrasi]').val(tgl_registrasi);
  $('input:text[name=nomor_asuransi]').val(no_peserta);
  $('input:text[name=no_kartu_pcare]').val(no_peserta);
  $('input:text[name=no_kartu_rs]').val(no_peserta);
  $("#display").hide();
  $("#form_rincian").hide();
  $("#form").hide();
  $("#notif").hide();
  $("#form_soap").hide();
  $("#form_sep").show();
  $("#bukaform").hide();
});


$('#manage').on('click', '#submit_periode_rawat_inap', function(event){
  var baseURL = mlite.url + '/' + mlite.admin;
  event.preventDefault();
  var url    = baseURL + '/rawat_inap/display?t=' + mlite.token;
  var periode_rawat_inap  = $('input:text[name=periode_rawat_inap]').val();
  var periode_rawat_inap_akhir  = $('input:text[name=periode_rawat_inap_akhir]').val();
  var status_pulang = 'all';

  if(periode_rawat_inap == '') {
    alert('Tanggal awal masih kosong!')
  }
  if(periode_rawat_inap_akhir == '') {
    alert('Tanggal akhir masih kosong!')
  }

  $.post(url, {periode_rawat_inap: periode_rawat_inap, periode_rawat_inap_akhir: periode_rawat_inap_akhir, status_pulang: status_pulang} ,function(data) {
  // tampilkan data
    $("#form").show();
    $("#display").html(data).show();
    $("#form_rincian").hide();
    $("#form_soap").hide();
    $("#form_sep").hide();
    $("#notif").hide();
    $("#rincian").hide();
    $("#sep").hide();
    $("#soap").hide();
    $("#form_hais").hide();
    $("#form_jadwaloperasi").hide();
    $("#form_dietpasien").hide();
    $("#form_kerohanian").hide();
    $('.periode_rawat_inap').datetimepicker('remove');
  });

  event.stopPropagation();

});

$('#manage').on('click', '#masuk_periode_rawat_inap', function(event){
  var baseURL = mlite.url + '/' + mlite.admin;
  event.preventDefault();
  var url    = baseURL + '/rawat_inap/display?t=' + mlite.token;
  var periode_rawat_inap  = $('input:text[name=periode_rawat_inap]').val();
  var periode_rawat_inap_akhir  = $('input:text[name=periode_rawat_inap_akhir]').val();
  var status_pulang = 'masuk';

  if(periode_rawat_inap == '') {
    alert('Tanggal awal masih kosong!')
  }
  if(periode_rawat_inap_akhir == '') {
    alert('Tanggal akhir masih kosong!')
  }

  $.post(url, {periode_rawat_inap: periode_rawat_inap, periode_rawat_inap_akhir: periode_rawat_inap_akhir, status_pulang: status_pulang} ,function(data) {
  // tampilkan data
    $("#form").show();
    $("#display").html(data).show();
    $("#form_rincian").hide();
    $("#form_soap").hide();
    $("#form_sep").hide();
    $("#notif").hide();
    $("#rincian").hide();
    $("#sep").hide();
    $("#soap").hide();
    $("#form_hais").hide();
    $("#form_jadwaloperasi").hide();
    $("#form_dietpasien").hide();
    $("#form_kerohanian").hide();
    $('.periode_rawat_inap').datetimepicker('remove');
  });

  event.stopPropagation();

});

$('#manage').on('click', '#pulang_periode_rawat_inap', function(event){
  var baseURL = mlite.url + '/' + mlite.admin;
  event.preventDefault();
  var url    = baseURL + '/rawat_inap/display?t=' + mlite.token;
  var periode_rawat_inap  = $('input:text[name=periode_rawat_inap]').val();
  var periode_rawat_inap_akhir  = $('input:text[name=periode_rawat_inap_akhir]').val();
  var status_pulang = 'pulang';

  if(periode_rawat_inap == '') {
    alert('Tanggal awal masih kosong!')
  }
  if(periode_rawat_inap_akhir == '') {
    alert('Tanggal akhir masih kosong!')
  }

  $.post(url, {periode_rawat_inap: periode_rawat_inap, periode_rawat_inap_akhir: periode_rawat_inap_akhir, status_pulang: status_pulang} ,function(data) {
  // tampilkan data
    $("#form").show();
    $("#display").html(data).show();
    $("#form_rincian").hide();
    $("#form_soap").hide();
    $("#form_sep").hide();
    $("#notif").hide();
    $("#rincian").hide();
    $("#sep").hide();
    $("#soap").hide();
    $("#form_hais").hide();
    $("#form_jadwaloperasi").hide();
    $("#form_dietpasien").hide();
    $("#form_kerohanian").hide();
    $('.periode_rawat_inap').datetimepicker('remove');
  });

  event.stopPropagation();

});

$('#manage').on('click', '#lunas_periode_rawat_inap', function(event){
  var baseURL = mlite.url + '/' + mlite.admin;
  event.preventDefault();
  var url    = baseURL + '/rawat_inap/display?t=' + mlite.token;
  var periode_rawat_inap  = $('input:text[name=periode_rawat_inap]').val();
  var periode_rawat_inap_akhir  = $('input:text[name=periode_rawat_inap_akhir]').val();
  var status_periksa = 'lunas';

  if(periode_rawat_inap == '') {
    alert('Tanggal awal masih kosong!')
  }
  if(periode_rawat_inap_akhir == '') {
    alert('Tanggal akhir masih kosong!')
  }

  $.post(url, {periode_rawat_inap: periode_rawat_inap, periode_rawat_inap_akhir: periode_rawat_inap_akhir, status_periksa: status_periksa} ,function(data) {
  // tampilkan data
    $("#form").show();
    $("#display").html(data).show();
    $("#form_rincian").hide();
    $("#form_soap").hide();
    $("#form_sep").hide();
    $("#notif").hide();
    $("#rincian").hide();
    $("#sep").hide();
    $("#soap").hide();
    $("#form_hais").hide();
    $("#form_jadwaloperasi").hide();
    $("#form_dietpasien").hide();
    $("#form_kerohanian").hide();
    $('.periode_rawat_inap').datetimepicker('remove');
  });

  event.stopPropagation();

});

//$("#display").on("click", ".soap", function(event){

// ketika tombol simpan diklik
$("#form_soap").on("click", "#simpan_soap", function(event){
  {if: !$this->core->getPegawaiInfo('nik', $this->core->getUserInfo('username', $_SESSION['mlite_user']))}
    bootbox.alert({
        title: "Pemberitahuan penggunaan!",
        message: "Silahkan login dengan akun non administrator (akun yang berelasi dengan modul kepegawaian)!"
    });
  {else}
    var baseURL = mlite.url + '/' + mlite.admin;
    event.preventDefault();

    var no_rawat        = $('input:text[name=no_rawat]').val();
    var tgl_perawatan   = $('input:text[name=tgl_perawatan]').val();
    var jam_rawat       = $('input:text[name=jam_rawat]').val();
    var suhu_tubuh      = $('input:text[name=suhu_tubuh]').val();
    var tensi           = $('input:text[name=tensi]').val();
    var nadi            = $('input:text[name=nadi]').val();
    var respirasi       = $('input:text[name=respirasi]').val();
    var tinggi          = $('input:text[name=tinggi]').val();
    var berat           = $('input:text[name=berat]').val();
    var spo2           = $('input:text[name=spo2]').val();
    var gcs             = $('input:text[name=gcs]').val();
    // var kesadaran       = $('input:text[name=kesadaran]').val();
    var alergi          = $('input:text[name=alergi]').val();
    var keluhan         = $('textarea[name=keluhan]').val();
    var pemeriksaan     = $('textarea[name=pemeriksaan]').val();
    var penilaian       = $('textarea[name=penilaian]').val();
    var rtl             = $('textarea[name=rtl]').val();
    var instruksi       = $('textarea[name=instruksi]').val();
    var evaluasi       = $('textarea[name=evaluasi]').val();

    var url = baseURL + '/rawat_inap/savesoap?t=' + mlite.token;
    $.post(url, {no_rawat : no_rawat,
    tgl_perawatan: tgl_perawatan,
    jam_rawat: jam_rawat,
    suhu_tubuh : suhu_tubuh,
    tensi : tensi,
    nadi : nadi,
    respirasi : respirasi,
    tinggi : tinggi,
    berat : berat,
    spo2 : spo2,
    gcs : gcs,
    // kesadaran : kesadaran,
    keluhan : keluhan,
    pemeriksaan : pemeriksaan,
    alergi : alergi,
    penilaian : penilaian,
    rtl : rtl,
    instruksi : instruksi,
    evaluasi : evaluasi
    }, function(data) {
      console.log(data);
      // tampilkan data
      $("#display").hide();
      var url = baseURL + '/rawat_inap/soap?t=' + mlite.token;
      $.post(url, {no_rawat : no_rawat,
      }, function(data) {
        // tampilkan data
        $("#soap").html(data).show();
      });
      $('input:text[name=suhu_tubuh]').val("");
      $('input:text[name=tensi]').val("");
      $('input:text[name=nadi]').val("");
      $('input:text[name=respirasi]').val("");
      $('input:text[name=tinggi]').val("");
      $('input:text[name=berat]').val("");
      $('input:text[name=spo2]').val("");
      $('input:text[name=gcs]').val("");
      // $('input:text[name=kesadaran]').val("");
      $('input:text[name=alergi]').val("");
      $('textarea[name=keluhan]').val("");
      $('textarea[name=pemeriksaan]').val("");
      $('textarea[name=penilaian]').val("");
      $('textarea[name=rtl]').val("");
      $('textarea[name=instruksi]').val("");
      $('textarea[name=evaluasi]').val("");
      $('input:text[name=tgl_perawatan]').val("{?=date('Y-m-d')?}");
      $('input:text[name=tgl_registrasi]').val("{?=date('Y-m-d')?}");
      $('input:text[name=jam_rawat]').val("{?=date('H:i:s')?}");
      $('#notif').html("<div class=\"alert alert-success alert-dismissible fade in\" role=\"alert\" style=\"border-radius:0px;margin-top:-15px;\">"+
      "Data soap telah disimpan!"+
      "<button type=\"button\" class=\"close\" data-dismiss=\"alert\" aria-label=\"Close\">&times;</button>"+
      "</div>").show();
    });
  {/if}
});

// ketika tombol hapus ditekan
$("#soap").on("click",".edit_soap", function(event){
  var baseURL = mlite.url + '/' + mlite.admin;
  event.preventDefault();
  var no_rawat        = $(this).attr("data-no_rawat");
  var tgl_perawatan   = $(this).attr("data-tgl_perawatan");
  var jam_rawat       = $(this).attr("data-jam_rawat");
  var suhu_tubuh      = $(this).attr("data-suhu_tubuh");
  var tensi           = $(this).attr("data-tensi");
  var nadi            = $(this).attr("data-nadi");
  var respirasi       = $(this).attr("data-respirasi");
  var tinggi          = $(this).attr("data-tinggi");
  var berat           = $(this).attr("data-berat");
  var spo2            = $(this).attr("data-spo2");
  var gcs             = $(this).attr("data-gcs");
  var kesadaran       = $(this).attr("data-kesadaran");
  var alergi          = $(this).attr("data-alergi");
  var keluhan         = $(this).attr("data-keluhan");
  var pemeriksaan     = $(this).attr("data-pemeriksaan");
  var penilaian       = $(this).attr("data-penilaian");
  var rtl             = $(this).attr("data-rtl");
  var instruksi       = $(this).attr("data-instruksi");
  var evaluasi       = $(this).attr("data-evaluasi");

  $('input:text[name=tgl_perawatan]').val(tgl_perawatan);
  $('input:text[name=jam_rawat]').val(jam_rawat);
  $('input:text[name=suhu_tubuh]').val(suhu_tubuh);
  $('input:text[name=tensi]').val(tensi);
  $('input:text[name=nadi]').val(nadi);
  $('input:text[name=respirasi]').val(respirasi);
  $('input:text[name=tinggi]').val(tinggi);
  $('input:text[name=berat]').val(berat);
  $('input:text[name=spo2]').val(spo2);
  $('input:text[name=gcs]').val(gcs);
  $('input:text[name=kesadaran]').val(kesadaran);
  $('input:text[name=alergi]').val(alergi);
  $('textarea[name=keluhan]').val(keluhan);
  $('textarea[name=pemeriksaan]').val(pemeriksaan);
  $('textarea[name=penilaian]').val(penilaian);
  $('textarea[name=rtl]').val(rtl);
  $('textarea[name=instruksi]').val(instruksi);
  $('textarea[name=evaluasi]').val(evaluasi);

});

$("#soap").on("click",".copy_soap", function(event){
  var baseURL = mlite.url + '/' + mlite.admin;
  event.preventDefault();
  var no_rawat        = $(this).attr("data-no_rawat");
  var suhu_tubuh      = $(this).attr("data-suhu_tubuh");
  var tensi           = $(this).attr("data-tensi");
  var nadi            = $(this).attr("data-nadi");
  var respirasi       = $(this).attr("data-respirasi");
  var tinggi          = $(this).attr("data-tinggi");
  var berat           = $(this).attr("data-berat");
  var spo2            = $(this).attr("data-spo2");
  var gcs             = $(this).attr("data-gcs");
  var kesadaran       = $(this).attr("data-kesadaran");
  var alergi          = $(this).attr("data-alergi");
  var keluhan         = $(this).attr("data-keluhan");
  var pemeriksaan     = $(this).attr("data-pemeriksaan");
  var penilaian       = $(this).attr("data-penilaian");
  var rtl             = $(this).attr("data-rtl");
  var instruksi       = $(this).attr("data-instruksi");
  var evaluasi       = $(this).attr("data-evaluasi");

  $('input:text[name=suhu_tubuh]').val(suhu_tubuh);
  $('input:text[name=tensi]').val(tensi);
  $('input:text[name=nadi]').val(nadi);
  $('input:text[name=respirasi]').val(respirasi);
  $('input:text[name=tinggi]').val(tinggi);
  $('input:text[name=berat]').val(berat);
  $('input:text[name=spo2]').val(spo2);
  $('input:text[name=gcs]').val(gcs);
  $('input:text[name=kesadaran]').val(kesadaran);
  $('input:text[name=alergi]').val(alergi);
  $('textarea[name=keluhan]').val(keluhan);
  $('textarea[name=pemeriksaan]').val(pemeriksaan);
  $('textarea[name=penilaian]').val(penilaian);
  $('textarea[name=rtl]').val(rtl);
  $('textarea[name=instruksi]').val(instruksi);
  $('textarea[name=evaluasi]').val(evaluasi);

});

// ketika tombol hapus ditekan
$("#soap").on("click",".hapus_soap", function(event){
  var baseURL = mlite.url + '/' + mlite.admin;
  event.preventDefault();
  var url = baseURL + '/rawat_inap/hapussoap?t=' + mlite.token;
  var no_rawat = $(this).attr("data-no_rawat");
  var tgl_perawatan = $(this).attr("data-tgl_perawatan");
  var jam_rawat = $(this).attr("data-jam_rawat");

  // tampilkan dialog konfirmasi
  bootbox.confirm("Apakah Anda yakin ingin menghapus data ini?", function(result){
    // ketika ditekan tombol ok
    if (result){
      // mengirimkan perintah penghapusan
      $.post(url, {
        no_rawat: no_rawat,
        tgl_perawatan: tgl_perawatan,
        jam_rawat: jam_rawat
      } ,function(data) {
        console.log(data);
        var url = baseURL + '/rawat_inap/soap?t=' + mlite.token;
        $.post(url, {no_rawat : no_rawat,
        }, function(data) {
          // tampilkan data
          $("#soap").html(data).show();
        });
        $('input:text[name=suhu_tubuh]').val("");
        $('input:text[name=tensi]').val("");
        $('input:text[name=nadi]').val("");
        $('input:text[name=respirasi]').val("");
        $('input:text[name=tinggi]').val("");
        $('input:text[name=berat]').val("");
        $('input:text[name=gcs]').val("");
        $('input:text[name=kesadaran]').val("");
        $('input:text[name=alergi]').val("");
        $('input:text[name=imun_ke]').val("");
        $('textarea[name=keluhan]').val("");
        $('textarea[name=pemeriksaan]').val("");
        $('textarea[name=penilaian]').val("");
        $('textarea[name=rtl]').val("");
        $('textarea[name=instruksi]').val("");
        $('input:text[name=tgl_perawatan]').val("{?=date('Y-m-d')?}");
        $('input:text[name=tgl_registrasi]').val("{?=date('Y-m-d')?}");
        $('input:text[name=jam_rawat]').val("{?=date('H:i:s')?}");
        $('#notif').html("<div class=\"alert alert-danger alert-dismissible fade in\" role=\"alert\" style=\"border-radius:0px;margin-top:-15px;\">"+
        "Data rincian riwayat telah dihapus!"+
        "<button type=\"button\" class=\"close\" data-dismiss=\"alert\" aria-label=\"Close\">&times;</button>"+
        "</div>").show();
      });
    }
  });
});

// tombol batal diklik
$("#form_rincian").on("click", "#selesai", function(event){
  bersih();
  $("#form_berkasdigital").hide();
  $("#form_rincian").hide();
  $("#form_soap").hide();
  $("#form").show();
  $("#display").show();
  $("#rincian").hide();
  $("#soap").hide();
  $("#berkasdigital").hide();
  $("#form_hais").hide();
  $("#form_jadwaloperasi").hide();
  $("#form_dietpasien").hide();
  $("#form_kerohanian").hide();
});

// tombol batal diklik
$("#form_soap").on("click", "#selesai_soap", function(event){
  bersih();
  $("#form_berkasdigital").hide();
  $("#form_rincian").hide();
  $("#form_soap").hide();
  $("#form").show();
  $("#display").show();
  $("#rincian").hide();
  $("#soap").hide();
  $("#berkasdigital").hide();
  $("#form_hais").hide();
  $("#form_jadwaloperasi").hide();
  $("#form_dietpasien").hide();
  $("#form_kerohanian").hide();
});

$("#form_hais").on("click", "#simpan_hais", function(event){
  var baseURL = mlite.url + '/' + mlite.admin;
  event.preventDefault();

  var no_rawat        = $('input:text[name=no_rawat]').val();
  var tanggal         = $('input:text[name=tanggal]').val();
  var kd_kamar        = $('input:text[name=kd_kamar]').val();
  var edit            = $('input:hidden[name=edit]').val();
  var DEKU            = $('select[name=DEKU]').val();
  var SPUTUM          = $('input:text[name=SPUTUM]').val();
  var DARAH           = $('input:text[name=DARAH]').val();
  var URINE           = $('input:text[name=URINE]').val();
  var ETT             = $('input:text[name=ETT]').val();
  var CVL             = $('input:text[name=CVL]').val();
  var IVL             = $('input:text[name=IVL]').val();
  var UC              = $('input:text[name=UC]').val();
  var VAP             = $('input:text[name=VAP]').val();
  var IAD             = $('input:text[name=IAD]').val();
  var PLEB            = $('input:text[name=PLEB]').val();
  var ISK             = $('input:text[name=ISK]').val();
  var ILO             = $('input:text[name=ILO]').val();
  var HAP             = $('input:text[name=HAP]').val();
  var Tinea           = $('input:text[name=Tinea]').val();
  var Scabies         = $('input:text[name=Scabies]').val();
  var ANTIBIOTIK      = $('input:text[name=ANTIBIOTIK]').val();
  
  var url = baseURL + '/rawat_inap/savehais?t=' + mlite.token;
  $.post(url, {
    no_rawat : no_rawat,
    tanggal: tanggal,
    kd_kamar: kd_kamar,
    DEKU: DEKU,
    SPUTUM: SPUTUM , 
    DARAH: DARAH,  
    URINE: URINE,  
    ETT : ETT, 
    CVL : CVL, 
    IVL : IVL, 
    UC : UC,  
    VAP : VAP,
    IAD : IAD,
    PLEB : PLEB,
    ISK : ISK,
    ILO : ILO,
    HAP : HAP,
    Tinea : Tinea,
    Scabies : Scabies,
    ANTIBIOTIK : ANTIBIOTIK,
    edit: edit
  }, function(data) {
    console.log(data);
    // tampilkan data
    var url = baseURL + '/rawat_inap/hais?t=' + mlite.token;
    $.post(url, {
      no_rawat : no_rawat,
    }, function(data) {
      // tampilkan data
      console.log(data);
      // $("#hais").html(data);
      $("#hais").html(data).show();
    });

    $('input:hidden[name=edit]').val('0');
    $('input:text[name=DEKU]').val("");
    $('input:text[name=SPUTUM]').val("");
    $('input:text[name=DARAH]').val("");
    $('input:text[name=URINE]').val("");
    $('input:text[name=ETT]').val("");
    $('input:text[name=CVL]').val("");
    $('input:text[name=IVL]').val("");
    $('input:text[name=UC]').val("");
    $('input:text[name=VAP]').val("");
    $('input:text[name=IAD]').val("");
    $('input:text[name=PLEB]').val("");
    $('input:text[name=ISK]').val("");
    $('input:text[name=ILO]').val("");
    $('input:text[name=HAP]').val("");
    $('input:text[name=Tinea]').val("");
    $('input:text[name=Scabies]').val("");
    $('input:text[name=ANTIBIOTIK]').val("");
    $('input:text[name=tanggal]').val("{?=date('Y-m-d')?}");
  
    $('#notif').html("<div class=\"alert alert-success alert-dismissible fade in\" role=\"alert\" style=\"border-radius:0px;margin-top:-15px;\">"+
    "Data HAIS telah disimpan!"+
    "<button type=\"button\" class=\"close\" data-dismiss=\"alert\" aria-label=\"Close\">&times;</button>"+
    "</div>").show();
  });
});

// ketika tombol edit ditekan
$("#hais").on("click",".edit_hais", function(event){
var baseURL = mlite.url + '/' + mlite.admin;
event.preventDefault();
var no_rawat        = $(this).attr("data-no_rawat");
var tanggal         = $(this).attr("data-tanggal");
var kd_kamar        = $(this).attr("data-kd_kamar");
var no_rkm_medis    = $(this).attr("data-no_rkm_medis");
var nm_pasien       = $(this).attr("data-nm_pasien");
var DEKU            = $(this).attr("data-DEKU");
var SPUTUM          = $(this).attr("data-SPUTUM");
var DARAH           = $(this).attr("data-DARAH");
var URINE           = $(this).attr("data-URINE");
var ETT             = $(this).attr("data-ETT");
var CVL             = $(this).attr("data-CVL");
var IVL             = $(this).attr("data-IVL");
var UC              = $(this).attr("data-UC");
var VAP             = $(this).attr("data-VAP");
var IAD             = $(this).attr("data-IAD");
var PLEB            = $(this).attr("data-PLEB");
var ISK             = $(this).attr("data-ISK");
var ILO             = $(this).attr("data-ILO");
var HAP             = $(this).attr("data-HAP");
var Tinea           = $(this).attr("data-Tinea");
var Scabies         = $(this).attr("data-Scabies");
var ANTIBIOTIK      = $(this).attr("data-ANTIBIOTIK");

$('input:hidden[name=edit]').val('1');
$('input:text[name=tanggal]').val(tanggal);
$('input:text[name=no_rawat]').val(no_rawat);
$('input:text[name=kd_kamar]').val(kd_kamar);
$('input:text[name=no_rkm_medis]').val(no_rkm_medis);
$('input:text[name=nm_pasien]').val(nm_pasien);
$('select[name=DEKU]').val(DEKU).change();
$('input:text[name=SPUTUM]').val(SPUTUM);
$('input:text[name=DARAH]').val(DARAH);
$('input:text[name=URINE]').val(URINE);
$('input:text[name=ETT]').val(ETT);
$('input:text[name=CVL]').val(CVL);
$('input:text[name=IVL]').val(IVL);
$('input:text[name=UC]').val(UC);
$('input:text[name=VAP]').val(VAP);
$('input:text[name=IAD]').val(IAD);
$('input:text[name=PLEB]').val(PLEB);
$('input:text[name=ISK]').val(ISK);
$('input:text[name=ILO]').val(ILO);
$('input:text[name=HAP]').val(HAP);
$('input:text[name=Tinea]').val(Tinea);
$('input:text[name=Scabies]').val(Scabies);
$('input:text[name=ANTIBIOTIK]').val(ANTIBIOTIK);
// alert("coba lagi"

// );
});

// ketika tombol hapus ditekan
$("#hais").on("click",".hapus_hais", function(event){
var baseURL = mlite.url + '/' + mlite.admin;
event.preventDefault();
var url = baseURL + '/rawat_inap/hapushais?t=' + mlite.token;
var no_rawat = $(this).attr("data-no_rawat");
var tanggal = $(this).attr("data-tanggal");

// tampilkan dialog konfirmasi
bootbox.confirm("Apakah Anda yakin ingin menghapus data ini?", function(result){
  // ketika ditekan tombol ok
  if (result){
    // mengirimkan perintah penghapusan
    $.post(url, {
      no_rawat: no_rawat,
      tanggal: tanggal,
    } ,function(data) {
      var url = baseURL + '/rawat_inap/hais?t=' + mlite.token;
      $.post(url, {no_rawat : no_rawat,
      }, function(data) {
        // tampilkan data
        $("#hais").html(data).show();
      });
     $('input:text[name=DEKU]').val("");
      $('input:text[name=SPUTUM]').val("");
      $('input:text[name=DARAH]').val("");
      $('input:text[name=URINE]').val("");
      $('input:text[name=ETT]').val("");
      $('input:text[name=CVL]').val("");
      $('input:text[name=IVL]').val("");
      $('input:text[name=UC]').val("");
      $('input:text[name=VAP]').val("");
      $('input:text[name=IAD]').val("");
      $('input:text[name=PLEB]').val("");
      $('input:text[name=ISK]').val("");
      $('input:text[name=ILO]').val("");
      $('input:text[name=HAP]').val("");
      $('input:text[name=Tinea]').val("");
      $('input:text[name=Scabies]').val("");
      $('input:text[name=ANTIBIOTIK]').val("");
      $('input:text[name=tanggal]').val("{?=date('Y-m-d')?}");
      $('#notif').html("<div class=\"alert alert-danger alert-dismissible fade in\" role=\"alert\" style=\"border-radius:0px;margin-top:-15px;\">"+
      "Data HAIS telah dihapus!"+
      "<button type=\"button\" class=\"close\" data-dismiss=\"alert\" aria-label=\"Close\">&times;</button>"+
      "</div>").show();
    });
  }
});
});


// tombol batal diklik
$("#form_rincian").on("click", "#selesai", function(event){
bersih();
$("#form_berkasdigital").hide();
$("#form_rincian").hide();
$("#form_soap").hide();
$("#form").show();
$("#display").show();
$("#rincian").hide();
$("#soap").hide();
$("#berkasdigital").hide();
$("#form_hais").hide();
$("#form_jadwaloperasi").hide();
$("#form_dietpasien").hide();
$("#form_kerohanian").hide();
});

// tombol batal diklik
$("#form_hais").on("click", "#selesai_hais", function(event){
bersih();
$("#form_berkasdigital").hide();
$("#form_rincian").hide();
$("#form_soap").hide();
$("#form").show();
$("#display").show();
$("#rincian").hide();
$("#soap").hide();
$("#berkasdigital").hide();
$("#form_hais").hide();
$("#hais").hide();
$("#form_jadwaloperasi").hide();
$("#jadwaloperasi").hide();
$("#form_dietpasien").hide();
$("#dietpasien").hide();
$("#form_kerohanian").hide();
$("#kerohanian").hide();
});


// ketika baris data diklik
//$("#display").on("click", ".layanan_obat", function(event){

// ketika inputbox pencarian diisi
$('input:text[name=layanan]').on('input',function(e){
  var baseURL = mlite.url + '/' + mlite.admin;
  var url    = baseURL + '/rawat_inap/layanan?t=' + mlite.token;
  var layanan = $('input:text[name=layanan]').val();

  if(layanan!="") {
      $.post(url, {layanan: layanan} ,function(data) {
      // tampilkan data yang sudah di perbaharui
        $("#layanan").html(data).show();
        $("#obat").hide();
      });
  }

});
// end pencarian

// ketika inputbox pencarian diisi
$('input:text[name=obat]').on('input',function(e){
  var baseURL = mlite.url + '/' + mlite.admin;
  var url    = baseURL + '/rawat_inap/obat?t=' + mlite.token;
  var obat = $('input:text[name=obat]').val();

  if(obat!="") {
      $.post(url, {obat: obat} ,function(data) {
      // tampilkan data yang sudah di perbaharui
        $("#obat").html(data).show();
        $("#layanan").hide();
      });
  }

});
// end pencarian

// ketika baris data diklik
$("#layanan").on("click", ".pilih_layanan", function(event){
  var baseURL = mlite.url + '/' + mlite.admin;
  event.preventDefault();

  var kd_jenis_prw = $(this).attr("data-kd_jenis_prw");
  var nm_perawatan = $(this).attr("data-nm_perawatan");
  var biaya = $(this).attr("data-biaya");
  var kat = $(this).attr("data-kat");

  $('input:hidden[name=kd_jenis_prw]').val(kd_jenis_prw);
  $('input:text[name=nm_perawatan]').val(nm_perawatan);
  $('input:text[name=biaya]').val(biaya);
  $('input:hidden[name=kat]').val(kat);

  $("#layanan").hide();
  $('#provider').show();
  $('#aturan_pakai').hide();
});

// ketika baris data diklik
$("#obat").on("click", ".pilih_obat", function(event){
  var baseURL = mlite.url + '/' + mlite.admin;
  event.preventDefault();

  var kode_brng = $(this).attr("data-kode_brng");
  var nama_brng = $(this).attr("data-nama_brng");
  var biaya = $(this).attr("data-ralan");
  var kat = $(this).attr("data-kat");

  $('input:hidden[name=kd_jenis_prw]').val(kode_brng);
  $('input:text[name=nm_perawatan]').val(nama_brng);
  $('input:text[name=biaya]').val(biaya);
  $('input:hidden[name=kat]').val(kat);

  /*$('#jumlah_jual').val(1);
  var jumlah_jual  = $('input:text[name=jumlah_jual]').val();

  $('#jumlah_jual').removeAttr("disabled");
  $('#potongan').removeAttr("disabled");
  $('#jumlah_jual').focus();

  var total = (Number(harga)) * (Number(jumlah_jual));
  $('input:text[name=total]').val(total);*/

  $('#obat').hide();
  $('#aturan_pakai').show();
  $('#rawat_jl_dr').show();
});

// ketika tombol simpan diklik
$("#form_rincian").on("click", "#simpan_rincian", function(event){
  var baseURL = mlite.url + '/' + mlite.admin;
  event.preventDefault();

  var no_rawat        = $('input:text[name=no_rawat]').val();
  var kd_jenis_prw 	  = $('input:hidden[name=kd_jenis_prw]').val();
  var provider        = $('select[name=provider]').val();
  var kode_provider   = $('input:text[name=kode_provider]').val();
  var kode_provider2   = $('input:text[name=kode_provider2]').val();
  var tgl_perawatan   = $('input:text[name=tgl_perawatan]').val();
  var jam_rawat       = $('input:text[name=jam_rawat]').val();
  var biaya           = $('input:text[name=biaya]').val();
  var aturan_pakai    = $('input:text[name=aturan_pakai]').val();
  var kat             = $('input:hidden[name=kat]').val();
  var jml             = $('input:text[name=jml]').val();

  var url = baseURL + '/rawat_inap/savedetail?t=' + mlite.token;
  $.post(url, {no_rawat : no_rawat,
  kd_jenis_prw   : kd_jenis_prw,
  provider       : provider,
  kode_provider  : kode_provider,
  kode_provider2 : kode_provider2,
  tgl_perawatan  : tgl_perawatan,
  jam_rawat      : jam_rawat,
  biaya          : biaya,
  aturan_pakai   : aturan_pakai,
  kat            : kat,
  jml            : jml
  }, function(data) {

    // tampilkan data
    $("#display").hide();
    var url = baseURL + '/rawat_inap/rincian?t=' + mlite.token;
    $.post(url, {no_rawat : no_rawat,
    }, function(data) {
      // tampilkan data
      $("#rincian").html(data).show();
    });
    $('input:hidden[name=kd_jenis_prw]').val("");
    $('input:text[name=nm_perawatan]').val("");
    $('input:hidden[name=kat]').val("");
    $('input:text[name=biaya]').val("");
    $('input:text[name=nama_provider]').val("");
    $('input:text[name=nama_provider2]').val("");
    $('input:text[name=kode_provider]').val("");
    $('input:text[name=kode_provider2]').val("");
    $('#notif').html("<div class=\"alert alert-success alert-dismissible fade in\" role=\"alert\" style=\"border-radius:0px;margin-top:-15px;\">"+
    "Data pasien telah disimpan!"+
    "<button type=\"button\" class=\"close\" data-dismiss=\"alert\" aria-label=\"Close\">&times;</button>"+
    "</div>").show();
  });
});

// ketika tombol hapus ditekan
$("#rincian").on("click",".hapus_detail", function(event){
  var baseURL = mlite.url + '/' + mlite.admin;
  event.preventDefault();
  var url = baseURL + '/rawat_inap/hapusdetail?t=' + mlite.token;
  var no_rawat = $(this).attr("data-no_rawat");
  var kd_jenis_prw = $(this).attr("data-kd_jenis_prw");
  var tgl_perawatan = $(this).attr("data-tgl_perawatan");
  var jam_rawat = $(this).attr("data-jam_rawat");
  var provider = $(this).attr("data-provider");

  // tampilkan dialog konfirmasi
  bootbox.confirm("Apakah Anda yakin ingin menghapus data ini?", function(result){
    // ketika ditekan tombol ok
    if (result){
      // mengirimkan perintah penghapusan
      $.post(url, {
        no_rawat: no_rawat,
        kd_jenis_prw: kd_jenis_prw,
        tgl_perawatan: tgl_perawatan,
        jam_rawat: jam_rawat,
        provider: provider
      } ,function(data) {
        var url = baseURL + '/rawat_inap/rincian?t=' + mlite.token;
        $.post(url, {no_rawat : no_rawat,
        }, function(data) {
          // tampilkan data
          $("#rincian").html(data).show();
        });
        $('#notif').html("<div class=\"alert alert-danger alert-dismissible fade in\" role=\"alert\" style=\"border-radius:0px;margin-top:-15px;\">"+
        "Data rincian rawat jalan telah dihapus!"+
        "<button type=\"button\" class=\"close\" data-dismiss=\"alert\" aria-label=\"Close\">&times;</button>"+
        "</div>").show();
      });
    }
  });
});

// ketika tombol hapus ditekan
$("#rincian").on("click",".hapus_resep_obat", function(event){
  var baseURL = mlite.url + '/' + mlite.admin;
  event.preventDefault();
  var url = baseURL + '/rawat_inap/hapusresep?t=' + mlite.token;
  var no_resep = $(this).attr("data-no_resep");
  var no_rawat = $(this).attr("data-no_rawat");
  var tgl_peresepan = $(this).attr("data-tgl_peresepan");
  var jam_peresepan = $(this).attr("data-jam_peresepan");

  // tampilkan dialog konfirmasi
  bootbox.confirm("Apakah Anda yakin ingin menghapus data ini?", function(result){
    // ketika ditekan tombol ok
    if (result){
      // mengirimkan perintah penghapusan
      $.post(url, {
        no_resep: no_resep,
        no_rawat: no_rawat,
        tgl_peresepan: tgl_peresepan,
        jam_peresepan: jam_peresepan
      } ,function(data) {
        var url = baseURL + '/rawat_inap/rincian?t=' + mlite.token;
        $.post(url, {no_rawat : no_rawat,
        }, function(data) {
          // tampilkan data
          $("#rincian").html(data).show();
        });
        $('#notif').html("<div class=\"alert alert-danger alert-dismissible fade in\" role=\"alert\" style=\"border-radius:0px;margin-top:-15px;\">"+
        "Data rincian rawat jalan telah dihapus!"+
        "<button type=\"button\" class=\"close\" data-dismiss=\"alert\" aria-label=\"Close\">&times;</button>"+
        "</div>").show();
      });
    }
  });
});

// ketika tombol hapus ditekan
$("#rincian").on("click",".hapus_resep_dokter", function(event){
  var baseURL = mlite.url + '/' + mlite.admin;
  event.preventDefault();
  var url = baseURL + '/rawat_inap/hapusresep?t=' + mlite.token;
  var no_resep = $(this).attr("data-no_resep");
  var no_rawat = $(this).attr("data-no_rawat");
  var kd_jenis_prw = $(this).attr("data-kd_jenis_prw");

  // tampilkan dialog konfirmasi
  bootbox.confirm("Apakah Anda yakin ingin menghapus data ini?", function(result){
    // ketika ditekan tombol ok
    if (result){
      // mengirimkan perintah penghapusan
      $.post(url, {
        no_resep: no_resep,
        no_rawat: no_rawat,
        kd_jenis_prw: kd_jenis_prw
      } ,function(data) {
        var url = baseURL + '/rawat_inap/rincian?t=' + mlite.token;
        $.post(url, {no_rawat : no_rawat,
        }, function(data) {
          // tampilkan data
          $("#rincian").html(data).show();
        });
        $('#notif').html("<div class=\"alert alert-danger alert-dismissible fade in\" role=\"alert\" style=\"border-radius:0px;margin-top:-15px;\">"+
        "Data rincian rawat jalan telah dihapus!"+
        "<button type=\"button\" class=\"close\" data-dismiss=\"alert\" aria-label=\"Close\">&times;</button>"+
        "</div>").show();
      });
    }
  });
});



//Dietpasien
// ketika tombol simpan diklik
$("#form_dietpasien").on("click", "#simpan_dietpasien", function(event){
  var baseURL = mlite.url + '/' + mlite.admin;
  event.preventDefault();

  var no_rawat      = $('input:text[name=no_rawat]').val();
  var kd_kamar      = $('input:text[name=kd_kamar]').val();
  var kd_diet       = $('input:hidden[name=kd_diet]').val();
  var tanggal       = $('input:text[name=tanggalhari]').val();
  var waktu         = $('select[name=waktu]').val();
  console.log({
    no_rawat, kd_kamar, kd_diet,
    tanggal, waktu
  });
 // return
  var url = baseURL + '/rawat_inap/savedietpasien?t=' + mlite.token;
  $.post(url, {
    no_rawat : no_rawat,
    kd_kamar: kd_kamar,
    kd_diet: kd_diet,
    tanggal: tanggal,
    waktu : waktu
  }, function(data) {
    console.log(data);
    // tampilkan data
    var url = baseURL + '/rawat_inap/dietpasien?t=' + mlite.token;
    $.post(url, {
      no_rawat : no_rawat,
    }, function(data) {
      // tampilkan data
      console.log(data);
      // $("#jadwaloperasi").html(data);
      $("#dietpasien").html(data).show();

    });

    $('input:text[name=waktu]').val("");
    $('input:text[name=nama_diet]').val("");
    $('input:text[name=tanggalhari]').val("{?=date('Y-m-d')?}");

    $('#notif').html("<div class=\"alert alert-success alert-dismissible fade in\" role=\"alert\" style=\"border-radius:0px;margin-top:-15px;\">"+
    "Data Diet Harian Pasien telah disimpan!"+
    "<button type=\"button\" class=\"close\" data-dismiss=\"alert\" aria-label=\"Close\">&times;</button>"+
    "</div>").show();
  });
});


// // ketika tombol edit ditekan
$("#dietpasien").on("click",".edit_dietpasien", function(event){
var baseURL = mlite.url + '/' + mlite.admin;
event.preventDefault();
var no_rawat        = $(this).attr("data-no_rawat");
var nm_pasien       = $(this).attr("data-nm_pasien");
var tanggal         = $(this).attr("data-tanggal");
var waktu          = $(this).attr("data-waktu");
var kd_kamar        = $(this).attr("data-kd_kamar");
var nm_penyakit     = $(this).attr("data-nm_penyakit");
var kd_diet        = $(this).attr("data-kd_diet");
var nama_diet       = $(this).attr("data-nama_diet");

$('input:text[name=no_rawat]').val(no_rawat);
$('input:text[name=nm_pasien]').val(nm_pasien);
$('input:text[name=tanggalhari]').val(tanggal);
$('select[name=waktu]').val(waktu).change();
$('input:text[name=kd_kamar]').val(kd_kamar);
$('input:text[name=nm_penyakit]').val(nm_penyakit);
$('input:hidden[name=kd_diet]').val(kd_diet);
$('input:text[name=nama_diet]').val(nama_diet);
// alert("coba lagi"

// );
});

// // ketika tombol hapus ditekan
$("#dietpasien").on("click",".hapus_dietpasien", function(event){
var baseURL = mlite.url + '/' + mlite.admin;
event.preventDefault();
var url = baseURL + '/rawat_inap/hapusdietpasien?t=' + mlite.token;
var no_rawat = $(this).attr("data-no_rawat");
var tanggal = $(this).attr("data-tanggal");
var waktu = $(this).attr("data-waktu");

// tampilkan dialog konfirmasi
bootbox.confirm("Apakah Anda yakin ingin menghapus data ini?", function(result){
  // ketika ditekan tombol ok
  if (result){
    // mengirimkan perintah penghapusan
    $.post(url, {
      no_rawat: no_rawat,
      tanggal: tanggal,
      waktu: waktu,

    } ,function(data) {
      var url = baseURL + '/rawat_inap/dietpasien?t=' + mlite.token;
      $.post(url, {no_rawat : no_rawat,
      }, function(data) {
        // tampilkan data
        $("#dietpasien").html(data).show();
      });

      $('input:text[name=waktu]').val("");
      $('input:text[name=tanggalhari]').val("{?=date('Y-m-d')?}");
      $('#notif').html("<div class=\"alert alert-danger alert-dismissible fade in\" role=\"alert\" style=\"border-radius:0px;margin-top:-15px;\">"+
      "Data Diet Pasien telah dihapus!"+
      "<button type=\"button\" class=\"close\" data-dismiss=\"alert\" aria-label=\"Close\">&times;</button>"+
      "</div>").show();
    });
  }
});
});


// tombol batal diklik
$("#form_rincian").on("click", "#selesai", function(event){
bersih();
$("#form_berkasdigital").hide();
$("#form_rincian").hide();
$("#form_soap").hide();
$("#form").show();
$("#display").show();
$("#rincian").hide();
$("#soap").hide();
$("#berkasdigital").hide();
$("#form_hais").hide();
$("#form_jadwaloperasi").hide();
$("#form_dietpasien").hide();
$("#form_kerohanian").hide();
});

// tombol batal diklik
$("#form_dietpasien").on("click", "#selesai_dietpasien", function(event){
bersih();
$("#form_berkasdigital").hide();
$("#form_rincian").hide();
$("#form_soap").hide();
$("#form").show();
$("#display").show();
$("#rincian").hide();
$("#soap").hide();
$("#berkasdigital").hide();
$("#form_hais").hide();
$("#hais").hide();
$("#form_jadwaloperasi").hide();
$("#jadwaloperasi").hide();
$("#form_dietpasien").hide();
$("#dietpasien").hide();
$("#form_kerohanian").hide();
$("#kerohanian").hide();
});

//Jadwaloperasi
// ketika tombol simpan diklik
$("#form_jadwaloperasi").on("click", "#simpan_jadwaloperasi", function(event){
  var baseURL = mlite.url + '/' + mlite.admin;
  event.preventDefault();

  var no_rawat        = $('input:text[name=no_rawat]').val();
  var kode_paket      = $('input:hidden[name=kode_paket]').val();
  var edit            = $('input:hidden[name=edit]').val();
  var kd_dokter       = $('input:hidden[name=kd_dokter]').val();
  var tanggal         = $('input:text[name=tanggal_hari]').val();
  var jam_mulai       = $('input:text[name=jam_mulai]').val();
  var jam_selesai     = $('input:text[name=jam_selesai]').val();
  var status          = $('select[name=status]').val();
 
  var url = baseURL + '/rawat_inap/savejadwaloperasi?t=' + mlite.token;
  $.post(url, {
    no_rawat : no_rawat,
    kode_paket: kode_paket,
    kd_dokter: kd_dokter,
    tanggal: tanggal,
    jam_mulai: jam_mulai,
    jam_selesai: jam_selesai,
    status: status,
    edit: edit 
  }, function(data) {
    console.log(data);
    // tampilkan data
    var url = baseURL + '/rawat_inap/jadwaloperasi?t=' + mlite.token;
    $.post(url, {
      no_rawat : no_rawat,
    }, function(data) {
      // tampilkan data
      console.log(data);
      // $("#jadwaloperasi").html(data);
      $("#jadwaloperasi").html(data).show();
    });
    $('input:hidden[name=edit]').val('0');
    $('input:text[name=jam_mulai]').val("");
    $('input:text[name=jam_selesai]').val("");
    $('input:text[name=status]').val("");
    $('input:text[name=nm_dokter]').val("");
    $('input:text[name=nm_perawatan]').val("");
    $('input:text[name=tanggal_hari]').val("{?=date('Y-m-d')?}");
  
    $('#notif').html("<div class=\"alert alert-success alert-dismissible fade in\" role=\"alert\" style=\"border-radius:0px;margin-top:-15px;\">"+
    "Data Jadwal Operasi telah disimpan!"+
    "<button type=\"button\" class=\"close\" data-dismiss=\"alert\" aria-label=\"Close\">&times;</button>"+
    "</div>").show();
  });
});

// // ketika tombol edit ditekan
$("#jadwaloperasi").on("click",".edit_jadwaloperasi", function(event){
var baseURL = mlite.url + '/' + mlite.admin;
event.preventDefault();
var no_rawat        = $(this).attr("data-no_rawat");
var nm_pasien       = $(this).attr("data-nm_pasien");
var umur            = $(this).attr("data-umur");
var jk              = $(this).attr("data-jk");
var tanggal         = $(this).attr("data-tanggal");
var jam_mulai       = $(this).attr("data-jam_mulai");
var jam_selesai     = $(this).attr("data-jam_selesai");
var status          = $(this).attr("data-status");
var kd_kamar        = $(this).attr("data-kd_kamar");
var nm_penyakit     = $(this).attr("data-nm_penyakit");
var kode_paket      = $(this).attr("data-kode_paket");
var nm_perawatan    = $(this).attr("data-nm_perawatan");
var kd_dokter       = $(this).attr("data-kd_dokter");
var nm_dokter       = $(this).attr("data-nm_dokter");

$('input:hidden[name=edit]').val('1');
$('input:text[name=no_rawat]').val(no_rawat);
$('input:text[name=nm_pasien]').val(nm_pasien);
$('input:text[name=umur]').val(umur); 
$('input:text[name=jk]').val(jk);
$('input:text[name=tanggal_hari]').val(tanggal);
$('input:text[name=jam_mulai]').val(jam_mulai);
$('input:text[name=jam_selesai]').val(jam_selesai);
$('select[name=status]').val(status).change();
$('input:text[name=kd_kamar]').val(kd_kamar);
$('input:text[name=nm_penyakit]').val(nm_penyakit);
$('input:hidden[name=kode_paket]').val(kode_paket);
$('input:text[name=nm_perawatan]').val(nm_perawatan);
$('input:hidden[name=kd_dokter]').val(kd_dokter);
$('input:text[name=nm_dokter]').val(nm_dokter);
// alert("coba lagi"

// );
});

// // ketika tombol hapus ditekan
$("#jadwaloperasi").on("click",".hapus_jadwaloperasi", function(event){
var baseURL = mlite.url + '/' + mlite.admin;
event.preventDefault();
var url = baseURL + '/rawat_inap/hapusjadwaloperasi?t=' + mlite.token;
var no_rawat = $(this).attr("data-no_rawat");
var tanggal = $(this).attr("data-tanggal");

// tampilkan dialog konfirmasi
bootbox.confirm("Apakah Anda yakin ingin menghapus data ini?", function(result){
  // ketika ditekan tombol ok
  if (result){
    // mengirimkan perintah penghapusan
    $.post(url, {
      no_rawat: no_rawat,
      tanggal: tanggal,
    } ,function(data) {
      var url = baseURL + '/rawat_inap/jadwaloperasi?t=' + mlite.token;
      $.post(url, {no_rawat : no_rawat,
      }, function(data) {
        // tampilkan data
        $("#jadwaloperasi").html(data).show();
      });
      $('input:text[name=jam_mulai]').val("");
      $('input:text[name=jam_selesai]').val("");
      $('input:text[name=status]').val("");
      $('input:text[name=tanggal_hari]').val("{?=date('Y-m-d')?}");
      $('#notif').html("<div class=\"alert alert-danger alert-dismissible fade in\" role=\"alert\" style=\"border-radius:0px;margin-top:-15px;\">"+
      "Jadwal Operasi telah dihapus!"+
      "<button type=\"button\" class=\"close\" data-dismiss=\"alert\" aria-label=\"Close\">&times;</button>"+
      "</div>").show();
    });
  }
});
});


// tombol batal diklik
$("#form_rincian").on("click", "#selesai", function(event){
bersih();
$("#form_berkasdigital").hide();
$("#form_rincian").hide();
$("#form_soap").hide();
$("#form").show();
$("#display").show();
$("#rincian").hide();
$("#soap").hide();
$("#berkasdigital").hide();
$("#form_hais").hide();
$("#form_jadwaloperasi").hide();
$("#form_dietpasien").hide();
$("#form_kerohanian").hide();
});

// tombol batal diklik
$("#form_jadwaloperasi").on("click", "#selesai_jadwaloperasi", function(event){
bersih();
$("#form_berkasdigital").hide();
$("#form_rincian").hide();
$("#form_soap").hide();
$("#form").show();
$("#display").show();
$("#rincian").hide();
$("#soap").hide();
$("#berkasdigital").hide();
$("#form_hais").hide();
$("#hais").hide();
$("#form_jadwaloperasi").hide();
$("#jadwaloperasi").hide();
$("#form_dietpasien").hide();
$("#dietpasien").hide();
$("#form_kerohanian").hide();
$("#kerohanian").hide();
});

//form kerohanian
$("#kerohanian").on("click", ".noorder", function(event){
  var baseURL = mlite.url + '/' + mlite.admin;
  event.preventDefault();
  var url = baseURL + '/rawat_inap/noroh?t=' + mlite.token;
  $.post(url, {} ,function(data) {
    // tampilkan data
    //console.log(data);
    $("#noorder").val(data);
  });
});

// ketika tombol simpan diklik
$("#kerohanian").on("click", "#simpan_kerohanian", function(event){
  var baseURL = mlite.url + '/' + mlite.admin;
  event.preventDefault();
  var no_rawat         = $('#no_rawat').val();
 //  $('input:text[name=no_rawat]').val();
  var kd_kamar        = $('input:text[name=kd_kamar]').val();
  var noorder         = $('input:text[name=noorder]').val();
  var tgl_permintaan  = $('input:text[name=tgl_permintaan]').val();
  var perujuk         = $('input:hidden[name=nip]').val();

  var kd_rh           = $("#kd_rh").val();
 //var kd_rh           = document.getElementById("kd_rh").value;
  var petugas         = $('input:text[name=petugas]').val();
  var keterangan      = $('textarea[name=keterangan]').val();
  // var datanorawat      = '';
  // alert(no_rawat);
  console.log({
    no_rawat, kd_kamar, noorder,
    tgl_permintaan, perujuk, kd_rh, keterangan
  });

  var url = baseURL + '/rawat_inap/savekerohanian?t=' + mlite.token;
  $.post(url, {
    no_rawat : no_rawat,
    kd_kamar: kd_kamar,
    noorder : noorder,
    tgl_permintaan : tgl_permintaan,
    perujuk : perujuk, 
    kd_rh : kd_rh,
    petugas : petugas,
    keterangan : keterangan,
  }, function(data) {

    var datanorawat = data;
    var url = baseURL + '/rawat_inap/formkerohanian/' + datanorawat + '?t=' + mlite.token;
    // window.location = url;
    $("#rohani").show().load(url);
    // $.post(url, {
    //   no_rawat : no_rawat,
    // }, function(data) {
    //   // tampilkan data
      // console.log(data);
    //   $("#kerohanian").html(data).show();
    // });
    $('input:text[name=no_rawat]').val(no_rawat);
    $('input:text[name=noorder]').val("");
    $('input:text[name=nama]').val("");
    $('input:text[name=keterangan]').val("");
    $("#kd_rh").val("");
    $('input:hidden[name=nip]').val("");
    $('input:text[name=tgl_permintaan]').val("{?=date('Y-m-d')?}");
    $('#notif').html("<div class=\"alert alert-success alert-dismissible fade in\" role=\"alert\" style=\"border-radius:0px;margin-top:-15px;\">"+
    "Data Permintaan Kerohanian telah disimpan!"+
    "<button type=\"button\" class=\"close\" data-dismiss=\"alert\" aria-label=\"Close\">&times;</button>"+
    "</div>").show();
  }); 
  // var no_rawat   = $('#no_rawat').val();
  // window.location = baseURL + '/rawat_inap/formkerohanian/' + no_rawat + '?t=' + mlite.token;
   //alert("coba lagi");
});

// ketika tombol edit ditekan
// $("#kerohanian").on("click",".edit_kerohanian", function(event){
//   var baseURL = mlite.url + '/' + mlite.admin;
//   event.preventDefault();
//   var no_rawat        = $(this).attr("data-no_rawat");
//   var tgl_permintaan  = $(this).attr("data-tgl_permintaan");
//   var kd_kamar        = $(this).attr("data-kd_kamar");
//   var no_rkm_medis    = $(this).attr("data-no_rkm_medis");
//   var nm_pasien       = $(this).attr("data-nm_pasien");
//   var noorder         = $(this).attr("data-noorder");
//   var perujuk         = $(this).attr("data-perujuk");
//   var nama            = $(this).attr("data-nama");
//   var kd_rh           = $(this).attr("data-kd_rh");
//   var nama_rh         = $(this).attr("data-nama_rh");
//   var keterangan      = $(this).attr("data-keterangan");
  
//   $('input:text[name=tgl_permintaan]').val(tgl_permintaan);
//   $('input:text[name=no_rawat]').val(no_rawat);
//   $('input:text[name=no_rkm_medis]').val(no_rkm_medis);
//   $('input:text[name=nm_pasien]').val(nm_pasien);
//   $('input:text[name=kd_kamar]').val(kd_kamar);
//   $('input:text[name=noorder]').val(noorder);
//   $('input:hidden[name=nip]').val(perujuk);
//   $('input:text[name=nama]').val(nama);
//   $('textarea[name=keterangan]').val(keterangan);
//   $("#kd_rh").val(kd_rh).change();
//   // $("#nama_rh").val(nama_rh).change();
//  //alert("coba lagi");
//   });
  
// ketika tombol hapus ditekan
$("#kerohanian").on("click",".hapus_kerohanian", function(event){
  var baseURL = mlite.url + '/' + mlite.admin;
event.preventDefault();
var url = baseURL + '/rawat_inap/hapuskerohanian?t=' + mlite.token;
var no_rawat = $(this).attr("data-no_rawat");
var tgl_permintaan = $(this).attr("data-tgl_permintaan");
var noorder = $(this).attr("data-noorder");
var kd_rh = $(this).attr("data-kd_rh");

// tampilkan dialog konfirmasi
bootbox.confirm("Apakah Anda yakin ingin menghapus data ini?", function(result){
  // ketika ditekan tombol ok
  if (result){
    // mengirimkan perintah penghapusan
    $.post(url, {
      no_rawat: no_rawat,
      tgl_permintaan: tgl_permintaan,
      noorder: noorder,
      kd_rh: kd_rh,

    } ,function(data) {
      var url = baseURL + '/rawat_inap/formkerohanian/' + data + '?t=' + mlite.token;
      // window.location=url;
      $.post(url, {no_rawat : no_rawat,
      }, function(data) {
        // tampilkan data
        $("#kerohanian").html(data).show();
        //document.location.href = baseURL + '/rawat_inap/manage?t=' + mlite.token;
      });
      $('input:text[name=noorder]').val("");
      $('input:text[name=tgl_permintaan]').val("{?=date('Y-m-d')?}");
      $('#notif').html("<div class=\"alert alert-danger alert-dismissible fade in\" role=\"alert\" style=\"border-radius:0px;margin-top:-15px;\">"+
      "Data telah dihapus!"+
      "<button type=\"button\" class=\"close\" data-dismiss=\"alert\" aria-label=\"Close\">&times;</button>"+
      "</div>").show();
    });
  }
 window.location.reload();
});
 // alert("coba lagi");
});
  
// tombol batal diklik
$("#form_rincian").on("click", "#selesai", function(event){
  bersih();
  $("#form_berkasdigital").hide();
  $("#form_rincian").hide();
  $("#form_soap").hide();
  $("#form").show();
  $("#display").show();
  $("#rincian").hide();
  $("#soap").hide();
  $("#berkasdigital").hide();
  $("#form_hais").hide();
  $("#form_jadwaloperasi").hide();
  $("#form_dietpasien").hide();
  $("#form_kerohanian").hide();
  });
  
  // tombol batal diklik
  $("#kerohanian").on("click", "#selesai_kerohanian", function(event){
  bersih();
  $("#form_berkasdigital").hide();
  $("#form_rincian").hide();
  $("#form_soap").hide();
  $("#form").show();
  $("#display").show();
  $("#rincian").hide();
  $("#soap").hide();
  $("#berkasdigital").hide();
  $("#form_hais").hide();
  $("#hais").hide();
  $("#form_jadwaloperasi").hide();
  $("#jadwaloperasi").hide();
  $("#form_dietpasien").hide();
  $("#dietpasien").hide();
  $("#form_kerohanian").hide();
  $("#kerohanian").hide();
 //alert("coba lagi");
  
  });

function bersih(){
  $('input:text[name=no_rawat]').val("");
  $('input:text[name=no_rkm_medis]').val("");
  $('input:text[name=nm_pasien]').val("");
  $('input:text[name=tgl_perawatan]').val("{?=date('Y-m-d')?}");
  $('input:text[name=tgl_registrasi]').val("{?=date('Y-m-d')?}");
  $('input:text[name=tgl_lahir]').val("");
  $('input:text[name=jenis_kelamin]').val("");
  $('input:text[name=alamat]').val("");
  $('input:text[name=telepon]').val("");
  $('input:text[name=pekerjaan]').val("");
  $('input:text[name=layanan]').val("");
  $('input:text[name=obat]').val("");
  $('input:text[name=nama_jenis]').val("");
  $('input:text[name=jumlah_jual]').attr("disabled", true);
  $('input:text[name=potongan]').attr("disabled", true);
  $('input:text[name=harga_jual]').val("");
  $('input:text[name=total]').val("");
  $('input:text[name=no_reg]').val("");
}

$(document).click(function (event) {
    $('.dropdown-menu[data-parent]').hide();
});
$(document).on('click', '.table-responsive [data-toggle="dropdown"]', function () {
    if ($('body').hasClass('modal-open')) {
        throw new Error("This solution is not working inside a responsive table inside a modal, you need to find out a way to calculate the modal Z-index and add it to the element")
        return true;
    }

    $buttonGroup = $(this).parent();
    if (!$buttonGroup.attr('data-attachedUl')) {
        var ts = +new Date;
        $ul = $(this).siblings('ul');
        $ul.attr('data-parent', ts);
        $buttonGroup.attr('data-attachedUl', ts);
        $(window).resize(function () {
            $ul.css('display', 'none').data('top');
        });
    } else {
        $ul = $('[data-parent=' + $buttonGroup.attr('data-attachedUl') + ']');
    }
    if (!$buttonGroup.hasClass('open')) {
        $ul.css('display', 'none');
        return;
    }
    dropDownFixPosition($(this).parent(), $ul);
    function dropDownFixPosition(button, dropdown) {
        var dropDownTop = button.offset().top + button.outerHeight();
        dropdown.css('top', dropDownTop-60 + "px");
        dropdown.css('left', button.offset().left+7 + "px");
        dropdown.css('position', "absolute");

        dropdown.css('width', dropdown.width());
        dropdown.css('heigt', dropdown.height());
        dropdown.css('display', 'block');
        dropdown.appendTo('body');
    }
});

$('body').on('hidden.bs.modal', '.modal', function () {
    $(this).removeData('bs.modal');
});
