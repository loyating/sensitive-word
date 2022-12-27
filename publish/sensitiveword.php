<?php

declare(strict_types=1);
/**
 * This file is part of loyating/sensitive-word.
 *
 * @link     https://github.com/loyating/sensitive-word
 * @document https://github.com/loyating/sensitive-word/blob/master/README.md
 * @contact  loyating@foxmail.com
 * @license  https://github.com/loyating/sensitive-word/blob/master/LICENSE
 */
use Loyating\SensitiveWord\SensitiveWordFilter;

return [
    'file' => '', // 词库路劲,每行一个词

    'words' => [], // 自定义敏感词列表

    // 干扰因子
    'disturbance' => [], // ['!','@','#',...]

    /*
     * 匹配规则,默认最小匹配.
     * $words = ['AB','ABC'];
     * $str = 'ABC';
     * SensitiveWordFilter::MATCH_TYPE_MIN => AB
     * SensitiveWordFilter::MATCH_TYPE_MAX => ABC
     */
    'match_type' => SensitiveWordFilter::MATCH_TYPE_MIN,

    // 匹配敏感词数量上限,默认获取所有
    'match_num' => 0,

    // 敏感词标记 若不配置则原字符串返回
    'stag' => '', // 起始标签
    'etag' => '', // 结束标签
];
