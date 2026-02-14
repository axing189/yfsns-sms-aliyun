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

use Yfsns\LaravelSmsAliyun\Services\SmsService;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

// 阿里云短信API路由
Route::prefix('api/v1/sms/aliyun')
    ->middleware(['api'])
    ->group(function (): void {
        // 发送短信
        Route::post('/send', function (Request $request) {
            $request->validate([
                'phone' => 'required|string|regex:/^1[3-9]\d{9}$/',
                'template_code' => 'required|string',
                'params' => 'nullable|array',
            ]);

            $smsService = app(SmsService::class);
            $result = $smsService->send(
                $request->input('phone'),
                $request->input('template_code'),
                $request->input('params', [])
            );

            return response()->json([
                'code' => $result['success'] ? 200 : 500,
                'message' => $result['message'],
                'data' => $result['data'],
            ]);
        });

        // 发送验证码
        Route::post('/send-verification', function (Request $request) {
            $request->validate([
                'phone' => 'required|string|regex:/^1[3-9]\d{9}$/',
                'code' => 'required|string|min:4|max:6',
                'expire' => 'nullable|integer|min:1|max:60',
            ]);

            $smsService = app(SmsService::class);
            $result = $smsService->sendVerification(
                $request->input('phone'),
                $request->input('code'),
                $request->input('expire', 10)
            );

            return response()->json([
                'code' => $result['success'] ? 200 : 500,
                'message' => $result['message'],
                'data' => $result['data'],
            ]);
        });

        // 发送通知
        Route::post('/send-notification', function (Request $request) {
            $request->validate([
                'phone' => 'required|string|regex:/^1[3-9]\d{9}$/',
                'template_code' => 'required|string',
                'params' => 'nullable|array',
            ]);

            $smsService = app(SmsService::class);
            $result = $smsService->sendNotification(
                $request->input('phone'),
                $request->input('template_code'),
                $request->input('params', [])
            );

            return response()->json([
                'code' => $result['success'] ? 200 : 500,
                'message' => $result['message'],
                'data' => $result['data'],
            ]);
        });

        // 批量发送
        Route::post('/send-batch', function (Request $request) {
            $request->validate([
                'phones' => 'required|array|min:1|max:100',
                'phones.*' => 'string|regex:/^1[3-9]\d{9}$/',
                'template_code' => 'required|string',
                'params' => 'nullable|array',
            ]);

            $smsService = app(SmsService::class);
            $result = $smsService->sendBatch(
                $request->input('phones'),
                $request->input('template_code'),
                $request->input('params', [])
            );

            return response()->json([
                'code' => $result['success'] ? 200 : 500,
                'message' => $result['message'],
                'data' => $result['data'],
            ]);
        });

        // 测试连接
        Route::get('/test', function () {
            $smsService = app(SmsService::class);
            $result = $smsService->testConnection();

            return response()->json([
                'code' => $result['success'] ? 200 : 500,
                'message' => $result['message'],
                'data' => $result['data'],
            ]);
        });

        // 获取配置
        Route::get('/config', function () {
            $smsService = app(SmsService::class);
            $config = $smsService->getConfig();

            return response()->json([
                'code' => 200,
                'message' => '获取配置成功',
                'data' => $config,
            ]);
        });
    });
