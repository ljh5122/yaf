<?php

namespace Error;

/**
 * 错误码设置类
 */
class CodeConfigModel {

    /**
     * 获取错误码配置
     */
    public static function getCodeConfig() {
        return array(
            '10010' => '测试输出错误',
            '10020' => '用户名不存在',
            '10030' => '类文件加载失败',
        );
    }
}