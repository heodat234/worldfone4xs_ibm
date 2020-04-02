<?php
function a($txt) {
  b("Glenn");
}
function b($txt) {
  c("Cleveland");
}
function c($txt) {
  var_dump(debug_backtrace());
}

a("Peter");

?>