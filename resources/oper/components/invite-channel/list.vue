<template>
    <page title="推广渠道">
        <el-button class="fr m-l-20" type="primary" @click="add">添加推广渠道</el-button>
        <el-button class="fr m-l-20" type="success" @click="exportExcel">导出Excel</el-button>

        <el-table stripe :data="list">
            <el-table-column prop="id" label="ID"/>
            <el-table-column prop="created_at" label="添加时间"/>
            <el-table-column prop="name" label="推广渠道名称"/>
            <el-table-column prop="remark" label="备注"/>
            <el-table-column label="操作">
                <template slot-scope="scope">
                    <el-button type="text" @click="edit(scope.row)">编辑</el-button>
                    <el-button type="text" @click="download(scope.row)">下载二维码</el-button>
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


        <el-dialog title="添加推广渠道" :visible.sync="isAdd">
            <invite-channel-form ref="addForm" @cancel="isAdd = false" @save="doAdd"/>
        </el-dialog>
        <el-dialog title="编辑推广渠道" :visible.sync="isEdit">
            <invite-channel-form ref="editForm" :data="currentEditData" @cancel="isEdit = false" @save="doEdit"/>
        </el-dialog>
    </page>
</template>

<script>
    import api from '../../../assets/js/api'
    import InviteChannelForm from './form'
    export default {
        name: "invite-channel-list",
        data() {
            return {
                list: [],
                total: 0,
                query: {
                    page: 1,
                },
                isAdd: false,
                isEdit: false,
                currentEditData: null,
            }
        },
        components: {
            InviteChannelForm
        },
        methods: {
            getList(){
                api.get('/inviteChannels', this.query).then(data => {
                    this.list = data.list;
                    this.total = data.total;
                })
            },
            exportExcel(){
                // 导出操作
            },
            add(){
                this.isAdd = true;
            },
            doAdd(data){
                // 添加操作
                api.post('inviteChannel/add', data).then(data => {
                    this.isAdd = false;
                    this.$refs.addForm.resetForm()
                    this.getList();
                })
            },
            edit(data){
                this.isEdit = true;
                this.currentEditData = data;
            },
            doEdit(data){
                // 编辑操作
                console.log(data)
                api.post('inviteChannel/edit', data).then(data => {
                    this.isEdit = false;
                    this.$refs.editForm.resetForm()
                    this.getList()
                })
            },
            download(){

            },
        },
        created(){
            this.getList();
        }
    }
</script>

<style scoped>

</style>