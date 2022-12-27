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
namespace HyperfTest\SensitiveWord\Cases;

use Hyperf\Config\Config;
use Loyating\SensitiveWord\SensitiveWordFilter;

/**
 * @internal
 * @coversNothing
 */
class SensitiveWordFilterTest extends AbstractTestCase
{
    public function getInstance(array $config = []): SensitiveWordFilter
    {
        $tmp = array_merge([
            /* @phpstan-ignore-next-line */
            'file' => TESTS_PATH . '/sensitiveword.txt', // 词库路劲,每行一个词

            'words' => [], // 自定义敏感词列表

            // 干扰因子
            'disturbance' => [], // ['!','@','#',...]

            // 匹配规则,默认最小匹配
            'match_type' => SensitiveWordFilter::MATCH_TYPE_MIN,

            // 匹配敏感词数量上限,默认获取所有
            'match_num' => 0,

            // 敏感词标记
            'stag' => '', // 起始标签
            'etag' => '', // 结束标签
        ], $config);
        $config = new Config(['sensitiveword' => $tmp]);

        return new SensitiveWordFilter($config);
    }

    public function testDefault()
    {
        $dfa = $this->getInstance();

        $str = '脱敏测试，哈哈哈哈23对对对';
        $this->assertEquals(['23'], $dfa->search($str));
        $this->assertEquals('脱敏测试，哈哈哈哈**对对对', $dfa->replace($str));
        $this->assertEquals($dfa->mark($str), $str);
    }

    public function testDisturbance()
    {
        $dfa = $this->getInstance([
            'disturbance' => ['@'],
        ]);

        $str = '脱敏测试，哈哈哈哈2@3对对对';
        $this->assertEquals(['2@3'], $dfa->search($str));
        $this->assertEquals('脱敏测试，哈哈哈哈***对对对', $dfa->replace($str));
        $this->assertEquals($dfa->mark($str), $str);
    }

    public function testMark()
    {
        $dfa = $this->getInstance([
            'stag' => '{',
            'etag' => '}',
        ]);

        $str = '脱敏测试，哈哈哈哈23对对对';
        $this->assertEquals(['23'], $dfa->search($str));
        $this->assertEquals('脱敏测试，哈哈哈哈**对对对', $dfa->replace($str));
        $this->assertEquals('脱敏测试，哈哈哈哈{23}对对对', $dfa->mark($str));
        $this->assertEquals(false, $dfa->isLegal($str));
    }

    public function testOther()
    {
        $str = '脱敏测试，哈哈哈哈2@34对对AB@C对';

        // 最小规则  无干扰因子
        $dfa = $this->getInstance([
            'match_type' => SensitiveWordFilter::MATCH_TYPE_MIN,
        ]);
        $this->assertEquals([], $dfa->search($str));
        $this->assertEquals($dfa->replace($str), $str);

        // 最小规则  有干扰因子
        $dfa = $this->getInstance([
            'disturbance' => ['@'],
            'words' => ['AB'],
            'match_type' => SensitiveWordFilter::MATCH_TYPE_MIN,
        ]);
        $this->assertEquals(['2@3', 'AB'], $dfa->search($str));
        $this->assertEquals('脱敏测试，哈哈哈哈***4对对**@C对', $dfa->replace($str));

        // 最大规则
        $dfa = $this->getInstance([
            'disturbance' => ['@'],
            'match_type' => SensitiveWordFilter::MATCH_TYPE_MAX,
        ]);
        $this->assertEquals(['2@34', 'AB@C'], $dfa->search($str));
        $this->assertEquals('脱敏测试，哈哈哈哈****对对****对', $dfa->replace($str));

        // 最大规则  匹配上限1个
        $dfa = $this->getInstance([
            'disturbance' => ['@'],
            'match_type' => SensitiveWordFilter::MATCH_TYPE_MAX,
            'match_num' => 1,
        ]);
        $this->assertEquals(['2@34'], $dfa->search($str));
        $this->assertEquals('脱敏测试，哈哈哈哈****对对AB@C对', $dfa->replace($str));
    }
}
