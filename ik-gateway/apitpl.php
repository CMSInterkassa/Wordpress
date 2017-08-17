<button id="InterkassaModalButton"><?php _e('Выбрать платежную систему', 'interkassa'); ?></button>


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
                                <h3><?php echo $info['title']; ?></h3>
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

                                <a class="btn btn-block btn-success ik-payment-confirmation" data-title="<?php echo $ps; ?>"
                                   href="#"><?php _e('Оплатить с', 'interkassa'); ?> <?php echo $info['title']; ?>
                                </a>
                            </div>
                        </div>
                    </div>
                <?php } ?>
            </div>
        </div>
    </div>
</div>


<script type="text/javascript">

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
        $('.ik-payment-confirmation').click(function (e) {
            e.preventDefault();
            if (!curtrigger) {
                alert('Вы не выбрали валюту');
                return;
            } else {
                form.submit();
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
        font-size: 18px;
        margin: 0;
        padding-top: 10px;
    }

    .payment_system .panel-heading {
        text-align: center;
    }

    .payment_system .btn-primary {
        background-image: none;
    }

    .payment_system .input-group {
        display: flex;
        justify-content: center;
        flex-wrap: wrap;
    }

    .payment_system .btn-primary, .payment_system .btn-secondary, .payment_system .btn-tertiary {
        padding: 2px;
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
    #radioBtn{
        padding: 20px 10px;
    }
    #radioBtn a{
        transition: 0.2s;
    }
    #radioBtn a:hover{
        transform: scale(1.02);
    }
    #radioBtn .notActive {
        background-color: #48bd82;
        border: 2px solid #48bd82;
        color: #fff;
        cursor: pointer;
    }
    #radioBtn .active {
        background-color: #fff;
        color: #48bd82;
        border: 2px solid #48bd82;
    }

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