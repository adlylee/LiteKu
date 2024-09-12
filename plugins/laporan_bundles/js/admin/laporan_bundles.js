$(document).ready(function(){

    $('#manage').on('click', '#submit_periode_rawat_jalan', function(event){
      var baseURL = mlite.url + '/' + mlite.admin;
      event.preventDefault();
      var url    = baseURL + '/laporan_bundles/bundlestanggal?t=' + mlite.token;
      var periode_rawat_jalan  = $('input:text[name=periode_rawat_jalan]').val();
      var periode_rawat_jalan_akhir  = $('input:text[name=periode_rawat_jalan_akhir]').val();
      var s  = $('input:text[name=s]').val();
    
      if(periode_rawat_jalan == '') {
        alert('Tanggal awal masih kosong!')
      }
      if(periode_rawat_jalan_akhir == '') {
        alert('Tanggal akhir masih kosong!')
      }
  
      var ss = decodeURI(s);
  
      var optionText = document.getElementById("bangsal").value;
      var option = optionText.toLowerCase();
      var opt = decodeURI(option);
  
      window.location.href = baseURL+'/laporan_bundles/bundlestanggal?awal='+periode_rawat_jalan+'&akhir='+periode_rawat_jalan_akhir+'&ruang='+opt+'&s='+ss+'&t=' + mlite.token;
      
      event.stopPropagation();
    
    });
      $('#manage').on('click', '#cari', function(event){
      var baseURL = mlite.url + '/' + mlite.admin;
      event.preventDefault();
      var url    = baseURL + '/laporan_bundles/bundlestanggal?t=' + mlite.token;
      var s  = $('input:text[name=s]').val();
    
      
      var optionText = document.getElementById("bangsal").value;
      var option = optionText.toLowerCase();
      var opt = decodeURI(option);
  
      window.location.href = baseURL+'/laporan_bundles/bundlestanggal?s='+s+'&ruang='+opt+'&t=' + mlite.token;
      
    
      event.stopPropagation();
    
    });
  });  

$(document).ready(function () {
    var t = $('#lapbundleshais').DataTable({
        destroy: true,
        "dom": 'Bfrtip',
        "buttons": [{
            extend: 'excel',
            footer: true
        },
            'pdf'],
        footerCallback: function (row, data, start, end, display) {
            var api = this.api();
            var intVal = function (i) {
                return typeof i === 'string' ? i.replace(/[\$,]/g, '') * 1 : typeof i === 'number' ? i : 0;
            };

            pageJumlah_pasien = api
                .column(2, { page: 'current' })
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);

            pageTothand_vap = api
                .column(3, { page: 'current' })
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);
            pageTdkhand_vap = api
                .column(4, { page: 'current' })
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);

            pageTottehniksteril_vap = api
                .column(5, { page: 'current' })
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);
            pageTdktehniksteril_vap = api
                .column(6, { page: 'current' })
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);

            pageTotapd_vap = api
                .column(7, { page: 'current' })
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);
            pageTdkapd_vap = api
                .column(8, { page: 'current' })
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);

            pageTotsedasi_vap = api
                .column(9, { page: 'current' })
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);
            pageTdksedasi_vap = api
                .column(10, { page: 'current' })
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);

            pageTothand_iadp = api
                .column(11, { page: 'current' })
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);
            pageTdkhand_iadp = api
                .column(12, { page: 'current' })
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);

            pageTotarea_iadp = api
                .column(13, { page: 'current' })
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);
            pageTdkarea_iadp = api
                .column(14, { page: 'current' })
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);

            pageTottehniksteril_iadp = api
                .column(15, { page: 'current' })
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);
            pageTdktehniksteril_iadp = api
                .column(16, { page: 'current' })
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);

            pageTotalcohol_iadp = api
                .column(17, { page: 'current' })
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);
             pageTdkalcohol_iadp = api
                .column(18, { page: 'current' })
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);

            pageTotapd_iadp = api
                .column(19, { page: 'current' })
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);
            pageTdkapd_iadp = api
                .column(20, { page: 'current' })
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);

            pageTothand_vena = api
                .column(21, { page: 'current' })
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);
             pageTdkhand_vena = api
                .column(22, { page: 'current' })
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);

            pageTotkaji_vena = api
                .column(23, { page: 'current' })
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);
            pageTdkkaji_vena = api
                .column(24, { page: 'current' })
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);

            pageTottehnik_vena = api
                .column(25, { page: 'current' })
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);
            pageTdktehnik_vena = api
                .column(26, { page: 'current' })
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);

            pageTotpetugas_vena = api
                .column(27, { page: 'current' })
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);
            pageTdkpetugas_vena = api
                .column(28, { page: 'current' })
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);

            pageTotdesinfeksi_vena = api
                .column(29, { page: 'current' })
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);
            pageTdkdesinfeksi_vena = api
                .column(30, { page: 'current' })
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);

            pageTotkaji_isk = api
                .column(31, { page: 'current' })
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);
            pageTdkkaji_isk = api
                .column(32, { page: 'current' })
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);

            pageTotpetugas_isk = api
                .column(33, { page: 'current' })
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);
            pageTdkpetugas_isk = api
                .column(34, { page: 'current' })
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);

            pageTottangan_isk = api
                .column(35, { page: 'current' })
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);
            pageTdktangan_isk = api
                .column(36, { page: 'current' })
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);

            pageTottehniksteril_isk = api
                .column(37, { page: 'current' })
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);
            pageTdktehniksteril_isk = api
                .column(38, { page: 'current' })
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);

            pageTothand_mainvap = api
                .column(39, { page: 'current' })
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);
            pageTdkhand_mainvap = api
                .column(40, { page: 'current' })
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);

            pageTotoral_mainvap = api
                .column(41, { page: 'current' })
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);
             pageTdkoral_mainvap = api
                .column(42, { page: 'current' })
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);

            pageTotmanage_mainvap = api
                .column(43, { page: 'current' })
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);
            pageTdkmanage_mainvap = api
                .column(44, { page: 'current' })
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);

            pageTotsedasi_mainvap = api
                .column(45, { page: 'current' })
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);
             pageTdksedasi_mainvap = api
                .column(46, { page: 'current' })
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);

            pageTotkepala_mainvap = api
                .column(47, { page: 'current' })
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);
            pageTdkkepala_mainvap = api
                .column(48, { page: 'current' })
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);

            pageTothand_mainiadp = api
                .column(49, { page: 'current' })
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);
            pageTdkhand_mainiadp = api
                .column(50, { page: 'current' })
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);

            pageTotdesinfeksi_mainiadp = api
                .column(51, { page: 'current' })
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);
            pageTdkdesinfeksi_mainiadp = api
                .column(52, { page: 'current' })
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);

            pageTotperawatan_mainiadp = api
                .column(53, { page: 'current' })
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);
            pageTdkperawatan_mainiadp = api
                .column(54, { page: 'current' })
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);

            pageTotdreasing_mainiadp = api
                .column(55, { page: 'current' })
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);
            pageTdkdreasing_mainiadp = api
                .column(56, { page: 'current' })
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);

            pageTotinfus_mainiadp = api
                .column(57, { page: 'current' })
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);
            pageTdkinfus_mainiadp = api
                .column(58, { page: 'current' })
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);

            pageTothand_mainvena = api
                .column(59, { page: 'current' })
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);
            pageTdkhand_mainvena = api
                .column(60, { page: 'current' })
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);

            pageTotperawatan_mainvena = api
                .column(61, { page: 'current' })
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);
            pageTdkperawatan_mainvena = api
                .column(62, { page: 'current' })
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);

            pageTotkaji_mainvena = api
                .column(63, { page: 'current' })
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);
            pageTdkkaji_mainvena = api
                .column(64, { page: 'current' })
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);

            pageTotadministrasi_mainvena = api
                .column(65, { page: 'current' })
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);
            pageTdkadministrasi_mainvena = api
                .column(66, { page: 'current' })
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);

            pageTotedukasi_mainvena = api
                .column(67, { page: 'current' })
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);
            pageTdkedukasi_mainvena = api
                .column(68, { page: 'current' })
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);

            pageTothand_mainisk = api
                .column(69, { page: 'current' })
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);
             pageTdkhand_mainisk = api
                .column(70, { page: 'current' })
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);

            pageTotkateter_mainisk = api
                .column(71, { page: 'current' })
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);
            pageTdkkateter_mainisk = api
                .column(72, { page: 'current' })
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);

            pageTotbaglantai_mainisk = api
                .column(73, { page: 'current' })
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);
            pageTdkbaglantai_mainisk = api
                .column(74, { page: 'current' })
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);

            pageTotbagrendah_mainisk = api
                .column(75, { page: 'current' })
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);
            pageTdkbagrendah_mainisk = api
                .column(76, { page: 'current' })
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);

            pageTotposisiselang_mainisk = api
                .column(77, { page: 'current' })
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);
            pageTdkposisiselang_mainisk = api
                .column(78, { page: 'current' })
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);

            pageTotlepas_mainisk = api
                .column(79, { page: 'current' })
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);
            pageTdklepas_mainisk = api
                .column(80, { page: 'current' })
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);

            pageTotmandi_idopre = api
                .column(81, { page: 'current' })
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);
             pageTdkmandi_idopre = api
                .column(82, { page: 'current' })
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);

            pageTotcukur_idopre = api
                .column(83, { page: 'current' })
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);
            pageTdkcukur_idopre = api
                .column(84, { page: 'current' })
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);

            pageTotguladarah_idopre = api
                .column(85, { page: 'current' })
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);
            pageTdkguladarah_idopre = api
                .column(86, { page: 'current' })
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);

            pageTotantibiotik_idopre = api
                .column(87, { page: 'current' })
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);
            pageTdkantibiotik_idopre = api
                .column(88, { page: 'current' })
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);

            pageTothand_idointra = api
                .column(89, { page: 'current' })
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);
            pageTdkhand_idointra = api
                .column(90, { page: 'current' })
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);

            pageTotsteril_idointra = api
                .column(91, { page: 'current' })
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);
             pageTdksteril_idointra = api
                .column(92, { page: 'current' })
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);

            pageTotantiseptic_idointra = api
                .column(93, { page: 'current' })
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);
            pageTdkantiseptic_idointra = api
                .column(94, { page: 'current' })
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);

            pageTottehnik_idointra = api
                .column(95, { page: 'current' })
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);
             pageTdktehnik_idointra = api
                .column(96, { page: 'current' })
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);

            pageTotmobile_idointra = api
                .column(97, { page: 'current' })
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);
             pageTdkmobile_idointra = api
                .column(98, { page: 'current' })
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);

            pageTotsuhu_idointra = api
                .column(99, { page: 'current' })
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);
            pageTdksuhu_idointra = api
                .column(100, { page: 'current' })
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);

            pageTotluka_idopost = api
                .column(101, { page: 'current' })
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);
            pageTdkluka_idopost = api
                .column(102, { page: 'current' })
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);

            pageTotrawat_idopost = api
                .column(103, { page: 'current' })
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);
            pageTdkrawat_idopost = api
                .column(104, { page: 'current' })
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);

            pageTotapd_idopost = api
                .column(105, { page: 'current' })
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);
            pageTdkapd_idopost = api
                .column(106, { page: 'current' })
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);

            pageTotkaji_idopost = api
                .column(107, { page: 'current' })
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);
            pageTdkkaji_idopost = api
                .column(108, { page: 'current' })
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);


            // Update footer
            $(api.column(2).footer()).html(pageJumlah_pasien);
            $(api.column(3).footer()).html(pageTothand_vap);
            $(api.column(4).footer()).html(pageTdkhand_vap);
            $(api.column(5).footer()).html(pageTottehniksteril_vap);
            $(api.column(6).footer()).html(pageTdktehniksteril_vap);
            $(api.column(7).footer()).html(pageTotapd_vap);
            $(api.column(8).footer()).html(pageTdkapd_vap);
            $(api.column(9).footer()).html(pageTotsedasi_vap);
            $(api.column(10).footer()).html(pageTdksedasi_vap);


            $(api.column(11).footer()).html(pageTothand_iadp);
            $(api.column(12).footer()).html(pageTdkhand_iadp);
            $(api.column(13).footer()).html(pageTotarea_iadp);
            $(api.column(14).footer()).html(pageTdkarea_iadp);
            $(api.column(15).footer()).html(pageTottehniksteril_iadp);
            $(api.column(16).footer()).html(pageTdktehniksteril_iadp);
            $(api.column(17).footer()).html(pageTotalcohol_iadp);
            $(api.column(18).footer()).html(pageTdkalcohol_iadp);
            $(api.column(19).footer()).html(pageTotapd_iadp);
            $(api.column(20).footer()).html(pageTdkapd_iadp);

            $(api.column(21).footer()).html(pageTothand_vena);
            $(api.column(22).footer()).html(pageTdkhand_vena);
            $(api.column(23).footer()).html(pageTotkaji_vena);
            $(api.column(24).footer()).html(pageTdkkaji_vena);
            $(api.column(25).footer()).html(pageTottehnik_vena);
            $(api.column(26).footer()).html(pageTdktehnik_vena);
            $(api.column(27).footer()).html(pageTotpetugas_vena);
            $(api.column(28).footer()).html(pageTdkpetugas_vena);
            $(api.column(29).footer()).html(pageTotdesinfeksi_vena);
            $(api.column(30).footer()).html(pageTdkdesinfeksi_vena);

            $(api.column(31).footer()).html(pageTotkaji_isk);
            $(api.column(32).footer()).html(pageTdkkaji_isk);
            $(api.column(33).footer()).html(pageTotpetugas_isk);
            $(api.column(34).footer()).html(pageTdkpetugas_isk);
            $(api.column(35).footer()).html(pageTottangan_isk);
            $(api.column(36).footer()).html(pageTdktangan_isk);
            $(api.column(37).footer()).html(pageTottehniksteril_isk);
            $(api.column(38).footer()).html(pageTdktehniksteril_isk);

            $(api.column(39).footer()).html(pageTothand_mainvap);
            $(api.column(40).footer()).html(pageTdkhand_mainvap);
            $(api.column(41).footer()).html(pageTotoral_mainvap);
            $(api.column(42).footer()).html(pageTdkoral_mainvap);
            $(api.column(43).footer()).html(pageTotmanage_mainvap);
            $(api.column(44).footer()).html(pageTdkmanage_mainvap);
            $(api.column(45).footer()).html(pageTotsedasi_mainvap);
            $(api.column(46).footer()).html(pageTdksedasi_mainvap);
            $(api.column(47).footer()).html(pageTotkepala_mainvap);
            $(api.column(48).footer()).html(pageTdkkepala_mainvap);

            $(api.column(49).footer()).html(pageTothand_mainiadp);
            $(api.column(50).footer()).html(pageTdkhand_mainiadp);
            $(api.column(51).footer()).html(pageTotdesinfeksi_mainiadp);
            $(api.column(52).footer()).html(pageTdkdesinfeksi_mainiadp);
            $(api.column(53).footer()).html(pageTotperawatan_mainiadp);
            $(api.column(54).footer()).html(pageTdkperawatan_mainiadp);
            $(api.column(55).footer()).html(pageTotdreasing_mainiadp);
            $(api.column(56).footer()).html(pageTdkdreasing_mainiadp);
            $(api.column(57).footer()).html(pageTotinfus_mainiadp);
            $(api.column(58).footer()).html(pageTdkinfus_mainiadp);

            $(api.column(59).footer()).html(pageTothand_mainvena);
            $(api.column(60).footer()).html(pageTdkhand_mainvena);
            $(api.column(61).footer()).html(pageTotperawatan_mainvena);
            $(api.column(62).footer()).html(pageTdkperawatan_mainvena);
            $(api.column(63).footer()).html(pageTotkaji_mainvena);
            $(api.column(64).footer()).html(pageTdkkaji_mainvena);
            $(api.column(65).footer()).html(pageTotadministrasi_mainvena);
            $(api.column(66).footer()).html(pageTdkadministrasi_mainvena);
            $(api.column(67).footer()).html(pageTotedukasi_mainvena);
            $(api.column(68).footer()).html(pageTotedukasi_mainvena);

            $(api.column(69).footer()).html(pageTothand_mainisk);
            $(api.column(70).footer()).html(pageTdkhand_mainisk);
            $(api.column(71).footer()).html(pageTotkateter_mainisk);
            $(api.column(72).footer()).html(pageTdkkateter_mainisk);
            $(api.column(73).footer()).html(pageTotbaglantai_mainisk);
            $(api.column(74).footer()).html(pageTdkbaglantai_mainisk);
            $(api.column(75).footer()).html(pageTotbagrendah_mainisk);
            $(api.column(76).footer()).html(pageTdkbagrendah_mainisk);
            $(api.column(77).footer()).html(pageTotposisiselang_mainisk);
            $(api.column(78).footer()).html(pageTdkposisiselang_mainisk);
            $(api.column(79).footer()).html(pageTotlepas_mainisk);
            $(api.column(80).footer()).html(pageTdklepas_mainisk);

            $(api.column(81).footer()).html(pageTotmandi_idopre);
            $(api.column(82).footer()).html(pageTdkmandi_idopre);
            $(api.column(83).footer()).html(pageTotcukur_idopre);
            $(api.column(84).footer()).html(pageTdkcukur_idopre);
            $(api.column(85).footer()).html(pageTotguladarah_idopre);
            $(api.column(86).footer()).html(pageTdkguladarah_idopre);
            $(api.column(87).footer()).html(pageTotantibiotik_idopre);
            $(api.column(88).footer()).html(pageTdkantibiotik_idopre);

            $(api.column(89).footer()).html(pageTothand_idointra);
            $(api.column(90).footer()).html(pageTdkhand_idointra);
            $(api.column(91).footer()).html(pageTotsteril_idointra);
            $(api.column(92).footer()).html(pageTdksteril_idointra);
            $(api.column(93).footer()).html(pageTotantiseptic_idointra);
            $(api.column(94).footer()).html(pageTdkantiseptic_idointra);
            $(api.column(95).footer()).html(pageTottehnik_idointra);
            $(api.column(96).footer()).html(pageTdktehnik_idointra);
            $(api.column(97).footer()).html(pageTotmobile_idointra);
            $(api.column(98).footer()).html(pageTdkmobile_idointra);
            $(api.column(99).footer()).html(pageTotsuhu_idointra);
            $(api.column(100).footer()).html(pageTdksuhu_idointra);

            $(api.column(101).footer()).html(pageTotluka_idopost);
            $(api.column(102).footer()).html(pageTdkluka_idopost);
            $(api.column(103).footer()).html(pageTotrawat_idopost);
            $(api.column(104).footer()).html(pageTdkrawat_idopost);
            $(api.column(105).footer()).html(pageTotapd_idopost);
            $(api.column(106).footer()).html(pageTdkapd_idopost);
            $(api.column(107).footer()).html(pageTotkaji_idopost);
            $(api.column(108).footer()).html(pageTdkkaji_idopost);
        },
        columnDefs: [
            {
                searchable: false,
                orderable: false,
                targets: 0,
            },
        ],
        order: [[1, 'asc']],
    });
    t.on('order.dt search.dt', function () {
        let i = 1;

        t.cells(null, 0, { search: 'applied', order: 'applied' }).every(function (cell) {
            this.data(i++);
        });
    }).draw();
});

$(document).ready(function () {
    var t = $('#lap').DataTable({
        lengthMenu: [
            [10, 25, 50, -1],
            [10, 25, 50, 'All'],
        ],
        destroy: true,
        "dom": 'lBfrtip',
        "buttons": [{ extend: 'excel', footer: true },
            'pdf'],
        footerCallback: function (row, data, start, end, display) {
            var api = this.api();
            var intVal = function (i) {
                return typeof i === 'string' ? i.replace(/[\$,]/g, '') * 1 : typeof i === 'number' ? i : 0;
            };
            jumlah_pasien = api
                .column(2)
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);

            tothand_vap = api
                .column(3)
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);

            tottehniksteril_vap = api
                .column(4)
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);

            totapd_vap = api
                .column(5)
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);

            totsedasi_vap = api
                .column(6)
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);

            tothand_iadp = api
                .column(7)
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);

            totarea_iadp = api
                .column(8)
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);

            tottehniksteril_iadp = api
                .column(9)
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);

            totalcohol_iadp = api
                .column(10)
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);

            totapd_iadp = api
                .column(11)
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);

            tothand_vena = api
                .column(12)
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);

            totkaji_vena = api
                .column(13)
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);

            tottehnik_vena = api
                .column(14)
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);

            totpetugas_vena = api
                .column(15)
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);

            totdesinfeksi_vena = api
                .column(16)
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);

            totkaji_isk = api
                .column(17)
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);

            totpetugas_isk = api
                .column(18)
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);

            tottangan_isk = api
                .column(19)
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);

            tottehniksteril_isk = api
                .column(20)
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);

            tothand_mainvap = api
                .column(21)
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);

            totoral_mainvap = api
                .column(22)
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);

            totmanage_mainvap = api
                .column(23)
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);

            totsedasi_mainvap = api
                .column(24)
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);

            totkepala_mainvap = api
                .column(25)
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);

            tothand_mainiadp = api
                .column(26)
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);

            totdesinfeksi_mainiadp = api
                .column(27)
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);

            totperawatan_mainiadp = api
                .column(28)
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);

            totdreasing_mainiadp = api
                .column(29)
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);

            totinfus_mainiadp = api
                .column(30)
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);

            tothand_mainvena = api
                .column(31)
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);

            totperawatan_mainvena = api
                .column(32)
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);

            totkaji_mainvena = api
                .column(33)
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);

            totadministrasi_mainvena = api
                .column(34)
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);

            totedukasi_mainvena = api
                .column(35)
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);

            tothand_mainisk = api
                .column(36)
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);

            totkateter_mainisk = api
                .column(37)
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);

            totbaglantai_mainisk = api
                .column(38)
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);

            totbagrendah_mainisk = api
                .column(39)
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);

            totposisiselang_mainisk = api
                .column(40)
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);

            totlepas_mainisk = api
                .column(41)
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);

            totmandi_idopre = api
                .column(42)
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);

            totcukur_idopre = api
                .column(43)
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);

            totguladarah_idopre = api
                .column(44)
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);

            totantibiotik_idopre = api
                .column(45)
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);

            tothand_idointra = api
                .column(46)
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);

            totsteril_idointra = api
                .column(47)
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);

            totantiseptic_idointra = api
                .column(48)
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);

            tottehnik_idointra = api
                .column(49)
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);

            totmobile_idointra = api
                .column(50)
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);

            totsuhu_idointra = api
                .column(51)
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);

            totluka_idopost = api
                .column(52)
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);

            totrawat_idopost = api
                .column(53)
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);

            totapd_idopost = api
                .column(54)
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);

            totkaji_idopost = api
                .column(55)
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);

           pageJumlah_pasien = api
                .column(2, { page: 'current' })
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);

            pageTothand_vap = api
                .column(3, { page: 'current' })
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);
            pageTdkhand_vap = api
                .column(4, { page: 'current' })
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);

            pageTottehniksteril_vap = api
                .column(5, { page: 'current' })
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);
            pageTdktehniksteril_vap = api
                .column(6, { page: 'current' })
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);

            pageTotapd_vap = api
                .column(7, { page: 'current' })
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);
            pageTdkapd_vap = api
                .column(8, { page: 'current' })
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);

            pageTotsedasi_vap = api
                .column(9, { page: 'current' })
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);
            pageTdksedasi_vap = api
                .column(10, { page: 'current' })
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);

            pageTothand_iadp = api
                .column(11, { page: 'current' })
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);
            pageTdkhand_iadp = api
                .column(12, { page: 'current' })
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);

            pageTotarea_iadp = api
                .column(13, { page: 'current' })
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);
            pageTdkarea_iadp = api
                .column(14, { page: 'current' })
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);

            pageTottehniksteril_iadp = api
                .column(15, { page: 'current' })
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);
            pageTdktehniksteril_iadp = api
                .column(16, { page: 'current' })
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);

            pageTotalcohol_iadp = api
                .column(17, { page: 'current' })
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);
             pageTdkalcohol_iadp = api
                .column(18, { page: 'current' })
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);

            pageTotapd_iadp = api
                .column(19, { page: 'current' })
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);
            pageTdkapd_iadp = api
                .column(20, { page: 'current' })
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);

            pageTothand_vena = api
                .column(21, { page: 'current' })
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);
             pageTdkhand_vena = api
                .column(22, { page: 'current' })
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);

            pageTotkaji_vena = api
                .column(23, { page: 'current' })
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);
            pageTdkkaji_vena = api
                .column(24, { page: 'current' })
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);

            pageTottehnik_vena = api
                .column(25, { page: 'current' })
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);
            pageTdktehnik_vena = api
                .column(26, { page: 'current' })
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);

            pageTotpetugas_vena = api
                .column(27, { page: 'current' })
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);
            pageTdkpetugas_vena = api
                .column(28, { page: 'current' })
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);

            pageTotdesinfeksi_vena = api
                .column(29, { page: 'current' })
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);
            pageTdkdesinfeksi_vena = api
                .column(30, { page: 'current' })
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);

            pageTotkaji_isk = api
                .column(31, { page: 'current' })
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);
            pageTdkkaji_isk = api
                .column(32, { page: 'current' })
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);

            pageTotpetugas_isk = api
                .column(33, { page: 'current' })
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);
            pageTdkpetugas_isk = api
                .column(34, { page: 'current' })
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);

            pageTottangan_isk = api
                .column(35, { page: 'current' })
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);
            pageTdktangan_isk = api
                .column(36, { page: 'current' })
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);

            pageTottehniksteril_isk = api
                .column(37, { page: 'current' })
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);
            pageTdktehniksteril_isk = api
                .column(38, { page: 'current' })
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);

            pageTothand_mainvap = api
                .column(39, { page: 'current' })
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);
            pageTdkhand_mainvap = api
                .column(40, { page: 'current' })
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);

            pageTotoral_mainvap = api
                .column(41, { page: 'current' })
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);
             pageTdkoral_mainvap = api
                .column(42, { page: 'current' })
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);

            pageTotmanage_mainvap = api
                .column(43, { page: 'current' })
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);
            pageTdkmanage_mainvap = api
                .column(44, { page: 'current' })
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);

            pageTotsedasi_mainvap = api
                .column(45, { page: 'current' })
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);
             pageTdksedasi_mainvap = api
                .column(46, { page: 'current' })
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);

            pageTotkepala_mainvap = api
                .column(47, { page: 'current' })
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);
            pageTdkkepala_mainvap = api
                .column(48, { page: 'current' })
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);

            pageTothand_mainiadp = api
                .column(49, { page: 'current' })
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);
            pageTdkhand_mainiadp = api
                .column(50, { page: 'current' })
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);

            pageTotdesinfeksi_mainiadp = api
                .column(51, { page: 'current' })
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);
            pageTdkdesinfeksi_mainiadp = api
                .column(52, { page: 'current' })
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);

            pageTotperawatan_mainiadp = api
                .column(53, { page: 'current' })
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);
            pageTdkperawatan_mainiadp = api
                .column(54, { page: 'current' })
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);

            pageTotdreasing_mainiadp = api
                .column(55, { page: 'current' })
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);
            pageTdkdreasing_mainiadp = api
                .column(56, { page: 'current' })
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);

            pageTotinfus_mainiadp = api
                .column(57, { page: 'current' })
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);
            pageTdkinfus_mainiadp = api
                .column(58, { page: 'current' })
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);

            pageTothand_mainvena = api
                .column(59, { page: 'current' })
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);
            pageTdkhand_mainvena = api
                .column(60, { page: 'current' })
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);

            pageTotperawatan_mainvena = api
                .column(61, { page: 'current' })
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);
            pageTdkperawatan_mainvena = api
                .column(62, { page: 'current' })
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);

            pageTotkaji_mainvena = api
                .column(63, { page: 'current' })
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);
            pageTdkkaji_mainvena = api
                .column(64, { page: 'current' })
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);

            pageTotadministrasi_mainvena = api
                .column(65, { page: 'current' })
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);
            pageTdkadministrasi_mainvena = api
                .column(66, { page: 'current' })
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);

            pageTotedukasi_mainvena = api
                .column(67, { page: 'current' })
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);
            pageTdkedukasi_mainvena = api
                .column(68, { page: 'current' })
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);

            pageTothand_mainisk = api
                .column(69, { page: 'current' })
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);
             pageTdkhand_mainisk = api
                .column(70, { page: 'current' })
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);

            pageTotkateter_mainisk = api
                .column(71, { page: 'current' })
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);
            pageTdkkateter_mainisk = api
                .column(72, { page: 'current' })
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);

            pageTotbaglantai_mainisk = api
                .column(73, { page: 'current' })
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);
            pageTdkbaglantai_mainisk = api
                .column(74, { page: 'current' })
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);

            pageTotbagrendah_mainisk = api
                .column(75, { page: 'current' })
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);
            pageTdkbagrendah_mainisk = api
                .column(76, { page: 'current' })
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);

            pageTotposisiselang_mainisk = api
                .column(77, { page: 'current' })
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);
            pageTdkposisiselang_mainisk = api
                .column(78, { page: 'current' })
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);

            pageTotlepas_mainisk = api
                .column(79, { page: 'current' })
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);
            pageTdklepas_mainisk = api
                .column(80, { page: 'current' })
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);

            pageTotmandi_idopre = api
                .column(81, { page: 'current' })
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);
             pageTdkmandi_idopre = api
                .column(82, { page: 'current' })
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);

            pageTotcukur_idopre = api
                .column(83, { page: 'current' })
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);
            pageTdkcukur_idopre = api
                .column(84, { page: 'current' })
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);

            pageTotguladarah_idopre = api
                .column(85, { page: 'current' })
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);
            pageTdkguladarah_idopre = api
                .column(86, { page: 'current' })
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);

            pageTotantibiotik_idopre = api
                .column(87, { page: 'current' })
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);
            pageTdkantibiotik_idopre = api
                .column(88, { page: 'current' })
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);

            pageTothand_idointra = api
                .column(89, { page: 'current' })
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);
            pageTdkhand_idointra = api
                .column(90, { page: 'current' })
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);

            pageTotsteril_idointra = api
                .column(91, { page: 'current' })
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);
             pageTdksteril_idointra = api
                .column(92, { page: 'current' })
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);

            pageTotantiseptic_idointra = api
                .column(93, { page: 'current' })
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);
            pageTdkantiseptic_idointra = api
                .column(94, { page: 'current' })
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);

            pageTottehnik_idointra = api
                .column(95, { page: 'current' })
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);
             pageTdktehnik_idointra = api
                .column(96, { page: 'current' })
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);

            pageTotmobile_idointra = api
                .column(97, { page: 'current' })
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);
             pageTdkmobile_idointra = api
                .column(98, { page: 'current' })
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);

            pageTotsuhu_idointra = api
                .column(99, { page: 'current' })
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);
            pageTdksuhu_idointra = api
                .column(100, { page: 'current' })
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);

            pageTotluka_idopost = api
                .column(101, { page: 'current' })
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);
            pageTdkluka_idopost = api
                .column(102, { page: 'current' })
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);

            pageTotrawat_idopost = api
                .column(103, { page: 'current' })
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);
            pageTdkrawat_idopost = api
                .column(104, { page: 'current' })
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);

            pageTotapd_idopost = api
                .column(105, { page: 'current' })
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);
            pageTdkapd_idopost = api
                .column(106, { page: 'current' })
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);

            pageTotkaji_idopost = api
                .column(107, { page: 'current' })
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);
            pageTdkkaji_idopost = api
                .column(108, { page: 'current' })
                .data()
                .reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);


            // Update footer
            $(api.column(2).footer()).html(pageJumlah_pasien);
            $(api.column(3).footer()).html(pageTothand_vap);
            $(api.column(4).footer()).html(pageTdkhand_vap);
            $(api.column(5).footer()).html(pageTottehniksteril_vap);
            $(api.column(6).footer()).html(pageTdktehniksteril_vap);
            $(api.column(7).footer()).html(pageTotapd_vap);
            $(api.column(8).footer()).html(pageTdkapd_vap);
            $(api.column(9).footer()).html(pageTotsedasi_vap);
            $(api.column(10).footer()).html(pageTdksedasi_vap);


            $(api.column(11).footer()).html(pageTothand_iadp);
            $(api.column(12).footer()).html(pageTdkhand_iadp);
            $(api.column(13).footer()).html(pageTotarea_iadp);
            $(api.column(14).footer()).html(pageTdkarea_iadp);
            $(api.column(15).footer()).html(pageTottehniksteril_iadp);
            $(api.column(16).footer()).html(pageTdktehniksteril_iadp);
            $(api.column(17).footer()).html(pageTotalcohol_iadp);
            $(api.column(18).footer()).html(pageTdkalcohol_iadp);
            $(api.column(19).footer()).html(pageTotapd_iadp);
            $(api.column(20).footer()).html(pageTdkapd_iadp);

            $(api.column(21).footer()).html(pageTothand_vena);
            $(api.column(22).footer()).html(pageTdkhand_vena);
            $(api.column(23).footer()).html(pageTotkaji_vena);
            $(api.column(24).footer()).html(pageTdkkaji_vena);
            $(api.column(25).footer()).html(pageTottehnik_vena);
            $(api.column(26).footer()).html(pageTdktehnik_vena);
            $(api.column(27).footer()).html(pageTotpetugas_vena);
            $(api.column(28).footer()).html(pageTdkpetugas_vena);
            $(api.column(29).footer()).html(pageTotdesinfeksi_vena);
            $(api.column(30).footer()).html(pageTdkdesinfeksi_vena);

            $(api.column(31).footer()).html(pageTotkaji_isk);
            $(api.column(32).footer()).html(pageTdkkaji_isk);
            $(api.column(33).footer()).html(pageTotpetugas_isk);
            $(api.column(34).footer()).html(pageTdkpetugas_isk);
            $(api.column(35).footer()).html(pageTottangan_isk);
            $(api.column(36).footer()).html(pageTdktangan_isk);
            $(api.column(37).footer()).html(pageTottehniksteril_isk);
            $(api.column(38).footer()).html(pageTdktehniksteril_isk);

            $(api.column(39).footer()).html(pageTothand_mainvap);
            $(api.column(40).footer()).html(pageTdkhand_mainvap);
            $(api.column(41).footer()).html(pageTotoral_mainvap);
            $(api.column(42).footer()).html(pageTdkoral_mainvap);
            $(api.column(43).footer()).html(pageTotmanage_mainvap);
            $(api.column(44).footer()).html(pageTdkmanage_mainvap);
            $(api.column(45).footer()).html(pageTotsedasi_mainvap);
            $(api.column(46).footer()).html(pageTdksedasi_mainvap);
            $(api.column(47).footer()).html(pageTotkepala_mainvap);
            $(api.column(48).footer()).html(pageTdkkepala_mainvap);

            $(api.column(49).footer()).html(pageTothand_mainiadp);
            $(api.column(50).footer()).html(pageTdkhand_mainiadp);
            $(api.column(51).footer()).html(pageTotdesinfeksi_mainiadp);
            $(api.column(52).footer()).html(pageTdkdesinfeksi_mainiadp);
            $(api.column(53).footer()).html(pageTotperawatan_mainiadp);
            $(api.column(54).footer()).html(pageTdkperawatan_mainiadp);
            $(api.column(55).footer()).html(pageTotdreasing_mainiadp);
            $(api.column(56).footer()).html(pageTdkdreasing_mainiadp);
            $(api.column(57).footer()).html(pageTotinfus_mainiadp);
            $(api.column(58).footer()).html(pageTdkinfus_mainiadp);

            $(api.column(59).footer()).html(pageTothand_mainvena);
            $(api.column(60).footer()).html(pageTdkhand_mainvena);
            $(api.column(61).footer()).html(pageTotperawatan_mainvena);
            $(api.column(62).footer()).html(pageTdkperawatan_mainvena);
            $(api.column(63).footer()).html(pageTotkaji_mainvena);
            $(api.column(64).footer()).html(pageTdkkaji_mainvena);
            $(api.column(65).footer()).html(pageTotadministrasi_mainvena);
            $(api.column(66).footer()).html(pageTdkadministrasi_mainvena);
            $(api.column(67).footer()).html(pageTotedukasi_mainvena);
            $(api.column(68).footer()).html(pageTotedukasi_mainvena);

            $(api.column(69).footer()).html(pageTothand_mainisk);
            $(api.column(70).footer()).html(pageTdkhand_mainisk);
            $(api.column(71).footer()).html(pageTotkateter_mainisk);
            $(api.column(72).footer()).html(pageTdkkateter_mainisk);
            $(api.column(73).footer()).html(pageTotbaglantai_mainisk);
            $(api.column(74).footer()).html(pageTdkbaglantai_mainisk);
            $(api.column(75).footer()).html(pageTotbagrendah_mainisk);
            $(api.column(76).footer()).html(pageTdkbagrendah_mainisk);
            $(api.column(77).footer()).html(pageTotposisiselang_mainisk);
            $(api.column(78).footer()).html(pageTdkposisiselang_mainisk);
            $(api.column(79).footer()).html(pageTotlepas_mainisk);
            $(api.column(80).footer()).html(pageTdklepas_mainisk);

            $(api.column(81).footer()).html(pageTotmandi_idopre);
            $(api.column(82).footer()).html(pageTdkmandi_idopre);
            $(api.column(83).footer()).html(pageTotcukur_idopre);
            $(api.column(84).footer()).html(pageTdkcukur_idopre);
            $(api.column(85).footer()).html(pageTotguladarah_idopre);
            $(api.column(86).footer()).html(pageTdkguladarah_idopre);
            $(api.column(87).footer()).html(pageTotantibiotik_idopre);
            $(api.column(88).footer()).html(pageTdkantibiotik_idopre);

            $(api.column(89).footer()).html(pageTothand_idointra);
            $(api.column(90).footer()).html(pageTdkhand_idointra);
            $(api.column(91).footer()).html(pageTotsteril_idointra);
            $(api.column(92).footer()).html(pageTdksteril_idointra);
            $(api.column(93).footer()).html(pageTotantiseptic_idointra);
            $(api.column(94).footer()).html(pageTdkantiseptic_idointra);
            $(api.column(95).footer()).html(pageTottehnik_idointra);
            $(api.column(96).footer()).html(pageTdktehnik_idointra);
            $(api.column(97).footer()).html(pageTotmobile_idointra);
            $(api.column(98).footer()).html(pageTdkmobile_idointra);
            $(api.column(99).footer()).html(pageTotsuhu_idointra);
            $(api.column(100).footer()).html(pageTdksuhu_idointra);

            $(api.column(101).footer()).html(pageTotluka_idopost);
            $(api.column(102).footer()).html(pageTdkluka_idopost);
            $(api.column(103).footer()).html(pageTotrawat_idopost);
            $(api.column(104).footer()).html(pageTdkrawat_idopost);
            $(api.column(105).footer()).html(pageTotapd_idopost);
            $(api.column(106).footer()).html(pageTdkapd_idopost);
            $(api.column(107).footer()).html(pageTotkaji_idopost);
            $(api.column(108).footer()).html(pageTdkkaji_idopost);
        },
        columnDefs: [
            {
                searchable: false,
                orderable: false,
                targets: 0,
            },
        ],
        order: [[1, 'asc']],
    });
    t.on('order.dt search.dt', function () {
        let i = 1;

        t.cells(null, 0, { search: 'applied', order: 'applied' }).every(function (cell) {
            this.data(i++);
        });
    }).draw();
});