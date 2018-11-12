<template>
    <page title="电子合同管理">
        <el-form label-width="100px" v-loading="formLoading" v-if="!formLoading">
            <el-form-item label="合同编号">{{electronicContract.el_contract_no}}</el-form-item>
            <el-form-item label="签约时间">{{electronicContract.sign_time}}</el-form-item>
            <el-form-item label="失效时间">{{electronicContract.expiry_time}}</el-form-item>
            <el-form-item label="状态">{{electronicContract.status ? '正常' : '已过期'}}</el-form-item>
            <el-form-item label="操作">
                <el-button type="text" @click="showContract">查看</el-button>
            </el-form-item>
        </el-form>

        <el-dialog
                title="大千生活商户服务协议"
                :visible.sync="showElectronicContract"
                center
        >
            <electronic-contract
                    @closeElectronicContract="showElectronicContract = false"
                    :check="true"
            ></electronic-contract>
        </el-dialog>
    </page>
</template>

<script>
    import api from '../../../assets/js/api'
    import ElectronicContract from './contract-detail'

    export default {
        data() {
            return {
                electronicContract: null,
                showElectronicContract: false,
                formLoading: false,
            }
        },
        methods: {
            getContract() {
                this.formLoading  = true;
                api.get('/self/checkElectronicContract').then(data => {
                    this.electronicContract = data;
                    this.formLoading = false;
                })
            },
            showContract() {
                this.showElectronicContract = true;
            }
        },
        created() {
            this.getContract();
        },
        components: {
            ElectronicContract,
        },
    }
</script>

<style scoped>

</style>