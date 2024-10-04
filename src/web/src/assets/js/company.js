$(document).ready(function () {

  var columns = [{
    title: "企業ID",
    data: "company_id"
  }, {
    title: "企業名",
    data: "company_name"
  }
  ];

  // テーブルの日本語化
  $.extend($.fn.dataTable.defaults, {
    language: {
      url: "https://cdn.datatables.net/plug-ins/2.0.5/i18n/ja.json"
    }
  });

  myTable = $('#company').DataTable({
    ajax: '/src/assets/json/company.json',
    columns: columns,
    columnDefs: [
      { targets: [0, 1], render: $.fn.dataTable.render.text() },    //XSS対策
    ]
  });
});
