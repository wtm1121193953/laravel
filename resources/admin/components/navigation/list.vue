<template>
    <page title="用户端宫格导航管理" v-loading="isLoading">
        <el-button class="fr" type="primary" @click="add">添加</el-button>
        <el-table :data="list" stripe>
            <el-table-column prop="id" label="ID"/>
            <el-table-column prop="title" label="标题"/>
            <el-table-column prop="icon" label="图标">
                <template slot-scope="scope">
                    <preview-img :url="scope.row.icon" width="50px" height="50px"></preview-img>
                </template>
            </el-table-column>
            <el-table-column prop="sort" label="排序"/>
            <el-table-column prop="created_at" label="添加时间">
                <template slot-scope="scope">
                    {{scope.row.created_at}}
                </template>
            </el-table-column>
            <el-table-column label="操作" width="250px">
                <template slot-scope="scope">
                    <navigation-item-options
                            :scope="scope"
                            @change="itemChanged"
                            @refresh="getList"
                    />
                </template>
            </el-table-column>
        </el-table>

        <el-dialog title="添加商品分类" :visible.sync="isAdd">
            <navigation-form
                    @cancel="isAdd = false"
                    @save="doAdd"/>
        </el-dialog>
    </page>
</template>

<script>
    import api from '../../../assets/js/api'

    import NavigationItemOptions from './navigation-item-options'
    import NavigationForm from './navigation-form'
    import PreviewImg from '../../../assets/components/img/preview-img'

    export default {
        name: "category-list",
        data(){
            return {
                isAdd: false,
                isLoading: false,
                query: {
                },
                parent: null,
                list: [],
            }
        },
        computed: {

        },
        methods: {
            getList(){
                this.isLoading = true;
                api.get('/navigation/all', this.query).then(data => {
                    this.list = data.list;
                }).finally(() => {
                    this.isLoading = false;
                })
            },
            add(){
                this.isAdd = true;
                this.parent = null;
            },
            doAdd(data){
                this.isLoading = true;
                api.post('/navigation/add', data).then(() => {
                    this.isAdd = false;
                    this.getList();
                }).finally(() => {
                    this.isLoading = false;
                })
            },
            itemChanged(index, data){
                this.list.splice(index, 1, data)
            },
        },
        created(){
            this.getList();
        },
        components: {
            NavigationItemOptions,
            NavigationForm,
            PreviewImg,
        }
    }
</script>

<style scoped>

</style>