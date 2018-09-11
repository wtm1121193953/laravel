<template>
    <page title="参数配置">
        <el-form v-model="form" v-loading="formLoading" label-width="200px">
            <el-form-item label="用户等级积分参数配置"></el-form-item>
            <el-form-item label="萌新" prop="user_level_1_of_credit_number">
                <span>0</span>
            </el-form-item>
            <el-form-item label="粉丝" prop="user_level_2_of_credit_number">
                <el-input-number v-model="form.user_level_2_of_credit_number" :min="0"></el-input-number>
            </el-form-item>
            <el-form-item label="铁杆" prop="user_level_3_of_credit_number">
                <el-input-number v-model="form.user_level_3_of_credit_number" :min="0"></el-input-number>
            </el-form-item>
            <el-form-item label="骨灰" prop="user_level_4_of_credit_number">
                <el-input-number v-model="form.user_level_4_of_credit_number" :min="0"></el-input-number>
            </el-form-item>

            <el-form-item label="商户等级数量参数配置"></el-form-item>
            <el-form-item label="签约商户" prop="merchant_level_1_of_invite_user_number">
                <span>0</span>
            </el-form-item>
            <el-form-item label="联盟商户" prop="merchant_level_2_of_invite_user_number">
                <el-input-number v-model="form.merchant_level_2_of_invite_user_number" :min="0"></el-input-number>
            </el-form-item>
            <el-form-item label="品牌商户" prop="merchant_level_3_of_invite_user_number">
                <el-input-number v-model="form.merchant_level_3_of_invite_user_number" :min="0"></el-input-number>
            </el-form-item>

            <el-form-item label="自返比例参数配置"></el-form-item>
            <el-form-item label="萌新" prop="credit_to_self_ratio_of_user_level_1">
                <el-input-number v-model="form.credit_to_self_ratio_of_user_level_1" :min="0"></el-input-number>
            </el-form-item>
            <el-form-item label="粉丝" prop="credit_to_self_ratio_of_user_level_2">
                <el-input-number v-model="form.credit_to_self_ratio_of_user_level_2" :min="0"></el-input-number>
            </el-form-item>
            <el-form-item label="铁杆" prop="credit_to_self_ratio_of_user_level_3">
                <el-input-number v-model="form.credit_to_self_ratio_of_user_level_3" :min="0"></el-input-number>
            </el-form-item>
            <el-form-item label="骨灰" prop="credit_to_self_ratio_of_user_level_4">
                <el-input-number v-model="form.credit_to_self_ratio_of_user_level_4" :min="0"></el-input-number>
            </el-form-item>

            <el-form-item label="直推返利比例参数配置"></el-form-item>
            <el-form-item label="粉丝" prop="credit_to_parent_ratio_of_user_level_2">
                <el-input-number v-model="form.credit_to_parent_ratio_of_user_level_2" :min="0"></el-input-number>
            </el-form-item>
            <el-form-item label="铁杆" prop="credit_to_parent_ratio_of_user_level_3">
                <el-input-number v-model="form.credit_to_parent_ratio_of_user_level_3" :min="0"></el-input-number>
            </el-form-item>
            <el-form-item label="骨灰" prop="credit_to_parent_ratio_of_user_level_4">
                <el-input-number v-model="form.credit_to_parent_ratio_of_user_level_4" :min="0"></el-input-number>
            </el-form-item>

            <el-form-item label="商家等级加成比例参数配置"></el-form-item>
            <el-form-item label="签约商家" prop="credit_multiplier_of_merchant_level_1">
                <el-input-number v-model="form.credit_multiplier_of_merchant_level_1" :min="0"></el-input-number>
            </el-form-item>
            <el-form-item label="联盟商户" prop="credit_multiplier_of_merchant_level_2">
                <el-input-number v-model="form.credit_multiplier_of_merchant_level_2" :min="0"></el-input-number>
            </el-form-item>
            <el-form-item label="品牌商户" prop="credit_multiplier_of_merchant_level_3">
                <el-input-number v-model="form.credit_multiplier_of_merchant_level_3" :min="0"></el-input-number>
            </el-form-item>

            <el-form-item label="其他"></el-form-item>
            <el-form-item label="运营中心抽成比例(百分比)" prop="oper_profit_radio">
                <el-input-number v-model="form.oper_profit_radio" :min="0"></el-input-number>
            </el-form-item>
            <el-form-item label="消费额直推比例(百分比)" prop="consume_quota_convert_ratio_to_parent">
                <el-input-number v-model="form.consume_quota_convert_ratio_to_parent" :min="0"></el-input-number>
            </el-form-item>
            <el-form-item label="积分兑换比例(倍数)" prop="credit_multiplier_of_amount">
                <el-input-number v-model="form.credit_multiplier_of_amount" :min="0"></el-input-number>
            </el-form-item>
            <el-form-item>
                <el-button type="primary" @click="save">保 存</el-button>
            </el-form-item>
        </el-form>
    </page>
</template>

<script>
    import api from '../../../assets/js/api'

    export default {
        name: "credit-rules",
        data() {
            return {
                formLoading: false,
                form: {
                    oper_profit_radio: 0,
                    consume_quota_convert_ratio_to_parent: 0,
                    credit_multiplier_of_amount: 0,
                    user_level_1_of_credit_number: 0,
                    user_level_2_of_credit_number: 0,
                    user_level_3_of_credit_number: 0,
                    user_level_4_of_credit_number: 0,
                    merchant_level_1_of_invite_user_number: 0,
                    merchant_level_2_of_invite_user_number: 0,
                    merchant_level_3_of_invite_user_number: 0,
                    credit_to_self_ratio_of_user_level_1: 0,
                    credit_to_self_ratio_of_user_level_2: 0,
                    credit_to_self_ratio_of_user_level_3: 0,
                    credit_to_self_ratio_of_user_level_4: 0,
                    credit_to_parent_ratio_of_user_level_2: 0,
                    credit_to_parent_ratio_of_user_level_3: 0,
                    credit_to_parent_ratio_of_user_level_4: 0,
                    credit_multiplier_of_merchant_level_1: 0,
                    credit_multiplier_of_merchant_level_2: 0,
                    credit_multiplier_of_merchant_level_3: 0,
                },
            }
        },
        methods: {
            save() {
                api.post('/setting/setCreditRules', this.form).then(() => {
                    this.$message.success('保存成功');
                })
            },
            initForm() {
                this.formLoading = true;
                api.get('/setting/getCreditRulesList').then(data => {
                    this.form = data.list;
                    this.formLoading = false;
                })
            }
        },
        created() {
            this.initForm();
        }
    }
</script>

<style scoped>

</style>