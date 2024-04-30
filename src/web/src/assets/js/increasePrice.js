function increasePrice(str) {
  var inputValue = str.value;
  // 入力されたカンマを消去
  var addComma = new String(inputValue).replace(/,/g, "");
  // 最上位桁まで下桁から３桁ごとにカンマ付加を繰り返す
  while (addComma != (addComma = addComma.replace(/^(-?\d+)(\d{3})/, "$1,$2")));
  document.getElementById("increase_price").value = addComma;
  return (str);
}
