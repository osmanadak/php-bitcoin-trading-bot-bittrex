<?php
include("inc/header.php");

$balances = $polo->get_balances();
$total = 0;
foreach($balances as $key=>$value){
    if($value > 0) {
        $valueOnOpenOrder = $polo->get_open_orders("BTC_".$key);
        for($k=0; $k<count($valueOnOpenOrder); $k++){
            if($valueOnOpenOrder[$k]['orderNumber'] != ""){
                $value = toplamaBtc($value, $valueOnOpenOrder[$k]['amount']);
            }
        }


        $stmt = $dbh->prepare("select last from data where coinType='BTC_$key' order by id desc limit 1");
        $stmt->execute();
        $price = $stmt->fetch();
        $last = $price['last'];
        if($last == ""){
            $last = 1;
        }
        echo $key."<br>Total: ".carpmaBtc($last, $value)."<br>Last:".$last."<br>Value: ".$value."<br><br>";
        if($last == 0){
            $last = 1;
        }
        $total = $total + carpmaBtc($last, $value);
    }
}

echo "Total: ".$total;

include("inc/footer.php");
