<?php

/**
 * YFSNS社交网络服务系统
 *
 * Copyright (C) 2025 合肥音符信息科技有限公司
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace Yfsns\LaravelSmsAliyun\Providers;

use Yfsns\LaravelSmsAliyun\Services\SmsService;
use Illuminate\Support\ServiceProvider;

class SmsAliyunServiceProvider extends ServiceProvider
{
    /**
     * 注册服务
     */
    public function register(): void
    {
        $this->app->singleton(SmsService::class, function ($app) {
            return new SmsService();
        });

        $this->app->alias(SmsService::class, 'sms.aliyun.service');
    }

    /**
     * 引导服务
     */
    public function boot(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../Config/sms.php',
            'sms'
        );

        $this->loadRoutesFrom(__DIR__ . '/../../routes/api.php');

        $this->registerPublishing();
    }

    /**
     * 注册资源发布
     */
    protected function registerPublishing(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../Config/sms.php' => config_path('sms.php'),
            ], 'yfsns-sms-aliyun-config');
        }
    }
}
