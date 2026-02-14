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

return [
    /*
    |--------------------------------------------------------------------------
    | 阿里云短信配置
    |--------------------------------------------------------------------------
    |
    | 配置阿里云短信服务的相关参数
    |
    */

    'aliyun' => [
        // 阿里云 AccessKey ID
        'access_key_id' => env('ALIYUN_SMS_ACCESS_KEY_ID', ''),

        // 阿里云 AccessKey Secret
        'access_key_secret' => env('ALIYUN_SMS_ACCESS_KEY_SECRET', ''),

        // 地域
        'region_id' => env('ALIYUN_SMS_REGION_ID', 'cn-hangzhou'),

        // 短信签名
        'sign_name' => env('ALIYUN_SMS_SIGN_NAME', ''),

        // 超时时间（秒）
        'timeout' => env('ALIYUN_SMS_TIMEOUT', 30),

        // 模板配置
        'templates' => [
            // 验证码模板CODE
            'verification' => env('ALIYUN_SMS_TEMPLATE_VERIFICATION', ''),

            // 通知模板CODE
            'notification' => env('ALIYUN_SMS_TEMPLATE_NOTIFICATION', ''),

            // 营销模板CODE
            'marketing' => env('ALIYUN_SMS_TEMPLATE_MARKETING', ''),
        ],
    ],
];
