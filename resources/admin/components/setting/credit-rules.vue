<template>
    <page title="参数配置">
        <el-form v-model="form" v-loading="formLoading" label-width="200px">
            <el-form-item label="用户权益配置"></el-form-item>
            <el-form-item label="用户自返比例设置" prop="user_level_2_of_credit_number">
                <el-input-number v-model="form.user_level_2_of_credit_number" :min="0"></el-input-number>%
                <span class="tips">用户自己消费，平台奖励用户本人的</span>
            </el-form-item>
            <el-form-item label="用户分享他人奖励设置" prop="user_level_3_of_credit_number">
                <el-input-number v-model="form.user_level_3_of_credit_number" :min="0"></el-input-number>%
                <span class="tips">用户分享他人，他人消费后，用户可以拿到的奖励</span>
            </el-form-item>

            <el-form-item label="商户权益配置"></el-form-item>
            <el-form-item label="普通商户奖励" prop="merchant_level_1_of_invite_user_number">
                <el-input-number v-model="form.merchant_level_2_of_invite_user_number" :min="0"></el-input-number>%
                <span class="tips">商户分享的用户消费，平台奖励商户的</span>
            </el-form-item>
            <el-form-item label="金牌商户奖励" prop="merchant_level_2_of_invite_user_number">
                <el-input-number v-model="form.merchant_level_2_of_invite_user_number" :min="0"></el-input-number>%
                <span class="tips">商户分享的用户消费，平台奖励商户的</span>
            </el-form-item>
            <el-form-item label="超级商户奖励" prop="merchant_level_3_of_invite_user_number">
                <el-input-number v-model="form.merchant_level_3_of_invite_user_number" :min="0"></el-input-number>%
                <span class="tips">商户分享的用户消费，平台奖励商户的</span>
            </el-form-item>

            <el-form-item label="运营中心权益配置"></el-form-item>
            <el-form-item label="运营中心分享奖励比例设置" prop="credit_multiplier_of_merchant_level_1">
                <el-input-number v-model="form.credit_multiplier_of_merchant_level_1" :min="0"></el-input-number>%
                <span class="tips">运营中心分享的用户消费，平台奖励运营中心的</span>
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