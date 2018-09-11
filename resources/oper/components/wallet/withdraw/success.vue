<template>
    <page title="提现申请成功">
        <el-form :model="form" v-loading="formLoading" label-width="100px">
            <el-form-item label="订单编号">
                {{form.withdraw_no}}
            </el-form-item>
            <el-form-item label="提现金额">
                {{form.amount}}
            </el-form-item>
            <el-form-item label="到账金额">
                {{form.remit_amount}}
            </el-form-item>
            <el-form-item label="手续费">
                {{form.charge_amount}}
            </el-form-item>
            <el-form-item label="提现账户名">
                {{form.bank_card_open_name}}
            </el-form-item>
            <el-form-item label="提现银行卡">
                {{form.bank_card_no}}
            </el-form-item>
            <el-form-item label="开户行">
                {{form.bank_name}}
            </el-form-item>
            <el-form-item label="发票快递公司">
                {{form.invoice_express_company}}
            </el-form-item>
            <el-form-item label="快递编号">
                {{form.invoice_express_no}}
            </el-form-item>
            <el-form-item label="提现发起时间">
                {{form.created_at}}
            </el-form-item>
            <el-form-item label="预计到账时间">
                {{(new Date((new Date()).setDate((new Date(form.created_at)).getDate() + 7))).format('yyyy-MM-dd')}} 24:00:00前，具体到账时间请以银行通知时间为准
            </el-form-item>
            <el-form-item>
                <el-button @click="goBack">完 成</el-button>
            </el-form-item>
        </el-form>
    </page>
</template>

<script>
    import api from '../../../../assets/js/api'

    export default {
        name: "wallet-withdraw-success",
        data() {
            return {
                form: {},
                formLoading: false,
            }
        },
        methods: {
            getDetail(id) {
                this.formLoading = true;
                api.get('/wallet/withdraw/getWithdrawDetail', {id: id}).then(data => {
                    this.form = data;
                    this.formLoading = false;
                })
            },
            goBack() {
                router.push('/wallet/summary/list');
            }
        },
        created() {
            let id = this.$route.query.id;
            if (!id) {
                this.$message.error('id不能为空');
                router.push('/wallet/summary/list');
            }
            this.getDetail(id);
        }
    }
</script>

<style scoped>

</style>