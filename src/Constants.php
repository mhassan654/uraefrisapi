<?php

    // ADJUSTMENT TYPE - T131
if(!defined('ADJUSTTYPE_EXPIRED_GOODS')) define('ADJUSTTYPE_EXPIRED_GOODS', '101');
if(!defined('ADJUSTTYPE_DAMAGED_GOODS')) define('ADJUSTTYPE_DAMAGED_GOODS', '102');
if(!defined('ADJUSTTYPE_PERSONAL_USES')) define('ADJUSTTYPE_PERSONAL_USES', '103');
if(!defined('ADJUSTTYPE_EXPIRED_GOODS')) define('ADJUSTTYPE_EXPIRED_GOODS', '105');
if(!defined('ADJUSTTYPE_RAW_MATERIALS')) define('ADJUSTTYPE_RAW_MATERIALS', '104');


// OPERATION TYPE
if(!defined('OPERATIONTYPE_INCREASE')) define('OPERATIONTYPE_INCREASE', '101');
if(!defined('OPERATIONTYPE_DECREASE')) define('OPERATIONTYPE_DECREASE', '102');

// STOCK IN TYPE
if(!defined('STOCKINTYPE_IMPORT')) define('STOCKINTYPE_IMPORT', '101');
if(!defined('STOCKINTYPE_LOCAL_PURCHASE')) define('ADJUSTTYPE_EXPIRED_GOODS', '102');
if(!defined('STOCKINTYPE_MANUFACTURE')) define('ADJUSTTYPE_EXPIRED_GOODS', '103');

// INTERRUPTION TYPE CODE
if(!defined('INTERRUPTIONTYPE_CODE_NO_OF_DISCONNECTED')) define('INTERRUPTIONTYPE_CODE_NO_OF_DISCONNECTED', '101');
if(!defined('INTERRUPTIONTYPE_CODE_LOGIN_FAILURE')) define('INTERRUPTIONTYPE_CODE_LOGIN_FAILURE', '102');
if(!defined('INTERRUPTIONTYPE_CODE_RECIEPT_UPLOAD_FAILURE')) define('INTERRUPTIONTYPE_CODE_RECIEPT_UPLOAD_FAILURE', '103');
if(!defined('INTERRUPTIONTYPE_CODE_SYSTEM_RELATED_ERRORS')) define('INTERRUPTIONTYPE_CODE_SYSTEM_RELATED_ERRORS', '104');
if(!defined('INTERRUPTIONTYPE_CODE_PAPER_ROLL_REPLACEMENT')) define('INTERRUPTIONTYPE_CODE_PAPER_ROLL_REPLACEMENT', '105');

// OS TYPE
if(!defined('OSTYPE_LINUX')) define('OSTYPE_LINUX', '0');
if(!defined('OSTYPE_WINDOWS')) define('OSTYPE_WINDOWS', '1');

// IS LEAF NODE
if(!defined('ISLEAFNODE_YES')) define('ISLEAFNODE_YES', '101');
if(!defined('ISLEAFNODE_NO')) define('ISLEAFNODE_NO', '102');

// SERVICE MARK
if(!defined('SERVICEMARK_YES')) define('SERVICEMARK_YES', '101');
if(!defined('SERVICEMARK_NO')) define('SERVICEMARK_NO', '102');

// IS ZERO RATE
if(!defined('ISZERORATE_YES')) define('ISZERORATE_YES', '101');
if(!defined('ISZERORATE_NO')) define('ISZERORATE_NO', '102');

// IS EXEMPT
if(!defined('ISEXEMPT_YES')) define('ISEXEMPT_YES', '101');
if(!defined('ISEXEMPT_NO')) define('ISEXEMPT_NO', '102');

// ENABLE STATUS CODE
if(!defined('ENABLESTATUSCODE_ENABLE')) define('ENABLESTATUSCODE_ENABLE', '1');
if(!defined('ENABLESTATUSCODE_DISABLE')) define('ENABLESTATUSCODE_DISABLE', '0');
if(!defined('ADJUSTTYPE_EXPIRED_GOODS')) define('ADJUSTTYPE_EXPIRED_GOODS', '101');

// TAXPAYER TYPE
if(!defined('TAXPAYERTYPE_NORMAL_TAXPAYERTYPE')) define('TAXPAYERTYPE_NORMAL_TAXPAYERTYPE', '101');
if(!defined('TAXPAYERTYPE_EXEMPT_TAXPAYERTYPE')) define('TAXPAYERTYPE_EXEMPT_TAXPAYERTYPE', '102');
if(!defined('TAXPAYERTYPE_DEEMED_TAXPAYERTYPE')) define('TAXPAYERTYPE_DEEMED_TAXPAYERTYPE', '103');



if(!defined('TAXPAYERTYPE_DEEMED_TAXPAYERTYPE')) define('TAXPAYERTYPE_DEEMED_TAXPAYERTYPE', '103');


    // COMMODITY CATEGORY TAXPAYER TYPE
if(!defined('COMMODITYCATEGORYTAXPAYERTYPE_NORMAL_TAXPAYER')) define('COMMODITYCATEGORYTAXPAYERTYPE_NORMAL_TAXPAYER', '101');
if(!defined('COMMODITYCATEGORYTAXPAYERTYPE_EXEMPT_TAXPAYER')) define('COMMODITYCATEGORYTAXPAYERTYPE_EXEMPT_TAXPAYER', '102');
if(!defined('COMMODITYCATEGORYTAXPAYERTYPE_DEEMED_TAXPAYER')) define('COMMODITYCATEGORYTAXPAYERTYPE_DEEMED_TAXPAYER', '103');


    // GOODS STOCK LIMIT - T103
if(!defined('GOODS_STOCK_LIMIT_RESTRICTED')) define('GOODS_STOCK_LIMIT_RESTRICTED', '101');
if(!defined('GOODS_STOCK_LIMIT_UNLIMITED')) define('GOODS_STOCK_LIMIT_UNLIMITED', '102');

    // EXPORT INVOICE EXCISE DUTY
    'EXPORT_INVOICE_EXCISE_DUTY_YES' => "1",
    'EXPORT_INVOICE_EXCISE_DUTY_NO' => "0",

    // INVOICE TYPE - T106 & 108
    'INVOICE_TYPE_TICKET' => "1",
    'INVOICE_TYPE_CREDIT' => "2",
    'INVOICE_TYPE_TEMPORARY_TICKET' => "3",
    'INVOICE_TYPE_DEBIT_CORRESPONDING_DICTIONARY_TABLE' => "4",

    // INVOICE KIND
    'INVOICEKIND_INVOICE' => "1",
    'INVOICEKIND_RECEIPT' => "2",

    // IS INVALID
    'ISINVALID_OBSELETE_SIGN' => "1",
    'ISINVALID_OBSELETE' => "0",

    // DATASOURCES
    'DATASOURCES_EFD' => "101",
    'DATASOURCES_CS' => "102",
    'DATASOURCES_WEBSERVICE_API' => "103",
    'DATASOURCES_BS' => "104",

    // INVOICE TYPE - 107 & 108
    'INVOICETYPE_INVOICE' => "1",
    'INVOICETYPE_DEBIT' => "4",

    // INTERFACE CODE
    'INTERFACE_CODE_EXCHANGE_RATES' => "T126",

    // INVOICE TYPE - 108
    'INVOICE_INDUSTRY_CODE_GENERAL_INDUSTRY' => "101",
    'INVOICE_INDUSTRY_CODE_EXPORT' => "102",
    'INVOICE_INDUSTRY_CODE_IMPORT' => "103",
    'INVOICE_INDUSTRY_CODE_IMPORTED_SERVICE' => "104"