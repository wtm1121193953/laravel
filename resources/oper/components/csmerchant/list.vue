<template>
    <page title="超市商户列表" v-loading="isLoading">
        <el-form class="fl" inline size="small">
            <el-form-item label="商户ID">
                <el-select v-model="query.merchantId" placeholder="输入商户ID或商户名" filterable clearable>
                    <el-option v-for="item in merchants" :key="item.id" :value="item.id" :label="item.name"/>
                </el-select>
            </el-form-item>

            <el-form-item label="商户名称" prop="name">
                <el-input v-model="query.name" @keyup.enter.native="search" clearable placeholder="商户名称"/>
            </el-form-item>

            <el-form-item prop="signboardName" label="商户招牌名">
                <el-input v-model="query.signboardName" size="small" placeholder="商家招牌名" clearable
                          @keyup.enter.native="search"/>
            </el-form-item>
            <el-form-item label="商户状态" prop="status">
                <el-select clearable v-model="query.status" size="small" class="w-150">
                    <el-option label="全部" value=""/>
                    <el-option label="正常" value="1"/>
                    <el-option label="冻结" value="2"/>
                    <el-option label="未知" value="3"/>
                </el-select>
            </el-form-item>
            <el-form-item label="审核状态" prop="audit_status">
                <el-select clearable v-model="query.audit_status" placeholder="请选择">
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

            <!--<el-button type="primary" size="small" class="m-l-30" @click="exportExcel">导出Excel</el-button>-->

            <el-button class="fr inline" size="small" type="success" @click="add" icon="plus">新增商户资料</el-button>
        </el-form>
        <el-table :data="list" stripe>
            <el-table-column prop="created_at" label="添加时间"/>
            <el-table-column prop="id" width="150" label="商户ID"/>
            <el-table-column prop="name" label="商户名称"/>
            <el-table-column label="商户类型">
                <template slot-scope="scope">
                    <span>超市商户</span>
                </template>
            </el-table-column>
            <el-table-column prop="signboard_name" label="商户招牌名"/>
            <el-table-column prop="city" label="城市">
                <template slot-scope="scope">
                    <!--<span> {{ scope.row.province }} </span>-->
                    <span> {{ scope.row.city }} </span>
                    <span> {{ scope.row.area }} </span>
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
                            width="200px" trigger="hover">
                        <div slot="reference" class="c-green"><p>审核通过</p><span class="message">{{scope.row.audit_suggestion}}</span>
                        </div>
                        审核意见: {{scope.row.audit_suggestion || '无'}}
                    </el-popover>

                    <el-popover
                            v-else-if="parseInt(scope.row.audit_status) === 2"
                            placement="bottom-start"
                            width="200px" trigger="hover">
                        <div slot="reference" class="c-danger"><p>审核不通过</p><span class="message">{{scope.row.audit_suggestion}}</span>
                        </div>
                        审核意见: {{scope.row.audit_suggestion || '无'}}
                    </el-popover>
                    <span v-else-if="parseInt(scope.row.audit_status) === 3" class="c-warning">重新提交审核</span>
                    <span v-else>未知 ({{scope.row.audit_status}})</span>
                </template>
            </el-table-column>
            <!--<el-table-column prop="settlement_cycle_type" label="结算周期">
                <template slot-scope="scope">
                    <span>{{ {1: '周结', 2: '半月结', 3: '月结', 4: '半年结', 5: '年结', 6: 'T+1', 7: '未知',}[scope.row.settlement_cycle_type] }}</span>
                </template>
            </el-table-column>-->
            <el-table-column label="操作" width="250px">
                <template slot-scope="scope">
                    <merchant-item-options
                            :scope="scope"
                            :query="query"
                            @change="itemChanged"
                            @accountChanged="accountChanged"
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
    import MerchantForm from './merchant-form'

    export default {
        name: "merchant-list",
        data() {
            return {
                isLoading: false,
                query: {
                    name: '',
                    merchantId: '',
                    status: '',
                    page: 1,
                    audit_status: '',
                    signboardName: '',
                },
                list: [],
                total: 0,
                merchants: [],
            }
        },
        computed: {},
        methods: {
            exportExcel() {
                let array = [];
                for (let key in this.query) {
                    array.push(key + '=' + this.query[key]);
                }
                location.href = '/api/oper/cs/merchant/export?' + array.join('&');
            },
            search() {
                this.query.page = 1;
                this.getList();
            },
            getList() {
                this.isLoading = true;
                let params = {};
                Object.assign(params, this.query);
                api.get('cs/merchants', params).then(data => {
                    this.query.page = params.page;
                    this.isLoading = false;
                    this.list = data.list;
                    this.total = data.total;
                })
            },
            itemChanged(index, data) {
                this.getList();
            },
            addBtnClick(command) {
                if (command === 'add') {
                    this.add()
                } else {
                    this.$menu.change('cs/merchant/pool')
                }
            },
            add() {
                router.push({
                    path: '/cs/merchant/add',
                    query: {
                        type: 'merchant-list'
                    }
                });
            },
            accountChanged(scope, account) {
                let row = this.list[scope.$index];
                row.account = account;
                this.list.splice(scope.$index, 1, row);
                this.getList();
            },

            getMerchants() {
                api.get('/cs/merchant/allNames').then(data => {
                    this.merchants = data.list;
                })
            },
        },


        created() {
            /*api.get('merchant/categories/tree').then(data => {
                this.categoryOptions = data.list;
            });*/
            if (this.$route.params) {
                Object.assign(this.query, this.$route.params);
            }
            this.getMerchants();
            this.getList();
        },
        components: {
            MerchantItemOptions,
            MerchantForm,
        }
    }
</script>

<style scoped>
    .message {
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        width: 120px;
        font-size: 12px;
        color: gray;
    }
</style>