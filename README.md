<p align="center"><img src="https://m-finder.github.io/images/avatar.jpeg"></p>
<p align="center">
<img src="https://img.shields.io/badge/Author-m--finder-red">
<img src="https://img.shields.io/badge/PHP->=8.4-red">
<a href="https://packagist.org/packages/wu/giorgio-generater"><img src="https://img.shields.io/badge/License-MIT-green" alt="License"></a>
</p>

本项目基于 [Laravel-Zero](https://laravel-zero.com), 目标是用 PHP 构建一个轻量的跨平台代码生成工具，主要功能包括：

- [ ] Laravel: Model,Controller,Request,Migrate
- [ ] Java: Entity,Mapper,Service
- [ ] Db migrate
- [ ] Db doc export

## 说明

> 项目还处于前期开发阶段！
>
> 主要功能开发完成后，会先通过 box 将项目打包成 phar 文件，然后再通过 static-php-cli 打包成对应平台的二进制文件。
>
>最终将通过在命令行运行 gen make:xxx、gen export:pdf 等命令来完成上述功能。

## 使用方式

#### 生成 Model

- 在当前文件夹生成 Models/TestModel 文件：
    - `gen make:model TestModel`
- 在指定文件夹生成 Models/TestModel 文件：
    - `gen make:model TestModel -p /opt/homebrew/var/www/Models`
    - `gen make:model TestModel --path=/opt/homebrew/var/www/Models`
- 在当前文件夹重新生成 Models/TestModel 文件：
    - `gen make:model TestModel -f`
    - `gen make:model TestModel --fource`

## PR 邀请

如果您对本项目有点兴趣，或者有更好的想法，欢迎随时提交 PR 和 ISSUE。

## License

Giorgio-Generater is an open-source software licensed under the MIT license.