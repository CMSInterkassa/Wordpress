<button id="InterkassaModalButton" style="display:none;"><?php _e('Выбрать платежную систему', 'interkassa'); ?></button>


<div id="InterkassaModal" class="interkassa-modal">
    <div class="interkassa-modal-content">
        <span class="close">&times;</span>
        <div class="row">
            <h1>
                1. <?php _e('Выберите удобный способ оплаты', 'interkassa'); ?><br>
                2. <?php _e('Укажите валюту', 'interkassa'); ?><br>
                3. <?php _e('Нажмите &laquo;Оплатить&raquo;', 'interkassa'); ?><br>
            </h1>
            <div class="row">

                <?php foreach ($payment_systems as $ps => $info) { ?>

                    <div class="text-center payment_system">
                        <div class="panel panel-warning panel-pricing">
                            <div class="panel-heading">
                                <img src="<?php echo $image_path; ?><?php echo $ps; ?>.png" alt="<?php echo $info['title']; ?>">
                               <!-- <h3><?php //echo $info['title']; ?></h3>-->
                            </div>
                            <div class="form-group">
                                <div class="input-group">
                                    <div id="radioBtn" class="btn-group">
                                        <?php foreach ($info['currency'] as $currency => $currencyAlias) { ?>
                                            <?php if ($currency == $shop_cur) { ?>
                                                <a class="btn btn-primary btn-sm active" data-toggle="fun"
                                                   data-title="<?php echo $currencyAlias; ?>"><?php echo $currency; ?></a>
                                            <?php } else { ?>
                                                <a class="btn btn-primary btn-sm notActive" data-toggle="fun"
                                                   data-title="<?php echo $currencyAlias; ?>"><?php echo $currency; ?></a>
                                            <?php } ?>
                                        <?php } ?>
                                    </div>
                                    <input type="hidden" name="fun" id="fun">
                                </div>
                            </div>
                            <div class="button-footer">

                                <!--<a class="btn btn-block btn-success ik-payment-confirmation" data-title="<?php echo $ps; ?>"
                                   href="#"><?php #_e('Оплатить с', 'interkassa'); ?> <?php #echo $info['title']; ?>
                                </a>-->
                            </div>
                        </div>
                    </div>
                <?php } ?>
                <div>
                	<a class="btn btn-lg btn-block-secondary btn-success ik-payment-confirmation" data-title="<?php echo $ps; ?>"
                                   href="#"><?php _e('Оплатить', 'interkassa'); ?>
                                </a>
            	</div>
            </div>
        </div>
    </div>
</div>


<script type="text/javascript">

	function test(){
		$("#InterkassaModalButton").click();
	}

	function paystart(data){
		data_array = JSON.parse(data);
        console.log(data_array);
        var form = $('form[name="interkassa_form"]');
        if(data_array['resultCode']!=0){
            //alert(data_array['resultMsg']);
            form[0].action="https://sci.interkassa.com/";
            $('input[name =  "ik_act"]').remove();
            $('input[name =  "ik_int"]').remove();
                form.submit();
        }
        else{
            if(data_array['resultData']['paymentForm']!=undefined)
            {
                var data_send_form=[];
                var data_send_inputs=[];
                data_send_form['url'] = data_array['resultData']['paymentForm']['action'];
                data_send_form['method'] = data_array['resultData']['paymentForm']['method'];
                for(var i in data_array['resultData']['paymentForm']['parameters']){
                    data_send_inputs[i]=data_array['resultData']['paymentForm']['parameters'][i];
                }
                $('body').append('<form method="'+data_send_form['method']+'" id="tempform" action="'+data_send_form['url']+'"></form>');
                for(var i in data_send_inputs){
                    $("#tempform").append('<input type="hidden" name="'+i+'" value="'+data_send_inputs[i]+'" />');
                }
                $('#tempform').submit();
            }
            else{
                $('form[name= "interkassa_form"]').append('<div id="tempdiv">'+data_array['resultData']['internalForm']+'</div>');
                var form2=$('#internalForm');
                //$('input[name =  "ik_act"]').remove();
                //$('input[name =  "ik_int"]').remove();
                //$('input[name =  "sci[ik_int]"]').remove();
                form2[0].action="javascript:test2()";
            }
        }
	}

	function test2(){
    var form2=$('#internalForm');
    var msg2 = form2.serialize();
    //console.log(msg2);
        $.ajax({
            type: 'POST',
            url: '<?php echo $ajax_url2?>',
            data: msg2,
            success: function(data) {
                paystart2(data);
            },
            error:  function(xhr, str){
                alert('Возникла ошибка: ' + xhr.responseCode);
            }
        });
    }

    function paystart2(string){
        data_array = JSON.parse(string);
        console.log(data_array);
        var form2=$('#internalForm');
        if(data_array['resultCode']!=0){
           // alert(data_array['resultMsg']);
            form2[0].action="https://sci.interkassa.com/";
            $('input[name =  "ik_act"]').remove();
            $('input[name =  "ik_int"]').remove();
            $('input[name =  "sci[ik_int]"]').remove();
            //console.log(form2);
            //console.log(form2[0]);
            form2.submit();
        }
        else{
            $('#tempdiv').html('');
            if(data_array['resultData']['paymentForm']!=undefined)
            {
                var data_send_form=[];
                var data_send_inputs=[];
                data_send_form['url'] = data_array['resultData']['paymentForm']['action'];
                data_send_form['method'] = data_array['resultData']['paymentForm']['method'];
                for(var i in data_array['resultData']['paymentForm']['parameters']){
                    data_send_inputs[i]=data_array['resultData']['paymentForm']['parameters'][i];
                }
                $('#tempdiv').append('<form method="'+data_send_form['method']+'" id="tempform2" action="'+data_send_form['url']+'"></form>');
                for(var i in data_send_inputs){
                    $("#tempform2").append('<input type="hidden" name="'+i+'" value="'+data_send_inputs[i]+'" />');
                }
                $('#tempform2').submit();
            }
            else{
                $('.woocommerce').append('<div id="tempdiv">'+data_array['resultData']['internalForm']+'</div>');
            }
        }
    }

    $ = jQuery;

    var modal = document.getElementById('InterkassaModal');
    var btn = document.getElementById("InterkassaModalButton");
    var span = document.getElementsByClassName("close")[0];


    btn.onclick = function () {
        modal.style.display = "block";
    };
    span.onclick = function () {
        modal.style.display = "none";
    };
    window.onclick = function (event) {
        if (event.target == modal) {
            modal.style.display = "none";
        }
    };
    $(document).ready(function () {

        var curtrigger = false;
        var form = $('form[name="interkassa_form"]');
        form[0].action = "javascript:test()";
        $('.ik-payment-confirmation').click(function (e) {
            e.preventDefault();
            if (!curtrigger) {
                alert('Вы не выбрали валюту');
                return;
            } else {
            	if($('input[name =  "ik_pw_via"]').val()!='test_interkassa_test_xts' || $('input[name = "ik_pw_via"]').val()=="svyaznoy_wp_merchantTn_rub" || $('input[name = "ik_pw_via"]').val()=="euroset_wp_merchantTn_rub")
            	{
            	 	form.append(
                            $('<input>', {
                                type: 'hidden',
                                name: 'ik_act',
                                val: 'process'
                            }));
                	form.append(
                            $('<input>', {
                                type: 'hidden',
                                name: 'ik_int',
                                val: 'json'
                            }));
            		$.post('<?php echo $ajax_url2; ?>', form.serialize())
                	.done(function (data) {
                		paystart(data);
                	})
                	.fail(function () {
                    	alert('Something wrong');
                	});
                }
                else{
                	form[0].action="https://sci.interkassa.com/";
                	form.submit();
                }
            	modal.style.display = "none";
            }
        });
        $('#radioBtn a').click(function () {
            curtrigger = true;
            var ik_cur = this.innerText;
            console.log(ik_cur);
            var ik_pw_via = $(this).attr('data-title');

            if ($('input[name =  "ik_pw_via"]').length > 0) {
                $('input[name =  "ik_pw_via"]').val(ik_pw_via);
            } else {
                form.append(
                    $('<input>', {
                        type: 'hidden',
                        name: 'ik_pw_via',
                        val: ik_pw_via
                    }));
            }
            $.post('<?php echo $ajax_url; ?>', form.serialize())
                .done(function (data) {
                    console.log(data);
                    if ($('input[name =  "ik_sign"]').length > 0) {
                        $('input[name =  "ik_sign"]').val(data);
                    }
                })
                .fail(function () {
                    alert('Something wrong');
                });
        });

        $('#radioBtn a').on('click', function () {
            var sel = $(this).data('title');
            var tog = $(this).data('toggle');
            $('#' + tog).prop('value', sel);
            $('a[data-toggle="' + tog + '"]').not('[data-title="' + sel + '"]').removeClass('active').addClass('notActive');
            $('a[data-toggle="' + tog + '"][data-title="' + sel + '"]').removeClass('notActive').addClass('active');
        })
    });


</script>
<style>

    #InterkassaModal {
        transition: 1s;
    }

    #InterkassaModal .input-group, #InterkassaModal h1 {
        text-align: center;
    }
    .payment_system{
        width: 24%;
        display: inline-block;
    }
    .payment_system h3, .payment_system img {
        display: inline-block;
        width: 100%;
        /*font-size: 18px;
        margin: 0;
        padding-top: 10px;*/
    }

    .payment_system .panel-heading {
        text-align: center;
    }

    .btn-primary {
        background-color: red;
    }
    
/*    .payment_system .btn-primary {
        background-image: none;
    }

    .payment_system .input-group {
        display: flex;
        justify-content: center;
        flex-wrap: wrap;
    }
*/
    .payment_system .btn-primary, .payment_system .btn-secondary, .payment_system .btn-tertiary {
        padding: 8px;
        font-size:10px;
    }

    .panel-pricing {
        -moz-transition: all .3s ease;
        -o-transition: all .3s ease;
        -webkit-transition: all .3s ease;
    }

    .panel-pricing:hover {
        box-shadow: 0px 0px 30px rgba(0, 0, 0, 0.2);
    }

    .panel-pricing .panel-heading {
        padding: 20px 10px;
    }

    .panel-pricing .panel-heading .fa {
        margin-top: 10px;
        font-size: 58px;
    }

    .panel-pricing .list-group-item {
        color: #777777;
        border-bottom: 1px solid rgba(250, 250, 250, 0.5);
    }

    .panel-pricing .list-group-item:last-child {
        border-bottom-right-radius: 0px;
        border-bottom-left-radius: 0px;
    }

    .panel-pricing .list-group-item:first-child {
        border-top-right-radius: 0px;
        border-top-left-radius: 0px;
    }

    .panel-pricing .panel-body {
        background-color: #f0f0f0;
        font-size: 40px;
        color: #777777;
        padding: 20px;
        margin: 0px;
    }
    /*#radioBtn{
        padding: 20px 10px;
    }
    #radioBtn a{
        transition: 0.2s;
    }
    #radioBtn a:hover{
        transform: scale(1.02);
    }*/
    #radioBtn .notActive {
    	color: #3276b1;
        background-color: #fff;
        /*background-color: #48bd82;
        border: 2px solid #48bd82;
        color: #fff;
        cursor: pointer;*/
    }
   /* #radioBtn .active {
        background-color: #fff;
        color: #48bd82;
        border: 2px solid #48bd82;
    }*/

    .interkassa-modal {
        display: none;
        position: fixed;
        z-index: 100000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        overflow: auto;
        background-color: rgb(0, 0, 0);
        background-color: rgba(0, 0, 0, 0.4);
    }

    .interkassa-modal-content {
        background-color: #fefefe;
        margin: 5% auto;
        padding: 20px;
        border: 1px solid #888;
        width: 60%;
    }

    .close {
        color: #aaa;
        float: right;
        font-size: 28px;
        font-weight: bold;
    }

    .close:hover,
    .close:focus {
        color: black;
        text-decoration: none;
        cursor: pointer;
    }

    #InterkassaModalButton {
        padding: 6px 12px;
        background: #48bd82;
        border: 1px solid transparent;
        border-radius: 4px;
        line-height: 1.42857143;
        font-weight: 700;
        font-size: 16px;
        color: #fff;
        transition: 1s;
    }

    #InterkassaModalButton:hover {
        background: #4CB16D;
    }
    .button-footer{
        text-align: center;
    }
    .ik-payment-confirmation{
        margin-top: 10px;
        background: #4CB16D;
        padding: 5px 5px;
        transition: 0.2s;
        color: #fff;
        font-weight: 500;
        font-size: 18px;
        width: 100%;
    }
    .ik-payment-confirmation:hover{
        transform: scale(1.02);
    }

    .btn-block-secondary{
        display:block;
        text-align: center;
        width:30%;
        position: relative;
        margin: auto;
        margin-top:10px;
    }
 
 	#phone{
 		width:50%;
 		display:inline;
 	}

    @media only screen and (min-width:768px) and (max-width:1200px) {
        .payment_system{
            width: 32%;
        }
    }

    @media only screen and (min-width:480px) and (max-width:768px) {
        .payment_system{
            width: 48%;
        }
        .ik-payment-confirmation{
            font-size: 14px;
        }
        #radioBtn a{
            font-size: 14px;
        }
    }
    @media only screen and (max-width:480px) {
        .payment_system{
            width: 100%;
        }
        .ik-payment-confirmation{
            font-size: 14px;
        }
        #radioBtn a{
            font-size: 14px;
        }
    }




</style>