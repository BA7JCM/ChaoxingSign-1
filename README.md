# ⭐ ChaoxingSign | 超星学习通签到
PHP 版超星自动签到，支持多用户，二次开发便捷！

# 🧀 使用方法
1. 下载源码：

    直接下载：https://github.com/PrintNow/ChaoxingSign/archive/master.zip
    
    克隆源码：`git clone https://github.com/PrintNow/ChaoxingSign`

2. 🚀 运行
    1. 上传到**网站根目录**运行
    
        然后访问 `http://你的域名/main.php?account=你的超星账号&password=你的超星密码`
    
    2. 或者使用**命令行**运行
       ```
       php main.php -A "你的超星账号" -P "你的超星密码"
       ```

3. ⚙ 实现自动签到
    > 推荐大于等于 **10 分钟** 执行一次，避免出现异常
    > 
    > 我已经硬编仅能在每天的 08:00 ~ 22:00 之间运行，
    > 如果要取消或修改这一限制，请删除或注释
    > `main.php` 第 7~9 行
    1. 如果以**网页方式**运行，定时监控 `http://你的域名/main.php?account=你的超星账号&password=你的超星密码` 即可
    2. 如果使用**命令行方式**运行，添加 `crontab` 任务即可，具体添加 `crontab 任务` 方法可以网上搜。
    每天 早上8点到晚上22点之间，每10分钟签到一次 crontab 表达式：`0 */10 8-22 * * * *`
    
# 🙇‍ 感谢
> 本项目的实现参考了以下文章

- https://www.z2blog.com/index.php/learn/423.html
- https://www.z2blog.com/index.php/default/459.html

> 本项目中使用到的 `Selector.php` 来自 [PHPSpider](https://github.com/owner888/phpspider) 