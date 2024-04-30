$(document).ready(function () {

  var columns = [{
    title: "カテゴリー",
    data: "category_name"
  }, {
    title: "商品名",
    data: "product_name"
  }, {
    title: "個数",
    data: "number"
  }, {
    title: "単価(税抜)",
    data: "price"
  }, {
    title: "小計(税抜)",
    data: "subtotal"
  }
  ];

  // テーブルの日本語化
  $.extend($.fn.dataTable.defaults, {
    language: {
      url: "https://cdn.datatables.net/plug-ins/2.0.5/i18n/ja.json"
    }
  });

  myTable = $('#contract').DataTable({
    ajax: { url: "/src/assets/json/salesProduct.json", dataSrc: userId },
    columns: columns,
    columnDefs: [
      { targets: [0, 1, 2], render: $.fn.dataTable.render.text() },    //XSS対策
      { targets: 3, render: $.fn.dataTable.render.number(',', '.', 0, '', '円') },
      { targets: 4, render: $.fn.dataTable.render.number(',', '.', 0, '', '円') },
    ],
    lengthChange: false,        // 件数切替機能 無効
    searching: false,        // 検索機能 無効
    info: false,    // 情報表示 無効
    paging: false,        // ページング機能 無効
    "footerCallback": function (row, data, start, end, display) {
      const plusTax = (taxRate + 1);
      var api = this.api(), data;

      // 合計用の整数データを取得するためのフォーマットを削除する
      var intVal = function (i) {
        return typeof i === 'string' ?
          i.replace(/[\$,]/g, '') * 1 :
          typeof i === 'number' ?
            i : 0;
      };


      // テーブルの合計値を求める
      total = api
        .column(4)
        .data()
        .reduce(function (a, b) {
          return intVal(a) + intVal(b);
        }, 0);


      // テーブルのフッターに合計値を表示する
      $(api.column(4).footer()).html(
        (total).toLocaleString("ja-JP") + '円',
      );

      $(api.table().footer())
        .find('.plusTax')
        .html(Math.ceil(total * plusTax).toLocaleString("ja-JP") + '円')

    },
  });

});
