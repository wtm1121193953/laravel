<?php
/**
 * 运营中心接口路由
 */

use App\Http\Middleware\User\UserLoginFilter;
use Illuminate\Support\Facades\Route;

Route::prefix('app/user')
    ->namespace('UserApp')
    ->middleware('user_app')->group(function () {

        Route::get('version/last', 'VersionController@last');
        Route::get('versions', 'VersionController@getList');
        Route::get('settings', 'SettingController@settings');

        Route::any('sms/verify_code', 'SmsController@sendVerifyCode');

        Route::any('navigation/index', 'NavigationController@index');

        Route::any('login', 'LoginController@login');
        Route::any('logout', 'LoginController@logout');

        Route::any('user/info', 'UserController@getInfo')->middleware(UserLoginFilter::class);
        Route::post('user/setNameAndAvatar', 'UserController@setAvatar')->middleware(UserLoginFilter::class);

        Route::get('area/tree', 'AreaController@getTree');
        Route::get('area/cities/groupByFirstLetter', 'AreaController@getCityListGroupByFirstLetter');
        Route::get('area/cities/withHot', 'AreaController@getCitiesWithHot');
        Route::get('area/getByGps', 'AreaController@getAreaByGps');
        Route::get('area/search', 'AreaController@searchCityList');

        Route::get('merchant/categories/tree', 'MerchantCategoryController@getTree');
        Route::get('merchants', 'MerchantController@getList');
        Route::get('merchant/detail', 'MerchantController@detail');
        Route::get('merchant/followStatus', 'MerchantFollowController@modifyFollowStatus')->middleware(UserLoginFilter::class);
        Route::get('merchant/followLists', 'MerchantFollowController@userFollowList')->middleware(UserLoginFilter::class);
        Route::get('merchant/getDishesCategoryAndGoods', 'DishesController@getDishesCategory'); // 废弃
        Route::get('merchant/getHotDishesGoods', 'DishesController@getHotDishesGoods'); // 废弃
        Route::post('merchant/dishesOrder', 'DishesController@add')->middleware(UserLoginFilter::class); // 废弃
        Route::get('merchant/dishesDetail', 'DishesController@detail')->middleware(UserLoginFilter::class); // 废弃

        Route::get('dishes/category', 'DishesController@getDishesCategory');
        Route::get('dishes/goods', 'DishesController@getDishesGoods');
        Route::get('dishes/hot', 'DishesController@getHotDishesGoods');
        Route::post('dishes/add','DishesController@add')->middleware(UserLoginFilter::class);
        Route::get('dishes/detail','DishesController@detail')->middleware(UserLoginFilter::class);

        Route::get('goods', 'GoodsController@getList');
        Route::get('goods/detail', 'GoodsController@detail');

        Route::get('orders', 'OrderController@getList')->middleware(UserLoginFilter::class);
        Route::get('userOrders', 'OrderController@getOrderList')->middleware(UserLoginFilter::class);

        Route::get('order/detail', 'OrderController@detail')->middleware(UserLoginFilter::class);
        Route::post('order/buy', 'OrderController@buy')->middleware(UserLoginFilter::class);
        Route::post('order/pay', 'OrderController@pay')->middleware(UserLoginFilter::class);
        Route::post('order/refund', 'OrderController@refund')->middleware(UserLoginFilter::class);
        Route::post('order/scanQrcodePay', 'OrderController@scanQrcodePay')->middleware(UserLoginFilter::class);
        Route::post('order/dishesBuy','OrderController@dishesBuy')->middleware(UserLoginFilter::class);

        Route::get('invite/qrcode', 'InviteChannelController@getInviteQrcode')->middleware(UserLoginFilter::class);
        Route::get('invite/getInviterByInviteChannelId', 'InviteChannelController@getInviterByChannelId');
        Route::post('invite/bindInviter', 'InviteChannelController@bindInviter')->middleware(UserLoginFilter::class);
        Route::get('invite/getInviteUserStatistics', 'InviteChannelController@getInviteUserStatistics')->middleware(UserLoginFilter::class);


        Route::get('invite/getInviterInfo', 'UnbindInviterController@getBindInfo')->middleware(UserLoginFilter::class);
        Route::post('invite/unbind', 'UnbindInviterController@unbind')->middleware(UserLoginFilter::class);

        Route::get('scene/info', 'SceneController@getSceneInfo');

        Route::get('credit/payAmountToCreditRatio', 'CreditController@payAmountToCreditRatio')->middleware(UserLoginFilter::class);//废弃
        Route::get('credit/getCreditList', 'CreditController@getCreditList')->middleware(UserLoginFilter::class);
        Route::get('credit/getUserCredit', 'CreditController@getUserCredit')->middleware(UserLoginFilter::class);
        Route::get('credit/getConsumeQuotaRecordList', 'CreditController@getConsumeQuotaRecordList')->middleware(UserLoginFilter::class);

        Route::get('merchant/dishesCategory', 'MerchantDishesController@getDishesCategory');
        Route::get('merchant/dishesGoods', 'MerchantDishesController@getDishesGoods');

        //app钱包接口
        Route::get('wallet/getWalletInfo', 'WalletController@getWallet')->middleware(UserLoginFilter::class);;
        Route::get('wallet/getBills', 'WalletController@getBills')->middleware(UserLoginFilter::class);;
        Route::get('wallet/getBillDetail', 'WalletController@getBillDetail')->middleware(UserLoginFilter::class);;
        Route::get('wallet/getConsumeQuotas', 'WalletController@getConsumeQuotas')->middleware(UserLoginFilter::class);;
        Route::get('wallet/getConsumeQuotaDetail', 'WalletController@getConsumeQuotaDetail')->middleware(UserLoginFilter::class);;
        Route::post('wallet/confirmPassword', 'WalletController@confirmPassword')->middleware(UserLoginFilter::class);
        Route::post('wallet/checkVerifyCode', 'WalletController@checkVerifyCode')->middleware(UserLoginFilter::class);
        Route::post('wallet/changePassword', 'WalletController@changePassword')->middleware(UserLoginFilter::class);
        Route::post('wallet/withdraw', 'WalletWithdrawController@withdraw')->middleware(UserLoginFilter::class);
        // todo 旧接口兼容, 之后需要删掉
        Route::post('wallet/withDraw', 'WalletWithdrawController@withdraw')->middleware(UserLoginFilter::class);
        Route::get('wallet/getWithdrawConfig', 'WalletWithdrawController@getWithdrawConfig')->middleware(UserLoginFilter::class);
        // todo 旧接口兼容, 之后需要删掉
        Route::get('wallet/getWithDrawConfig', 'WalletWithdrawController@getWithdrawConfig')->middleware(UserLoginFilter::class);
        Route::get('wallet/getTpsConsume', 'WalletController@getTpsConsume')->middleware(UserLoginFilter::class);
        Route::get('wallet/getTpsConsumeQuotasList', 'WalletController@getTpsConsumeQuotasList')->middleware(UserLoginFilter::class);//废弃
        Route::get('wallet/getTpsConsumeQuotaDetail', 'WalletController@getTpsConsumeQuotaDetail')->middleware(UserLoginFilter::class);//废弃
        Route::get('wallet/getUserFeeSplittingRatioToSelf', 'WalletController@getUserFeeSplittingRatioToSelf');
        Route::get('wallet/getTpsCreditStatistics', 'WalletController@getTpsCreditStatistics')->middleware(UserLoginFilter::class); //未录入接口文档，废弃
        Route::get('wallet/getTpsCreditList', 'WalletController@getTpsCreditList')->middleware(UserLoginFilter::class);//未录入接口文档，废弃

        //app银行卡接口
        Route::post('bankcard/addCard', 'BankCardsController@addCard')->middleware(UserLoginFilter::class);
        Route::post('bankcard/changDefault', 'BankCardsController@changDefault')->middleware(UserLoginFilter::class);
        Route::post('bankcard/deleteCard', 'BankCardsController@delCard')->middleware(UserLoginFilter::class);
        Route::get('bankcard/getCardsList', 'BankCardsController@getCardsList')->middleware(UserLoginFilter::class);
        Route::get('bankcard/getSupportBankList', 'BankController@getList');

        //Tps绑定接口
        Route::get('tps/getBindInfo', 'TpsBindController@getBindInfo')->middleware(UserLoginFilter::class);
        Route::post('tps/bindAccount', 'TpsBindController@bindAccount')->middleware(UserLoginFilter::class);


        Route::post('identity/record/addRecord', 'UserIdentityAuditRecordController@addRecord')->middleware(UserLoginFilter::class);
        Route::post('identity/record/modRecord', 'UserIdentityAuditRecordController@modRecord')->middleware(UserLoginFilter::class);
        Route::get('identity/record/getRecord', 'UserIdentityAuditRecordController@getRecord')->middleware(UserLoginFilter::class);

        Route::get('message/notices', 'MessageController@getNotices')->middleware(UserLoginFilter::class);
        Route::get('message/noticesNum', 'MessageController@getNeedViewNum')->middleware(UserLoginFilter::class);
        Route::get('message/noticesDetail', 'MessageController@getNoticeDetail')->middleware(UserLoginFilter::class);
        Route::get('message/systemDetail', 'MessageController@getSystemDetail')->middleware(UserLoginFilter::class);
        Route::get('country/list', 'CountryController@getList');

        //收货地址
        Route::post('cs/address/add','CsUserAddressController@addUserAddresses')->middleware(UserLoginFilter::class);
        Route::get('cs/address/getList','CsUserAddressController@getAddresses')->middleware(UserLoginFilter::class);
        Route::post('cs/address/edit','CsUserAddressController@editAddress')->middleware(UserLoginFilter::class);
        Route::post('cs/address/delete','CsUserAddressController@deleteAddress')->middleware(UserLoginFilter::class);
        Route::get('cs/address/delivery/setting','CsUserAddressController@getDeliverySetting')->middleware(UserLoginFilter::class);
        Route::get('cs/address/merchant/setting','CsUserAddressController@merchantSetting')->middleware(UserLoginFilter::class);

        //超市
        Route::get('cs/merchant/list','CsMerchantController@getList');
        Route::get('cs/merchant/category','CsMerchantController@getCategoryTree');
        Route::get('cs/merchant/goods','CsGoodsController@getAllGoods');
        Route::post('cs/dishes/add','OrderController@csOrderCreate')->middleware(UserLoginFilter::class);
        Route::post('cs/confirm_delivery','OrderController@confirmDelivery')->middleware(UserLoginFilter::class);
        Route::post('cs/order/del','OrderController@userDel')->middleware(UserLoginFilter::class);
        Route::post('cs/order/check','OrderController@checkGoodsStockAndReturnPrice')->middleware(UserLoginFilter::class);
    });

Route::prefix('app/user')
    ->middleware('user_app')->group(function () {
        Route::get('message/isShowRedDot', 'User\MessageController@isShowRedDot')->middleware(UserLoginFilter::class);
        Route::get('message/systems', 'Admin\MessageSystemController@getSystems')->middleware(UserLoginFilter::class);
        Route::get('payments/platform', 'Admin\PaymentController@getListByPlatform')->middleware(UserLoginFilter::class);
        Route::get('message/systems', 'Admin\MessageSystemController@getSystems')->middleware(UserLoginFilter::class);
        Route::post('message/behavior', 'User\MessageController@userBehavior')->middleware(UserLoginFilter::class);
        Route::get('message/redDotNum', 'User\MessageController@redDotNumList')->middleware(UserLoginFilter::class);
    });