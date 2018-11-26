<template>
    <page title="运营中心详情" :breadcrumbs="{运营中心管理: '/opers'}" v-loading="loading">
        <el-form label-width="150px" label-position="left">
            <el-form-item label="运营中心名称">
                {{oper.name}}
            </el-form-item>
            <el-form-item label="运营中心编码">
                {{oper.number}}
            </el-form-item>
            <el-form-item prop="contacter" label="负责人">
                {{oper.contacter}}
            </el-form-item>
            <el-form-item prop="tel" label="手机号码">
                {{oper.tel}}
            </el-form-item>
            <el-form-item prop="selectAreas" label="城市">
                {{oper.province}}&nbsp;{{oper.city}}&nbsp;{{oper.area}}
            </el-form-item>
            <el-form-item prop="address" label="详细地址">
                {{oper.address}}
            </el-form-item>
            <el-form-item prop="email" label="邮箱">
                {{oper.email}}
            </el-form-item>
            <el-form-item prop="legal_name" label="法人姓名">
                {{oper.legal_name}}
            </el-form-item>
            <el-form-item label="法人身份证" prop="legal_id_card">
                {{oper.legal_id_card}}
            </el-form-item>
            <el-form-item label="发票类型" prop="invoice_type">
                {{['其他', '增值税普票', '增值税专票', '国税普票'][oper.invoice_type]}}
            </el-form-item>
            <el-form-item label="发票税点" prop="invoice_tax_rate">
                {{oper.invoice_tax_rate}}
            </el-form-item>
            <el-form-item label="公司账号" prop="bank_card_no">
                {{oper.bank_card_no}}
            </el-form-item>
            <el-form-item label="开户支行名称" prop="sub_bank_name">
                {{oper.sub_bank_name}}
            </el-form-item>
            <el-form-item label="开户名" prop="bank_open_name">
                {{oper.bank_open_name}}
            </el-form-item>
            <el-form-item label="开户地址" prop="bank_open_address">
                {{oper.bank_open_address}}
            </el-form-item>
            <el-form-item label="银行代码" prop="bank_code">
                {{oper.bank_code}}
            </el-form-item>
            <el-form-item label="开户许可证" prop="licence_pic_url">
                <div v-viewer>
                    <img v-if="oper.licence_pic_url" :src="oper.licence_pic_url" width="200px" height="100px" alt="开户许可证">
                </div>
            </el-form-item>
            <el-form-item label="营业执照" prop="business_licence_pic_url">
                <div v-viewer>
                    <img v-if="oper.business_licence_pic_url" :src="oper.business_licence_pic_url" width="200px" height="100px" alt="营业执照">
                </div>
            </el-form-item>
            <el-form-item label="合作状态" prop="status">
                {{['', '正常合作中', '已冻结', '停止合作'][oper.status]}}
            </el-form-item>

            <el-form-item label="客服联系方式-QQ" prop="contact_qq">
                {{oper.contact_qq}}
            </el-form-item>
            <el-form-item label="客服联系方式-微信" prop="contact_wechat">
                {{oper.contact_wechat}}
            </el-form-item>
            <el-form-item label="客服联系方式-手机" prop="contact_mobile">
                {{oper.contact_mobile}}
            </el-form-item>

            <el-form-item>
                <el-button @click="cancel" type="primary">返 回</el-button>
            </el-form-item>
        </el-form>
    </page>
</template>

<script>
    import api from '../../../assets/js/api'

    export default {
        name: "oper-detail",
        data() {
            return {
                loading: false,
                oper: {},
                id: null,
            }
        },
        methods: {
            getDetail() {
                this.loading = true;
                api.get('oper/detail', {id: this.id}).then(data => {
                    this.oper = data;
                }).finally(() => {
                    this.loading = false;
                });
            },
            cancel() {
                router.push('/opers');
            }
        },
        created(){
            this.id = this.$route.query.id;
            if(!this.id){
                this.$message.error('id不能为空');
                router.push('/opers');
                return ;
            }
            this.getDetail();
        },
    }
</script>

<style scoped>

</style>