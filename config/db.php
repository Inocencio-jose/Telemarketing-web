<?php 
session_start();

define('SERVER', 'localhost');
define('USER', 'root');
define('PASSWORD', '');
define('DATABASE', 'db_tl');

$strcon = new mysqli(SERVER, USER, PASSWORD, DATABASE);

if($strcon->error){
   die('Falha ao conectar ao banco de dados' . $strcon->error);
}
?>