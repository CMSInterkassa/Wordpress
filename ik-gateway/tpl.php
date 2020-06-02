<!--<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" crossorigin="anonymous">-->
<!--<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" crossorigin="anonymous"></script>-->
<link rel="stylesheet" href="<?php echo $plugin_path;?>css/interkassa.css">

<form name="payment_interkassa" id="InterkassaForm" action="javascript:;" method="POST" class="">
    <?php echo $hidden_fields;?>
    <input class="button" type="submit" value="<?php _e('Оплатить', 'interkassa');?>" style="background-color: #ef8989">
    <?php echo $cancel_url;?>
</form>

<div class="interkasssa" style="text-align: center;">
<?php
if($this->enabledAPI == 'yes') {
    $payment_systems = $this->getIkPaymentSystems($this->merchant_id, $this->api_id, $this->api_key);
    if (is_array($payment_systems) && !empty($payment_systems)) {
        ?>
        <button type="button" class="sel-ps-ik btn btn-info btn-lg" data-toggle="modal" data-target="#InterkassaModal" style="display: none;">
            Select Payment Method
        </button>
        <div id="InterkassaModal" class="ik-modal fade" role="dialog">
            <div class="ik-modal-dialog ik-modal-lg">
                <div class="ik-modal-content" id="plans">
                    <div class="container">
                        <h3>
                            1. <?php _e('Выберите удобный способ оплаты', 'interkassa'); ?><br>
                            2. <?php _e('Укажите валюту', 'interkassa'); ?><br>
                            3. <?php _e('Нажмите &laquo;Оплатить&raquo;', 'interkassa'); ?><br>
                        </h3>
                        <div class="ik-row">
                            <?php foreach ($payment_systems as $ps => $info) { ?>
                                <div class="col-sm-3 text-center payment_system">
                                    <div class="panel panel-warning panel-pricing">
                                        <div class="ik-panel-heading">
                                            <div class="panel-image">
                                                <img src="<?php echo $image_path . $ps; ?>.png"
                                                     alt="<?php echo $info['title']; ?>">
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <div class="input-group">
                                                <div class="radioBtn btn-group">
                                                    <?php foreach ($info['currency'] as $currency => $currencyAlias) { ?>
                                                        <a class="ik-btn btn ik-btn-primary btn-sm notActive"
                                                           data-toggle="fun"
                                                           data-title="<?php echo $currencyAlias; ?>"><?php echo $currency; ?></a>
                                                    <?php } ?>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="panel-footer">
                                            <a class="btn btn-lg btn-block btn-success ik-payment-confirmation"
                                               data-title="<?php echo $ps; ?>"
                                               href="#"><?php _e('Оплатить через', 'interkassa'); ?><br>
                                                <strong><?php echo $info['title']; ?></strong>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
    } 
	else
        echo $payment_systems;
}
?>
</div>
<script type="text/javascript">
	var reqUrlApiIk = '<?php echo $ajax_url;?>';
	var dataLangIk = {
		currency_no_select : '<?php _e('Вы не выбрали валюту', 'interkassa');?>'
	};	
	
    var str=document.createElement('script');
    str.type='text/javascript';
    str.src='/wp-content/plugins/ik-gateway/js/interkassa.js';
    document.body.appendChild(str);
</script>
<?php /*wp_enqueue_script('', '/wp-content/plugins/ik-gateway/js/interkassa.js', [], false, true);*/ ?>