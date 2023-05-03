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
    var url    = baseURL + '/data_nonmedis/rekap_dietpasien?t=' + mlite.token;
    var s  = $('input:text[name=s]').val();
  
    
    var optionText = document.getElementById("bangsal").value;
    var option = optionText.toLowerCase();
    var opt = decodeURI(option);

    window.location.href = baseURL+'/data_nonmedis/rekap_dietpasien?s='+s+'&ruang='+opt+'&t=' + mlite.token;
    
  
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
  var date = new Date();
  var tahun = date.getFullYear();
  $("#nonmedis").DataTable({
    dom: "Bfrtip",
     "pageLength": 50,
    // order: [[0, "asc"]],
    "columnDefs": [
    { "visible": false, "targets": 0 }
  ],
  "buttons": [
            {
                extend: 'excel',
                title: "Laporan Stock Opname Persediaan Bulan Januari - Desember " + tahun,
                messageTop: 'RSUD H.Damanhuri Barabai Kabupaten Hulu Sungai Tengah',
                filename: "Laporan Stock Opname Persediaan Bulan Januari - Desember " + tahun,
                exportOptions: {
          columns: [ 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15],
        },
            }
    ],
  "language": {
            searchPanes: {
                emptyPanes: 'There are no panes to display. :/'
            }
        }
  });
});


$("#form").hide();
$('select').selectator();

// tombol buka form diklik
$("#index").on('click', '#bukaform', function(){
  var baseURL = mlite.url + '/' + mlite.admin;
  event.preventDefault();
  $("#form").show().load(baseURL + '/data_nonmedis/form?t=' + mlite.token);
  $("#bukaform").val("Tutup Form");
  $("#bukaform").attr("id", "tutupform");
});

// tombol tutup form diklik
$("#index").on('click', '#tutupform', function(){
  event.preventDefault();
  $("#form").hide();
  $("#tutupform").val("Upload File Excel");
  $("#tutupform").attr("id", "bukaform");
});


$("#form").on("click",".batal",function(event){
    event.preventDefault();
    $("#form").hide();
    $("#tutupform").val("Upload File Excel");
    $("#tutupform").attr("id", "bukaform");
});


