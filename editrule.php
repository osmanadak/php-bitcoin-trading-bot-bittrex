<?php
include("inc/header.php");
$coins = $bit->getMarkets();
$stmt = $dbh->prepare("select * from rules where id='$_GET[id]' limit 1");
$stmt->execute();
$rule = $stmt->fetch();
?>

<section id="main">
    <section id="content">
        <div class="container">
            <form id="ruleForm">
                <div class="card">
                    <div class="card-header">
                        <h2><?php echo $lang["edit_rule"];?> <small><?php echo $lang["edit_rule_definition"];?></small></h2>
                        <ul class="actions">
                            <li>
                                <a href="rules.php">
                                    <i class="zmdi zmdi-view-list"></i>
                                </a>
                            </li>
                        </ul>
                    </div>

                    <div class="card-body card-padding">
                        <div class="form-group">
                            <div class="fg-line">
                                <div class="select">
                                    <select id="coin" name="coin" class="form-control" onchange="coinChanged(this.value);">
                                        <option value="0"><?php echo $lang["select_an_altcoin"];?></option>
                                        <?php
                                        for($i = 0; $i < count($coins); $i++){
                                            $x = $coins[$i];
                                            $xCheck = substr($x->MarketName, 0, 3);
                                            if($xCheck == 'BTC' and $x->IsActive == 1){
                                                ?>
                                                <option <?php if($rule['coin'] == $x->MarketName){echo "selected"; }?> value="<?php echo $x->MarketName; ?>"><?php echo substr($x->MarketName,4,4); ?></option>
                                            <?php }} ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="fg-line">
                                <?php echo $lang["buy_price"];?>: <span id="buyPrice"> - </span>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="fg-line">
                                <?php echo $lang["sell_price"];?>: <span id="sellPrice"> - </span>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="fg-line">
                                <div class="select">
                                    <select id="buy_type" name="buy_type" class="form-control">
                                        <option value="0"><?php echo $lang["select_buy_type"];?></option>
                                        <option <?php if($rule['buy_type'] == "1"){echo "selected"; }?> value="1">Price (-)</option>
                                        <option <?php if($rule['buy_type'] == "2"){echo "selected"; }?> value="2">Price (+)</option>
                                        <option <?php if($rule['buy_type'] == "3"){echo "selected"; }?> value="3">Volume (-)</option>
                                        <option <?php if($rule['buy_type'] == "4"){echo "selected"; }?> value="4">Volume (+)</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="fg-line">
                                <div class="select">
                                    <select id="time" name="time" class="form-control">
                                        <option value="0">Select Time</option>
                                        <option <?php if($rule['time'] == "1"){echo "selected"; }?> value="1">1 <?php echo $lang["minute"];?></option>
                                        <option <?php if($rule['time'] == "2"){echo "selected"; }?> value="2">2 <?php echo $lang["minutes"];?></option>
                                        <option <?php if($rule['time'] == "3"){echo "selected"; }?> value="3">3 <?php echo $lang["minutes"];?></option>
                                        <option <?php if($rule['time'] == "4"){echo "selected"; }?> value="4">4 <?php echo $lang["minutes"];?></option>
                                        <option <?php if($rule['time'] == "5"){echo "selected"; }?> value="5">5 <?php echo $lang["minutes"];?></option>
                                        <option <?php if($rule['time'] == "10"){echo "selected"; }?> value="10">10 <?php echo $lang["minutes"];?></option>
                                        <option <?php if($rule['time'] == "15"){echo "selected"; }?> value="15">15 <?php echo $lang["minutes"];?></option>
                                        <option <?php if($rule['time'] == "20"){echo "selected"; }?> value="20">20 <?php echo $lang["minutes"];?></option>
                                        <option <?php if($rule['time'] == "30"){echo "selected"; }?> value="30">30 <?php echo $lang["minutes"];?></option>
                                        <option <?php if($rule['time'] == "60"){echo "selected"; }?> value="60">1 <?php echo $lang["hour"];?></option>
                                        <option <?php if($rule['time'] == "120"){echo "selected"; }?> value="120">2 <?php echo $lang["hours"];?></option>
                                        <option <?php if($rule['time'] == "180"){echo "selected"; }?> value="180">3 <?php echo $lang["hours"];?></option>
                                        <option <?php if($rule['time'] == "360"){echo "selected"; }?> value="360">6 <?php echo $lang["hours"];?></option>
                                        <option <?php if($rule['time'] == "720"){echo "selected"; }?> value="720">12 <?php echo $lang["hours"];?></option>
                                        <option <?php if($rule['time'] == "1440"){echo "selected"; }?> value="1440">1 <?php echo $lang["day"];?></option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="fg-line">
                                <div class="select">
                                    <select id="buy_percent" name="buy_percent" class="form-control">
                                        <option value="0"><?php echo $lang["select_buy"];?> %</option>
                                        <option <?php if($rule['buy_percent'] == "1"){echo "selected"; }?> value="1">1 %</option>
                                        <option <?php if($rule['buy_percent'] == "2"){echo "selected"; }?> value="2">2 %</option>
                                        <option <?php if($rule['buy_percent'] == "3"){echo "selected"; }?> value="3">3 %</option>
                                        <option <?php if($rule['buy_percent'] == "4"){echo "selected"; }?> value="4">4 %</option>
                                        <option <?php if($rule['buy_percent'] == "5"){echo "selected"; }?> value="5">5 %</option>
                                        <option <?php if($rule['buy_percent'] == "10"){echo "selected"; }?> value="10">10 %</option>
                                        <option <?php if($rule['buy_percent'] == "15"){echo "selected"; }?> value="15">15 %</option>
                                        <option <?php if($rule['buy_percent'] == "20"){echo "selected"; }?> value="20">20 %</option>
                                        <option <?php if($rule['buy_percent'] == "25"){echo "selected"; }?> value="25">25 %</option>
                                        <option <?php if($rule['buy_percent'] == "30"){echo "selected"; }?> value="30">30 %</option>
                                        <option <?php if($rule['buy_percent'] == "35"){echo "selected"; }?> value="35">35 %</option>
                                        <option <?php if($rule['buy_percent'] == "40"){echo "selected"; }?> value="40">40 %</option>
                                        <option <?php if($rule['buy_percent'] == "45"){echo "selected"; }?> value="45">45 %</option>
                                        <option <?php if($rule['buy_percent'] == "50"){echo "selected"; }?> value="50">50 %</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="fg-line">
                                <div class="select">
                                    <select id="sell_on_profit" name="sell_on_profit" class="form-control">
                                        <option value="0"><?php echo $lang["sell_on_profit"];?> %</option>
                                        <option <?php if($rule['sell_on_profit'] == "1"){echo "selected"; }?> value="1">1 %</option>
                                        <option <?php if($rule['sell_on_profit'] == "2"){echo "selected"; }?> value="2">2 %</option>
                                        <option <?php if($rule['sell_on_profit'] == "3"){echo "selected"; }?> value="3">3 %</option>
                                        <option <?php if($rule['sell_on_profit'] == "4"){echo "selected"; }?> value="4">4 %</option>
                                        <option <?php if($rule['sell_on_profit'] == "5"){echo "selected"; }?> value="5">5 %</option>
                                        <option <?php if($rule['sell_on_profit'] == "10"){echo "selected"; }?> value="10">10 %</option>
                                        <option <?php if($rule['sell_on_profit'] == "15"){echo "selected"; }?> value="15">15 %</option>
                                        <option <?php if($rule['sell_on_profit'] == "20"){echo "selected"; }?> value="20">20 %</option>
                                        <option <?php if($rule['sell_on_profit'] == "25"){echo "selected"; }?> value="25">25 %</option>
                                        <option <?php if($rule['sell_on_profit'] == "30"){echo "selected"; }?> value="30">30 %</option>
                                        <option <?php if($rule['sell_on_profit'] == "35"){echo "selected"; }?> value="35">35 %</option>
                                        <option <?php if($rule['sell_on_profit'] == "40"){echo "selected"; }?> value="40">40 %</option>
                                        <option <?php if($rule['sell_on_profit'] == "45"){echo "selected"; }?> value="45">45 %</option>
                                        <option <?php if($rule['sell_on_profit'] == "50"){echo "selected"; }?> value="50">50 %</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="fg-line">
                                <div class="select">
                                    <select id="stop_loss" name="stop_loss" class="form-control">
                                        <option><?php echo $lang["stop_loss"];?> %</option>
                                        <option <?php if($rule['stop_loss'] == "1"){echo "selected"; }?> value="1">1 %</option>
                                        <option <?php if($rule['stop_loss'] == "2"){echo "selected"; }?> value="2">2 %</option>
                                        <option <?php if($rule['stop_loss'] == "3"){echo "selected"; }?> value="3">3 %</option>
                                        <option <?php if($rule['stop_loss'] == "4"){echo "selected"; }?> value="4">4 %</option>
                                        <option <?php if($rule['stop_loss'] == "5"){echo "selected"; }?> value="5">5 %</option>
                                        <option <?php if($rule['stop_loss'] == "10"){echo "selected"; }?> value="10">10 %</option>
                                        <option <?php if($rule['stop_loss'] == "15"){echo "selected"; }?> value="15">15 %</option>
                                        <option <?php if($rule['stop_loss'] == "20"){echo "selected"; }?> value="20">20 %</option>
                                        <option <?php if($rule['stop_loss'] == "25"){echo "selected"; }?> value="25">25 %</option>
                                        <option <?php if($rule['stop_loss'] == "30"){echo "selected"; }?> value="30">30 %</option>
                                        <option <?php if($rule['stop_loss'] == "35"){echo "selected"; }?> value="35">35 %</option>
                                        <option <?php if($rule['stop_loss'] == "40"){echo "selected"; }?> value="40">40 %</option>
                                        <option <?php if($rule['stop_loss'] == "45"){echo "selected"; }?> value="45">45 %</option>
                                        <option <?php if($rule['stop_loss'] == "50"){echo "selected"; }?> value="50">50 %</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="fg-line">
                                <button type="button" class="btn btn-success" onclick="editRule(); return false;"><?php echo $lang["save_rule"];?></button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </section>
</section>
<?php
include("inc/footer.php");
?>

<script>
    function coinChanged(coin){
        $.post('inc/data.php?type=getTicker',{coin: coin}, function(r){
            var result = JSON.parse(r);
            $('#buyPrice').html(result.Bid);
            $('#sellPrice').html(result.Ask);
        });
    }
    function editRule(){
        if($('#coin').val() == "0" || $('#buy_type').val() == "0" || $('#time').val() == "0" || $('#buy_percent').val() == "0" || $('#sell_on_profit').val() == "0"){
            toastr.options.closeButton = true;
            toastr.error('Please fill all fields!', 'Error!', {
                "positionClass": "toast-top-center",
                timeOut: 5000
            });
        }else{
            $.post('inc/data.php?type=editRule&id=<?php echo $rule['id']; ?>',$('#ruleForm').serialize(),function(r){
                toastr.options.closeButton = true;
                toastr.success('Rule saved successfully!', 'Rule saved!', {
                    "positionClass": "toast-top-center",
                    timeOut: 5000
                });
                setTimeout(function(){
                    window.location = 'rules.php';
                },1000);
            });
        }
    }
    $( document ).ready(function() {
        coinChanged($('#coin').val());
    });
</script>