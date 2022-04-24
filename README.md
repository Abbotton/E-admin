<div align="center">
    <img src="https://www.ex-admin.com/cms/logo.jpg" height="80"> 
</div>
<br>

本仓库Fork自[Ex-Admin](https://github.com/rocky-git/E-admin), 非常感谢`Ex-Admin`原作者开发这么优秀的后台框架，但是由于项目业务场景以及设计思路的不同，`Ex-Admin`的一些功能无法满足我这边的业务需要，所以我单独切出来一个分支进行自定义开发。

之前给`Ex-Admin`提交过的PR还有两个在那里挂着呢，所以暂时不计划提交PR了，下面会列出我做过的一些改动，如果正好有您需要的功能点，可以直接使用本仓库的`dev`分支或者查看提交记录自行集成，非常感谢。

- [x] 修复安装完毕后因为模型引入错误导致无法登录的问题；
- [x] 修改数据输出方式, 修复每次请求都触发`app\ExceptionHandle`的问题；
- [x] 增加插件重装功能，可以防止插件文件被直接删除，对于不上架应用市场的插件非常有用；
- [x] 增加插件排序功能，可以有效解决插件之间事件监听的问题，触发事件的插件排序需要靠前；
- [x] 修复多应用模式下创建新应用后因为模型引入错误导致无法登录的问题；
- [x] 修复多应用模式下，部分数据未按照多应用配置文件读取的问题；
- [x] 升级依赖的php版本；