<?php
$password = '@Merauke99616';
$hash = password_hash($password, PASSWORD_BCRYPT);
echo "Hash untuk password '@Merauke99616':\n";
echo $hash;
?>