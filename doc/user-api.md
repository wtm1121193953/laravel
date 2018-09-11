### 用户端小程序接口列表

[TOC]

##### 接口统一前缀地址: https://domain/api/user

##### 请求统一参数:

```
token (wxLogin接口除外)
```

##### 统一返回结果:

```
{
  code: 状态码 (0表示成功, 其他为失败),
  message: 返回信息(成功时一般为请求成功, 失败时为失败信息),
  data: 返回数据, 始终是一个对象(成功时存在, 失败时一般不存在, 除非特殊情况需要数据时才存在)
}
```


##### 全局状态码:

- 0  成功
- 500 未知错误
- 10000 接口不存在
- 10001 参数不合法
- 10003 未登录
- 10004 账号不存在
- 10005 密码错误
- 10006 账号已存在
- 10007  token无效或不存在
- 70010 微信openId不存在(此时需重新调用`wx.login`并调用/wxLogin接口以获取微信openId)
- 70011  微信appid不存在



> 用户端登陆机制 (即 token , openId, 用户账号之间的关系):
>
> ```
> 步骤:
> 1. token获取: 调用 wx.login, 获取code, 然后请求 /wxLogin 接口 (通过user agent 获取appid), 获取token, 并缓存在本地
> 2. 绑定openId 到账户: 通过 /login 接口, 将用户手机号(即用户实际账号)与openId相关联
> 流程:
> 首次进入小程序后, 首先 获取token, 然后 登陆时绑定openId 到账户
> 每次进入小程序, 首先获取本地token缓存, 若不存在则 获取token , 获取后若不存在用户信息,则需要登录(即步骤2)
> 后续操作:
> 若收到后台返回的状态码为 10007 和 70010 , 则需要执行步骤1
> 若收到后台返回的状态码为 10003 , 则需要执行步骤2
> ```



#### 登陆相关

- [ ] 微信登陆接口

  接口地址 `POST`  `/wxLogin`

  参数: 

  ```
  code wx.login 获取到的code
  ```

  返回值: 

  ```
  data: {
    token: 用户令牌,
    userInfo: 用户信息, 微信openId已绑定用户的情况下返回, 测试时code传值为user时返回用户信息: {
        name: 用户名称
        mobile: 手机号
    }
  }
  ```



- [ ] 登陆接口(绑定手机号)

  接口地址: `POST` `/login`

  参数:

  ```
  mobile 手机号
  verify_code 验证码
  ```

  返回值

  ```
  data: {
    userInfo: 用户信息 {
        name: 用户名称,
        mobile: 手机号
    }
  }
  ```



- [ ] 使用场景ID快捷登陆 (用于跳转到一个新的小程序时用户不需要重复登陆操作)

  接口地址: POST `/loginWithSceneId`

  参数: 

  ```
  token 通过wxLogin接口获取到的token
  sceneId: 在onLoad中获取到的scene值
  ```

  返回

  ```
  data: {
      userInfo: 用户信息 {
          name: 用户名称,
          mobile: 手机号,
      },
      payload: { 场景值所携带的数据
          user_id: 用户ID,
          order_no: 订单号
      }
  }
  ```

  ​



- [ ] 短信接口 （测试验证码使用6666）

  接口地址: `POST`  `/sms/verify_code`

  参数:

  ```
  mobile 手机号
  ```

  返回值: 

  ```
  data: {
    verify_code: 验证码, 测试时存在
  }
  ```



- [x] 获取地区列表(树形结构)

  接口地址: GET `/area/tree`

  参数: `tier ` 要获取的层级数 1-3 (省/市/县) , 默认为3

  返回: 

  ```
  data: {
    list: [
      {
        id: id,
        area_id: 地区ID,
        name: 地区名,
        parent_id: 父级地区ID
        sub: [
          子地区列表, 有下级时存在, 结构同父级结构
        ]
      }
    ]
  }
  ```



- [ ] 获取城市及热门城市列表

  接口地址: GET `/area/cites/withHot`

  参数: 无

  返回

  ```
  data: {
  		{
            tag: 热门,
            list: [
                同地区列表的每一项, 没有sub元素
            ]
  		},
  		{
            tag: A,
            list: [
                同地区列表的每一项, 没有sub元素
            ]
  		}
        ...
      }
  ```

  


- [ ] 根据经纬度获取所在城市

  接口地址: GET `/area/getByGps`

  参数:

  ```
  lng: 经度
  lat: 纬度
  ```

  返回

  ```
  data: {
      province: 省份名,
      province_id: 省份ID, 对应地区信息里里面的area_id
      city: 城市名,
      city_id: 城市ID,
      area: 县区名称,
      area_id: 县区ID
  }
  ```



- [ ] 通过关键字搜索城市列表

  接口地址：GET  `area/search`

  参数：

  ```
  name: 城市关键字
  ```

  返回：

  ```
  data: {
      list: [ 城市列表
          {
              id: 主键
              area_id: 地区id
              name: 地区名称
              type: 类型，保留字段
              path: 路径，从1开始
              area_code: 区号
              spell: 拼音
              letter: 简拼
              first_letter: 首字母
              status: 状态 1-正常 2-禁用
              parent_id: 父ID，如果是省份，则父ID为0
          }
          ......
      ]
  }
  ```

  



#### 商家模块


- [ ] 获取商家类别列表

接口地址： GET    `/merchant/categories/tree`

参数： 无

返回：

  ```
    data: {
      list: [
        {
          id: id,
          pid: 父id,
          name: 分类名,
          icon: 图标 url,
          status: 状态 (只返回状态正常的分类),
          sub: [
            子分类列表
          ]
        }
      ]
    }
  ```

  

- [ ] 获取商家列表 (关键字搜索, 附近商家等)

  接口地址: GET `/merchants`

  参数: 

  ```
  city_id: 城市ID
  keyword: 搜索关键字
  merchant_category_id: 商家类别ID,
  lng: 用户当前经度
  lat: 用户当前纬度
  radius: 范围, 范围参数只有在经纬度信息存在时才有效, 当传递范围参数时, 会获取用户位置所指定范围内的商家并根据距离排序
  lowest_price: 价格筛选，最低的最低消费价格
  highest_price：价格筛选，最高的最低消费价格，有价格筛选时，按照价格由低到高排序
  page: 获取的页数
  ```

返回：

  ```
  data: {
    total: 总记录数,
    list: [
      {
        id: 商家id,
        oper_id: 运营中心ID
        merchant_category_id: 商家分类ID,
        merchantCategoryName: 商家分类名,
        name: 商家名,
        brand: 品牌,
        region: 运营地区/大区 1-中国 2-美国 3-韩国 4-香港
        address: 详细地址,
        province: 所在省份,
        province_id: 省份ID,
        city: 城市,
        city_id: 城市ID,
        area: 县区,
        area_id: 县区ID,
        lng: 商家所在位置经度,
        lat: 商家所在位置纬度,
        business_time: 商家营业时间 数组格式:[开始时间, 结束时间], 如: ['10:30:00', '18:30:00'],
        logo: 商家logo
        desc_pic: 商家描述图片
        desc: 商家介绍
        contacter: 联系人姓名
        contacter_phone: 负责人电话号码
        audit_status: 商户资料审核状态 0-未审核 1-已审核 2-审核不通过 3-重新提交审核
        status: 状态 1-正常 2-禁用 (只返回状态正常的商家),
        distance: 距离, 当传递经纬度信息时才存在,
        lowest_amount: 最低消费金额,
        is_pilot：是否是试点商户 0普通商户 1试点商户
        isOperSelf: 是否归属于当前小程序的运营中心,
        grade: 商户评级,目前默认为5,
        lowestGoods: [	价格最低的两个团购商品
            {
                id: 商品ID,
                oper_id: 运营中心ID
                merchant_id: 商家ID,
                name: 商品名,
                desc: 商品描述,
                market_price: 市场价(商品原价),
                price; 商品价格,
                start_date: 商品有效期开始日期,
                end_date: 商品有效期结束日期,
                business_time: 可用时间 数组格式:[开始时间, 结束时间], 如: ['10:30:00', '18:30:00'],
                thumb_url: 商品缩略图,
                pic: 商品默认图,
                pic_list: 商品小图列表, 数组
                buy_info: 购买须知,
                status: 状态 1-上架 2-下架,
                sell_number: 商品已售数量,
                business_time: 营业时间，数组
            }
            ......
        ]
      }
    ]
  } 
  ```



- [ ] 商户详情

  接口地址: GET `/merchant/detail`

  参数: 

  ```
  id : 商户ID
  lng: 用户所在经度,
  lat: 用户所在纬度
  ```

  返回

  ```
  data: {
    list: {
        id: 商家id,
        oper_id: 运营中心ID
        merchant_category_id: 商家分类ID,
        merchantCategoryName: 商家分类名,
        name: 商家名,
        brand: 品牌,
        region: 运营地区/大区 1-中国 2-美国 3-韩国 4-香港
        address: 详细地址,
        province: 所在省份,
        province_id: 省份ID,
        city: 城市,
        city_id: 城市ID,
        area: 县区,
        area_id: 县区ID,
        lng: 商家所在位置经度,
        lat: 商家所在位置纬度,
        business_time: 商家营业时间 数组格式:[开始时间, 结束时间], 如: ['10:30:00', '18:30:00'],
        logo: 商家logo
        desc_pic: 商家描述图片
        desc: 商家介绍
        contacter: 联系人姓名
        contacter_phone: 负责人电话号码
        audit_status: 商户资料审核状态 0-未审核 1-已审核 2-审核不通过 3-重新提交审核
        status: 状态 1-正常 2-禁用 (只返回状态正常的商家),
        distance: 距离, 当传递经纬度信息时才存在
        lowestAmount: 最低消费金额,
        is_pilot：是否是试点商户 0普通商户 1试点商户,
        isOperSelf: 是否归属于当前小程序的运营中心,
        isOpenDish: 商家是否开启单品模式,
      }
  } 
  ```

  ​


- [ ] 商品列表

接口地址：GET   `/goods`

参数：

```
 merchant_id： 商家ID
```

返回：

  ```
  data: {
      list: [
        {
          id: 商品ID,
          oper_id: 运营中心ID
          merchant_id: 商家ID,
          name: 商品名,
          desc: 商品描述,
          market_price: 市场价(商品原价),
          price; 商品价格,
          start_date: 商品有效期开始日期,
          end_date: 商品有效期结束日期,
          business_time: 可用时间 数组格式:[开始时间, 结束时间], 如: ['10:30:00', '18:30:00'],
          thumb_url: 商品缩略图,
          pic: 商品默认图,
          pic_list: 商品小图列表, 数组
          buy_info: 购买须知,
          status: 状态 1-上架 2-下架,
          sell_number: 商品已售数量,
          business_time: 营业时间，数组
        }
        ......
      ]
    }
  ```

  


- [ ] 商品详情

接口地址： GET  `/goods/detail`

参数

```
id: 商品id
```

返回：

  ```
  data: {
      id: 商品ID,
      oper_id: 运营中心ID
      merchant_id: 商家ID,
      name: 商品名,
      desc: 商品描述,
      market_price: 市场价(商品原价),
      price; 商品价格,
      start_date: 商品有效期开始日期,
      end_date: 商品有效期结束日期,
      business_time: 可用时间 数组格式:[开始时间, 结束时间], 如: ['10:30:00', '18:30:00'],
      thumb_url: 商品缩略图,
      pic: 商品默认图,
      pic_list: 商品小图列表, 数组
      buy_info: 购买须知,
      status: 状态 1-上架 2-下架,
      sell_number: 商品已售数量,
      business_time: 营业时间，数组
    }
  ```



####  订单模块


- [ ] 订单列表

  接口地址: GET `/orders`

  参数:

  ```
   status: 状态 1-未支付 2-已取消 3-已关闭 (超时自动关闭) 4-已支付 5-退款中[保留状态] 6-已退款 7-已完成 (不可退款)
  ```

  返回

  ```
   data: {
        total: 总订单数,
        list: [
            {
                id: 订单ID,
                oper_id: 运营中心ID,
                order_no: 订单号,
                user_id: 用户ID,
                user_name: 用户名,
                notify_mobile: 用户通知手机号,
                merchant_id: 商家ID,
                merchant_name: 商家名,
                merchant_logo: 商家logo,
                signboard_name：商家招牌名称,
                tyep: 订单类型 1-团购订单 2-扫码支付订单 3-点菜订单,
                goods_id: 商品ID,
                goods_name: 商品名,
                goods_pic: 商品图片,
                goods_thumb_url: 商品缩略图,
                price: 商品单价,
                buy_number: 购买数量,
                pay_price: 支付金额,
                pay_time: 支付时间,
                refund_price: 退款金额,
                refund_time: 退款时间,
                status: 状态 1-未支付 2-已取消 3-已关闭 (超时自动关闭) 4-已付款  6-已退款 7-已完成 (不可退款),
                items: 核销码列表(已支付及之后的状态才存在) [
                    {
                        id: 核销码ID,
                        order_id: 订单ID,
                        verify_code: 核销码,
                        status: 状态 1-未核销, 2-已核销 3-已退款,
                    }
                ]
                isOperSelf: 是否归属于当前小程序的运营中心,
                goods_end_date: 商品有效期结束日期,
                dishes_items:[   订单为点菜订单时存在
                    {
                        id: 列表id, 
                        user_id: 用户ID, 
                        oper_id: 所属运营中心ID, 
                        merchant_id: 商家ID, 
                        dishes_id: dishes表的id, 
                        dishes_goods_id: 单品id, 
                        number: 商品数量, 
                        dishes_goods_sale_price: 单品售价, 
                        dishes_goods_detail_image: 单品logo, 
                        dishes_goods_name: 单品名称, 
                        created_at: 创建时间, 
                        updated_at: 更新时间,
                    }
                    ......
                ]
            }
        ]
    } 
  ```



- [ ] 订单详情

地址：GET  `/order/detail`

参数：

  ```
order_no 订单号
  ```

  返回

  ```
     data: {
         id: 订单ID,
         oper_id: 运营中心ID,
         order_no: 订单号,
         user_id: 用户ID,
         user_name: 用户名,
         notify_mobile: 用户通知手机号,
         merchant_id: 商家ID,
         merchant_name: 商家名,
         signboard_name：商家招牌名称,
         tyep: 订单类型 1-团购订单 2-扫码支付订单 3-点菜订单,
         goods_id: 商品ID,
         goods_name: 商品名,
         goods_pic: 商品图片,
         goods_thumb_url: 商品缩略图,
         price: 商品单价,
         buy_number: 购买数量,
         pay_price: 支付金额,
         pay_time: 支付时间,
         refund_price: 退款金额,
         refund_time: 退款时间,
         status: 状态 1-未支付 2-已取消 3-已关闭 (超时自动关闭) 4-已付款  6-已退款 7-已完成 (不可退款),
         items: 核销码列表(已支付及之后的状态才存在) [
             {
             id: 核销码ID,
             order_id: 订单ID,
             verify_code: 核销码,
             status: 状态 1-未核销, 2-已核销 3-已退款,
             }
         ]
         isOperSelf: 是否归属于当前小程序的运营中心,
         user_level: 获取积分时的用户等级（该订单有自反积分时存在）,
         user_level_text: 获取积分时的用户等级文字版（该订单有自反积分时存在）,
         credit: 该订单获取的自反积分（该订单有自反积分时存在）,
         fee_splitting_amount: 该订单用户的分润金额,
         consume_quota: 贡献值,
         dishes_items:[   订单为点菜订单时存在
             {
                 id: 列表id, 
                 user_id: 用户ID, 
                 oper_id: 所属运营中心ID, 
                 merchant_id: 商家ID, 
                 dishes_id: dishes表的id, 
                 dishes_goods_id: 单品id, 
                 number: 商品数量, 
                 dishes_goods_sale_price: 单品售价, 
                 dishes_goods_detail_image: 单品logo, 
                 dishes_goods_name: 单品名称, 
                 created_at: 创建时间, 
                 updated_at: 更新时间,
             }
             ......
         ]
    } 
  ```

  


- [ ] 下单接口

  地址: POST `/order/buy`

  参数

  ```
  goods_id: 商品ID
  number: 数量
  notify_mobile: 通知手机号(可为空)
  ```

  返回

  ```
    data: {
    	order_no: 订单号,
    	isOperSelf: 是否归属于当前小程序的运营中心
    	sdk_config: 调起微信支付配置, isOperSelf 为1时存在 {
          appId: appid,
          nonceStr: 随机字符串,
          package: package,
          signType: signType,
          paySign: 支付签名,
          timestamp: 时间戳,
    	}
    }
  ```

  

- [ ] 扫码买单接口

  地址：ANY   `/order/scanQrcodePay`

  参数：

  ```
  merchant_id: 商户ID
  price: 买单价格
  remark: 备注
  ```

  返回：

  ```
  data: {
      	order_no: 订单号,
      	isOperSelf: 是否归属于当前小程序的运营中心
      	sdk_config: 调起微信支付配置, isOperSelf 为1时存在 {
            appId: appid,
            nonceStr: 随机字符串,
            package: package,
            signType: signType,
            paySign: 支付签名,
            timestamp: 时间戳,
      	}
      }
  ```



- [ ] 退款接口

  地址: POST `/order/refund`

  参数

  ```
  order_no 订单号
  ```

  返回

  ```
  data: {
      id: 16,  退款id
      order_id: 893846835,  订单id
      order_no: "O20180415222010166615",  订单号
      amount: "0.01",  退款金额
      updated_at: "2018-04-16 19:30:45",  更新时间
      created_at: "2018-04-16 19:30:44",  创建时间
      refund_id: "50000206712018041604196575582", 微信退款单号
      status: 2  退款状态 1-未退款 2-已退款
  }
  ```



- [ ] 小程序间跳转h5

  地址:  GET `H5页面 https://o2o.niucha.ren/miniprogram_bridge/pay` 

  参数

  ```
  targetOperId: 要跳转的目标小程序运营中心ID, 商户/订单/商品信息里面都有
  orderNo: 订单号,
  userId: 当前用户ID,
  page: 目标页面地址, 必须是小程序已发布的页面, 目前默认 pages/severs/index/index
  ```

  返回

```
页面会生成一个小程序码, 用户扫码进入小程序指定页面, 可在onLoad中获取到scene值
通过scene值请求接口登陆, 获取用户信息, 并调起支付
```



**点菜相关**

- [ ] 获取该商家菜单的所有种类接口

  地址: Get `dishes/category`

  参数

  ```
  merchant_id   商家id
  ```

  返回

  ```
  {
    "code": 0,
    "message": "请求成功",
    "data": {
        "list": [
            {
                "id": 1,     种类ID
                "oper_id": 3,   运营ID
                "merchant_id": 19,  商家ID
                "name": "海鲜",
                "sort": 1,
                "status": 1,   状态 1上架  2下架
                "created_at": "2018-06-15 14:24:43",
                "updated_at": "2018-06-15 16:50:02",
                "deleted_at": null
            },
  		....
        ]
    },
    "timestamp": 1529477361
  }
  ```


- [ ] 获取菜单各种类菜品接口

  地址: Get `dishes/goods`

  参数

  ```
  merchant_id   商家id
  category_id   菜的种类
  ```

  返回

  ```
  {
    "code": 0,
    "message": "请求成功",
    "data": {
        "list": [
            {
                "id": 3,
                "oper_id": 3,
                "merchant_id": 19,  
                "name": "龙虾",
                "market_price": 111,  市场价格
                "sale_price": 110,  零售价
                "dishes_category_id": 1,  分类ID
                "intro": "11111",  商品描述
                "detail_image": "http://www.daqian.com/storage/image/item/z5tBnB2XoBvXEYUQWyQ3odPz49OPrEKLMEIYmnj6.jpeg",  商品详情图片
                "status": 1,  1上架 2下架
                "created_at": "2018-06-15 14:26:09",
                "updated_at": "2018-06-15 14:26:09",
                "deleted_at": null
            },
           .....
  }
  ```




- [ ] 点菜接口

  地址: post `dishes/add`

  参数   
  ```
  merchant_id   商家id
  goods_list :[
    [id=>'',number=>''],
    [id=>'',number=>'']
  ] id:菜的ID  number 数量

  ```

  返回

  ```
  {
    "code": 0,
    "message": "请求成功",
    "data": {
        "oper_id": 3,
        "merchant_id": 19,
        "user_id": 1,  用户ID
        "updated_at": "2018-06-20 15:05:15",
        "created_at": "2018-06-20 15:05:15",
        "id": 3   菜单ID
    },
    "timestamp": 1529478315
  }
  ```


- [ ] 菜单详情

  地址: get `dishes/detail`

  参数   
  ```
   dishes_id  : 菜单id
   merchant_id: 商户id
  ```

  返回

  ```
  {
  code: 返回码，
  message：返回消息，
  data：[
      {
          "user_id": "",  用户id
          "oper_id": "",   运营中心id
          "dishes_goods_name": "",   商品名称
          "number": "",   商品数量
          "total_price": "",   该商品总价格
          "dishes_goods_detail_image": ""  商品logo
      }
      ......
  ],
  timestamp: 当前时间戳
  }
  ```

  - [ ] 点菜下单接口

  地址: POST `/order/dishesBuy`

  参数

  ```
  dishes_id  ：菜单id
  remark: 备注
  ```

  返回

  ```
    data: {
    	order_no: 订单号,
    	isOperSelf: 是否归属于当前小程序的运营中心
    	sdk_config: 调起微信支付配置, isOperSelf 为1时存在 {
                appId: appid,
                nonceStr: 随机字符串,
                package: package,
                signType: signType,
                paySign: 支付签名,
                timestamp: 时间戳,
                }
    }
  ```

- [ ] 获取热门菜品

  地址：GET  `/dishes/hot`

  参数：

  ```
  merchant_id: 商户id
  ```

  返回：

  ```
  data: {
      list:[
          {
              id: 商品id
              oper_id: 所属运营中心ID
              merchant_id: 商家ID
              name：点菜商品名称
              market_price：市场价格
              sale_price：销售价格
              dishes_category_id：分类
              intro：商品描述
              sell_number：已销售数量
              is_hot：是否热销
              detail_image：商品详情图片
              status：1-上架，2-下架
              created_at：创建时间
              updated_at：更新时间
              deleted_at：删除时间
          }
          ......
      ]
  }
  ```

订单详情，订单列表接口不变



**分享相关**

- [ ] 获取分享二维码

  地址：GET     `invite/qrcode`

  参数：无

  返回：

  ```
  {
      "code": 响应码
      "message": 响应消息,
      "data": {
          "qrcode_url": 分享二维码url,
          "inviteChannel": {
              "id": 推广渠道id,
              "oper_id": 运营中心id,
              "origin_id": 推广人ID(用户ID, 商户ID 或 运营中心ID),
              "origin_type": 推广人类型  1-用户 2-商户 3-运营中心,
              "scene_id": 场景ID (miniprogram_scenes表id) 基于app的邀请码, 没有场景ID与运营中心ID,
              "created_at": 创建时间,
              "updated_at": 更新时间,
              "name": 渠道名称,
              "remark": 备注,
              "origin_name": 推广渠道邀请者名称
          }
      },
      "timestamp": 当前时间戳
  }
  ```

  

- [ ] 根据场景id获取邀请人信息

  地址：GET     `invite/getInviterBySceneId`

  参数：

  ```
  sceneId: 场景ID
  ```

  返回：

  ```
  "data": {
      "id": 推广渠道id,
      "oper_id": 运营中心id,
      "origin_id": 推广人ID(用户ID, 商户ID 或 运营中心ID),
      "origin_type": 推广人类型  1-用户 2-商户 3-运营中心,
      "scene_id": 场景ID (miniprogram_scenes表id) 基于app的邀请码, 没有场景ID与运营中心ID,
      "created_at": 创建时间,
      "updated_at": 更新时间,
      "name": 渠道名称,
      "remark": 备注,
      "origin_name": 推广渠道邀请者名称
  },
  ```



- [ ] 根据场景ID获取场景信息

  地址：GET `scene/info`

  参数：

  ```
  sceneId: 场景ID
  ```

  返回：

  ```
  {
      "code": 响应码,
      "message": 响应信息,
      "data": {	响应参数
          "id": 场景ID,
          "oper_id": 运营中心ID,
          "merchant_id": 商户ID,
          "invite_channel_id": 小程序场景码关联的邀请渠道ID,
          "page": 小程序页面,
          "type": 场景类型 1-小程序间支付跳转码 2-推广注册小程序码 3-扫码支付,
          "payload": 场景附带的参数, json格式, type为1时为 {order_no, user_id} type等于2时为: {origin_id, origin_type}, 参见invite_channels表,
          "created_at": 创建时间,
          "updated_at": 更新时间,
          "qrcode_url": 二维码或小程序码url, 如果不存在则需要去微信生成
      },
      "timestamp": 时间戳
  }
  ```



- [ ] 绑定推荐人

  地址：POST     `invite/bindInviter`

  参数：

  ```
  inviteChannelId: 邀请渠道ID
  ```

  返回：

  ```
  {
      "code": 响应码
      "message": 响应消息,
      "data": {}
      },
      "timestamp": 当前时间戳
  }
  ```

  

- [ ] 用户邀请人信息

  地址: GET ```invite/getInviterInfo```

  参数：无

  返回

```
        "data": {
        "origin_type": 1,  邀请人类型 1-用户 2-商户 3-运营中心
        "user": {        邀请的用户信息, origin_type 为1 时存在
            "id": 2,
            "name": "",
            "mobile": "13923756372",
            "email": "",
            "account": "",
            "status": 1,
            "created_at": "2018-05-11 15:41:50",
            "updated_at": "2018-05-11 15:41:50",
            "level": 1
        },
        "merchant":邀请的商户信息 origin_type为2时存在 {
          name:...
        },   
        "oper":  邀请的运营中心信息 origin_type为3时存在 {
          name:...       
        },     
        "mappingUser":  邀请的运营中心或商户绑定的用户信息, origin_type 为2或3, 以及商户或运营中心已绑定用户时存在 {
          mobile:...
        }    
    },
    

```

  ​

- [ ] 用户解绑

  地址: POST     ```invite/unbind```

  参数：无

  首次解绑 成功返回

```
  {
    "code": 0,
    "message": "请求成功",
    "data": [],
    "timestamp": 1528947476
  }
     

```

  若第二次解绑

```
  {
    "code": 500,
    "message": "该用户已解绑一次，不能再次解绑",
    "timestamp": 1528947551
  }

```

  

- [ ] 获取用户分享列表

  地址：GET   `invite/getInviteUserStatistics`

  参数：

  ```
  userId: 用户ID
  date: 日期（格式：2018-05）
  ```

  返回：

  ```
  {
      "code": 响应码
      "message": 响应信息
      "data": {	响应数据
          "data": {
              "2018-05": { 月份
                  "sub": [
                      {
                          "id": 14,
                          "user_id": 13,
                          "invite_channel_id": 9,
                          "origin_id": 6,
                          "origin_type": 1,
                          "created_at": "2018-05-14 16:18:20",
                          "updated_at": "2018-06-08 10:14:14",
                          "user_mobile": "18986122861"
                      },
                      ......
                  ],
                  "count": 该月邀请总人数
              }
              ......
          },
          "totalCount": 邀请总人数
          "todayInviteCount": 今日邀请总人数
      },
      "timestamp": 时间戳
  }
  ```

  

**用户相关**

- [ ] 获取用户信息

  地址：ANY  ` user/info`

  参数：无

  返回：

  ```
  {
      "code": 响应码
      "message": 响应信息,
      "data": {	响应数据
          "userInfo": {
              "id": 用户ID,
              "name": 用户姓名,
              "mobile": 用户手机号码,
              "email": 用户邮箱,
              "account": 用户账号,
              "status": 用户状态	状态 1-正常 2-禁用,
              "wx_nick_name": 用户微信昵称,
              "wx_avatar_url": 用户微信头像,
              "created_at": 创建时间,
              "updated_at": 更新时间,
              "level": 用户等级值,
              "level_text": 用户等级
          }
      },
      "timestamp": 时间戳
  }
  ```

  

- [ ] 更新用户微信昵称和头像

  地址：ANY  `user/updateWxInfo`

  参数：

  ```
  userInfo: 用户微信信息的json字符串
  ```

  返回： 

  ```
  {
      "code": 响应码
      "message": 响应信息,
      "data": { },
      "timestamp": 时间戳
  }
  ```


#### **钱包**

- [ ] 获取钱包信息接口

  地址：GET `wallet/info`

  参数：

  ```
  无
  ```

  返回： 

  ```
  {
      "code": 响应码
      "message": 响应信息,
      "data": {
      	balance: 余额(不包含冻结余额),
      	freeze_balance: 冻结余额,
      	consume_quota: 当月消费额(不包含冻结),
      	freeze_consume_quota: 冻结消费额,
      	total_consume_quota: 累计消费额,
      },
      "timestamp": 时间戳
  }
  ```

  



- [ ] 获取账单接口

  地址：GET `wallet/bills`

  参数：

  ```
  {
      startDate: 起始日期,
      endDate: 结束日期,
      type: 类型 1-个人消费返利  2-下级消费返利  3-个人消费返利退款  4-下级消费返利退款 5-运营中心交易分润 6-运营中心交易分润退款 7-提现 8-提现失败退回
  }
  ```

  返回： 

  ```
  {
      "code": 响应码
      "message": 响应信息,
      "data": {
      	list: [
              {
                  id: 流水ID,
                  bill_no: 流水单号,
                  type: 类型 1-个人消费返利  2-下级消费返利  3-个人消费返利退款  4-下级消费返利退款 5-运营中心交易分润 6-运营中心交易分润退款 7-提现 8-提现失败,
                  obj_id: 产生流水的来源ID, 返利相关为返利记录ID, 提现为提现记录ID,
                  inout_type: 收支类型 1-收入 2-支出,
                  amount: 变动金额,
                  amount_type: 变动金额类型, 1-冻结金额, 2-非冻结金额,
                  created_at: 交易时间,
                  status: 提现状态(若类型是提现相关[7,8]),
              }
      	],
      	total: 总记录数
      },
      "timestamp": 时间戳
  }
  ```

- [ ] 获取账单详情接口

  地址: GET `wallet/bill/detail`

  参数: 

  ```
  id: 必填
  page: 当前页码
  ```

  返回:

  ```
  {
      "code": 响应码
      "message": 响应信息,
      "data": {
      	id: 流水ID,
      	bill_no: 流水单号,
      	type: 类型 1-个人消费返利  2-下级消费返利  3-个人消费返利退款  4-下级消费返利退款 5-运营中心交易分润 6-运营中心交易分润退款 7-提现 8-提现失败,
      	obj_id: 产生流水的来源ID, 返利相关为返利记录ID, 提现为提现记录ID,
      	inout_type: 收支类型 1-收入 2-支出,
      	amount: 变动金额,
      	amount_type: 变动金额类型, 1-冻结金额, 2-非冻结金额,
      	created_at: 交易时间,
      	status: 提现状态(若类型是提现相关[7,8]),
      	order: 订单信息, 当类型为返利相关时存在(1,2,3,4,5,6) {
              id: 订单ID,
              order_no: 订单号,
              status: 订单状态,
              created_at: 订单创建时间,
              pay_time: 支付时间(交易时间),
              notify_mobile: 用户手机号码,
              pay_price: 支付金额,
      	},
      	refund: 退款信息, 当类型为返利退款时存在(3,4,6) {
              id: 退款ID,
              refund_no: 退款单号,
              status: 退款状态,
              created_at: 退款时间,
      	},
      	withdraw: 提现信息, 当类型为提现时存在(7,8)  {
              id: 提现ID,
              withdraw_no: 提现编号,
              amount: 提现金额,
              charge_amount: 手续费,
              status: 状态 1-审核中 2-审核通过 3-已打款 4-打款失败 5-审核不通过,
              bank_card_type: 账户类型 1-公司 2-个人,
              bank_card_open_name: 银行卡开户名,
              bank_card_no: 银行卡号,
              bank_name: 开户行,
              remark: 备注,
              created_at: 提现时间,
      	}
      	
      },
      "timestamp": 时间戳
  }
  ```

- [ ] 获取TPS消费额列表

  地址: GET `wallet/consumeQuotas`

  参数: 

  ```
  month: 月份, 默认当前月
  status: 状态 1-冻结中 2-已解冻待置换 3-已置换 4-已退款  不填查询全部 
  page: 当前页码
  ```

  返回:

  ```
  {
      "code": 响应码
      "message": 响应信息,
      "data": {
      	list: [
              {
                  id: 消费额记录ID,
                  consume_quota_no: 消费额交易号
                  type: 来源类型 1-消费自返 2-直接下级消费返
                  order_id: 分润的订单ID
                  order_no: 分润的订单号
                  pay_price: 支付金额
                  order_profit_amount: 订单利润
                  consume_quota: 消费额
                  consume_user_mobile: 消费用户手机号
                  status: 状态 1-冻结中 2-已解冻待置换 3-已置换 4-已退款
              }
      	],
      	total: 总记录数，
      	amount: 当月总消费额
      },
      "timestamp": 时间戳
  }
  ```

- [ ] 获取TPS消费额详情

  地址: GET `wallet/consumeQuota/detail`

  参数: 

  ```
  month: 月份, 默认当前月
  status: 状态 1-冻结中 2-已解冻待置换 3-已置换 4-已退款  不填查询全部 
  page: 当前页码
  ```

  返回:

  ```
  {
      "code": 响应码
      "message": 响应信息,
      "data": {
      	id: 消费额记录ID,
      	consume_quota_no: 消费额交易号
      	type: 来源类型 1-消费自返 2-直接下级消费返
      	order_id: 分润的订单ID
      	order_no: 分润的订单号
      	pay_price: 支付金额
      	order_profit_amount: 订单利润 (订单毛利润-税-分润金额)
      	consume_quota: 消费额 [ =订单金额 ]
        consume_quota_profit: TPS消费额对应的利润金额 (订单利润/2)
        tps_consume_quota: TPS消费额 (订单金额/6/汇率[6.5])  单位: 美金
        tps_credit: 消费额转化的tps积分值 (TPS消费额/4)
        sync_tps_credit: 要同步给tps的积分值, 累计积分每满1积分才同步
      	consume_user_mobile: 消费用户手机号
        sync_time: 同步到TPS的时间, 即置换时间
      	status: 状态 1-冻结中 2-已解冻待置换 3-已置换 4-已退款
      	order: 消费额对应的订单信息 {
              "id": 174,
      		"pay_time": "2018-06-12 17:35:38"
      	},
      	unfreeze_record: 解冻记录, 若状态是已解冻 {
              "id": 4, 解冻记录ID
              "wallet_id": 4,
              "consume_quota_record_id": 4,
              "origin_id": 19,
              "origin_type": 2,
              "unfreeze_consume_quota": "117.50",
              "created_at": "2018-08-22 21:26:43", 解冻时间
              "updated_at": "2018-08-22 21:26:43"
      	}
      },
      "timestamp": 时间戳
  }
  ```

- [ ] 获取TPS消费额统计

  地址：GET `wallet/tpsConsume/statistics`

  参数： 无

  返回： 

  ```
  {
      "code": 0,
      "message": "请求成功",
      "data": {
          "totalTpsConsume": 累计置换TPS消费额,
          "theMonthTpsConsume": 本月已置换TPS消费额,
          'showReminder': '', // 是否显示提示语 有则显示，没有则不显示
      },
      "timestamp": 1535870137
  }
  ```

  

- [ ] 根据商户ID获取该商户订单下 分润给用户自己的分润系数

  地址：GET `wallet/userFeeSplitting/ratio`

  参数：

  ```
  merchantId: 商户ID
  ```

  返回

  ```
  {
      "code": 0,
      "message": "请求成功",
      "data": {
          "ratio": 分润给用户自己的系数 （直接乘以订单金额就好了）
      },
      "timestamp": 1535528467
  }
  ```

  

- [ ] 获取TPS积分统计

  地址：GET `wallet/tpsCredit/statistics`

  参数：无

  返回：

  ```
  {
      "code": 0,
      "message": "请求成功",
      "data": {
          "totalTpsCredit": 个人消费获得TPS积分,
          "totalShareTpsCredit": 下级累计贡献TPS积分,
          "tpsCreditSum": 总累计TPS积分,
          "totalSyncTpsCredit": 已置换的TPS积分,
          "contributeToParent": 累计贡献上级TPS积分,
          'showReminder': '', // 是否显示提示语 有则显示，没有则不显示
      },
      "timestamp": 1535870518
  }
  ```

- [ ] 获取TPS积分列表

  地址： `wallet/tpsCredit/list`

  参数：

  ```
  month: 月份 yyyy-MM （不传默认当月）
  type: 来源类型 1-自己消费 2-下级贡献 （不传默认全部）
  pageSize: 分页大小 （默认15）
  page: 当前页数 （默认为1）
  ```

  返回： 

  ```
  {
      "code": 0,
      "message": "请求成功",
      "data": {
          "list": [ 积分列表数据
              {
                  "id": 消费额记录ID
                  "wallet_id": 钱包ID,
                  "consume_quota_no": 消费额交易号,
                  "origin_id": 用户ID,
                  "origin_type": 用户类型 1-用户 2-商户 3-运营中心,
                  "type": 来源类型 1-消费自返 2-直接下级消费返[下级返时只有积分 其他全部为0],
                  "order_id": 分润的订单ID,
                  "order_no": 分润的订单号,
                  "pay_price": 支付金额,
                  "order_profit_amount": 订单利润 (订单毛利润-税-分润金额),
                  "consume_quota": 消费额 [ =订单金额 ],
                  "consume_quota_profit": TPS消费额对应的利润金额 (订单利润/2),
                  "tps_consume_quota": TPS消费额 (订单金额/6/汇率[6.5])  单位: 美金,
                  "tps_credit": 消费额转化的tps积分值 (TPS消费额/4),
                  "sync_tps_credit": 要同步给tps的积分值, 累计积分每满1积分才同步,
                  "consume_user_mobile": 消费用户手机号,
                  "status": 状态 1-冻结中 2-已解冻待置换 3-已置换 4-已退款,
                  "created_at": "2018-08-31 15:00:32",
                  "updated_at": "2018-08-31 15:00:32"
              },
              ......
          ],
          "total": 总条数
          "totalTpsCredit": 当月获得的TPS积分
          "hasSyncTpsCredit": 当月置换的TPS积分
      },
      "timestamp": 1535871197
  }
  ```

  















