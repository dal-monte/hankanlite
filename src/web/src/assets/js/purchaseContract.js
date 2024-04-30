$(document).ready(function () {

  var columns = [{
    title: "仕入契約番号",
    data: "purchase_contract_id"
  }, {
    title: "業者ID",
    data: "supplier_id"
  }, {
    title: "業者名",
    data: "supplier_name"
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

  myTable = $('#purchaseContract').DataTable({
    ajax: '/src/assets/json/purchaseContract.json',
    columns: columns,
    columnDefs: [
      { targets: [0, 1, 2, 3], render: $.fn.dataTable.render.text() },    //XSS対策
    ]
  });
});
