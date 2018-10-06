<template>
    <page title="用户邀请关系解绑" v-loading="isLoading">
        <el-form :model="query" inline size="small" class="fl" @submit.native.prevent>
            <el-form-item>
                <el-input v-model="query.keyword" placeholder="请输入用户手机搜索"  clearable  @keyup.native.enter="search"/>
            </el-form-item>
            <el-form-item>
                <el-button type="primary" @click="search">搜索</el-button>
            </el-form-item>
        </el-form>
        <el-table :data="list" stripe>
            <!--<el-table-column prop="name" label="用户名称"/>-->
            <el-table-column prop="mobile" label="手机号"/>
            <!--<el-table-column prop="email" label="邮箱"/>-->
            <el-table-column prop="parent" label="分享人"/>
            <el-table-column prop="created_at" label="添加时间"/>
            <!--<el-table-column prop="city" label="城市">-->
                <!--<template slot-scope="scope">-->
                    <!--&lt;!&ndash;<span> {{ scope.row.province }} </span>&ndash;&gt;-->
                    <!--<span> {{ scope.row.city }} </span>-->
                    <!--<span> {{ scope.row.area }} </span>-->
                <!--</template>-->
            <!--</el-table-column>-->
            <el-table-column label="操作" width="250px">
                <template slot-scope="scope">
                    <members-item-options
                            :scope="scope"
                            @refresh="getList"
                            @change="itemChanged"
                    />
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
    import MembersItemOptions from './members-item-options'

    export default {
        name: "member-list",
        data(){
            return {
                showDetail: false,
                isLoading: false,
                query: {
                    page: 1,
                    keyword: '',
                },
                list: [],
                total: 0,
                currentMerchant: null,
            }
        },
        computed: {

        },
        methods: {
            search(){
                this.query.page = 1;
                this.getList();
            },
            getList(){
                api.get('/members', this.query).then(data => {
                    this.list = data.list;
                    this.total = data.total;
                })
            },
            itemChanged(index, data){
                this.list.splice(index, 1, data)
                this.getList();
            },
        },
        created(){
            this.getList();
        },
        components: {
            MembersItemOptions,
        }
    }
</script>

<style scoped>

</style>