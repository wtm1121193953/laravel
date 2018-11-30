<template>
    <page :title="pageTitle" :breadcrumbs="{提现记录: '/withdraw/records'}">
        <el-row :gutter="20" v-loading="loading">
            <el-form size="small" label-width="130px">
                <el-col :span="12">
                    <el-form-item label="提现时间">
                        {{data.created_at}}
                    </el-form-item>
                    <el-form-item label="提现金额">
                        {{data.amount}}元
                    </el-form-item>
                    <el-form-item label="到账金额">
                        {{data.remit_amount}}元
                    </el-form-item>
                    <el-form-item label="账户名">
                        {{data.bank_card_open_name}}
                    </el-form-item>
                    <el-form-item label="账户类型">
                        <span v-if="data.bank_card_type == 1">公司</span>
                        <span v-else-if="data.bank_card_type == 2">个人</span>
                        <span v-else>未知</span>
                    </el-form-item>
                    <el-form-item label="商户名称">
                        {{data.merchant_name}}
                    </el-form-item>
                    <el-form-item label="商户所属运营中心">
                        {{data.oper_name}}
                    </el-form-item>
                    <el-form-item label="提现状态">
                        <span v-if="data.status == 1">审核中</span>
                        <span v-else-if="data.status == 2">审核通过</span>
                        <span v-else-if="data.status == 3">已打款</span>
                        <span v-else-if="data.status == 4">打款失败</span>
                        <span v-else-if="data.status == 5">审核不通过</span>
                        <span v-else>未知({{data.status}})</span>
                    </el-form-item>
                    <el-form-item label="账户余额">
                        {{data.after_amount}}元
                    </el-form-item>
                    <el-form-item label="审核意见" v-if="audit">
                        <el-input type="textarea" :rows="3" v-model="remark" placeholder="最多输入50个汉字"></el-input>
                    </el-form-item>
                </el-col>
                <el-col :span="12">
                    <el-form-item label="提现编号">
                        {{data.withdraw_no}}
                    </el-form-item>
                    <el-form-item label="手续费">
                        {{data.charge_amount}}元
                    </el-form-item>
                    <el-form-item label="提现银行账号">
                        {{data.bank_card_no}}
                    </el-form-item>
                    <el-form-item label="开户行">
                        {{data.bank_name}}
                    </el-form-item>
                    <el-form-item label="发票快递信息">
                        <span v-if="data.bank_card_type == 1">
                            {{data.invoice_express_company}}/{{data.invoice_express_no}}
                        </span>
                        <span v-else-if="data.bank_card_type == 2">无需发票</span>
                        <span v-else>未知</span>
                    </el-form-item>
                    <el-form-item label="商户ID">
                        {{data.origin_id}}
                    </el-form-item>
                    <el-form-item label="运营中心ID">
                        {{data.oper_id}}
                    </el-form-item>
                    <el-form-item label="备注">
                        {{data.remark}}
                    </el-form-item>
                    <el-form-item label="可提现金额">
                        {{data.after_balance}}元
                    </el-form-item>
                </el-col>
            </el-form>
            <el-col>
                <el-button size="small" type="primary" @click="goBack">返 回</el-button>
                <el-button v-if="hasRule('/api/admin/withdraw/record/audit') && audit" size="small" type="primary" @click="auditSuccess">审核通过</el-button>
                <el-button v-if="hasRule('/api/admin/withdraw/record/audit') && audit" size="small" type="warning" @click="auditFailed">审核不通过</el-button>
            </el-col>
        </el-row>

        <!--审核dialog-->
        <el-dialog
            title="提现审核"
            :visible.sync="showDialog"
            width="20%"
            center>
            <div class="audit-div">确认审核通过该提现申请？</div>
            <span>批次</span>
            <el-select v-model="batchId" size="small" placeholder="请选择批次">
                <el-option
                    v-for="item in batchList"
                    :key="item.id"
                    :label="item.batch_no"
                    :value="item.id"
                ></el-option>
            </el-select>
            <div class="tips">提示：审核通过的提现申请可加入批次进行打款，若无批次请先到提现批次管理页面创建批次</div>
            <span slot="footer" class="dialog-footer">
                <el-button size="small" @click="showDialog = false">取 消</el-button>
                <el-button size="small" type="primary" @click="commit">确 定</el-button>
            </span>
        </el-dialog>
    </page>
</template>

<script>
    import api from '../../../../assets/js/api'

    export default {
        name: "merchant-detail-audit",
        data() {
            return {
                id: null,
                pageTitle: '',
                type: null, // 提现批次类型
                audit: false,
                data: {},
                remark: '',
                loading: false,
                showDialog: false,
                batchId: null,
                batchList: [],
            }
        },
        methods: {
            getDetail() {
                this.loading = true;
                api.get('/withdraw/record/detail', {id: this.id}).then(data => {
                    this.data = data;
                    this.loading = false;
                })
            },
            goBack() {
                router.push({
                    path: '/withdraw/records',
                    query: {
                        type: 'cs',
                    }
                });
            },
            auditSuccess() {
                this.showDialog = true;
            },
            auditFailed() {
                this.$confirm('确定审核不通过吗？', '提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    type: 'warning'
                }).then(() => {
                    if (this.remark.length > 50) {
                        this.$message.error('备注不能超过50个字');
                        return;
                    }
                    let param = {
                        id: this.id,
                        status: 5,   //审核不通过状态 WalletWithdraw::STATUS_AUDIT_FAILED
                        remark: this.remark,
                    };
                    api.post('/withdraw/record/audit', param).then(data => {
                        this.$message.success('审核不通过');
                        this.goBack();
                    })
                }).catch(() => {

                })
            },
            commit() {
                if (!this.batchId) {
                    this.$message.error('请先选择批次');
                    return false;
                }
                if (this.remark.length > 50) {
                    this.$message.error('备注不能超过50个字');
                    return;
                }
                let param = {
                    id: this.id,
                    status: 2,     // 审核通过状态 WalletWithdraw::STATUS_AUDIT
                    batchId: this.batchId,   //提现批次id
                    remark: this.remark,
                };
                api.post('/withdraw/record/audit', param).then(data => {
                    this.$message.success('审核通过');
                    this.goBack();
                })
            },
            getBatchList() {
                let param = {
                    type: this.type,
                    status: 1,   //批次状态：结算中
                };
                api.get('/withdraw/batches', param).then(data => {
                    this.batchList = data.list;
                })
            }
        },
        created() {
            let query = this.$route.query;
            this.id = query.hasOwnProperty('id') ? query.id : null;
            this.audit = query.hasOwnProperty('audit') ? query.audit : false;
            this.type = query.hasOwnProperty('type') ? query.type : null;
            if (!this.id) {
                this.$message.error('id不能为空');
                this.goBack();
            }
            if (this.audit) {
                this.pageTitle = '商户提现审核';
                if (!this.type) {
                    this.$message.error('批次类型不能为空');
                    this.goBack();
                }
            } else {
                this.pageTitle = '商户提现明细';
            }
            this.getDetail();
            this.getBatchList();
        }
    }
</script>

<style scoped>
    .audit-div,.tips {
        margin: 10px auto;
    }
</style>