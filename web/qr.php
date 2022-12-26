<?php
header("Content-type: image/png");
$str=stripslashes($_GET['str']);
if(preg_match('/^[0-9A-Za-z_:\\/ \\?\\.=]{1,150}$/',$str)) {
        passthru("qrencode '$str' -o -");
}
