<template>
    <page title="过滤关键词列表">
        <el-col>
            <el-button type="success" class="fr" @click="add">添加关键词</el-button>
        </el-col>
        <el-table :data="list" v-loading="tableLoading">
            <el-table-column prop="id" label="ID"></el-table-column>
            <el-table-column prop="keyword" label="关键词"></el-table-column>
            <el-table-column prop="status" label="状态">
                <template slot-scope="scope">
                    <span v-if="parseInt(scope.row.status) === 1" class="c-green">正常</span>
                    <span v-else-if="parseInt(scope.row.status) === 2" class="c-warning">禁止</span>
                    <span v-else class="c-danger">未知</span>
                </template>
            </el-table-column>
            <el-table-column prop="category" label="适用分类">
                <template slot-scope="scope">
                    <span v-for="(value,index) in scope.row.category" :key="index" class="span-tag">
                        <el-tag
                            v-for="item in filterKeywordCategoryList"
                            :key="item.categoryNumber"
                            v-if="item.categoryNumber == value"
                        >
                            {{item.categoryName}}
                        </el-tag>
                    </span>
                </template>
            </el-table-column>
            <el-table-column label="操作">
                <template slot-scope="scope">
                    <el-button type="text" :class="parseInt(scope.row.status) === 1 ? 'c-warning' : 'c-green'" @click="changeStatus(scope.row)">{{parseInt(scope.row.status) === 1 ? '下 架' : '上 架'}}</el-button>
                    <el-button type="text" @click="edit(scope.row)">编 辑</el-button>
                    <el-button type="text" @click="del(scope.row)">删 除</el-button>
                </template>
            </el-table-column>
        </el-table>

        <el-pagination
            class="fr m-t-20"
            layout="total, prev, pager, next"
            :current-page.sync="query.page"
            :total="total"
            :page-size="query.pageSize"
            @current-change="getList"
        ></el-pagination>

        <el-dialog
            :visible.sync="showDialog"
            :title="dialogTitle"
            width="30%"
            :close-on-click-modal="false"
            @close="close">
                <filter-keyword-form
                    ref="form"
                    :editData="editData"
                    @cancel="close"
                    @addOrEditSuccess="addOrEditSuccess"
                ></filter-keyword-form>
        </el-dialog>
    </page>
</template>

<script>
    import api from  '../../../../assets/js/api';
    import FilterKeywordForm from './form';
    import {mapState} from 'vuex'

    export default {
        name: "list",
        data() {
            return {
                list: [],
                tableLoading: false,
                showDialog: false,
                dialogTitle: '',

                total: 0,
                query: {
                    page: 1,
                    pageSize: 15,
                },

                editData: {},
            }
        },
        computed: {
            ...mapState([
                'filterKeywordCategoryList'
            ])
        },
        methods: {
            getList() {
                this.tableLoading = true;
                let param = {};
                Object.assign(param, this.query);
                api.get('setting/filterKeywords', param).then(data => {
                    this.query.page = param.page;
                    this.list = data.list;
                    this.total = data.total;
                    this.tableLoading = false;
                })
            },
            changeStatus(row) {
                let option = parseInt(row.status) === 1 ? '下架' : '上架';
                api.post('setting/filterKeyword/changeStatus', {id: row.id}).then(data => {
                    row.status = parseInt(data.status);
                    this.$message.success('「'+ data.keyword +'」关键词' + option + '成功');
                })
            },
            edit(row) {
                this.dialogTitle = '编辑关键词';
                this.editData = row;
                this.showDialog = true;
            },
            add() {
                this.dialogTitle = '添加关键词';
                this.showDialog = true;
            },
            addOrEditSuccess() {
                this.close();
                this.getList();
            },
            del(row) {
                this.$confirm('确定删除「'+ row.keyword +'」关键词吗？', '提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    type: 'warning',
                }).then(() => {
                    if (this.query.page > 1 && this.list.length <= 1) {
                        this.query.page--;
                    }
                    api.post('setting/filterKeyword/delete', {id: row.id}).then(data => {
                        this.getList();
                        this.$message.success('「'+ data.keyword +'」关键词删除成功');
                    })
                })
            },
            close() {
                this.showDialog = false;
                this.editData = {};
                this.$refs.form.resetForm();
            }
        },
        created() {
            this.getList();
        },
        components: {
            FilterKeywordForm,
        }
    }
</script>

<style scoped>
    .span-tag {
        margin-right: 10px;
    }
</style>