function pushHideButtonAgain() {
  var txtPass = document.getElementById("password_again");
  var btnEye = document.getElementById("buttonAgainEye");
  if (txtPass.type === "text") {
    txtPass.type = "password";
    btnEye.className = "me-2 bi-eye translate-middle position-absolute top-50 end-0";
  } else {
    txtPass.type = "text";
    btnEye.className = "me-2 bi-eye-slash translate-middle position-absolute top-50 end-0";
  }
}
