<template>
    <page title="小程序端商户共享设置" v-show="show">
        <el-form v-model="form" label-width="150px">
            <el-form-item
                          label="小程序端商户共享"
                          prop="merchant_share_in_miniprogram">
                <el-switch
                        v-model="form.merchant_share_in_miniprogram"
                        active-text="共享"
                        inactive-text="不共享"
                        active-value="1"
                        inactive-value="0">
                </el-switch>
                <div>不同运营中心的小程序共享用户间的商户与订单</div>
            </el-form-item>
            <el-form-item>
                <el-button type="primary" @click="save">保存</el-button>
            </el-form-item>
        </el-form>
    </page>
</template>

<script>
    import api from '../../../assets/js/api'

    export default {
        name: "index",
        data(){
            return {
                form: {
                    merchant_share_in_miniprogram: ''
                },
                show: false,
            }
        },
        methods: {
            save(){
                api.post('/setting/edit', this.form).then(data => {
                    this.$message.success('保存配置成功');
                    this.getSetting();
                });
            },
            getSetting(){
                api.get('/settings').then(data => {
                    this.form = data.list;
                    this.show = true;
                });
            }
        },
        created(){
            this.getSetting();
        }
    }
</script>

<style scoped>

</style>