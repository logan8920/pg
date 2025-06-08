<?php
$template = `
<script src="https://ebz-static.s3.ap-south-1.amazonaws.com/easecheckout/v2.0.0/easebuzz-checkout-v2.min.js"></script>
<script>
   $(document).ready(function(){
       var base_url = $('head base').attr('href');
       var easebuzzCheckout = new EasebuzzCheckout('`.$key.`','`.$env.`')
       var options = {
           access_key: '`.$accesskey.`',
           onResponse: (response) => {
               console.log(response);
               $.ajax({
                    url: '`.route('pg-redirecturl.callback', $gateway).`',
                    type: 'POST',
                    data: response,
                    success: function(responseback) {
                        responseback    =   JSON.parse(responseback);
                        console.log(responseback);
                        if(responseback.status==true){
                            window.location.href=responseback.redirectURL;
                        }else{
                            alert(responseback.message);
                            window.location.href='`.url("gateway-pg-receipt").`'+'?resdata='+responseback.message;
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error:', error);
                        alert('Exception Error!!!!');
                        window.location.href='`.url("gateway-pg-receipt").`';
                    }
                });
           },
           theme: "#123456"
       }
       easebuzzCheckout.initiatePayment(options);
   });
</script>`;