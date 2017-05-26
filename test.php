<?php
class P{
    function P(){

    }
   static function stfnc(){
        static  $loder;
        $loder++;
        echo $loder;
    }
}
class A extends P{
    function a(){

    }
}
class B extends P{
    function a(){

    }
}

$a= new A();
$b = new B();
$a->stfnc();
$a->stfnc();
$a->stfnc();
$b->stfnc();
echo '<hr>';
A::stfnc();
B::stfnc();

1231
42