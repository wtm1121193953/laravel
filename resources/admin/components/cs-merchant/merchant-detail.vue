<template>
    <el-row>
        <audit-message v-show="isShowAuditMessage" :data="data"></audit-message>
        <el-col :span="24">
            <el-form label-width="120px" label-position="left" size="small">
                <el-col>
                    <div class="title">商户信息</div>
                </el-col>

                <!--商户录入信息左侧块-->
                <el-col :span="11">
                    <el-form-item label="商户类型">
                        <el-tooltip class="item" effect="dark" content="Top Left 提示文字" placement="bottom">
                            <span>超市类</span>
                        </el-tooltip>
                    </el-form-item>
                    <el-form-item prop="status" label="商户状态">
                        <span v-if="data.status === 1" class="c-green">已启用</span>
                        <span v-else-if="data.status === 2" class="c-danger">已冻结</span>
                        <span v-else>未知 ({{data.status}})</span>
                    </el-form-item>
                    <el-form-item prop="settlement_cycle_type" label="结算周期">
                        <span v-if="data.settlement_cycle_type === 1" class="c-green">周结</span>
                        <span v-else-if="data.settlement_cycle_type === 2" class="c-green">半月结</span>
                        <span v-else-if="data.settlement_cycle_type === 3" class="c-green">月结</span>
                        <span v-else-if="data.settlement_cycle_type === 4" class="c-green">半年结</span>
                        <span v-else-if="data.settlement_cycle_type === 5" class="c-green">年结</span>
                        <span v-else-if="data.settlement_cycle_type === 6" class="c-green">T+1</span>
                        <span v-else>未知</span>
                    </el-form-item>
                    <el-form-item prop="id" label="商户ID">{{data.cs_merchant_id}}</el-form-item>
                    <el-form-item v-if="data.operName" prop="operName" label="运营中心">{{data.operName}}</el-form-item>

                    <el-form-item prop="name" label="商户名称">
                        <template v-if="checkIsset('name')">
                            <el-tooltip class="item" effect="dark" :content="data.changeData.name" placement="bottom">
                                <span class="c-danger">{{data.name}}</span>
                            </el-tooltip>
                        </template>
                        <template v-else>
                            <span>{{data.name}}</span>
                        </template>
                    </el-form-item>

                    <el-form-item prop="name" label="招牌名称">
                        <template v-if="checkIsset('name')">
                            <el-tooltip class="item" effect="dark" :content="data.changeData.signboard_name" placement="bottom">
                                <span class="c-danger">{{data.signboard_name}}</span>
                            </el-tooltip>
                        </template>
                        <template v-else>
                            <span>{{data.signboard_name}}</span>
                        </template>
                    </el-form-item>

                    <!--<el-form-item prop="merchant_category" label="所属行业">
                        <span v-for="item in data.categoryPath" :key="item.id">
                            {{ item.name }}
                        </span>
                    </el-form-item>-->
                    <el-form-item label="营业执照">
                        <div class="licence" v-viewer style="display: none;">
                                <img :src="data.business_licence_pic_url" />
                            </div>
                        <el-button v-if="data.business_licence_pic_url" type="text" @click="previewImage('licence')">查看</el-button>
                        <!-- <el-button type="text" @click="previewImage(data.business_licence_pic_url)">查看</el-button> -->
                    </el-form-item>

                </el-col>
                <el-col :span="12" :offset="1">
                    <el-form-item prop="audit_status" label="审核状态">
                        <span v-if="data.audit_status === 1" class="c-warning">待审核</span>
                        <span v-else-if="data.audit_status === 2" class="c-green">审核通过 {{data.suggestion}}</span>
                        <span v-else-if="data.audit_status === 3" class="c-danger">审核不通过 {{data.suggestion}}</span>
                        <span v-else-if="data.audit_status === 4" class="c-warning">撤回</span>
                        <span v-else>未知 ({{data.audit_status}})</span>
                    </el-form-item>
                </el-col>

                <!-- 商户录入信息右侧块 -->
                <el-col :span="11" :offset="1">
                    <el-form-item prop="operAddress" label="运营中心地址">
                        {{data.operAddress}}
                    </el-form-item>
                    <el-form-item prop="area" label="省市区">
                        {{data.province}} {{data.city}} {{data.area}}
                    </el-form-item>
                    <el-form-item prop="address" label="详细地址">
                        {{data.address}}
                    </el-form-item>
                    <el-form-item label="营业执照代码">
                        {{ data.organization_code}}
                    </el-form-item>
                </el-col>

                <!-- 商户坐标：展示地图上的位置 -->
                <el-col :span="16">
                    <el-form-item prop="location" label="商户坐标">
                        <qmap-choose-point width="100%" height="300px" :shown-markers="[[data.lng, data.lat]]" disabled/>
                    </el-form-item>
                </el-col>

                <!-- 商户激活信息 -->
                <el-col v-if="type != 'poolOnly'">
                    <el-col>
                        <div class="title">商户激活信息</div>
                    </el-col>
                    <!-- 商户激活信息左侧块 -->
                    <el-col :span="11">
                        <!--<el-form-item prop="oper_biz_member_code" label="签约人">
                            <template>
                                <span v-if="data.bizer_id && data.bizer">
                                    <span>{{data.bizer.name}}</span>
                                    <span>{{data.bizer.mobile}}</span>
                                    <span>(业务员)</span>
                                </span>
                                <span v-else-if="data.oper_biz_member_code && data.operBizMember">
                                    <span>{{data.operBizMember.name}}</span>
                                    <span>{{data.operBizMember.mobile}}</span>
                                    <span>(员工)</span>
                                </span>
                                <span v-else>无</span>
                            </template>
                        </el-form-item>-->
                        <!--<el-form-item prop="brand" label="品牌">{{data.brand}}</el-form-item>-->
                        <!--<el-form-item prop="invoice_title" label="发票抬头">{{data.invoice_title}}</el-form-item>-->
                        <!--<el-form-item prop="invoice_no" label="发票税号">{{data.invoice_no}}</el-form-item>-->

                        <el-form-item prop="business_time" label="营业时间">
                            <span v-if="data.business_time">{{data.business_time[0]}} 至 {{data.business_time[1]}}</span>

                        </el-form-item>
                        <el-form-item prop="logo" label="商家logo">
                            <div v-viewer>
                                <img :src="data.logo" alt="商家logo" width="200px" height="100px" />
                            </div>
                            <!-- <preview-img :url="data.logo" width="50px" height="50px"/> -->
                        </el-form-item>
                        <el-form-item prop="desc_pic" label="商家介绍图片">
                            <div class="desc" v-viewer style="display: none;">
                                <img v-for="(item,index) in data.desc_pic_list" :src="item" :key="index" />
                            </div>
                            <el-button v-if="data.desc_pic_list.length > 0" type="text" @click="previewImage('desc')">查看</el-button>
                            <!-- <el-button type="text" @click="previewImage(data.desc_pic_list)">查看</el-button> -->
                        </el-form-item>
                        <el-form-item prop="desc_pic" label="商家介绍">
                            <template v-if="checkIsset('desc')">
                                <el-tooltip class="item" effect="dark" :content="data.changeData.desc" placement="bottom">
                                    <span class="c-danger">{{data.desc}}</span>
                                </el-tooltip>
                            </template>
                            <template v-else>
                                <span>{{data.desc}}</span>
                            </template>
                        </el-form-item>
                        <!--<el-form-item label="结算周期">
                            {{ {1: '周结', 2: '半月结', 3: '月结', 4: '半年结', 5: '年结', 6: 'T+1',}[data.settlement_cycle_type] }}
                        </el-form-item>-->
                        <el-form-item label="分利比例">
                            <template v-if="checkIsset('settlement_rate')">
                                <el-tooltip class="item" effect="dark" :content="data.changeData.settlement_rate" placement="bottom">
                                    <span class="c-danger">{{data.settlement_rate}}</span>
                                </el-tooltip>
                            </template>
                            <template v-else>
                                <span>{{data.settlement_rate}}</span>
                            </template>
                        </el-form-item>

                        <!-- 银行卡信息 start -->
                        <el-form-item label="银行帐户类型">
                            <span v-if="data.bank_card_type === 1" class="c-gray">公司</span>
                            <span v-if="data.bank_card_type === 2" class="c-green">个人</span>
                        </el-form-item>
                        <el-form-item label="银行开户名">
                            <template v-if="checkIsset('bank_open_name')">
                                <el-tooltip class="item" effect="dark" :content="data.changeData.bank_open_name" placement="bottom">
                                    <span class="c-danger">{{data.bank_open_name}}</span>
                                </el-tooltip>
                            </template>
                            <template v-else>
                                <span>{{data.bank_open_name}}</span>
                            </template>
                        </el-form-item>
                        <el-form-item label="银行账号">
                            <template v-if="checkIsset('bank_card_no')">
                                <el-tooltip class="item" effect="dark" :content="data.changeData.bank_card_no" placement="bottom">
                                    <span class="c-danger">{{data.bank_card_no}}</span>
                                </el-tooltip>
                            </template>
                            <template v-else>
                                <span>{{data.bank_card_no}}</span>
                            </template>
                        </el-form-item>
                        <el-form-item label="开户行">
                            <template v-if="checkIsset('bank_name')">
                                <el-tooltip class="item" effect="dark" :content="data.changeData.bank_name" placement="bottom">
                                    <span class="c-danger">{{data.bank_name}}</span>
                                </el-tooltip>
                            </template>
                            <template v-else>
                                <span>{{data.bank_name}}</span>
                            </template>
                        </el-form-item>
                        <el-form-item label="开户行网点名称">
                            <template v-if="checkIsset('sub_bank_name')">
                                <el-tooltip class="item" effect="dark" :content="data.changeData.sub_bank_name" placement="bottom">
                                    <span class="c-danger">{{data.sub_bank_name}}</span>
                                </el-tooltip>
                            </template>
                            <template v-else>
                                <span>{{data.sub_bank_name}}</span>
                            </template>
                        </el-form-item>
                        <el-form-item label="开户行网点地址">

                            <template v-if="checkIsset('bank_province')">
                                <el-tooltip class="item" effect="dark" :content="data.changeData.bank_province" placement="bottom">
                                    <span class="c-danger">{{data.bank_province}}</span>
                                </el-tooltip>
                            </template>
                            <template v-else>
                                <span>{{data.bank_province}}</span>
                            </template>

                            <template v-if="checkIsset('bank_city')">
                                <el-tooltip class="item" effect="dark" :content="data.changeData.bank_city" placement="bottom">
                                    <span class="c-danger">{{data.bank_city}}</span>
                                </el-tooltip>
                            </template>
                            <template v-else>
                                <span>{{data.bank_city}}</span>
                            </template>

                            <template v-if="checkIsset('bank_area')">
                                <el-tooltip class="item" effect="dark" :content="data.changeData.bank_area" placement="bottom">
                                    <span class="c-danger">{{data.bank_area}}</span>
                                </el-tooltip>
                            </template>
                            <template v-else>
                                <span>{{data.bank_area}}</span>
                            </template>

                            <template v-if="checkIsset('bank_open_address')">
                                <el-tooltip class="item" effect="dark" :content="data.changeData.bank_open_address" placement="bottom">
                                    <span class="c-danger">{{data.bank_open_address}}</span>
                                </el-tooltip>
                            </template>
                            <template v-else>
                                <span>{{data.bank_open_address}}</span>
                            </template>

                        </el-form-item>
                        <el-form-item v-if="data.bank_card_type == 1" required prop="licence_pic_url" label="开户许可证">
                            <div v-viewer>
                                <img v-if="data.licence_pic_url" :src="data.licence_pic_url" alt="开户许可证" width="200px" height="100px" />
                            </div>
                            <!-- <preview-img :url="data.licence_pic_url" width="100px" height="100px"/> -->
                        </el-form-item>
                        <el-form-item v-if="data.bank_card_type == 2" required label="法人银行卡正面照" prop="bank_card_pic_a">
                            <div v-viewer>
                                <img v-if="data.bank_card_pic_a.length == 1" :src="data.bank_card_pic_a" alt="法人银行卡正面照" width="200px" height="100px" />
                                <div>
                                    <img v-if="data.bank_card_pic_a.length == 2" :src="data.bank_card_pic_a[0]" alt="法人银行卡正面照" width="200px" height="100px" />
                                </div>
                                <div>
                                    <img v-if="data.bank_card_pic_a.length == 2" :src="data.bank_card_pic_a[1]" alt="法人银行卡正面照" width="200px" height="100px" />
                                </div>
                            </div>
                            <!-- <preview-img :url="data.bank_card_pic_a" width="100px" height="100px"/> -->
                        </el-form-item>
                        <!-- 银行卡信息 end -->

                        <el-form-item label="法人身份证正反面">
                            <div v-viewer>
                                <img v-if="data.legal_id_card_pic_a" :src="data.legal_id_card_pic_a" width="200px" height="100px" alt="法人身份证正反面" />
                            </div>
                            <div v-viewer>
                                <img v-if="data.legal_id_card_pic_b" :src="data.legal_id_card_pic_b" width="200px" height="100px" alt="法人身份证正反面" />
                            </div>
                            <!-- <preview-img :url="data.legal_id_card_pic_a" width="200px" height="100px"/>
                            <preview-img :url="data.legal_id_card_pic_b" width="200px" height="100px"/> -->
                        </el-form-item>

                        <el-form-item label="法人姓名">
                            <template v-if="checkIsset('corporation_name')">
                                <el-tooltip class="item" effect="dark" :content="data.changeData.corporation_name" placement="bottom">
                                    <span class="c-danger">{{data.corporation_name}}</span>
                                </el-tooltip>
                            </template>
                            <template v-else>
                                <span>{{data.corporation_name}}</span>
                            </template>
                        </el-form-item>
                        <el-form-item label="法人身份证号码">
                            <template v-if="checkIsset('countryName')">
                                <el-tooltip class="item" effect="dark" :content="data.changeData.countryName" placement="bottom">
                                    <span class="c-danger">（{{data.countryName}}）</span>
                                </el-tooltip>
                            </template>
                            <template v-else>
                                <span>（{{data.countryName}}）</span>
                            </template>

                            <template v-if="checkIsset('legal_id_card_num')">
                                <el-tooltip class="item" effect="dark" :content="data.changeData.legal_id_card_num" placement="bottom">
                                    <span class="c-danger">{{data.legal_id_card_num}}</span>
                                </el-tooltip>
                            </template>
                            <template v-else>
                                <span>{{data.legal_id_card_num}}</span>
                            </template>

                        </el-form-item>

                        <el-form-item label="合同">
                            <div class="contract" v-viewer style="display: none;">
                                <img v-for="(item, index) in data.contract_pic_url" :src="item" :key="index" alt="合同" />
                            </div>
                            <el-button v-if="data.contract_pic_url" type="text" @click="previewImage('contract')">查看</el-button>
                        </el-form-item>

                        <el-form-item prop="other_card_pic_urls" label="其他证件">
                            <div v-viewer>
                                <img v-for="(src,index) in data.other_card_pic_urls" :src="src" :key="index"width="200px" height="100px" alt="其他证件" />
                            </div>
                            <!-- <template v-for="pic in data.other_card_pic_urls">
                                <preview-img :url="pic" width="200px" height="100px"/>
                            </template> -->
                        </el-form-item>
                        <el-col v-if="data.audit_status == 1">
                            <el-form-item prop="audit_suggestion" label="审核意见">
                                <el-input v-if="data.audit_status == 1" placeholder="最多输入50个汉字"  maxlength="50" v-model="data.audit_suggestion" :autosize="{minRows: 3}" type="textarea"/>
                                <span v-else>{{data.audit_suggestion}}</span>
                            </el-form-item>
                        </el-col>
                    </el-col>


                    <!-- 商户激活信息右侧块 -->
                    <el-col :span="11" :offset="1">
                        <el-form-item prop="contacter" label="负责人姓名">
                            <template v-if="checkIsset('contacter')">
                                <el-tooltip class="item" effect="dark" :content="data.changeData.contacter" placement="bottom">
                                    <span class="c-danger">{{data.contacter}}</span>
                                </el-tooltip>
                            </template>
                            <template v-else>
                                <span>{{data.contacter}}</span>
                            </template>
                        </el-form-item>
                        <el-form-item prop="contacter_phone" label="负责人手机号码">
                            <template v-if="checkIsset('contacter_phone')">
                                <el-tooltip class="item" effect="dark" :content="data.changeData.contacter_phone" placement="bottom">
                                    <span class="c-danger">{{data.contacter_phone}}</span>
                                </el-tooltip>
                            </template>
                            <template v-else>
                                <span>{{data.contacter_phone}}</span>
                            </template>
                        </el-form-item>
                        <el-form-item prop="service_phone" label="客服电话">
                            <template v-if="checkIsset('service_phone')">
                                <el-tooltip class="item" effect="dark" :content="data.changeData.service_phone" placement="bottom">
                                    <span class="c-danger">{{data.service_phone}}</span>
                                </el-tooltip>
                            </template>
                            <template v-else>
                                <span>{{data.service_phone}}</span>
                            </template>
                        </el-form-item>
                        <!--<el-form-item prop="site_acreage" label="商户面积">
                            {{data.site_acreage}} ㎡
                        </el-form-item>-->
                        <!--<el-form-item prop="employees_number" label="商户员工人数">
                            {{data.employees_number}} 人
                        </el-form-item>-->
                    </el-col>

                </el-col>
                <!-- 商户激活信息右侧块 -->
                <el-col v-if="data.audit_status == 1"  >
                    <el-form-item>
                        <el-button type="success" @click="audit(1)">审核通过</el-button>
                        <el-button type="warning" @click="audit(2)">审核不通过</el-button>
                        <el-button type="primary" @click="back()">返回</el-button>
                    </el-form-item>
                </el-col>

                <el-col  v-else >
                    <el-form-item >
                        <el-button type="primary" @click="back()">返回</el-button>
                        <!-- <el-button type="text" @click="previewImage(data.business_licence_pic_url)">查看</el-button> -->
                    </el-form-item>
                </el-col>
            </el-form>

            <img-preview-dialog :visible.sync="isShowPreviewImage" :url="currentPreviewImage"/>

        </el-col>
    </el-row>

</template>

<script>

    import previewImg from '../../../assets/components/img/preview-img'
    import imgPreviewDialog from '../../../assets/components/img/preview-dialog'
    import QmapChoosePoint from '../../../assets/components/qmap/qmap-choose-point'
    import AuditMessage from './audit-message'
    import api from '../../../assets/js/api'
    import 'viewerjs/dist/viewer.css'

    export default {
        name: 'merchant-detail',
        props: {
            data: Object,
            type: String,
            auditType:Number,
        },
        computed:{
        },
        data(){
            return {
                isShowPreviewImage: false,
                currentPreviewImage: '',
                isShowAuditMessage: false
            }
        },
        methods: {
            previewImage(viewerEl){
                // this.currentPreviewImage = url;
                // this.isShowPreviewImage = true;

                const viewer = this.$el.querySelector('.' + viewerEl).$viewer
                viewer.show()
            },
            checkIsset(key){
                // console.log('changeData',key,this.data.changeData[key],this.data.changeData);
                // console.log('change-data',this.data.changeData.key);
                console.log('this.data.changeData[key]',key,this.data.changeData[key],this.data[key])
                if(this.data.changeData!==null&&this.data.changeData[key]!==undefined){
                    return true;
                }
                return false;
            },
            audit(type){
                api.post('/cs/merchant/audit', {id: this.data.id, type: type,audit_suggestion:this.data.audit_suggestion}).then(data => {
                    this.$alert(['', '审核通过', '审核不通过'/*, '打回商户池'*/][type] + ' 成功');
                    this.$emit('change')
                })
            },
            back(){
                this.$emit('change')
            }
        },
        created(){
            // 判断请求路由
            if(this.$route.path.indexOf('unaudits')!==-1){
                this.isShowAuditMessage = true;
            }
        },
        components: {
            previewImg,
            imgPreviewDialog,
            QmapChoosePoint,
            AuditMessage
        }
    }

</script>

<style scoped>
    .title {
        font-weight: 600;
        line-height: 50px;
    }
</style>