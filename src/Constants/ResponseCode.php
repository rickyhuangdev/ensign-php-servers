<?php

declare(strict_types=1);

namespace Rickytech\Library\Constants;

use Hyperf\Constants\AbstractConstants;
use Hyperf\Constants\Annotation\Constants;

/**
 * @Constants
 */
#[Constants]
class ResponseCode extends AbstractConstants
{
    /**
     * @Message("Server Error！")
     */
    const SERVER_ERROR = 500;
    /**
     * @Message("success")
     */
    public const SUCCESS = 200;
    /**
     * @Message("error")
     */
    public const ERROR = 0;
}
