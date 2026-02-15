<?php

namespace Yfsns\SmsAliyun\Providers;

use Yfsns\SmsAliyun\Channels\AliyunChannel;
use App\Modules\Sms\Channels\Registry\SmsChannelRegistryInterface;
use Illuminate\Support\ServiceProvider;

class SmsAliyunServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->extend(SmsChannelRegistryInterface::class, function ($registry) {
            $registry->registerChannel('aliyun', AliyunChannel::class);
            return $registry;
        });
    }

    public function boot(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../Config/sms.php',
            'sms'
        );

        $this->registerPublishing();
    }

    protected function registerPublishing(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../Config/sms.php' => config_path('sms.php'),
            ], 'yfsns-sms-aliyun-config');
        }
    }
}
