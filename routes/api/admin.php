<?php
/**
 * 后台管理接口路由
 */
use Illuminate\Support\Facades\Route;


Route::prefix('admin')
    ->namespace('Admin')
    ->middleware('admin')->group(function (){

        Route::post('login', 'SelfController@login');
        Route::post('logout', 'SelfController@logout');
        Route::get('self/rules', 'SelfController@getRules');
        Route::post('self/modifyPassword', 'SelfController@modifyPassword');

        Route::get('users', 'Auth\UserController@getList');
        Route::post('user/add', 'Auth\UserController@add');
        Route::post('user/edit', 'Auth\UserController@edit');
        Route::post('user/del', 'Auth\UserController@del');
        Route::post('user/changeStatus', 'Auth\UserController@changeStatus');
        Route::post('user/resetPassword', 'Auth\UserController@resetPassword');

        Route::get('groups', 'Auth\GroupController@getList');
        Route::post('group/add', 'Auth\GroupController@add');
        Route::post('group/edit', 'Auth\GroupController@edit');
        Route::post('group/del', 'Auth\GroupController@del');
        Route::post('group/changeStatus', 'Auth\GroupController@changeStatus');

        Route::get('rules', 'Auth\RuleController@getList');
        Route::get('rules/tree', 'Auth\RuleController@getTree');
        Route::post('rule/add', 'Auth\RuleController@add');
        Route::post('rule/edit', 'Auth\RuleController@edit');
        Route::post('rule/del', 'Auth\RuleController@del');
        Route::post('rule/changeStatus', 'Auth\RuleController@changeStatus');

        Route::get('members','UsersController@getList');
        Route::get('member/userlist','UsersController@userList');
        Route::get('member/download','UsersController@download');
        Route::get('member/identity','UsersController@identity');
        Route::any('member/batch_identity','UsersController@batchIdentity');
        Route::get('member/identity_download','UsersController@identityDownload');
        Route::get('member/identity_detail','UsersController@identityDetail');
        Route::post('member/identity_do','UsersController@identityDo');
        Route::post('users/unBind','UsersController@unBind');
        Route::get('users/getChangeBindList', 'UsersController@getChangeBindList');
        Route::get('users/getInviteUsersList', 'UsersController@getInviteUsersList');
        Route::post('users/changeBind', 'UsersController@changeBind');
        Route::get('users/getChangeBindRecordList', 'UsersController@getChangeBindRecordList');
        Route::get('users/getChangeBindPeopleRecordList', 'UsersController@getChangeBindPeopleRecordList');

        Route::get('area/tree', 'AreaController@getTree');

        Route::get('merchant/categories', 'MerchantCategoryController@getList');
        Route::get('merchant/category/tree', 'MerchantCategoryController@getTree');
        Route::get('merchant/category/getTreeWithoutDisable', 'MerchantCategoryController@getTreeWithoutDisable');
        Route::post('merchant/category/add', 'MerchantCategoryController@add');
        Route::post('merchant/category/edit', 'MerchantCategoryController@edit');
        Route::post('merchant/category/changeStatus', 'MerchantCategoryController@changeStatus');
        Route::post('merchant/category/del', 'MerchantCategoryController@del');

        Route::get('merchants', 'MerchantController@getList');
        Route::get('merchant/detail', 'MerchantController@detail');
        Route::post('merchant/audit', 'MerchantController@audit');
        Route::post('merchant/batch_audit', 'MerchantController@batchAudit');
        Route::get('merchant/download', 'MerchantController@downloadExcel');
        Route::post('merchant/changeStatus', 'MerchantController@changeStatus');
        Route::post('merchant/edit', 'MerchantController@edit');
        Route::get('/merchant/isPayToPlatform', 'MerchantController@isPayToPlatform');
        Route::get('/merchant/getElectronicContractList', 'MerchantController@getElectronicContractList');
        Route::get('/merchant/getElectronicContractDetail', 'MerchantController@getElectronicContractDetail');

        Route::get('merchant/audit/list', 'MerchantController@getAuditList');
        Route::get('merchant/audit/record/newest', 'MerchantController@getNewestAuditRecord');

        Route::get('merchant/pool', 'MerchantPoolController@getList');
        Route::get('merchant/pool/detail', 'MerchantPoolController@detail');

        Route::get('/operBizMembers/search', 'OperBizMemberController@search');

        Route::get('navigation/all', 'NavigationController@getAll');
        Route::post('navigation/add', 'NavigationController@add');
        Route::post('navigation/edit', 'NavigationController@edit');
        Route::post('navigation/sort', 'NavigationController@changeSort');
        Route::get('navigation/getAllTopMerchantCategories', 'NavigationController@getAllTopCategories');

        Route::get('cs/category/all', 'CsCategoryController@getAll');
        Route::get('cs/category/tree', 'CsCategoryController@getTree');
        Route::post('cs/category/add', 'CsCategoryController@add');
        Route::post('cs/category/edit', 'CsCategoryController@edit');
        Route::post('cs/category/changeStatus', 'CsCategoryController@changeStatus');

        Route::get('cs/merchants', 'CsMerchantController@getList');
        Route::get('cs/merchant/detail', 'CsMerchantController@detail');
        Route::post('cs/merchant/edit', 'CsMerchantController@edit');
        Route::get('cs/merchant/export', 'CsMerchantController@export');
        Route::post('cs/merchant/changeStatus', 'CsMerchantController@changeStatus');
        Route::get('cs/merchant/list', 'CsMerchantController@getList');

        Route::get('cs/merchant/audit/list', 'CsMerchantAuditController@getAuditList');
        Route::get('cs/merchant/audit/detail', 'CsMerchantAuditController@getAuditDetail');
        Route::post('cs/merchant/audit', 'CsMerchantAuditController@audit');
        Route::get('cs/merchant/audit/record/newest', 'CsMerchantAuditController@getNewestAuditRecord');

        Route::get('/tps/getBindInfo', 'TpsBindController@getBindInfo');
        Route::post('/tps/bindAccount', 'TpsBindController@bindAccount');
        Route::post('/tps/sendVerifyCode', 'TpsBindController@sendVerifyCode');

        Route::get('/country/list', 'CountryController@getList');

        Route::group([], base_path('routes/api/admin/goods.php'));
        Route::group([], base_path('routes/api/admin/oper.php'));
        Route::group([], base_path('routes/api/admin/oper_account.php'));
        Route::group([], base_path('routes/api/admin/miniprogram.php'));
        Route::group([], base_path('routes/api/admin/setting.php'));
        Route::group([], base_path('routes/api/admin/wallet.php'));
        Route::group([], base_path('routes/api/admin/bizer.php'));

        Route::get('settlement/platforms', 'SettlementPlatformController@getList');
        Route::get('settlement/allPlatforms', 'SettlementPlatformController@getList');
        Route::get('settlement/csPlatforms', 'SettlementPlatformController@getList');
        Route::get('settlement/download', 'SettlementPlatformController@downloadExcel');
        Route::get('settlement/csDownload', 'SettlementPlatformController@downloadExcel');
        Route::get('settlement/modifyStatus', 'SettlementPlatformController@modifyStatus');
        Route::get('settlement/reBatchAgain', 'SettlementPlatformController@reBatchAgain');


        Route::get('settlementPlatformBatches/list', 'SettlementPlatformBatchesController@getList');
        Route::get('settlementPlatformBatches/modifyStatus', 'SettlementPlatformBatchesController@modifyStatus');


        Route::get('settlement/getPlatformOrders', 'SettlementPlatformController@getSettlementOrders');
        Route::get('bank/list', 'BankController@getList');
        Route::post('bank/add', 'BankController@add');
        Route::post('bank/del', 'BankController@del');
        Route::post('bank/changeStatus', 'BankController@changeStatus');
        Route::post('bank/edit', 'BankController@edit');

        Route::get('statistics/list','StatisticsController@getList');
        Route::get('statistics/all_opers','StatisticsController@allOpers');
        Route::get('statistics/all_merchants','StatisticsController@allMerchants');
        Route::get('statistics/all_users','StatisticsController@allUsers');
        Route::get('statistics/export','StatisticsController@exportExcel');
        Route::get('statistics/getInviteUserRecords','StatisticsController@getInviteUserRecords');

        Route::get('versions', 'VersionController@getList');
        Route::get('version/detail', 'VersionController@detail');
        Route::post('version/add', 'VersionController@add');
        Route::post('version/edit', 'VersionController@edit');
        Route::post('version/del', 'VersionController@del');

        Route::get('/feeSplitting/getList', 'FeeSplittingController@getList');
        Route::post('/feeSplitting/ReFeeSplitting', 'FeeSplittingController@ReFeeSplitting');

        Route::get('payments', 'PaymentController@getList');
        Route::get('payment/detail', 'PaymentController@detail');
        Route::post('payment/add', 'PaymentController@add');
        Route::post('payment/edit', 'PaymentController@edit');
        Route::post('payment/del', 'PaymentController@del');

        Route::get('agentpays', 'AgentPayController@getList');
        Route::get('agentpay/detail', 'AgentPayController@detail');
        Route::post('agentpay/add', 'AgentPayController@add');
        Route::post('agentpay/edit', 'AgentPayController@edit');
        Route::post('agentpay/del', 'AgentPayController@del');

        Route::get('orders','OrderController@getList');
        Route::get('getOptions','OrderController@getOptions');
        Route::get('order/export','OrderController@export');


        Route::get('message/systems', 'MessageSystemController@getSystems');
        Route::post('message/addSystems', 'MessageSystemController@addMessage');

        Route::get('trade_records','OrderController@platformTradeRecords');
        Route::get('trade_records/export','OrderController@platformTradeRecordsExport');
        Route::get('trade_records_daily','PlatformController@getDailyList');
        Route::get('trade_records_daily/export','PlatformController@dailyListExport');
    });
