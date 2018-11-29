<template>
    <page title="配送设置">
        <el-form v-model="form" v-loading="formLoading" label-width="200px">
            <el-form-item label="起送价" prop="fee_splitting_ratio_to_self">
                <el-input-number v-model="form.delivery_start_price" :min="0"></el-input-number>元

            </el-form-item>
            <el-form-item label="配送费" prop="fee_splitting_ratio_to_parent_of_user">
                <el-input-number v-model="form.delivery_charges" :min="0"></el-input-number>元

            </el-form-item>
            <el-form-item v-if="form.delivery_charges!=0" label="订单满免配送费" prop="fee_splitting_ratio_to_parent_of_user">
                <el-switch
                        v-model="form.delivery_free_start"
                        :active-value="1"
                        :inactive-value="0"
                >
                </el-switch>
                <span v-if="form.delivery_free_start == 1" class="m-l-20">
                    订单价格满 <el-input-number v-model="form.delivery_free_order_amount" :min="0"></el-input-number>元，免配送费
                </span>
            </el-form-item>

            <el-form-item>
                <el-button type="primary" @click="back">返回</el-button>
                <el-button type="primary" @click="save">保 存</el-button>
            </el-form-item>
        </el-form>
    </page>
</template>

<script>
    import api from '../../../assets/js/api'

    export default {
        name: "delivery_setting",
        data() {
            return {
                formLoading: false,
                form: {
                    delivery_start_price : '',
                    delivery_charges : '',
                    delivery_free_start : false,
                    delivery_free_order_amount : ''
                },
            }
        },
        methods: {
            save() {
                api.post('/setting/saveDeliverySetting', this.form).then(() => {
                    this.$message.success('保存成功');
                })
            },
            back() {

            },

            getSetting() {

                api.get('/setting/getDeliverySetting').then(data => {
                    this.form = data;
                    this.formLoading = false;
                })
            }
        },
        created() {

            this.getSetting();
        }
    }
</script>

<style scoped>

</style>