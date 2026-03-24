<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>


   <script src="https://www.paypal.com/sdk/js?client-id=AfxpHsm1P-5ELgjGsvIUWhcnwda6PIIahiRoy9bJzDuzsRbR8Xh60vH3d7qROyJ7oeVZ_f__ntm9b3kp&components=buttons"></script>
   <div id="paypal-button-container"></div>    


    <script>
        paypal.Buttons({
            style:{
                color:  'blue',
                shape:  'pill',
                label:  'pay'
            },
            createOrder: function(data, actions) {
                return actions.order.create({
                    purchase_units: [{
                        amount: {
                            value: '100.00'
                        }
                    }]
                });
            },
            onCancel: function(data) {
                alert('Transacción fue cancelada');
                console.log(data);
            },
            onApprove: function(data, actions) {
                return actions.order.capture().then(function(details) {
                    alert('Transacción completada por ' + details.payer.name.given_name);
                    console.log(details);
                });
            }
        }).render('#paypal-button-container');
    </script>

</body>
</html>