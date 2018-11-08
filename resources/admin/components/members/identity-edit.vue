<template>
    <page title="用户审核详情" :breadcrumbs="{用户审核列表: '/member/indetity'}" v-loading="loading">
            <template>
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
                            <el-form-item label="审核意见" prop="reason">
                                <el-input
                                        type="text"
                                        :rows="2"
                                        placeholder="最多输入50字汉字，可不填"
                                        maxlength="50"
                                        v-model="info.reason">
                                </el-input>
                            </el-form-item>


                            <el-form-item>
                                <el-button @click="cancel">取消</el-button>
                                <el-button v-if="info.status !== 2" type="primary" @click="doEdit">审核通过</el-button>
                                <el-button v-if="info.status !== 3" type="primary" @click="doReject">审核不通过</el-button>
                            </el-form-item>
                        </el-form>
            </template>

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
                    created_at:'',
                    user_id:'',
                    name:'',
                    number:'',
                    front_pic:'',
                    opposite_pic:'',
                    reason:'',
                    user:{},
                },
                id: null,
            }
        },
        methods: {
            cancel(){
                router.push('/member/identity');
            },
            getDetail(){
                this.loading = true;
                api.get('member/identity_detail', {id: this.id}).then(data => {
                    this.info = data;
                }).finally(() => {
                    this.loading = false;
                });
            },
            doEdit(){
                this.loading = true;
                let data = {id:this.id,status:2,reason:this.info.reason}
                api.post('/member/identity_do', data).then((data) => {
                    router.push('/member/identity');
                }).finally(() => {
                    this.loading = false;
                })
            },
            doReject(){
                this.loading = true;
                let data = {id:this.id,status:3,reason:this.info.reason}
                api.post('/member/identity_do', data).then((data) => {
                    router.push('/member/identity');
                }).finally(() => {
                    this.loading = false;
                })
            },
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