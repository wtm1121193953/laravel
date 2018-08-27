<template>
    <page title="我的业务员" v-loading="isLoading">
        <el-table :data="list" stripe>
            <el-table-column prop="created_at" label="加入时间">
                <template slot-scope="scope">
                    {{scope.row.created_at.substr(0, 16)}}
                </template>
            </el-table-column>
            <el-table-column prop="name" label="姓名"/>
            <el-table-column prop="mobile" label="手机号"/>
            <el-table-column prop="dividedInto" label="业务员分成"/>
            <el-table-column prop="activeMerchantNumber" label="发展商户（家）"/>
            <el-table-column prop="auditMerchantNumber" label="审核通过商户（家）"/>
            <el-table-column prop="remark" label="备注"/>
            <el-table-column prop="status" label="状态">
                <template slot-scope="scope">
                    <span v-if="scope.row.status === 1" class="c-green">正常</span>
                    <span v-else-if="scope.row.status === 2" class="c-danger">冻结</span>
                    <span v-else>未知 ({{scope.row.status}})</span>
                </template>
            </el-table-column>
            <el-table-column fixed="right" label="操作">
                <template slot-scope="scope">
                    <el-button type="text" @click="remarks">备注</el-button>
                    <el-button type="text" @click="merchants">业务</el-button>
                    <el-button type="text" @click="changeStatus">{{scope.row.status === 1 ? '冻结' : '解冻'}}</el-button>
                    <el-button type="text" @click="dividedIntoSettings">分成设置</el-button>
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

        <el-dialog title="业务员备注" :visible.sync="dialogRemarksFormVisible" width="30%">
            <el-form :model="formRemarks" label-width="70px">
                <el-form-item label="备注">
                    <el-input type="textarea" v-model="formRemarks.remark" auto-complete="off" placeholder="最多50个字"/>
                </el-form-item>
            </el-form>
            <div slot="footer" class="dialog-footer">
                <el-button @click="dialogRemarksFormVisible = false">取 消</el-button>
                <el-button type="primary" @click="dialogRemarksFormVisible">提 交</el-button>
            </div>
        </el-dialog>

        <el-dialog title="业务员分成" :visible.sync="dialogDividedIntoSettingsFormVisible" width="30%">
            <el-form :model="formDividedIntoSettings" :rules="rules" label-width="70px">
                <el-form-item label="分成" prop="divided_into">
                    <el-input v-model="formDividedIntoSettings.divided_into" auto-complete="off" style="width:90%;"/> %
                </el-form-item>
            </el-form>
            <div slot="footer" class="dialog-footer">
                <el-button @click="dialogDividedIntoSettingsFormVisible = false">取 消</el-button>
                <el-button type="primary" @click="dialogDividedIntoSettingsFormVisible = false">提 交</el-button>
            </div>
        </el-dialog>
    </page>
</template>

<script>
    import api from '../../../assets/js/api'

    export default {
        data(){
            return {
                isLoading: false,
                query: {
                    page: 1,
                },
                list: [],
                total: 0,

                dialogRemarksFormVisible: false,
                dialogDividedIntoSettingsFormVisible: false,
                formRemarks: {
                    remark: ''
                },
                formDividedIntoSettings: {
                    divided_into: ''
                },
                rules: {
                    divided_into: [
                        { required: true, message: '请输入分成', trigger: 'blur' },
                        { min: 0, max: 100, message: '长度在 0 到 100 个字符', trigger: 'blur' }
                    ]
                }
            }
        },
        computed: {

        },
        methods: {
            search(){
                this.query.page = 1;
                this.getList()
            },
            getList(){
                // api.get('/operBizMembers', this.query).then(data => {
                //     this.list = data.list;
                //     this.total = data.total;
                // })
            },
            merchants(){
                // router.push({
                //     path: '/operBizMember/merchants',
                //     query: {
                //         id: this.scope.row.id
                //     }
                // })
            },
            changeStatus(){
                // let status = this.scope.row.status === 1 ? 2 : 1;
                // this.$emit('before-request')
                // api.post('/operBizMember/changeStatus', {id: this.scope.row.id, status: status}).then((data) => {
                //     this.scope.row.status = status;
                //     this.$emit('change', this.scope.$index, data)
                // }).finally(() => {
                //     this.$emit('after-request')
                // })
            },
            remarks() {
                this.dialogRemarksFormVisible = true;
            },
            dividedIntoSettings() {
                this.dialogDividedIntoSettingsFormVisible = true;
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