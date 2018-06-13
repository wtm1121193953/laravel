<template>
    <page title="积分规则配置">
        <el-form v-model="form" v-loading="formLoading" label-width="300px">
            <el-form-item label="运营中心抽成比例(百分比)" prop="oper_profit_radio">
                <el-input-number v-model="form.oper_profit_radio" :min="0"></el-input-number>
            </el-form-item>
            <el-form-item label="直推用户累计额度换算系数(百分比)" prop="consume_quota_convert_ratio_to_parent">
                <el-input-number v-model="form.consume_quota_convert_ratio_to_parent" :min="0"></el-input-number>
            </el-form-item>
            <el-form-item label="积分系数配置" prop="credit_multiplier_of_amount">
                <el-input-number v-model="form.credit_multiplier_of_amount" :min="0"></el-input-number>
            </el-form-item>
            <el-form-item label="用户等级1(萌新)对应的积分数量" prop="user_level_1_of_credit_number">
                <el-input-number v-model="form.user_level_1_of_credit_number" :min="0"></el-input-number>
            </el-form-item>
            <el-form-item label="用户等级2(粉丝)对应的积分数量" prop="user_level_2_of_credit_number">
                <el-input-number v-model="form.user_level_2_of_credit_number" :min="0"></el-input-number>
            </el-form-item>
            <el-form-item label="用户等级3(铁杆)对应的积分数量" prop="user_level_3_of_credit_number">
                <el-input-number v-model="form.user_level_3_of_credit_number" :min="0"></el-input-number>
            </el-form-item>
            <el-form-item label="用户等级4(骨灰)对应的积分数量" prop="user_level_4_of_credit_number">
                <el-input-number v-model="form.user_level_4_of_credit_number" :min="0"></el-input-number>
            </el-form-item>
            <el-form-item label="商户等级1(签约商户)对应的邀请用户数量" prop="merchant_level_1_of_invite_user_number">
                <el-input-number v-model="form.merchant_level_1_of_invite_user_number" :min="0"></el-input-number>
            </el-form-item>
            <el-form-item label="商户等级2(联盟商户)对应的邀请用户数量" prop="merchant_level_2_of_invite_user_number">
                <el-input-number v-model="form.merchant_level_2_of_invite_user_number" :min="0"></el-input-number>
            </el-form-item>
            <el-form-item label="商户等级3(品牌商户)对应的邀请用户数量" prop="merchant_level_3_of_invite_user_number">
                <el-input-number v-model="form.merchant_level_3_of_invite_user_number" :min="0"></el-input-number>
            </el-form-item>
            <el-form-item label="自返比例: 用户等级1(萌新), 百分比" prop="credit_to_self_ratio_of_user_level_1">
                <el-input-number v-model="form.credit_to_self_ratio_of_user_level_1" :min="0"></el-input-number>
            </el-form-item>
            <el-form-item label="自返比例: 用户等级2(粉丝), 百分比" prop="credit_to_self_ratio_of_user_level_2">
                <el-input-number v-model="form.credit_to_self_ratio_of_user_level_2" :min="0"></el-input-number>
            </el-form-item>
            <el-form-item label="自返比例: 用户等级3(铁杆), 百分比" prop="credit_to_self_ratio_of_user_level_3">
                <el-input-number v-model="form.credit_to_self_ratio_of_user_level_3" :min="0"></el-input-number>
            </el-form-item>
            <el-form-item label="自返比例: 用户等级4(骨灰), 百分比" prop="credit_to_self_ratio_of_user_level_4">
                <el-input-number v-model="form.credit_to_self_ratio_of_user_level_4" :min="0"></el-input-number>
            </el-form-item>
            <el-form-item label="分享提成: 用户等级2(粉丝), 百分比" prop="credit_to_parent_ratio_of_user_level_2">
                <el-input-number v-model="form.credit_to_parent_ratio_of_user_level_2" :min="0"></el-input-number>
            </el-form-item>
            <el-form-item label="分享提成: 用户等级3(铁杆), 百分比" prop="credit_to_parent_ratio_of_user_level_3">
                <el-input-number v-model="form.credit_to_parent_ratio_of_user_level_3" :min="0"></el-input-number>
            </el-form-item>
            <el-form-item label="分享提成: 用户等级4(骨灰), 百分比" prop="credit_to_parent_ratio_of_user_level_4">
                <el-input-number v-model="form.credit_to_parent_ratio_of_user_level_4" :min="0"></el-input-number>
            </el-form-item>
            <el-form-item label="商户等级加成: 商户等级1(签约商家), 倍数" prop="credit_multiplier_of_merchant_level_1">
                <el-input-number v-model="form.credit_multiplier_of_merchant_level_1" :min="0"></el-input-number>
            </el-form-item>
            <el-form-item label="商户等级加成: 商户等级2(联盟商户), 倍数" prop="credit_multiplier_of_merchant_level_2">
                <el-input-number v-model="form.credit_multiplier_of_merchant_level_2" :min="0"></el-input-number>
            </el-form-item>
            <el-form-item label="商户等级加成: 商户等级2(品牌商户), 倍数" prop="credit_multiplier_of_merchant_level_3">
                <el-input-number v-model="form.credit_multiplier_of_merchant_level_3" :min="0"></el-input-number>
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