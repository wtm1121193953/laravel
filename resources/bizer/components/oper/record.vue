<template>
    <page title="申请记录" v-loading="isLoading">
		<el-table :data="list" stripe>
            <el-table-column prop="date" label="申请签约时间"/>
            <el-table-column prop="name" label="运营中心名称"/>
            <el-table-column prop="id" label="运营中心ID"/>
            <el-table-column prop="status" label="签约状态">
                <template slot-scope="scope">
                    <span v-if="scope.row.status === 1">已签约</span>
                    <span v-else-if="scope.row.status === 2">已拒绝</span>
                    <span v-else-if="scope.row.status === 3">待签约</span>
                </template>
            </el-table-column>
            <el-table-column prop="reason" label="原因"/>
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
        // name: "merchant-list",
        data(){
            return {
                isLoading: false,
                query: {
                    page: 1
                },
                // list: [],
                list: [{
		        	date: '2018-07-05 12:30:20',
		            name: '位西',
		            id: '8888',
		            status: 1,
		            reason: ''
		        }, {
		            date: '2018-07-05 12:30:20',
		            name: '位西',
		            id: '8888',
		            status: 2,
		            reason: '位西'
		        }, {
		            date: '2018-07-05 12:30:20',
		            name: '位西',
		            id: '8888',
		            status: 3,
		            reason: ''
		        }],
		        total: 0
            }
        },
        computed: {

        },
        methods: {
            getList(){
                // this.isLoading = true;
                // let params = {};
                // Object.assign(params, this.query);
                // api.get('/merchants', params).then(data => {
                //     this.query.page = params.page;
                //     this.isLoading = false;
                //     this.list = data.list;
                //     this.total = data.total;
                // })
            },
        },
        created(){
            
        },
        components: {

        }
    }
</script>

<style scoped>

</style>
