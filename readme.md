### 前端自动化：

#### 本项目为会小二基础项目框架-盗版必究

[链接地址](http://www.huixiaoer.com/index.html)

#### 环境依赖

```blade
1. php7.0+
```

#### 开发环境： 

```blade
1. 复制.env.example文件生成.env 文件

2. .ENV配置需要注意LOG_PATH对照自己本地路径与权限

3. 执行composer update 更新依赖包
```

#### 扩展说明

```

"gregwar/captcha": "1.*"     验证码
"jenssegers/mongodb": "^3.2",  MongoDB
"maatwebsite/excel": "~2.1.0", Excel读写
"sensorsdata/sa-sdk-php": "^1.7",  神策
"simplesoftwareio/simple-qrcode": "1.3.*",  二维码
"huixiaoer/hxevendor": "dev-master",   集成接受参数验证

集成Middleware ReturnCode 
集成日志收集功能
集成数据库相关配置
集成Redis相关配置
集成Kong接口相关配置（kong账户需要各自单独配置）
通用方法在Controller.php
```

####更新记录
```
2018-09-27 更新日志收集配置

09-29 删除mongo扩展  神策扩展
添加phpdotenv
添加aliyunMNS扩展与demo

```
