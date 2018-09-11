<template>
    <page title="发票详情">
        <div>
            <el-radio v-model="form.invoice_type" :label="1" border :disabled="uploaded">上传电子发票</el-radio>
            <el-radio v-model="form.invoice_type" :label="2" border :disabled="uploaded">寄送纸质发票</el-radio>
        </div>
        <el-col style="margin-top: 20px">
            <el-form label-width="150px" ref="form">
                <template v-if="parseInt(form.invoice_type) === 1" >
                    <el-form-item label="上传电子发票：" required>
                        <image-upload v-model="form.invoice_pic_url" :limit="2"/>
                    </el-form-item>
                </template>
                <template v-else>
                    <el-form-item prop="logistics_name" label="物流公司"  required>
                        <el-input v-model="form.logistics_name" style="width: 400px"/>
                    </el-form-item>
                    <el-form-item prop="logistics_no" label="物流单号"  required>
                        <el-input v-model="form.logistics_no" style="width: 400px"/>
                    </el-form-item>
                </template>
                <el-form-item>
                    <el-button @click="cancel">取消</el-button>
                    <el-button type="primary" @click="save">保存</el-button>
                </el-form-item>
            </el-form>
        </el-col>
    </page>
</template>

<script>
    import api from '../../../assets/js/api'

    let defaultForm = {
        id: 0,
        invoice_type: 1,
        invoice_pic_url: '',
        logistics_name: '',
        logistics_no: '',
    };

    export default {
        props: {
            scope: {type: Object, required: true}
        },
        data() {
            return {
                form: {
                    id: 0,
                    invoice_type: 1,
                    invoice_pic_url: '',
                    logistics_name: '',
                    logistics_no: '',
                },
                invoice_type: 0,    //数据库中的发票状态
                uploaded: false, // 是否已上传过发票
            }
        },
        methods: {
            cancel() {
                this.$emit('cancel');
            },
            save() {
                if (this.form.invoice_type == 1){
                    if (!this.form.invoice_pic_url){
                        this.$message.error('请添加电子发票');
                        return false;
                    }
                } else{
                    if (!this.form.logistics_name || !this.form.logistics_no){
                        this.$message.error('请填写物流公司和物流单号');
                        return false;
                    }
                }
                api.post('/updateInvoice', this.form).then(data => {
                    this.$message.success('上传发票成功');
                    this.$refs.form.resetFields();
                    this.form.invoice_pic_url = '';
                    this.$emit('save');
                })
            },
            initForm () {
                this.form = deepCopy(defaultForm)
                this.form.id = parseInt(this.scope.id);
                this.invoice_type = parseInt(this.scope.invoice_type);

                this.uploaded = parseInt(this.invoice_type) === 1 || parseInt(this.invoice_type) === 2;
                if( this.uploaded ){
                    this.form.invoice_type = this.invoice_type;
                    this.form.invoice_pic_url = this.scope.invoice_pic_url;
                    this.form.logistics_name = this.scope.logistics_name;
                    this.form.logistics_no = this.scope.logistics_no;
                }
            }
        },
        created() {
            this.initForm();
        },
        watch:{
            scope: {
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