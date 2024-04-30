$(document).ready(function () {

  var columns = [{
    title: "販売契約番号",
    data: "sales_contract_id"
  }, {
    title: "顧客ID",
    data: "customer_id"
  }, {
    title: "顧客名",
    data: "customer_name"
  }, {
    title: "登録日",
    data: "created_at",
  }

  ];

  // テーブルの日本語化
  $.extend($.fn.dataTable.defaults, {
    language: {
      url: "https://cdn.datatables.net/plug-ins/2.0.5/i18n/ja.json"
    }
  });

  myTable = $('#salesContract').DataTable({
    ajax: '/src/assets/json/salesContract.json',
    columns: columns,
    columnDefs: [
      { targets: [0, 1, 2, 3], render: $.fn.dataTable.render.text() },    //XSS対策
    ]
  });
});
