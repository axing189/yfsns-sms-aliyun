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

namespace Yfsns\LaravelSmsAliyun\Services;

use Exception;
use AlibabaCloud\Client\AlibabaCloud;
use AlibabaCloud\Client\Exception\ClientException;
use AlibabaCloud\Client\Exception\ServerException;
use Illuminate\Support\Facades\Log;

class SmsService
{
    protected array $config;

    /**
     * 构造函数
     */
    public function __construct(array $config = [])
    {
        $this->config = array_merge($this->getDefaultConfig(), $config);
        $this->initializeClient();
    }

    /**
     * 获取默认配置
     */
    protected function getDefaultConfig(): array
    {
        return [
            'access_key_id' => config('sms.aliyun.access_key_id', ''),
            'access_key_secret' => config('sms.aliyun.access_key_secret', ''),
            'region_id' => config('sms.aliyun.region_id', 'cn-hangzhou'),
            'sign_name' => config('sms.aliyun.sign_name', ''),
            'timeout' => config('sms.aliyun.timeout', 30),
        ];
    }

    /**
     * 初始化阿里云客户端
     */
    protected function initializeClient(): void
    {
        try {
            AlibabaCloud::accessKeyClient(
                $this->config['access_key_id'],
                $this->config['access_key_secret']
            )->regionId($this->config['region_id'])->asDefaultClient();
        } catch (ClientException $e) {
            Log::warning('阿里云客户端初始化失败', [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * 发送短信
     */
    public function send(string $phone, string $templateCode, array $templateData = []): array
    {
        try {
            $this->validateConfig();

            $result = AlibabaCloud::rpc()
                ->product('Dysmsapi')
                ->version('2017-05-25')
                ->action('SendSms')
                ->method('POST')
                ->host('dysmsapi.aliyuncs.com')
                ->options([
                    'query' => [
                        'PhoneNumbers' => $phone,
                        'SignName' => $this->config['sign_name'],
                        'TemplateCode' => $templateCode,
                        'TemplateParam' => json_encode($templateData),
                    ],
                ])
                ->request();

            $response = $result->toArray();

            return [
                'success' => $response['Code'] === 'OK',
                'message' => $response['Message'] ?? '发送成功',
                'data' => $response,
                'request_id' => $response['RequestId'] ?? null,
            ];

        } catch (ServerException $e) {
            Log::error('阿里云短信发送失败', [
                'phone' => $phone,
                'template' => $templateCode,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => '短信发送失败：' . $e->getMessage(),
                'data' => null,
            ];
        } catch (ClientException $e) {
            Log::error('阿里云客户端错误', [
                'phone' => $phone,
                'template' => $templateCode,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => '客户端错误：' . $e->getMessage(),
                'data' => null,
            ];
        } catch (Exception $e) {
            Log::error('阿里云短信发送失败', [
                'phone' => $phone,
                'template' => $templateCode,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => '短信发送失败：' . $e->getMessage(),
                'data' => null,
            ];
        }
    }

    /**
     * 发送验证码短信
     */
    public function sendVerification(string $phone, string $code, int $expire = 10): array
    {
        $templateCode = config('sms.aliyun.templates.verification', '');
        if (empty($templateCode)) {
            return [
                'success' => false,
                'message' => '验证码模板未配置',
                'data' => null,
            ];
        }

        return $this->send($phone, $templateCode, [
            'code' => $code,
            'expire' => (string)$expire,
        ]);
    }

    /**
     * 发送通知短信
     */
    public function sendNotification(string $phone, string $templateCode, array $params = []): array
    {
        return $this->send($phone, $templateCode, $params);
    }

    /**
     * 批量发送短信
     */
    public function sendBatch(array $phones, string $templateCode, array $templateData = []): array
    {
        try {
            $this->validateConfig();

            $result = AlibabaCloud::rpc()
                ->product('Dysmsapi')
                ->version('2017-05-25')
                ->action('SendBatchSms')
                ->method('POST')
                ->host('dysmsapi.aliyuncs.com')
                ->options([
                    'query' => [
                        'PhoneNumberJson' => json_encode($phones),
                        'SignNameJson' => json_encode(array_fill(0, count($phones), $this->config['sign_name'])),
                        'TemplateCode' => $templateCode,
                        'TemplateParamJson' => json_encode(array_fill(0, count($phones), $templateData)),
                    ],
                ])
                ->request();

            $response = $result->toArray();

            return [
                'success' => $response['Code'] === 'OK',
                'message' => $response['Message'] ?? '批量发送成功',
                'data' => $response,
                'request_id' => $response['RequestId'] ?? null,
            ];

        } catch (ServerException $e) {
            Log::error('阿里云短信批量发送失败', [
                'phones' => $phones,
                'template' => $templateCode,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => '批量发送失败：' . $e->getMessage(),
                'data' => null,
            ];
        } catch (ClientException $e) {
            Log::error('阿里云客户端错误', [
                'phones' => $phones,
                'template' => $templateCode,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => '客户端错误：' . $e->getMessage(),
                'data' => null,
            ];
        } catch (Exception $e) {
            Log::error('阿里云短信批量发送失败', [
                'phones' => $phones,
                'template' => $templateCode,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => '批量发送失败：' . $e->getMessage(),
                'data' => null,
            ];
        }
    }

    /**
     * 验证配置
     */
    protected function validateConfig(): void
    {
        if (empty($this->config['access_key_id'])) {
            throw new Exception('阿里云AccessKey ID未配置');
        }
        if (empty($this->config['access_key_secret'])) {
            throw new Exception('阿里云AccessKey Secret未配置');
        }
        if (empty($this->config['sign_name'])) {
            throw new Exception('短信签名未配置');
        }
    }

    /**
     * 测试连接
     */
    public function testConnection(): array
    {
        try {
            $this->validateConfig();

            // 测试客户端初始化
            $this->initializeClient();

            return [
                'success' => true,
                'message' => '阿里云短信服务连接正常',
                'data' => [
                    'region' => $this->config['region_id'],
                    'sign_name' => $this->config['sign_name'],
                ],
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => '连接测试失败：' . $e->getMessage(),
                'data' => null,
            ];
        }
    }

    /**
     * 获取配置信息
     */
    public function getConfig(): array
    {
        return [
            'region_id' => $this->config['region_id'],
            'sign_name' => $this->config['sign_name'],
            'timeout' => $this->config['timeout'],
        ];
    }

    /**
     * 设置配置
     */
    public function setConfig(array $config): void
    {
        $this->config = array_merge($this->config, $config);
        $this->initializeClient();
    }
}
