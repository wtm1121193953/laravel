<template>
    <page title="商户端电子合同开关设置" v-show="show">
        <el-form v-model="form" label-width="150px">
            <el-form-item
                          label="商户端电子合同开关"
                          prop="merchant_electronic_contract">
                <el-switch
                        v-model="form.merchant_electronic_contract"
                        active-text="开启"
                        inactive-text="关闭"
                        active-value="1"
                        inactive-value="0">
                </el-switch>
                <div>商户端是否显示电子合同的签约和详情页</div>
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
        data(){
            return {
                form: {
                    merchant_electronic_contract: '',
                },
                show: false,
            }
        },
        methods: {
            save(){
                api.post('/setting/setMerchantElectronicContract', this.form).then(data => {
                    this.$message.success('保存配置成功');
                    this.getSetting();
                });
            },
            getSetting(){
                api.get('/setting/getMerchantElectronicContractList').then(data => {
                    if (data.list.merchant_electronic_contract) {
                        this.form = data.list;
                    }
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