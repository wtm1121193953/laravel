<template>
    <page title="商户池" v-loading="isLoading">
        <el-table :data="list" stripe>
            <el-table-column prop="created_at" label="添加时间"/>
            <el-table-column prop="id" label="ID"/>
            <el-table-column prop="creatorOperName" label="录入运营中心"/>
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
            <el-table-column label="操作" width="250px">
                <template slot-scope="scope">
                    <el-button type="text" @click="detail(scope)">查看</el-button>
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
                api.get('/merchant/pool', this.query).then(data => {
                    this.list = data.list;
                    this.total = data.total;
                })
            },
            detail(scope){
                router.push({
                    path: '/merchant/pool/detail',
                    query: {id: scope.row.id},
                })
                return false;
            },
        },
        created(){
            this.getList();
        },
        components: {
        }
    }
</script>

<style scoped>

</style>