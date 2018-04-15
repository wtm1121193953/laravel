<template>
    <page title="商户审核管理" v-loading="isLoading">
        <el-table :data="list" stripe>
            <el-table-column prop="created_at" label="添加时间"/>
            <el-table-column prop="id" label="ID"/>
            <el-table-column prop="name" label="商户名称"/>
            <el-table-column prop="categoryPath" label="行业">
                <template slot-scope="scope">
                    <span v-for="item in scope.row.categoryPath" :key="item.id">
                        {{ item.name }}
                    </span>
                </template>
            </el-table-column>
            <el-table-column prop="city" label="城市">
                <template slot-scope="scope">
                    <!--<span> {{ scope.row.province }} </span>-->
                    <span> {{ scope.row.city }} </span>
                    <span> {{ scope.row.area }} </span>
                </template>
            </el-table-column>
            <el-table-column prop="audit_status" label="审核状态">
                <template slot-scope="scope">
                    <span v-if="scope.row.audit_status === 0" class="c-warning">待审核</span>
                    <span v-else-if="scope.row.audit_status === 1" class="c-green">审核通过</span>
                    <span v-else-if="scope.row.audit_status === 2" class="c-danger">审核不通过</span>
                    <span v-else-if="scope.row.audit_status === 3" class="c-warning">待审核(重新提交)</span>
                    <span v-else>未知 ({{scope.row.audit_status}})</span>
                </template>
            </el-table-column>
            <el-table-column label="操作" width="250px">
                <template slot-scope="scope">
                    <el-button type="text" @click="detail(scope)">查看</el-button>
                    <template v-if="scope.row.audit_status === 0">
                        <el-button type="text" @click="audit(scope, 1)">审核通过</el-button>
                        <el-button type="text" @click="audit(scope, 2)">审核不通过</el-button>
                    </template>
                </template>
            </el-table-column>
        </el-table>
        <el-pagination
                class="fr m-t-20"
                layout="total, prev, pager, next"
                :current-page.sync="query.page"
                @current-change="getList"
                :page-size="15"
                :total="total"/>

        <el-dialog :visible.sync="showDetail" width="70%" title="商户详情">
            <merchant-detail :data="currentMerchant" @change="() => {getList(); showDetail = false;}"/>
        </el-dialog>
    </page>
</template>

<script>
    import api from '../../../assets/js/api'
    import MerchantDetail from './merchant-detail'

    export default {
        name: "merchant-list",
        data(){
            return {
                showDetail: false,
                isLoading: false,
                query: {
                    page: 1,
                },
                list: [],
                total: 0,
                currentMerchant: null,
            }
        },
        computed: {

        },
        methods: {
            getList(){
                api.get('/merchants', this.query).then(data => {
                    this.list = data.list;
                    this.total = data.total;
                })
            },
            detail(scope){
                this.showDetail = true;
                this.currentMerchant = scope.row;
            },
            audit(scope, status){
                api.post('/merchant/audit', {id: scope.row.id, audit_status: status}).then(data => {
                    this.$alert(status === 1 ? '审核通过' : '审核不通过');
                    this.getList();
                })
            }
        },
        created(){
            this.getList();
        },
        components: {
            MerchantDetail,
        }
    }
</script>

<style scoped>

</style>