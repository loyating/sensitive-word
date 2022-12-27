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
namespace Loyating\SensitiveWord;

use Hyperf\Contract\ConfigInterface;

class SensitiveWordFilter
{
    public const MATCH_TYPE_MIN = 1; // 最小匹配规则  默认

    public const MATCH_TYPE_MAX = 2; // 最大匹配规则

    protected array $config;

    protected TreeNode $treeNode;

    public function __construct(ConfigInterface $config)
    {
        $this->config = $config->get('sensitiveword', []);

        $this->build();
    }

    private function __clone()
    {
    }

    public function build(): void
    {
        $this->initTreeNode();

        if (! empty($this->config['file']) && file_exists($this->config['file'])) {
            foreach ($this->readLineFromFile($this->config['file']) as $word) {
                $this->buildTree(trim($word));
            }
        }

        if (! empty($this->config['words'] ?? [])) {
            foreach ($this->config['words'] as $word) {
                $this->buildTree(trim($word));
            }
        }
    }

    /**
     * 检测文本中的敏感词.
     * @param string $content 待检测内容
     */
    public function search(string $content): array
    {
        $matchType = (int) ($this->config['match_type'] ?? self::MATCH_TYPE_MIN);
        $wordNum = (int) ($this->config['match_num'] ?? 0);

        $contentLength = mb_strlen($content, 'utf-8');
        $badWordList = [];
        for ($i = 0; $i < $contentLength; ++$i) {
            if ($this->checkDisturbance(mb_substr($content, $i, 1, 'utf-8'))) {
                continue;
            }

            $matchCount = 0;
            $flag = false;
            $tempMap = $this->treeNode;

            for ($j = $i; $j < $contentLength; ++$j) {
                $char = mb_substr($content, $j, 1, 'utf-8');

                if ($this->checkDisturbance($char)) {
                    $matchCount > 0 && ++$matchCount;
                    continue;
                }

                $nowMap = $tempMap->get($char);

                if (empty($nowMap)) {
                    break;
                }

                $tempMap = $nowMap;
                ++$matchCount;

                if ($nowMap->get('ending') === false) {
                    continue;
                }

                $flag = true;

                if ($matchType === self::MATCH_TYPE_MIN) {
                    break;
                }
            }

            if (! $flag) {
                $matchCount = 0;
            }

            if ($matchCount <= 0) {
                continue;
            }

            $badWordList[] = mb_substr($content, $i, $matchCount, 'utf-8');

            if ($wordNum > 0 && count($badWordList) == $wordNum) {
                return $badWordList;
            }

            $i = $i + $matchCount - 1;
        }
        return $badWordList;
    }

    /**
     * 替换敏感字符.
     */
    public function replace(string $content, string $replaceChar = '*', bool $isEqualLength = true): string
    {
        if ($badWordList = $this->search($content)) {
            $replaceAfterStr = $replaceChar;
            foreach ($badWordList as $badWord) {
                if ($isEqualLength === true) {
                    $replaceAfterStr = $this->repalceEqualLength($badWord, $replaceChar);
                }
                $content = str_replace($badWord, $replaceAfterStr, $content);
            }
        }

        return $content;
    }

    /**
     * 标记敏感字符.
     */
    public function mark(string $content): string
    {
        $sTag = $this->config['stag'] ?? '';
        $eTag = $this->config['etag'] ?? '';

        if (! $sTag || ! $eTag) {
            return $content;
        }

        if ($badWordList = $this->search($content)) {
            foreach ($badWordList as $badWord) {
                $content = str_replace($badWord, $sTag . $badWord . $eTag, $content);
            }
        }

        return $content;
    }

    /**
     * 被检测内容是否合法.
     * @return bool true表示合法
     */
    public function isLegal(string $content): bool
    {
        $contentLength = mb_strlen($content, 'utf-8');

        for ($i = 0; $i < $contentLength; ++$i) {
            if ($this->checkDisturbance(mb_substr($content, $i, 1, 'utf-8'))) {
                continue;
            }

            $matchFlag = 0;
            $tempMap = $this->treeNode;

            for ($j = $i; $j < $contentLength; ++$j) {
                $keyChar = mb_substr($content, $j, 1, 'utf-8');

                if ($this->checkDisturbance($keyChar)) {
                    continue;
                }

                $nowMap = $tempMap->get($keyChar);

                if (empty($nowMap)) {
                    break;
                }

                $tempMap = $nowMap;
                ++$matchFlag;

                if ($nowMap->get('ending') === false) {
                    continue;
                }

                return false;
            }

            if ($matchFlag <= 0) {
                continue;
            }

            $i = $i + $matchFlag - 1;
        }
        return true;
    }

    protected function readLineFromFile(string $filepath): \Generator
    {
        $fp = fopen($filepath, 'r');
        while (! feof($fp)) {
            yield fgets($fp);
        }
        fclose($fp);
    }

    /**
     * 将单个敏感词构建成树结构.
     */
    protected function buildTree(string $word = ''): void
    {
        if ($word === '') {
            return;
        }
        $tree = $this->treeNode;

        $wordLength = mb_strlen($word, 'utf-8');
        for ($i = 0; $i < $wordLength; ++$i) {
            $keyChar = mb_substr($word, $i, 1, 'utf-8');

            $tempTree = $tree->get($keyChar);

            if ($tempTree) {
                $tree = $tempTree;
            } else {
                $newTree = new TreeNode();
                $newTree->put('ending', false);

                $tree->put($keyChar, $newTree);
                $tree = $newTree;
            }

            if ($i == $wordLength - 1) {
                $tree->put('ending', true);
            }
        }
    }

    /**
     * 干扰因子检查.
     */
    protected function checkDisturbance(string $char): bool
    {
        return in_array($char, $this->config['disturbance'] ?? []);
    }

    /**
     * 替换同等长度字符.
     */
    protected function repalceEqualLength(string $word, string $char): string
    {
        return str_repeat($char, mb_strlen($word, 'utf-8'));
    }

    private function initTreeNode(): void
    {
        if (isset($this->treeNode)) {
            $this->treeNode->clear();
        } else {
            $this->treeNode = new TreeNode();
        }
    }
}
