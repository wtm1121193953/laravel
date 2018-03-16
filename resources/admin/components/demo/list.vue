<template>
    <page title="示例管理" v-loading="isLoading">
        <el-button class="fr" type="primary" @click="add">添加示例</el-button>
        <el-table :data="list" stripe>
            <el-table-column prop="id" label="ID"/>
            <el-table-column prop="name" label="示例名称"/>
            <el-table-column prop="status" label="状态">
                <template slot-scope="scope">
                    <span v-if="scope.row.status === 1" class="c-green">已上架</span>
                    <span v-else-if="scope.row.status === 2" class="c-danger">已下架</span>
                    <span v-else>未知 ({{scope.row.status}})</span>
                </template>
            </el-table-column>
            <el-table-column prop="created_at" label="添加时间">
                <template slot-scope="scope">
                    {{scope.row.created_at.substr(0, 10)}}
                </template>
            </el-table-column>
            <el-table-column label="操作" width="250px">
                <template slot-scope="scope">
                    <demo-item-options
                            :scope="scope"
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
                :total="total"/>

        <el-dialog title="添加商品" :visible.sync="isAdd">
            <demo-form
                    @cancel="isAdd = false"
                    @save="doAdd"/>
        </el-dialog>
    </page>
</template>

<script>
    import api from '../../../assets/js/api'
    import { mapState, mapGetters } from 'vuex'

    import DemoItemOptions from 'demo-item-options'
    import DemoForm from './demo-form'

    export default {
        name: "demo-list",
        data(){
            return {
                isLoading: false,
                query: {
                    page: 1,
                },
            }
        },
        computed: {
            ...mapState('demo', [
                'list',
                'total',
                // some other state mapping
            ]),
            ...mapGetters('demo', [
                // some getters mapping
            ])
        },
        methods: {
            getList(){
                store.dispatch('demo/getList', this.query)
            },
            itemChanged(index, data){
                this.list.splice(index, 1, data)
            },
        },
        components: {
            DemoItemOptions,
            DemoForm,
        }
    }
</script>

<style scoped>

</style>