<div class="relative">
    <x-mixpost::preloader size="lg" :rounded="false" id="preloader" class="hidden"/>

    @if(count($authorizations))
        <x-mixpost::button-primary id="card-button">
            Add new card and Subscribe
        </x-mixpost::button-primary>


        <div id="payment-methods" class="flex flex-col overflow-x-hidden overflow-y-hidden mt-lg">
            <h2 class="font-medium text-base">Payment Methods:</h2>

            <div class="-m-1.5 overflow-x-auto max-h-[500px] mt-xs">
                <div class="p-1.5 min-w-full inline-block align-middle overflow-hidden">
                    <div class="flex flex-col space-y-sm">
                        @foreach($authorizations as $authorization)
                            <div data-payment_method_authorization_code="{{ $authorization['authorization_code'] }}"
                                 class="group border border-gray-200 p-sm rounded-md w-full">
                                <div class="flex space-x-md">
                                    <div>
                                        <span class="capitalize">{{ $authorization['brand'] }}</span>
                                        <span>ending in {{ $authorization['last4'] }}</span>
                                    </div>
                                    <div class="text-gray-500">Expires
                                        <span>{{ $authorization['exp_month'] }}/{{ $authorization['exp_year'] }}
                                    </div>
                                </div>

                                <div class="flex justify-between items-center mt-md">
                                    <button class="subscribe link-primary">
                                        Subscribe using this card
                                    </button>

                                    <button
                                        class="remove-payment-method-button text-red-500 hidden group-hover:block transition ease-in-out duration-200">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none"
                                             viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                  d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    @endif

    <div id="card-errors" class="hidden mt-xs">
        <x-mixpost::form-error>
            <span id="card-errors-message"></span>
        </x-mixpost::form-error>
    </div>
</div>
<script src="https://js.paystack.co/v2/inline.js"></script>
<script type="text/javascript">
    (function () {
        function subscribeUsingInline() {
            const data = {
                key: '{{ $key }}',
                email: '{{ $email }}',
                planCode: '{{ $plan_code }}',
                metadata: {
                    custom_fields: [
                        {
                            subscription_name: "{{ $passthrough['subscription_name'] }}",
                            billable_id: "{{ $passthrough['billable_id'] }}",
                        }
                    ]
                },
                onSuccess: (transaction) => {
                    if (transaction.status === 'success') {
                        window.location.href = '{{ $success_url }}';
                        return;
                    }

                    displayErrors('Something went wrong. Please try again.');
                },
                onCancel: () => {
                    isNotLoading();
                }
            }

            try {
                hideErrors();
                isLoading();

                const paystack = new PaystackPop();
                paystack.newTransaction(data);
            } catch (e) {
                isNotLoading();
                displayErrors('Something went wrong. Please try again.');
            }
        }

        function subscribeUsingApi(authorizationCode = null) {
            isLoading();

            fetch('{{ $create_subscription_url }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                },
                body: JSON.stringify({
                    add_subscription: true,
                    authorization_code: authorizationCode,
                    plan_id: '{{ $original_request['plan_id'] }}', // requires by the create subscription endpoint. No effect for this Paystack scenario.
                    cycle: '{{ $original_request['cycle'] }}', // requires by the create subscription endpoint. No effect for this Paystack scenario.
                }),
            })
                .then(response => response.json())
                .then((message) => {
                    if (message === 'ok') {
                        window.location.href = '{{ $success_url }}';
                        return;
                    }

                    isNotLoading();
                    displayErrors(message);
                })
                .catch(() => {
                    isNotLoading();
                    displayErrors('Something went wrong. Please try again.');
                });
        }

        function removePaymentMethod(authorizationCode) {
            if (!confirm('Are you sure you want to remove this payment method?')) {
                return;
            }

            isLoading();

            fetch('{{ $create_subscription_url }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                },
                body: JSON.stringify({
                    delete_payment_method: true,
                    authorization_code: authorizationCode,
                    plan_id: '{{ $original_request['plan_id'] }}', // requires by the create subscription endpoint. No effect for this Paystack scenario.
                    cycle: '{{ $original_request['cycle'] }}', // requires by the create subscription endpoint. No effect for this Paystack scenario.
                }),
            })
                .then(response => response.json())
                .then((data) => {
                    isNotLoading();

                    if (data !== 'ok') {
                        displayErrors('Something went wrong. Please try again.');
                        return;
                    }

                    const paymentMethodDiv = document.querySelector(`div[data-payment_method_authorization_code="${authorizationCode}"]`);

                    if (paymentMethodDiv) {
                        paymentMethodDiv.remove();
                    }

                    // Check if all payment method divs are deleted
                    const paymentMethodDivs = document.querySelectorAll('div[data-payment_method_authorization_code]');

                    if (paymentMethodDivs.length === 0) {
                        const parentBlock = document.getElementById('payment-methods');

                        if (parentBlock) {
                            parentBlock.style.display = 'none';
                        }
                    }
                })
                .catch(() => {
                    isNotLoading();
                    displayErrors('Something went wrong. Please try again.');
                });
        }

        function isLoading() {
            document.getElementById('preloader').classList.remove('hidden');
        }

        function isNotLoading() {
            document.getElementById('preloader').classList.add('hidden');
        }

        function displayErrors(message) {
            document.getElementById('card-errors-message').innerHTML = message;
            document.getElementById('card-errors').classList.remove('hidden');

            setTimeout(() => {
                hideErrors();
            }, 5000);
        }

        function hideErrors() {
            document.getElementById('card-errors').classList.add('hidden');
        }

        // Remove payment method
        document.querySelectorAll('button.remove-payment-method-button').forEach((button) => {
            button.addEventListener('click', function () {
                const authorizationCode = this.closest('[data-payment_method_authorization_code]').getAttribute('data-payment_method_authorization_code');
                removePaymentMethod(authorizationCode);
            });
        });

        // Subscribe using existing card
        document.querySelectorAll('button.subscribe').forEach((button) => {
            button.addEventListener('click', function () {
                const authorizationCode = this.closest('[data-payment_method_authorization_code]').getAttribute('data-payment_method_authorization_code');
                subscribeUsingApi(authorizationCode);
            });
        });

        // Immediately open the inline popup if there are no payment methods.
        @if(!count($authorizations))
        isLoading();
        subscribeUsingInline();
        const event = new Event('closePaymentDetailsModal');
        document.dispatchEvent(event);
        @endif

        // Subscribe using inline popup on button click when there are payment methods.
        @if(count($authorizations))
        document.getElementById('card-button').addEventListener('click', async () => {
            subscribeUsingInline();
        });
        @endif
    })();
</script>
