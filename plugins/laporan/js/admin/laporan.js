$('body').on('change','#kd_dokter', function() {
     var optionText = $("#kd_dokter option:selected").text();
     $('#nm_dokter').val(optionText);
});
$('body').on('change','#nip', function() {
     var optionText = $("#nip option:selected").text();
     $('#nama').val(optionText);
});

$('#manage').on('click', '#submit_diagnosa', function(event){
  var baseURL = mlite.url + '/' + mlite.admin;
  event.preventDefault();
  var url    = baseURL + '/laporan/riwayatdiagnosa?t=' + mlite.token;
  var periode_rawat_jalan  = $('input:text[name=tanggal]').val();
  var periode_rawat_jalan_akhir  = $('input:text[name=tanggal_akhir]').val();
  var s  = $('input:text[name=s]').val();

  if(periode_rawat_jalan == '') {
      alert('Tanggal awal masih kosong!')
  }
  if(periode_rawat_jalan_akhir == '') {
       alert('Tanggal akhir masih kosong!')
  }
  var ss = decodeURI(s);
  var optionText = document.getElementById("status").value;
  var option = optionText.toLowerCase();
  var opt = decodeURI(option);

  window.location.href = baseURL+'/laporan/riwayatdiagnosa?awal='+periode_rawat_jalan+'&akhir='+periode_rawat_jalan_akhir+'&status='+opt+'&s='+ss+'&t=' + mlite.token;   
  event.stopPropagation();
});

$('#manage').on('click', '#submit_prosedur', function(event){
  var baseURL = mlite.url + '/' + mlite.admin;
  event.preventDefault();
  var url    = baseURL + '/laporan/riwayatprosedur?t=' + mlite.token;
  var periode_rawat_jalan  = $('input:text[name=tanggal]').val();
  var periode_rawat_jalan_akhir  = $('input:text[name=tanggal_akhir]').val();
  var s  = $('input:text[name=s]').val();
   
  if(periode_rawat_jalan == '') {
     alert('Tanggal awal masih kosong!')
  }
  if(periode_rawat_jalan_akhir == '') {
    alert('Tanggal akhir masih kosong!')
  }

  var ss = decodeURI(s);
  var optionText = document.getElementById("status").value;
  var option = optionText.toLowerCase();
  var opt = decodeURI(option);
 
  window.location.href = baseURL+'/laporan/riwayatprosedur?awal='+periode_rawat_jalan+'&akhir='+periode_rawat_jalan_akhir+'&status='+opt+'&s='+ss+'&t=' + mlite.token;   
  event.stopPropagation();
   
});