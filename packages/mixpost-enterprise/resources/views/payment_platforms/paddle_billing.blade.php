<script type="text/javascript" src="https://cdn.paddle.com/paddle/v2/paddle.js"></script>
<script type="text/javascript">
    (function () {
        @if ($sandbox)
        Paddle.Environment.set('sandbox');
        @endif

        Paddle.Setup(@json(['token' => $client_side_token]));

        window.Paddle.Checkout.open({
            items: [
                {
                    priceId: '{{ $price_id }}',
                    quantity: 1
                }
            ],
            customer: {
                id: '{{ $customer->platform_customer_id }}',
            },
            discountCode: '{{ $discount_code }}',
            customData: @json($custom_data),
            settings: {
                successUrl: '{{ $success_url }}',
            },
        });

        const event = new Event('closePaymentDetailsModal');
        document.dispatchEvent(event);
    })();
</script>
<x-mixpost::preloader size="lg" :rounded="true" id="preloader"/>
