<template>
    <page :title="title" :breadcrumbs="breadcrumbs" v-loading="loading">
        <el-col :span="10">
            <el-form label-width="150px" size="small" label-position="right">
                <el-form-item label="业务员ID">
                    {{info.id}}
                </el-form-item>
                <el-form-item label="手机号码">
                    {{info.mobile}}
                </el-form-item>
                <el-form-item label="注册时间">
                    {{info.created_at}}
                </el-form-item>
                <el-form-item label="昵称">
                    {{info.name}}
                </el-form-item>
                <el-form-item label="身份证姓名">
                    {{info.bizerIdentityAuditRecord ? info.bizerIdentityAuditRecord.name: ''}}
                </el-form-item>
                <el-form-item label="身份证号码">
                    {{info.bizerIdentityAuditRecord ? info.bizerIdentityAuditRecord.id_card_no: ''}}
                </el-form-item>
                <el-form-item label="签约运营中心">
                    <span v-for="item in info.operBizers">
                        <span style="margin-right: 15px;">{{item.operName}}</span>
                    </span>
                </el-form-item>
                <el-form-item label="身份证正面">
                    <div v-viewer>
                        <img v-if="info.bizerIdentityAuditRecord" :src="info.bizerIdentityAuditRecord.front_pic" width="200px" height="100px" alt="身份证正面">
                    </div>
                </el-form-item>
                <el-form-item label="身份证反面">
                    <div v-viewer>
                        <img v-if="info.bizerIdentityAuditRecord" :src="info.bizerIdentityAuditRecord.opposite_pic" width="200px" height="100px" alt="身份证反面">
                    </div>
                </el-form-item>
                <el-form-item label="审核意见" prop="reason" v-if="isAudit">
                    <el-input
                        type="textarea"
                        :rows="2"
                        placeholder="最多输入50字汉字，可不填"
                        maxlength="50"
                        v-model="reason">
                    </el-input>
                </el-form-item>

                <el-form-item>
                    <el-button @click="cancel" type="primary">返 回</el-button>
                    <el-button v-if="info.bizerIdentityAuditRecord && info.bizerIdentityAuditRecord.status !== 2 && isAudit" type="success" @click="doAudit(2)">审核通过</el-button>
                    <el-button v-if="info.bizerIdentityAuditRecord && info.bizerIdentityAuditRecord.status !== 3 && isAudit" type="danger" @click="doAudit(3)">审核不通过</el-button>
                </el-form-item>
            </el-form>
        </el-col>
    </page>
</template>

<script>
    import api from '../../../assets/js/api'

    export default {
        name: "identity-edit",
        data(){
            return {
                loading: false,
                info: {
                    bizerIdentityAuditRecord: {},
                    operBizers: {}
                },
                reason: '',
                id: null,
                isAudit: false,
                breadcrumbs: {},
                breadcrumbsPath: '',
                title: '',
            }
        },
        methods: {
            cancel(){
                router.push(this.breadcrumbsPath);
            },
            getDetail(){
                this.loading = true;
                api.get('bizer/detail', {id: this.id}).then(data => {
                    this.info = data;
                }).finally(() => {
                    this.loading = false;
                });
            },
            doAudit(status){
                this.loading = true;
                let data = {ids: this.id, status: status, reason: this.reason}
                api.post('/bizer/identity/audit', data).then((data) => {
                    let msg = status == 2 ? '审核通过操作成功' : '审核不通过操作成功';
                    this.$message.success(msg);
                    router.push(this.breadcrumbsPath);
                }).finally(() => {
                    this.loading = false;
                })
            },
        },
        created(){
            this.id = this.$route.query.id;
            this.breadcrumbsPath = this.$route.query.breadcrumbsPath;
            if (this.breadcrumbsPath == '/bizer/list') {
                this.breadcrumbs = {业务员列表: this.breadcrumbsPath};
            } else if (this.breadcrumbsPath == '/bizer/identity/list') {
                this.breadcrumbs = {业务员身份审核列表: this.breadcrumbsPath};
            } else {
                this.$message.error('缺少面包屑路径');
                router.go(-1);
            }
            this.title = this.$route.query.title;
            if (this.$route.query.isAudit || this.$route.query.isAudit == 'true') {
                this.isAudit = true;
            } else {
                this.isAudit = false;
            }
            if(!this.id){
                this.$message.error('id不能为空');
                router.push(this.breadcrumbsPath);
                return ;
            }
            this.getDetail();
        },
    }
</script>

<style scoped>

</style>