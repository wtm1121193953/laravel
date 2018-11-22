<template>
    <page title="配送设置">
        <el-form v-model="form" v-loading="formLoading" label-width="200px">
            <el-form-item label="用户权益配置"></el-form-item>
            <el-form-item label="用户自返比例设置" prop="fee_splitting_ratio_to_self">
                <el-input-number v-model="form.fee_splitting_ratio_to_self" :min="0"></el-input-number>%
                <span class="tips">用户自己消费，平台奖励用户本人的</span>
            </el-form-item>
            <el-form-item label="用户分享他人奖励设置" prop="fee_splitting_ratio_to_parent_of_user">
                <el-input-number v-model="form.fee_splitting_ratio_to_parent_of_user" :min="0"></el-input-number>%
                <span class="tips">用户分享他人，他人消费后，用户可以拿到的奖励</span>
            </el-form-item>
            <el-form-item>
                <el-button type="primary" @click="back">返回</el-button>
                <el-button type="primary" @click="save">保 存</el-button>
            </el-form-item>
        </el-form>
    </page>
</template>

<script>
    export default {
        name: "delivery_setting",
        data() {
            return {
                formLoading: false,
                form: {
                    fee_splitting_ratio_to_self : 5,
                    fee_splitting_ratio_to_parent_of_user : 20,
                    fee_splitting_ratio_to_parent_of_merchant_level_1 : 20,
                    fee_splitting_ratio_to_parent_of_merchant_level_2 : 25,
                    fee_splitting_ratio_to_parent_of_merchant_level_3 : 30,
                    fee_splitting_ratio_to_parent_of_oper : 20,
                },
            }
        },
        methods: {
            save() {

            },
            back() {
                
            }
        }
    }
</script>

<style scoped>

</style>