$(document).ready(function () {

  var columns = [{
    title: "カテゴリー",
    data: "name"
  }
  ];

  // テーブルの日本語化
  $.extend($.fn.dataTable.defaults, {
    language: {
      url: "https://cdn.datatables.net/plug-ins/2.0.5/i18n/ja.json"
    }
  });

  myTable = $('#category').DataTable({
    ajax: '/src/assets/json/category.json',
    columns: columns,
    columnDefs: [
      { targets: 0, render: $.fn.dataTable.render.text() },    //XSS対策
      { targets: 0, className: 'dt-center' }
    ]
  });
});
