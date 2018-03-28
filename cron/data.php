<?php
/**
 * Created by PhpStorm.
 * User: osmanadak
 * Date: 13.08.17
 * Time: 13:18
 */

require_once('../inc/settings.php');

$coins = $bit->getMarkets();

for ($i=0; $i < count($coins) ; $i++) {
    $x = $coins[$i];
    $xCheck = substr($x->MarketName, 0, 3);

    if($xCheck == 'BTC' and $x->IsActive == 1){
        $data = $bit->getMarketSummary($x->MarketName);

        $coinId = $x->MarketName;
        $last = number_format($data[0]->Last,8);
        $lowestAsk = number_format($data[0]->Low,8);
        $highestBid = number_format($data[0]->High,8);
        //$percentChange = null;
        $baseVolume = $data[0]->BaseVolume;
        $quoteVolume = $data[0]->Volume;
        //$high24hr = null;
        //$low24hr = null;

        $date = date("Y-m-j H:i:s");

        $stmt = $dbh->prepare("INSERT INTO data (coinType, last, lowestAsk, highestBid, baseVolume, quoteVolume, isFrozen, date) VALUES ('$coinId', '$last', '$lowestAsk', '$highestBid', '$baseVolume', '$quoteVolume', '', '$date')");

        if($stmt->execute()){
            //echo 'Data added!<br>';
        } else {
            echo  'Error!<br>';
        }
    }
}
$stmt1 = $dbh->prepare("SELECT * from rules where status = 1");
$stmt1->execute();

while($rules = $stmt1->fetch()){
    $openOrderCount = 0;
    echo $rules['coin']."<br>";
    $openOrders = $bit -> getOpenOrders($rules['coin']);

    if(!empty($openOrders)){
        for($i = 0; $i < count($openOrders); $i++){
            if($openOrders[$i]->OrderType == "LIMIT_BUY"){
                $bit->cancel($openOrders[$i]->OrderUuid);
            }else{
                $openOrderCount++;
            }
        }
    }

    echo "Open Order Count: ".$openOrderCount."<br>";
    for($i=0; $i<$openOrderCount;$i++){
        $orderNumber = $openOrders[$i]->OrderUuid;
        $orderType = $openOrders[$i]->OrderType;
        if($orderType == "LIMIT_BUY"){
            $bit->cancel($orderNumber);
        }else{
            $stmt = $dbh->prepare("SELECT * from processes where sellOrderNumber = '$orderNumber' limit 1");
            $stmt->execute();
            $row = $stmt->fetch();
            $stopLossPrice = $row['stopLossPrice'];
            echo "Stop Loss Price: ".$stopLossPrice."<br>";
            if($stopLossPrice > 0){
                $stmt2 = $dbh->prepare("SELECT * from data where coinType = '$rules[coin]' order by id desc limit 1");
                $stmt2->execute();
                $row2 = $stmt2->fetch();
                echo "Current Price: ".$row2['last']."<br>";
                if($row2['last'] < $stopLossPrice){
                    echo "Coin: ".$rules['coin']." Order Number: ".$orderNumber."<br>";
                    $cancelOrderStopLoss = $bit->cancel($orderNumber);
                    if(!empty($cancelOrderStopLoss)){
                        $sellCancelOrder = $bit->sellLimit($rules['coin'], carpmaBtc($row["amount"],0.9975), $row2['last']);
                        echo "<pre>";
                        print_r($sellCancelOrder);
                        $text = $row["amount"]." Stop Loss Sell Order saved for ".$rules['coin']." with price ".$row2['last'];
                        $stmt = $dbh->prepare("insert into notifications set text='$text', date = NOW()");
                        $stmt->execute();
                    }
                }
            }
        }
    }

    if($openOrderCount >= $settings['buy_limit_per_coin']){
        echo "Limit: ".$settings['buy_limit_per_coin'].", Open Orders: ".$openOrderCount."<br>";
        continue;
    }
    $time = $rules['time'];
    $stmt = $dbh->prepare("SELECT * from data where coinType = '$rules[coin]' and date > (NOW() - INTERVAL $time MINUTE) order by date desc");
    $stmt->execute();
    $lastPrices = array();
    $lastVolume = array();
    while($row = $stmt->fetch()){
        $lastPrices[] =  $row['last'];
        $lastVolumes[] =  $row['baseVolume'];
    }
    $newestPrice = $lastPrices[0];
    $oldestPrice = $lastPrices[count($lastPrices) - 1];

    $newestVolume = $lastVolumes[0];
    $oldestVolume = $lastVolumes[count($lastVolumes) - 1];

    echo "Newest: ".$newestPrice."<br>Oldest: ".$oldestPrice."<br>";
    echo "Newest Volume: ".$newestVolume."<br>Oldest Volume: ".$oldestVolume."<br>";

    $percentChange = ($newestPrice - $oldestPrice)*100/$oldestPrice;
    $percentVolumeChange = ($newestVolume - $oldestVolume)*100/$oldestVolume;
    echo "Change %: ".$percentChange."<br>";
    echo "Change % (Volume): ".$percentVolumeChange."<br>";

    if($rules['buy_type'] == "1"){
        $buyPercent = $rules['buy_percent']*(-1);
    }
    if($rules['buy_type'] == "2"){
        $buyPercent = $rules['buy_percent'];
    }
    if($rules['buy_type'] == "3"){
        $buyPercent = $rules['buy_percent']*(-1);
    }
    if($rules['buy_type'] == "4"){
        $buyPercent = $rules['buy_percent'];
    }

    echo "Buy Percent: ".$buyPercent."<br><br>";

    /*echo "Buying process<br>";
    $amount = bolmeBtc($settings['btc_amount_per_buy'], $newestPrice);
    $buyResult = $bit->buyLimit($rules['coin'], $amount, $newestPrice);
    echo "<pre>";
    print_r($buyResult);
    if(!empty($buyResult->uuid)){
        $sellPercent = 1 + ($rules['sell_on_profit'] / 100);
        $sellPrice = carpmaBtc($sellPercent, $newestPrice);
        if ($rules['stop_loss'] > 0) {
            $stopLossPrice = carpmaBtc((1 - $rules['stop_loss'] / 100), $newestPrice);
        } else {
            $stopLossPrice = 0;
        }

        $orderNumber = $buyResult->uuid;
        $stmt = $dbh->prepare("INSERT INTO processes (coinType, buyPrice, sellPrice, stopLossPrice, status, orderNumber,amount) VALUES ('$rules[coin]', '$newestPrice', '$sellPrice', '$stopLossPrice', 1, '$orderNumber', '$amount')");
        $stmt->execute();
    }*/


    if($rules['buy_type'] == "1" or $rules['buy_type'] == "2") {
        if (($buyPercent < 0 and $percentChange < $buyPercent) or ($buyPercent > 0 and $percentChange > $buyPercent)) {
            echo "Buying process<br>";
            $btcBalance = $bit->getBalance("BTC");
            $btcBalance->Balance;
            if($btcBalance->Balance > $settings['btc_amount_per_buy']){
                $amount = bolmeBtc($settings['btc_amount_per_buy'], $newestPrice);
                $buyResult = $bit->buyLimit($rules['coin'], $amount, $newestPrice);
                echo "<pre>";
                print_r($buyResult);
                if(!empty($buyResult->uuid)){
                    $sellPercent = 1 + ($rules['sell_on_profit'] / 100);
                    $sellPrice = carpmaBtc($sellPercent, $newestPrice);
                    if ($rules['stop_loss'] > 0) {
                        $stopLossPrice = carpmaBtc((1 - $rules['stop_loss'] / 100), $newestPrice);
                    } else {
                        $stopLossPrice = 0;
                    }

                    $orderNumber = $buyResult->uuid;
                    $stmt = $dbh->prepare("INSERT INTO processes (coinType, buyPrice, sellPrice, stopLossPrice, status, orderNumber,amount) VALUES ('$rules[coin]', '$newestPrice', '$sellPrice', '$stopLossPrice', 1, '$orderNumber', '$amount')");
                    $stmt->execute();
                }
            }else{
                echo "Not enough BTC<br>";
            }
        }
    }elseif($rules['buy_type'] == "3" or $rules['buy_type'] == "4") {
        if (($buyPercent < 0 and $percentVolumeChange < $buyPercent) or ($buyPercent > 0 and $percentVolumeChange > $buyPercent)) {
            echo "Buying process<br>";
            $btcBalance = $bit->getBalance("BTC");
            $btcBalance->Balance;
            if($btcBalance->Balance > $settings['btc_amount_per_buy']){
                $amount = bolmeBtc($settings['btc_amount_per_buy'], $newestPrice);
                $buyResult = $bit->buyLimit($rules['coin'], $amount, $newestPrice);
                echo "<pre>";
                print_r($buyResult);
                if(!empty($buyResult->uuid)){
                    $sellPercent = 1 + ($rules['sell_on_profit'] / 100);
                    $sellPrice = carpmaBtc($sellPercent, $newestPrice);
                    if ($rules['stop_loss'] > 0) {
                        $stopLossPrice = carpmaBtc((1 - $rules['stop_loss'] / 100), $newestPrice);
                    } else {
                        $stopLossPrice = 0;
                    }

                    $orderNumber = $buyResult->uuid;
                    $stmt = $dbh->prepare("INSERT INTO processes (coinType, buyPrice, sellPrice, stopLossPrice, status, orderNumber,amount) VALUES ('$rules[coin]', '$newestPrice', '$sellPrice', '$stopLossPrice', 1, '$orderNumber', '$amount')");
                    $stmt->execute();
                }
            }else{
                echo "Not enough BTC<br>";
            }
        }
    }

    sleep(1); // ?!
    $openOrders = $bit -> getOpenOrders($rules['coin']);

    $stmt = $dbh->prepare("select * from processes where status='1' and coinType='$rules[coin]' order by id asc");
    $stmt->execute();
    while ($a = $stmt->fetch()) {
        $alimAcikMi = false;

        foreach ($openOrders as $oO) {
            if ($oO->OrderUuid == $a["orderNumber"]) {
                $alimAcikMi = true;
                echo $rules['coin'] . " - Buy order is still open<br>";
                $bit->cancel($a["orderNumber"]);
                $stmtDelete = $dbh->prepare("delete from processes where ordeNumber = '$a[orderNumber]'");
                $stmtDelete->execute();
            }
        }
        if ($alimAcikMi == false) {
            echo "Sell<br>";

            $sellFirst = $bit->sellLimit($rules['coin'], carpmaBtc($a["amount"],0.9975), $a["sellPrice"]);
            echo "<pre></pre>";
            var_dump($sellFirst);
            echo "---";
            if(!empty($sellFirst)){
                $text = $a["amount"]." Sell Order saved for ".$rules['coin']." with price ".$a["sellPrice"].". You bought with price ".$a['buyPrice'];
                $stmt = $dbh->prepare("insert into notifications set text='$text', date = NOW()");
                $stmt->execute();
                $sellOrderNumber = $sellFirst->uuid;
            }else{
                $stmt = $dbh->prepare("delete from processes where id='$a[id]'");
                $stmt->execute();
            }
            $stmt = $dbh->prepare("update processes set status = 0, sellOrderNumber = '$sellOrderNumber' where id='$a[id]'");
            $stmt->execute();
        }
    }

}


$stmt = $dbh->prepare("DELETE FROM data WHERE date < (NOW() - INTERVAL 25 HOUR)");
$stmt->execute();

$balances = $bit->getBalances();
$total = 0;

$stmt = $dbh->prepare("TRUNCATE current_balances");
$stmt->execute();

foreach($balances as $b){
    if($b->Balance > 0) {

        $stmt = $dbh->prepare("select last from data where coinType='BTC-$b->Currency' order by id desc limit 1");
        $stmt->execute();
        $price = $stmt->fetch();
        $last = $price['last'];
        if($last == ""){
            $last = 1;
        }

        if($last == 0){
            $last = 1;
        }
        $btcValue = carpmaBtc($last, $b->Balance);
        $total = $total + $btcValue;
        $stmt_balance = $dbh->prepare("insert into current_balances set coin = '$b->Currency', balance = '$b->Balance', btc_value = '$btcValue'");
        $stmt_balance->execute();
    }
}
echo $total;
$stmt = $dbh->prepare("insert into total_btc set amount = '$total', date=NOW()");
$stmt->execute();

$stmt = $dbh->prepare("delete total_btc where amount = 0");
$stmt->execute();

