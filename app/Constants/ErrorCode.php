<?php

declare(strict_types=1);

namespace App\Constants;

use Hyperf\Constants\AbstractConstants;
use Hyperf\Constants\Annotation\Constants;

/**
 * @Constants
 */
class ErrorCode extends AbstractConstants
{
    /**
     * @Message("NO AUTH！")
     */
    const ERR_NOT_ACCESS = 1000;
    
    /**
     * @Message("System Error！")
     */
    const SERVER_ERROR = 50000;

    /**
     * @Message("Auth Error！")
     */
    const AUTH_ERROR = 40101;

    /**
     * @Message("Invalid token！")
     */
    const TOKEN_ERROR = 40102;

    /**
     * @Message("Token has expired！")
     */
    const TOKEN_EXPIRED = 40103;

    /**
     * @Message("Invalid Store！")
     */
    const STORE_ERROR = 40104;

    /**
     * @Message("Parameter value invalid!")
     */
    const PARAMETER_ERROR = 42201;

    /**
     * @Message("语法错误")
     */
    const ERR_SERVER= 201;
    /**
     * @Message("存在子节点不允许删除")
     */
    const ERR_NO_DEL = 101;

    /**
     * @Message("encodingAesKey 非法")
     */
    const ILLEGAL_AES_KEY = -41001;

    /**
     * @Message("iv 非法")
     */
    const ILLEGAL_IV = -41002;

    /**
     * @Message("aes 解密失败")
     */
    const ILLEGAL_BUFFER = -41003;

    /**
     * @Message("解密后得到的buffer非法")
     */
    const DECODE_BASE64_EEEOR = -41004;
}
