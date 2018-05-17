### 用户端App接口列表

##### 域名信息: 

- 正式环境: https://o2o.niucha.ren
- 测试环境: https://xiaochengxu.niucha.ren

##### 接口统一前缀地址: https://domain/api/app/user

##### 请求统一参数, 统一参数放到header中:

```
token (不是必填, 需要登录的接口要填)
version: 当前客户端版本
app-type: 客户端类型  1-Android 2-iOS
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



#### 版本相关

- [ ] 获取最新版本

  接口地址 GET `/version/last`

  参数: 无

  返回: 

  ```
  data: {
      version: 版本号,
      force: 是否强制更新, 1-强制更新, 0-不强制更新,
      desc: 更新说明 (客户端文档格式需要怎么处理, 简单的换行?)
  }
  ```

  ​

- [ ] 获取版本号

  接口地址 GET `/versions`

  参数: 无

  返回: 

  ```
  data: {
      last: 最新版本 {
          version: 版本号,
          force: 是否强制更新,
          desc: 更新说明,
      },
      versions: 版本列表 [
          {
          	id: id,
              version: 版本号,
              force: 是否强制更新,
          	desc: 更新说明,
          },
          ...
      ]
      
  }
  ```

  ​

#### 登陆相关

- [ ] 登陆接口(绑定手机号) 

   接口地址: `POST` `/login`

  参数:

  ```
  mobile 手机号
  verify_code 验证码
  inviteChannelId : 邀请渠道ID (非必填)
  ```

  返回值

  ```
  data: {
      userInfo: 用户信息 {
          name: 用户名称,
          mobile: 手机号
      },
      token: 返回用户token
  }
  ```

  返回码

  ```
  10008: 用户已经被邀请过了, 需要去掉邀请人信息再次登录
  ```

  ​


- [ ] 退出登陆

    接口地址: POST `/logout`
    
    参数: 

  ```
  token 用户登录token
  ```

  返回: 

  ```
  data: {
      无
  }
  ```

  ​


- [ ] 短信接口

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

  接口地址: GET `/area/cities/withHot`

  参数: 无

  返回

  ```
  data: {
      热门: [
          同地区列表的每一项, 没有sub元素
      ],
      A: [
          同地区列表的每一项, 没有sub元素
      ],
      ...
  }
  ```

  ​



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

  

#### 商家模块	


- [ ] 获取商家类别列表

  接口地址: GET `/merchant/categories/tree`

  返回:

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

  ​

  ​

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
  page: 页码
  ```

返回

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
        contacter_phone: 负责人联系方式
        service_phone: 客服电话
        status: 状态 1-正常 2-禁用 (只返回状态正常的商家),
        distance: 距离, 当传递经纬度信息时才存在
        lowestAmount: 最低消费金额
      }
    ]
  } 
  ```



- [ ] 商户详情

  接口地址: GET `/merchant/detail`

  参数: 

  ```
  id: 商户ID
  lng: 用户所在经度,
  lat: 用户所在纬度
  ```

  返回

  ```
  同商户列表返回的字段
  ```

  


- [ ] 商品列表

  接口地址: GET `/goods`

  参数: `merchant_id` 商家ID

  返回

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
        }
      ]
    }
  ```

  ​


- [ ] 商品详情

  接口地址: GET `/goods/detail`

  参数: id 商品ID

  返回

  ```
  同商品列表中的每一项
  ```



####  订单模块


- [ ] 订单列表

  接口地址: GET `/orders`

  参数:

  ```
   status: 状态	
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
                goods_id: 商品ID,
                goods_name: 商品名,
                goods_pic: 商品图片,
                goods_thumb_url: 商品缩略图,
                price: 商家单价,
                buy_number: 购买数量,
                pay_price: 支付金额,
                pay_time: 支付时间,
                refund_price: 退款金额,
                refund_time: 退款时间,
                status: 状态 1-未支付 2-已取消 3-已关闭 (超时自动关闭) 4-已付款  6-已退款 7-已完成 (不可退款),
                origin_app_type: 订单来源客户端类型  1-安卓 2-ios 3-小程序
                items: 核销码列表(已支付及之后的状态才存在) [
                    {
                        id: 核销码ID,
                        order_id: 订单ID,
                        verify_code: 核销码,
                        status: 状态 1-未核销, 2-已核销 3-已退款,
                    }
                ],
                goods_end_date: 商品截止日期
            }
        ]
    } 
  ```



- [ ] 订单详情

  地址: GET`/order/detail`

  参数

  ```
  order_no 订单号
  ```

  返回

  ```
  同订单列表中的每一项
  ```

  ​


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
        id: 订单ID,
        oper_id: 运营中心ID,
        order_no: 订单号,
        user_id: 用户ID,
        user_name: 用户名,
        notify_mobile: 用户通知手机号,
        merchant_id: 商家ID,
        merchant_name: 商家名,
        goods_id: 商品ID,
        goods_name: 商品名,
        goods_pic: 商品图片,
        goods_thumb_url: 商品缩略图,
        price: 商家单价,
        buy_number: 购买数量,
        pay_price: 支付金额,
        pay_time: 支付时间,
        refund_price: 退款金额,
        refund_time: 退款时间,
        status: 状态 1-未支付 2-已取消 3-已关闭 (超时自动关闭) 4-已付款  6-已退款 7-已完成 (不可退款),
        origin_app_type: 订单来源客户端类型  1-安卓 2-ios 3-小程序
    }
  ```

  

- [ ] 订单支付接口

  地址: POST `/order/pay`

  参数

  ```
  order_no: 订单号
  pay_type: 支付类型 1-微信支付 2-支付宝支付  默认1
  ```

  返回

  ```
  data: {
      order_no: 订单号,
      sdk_config: 调起支付参数 {
      	appid,
      	partnerid,
      	prepayid,
      	noncestr,
      	timestamp,
      	package,
      	sign
      }
  }
  ```

  ​



- [ ] 退款接口

  地址: POST `/order/refund`

  参数

  ```
  order_no 订单号
  ```

  返回

  ```
  data: {
      id: 16,
      order_id: 893846835,
      order_no: "O20180415222010166615",
      amount: "0.01",
      updated_at: "2018-04-16 19:30:45",
      created_at: "2018-04-16 19:30:44",
      refund_id: "50000206712018041604196575582",
      status: 2
  }
  ```



####邀请渠道

- [ ] 获取邀请二维码

  地址: GET `/invite/qrcode`

  参数: 无

  返回

  ```
  data: {
      qrcode_url: 二维码地址
  }
  ```



- [ ] 获取邀请人信息

  地址: GET `/invite/getInviterByInviteChannelId`

  参数: `inviteChannleId 邀请渠道ID(通过扫码获得)`

  返回: 

  ```
  data: {
      id: 邀请渠道ID,
      origin_id: 推广人ID,
      origin_type: 推广人类型,
      origin_name: 推广人名称,
      created_at: 创建时间
  }
  ```

- [ ] 绑定推荐人

  地址: GET `/invite/bindInviter`

  参数: 

  ```
  inviteChannelId: 邀请渠道ID
  ```

  返回: 

  ```
  data: {
      无
  }
  ```

  返回码:

  ```
  10008: 用户已经绑定了邀请人
  ```

  ​