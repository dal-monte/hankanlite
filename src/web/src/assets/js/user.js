$(document).ready(function () {

  var columns = [{
    title: "社員番号",
    data: "user_id"
  }, {
    title: "社員名",
    data: "user_name"
  }, {
    title: "役割",
    data: "role"
  }
  ];

  // テーブルの日本語化
  $.extend($.fn.dataTable.defaults, {
    language: {
      url: "https://cdn.datatables.net/plug-ins/2.0.5/i18n/ja.json"
    }
  });

  myTable = $('#user').DataTable({
    ajax: '/src/assets/json/user.json',
    columns: columns,
    columnDefs: [
      { targets: [0, 1, 2], render: $.fn.dataTable.render.text() },    //XSS対策
    ]
  });
});
