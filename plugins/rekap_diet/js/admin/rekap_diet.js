$(document).ready(function(){

  $('#manage').on('click', '#submit_periode_rawat_jalan', function(event){
    var baseURL = mlite.url + '/' + mlite.admin;
    event.preventDefault();
    var url    = baseURL + '/presensi/rekap_presensi?t=' + mlite.token;
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

    window.location.href = baseURL+'/rekap_diet/rekap_dietpasien?awal='+periode_rawat_jalan+'&akhir='+periode_rawat_jalan_akhir+'&ruang='+opt+'&s='+ss+'&t=' + mlite.token;
    
    event.stopPropagation();
  
  });
  $('#manage').on('click', '#cari', function(event){
    var baseURL = mlite.url + '/' + mlite.admin;
    event.preventDefault();
    var url    = baseURL + '/rekap_diet/rekap_dietpasien?t=' + mlite.token;
    var s  = $('input:text[name=s]').val();
  
    
    var optionText = document.getElementById("bangsal").value;
    var option = optionText.toLowerCase();
    var opt = decodeURI(option);

    window.location.href = baseURL+'/rekap_diet/rekap_dietpasien?s='+s+'&ruang='+opt+'&t=' + mlite.token;
    
  
    event.stopPropagation();
  
  });
})

 
   $(function () {
       $('.periode_rawat_jalan').datetimepicker({
         defaultDate: new Date(),
         format: 'YYYY-MM-DD',
         locale: 'id'
       });
   });

$(document).ready(function () {
  $("#pardah").DataTable({
    dom: "Bfrtip",
    order: [[0, "asc"]],
  });
});

$("#form").hide();
$('select').selectator();

// tombol buka form diklik
$("#index").on('click', '#bukaform', function(){
  var baseURL = mlite.url + '/' + mlite.admin;
  event.preventDefault();
  $("#form").show().load(baseURL + '/rekap_diet/form?t=' + mlite.token);
  $("#bukaform").val("Tutup Form");
  $("#bukaform").attr("id", "tutupform");
});

// tombol tutup form diklik
$("#index").on('click', '#tutupform', function(){
  event.preventDefault();
  $("#form").hide();
  $("#tutupform").val("Tambah Item Diet");
  $("#tutupform").attr("id", "bukaform");
});

$("#index").on('click', '#bukaform', function(){
  var baseURL = mlite.url + '/' + mlite.admin;
  event.preventDefault();
  var url = baseURL + '/rekap_diet/kodediet?t=' + mlite.token;
  $.get(url ,function(data) {
    // tampilkan data
    //console.log(data);
    $("#kd_diet").val(data);
  });
});

// $("#form").on("click", ".kd_diet", function(event){
//   var baseURL = mlite.url + '/' + mlite.admin;
//   event.preventDefault();
//   var url = baseURL + '/rekap_diet/kodediet?t=' + mlite.token;
//   $.get(url ,function(data) {
//     // tampilkan data
//     //console.log(data);
//     $("#kd_diet").val(data);
//   });
// });

$("#form").on("click",".batal",function(event){
    event.preventDefault();
    $("#form").hide();
    $("#tutupform").val("Tambah Item Diet");
    $("#tutupform").attr("kd_diet", "bukaform");
    $("input:text[name=kd_diet]").val("");
    $("input:text[name=nama_diet]").val("");
});

$("#display").on("click",".edititem",function(event){
    event.preventDefault();
    $("#form").show();
    $("#bukaform").val("Tutup Form");
    $("#bukaform").attr("id", "tutupform");
    var nama_diet = $(this).attr("data-nama_diet");
    var kd_diet = $(this).attr("data-kd_diet");


    // $('input:hidden[name=edit]').val('1');
    $("input:text[name=nama_diet]").val(nama_diet);
    $("input:text[name=kd_diet]").val(kd_diet);

    // document.getElementById("kd_diet").value = kd_diet;
});