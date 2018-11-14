<template>
    <page title="商户电子合同管理" v-loading="isLoading">
        <el-form :model="query" inline size="small" class="fl" @submit.native.prevent>
            <el-form-item prop="merchantId" label="商户ID" >
                <el-input v-model="query.merchantId" size="small"  placeholder="商户ID"  class="w-200" clearable></el-input>
            </el-form-item>
            <el-form-item prop="merchantName" label="商户名称" >
                <el-input v-model="query.merchantName" size="small"  placeholder="商户名称"  class="w-200" clearable></el-input>
            </el-form-item>
            <el-form-item prop="contractNo" label="合同编号" >
                <el-input v-model="query.contractNo" size="small"  placeholder="合同编号"  class="w-200" clearable></el-input>
            </el-form-item>
            <el-form-item label="状态" prop="status">
                <el-select v-model="query.status" size="small" placeholder="请选择" clearable class="w-150">
                    <el-option label="全部" :value="0"/>
                    <el-option label="正常" :value="1"/>
                    <el-option label="已过期" :value="2"/>
                </el-select>
            </el-form-item>
            <el-form-item prop="operName" label="激活运营中心名称" >
                <el-input v-model="query.operName" size="small"  placeholder="激活运营中心名称"  class="w-200" clearable></el-input>
            </el-form-item>
            <el-form-item prop="operId" label="激活运营中心ID" >
                <el-input v-model="query.operId" size="small"  placeholder="激活运营中心ID"  class="w-200" clearable></el-input>
            </el-form-item>

            <el-form-item>
                <el-button type="primary" @click="search">搜索</el-button>
            </el-form-item>
        </el-form>

        <el-table :data="list" stripe>
            <el-table-column prop="el_contract_no" label="合同编号"/>
            <el-table-column prop="merchant_id" label="商户ID"/>
            <el-table-column prop="merchant.name" label="商户名称"/>
            <el-table-column prop="oper.id" label="激活运营中心ID"/>
            <el-table-column prop="oper.name" label="激活运营中心名称"/>
            <el-table-column label="合同状态">
                <template slot-scope="scope">
                    <span v-if="scope.row.status == 1" class="c-green">正常</span>
                    <span v-else-if="scope.row.status == 0" class="c-danger">已过期</span>
                    <span v-else>未知({{scope.row.status}})</span>
                </template>
            </el-table-column>
            <el-table-column prop="sign_time" label="签约时间"></el-table-column>
            <el-table-column prop="expiry_time" label="失效时间"></el-table-column>
            <el-table-column label="操作">
                <template slot-scope="scope">
                    <el-button type="text" @click="detail(scope.row)">查 看</el-button>
                </template>
            </el-table-column>

        </el-table>
        <el-pagination
                class="fr m-t-20"
                layout="total, prev, pager, next"
                :current-page.sync="query.page"
                @current-change="getList"
                :page-size="query.pageSize"
                :total="total"/>


        <el-dialog
                title="大千生活商户服务协议"
                :visible.sync="showElectronicContract"
                center
        >
            <electronic-contract
                    @closeElectronicContract="showElectronicContract = false"
                    :electronicContract="electronicContract"
            ></electronic-contract>
        </el-dialog>
    </page>
</template>

<script>
    import api from '../../../../assets/js/api'
    import ElectronicContract from './contract-detail'

    export default {
        data() {
            return {
                isLoading: false,
                query: {
                    page: 1,
                    pageSize: 15,
                    merchantId: '',
                    merchantName: '',
                    contractNo: '',
                    status: 0,
                    operName: '',
                    operId: '',
                },
                list: [],
                total: 0,

                electronicContract: null,
                showElectronicContract: false,
            }
        },
        methods: {
            getList() {
                this.isLoading = true;
                api.get('/merchant/getElectronicContractList', this.query).then(data => {
                    this.list = data.list;
                    this.total = data.total;
                    this.isLoading = false;
                })
            },
            search() {
                this.query.page = 1;
                this.getList();
            },
            detail(row) {
                api.get('/merchant/getElectronicContractDetail', {id: row.id}).then(data => {
                    this.electronicContract = data;
                    let signTime = new Date(data.sign_time);
                    this.electronicContract.sign_time = '【' + signTime.getFullYear() + '】年【' + (signTime.getMonth() + 1) + '】月【' + signTime.getDate() + '】日';
                    let expiryTime = new Date(data.expiry_time);
                    this.electronicContract.expiry_time = '【' + (expiryTime.getFullYear()) + '】年【' + (expiryTime.getMonth() + 1) + '】月【' + expiryTime.getDate() + '】日';
                    this.showElectronicContract = true;
                })
            }
        },
        created() {
            this.getList();
        },
        components: {
            ElectronicContract,
        },
    }
</script>

<style scoped>

</style>