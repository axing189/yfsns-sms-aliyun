<?php

namespace Yfsns\SmsAliyun\Channels;

use App\Modules\Sms\Contracts\SmsChannelInterface;
use Illuminate\Support\Facades\Config;
use Exception;

class AliyunChannel implements SmsChannelInterface
{
    protected array $config;

    public function __construct()
    {
        $this->config = $this->getDefaultConfig();
    }

    protected function getDefaultConfig(): array
    {
        return [
            'access_key_id' => Config::get('sms.aliyun.access_key_id', ''),
            'access_key_secret' => Config::get('sms.aliyun.access_key_secret', ''),
            'region_id' => Config::get('sms.aliyun.region_id', 'cn-hangzhou'),
            'sign_name' => Config::get('sms.aliyun.sign_name', ''),
            'timeout' => Config::get('sms.aliyun.timeout', 30),
        ];
    }

    public function getName(): string
    {
        return '阿里云短信';
    }

    public function getConfig(): array
    {
        return $this->config;
    }

    public function send(string $phone, string $templateCode, array $templateData = []): array
    {
        try {
            $this->validateCurrentConfig();

            $accessKeyId = $this->config['access_key_id'];
            $accessKeySecret = $this->config['access_key_secret'];
            $regionId = $this->config['region_id'];
            $signName = $this->config['sign_name'];

            $config = new \Darabonba\OpenApi\Models\Config([
                'accessKeyId' => $accessKeyId,
                'accessKeySecret' => $accessKeySecret,
            ]);

            $config->endpoint = "dysmsapi.{$regionId}.aliyuncs.com";

            $client = new \AlibabaCloud\SDK\Dysmsapi\V20170525\Dysmsapi($config);

            $sendSmsRequest = new \AlibabaCloud\SDK\Dysmsapi\V20170525\Models\SendSmsRequest([
                'phoneNumbers' => $phone,
                'signName' => $signName,
                'templateCode' => $templateCode,
                'templateParam' => json_encode($templateData, JSON_UNESCAPED_UNICODE),
            ]);

            $response = $client->sendSms($sendSmsRequest);
            $body = $response->body;

            $success = $body->code === 'OK';

            return [
                'success' => $success,
                'message' => $body->message ?: ($success ? '发送成功' : '发送失败'),
                'data' => [
                    'request_id' => $body->requestId,
                    'biz_id' => $body->bizId,
                    'code' => $body->code,
                ],
                'request_id' => $body->requestId,
            ];

        } catch (Exception $e) {
            \Illuminate\Support\Facades\Log::error('阿里云短信发送失败', [
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

    public function setConfig(array $config): void
    {
        $this->config = array_merge($this->config, $config);
    }

    public function getChannelType(): string
    {
        return 'aliyun';
    }

    public function getConfigFields(): array
    {
        return [
            [
                'name' => 'access_key_id',
                'label' => 'Access Key ID',
                'type' => 'text',
                'required' => true,
                'default' => '',
            ],
            [
                'name' => 'access_key_secret',
                'label' => 'Access Key Secret',
                'type' => 'password',
                'required' => true,
                'default' => '',
            ],
            [
                'name' => 'region_id',
                'label' => '区域 ID',
                'type' => 'text',
                'required' => false,
                'default' => 'cn-hangzhou',
            ],
            [
                'name' => 'sign_name',
                'label' => '短信签名',
                'type' => 'text',
                'required' => true,
                'default' => '',
            ],
        ];
    }

    public function validateConfig(array $config): array
    {
        $errors = [];

        if (empty($config['access_key_id'] ?? $this->config['access_key_id'])) {
            $errors[] = 'Access Key ID 不能为空';
        }
        if (empty($config['access_key_secret'] ?? $this->config['access_key_secret'])) {
            $errors[] = 'Access Key Secret 不能为空';
        }
        if (empty($config['sign_name'] ?? $this->config['sign_name'])) {
            $errors[] = '短信签名不能为空';
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'warnings' => [],
        ];
    }

    public function getCapabilities(): array
    {
        return ['verification', 'notification', 'marketing', 'international'];
    }

    public function testConnection(array $config): array
    {
        try {
            $testConfig = array_merge($this->config, $config);
            $this->validateConfig($testConfig);

            return [
                'success' => true,
                'message' => '阿里云短信服务连接正常',
                'data' => [
                    'region' => $testConfig['region_id'],
                    'sign_name' => $testConfig['sign_name'],
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

    public function getProviderInfo(): array
    {
        return [
            'name' => '阿里云',
            'website' => 'https://www.aliyun.com/',
            'description' => '阿里云 SMS 短信服务',
            'regions' => [
                'cn-hangzhou', 'cn-beijing', 'cn-shanghai',
                'cn-shenzhen', 'cn-hongkong', 'ap-southeast-1',
            ],
        ];
    }

    public function supportsInternational(): bool
    {
        return true;
    }

    public function getSupportedRegions(): array
    {
        return ['CN', 'HK', 'US', 'SG', 'JP', 'KR'];
    }

    protected function validateCurrentConfig(): void
    {
        $result = $this->validateConfig([]);
        if (!$result['valid']) {
            throw new Exception(implode(', ', $result['errors']));
        }
    }
}
