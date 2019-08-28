<h1 align="center"> wechat </h1>

<p align="center"> api.</p>


## Installing

```shell
$ composer require lsshu/wechat
```
## Usage ACCOUNT
------------
#### 实例化ACCOUNT
```php
$config = ['appId'=>'','appSecret'=>''] ;
$account = Lsshu\Wechat\Service::account($config);
```

## Usage MESSAGE
------------
#### 实例化MESSAGE
```php
$config = ['appId'=>'','token'=>'','encodingAesKey'=>''] ;
$message = Lsshu\Wechat\Service::message($config);
```

## Usage PROGRAM
------------
#### 实例化PROGRAM
```php
$config = ['appId'=>'','appSecret'=>''] ;
$program = Lsshu\Wechat\Service::program($config);
```

#### 登录凭证校验 code2Session
```php
$res = $program->code2Session($code);
```

#### 二维码 createWXAQRCode
```php
$res = $program->createWXAQRCode($path, $width);
```

#### 二维码 getWXACode
```php
$res = $program->getWXACode($path, $width, $auto_color, $line_color, $is_hyaline);
```

#### 二维码 getWXACodeUnlimit
```php
$res = $program->getWXACodeUnlimit($scene, $page, $width, $auto_color, $line_color, $is_hyaline);
```

## Contributing

You can contribute in one of three ways:

1. File bug reports using the [issue tracker](https://github.com/lsshu/wechat/issues).
2. Answer questions or fix bugs on the [issue tracker](https://github.com/lsshu/wechat/issues).
3. Contribute new features or update the wiki.

_The code contribution process is not very formal. You just need to make sure that you follow the PSR-0, PSR-1, and PSR-2 coding guidelines. Any new code contributions must be accompanied by unit tests where applicable._

## License

MIT