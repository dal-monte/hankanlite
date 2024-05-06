$(document).ready(function () {

  var columns = [{
    title: "カテゴリー",
    data: "category_name"
  }, {
    title: "商品名",
    data: "product_name"
  }, {
    title: "定価",
    data: "list_price"
  }, {
    title: "在庫数",
    data: "quantity"
  }
  ];

  // テーブルの日本語化
  $.extend($.fn.dataTable.defaults, {
    language: {
      url: "https://cdn.datatables.net/plug-ins/2.0.5/i18n/ja.json"
    }
  });

  myTable = $('#product').DataTable({
    ajax: '/src/assets/json/product.json',
    columns: columns,
    columnDefs: [
      { targets: [0, 1, 3], render: $.fn.dataTable.render.text() },    //XSS対策
      { targets: 2, render: $.fn.dataTable.render.number(',', '.', 0, '', '円') }
    ]
  });

});
