# 介绍

后台框架，实现Rbac权限控制，后端基于Hyperf框架，前端基于vue+element-ui，实现前后的分离，可快速搭建后台框架。


# 安装


```bash
$ cd path/to/install

$ composer install

$ cp .env.example .env

$ php bin/hyperf.php init  #初始化

$ php bin/hyperf.php start #启动
```

这将在端口“9501”上启动cli服务器，并将其绑定到所有网络接口。然后，您可以访问以下网站：`http://localhost:9501/`