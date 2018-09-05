<template>
    <page title="提现批次管理">
        <el-form v-model="form" inline size="small">
            <el-form-item prop="batchNo" label="批次编号">
                <el-input v-model="form.batchNo" clearable placeholder="请输入批次编号" class="w-200"/>
            </el-form-item>
            <el-form-item prop="type" label="批次类型">
                <el-select v-model="form.type" placeholder="请选择" clearable class="w-100">
                    <el-option label="全部" :value="0"></el-option>
                    <el-option label="对公" :value="1"></el-option>
                    <el-option label="对私" :value="2"></el-option>
                </el-select>
            </el-form-item>
            <el-form-item label="批次状态">
                <el-select v-model="form.status" clearable class="w-100">
                    <el-option label="全部" :value="0"></el-option>
                    <el-option label="结算中" :value="1"></el-option>
                    <el-option label="准备打款" :value="2"></el-option>
                    <el-option label="打款完成" :value="3"></el-option>
                </el-select>
            </el-form-item>
            <el-form-item label="添加批次时间">
                <el-date-picker
                        v-model="form.startDate"
                        type="date"
                        placeholder="选择开始日期"
                        format="yyyy 年 MM 月 dd 日"
                        value-format="yyyy-MM-dd"
                ></el-date-picker>
                <span>—</span>
                <el-date-picker
                        v-model="form.endDate"
                        type="date"
                        placeholder="选择结束日期"
                        format="yyyy 年 MM 月 dd 日"
                        value-format="yyyy-MM-dd"
                        :picker-options="{disabledDate: (time) => {return time.getTime() < new Date(form.startDate) - 8.64e7}}"
                ></el-date-picker>
            </el-form-item>
            <el-form-item>
                <el-button type="primary" @click="search">查 询</el-button>
                <el-button type="success" @click="exportExcel">导出Excel</el-button>
            </el-form-item>
            <el-button class="fr" size="small" type="success" @click="add">添加批次</el-button>
        </el-form>
        <el-table :data="list" v-loading="tableLoading" stripe>
            <el-table-column prop="created_at" label="添加批次时间"></el-table-column>
            <el-table-column prop="batch_no" label="批次编号"></el-table-column>
            <el-table-column prop="type" label="批次类型">
                <template slot-scope="scope">
                    <span v-if="scope.row.type == 2">对私</span>
                    <span v-else-if="scope.row.type == 1">对公</span>
                    <span v-else>未知({{scope.row.type}})</span>
                </template>
            </el-table-column>
            <el-table-column label="批次总金额/笔数">
                <template slot-scope="scope">
                    {{scope.row.amount}}元/{{scope.row.total}}笔
                </template>
            </el-table-column>
            <el-table-column label="打款成功金额/笔数">
                <template slot-scope="scope">
                    {{scope.row.success_amount}}元/{{scope.row.success_total}}笔
                </template>
            </el-table-column>
            <el-table-column label="打款失败金额/笔数">
                <template slot-scope="scope">
                    {{scope.row.failed_amount}}元/{{scope.row.failed_total}}笔
                </template>
            </el-table-column>
            <el-table-column prop="status" label="批次状态">
                <template slot-scope="scope">
                    <span v-if="scope.row.status == 1">结算中</span>
                    <span v-else-if="scope.row.status == 2">准备打款</span>
                    <span v-else-if="scope.row.status == 3">打款完成</span>
                    <span v-else>未知({{scope.row.status}})</span>
                </template>
            </el-table-column>
            <el-table-column prop="remark" label="备注"></el-table-column>
            <el-table-column label="操作">
                <template slot-scope="scope">
                    <el-button type="text" @click="detail(scope.row)">查看明细</el-button>
                    <el-button type="text" v-if="scope.row.status == 1 && scope.row.total == 0" @click="del(scope.row)">删 除</el-button>
                </template>
            </el-table-column>
        </el-table>
        <el-pagination
                class="fr m-t-20"
                layout="total, prev, pager, next"
                :current-page.sync="form.page"
                @current-change="getList"
                :page-size="form.pageSize"
                :total="total"
        ></el-pagination>

        <el-dialog
            title="添加批次"
            :visible.sync="addDialog"
            width="20%"
            center
            :close-on-click-modal="false"
            :close-on-press-escape="false"
        >
            <el-form :model="addForm" ref="addForm" :rules="addFormRules" size="small" label-width="80px">
                <el-form-item prop="type" label="批次类型">
                    <el-radio-group v-model="addForm.type">
                        <el-radio :label="2">对私</el-radio>
                        <el-radio :label="1">对公</el-radio>
                    </el-radio-group>
                </el-form-item>
                <el-form-item prop="remark" label="备注">
                    <el-input type="textarea" :rows="2" v-model="addForm.remark"/>
                </el-form-item>
            </el-form>
            <span slot="footer" class="dialog-footer">
                <el-button @click="cancel">取 消</el-button>
                <el-button type="primary" @click="commit">确 定</el-button>
            </span>
        </el-dialog>
    </page>
</template>

<script>
    import api from '../../../../assets/js/api'

    export default {
        name: "withdraw-batch-list",
        data() {
            return {
                form: {
                    batchNo: '',
                    type: 0,
                    status: 0,
                    startDate: '',
                    endDate: '',

                    page: 1,
                    pageSize: 15,
                },
                total: 0,

                list: [],
                tableLoading: false,

                addDialog: false,
                addForm: {
                    type: 2,
                    remark: '',
                },

                addFormRules: {
                    type: [
                        {required: true, message: '批次类型不能为空'}
                    ],
                    remark: [
                        {max: 50, message: '备注不能超过50个字'},
                    ]
                }
            }
        },
        methods: {
            getList() {
                this.tableLoading = true;
                api.get('/withdraw/batches', this.form).then(data => {
                    this.list = data.list;
                    this.total = data.total;
                    this.tableLoading = false;
                })
            },
            search() {
                this.form.page = 1;
                this.getList();
            },
            exportExcel() {
                let data = this.form;
                let params = [];
                Object.keys(data).forEach((key) => {
                    let value =  data[key];
                    if (typeof value === 'undefined' || value == null) {
                        value = '';
                    }
                    params.push([key, encodeURIComponent(value)].join('='))
                }) ;
                let uri = params.join('&');

                location.href = `/api/admin/withdraw/batch/export?${uri}`;
            },
            detail(row) {
                router.push({
                    path: '/withdraw/batch/detail',
                    query: {
                        id: row.id,
                    }
                });
            },
            del(row) {
                this.$confirm('确认删除该批次，删除后不可取消', '删除批次', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    type: 'warning',
                    center: true,
                }).then(() => {
                    api.post('/withdraw/batch/delete',{id: row.id}).then(data => {
                        this.$message.success('删除成功');
                        this.getList();
                    })
                }).catch(() => {});
            },
            add() {
                this.addDialog = true;
            },
            cancel() {
                this.addDialog = false;
                this.$refs.addForm.resetFields();
            },
            commit() {
                this.$refs.addForm.validate(valid => {
                    if (valid) {
                        api.post('/withdraw/batch/add', this.addForm).then(data => {
                            this.$message.success('添加成功');
                            this.addDialog = false;
                            this.$refs.addForm.resetFields();
                            this.getList();
                        })
                    }
                })
            }
        },
        created() {
            this.getList();
        }
    }
</script>

<style scoped>

</style>