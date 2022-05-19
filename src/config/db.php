<?php

class DB {
  private $host = HOST_BD;
  private $user = USER_BD;
  private $password = PW_BD;
  private $bbdd = BD;

  public function connect(){
    $cnn = new mysqli($this->host, $this->user, $this->password, $this->bbdd);

    if($cnn->connect_errno){
      return false;
    }else{
      return $cnn;
    }
  }

}