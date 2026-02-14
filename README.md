# YFSNS 阿里云短信独立包

[![License](https://img.shields.io/badge/License-Apache%202.0-blue.svg)](https://opensource.org/licenses/Apache-2.0)
[![PHP Version](https://img.shields.io/badge/PHP-%3E%3D8.1-blue.svg)](https://php.net)
[![Laravel Version](https://img.shields.io/badge/Laravel-%3E%3D10.0-blue.svg)](https://laravel.com)

YFSNS 阿里云短信独立包是一个完整的阿里云短信对接解决方案，为 Laravel 应用提供简单易用的短信发送能力。

## 📦 特性

- ✅ 完整的阿里云短信 API 对接
- ✅ 支持单条短信发送
- ✅ 支持批量短信发送
- ✅ 支持验证码短信发送
- ✅ 支持通知短信发送
- ✅ 配置简单，使用便捷
- ✅ 完整的错误处理和日志记录
- ✅ 支持环境变量配置
- ✅ 支持连接测试

## 🚀 安装

### 1. 安装包

```bash
composer require yfsns/yfsns-sms-aliyun
```

### 2. 配置环境变量

在 `.env` 文件中添加以下配置：

```env
# 阿里云短信配置
ALIYUN_SMS_ACCESS_KEY_ID=your_access_key_id
ALIYUN_SMS_ACCESS_KEY_SECRET=your_access_key_secret
ALIYUN_SMS_REGION_ID=cn-hangzhou
ALIYUN_SMS_SIGN_NAME=your_sign_name
ALIYUN_SMS_TIMEOUT=30

# 模板配置
ALIYUN_SMS_TEMPLATE_VERIFICATION=your_verification_template_code
ALIYUN_SMS_TEMPLATE_NOTIFICATION=your_notification_template_code
ALIYUN_SMS_TEMPLATE_MARKETING=your_marketing_template_code
```

### 3. 发布配置文件（可选）

```bash
php artisan vendor:publish --tag=yfsns-sms-aliyun-config
```

## 📡 API 接口

| 接口 | 方法 | 功能 |
|------|------|------|
| `/api/v1/sms/aliyun/send` | POST | 发送短信 |
| `/api/v1/sms/aliyun/send-verification` | POST | 发送验证码 |
| `/api/v1/sms/aliyun/send-notification` | POST | 发送通知 |
| `/api/v1/sms/aliyun/send-batch` | POST | 批量发送 |
| `/api/v1/sms/aliyun/test` | GET | 测试连接 |
| `/api/v1/sms/aliyun/config` | GET | 获取配置 |

## 🎯 使用示例

### 1. 通过依赖注入使用

```php
use Yfsns\LaravelSmsAliyun\Services\SmsService;

class SmsController extends Controller
{
    protected $smsService;

    public function __construct(SmsService $smsService)
    {
        $this->smsService = $smsService;
    }

    public function sendSms(Request $request)
    {
        $result = $this->smsService->send(
            '13800138000',
            'SMS_123456789',
            ['code' => '123456', 'expire' => '10']
        );

        return response()->json($result);
    }

    public function sendVerificationCode(Request $request)
    {
        $result = $this->smsService->sendVerification(
            '13800138000',
            '123456',
            10 // 10分钟有效期
        );

        return response()->json($result);
    }
}
```

### 2. 通过服务容器使用

```php
// 获取短信服务实例
$smsService = app(Yfsns\LaravelSmsAliyun\Services\SmsService::class);

// 或者使用别名
$smsService = app('sms.aliyun.service');

// 发送短信
$result = $smsService->send(
    '13800138000',
    'SMS_123456789',
    ['code' => '123456']
);

// 测试连接
$testResult = $smsService->testConnection();
```

### 3. 批量发送短信

```php
$smsService = app(SmsService::class);

$result = $smsService->sendBatch(
    [
        '13800138000',
        '13900139000',
        '13700137000'
    ],
    'SMS_123456789',
    ['code' => '123456']
);

return response()->json($result);
```

## 🔧 配置说明

### 主要配置项

| 配置项 | 类型 | 说明 | 默认值 |
|--------|------|------|--------|
| `access_key_id` | string | 阿里云 AccessKey ID | '' |
| `access_key_secret` | string | 阿里云 AccessKey Secret | '' |
| `region_id` | string | 地域 | 'cn-hangzhou' |
| `sign_name` | string | 短信签名 | '' |
| `timeout` | int | 超时时间（秒） | 30 |

### 模板配置

| 配置项 | 说明 |
|--------|------|
| `templates.verification` | 验证码模板CODE |
| `templates.notification` | 通知模板CODE |
| `templates.marketing` | 营销模板CODE |

## 📋 返回格式

所有方法返回统一的格式：

```json
{
    "success": true, // 是否成功
    "message": "发送成功", // 消息
    "data": {...}, // 详细数据
    "request_id": "abc123" // 请求ID（仅发送成功时）
}
```

## 🔍 错误处理

包内置了完整的错误处理机制，会捕获并记录所有异常：

- 配置错误：返回配置相关的错误信息
- API 错误：返回阿里云 API 的错误信息
- 网络错误：返回网络相关的错误信息

所有错误都会被记录到 Laravel 的日志系统中，方便排查问题。

## 🎯 注意事项

1. **阿里云账号**：需要先在阿里云控制台开通短信服务
2. **签名和模板**：需要先在阿里云控制台申请并审核通过短信签名和模板
3. **AccessKey**：需要在阿里云控制台获取，建议使用子账号并设置权限
4. **地域选择**：根据实际情况选择合适的地域，默认 `cn-hangzhou`

## 📞 支持

- **官方网站**：https://yfsns.com
- **技术支持**：support@yfsns.com
- **GitHub**：https://github.com/axing189/yfsns-sms-aliyun

## 📄 许可证

本项目采用 Apache 2.0 许可证 - 详见 [LICENSE](LICENSE) 文件

---

**🎉 开始使用阿里云短信服务吧！**
