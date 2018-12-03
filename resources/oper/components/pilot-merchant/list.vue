<template>
    <page title="试点商户管理" v-loading="isLoading">
        <el-form class="fl" inline size="small">
            <el-form-item label="商户ID">
                <el-select v-model="query.merchantId" placeholder="输入商户ID或商户名" filterable clearable >
                    <el-option v-for="item in merchants" :key="item.id" :value="item.id" :label="item.name"/>
                </el-select>
            </el-form-item>

            <el-form-item label="商户名称" prop="name">
                <el-input v-model="query.name" @keyup.enter.native="search" clearable placeholder="商户名称"/>
            </el-form-item>

            <el-form-item prop="signboardName" label="商户招牌名" >
                <el-input v-model="query.signboardName" size="small" placeholder="商家招牌名" class="w-200" clearable @keyup.enter.native="search"/>
            </el-form-item>

            <el-form-item prop="merchant_category" label="所属行业">
                <el-cascader
                        change-on-select
                        clearable
                        filterable
                        :options="categoryOptions"
                        :props="{
                            value: 'id',
                            label: 'name',
                            children: 'sub',
                        }"
                        v-model="query.merchant_category">
                </el-cascader>
            </el-form-item>

            <el-form-item label="状态" prop="status">
                <el-select v-model="query.status" clearable>
                    <el-option label="全部" value=""/>
                    <el-option label="正常" value="1"/>
                    <el-option label="已冻结" value="2"/>
                </el-select>
            </el-form-item>
            <el-form-item label="审核状态" prop="audit_status">
                <el-select v-model="query.audit_status" placeholder="请选择" clearable>
                    <el-option label="全部" value=""/>
                    <el-option label="待审核" value="-1"/>
                    <el-option label="审核通过" value="1"/>
                    <el-option label="审核不通过" value="2"/>
                    <el-option label="重新提交审核" value="3"/>
                </el-select>
            </el-form-item>
            <el-form-item>
                <el-button type="primary" @click="search"><i class="el-icon-search">搜索</i></el-button>
            </el-form-item>
            <el-button type="primary" size="small" class="m-l-30" @click="exportExcel">导出Excel</el-button>
        </el-form>
        <el-button class="fr" type="primary" @click="add">录入试点商户</el-button>
        <el-table :data="list" stripe>
            <el-table-column prop="created_at" label="添加时间"/>
            <el-table-column prop="id" width="80" label="商户ID"/>
            <el-table-column prop="name" label="商户名称"/>
            <el-table-column prop="signboard_name" label="商户招牌名"/>
            <el-table-column prop="categoryPath" label="行业">
                <template slot-scope="scope">
                    <span v-for="item in scope.row.categoryPath" :key="item.id">
                        {{ item.name }}
                    </span>
                </template>
            </el-table-column>
            <el-table-column prop="city" label="城市">
                <template slot-scope="scope">
                    <span> {{ scope.row.city }} </span>
                    <span> {{ scope.row.area }} </span>
                </template>
            </el-table-column>
            <el-table-column label="签约人" width="250px">
                <template slot-scope="scope">
                    <span v-if="scope.row.bizer"><span class="c-green">业务员 </span>{{scope.row.bizer.name}}/{{scope.row.bizer.mobile}}</span>
                    <span v-else-if="scope.row.operBizMember"><span class="c-light-gray">员工 </span>{{scope.row.operBizMember.name}}/{{scope.row.operBizMember.mobile}}</span>
                    <span v-else>无</span>
                </template>
            </el-table-column>
            <el-table-column prop="status" label="商户状态">
                <template slot-scope="scope" v-if="scope.row.audit_status == 1 || scope.row.audit_status == 3">
                    <span v-if="scope.row.status === 1" class="c-green">正常</span>
                    <span v-else-if="scope.row.status === 2" class="c-danger">已冻结</span>
                    <span v-else>未知 ({{scope.row.status}})</span>
                </template>
            </el-table-column>
            <el-table-column prop="audit_status" label="审核状态">
                <template slot-scope="scope">
                    <span v-if="parseInt(scope.row.audit_status) === 0" class="c-warning">待审核</span>
                    <el-popover
                            v-else-if="scope.row.audit_status === 1"
                            placement="bottom-start"
                            width="200px"  trigger="hover"
                            @show="showMessage(scope)"
                            :disabled="scope.row.audit_suggestion == ''">
                        <div   slot="reference" class="c-green"><p>审核通过</p><span class="message">{{scope.row.audit_suggestion}}</span></div>
                        <unaudit-record-reason    :data="auditRecord"  />
                    </el-popover>

                    <el-popover
                            v-else-if="parseInt(scope.row.audit_status) === 2"
                            placement="bottom-start"
                            width="200px"  trigger="hover"
                            @show="showMessage(scope)"
                            :disabled="scope.row.audit_suggestion == ''" >
                        <div   slot="reference" class="c-danger"><p>审核不通过</p><span class="message">{{scope.row.audit_suggestion}}</span></div>
                        <unaudit-record-reason    :data="auditRecord"  />
                    </el-popover>


                    <span v-else-if="parseInt(scope.row.audit_status) === 3" class="c-warning">重新提交审核</span>
                    <span v-else>未知 ({{scope.row.audit_status}})</span>
                </template>
            </el-table-column>
            <el-table-column label="操作" width="250px">
                <template slot-scope="scope">
                    <merchant-item-options
                            :scope="scope"
                            :query="query"
                            @change="itemChanged"
                            @refresh="getList"/>
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
    </page>
</template>

<script>
    import api from '../../../assets/js/api'

    import MerchantItemOptions from './merchant-item-options'
    import UnauditRecordReason from './unaudit-record-reason'

    export default {
        name: "pilot-merchant-list",
        data(){
            return {
                categoryOptions: [],
                auditRecord:[],
                isLoading: false,
                query: {
                    name: '',
                    merchantId: '',
                    status: '',
                    page: 1,
                    audit_status: '',
                    signboardName: '',
                    isPilot: 1,
                },
                list: [],
                total: 0,
                merchants: [],
            }
        },
        computed: {

        },
        methods: {
            exportExcel(){
                let array = [];
                for (let key in this.query){
                    array.push(key + '=' + this.query[key]);
                }
                location.href = '/api/oper/merchant/export?' + array.join('&');
            },
            search(){
                this.query.page = 1;
                this.getList();
            },
            getList(){
                this.isLoading = true;
                let params = {};
                Object.assign(params, this.query);
                api.get('/merchants', params).then(data => {
                    this.query.page = params.page;
                    this.isLoading = false;
                    this.list = data.list;
                    this.total = data.total;
                })
            },
            itemChanged(index, data){
                this.getList();
            },
            add(){
                router.push('/merchant/pilot/add');
            },
            showMessage(scope){
                api.get('/merchant/audit/record/newest', {id: scope.row.id}).then(data => {
                    this.auditRecord = [data];
                })
            },
            getMerchants(){
                api.get('/merchant/allNames', {isPilot: 1}).then(data => {
                    this.merchants = data.list;
                })
            },
        },
        created(){
            api.get('merchant/categories/tree').then(data => {
                this.categoryOptions = data.list;
            });
            if (this.$route.params){
                Object.assign(this.query, this.$route.params);
            }
            this.getMerchants();
            this.getList();
        },
        components: {
            MerchantItemOptions,
            UnauditRecordReason
        }
    }
</script>

<style scoped>
    .message{
        overflow: hidden;
        text-overflow:ellipsis;
        white-space: nowrap;
        width:120px;
        font-size:12px;
        color:gray;
    }
</style>