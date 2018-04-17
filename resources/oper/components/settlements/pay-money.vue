<template>
    <page title="回款信息">
        <el-form>
            <el-form-item label="银行开户名：">
                {{scope.bank_open_name}}
            </el-form-item>
            <el-form-item label="公司银行账号：">
                {{scope.bank_card_no}}
            </el-form-item>
            <el-form-item label="开户支行名称：">
                {{scope.sub_bank_name}}
            </el-form-item>
            <el-form-item label="开户支行地址：">
                {{scope.bank_open_address}}
            </el-form-item>
            <el-form-item label="回款单图片：">
                <image-upload v-model="form.pay_pic_url"></image-upload>
            </el-form-item>
        </el-form>
        <div class="fl">
            <el-button @click="cancel">取消</el-button>
            <el-button type="primary" @click="save">保存</el-button>
        </div>
    </page>
</template>

<script>
    import api from '../../../assets/js/api'

    export default {
        props: {
            scope: {type: Object, required: true}
        },
        data() {
            return {
                form: {
                    id: 0,
                    pay_pic_url: '',
                },
            }
        },
        methods: {
            cancel() {
                this.$emit('cancel');
            },
            save() {
                if (!this.form.pay_pic_url){
                    this.$message.error('请上传回款单图片');
                    return false;
                }
                api.post('/updatePayPicUrl', this.form).then(data => {
                    this.$message.success('上传回款单成功');
                    this.$refs.form.resetFields();
                    this.form.pay_pic_url = '';
                    this.$emit('save');
                })
            },
            initForm(){
                this.form.id = parseInt(this.scope.id);
                this.form.pay_pic_url = this.scope.pay_pic_url;
            }
        },
        created() {
            this.initForm();
        },
        watch: {
            scope:{
                deep: true,
                handler(){
                    this.initForm();
                }
            }
        }
    }
</script>

<style scoped>

</style>