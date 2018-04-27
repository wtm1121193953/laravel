<template>
    <el-row>
        <el-col :span="24">
            <el-form label-width="150px" label-position="left">
                <div class="title">
                    商户基本信息:
                </div>
                <el-col :span="11">
                    <el-form-item prop="name" label="商户名称">{{data.name}}</el-form-item>
                    <el-form-item prop="brand" label="品牌">{{data.brand}}</el-form-item>
                    <el-form-item prop="region" label="运营地区">
                        {{ {1: '中国', 2: '美国', 3: '韩国', 4: '香港',}[data.region] }}
                    </el-form-item>
                    <el-form-item prop="categoryPath" label="所属行业">
                        <span v-for="item in data.categoryPath" :key="item.id">
                            {{ item.name }}
                        </span>
                    </el-form-item>
                    <el-form-item prop="area" label="省市区">{{data.privince}} {{data.city}} {{data.area}}</el-form-item>
                    <el-form-item prop="business_time" label="营业时间">{{data.business_time[0]}} 至 {{data.business_time[1]}}</el-form-item>
                    <el-form-item prop="logo" label="商家logo">
                        <preview-img :url="data.logo" width="50px" height="50px"/>
                    </el-form-item>
                    <el-form-item prop="desc_pic" label="商家介绍图片">
                        <preview-img :url="data.desc_pic" width="200px" height="100px"/>
                    </el-form-item>
                    <el-form-item prop="desc_pic" label="商家介绍">
                        {{data.desc}}
                    </el-form-item>
                </el-col>
                <el-col :span="11">
                    <el-form-item prop="invoice_title" label="发票抬头">{{data.invoice_title}}</el-form-item>
                    <el-form-item prop="invoice_no" label="发票编号">{{data.invoice_no}}</el-form-item>
                    <el-form-item prop="status" label="商户状态">
                        <span v-if="data.status === 1" class="c-green">已启用</span>
                        <span v-else-if="data.status === 2" class="c-danger">已冻结</span>
                        <span v-else>未知 ({{data.status}})</span>
                    </el-form-item>
                    <el-form-item prop="audit_status" label="审核状态">
                        <span v-if="data.audit_status === 0" class="c-gray">待审核</span>
                        <span v-else-if="data.audit_status === 1" class="c-green">审核通过</span>
                        <span v-else-if="data.audit_status === 2" class="c-danger">审核不通过</span>
                        <span v-else>未知 ({{data.audit_status}})</span>
                    </el-form-item>
                    <el-form-item prop="location" label="商户位置">
                        {{data.lng}} , {{data.lat}}
                    </el-form-item>
                    <el-form-item prop="address" label="详细地址">
                        {{data.address}}
                    </el-form-item>
                    <el-form-item prop="contacter" label="负责人姓名">
                        {{data.contacter}}
                    </el-form-item>
                    <el-form-item prop="contacter_phone" label="负责人联系方式">
                        {{data.contacter_phone}}
                    </el-form-item>
                </el-col>

                <el-col>
                    <div class="title">
                        商务信息：
                    </div>
                </el-col>
                <el-col :span="11">
                    <el-form-item label="结算周期">
                        {{ {1: '周结', 2: '半月结', 3: '月结', 4: '半年结', 5: '年结',}[data.settlement_cycle_type] }}
                    </el-form-item>
                    <el-form-item label="分利比例">
                        {{ data.settlement_rate }} %
                    </el-form-item>
                    <el-form-item label="营业执照">
                        <el-button type="text" @click="previewImage(data.business_licence_pic_url)">查看</el-button>
                    </el-form-item>
                    <el-form-item label="营业执照代码">
                        {{ data.organization_code}}
                    </el-form-item>
                    <el-form-item label="税务登记证">
                        <el-button type="text" @click="previewImage(data.tax_cert_pic_url)">查看</el-button>
                    </el-form-item>
                    <el-form-item label="法人身份证正反面">
                        <preview-img :url="data.legal_id_card_pic_a" width="200px" height="100px"/>
                        <preview-img :url="data.legal_id_card_pic_b" width="200px" height="100px"/>
                    </el-form-item>
                    <el-form-item label="税务登记证">
                        <el-button type="text" @click="previewImage(data.tax_cert_pic_url)">查看</el-button>
                    </el-form-item>
                    <el-form-item label="合同">
                        <el-button type="text" @click="previewImage(data.contract_pic_url)">查看</el-button>
                    </el-form-item>
                    <el-form-item label="开户许可证">
                        <el-button type="text" @click="previewImage(data.licence_pic_url)">查看</el-button>
                    </el-form-item>
                    <el-form-item label="卫生许可证">
                        <el-button type="text" @click="previewImage(data.hygienic_licence_pic_url)">查看</el-button>
                    </el-form-item>
                    <el-form-item label="协议文件（必填）">
                        <el-button type="text" @click="previewImage(data.agreement_pic_url)">查看</el-button>
                    </el-form-item>
                </el-col>
                <el-col :span="11">
                    <el-form-item label="银行账户类型">
                        <span v-if="data.bank_card_type === 1" class="c-gray">公司</span>
                        <span v-if="data.bank_card_type === 2" class="c-green">个人</span>
                    </el-form-item>
                    <el-form-item label="开户名">
                        {{data.bank_open_name}}
                    </el-form-item>
                    <el-form-item label="银行账号">
                        {{data.bank_card_no}}
                    </el-form-item>
                    <el-form-item label="开户支行名称">
                        {{data.sub_bank_name}}
                    </el-form-item>
                    <el-form-item label="开户支行地址">
                        {{data.bank_open_address}}
                    </el-form-item>
                </el-col>

                <el-col>
                    <el-form-item v-if="data.audit_status === 0">
                        <el-button type="success" @click="audit(1)">审核通过</el-button>
                        <el-button type="warning" @click="audit(2)">审核不通过</el-button>
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
    import api from '../../../assets/js/api'

    export default {
        name: 'merchant-detail',
        props: {
            data: Object,
        },
        computed:{

        },
        data(){
            return {
                isShowPreviewImage: false,
                currentPreviewImage: '',
            }
        },
        methods: {
            previewImage(url){
                this.currentPreviewImage = url;
                this.isShowPreviewImage = true;
            },
            audit(status){
                api.post('/merchant/audit', {id: this.data.id, audit_status: status}).then(data => {
                    this.$alert(status === 1 ? '审核通过' : '审核不通过');
                    this.$emit('change')
                })
            }
        },
        created(){
        },
        components: {
            previewImg,
            imgPreviewDialog,
        }
    }
</script>

<style scoped>
    .title {
        font-weight: 600;
        line-height: 50px;
    }
</style>