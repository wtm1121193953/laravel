<template>
    <page title="用户审核详情" :breadcrumbs="{用户审核列表: '/member/identity'}" v-loading="loading">
        <el-form label-width="150px" label-position="left">
            <el-form-item label="提交认证时间">
                {{info.created_at}}
            </el-form-item>
            <el-form-item prop="contacter" label="手机号">
                {{info.user.mobile}}
            </el-form-item>
            <el-form-item prop="tel" label="用户ID">
                {{info.user_id}}
            </el-form-item>

            <el-form-item prop="address" label="注册时间">
                {{info.user.created_at}}
            </el-form-item>
            <el-form-item prop="email" label="姓名">
                {{info.name}}
            </el-form-item>
            <el-form-item prop="legal_name" label="身份证号码">
                ({{info.countryName}})
                {{info.id_card_no}}
            </el-form-item>
            <el-form-item label="身份证正面" prop="front_pic">
                <div v-viewer>
                    <img v-if="info.front_pic" :src="info.front_pic" width="200px" height="100px" alt="开户许可证">
                </div>
            </el-form-item>
            <el-form-item label="身份证反面" prop="opposite_pic">
                <div v-viewer>
                    <img v-if="info.opposite_pic" :src="info.opposite_pic" width="200px" height="100px" alt="营业执照">
                </div>
            </el-form-item>

            <el-form-item prop="status" label="审核状态">
                <span v-if="parseInt(info.status) === 1">待审核</span>
                <span v-else-if="parseInt(info.status) === 2">审核通过</span>
                <span v-else-if="parseInt(info.status) === 3">审核不通过</span>
            </el-form-item>
            <el-form-item prop="email" label="审核意见">
                {{info.reason}}
            </el-form-item>
            <el-form-item>
                <el-button @click="cancel" type="primary">返 回</el-button>
            </el-form-item>
        </el-form>
    </page>
</template>

<script>
    import api from '../../../assets/js/api'

    export default {
        name: "identity-detail",
        data() {
            return {
                loading: false,
                info: {
                    created_at:'',
                    user_id:'',
                    name:'',
                    number:'',
                    front_pic:'',
                    opposite_pic:'',
                    user:{},
                },
                id: null,
            }
        },
        methods: {
            getDetail() {
                this.loading = true;
                api.get('member/identity_detail', {id: this.id}).then(data => {
                    this.info = data;
                }).finally(() => {
                    this.loading = false;
                });
            },
            cancel() {
                router.push('/member/identity');
            }
        },
        created(){
            this.id = this.$route.query.id;
            if(!this.id){
                this.$message.error('id不能为空');
                router.push('/member/identity');
                return ;
            }
            this.getDetail();
        },
    }
</script>

<style scoped>

</style>