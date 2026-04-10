<script type="text/javascript" src="https://cdn.paddle.com/paddle/paddle.js"></script>
<script type="text/javascript">
    (function () {
        @if ($sandbox)
        Paddle.Environment.set('sandbox');
        @endif

        Paddle.Setup(@json(['vendor' => (int) $vendor]));

        window.Paddle.Checkout.open({
            override: '{{ $payLink }}',
            email: '{{ $email }}',
        });

        const event = new Event('closePaymentDetailsModal');
        document.dispatchEvent(event);
    })();
</script>
<x-mixpost::preloader size="lg" :rounded="true" id="preloader"/>
