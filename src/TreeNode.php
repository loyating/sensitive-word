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

class TreeNode
{
    public array $hashTable = [];

    public function put(mixed $key, mixed $value): void
    {
        $this->hashTable[$key] = $value;
    }

    /**
     * 根据key获取对应的value.
     */
    public function get(mixed $key): mixed
    {
        return $this->hashTable[$key] ?? null;
    }

    /**
     * 删除指定key的键值对.
     *
     * @param int|string $key
     */
    public function remove(mixed $key): mixed
    {
        $tmpNode = [];
        if (array_key_exists($key, $this->hashTable)) {
            $tempValue = $this->hashTable[$key];
            while ($currentValue = current($this->hashTable)) {
                if (! (key($this->hashTable) == $key)) {
                    $tmpNode[key($this->hashTable)] = $currentValue;
                }
                next($this->hashTable);
            }
            $this->hashTable = $tmpNode;
            return $tempValue;
        }
        return null;
    }

    public function keys(): array
    {
        return array_keys($this->hashTable);
    }

    public function values(): array
    {
        return array_values($this->hashTable);
    }

    public function fromTreeNode(TreeNode $node): void
    {
        if (! $node->isEmpty() && $node->size() > 0) {
            $keys = $node->keys();
            foreach ($keys as $key) {
                $this->put($key, $node->get($key));
            }
        }
    }

    public function clear(): void
    {
        $this->hashTable = [];
    }

    public function hasValue(string|int $value): bool
    {
        while ($curValue = current($this->hashTable)) {
            if ($curValue == $value) {
                return true;
            }
            next($this->hashTable);
        }
        return false;
    }

    public function hasKey(string|int $key): bool
    {
        return array_key_exists($key, $this->hashTable);
    }

    public function size(): int
    {
        return count($this->hashTable);
    }

    public function isEmpty(): bool
    {
        return count($this->hashTable) === 0;
    }
}
