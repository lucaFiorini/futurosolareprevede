<?php

$conn = mysqli_connect('localhost', 'futurosolareprevede', '', 'my_futurosolareprevede');	

if (!$conn){
  die("Connessione fallita: " . mysqli_connect_error());
}else
  echo "Connessione avvenuta con successo";
?>
      