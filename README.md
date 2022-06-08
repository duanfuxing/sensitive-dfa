# 敏感词过滤

自定义敏感词过滤,提供两种方式【文件,数组】,适用于论坛、弹幕、角色名称等等.

## 安装

```bash
$ composer require duan617/sensitive-dfa:1.0
```

## 使用

```php
<?php

use duan617\sensitive\dfa\SensitiveDfa;

$path = '敏感词汇文件路径';
$content = '这是待检测过滤的文本';
$sensitiveWords = ['词汇1','词汇2'];
$replaceStr = '***';


$DFA = SensitiveDfa::init();

// 文件加载词汇模式
$handle = $DFA->setTreeByFile($path);

// 数组加载词汇模式
$handle = $DFA->setTree($sensitiveWords);

$result = $handle->replace($content, $replaceStr);
dd($result);


// 缓存到Redis中直接使用即可
$handle = SensitiveDfa::init()->setTreeByFile($path);
$redis = new Redis();
$redis->set('DFA_handle',$handle);
$DFA_handle = $redis->get('DFA_handle');
$result = $DFA_handle->replace($content, $replaceStr);

```