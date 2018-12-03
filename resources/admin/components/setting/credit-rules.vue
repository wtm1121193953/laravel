<template>
    <page title="参数配置">
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

            <el-form-item label="商户权益配置"></el-form-item>
            <el-form-item label="普通商户奖励" prop="fee_splitting_ratio_to_parent_of_merchant_level_1">
                <el-input-number v-model="form.fee_splitting_ratio_to_parent_of_merchant_level_1" :min="0"></el-input-number>%
                <span class="tips">商户分享的用户消费，平台奖励商户的</span>
            </el-form-item>
            <el-form-item label="金牌商户奖励" prop="fee_splitting_ratio_to_parent_of_merchant_level_2">
                <el-input-number v-model="form.fee_splitting_ratio_to_parent_of_merchant_level_2" :min="0"></el-input-number>%
                <span class="tips">商户分享的用户消费，平台奖励商户的</span>
            </el-form-item>
            <el-form-item label="超级商户奖励" prop="fee_splitting_ratio_to_parent_of_merchant_level_3">
                <el-input-number v-model="form.fee_splitting_ratio_to_parent_of_merchant_level_3" :min="0"></el-input-number>%
                <span class="tips">商户分享的用户消费，平台奖励商户的</span>
            </el-form-item>

            <el-form-item label="运营中心权益配置"></el-form-item>
            <el-form-item label="运营中心分享奖励比例设置" prop="fee_splitting_ratio_to_parent_of_oper">
                <el-input-number v-model="form.fee_splitting_ratio_to_parent_of_oper" :min="0"></el-input-number>%
                <span class="tips">运营中心分享的用户消费，平台奖励运营中心的</span>
            </el-form-item>

            <el-form-item label="超市配置"></el-form-item>
            <el-form-item label="超市开关" prop="supermarket_on">
                <el-switch
                        v-model="form.supermarket_on"
                        :active-value="1"
                        :inactive-value="0"
                >
                </el-switch>
                <span class="tips">超市开关</span>
            </el-form-item>
            <el-form-item label="是否限制只能选择同城地址" prop="supermarket_city_limit">
                <el-switch
                        v-model="form.supermarket_city_limit"
                        :active-value="1"
                        :inactive-value="0"
                >
                </el-switch>
                <span class="tips">是否限制只能选择同城地址</span>
            </el-form-item>
            <el-form-item label="是否显示只能选择同城的文字" prop="supermarket_show_city_limit">
                <el-switch
                        v-model="form.supermarket_show_city_limit"
                        :active-value="1"
                        :inactive-value="0"
                >
                </el-switch>
                <span class="tips">是否显示只能选择同城的文字</span>
            </el-form-item>
            <el-form-item label="首页超市banner" prop="supermarket_index_cs_banner_on">
                <el-switch
                        v-model="form.supermarket_index_cs_banner_on"
                        :active-value="1"
                        :inactive-value="0"
                >
                </el-switch>
                <span class="tips">首页超市banner</span>
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
                    fee_splitting_ratio_to_self : 5,
                    fee_splitting_ratio_to_parent_of_user : 20,
                    fee_splitting_ratio_to_parent_of_merchant_level_1 : 20,
                    fee_splitting_ratio_to_parent_of_merchant_level_2 : 25,
                    fee_splitting_ratio_to_parent_of_merchant_level_3 : 30,
                    fee_splitting_ratio_to_parent_of_oper : 20,
                    supermarket_on:0,
                    supermarket_city_limit:0,
                    supermarket_show_city_limit:0,
                    supermarket_index_cs_banner_on:0
                },
            }
        },
        methods: {
            save() {
                console.log(this.form);
                api.post('/setting/setCreditRules', this.form).then(() => {
                    this.$message.success('保存成功');
                })
            },
            initForm() {
                this.formLoading = true;
                let self = this;
                api.get('/setting/getCreditRulesList').then(data => {
                    for (let key in self.form) {
                        if (data.list.hasOwnProperty(key)) {
                            self.form[key] = data.list[key];
                        }
                    }
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