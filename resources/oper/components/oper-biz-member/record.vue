<template>
    <page title="业务员申请" v-loading="isLoading">
        <el-tabs type="card" v-model="activeName" @tab-click="handleClick">
            <el-tab-pane label="新申请10" name="first"></el-tab-pane>
            <el-tab-pane label="已拒绝" name="second"></el-tab-pane>
        </el-tabs>

        <el-table :data="list" stripe>
            <el-table-column prop="created_at" label="申请签约时间"/>
            <el-table-column prop="name" label="姓名">
                <template slot-scope="scope">
                    <span> {{ scope.row.operInfo.name }} </span>
                </template>
            </el-table-column>
            <el-table-column prop="tel" label="手机号">
                <template slot-scope="scope">
                    <span> {{ scope.row.operInfo.tel }} </span>
                </template>
            </el-table-column>

            <el-table-column prop="updated_at" label="拒绝签约时间" v-if="secondTable"/>
            <el-table-column prop="remark" label="原因" v-if="secondTable"/>

            <el-table-column fixed="right" label="操作" v-if="firstTable">
                <template slot-scope="scope">
                    <el-button type="text" @click="signing">签约</el-button>
                    <el-button type="text" @click="refusal">拒绝</el-button>
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

        <el-dialog title="签约业务员" :visible.sync="dialogSigningFormVisible" width="30%">
            <el-form :model="formSigning" :rules="rules" label-width="70px">
                <el-form-item>
                    确定签约业务员<span class="c-danger">张三</span>
                </el-form-item>
                <el-form-item label="分成" prop="divided_into">
                    <el-input v-model="formSigning.divided_into" auto-complete="off" style="width:90%;"/> %
                </el-form-item>
                <el-form-item label="备注">
                    <el-input type="textarea" v-model="formSigning.remark" auto-complete="off" placeholder="最多50个字" style="width:90%;"/>
                </el-form-item>
            </el-form>
            <div slot="footer" class="dialog-footer">
                <el-button @click="dialogSigningFormVisible = false">取 消</el-button>
                <el-button type="primary" @click="dialogSigningFormVisible = false">提 交</el-button>
            </div>
        </el-dialog>

        <el-dialog title="拒绝业务员" :visible.sync="dialogRefusalFormVisible" width="30%">
            <el-form :model="formRefusal" label-width="70px">
                <el-form-item>
                    确定拒绝签约业务员<span class="c-danger">张三</span>
                </el-form-item>
                <el-form-item label="原因">
                    <el-input type="textarea" v-model="formRefusal.remark" auto-complete="off" placeholder="最多50个字"/>
                </el-form-item>
            </el-form>
            <div slot="footer" class="dialog-footer">
                <el-button @click="dialogRefusalFormVisible = false">取 消</el-button>
                <el-button type="primary" @click="dialogRefusalFormVisible = false">提 交</el-button>
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
                activeName: 'first',
                firstTable: true,
                secondTable: false,
                query: {
                    page: 1
                },
                list: [],
                total: 0,

                dialogSigningFormVisible: false,
                dialogRefusalFormVisible: false,
                formSigning: {
                    divided_into: '',
                    remark: ''
                },
                formRefusal: {
                    remark: ''
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
            getList() {
                this.isLoading = true;
                let params = {};
                Object.assign(params, this.query);
                api.get('/bizerRecord', params).then(data => {
                    // console.log(data.list)
                    this.query.page = params.page;
                    this.isLoading = false;
                    this.list = data.list;
                    this.total = data.total;
                })
            },
            handleClick(tab, event) {
                // console.log(tab, event);
                let _self = this;
                if ( _self.activeName == "first" ) {
                    _self.secondTable = false;
                    // _self.firstTable = true;
                } else if ( _self.activeName == "second" ) {
                    _self.secondTable = true;
                    // _self.firstTable = false;
                }
            },
            signing() {
                this.dialogSigningFormVisible = true;
            },
            refusal() {
                this.dialogRefusalFormVisible = true;
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
