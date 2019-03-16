yaf框架应用
===============

一个基于yaf框架的应用，其主要特性包括：

 + 实现了数据模型
 + 引入tp5的缓存模块
 + 实现了阿里云oss对象存储
 + 实现了微信公众号基本接口
 + 实现了微信小程序数据统计接口
 + 实现了验证码
 + 添加了一些共用方法
 + REST支持


php.ini中 yaf 扩展配置
[yaf]
yaf.environ = develop
yaf.library = NULL
yaf.cache_config = 0
yaf.name_suffix = 1
yaf.name_separator = ""
yaf.forward_limit = 5
yaf.use_namespace = 1
yaf.use_spl_autoload = 0
