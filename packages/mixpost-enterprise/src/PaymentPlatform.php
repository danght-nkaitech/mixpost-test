<?php

namespace Inovector\MixpostEnterprise;

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;
use Inovector\Mixpost\Support\Log;
use Inovector\MixpostEnterprise\Abstracts\PaymentPlatform as PaymentPlatformAbstract;
use Inovector\MixpostEnterprise\Contracts\PaymentPlatform as PaymentPlatformContract;
use Inovector\MixpostEnterprise\Exceptions\NoPaymentPlatformActiveException;
use Inovector\MixpostEnterprise\Models\PaymentPlatform as PaymentPlatformModel;
use Inovector\MixpostEnterprise\PaymentPlatforms\Paddle\PaddlePaymentPlatform;
use Inovector\MixpostEnterprise\PaymentPlatforms\PaddleBilling\PaddleBillingPaymentPlatform;
use Inovector\MixpostEnterprise\PaymentPlatforms\Paystack\PaystackPaymentPlatform;
use Inovector\MixpostEnterprise\PaymentPlatforms\Stripe\StripePaymentPlatform;

class PaymentPlatform
{
    protected static mixed $config;

    protected static ?Collection $platformCollection = null;

    protected static array $customPlatforms = [];

    public static function platforms(): array
    {
        return array_merge([
            StripePaymentPlatform::class,
            PaddleBillingPaymentPlatform::class,
            PaddlePaymentPlatform::class,
            PaystackPaymentPlatform::class,
        ], self::$customPlatforms);
    }

    public static function registerPlatforms(array $platforms): static
    {
        static::$customPlatforms = $platforms;

        return new static;
    }

    public static function get(string $name, bool $forceFresh = false): array
    {
        if (! $forceFresh && ! self::$platformCollection) {
            self::$platformCollection = PaymentPlatformModel::all();
        } elseif ($forceFresh) {
            self::$platformCollection = PaymentPlatformModel::all();
        }

        $platform = self::$platformCollection->where('name', $name)->first();

        $platformClass = self::getPlatformClassByName($name);

        $defaultPayload = [
            'name' => $platformClass::name(),
            'readable_name' => $platformClass::readableName(),
            'component' => $platformClass::component(),
            'credentials' => $platformClass::formCredentials(),
            'options' => $platformClass::formOptions(),
            'enabled' => false,
        ];

        if (! $platform) {
            return $defaultPayload;
        }

        try {
            $credentials = $platform->credentials->toArray();
        } catch (DecryptException $exception) {
            self::logDecryptionError($name, $exception);

            $credentials = $defaultPayload['credentials'];
        }

        return [
            'name' => $platformClass::name(),
            'readable_name' => $platformClass::readableName(),
            'component' => $platformClass::component(),
            'credentials' => array_merge($defaultPayload['credentials'], $credentials),
            'options' => array_merge($defaultPayload['options'], Arr::wrap($platform->options ?? [])),
            'enabled' => $platform->enabled,
        ];
    }

    public static function all(): array
    {
        return Arr::map(self::platformClasses(), function ($platformClass) {
            return self::get($platformClass::name());
        });
    }

    public static function supported(): array
    {
        return Arr::map(self::platformClasses(), function ($platformClass) {
            return $platformClass::name();
        });
    }

    public static function activePlatformInstance(): PaymentPlatformContract
    {
        $model = PaymentPlatformModel::where('enabled', 1)->first();

        if (! $model) {
            throw new NoPaymentPlatformActiveException('No payment platform is currently active');
        }

        $instance = self::getPlatformClassByName($model->name);

        $instance->setCredentials($model->credentials->toArray());
        $instance->setOptions($model->options ?? []);

        return $instance;
    }

    public static function getPlatformClassByName(string $name): PaymentPlatformContract
    {
        $platform = Arr::first(self::platformClasses(), function ($platformClass) use ($name) {
            return $name === $platformClass::name();
        });

        if (! $platform) {
            throw new \Exception("The `$name` platform does not registered.");
        }

        return app($platform);
    }

    private static function platformClasses(): array
    {
        $platforms = self::platforms();

        foreach ($platforms as $platform) {
            if (! app($platform) instanceof PaymentPlatformAbstract) {
                throw new \Exception("The `$platform` platform must be an instance of Inovector\MixpostEnterprise\Abstracts\PaymentPlatform.");
            }
        }

        return $platforms;
    }

    private static function logDecryptionError($name, DecryptException $exception): void
    {
        Log::error("The application key cannot decrypt the payment platform credentials: {$exception->getMessage()}", [
            'name' => $name,
        ]);
    }
}
