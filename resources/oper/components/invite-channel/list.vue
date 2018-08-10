<template>
    <page title="渠道列表">
        <el-form inline class="fl" :model="query" size="small">
            <el-form-item prop="keyword">
                <el-input v-model="query.keyword" placeholder="渠道名称" clearable @keyup.enter.native="search"></el-input>
            </el-form-item>
            <el-form-item>
                <el-button type="primary" @click="search"><i class="el-icon-search">搜索</i></el-button>
            </el-form-item>
        </el-form>
        <el-button class="fr m-l-20" type="primary" @click="add">添加推广渠道</el-button>
        <el-button class="fr m-l-20" type="success" @click="exportExcel">导出Excel</el-button>

        <el-table stripe :data="list" @sort-change="sortChange">
            <el-table-column prop="id" label="ID"/>
            <el-table-column prop="created_at" label="添加时间"/>
            <el-table-column prop="name" label="推广渠道名称"/>
            <el-table-column prop="invite_user_records_count" label="注册人数" sortable="custom">
                <template slot-scope="scope">
                    <el-button type="text" @click="inviteRecords(scope.row)">
                        {{scope.row.invite_user_records_count}}
                    </el-button>
                </template>
            </el-table-column>
            <el-table-column prop="remark" label="备注"/>
            <el-table-column label="操作">
                <template slot-scope="scope">
                    <el-button type="text" @click="edit(scope.row)">编辑</el-button>
                    <el-popover
                            placement="top-end"
                            width="250"
                            trigger="click">
                        <p>请选择您需要的尺寸</p>
                        <div style="text-align: left; margin: 0">
                            <el-radio-group v-model="qrcodeSizeType" size="mini">
                                <el-radio-button label="1">小</el-radio-button>
                                <el-radio-button label="2">中</el-radio-button>
                                <el-radio-button label="3">大</el-radio-button>
                            </el-radio-group>
                            <p v-if="qrcodeSizeType == 1">尺寸: 258 * 258px, 适合打印尺寸: 8cm</p>
                            <p v-if="qrcodeSizeType == 2">尺寸: 430 * 430px, 适合打印尺寸: 15cm</p>
                            <p v-if="qrcodeSizeType == 3">尺寸: 1280 * 1280px, 适合打印尺寸: 50cm</p>
                        </div>
                        <div style="text-align: right; margin: 0">
                            <el-button type="primary" size="mini" @click="download(scope.row)">确定</el-button>
                        </div>
                        <el-button slot="reference" type="text">下载小程序码</el-button>
                    </el-popover>
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


        <el-dialog title="添加推广渠道" :visible.sync="isAdd" :close-on-click-modal="false" @close="resetAddForm">
            <invite-channel-form ref="addForm" @cancel="isAdd = false" @save="doAdd"/>
        </el-dialog>
        <el-dialog title="编辑推广渠道" :visible.sync="isEdit" :close-on-click-modal="false" @close="resetEditForm">
            <invite-channel-form ref="editForm" :data="currentEditData" @cancel="isEdit = false" @save="doEdit"/>
        </el-dialog>
        <a :href="downloadUrl" ref="downloadBtn" style="display: none;"></a>
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
                    keyword: '',
                    page: 1,
                    pageSize: 15,
                    orderColumn: null,
                    orderType: null,
                },
                isAdd: false,
                isEdit: false,
                currentEditData: null,
                qrcodeSizeType: 1,
                downloadUrl: '',
            }
        },
        components: {
            InviteChannelForm
        },
        methods: {
            search(){
                this.query.page = 1;
                this.getList()
            },
            getList(){
                api.get('/inviteChannels', this.query).then(data => {
                    this.list = data.list;
                    this.total = data.total;
                })
            },
            sortChange (column) {
                this.query.orderColumn = column.prop;
                this.query.orderType = column.order;
                api.get('/inviteChannels', this.query).then(data => {
                    this.list = data.list;
                    this.total = data.total;
                })
            },
            exportExcel(){
                // 导出操作
                let message = '确定导出全部推广渠道列表么?'
                if(this.query.keyword){
                    message = '确定导出当前筛选的推广渠道列表么?'
                }
                this.$confirm(message).then(() => {
                    window.open('/api/oper/inviteChannel/export?keyword=' + this.query.keyword)
                })
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
                api.post('inviteChannel/edit', data).then(data => {
                    this.isEdit = false;
                    this.$refs.editForm.resetForm()
                    this.getList()
                })
            },
            download(data){
                api.get('inviteChannel/downloadInviteQrcode', {id: data.id, qrcodeSizeType: this.qrcodeSizeType}).then(() => {
                    this.downloadUrl = `/api/oper/inviteChannel/downloadInviteQrcode?id=${data.id}&qrcodeSizeType=${this.qrcodeSizeType}`;
                    this.$nextTick(() => {
                        this.$refs.downloadBtn.click()
                    })
                })
            },
            inviteRecords(data){
                router.push(`/invite-records?id=${data.id}&name=${data.name}`)
            },
            resetEditForm() {
                this.$refs.editForm.resetForm();
            },
            resetAddForm() {
                this.$refs.addForm.resetForm();
            }
        },
        created(){
            this.getList();
        }
    }
</script>

<style scoped>

</style>