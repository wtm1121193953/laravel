<template>
    <page title="电子合同管理">
        <el-col v-if="electronicContract.contract_switch == 0">
            努力上线中，敬请期待~
        </el-col>
        <el-form label-width="100px" v-loading="formLoading" v-else>
            <el-form-item label="合同编号">{{electronicContract.el_contract_no}}</el-form-item>
            <el-form-item label="签约时间">{{electronicContract.sign_time}}</el-form-item>
            <el-form-item label="失效时间">{{electronicContract.expiry_time}}</el-form-item>
            <el-form-item label="状态">{{electronicContract.status ? '正常' : '已过期'}}</el-form-item>
            <el-form-item label="操作">
                <el-button type="text" @click="showContract">查看</el-button>
            </el-form-item>
        </el-form>

        <el-dialog
                title=""
                :visible.sync="showElectronicContract"
                center
        >
            <el-button @click="doPrint" size="small" class="fr">打印</el-button>
            <iframe id="printed" :src="detailPath" frameborder="0" style="width: 100%; min-height: 500px"></iframe>
            <!--<electronic-contract-->
                    <!--@closeElectronicContract="showElectronicContract = false"-->
                    <!--:check="true"-->
            <!--&gt;</electronic-contract>-->
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
                detailPath: window.location.origin + '/api/merchant/self/showElectronicContract'
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
            },
            doPrint() {
                var printWin = window.open(document.getElementById("printed").src);
                printWin.print();
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