<?php

/**
 * 当有未捕获的异常, 则控制流会流到这里
 */
class ErrorController extends \Yaf\Controller_Abstract {

    public function init() {
        \Yaf\Dispatcher::getInstance()->disableView();
    }

    public function errorAction($exception) {
        $code = $exception->getCode();
        $codeConfig  = \Error\CodeConfigModel::getCodeConfig();
        $message = isset($codeConfig[$code]) ? $codeConfig[$code] : $exception->getMessage();
        json_return(['code' => $code, 'message' => $message]);
        return false;
    }
}