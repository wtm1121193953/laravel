<template>
    <div>
        <el-button type="text" @click="detail">查看</el-button>
        <el-button v-if="scope.row.status == 1" type="text" @click="changeStatus(scope.row)">冻结</el-button>
        <el-button v-if="scope.row.status == 2" type="text" @click="changeStatus(scope.row)">解冻</el-button>
        <el-button v-if="scope.row.bizer_identity_audit_record" type="text" @click="audit">审核</el-button>
        <el-dropdown v-if="scope.row.bizer_identity_audit_record" @command="quickAudit" class="m-l-10">
            <span class="el-dropdown-link">
                <el-button type="text">快捷审核<i class="el-icon-arrow-down el-icon--right"></i></el-button>
            </span>
            <el-dropdown-menu slot="dropdown">
                <el-dropdown-item v-if="scope.row.bizer_identity_audit_record.status !== 2" command="1">审核通过</el-dropdown-item>
                <el-dropdown-item v-if="scope.row.bizer_identity_audit_record.status !== 3" command="2">审核不通过</el-dropdown-item>
            </el-dropdown-menu>
        </el-dropdown>
    </div>
</template>

<script>
    import api from '../../../assets/js/api'
    export default {
        name: "identity-item-options",
        props: {
            scope: {type: Object, required: true}
        },
        data(){
            return {

            }
        },
        methods: {
            audit(){
                router.push({
                    path: '/bizer/identity/audit',
                    query: {
                        id: this.scope.row.id,
                        isAudit: true,
                        breadcrumbsPath: this.$route.path,
                        title: '业务员身份审核',
                    }
                });
            },
            detail() {
                router.push({
                    path: '/bizer/identity/audit',
                    query: {
                        id: this.scope.row.id,
                        isAudit: '',
                        breadcrumbsPath: this.$route.path,
                        title: '业务员详情',
                    }
                });
            },
            changeStatus(row) {
                let message = row.status == 1 ? '确认冻结该业务员吗？' : '确认解冻该业务员吗？';
                this.$confirm(message, '提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    type: 'warning',
                }).then(() => {
                    api.post('/bizer/changeStatus', {id: row.id}).then(data => {
                        row.status = data.status;
                        let msg = data.status == 1 ? '解冻成功' : '冻结成功';
                        this.$message.success(msg);
                    })
                }).catch(() => {});
            },
            quickAudit(type){
                if(type == 1){
                    this.$prompt('确认审核通过吗', {
                        inputType: 'textarea',
                        inputPlaceholder: '请填写通过原因，可不填，最多50字',
                        inputValidator: (val) => {if(val && val.length > 50) return '备注不能超过50个字'}
                    }).then((val) => {
                        this.loading = true;
                        let data = {ids: this.scope.row.id, status: 2, reason: val.value};
                        api.post('/bizer/identity/audit', data).then((data) => {
                            this.$message.success('审核通过操作成功');
                            this.$emit('refresh')
                        }).finally(() => {
                            this.loading = false;
                        })
                    }).catch(() => { })
                }else {
                    this.$prompt('确认审核不通过吗', {
                        inputType: 'textarea',
                        inputPlaceholder: '请填写失败原因，可不填，最多50字',
                        inputValidator: (val) => {if(val && val.length > 50) return '备注不能超过50个字'}
                    }).then((val) => {
                        this.loading = true;
                        let data = {ids: this.scope.row.id, status: 3, reason: val.value};
                        api.post('/bizer/identity/audit', data).then((data) => {
                            this.$message.success('审核不通过操作成功');
                            this.$emit('refresh')
                        }).finally(() => {
                            this.loading = false;
                        })
                    }).catch(() => { })
                }
            }
        },
    }
</script>

<style scoped>

</style>