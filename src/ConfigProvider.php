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

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => [
            ],
            'annotations' => [
                'scan' => [
                    'paths' => [
                        __DIR__,
                    ],
                ],
            ],
            'publish' => [
                [
                    'id' => 'sensitiveword',
                    'description' => 'the config for sensitiveword', // 描述
                    // 建议默认配置放在 publish 文件夹中，文件命名和组件名称相同
                    'source' => __DIR__ . '/../publish/sensitiveword.php',  // 对应的配置文件路径
                    'destination' => BASE_PATH . '/config/autoload/sensitiveword.php', // 复制为这个路径下的该文件
                ],
            ],
        ];
    }
}
