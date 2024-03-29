<?php

namespace App\Defined;

abstract class ResponseDefined
{
    /** 成功 */
    const SUCCESS = 0;

    // ------ 參數驗證預設錯誤碼 ------
    /** 參數驗證有誤 */
    const UNDEFINED_ARGUMENT = 100;

    // ------ 登入認證相關 ------
    /** 登入失敗 */
    const UNAUTHORIZED = 101;
    /** 憑證非法 */
    const TOKEN_INVALID = 102;
    /** Token過期 */
    const TOKEN_EXPIRED = 103;
    /** 驗證碼失效 */
    const VERIFY_CODE_EXPIRED = 104;
    /** 驗證碼錯誤 */
    const VERIFY_CODE_ERROR = 105;
    /** 驗證碼不允許為空 */
    const VERIFY_CODE_REQUIRED = 106;
    /** 該會員已驗證 */
    const VERIFY_HAS_PASSED = 107;

    // ------ 帳號註冊相關 ------
    /** 查無此帳號 */
    const USER_NOT_FOUND = 201;
    /** 信箱必填 */
    const EMAIL_REQUIRED = 202;
    /** 姓名必填 */
    const NAME_REQUIRED = 203;
    /** 姓名不得超過100字元 */
    const NAME_MAX = 204;
    /** 生日必填 */
    const BIRTHDAY_REQUIRED = 205;
    /** 生日格式非法 */
    const BIRTHDAY_INVALID = 206;
    /** 性別必選 */
    const GENDER_REQUIRED = 207;
    /** 性別非法 */
    const GENDER_INVALID = 208;
    /** 暱稱必填 */
    const NICKNAME_REQUIRED = 209;
    /** 信箱格式非法 */
    const EMAIL_INVALID = 210;
    /** 信箱已被使用 */
    const EMAIL_HAS_EXISTS = 211;
    /** 密碼必填 */
    const PASSWORD_REQUIRED = 212;
    /** 密碼長度過短 */
    const PASSWORD_MIN = 213;

    // ------ 文章相關 ------
    /** 查無文章 */
    const POST_NOT_FOUND = 301;
    /** 已按過讚 */
    const POST_HAS_LIKE = 302;
    /** 未按過讚 */
    const POST_NOT_LIKE = 303;

    // ------ 任務相關 ------
    /** 今日已簽到 */
    const TODAY_HAS_SIGNED = 401;

    // ------ 公告相關 ------
    /** 查無公告 */
    const ANNOUNCEMENT_NOT_FOUND = 501;

    // ------ Banner相關 ------
    /** 查無Banner */
    const BANNER_NOT_FOUND = 601;

    // ------ 約會相關 ------
    /** 暫時無法新增約會 */
    const DATE_PUBLISH_NOT_ALLOW = 701;
    /** 查無約會 */
    const DATE_NOT_FOUND = 702;
    /** 不允許報名自己的約會 */
    const DATE_NOT_ALLOW_SELF = 703;
    /** 該約會已配對完畢 */
    const DATE_HAS_MATCHED = 704;
    /** 約會已結束 */
    const DATE_HAS_CLOSED = 705;
    /** 不可重複報名 */
    const DATE_HAS_SIGNUP = 706;
    /** 此配對人無報名該約會 */
    const DATE_MATCH_NOT_EXISTS = 707;
    /** 權限不符 (必須是發佈人才有權限) */
    const PERMISSION_DENIED = 708;

    // ------ LIKE配對相關 ------
    /** 雙方已配對 */
    const USER_HAS_MATCHED = 801;
    /** 邀請已發送 */
    const MATCH_HAS_SEND = 802;
    /** 查無配對資料 */
    const MATCH_NOT_FOUND = 803;
    /** 與該對象非好友關係 */
    const USER_NOT_FRIEND = 804;
    /** 對象必填 */
    const TARGET_IS_REQUIRED = 805;
    /** 不可邀請自己 */
    const NOT_ALLOW_SEND_SELF = 806;
    /** 不可刪除自己 */
    const NOT_ALLOW_REMOVE_SELF = 807;

    // ------ Admin相關 ------
    /** 查無後台人員 */
    const ADMIN_NOT_FOUND = 901;
    /** 當舊密碼不為空，則新密碼必填 */
    const NEW_PASSWORD_REQUIRED = 902;
    /** 新密碼不吻合 */
    const NEW_PASSWORD_NOT_MATCH = 903;
    /** 當新密碼不為空，則舊密碼必填 */
    const OLD_PASSWORD_REQUIRED = 904;
    /** 舊密碼不符合 */
    const OLD_PASSWORD_VERIFY_FAIL = 905;
    /** 帳號必填 */
    const USERNAME_REQUIRED = 906;
    /** 帳號已存在 */
    const USERNAME_HAS_EXISTS = 907;
    /** 密碼不吻合 */
    const PASSWORD_NOT_MATCH = 908;

    // ------ 通知相關 ------
    /** 查無通知 */
    const NOTIFICATION_NOT_FOUND = 1001;
    /** 本則通知已閱讀 */
    const NOTIFICATION_HAS_READ = 1002;

    // ------ 錢包交易相關 ------
    /** 餘額不足 */
    const BALANCE_NOT_ENOUGH = 1101;
}
