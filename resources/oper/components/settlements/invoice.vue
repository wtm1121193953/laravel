<template>
    <page title="发票详情">
        <div>
            <el-radio v-model="form.invoice_type" :label="1" border :disabled="disable">上传电子发票</el-radio>
            <el-radio v-model="form.invoice_type" :label="2" border :disabled="disable">寄送纸质发票</el-radio>
        </div>
        <el-col style="margin-top: 20px">
            <el-form v-if="parseInt(form.invoice_type) === 1" ref="form">
                <el-form-item label="上传电子发票：">
                    <image-upload v-model="form.invoice_pic_url"></image-upload>
                </el-form-item>
            </el-form>
            <el-form label-width="70px" v-else ref="form">
                <el-form-item prop="logistics_name" label="物流公司">
                    <el-input v-model="form.logistics_name" style="width: 400px"></el-input>
                </el-form-item>
                <el-form-item prop="logistics_no" label="物流单号">
                    <el-input v-model="form.logistics_no" style="width: 400px"></el-input>
                </el-form-item>
            </el-form>
            <div class="fl">
                <el-button @click="cancel">取消</el-button>
                <el-button type="primary" @click="save">保存</el-button>
            </div>
        </el-col>
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
                    invoice_type: 1,
                    invoice_pic_url: '',
                    logistics_name: '',
                    logistics_no: '',
                },
                invoice_type: 0,    //数据库中的发票状态
                disable: false,
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
                    this.$emit('save');
                })
            }
        },
        created() {
            this.form.id = parseInt(this.scope.id);
            this.invoice_type = parseInt(this.scope.invoice_type);
            if(parseInt(this.invoice_type) !== 0){
                this.disable = true;
                this.form.invoice_type = this.invoice_type;
                this.form.invoice_pic_url = this.scope.invoice_pic_url;
                this.form.logistics_name = this.scope.logistics_name;
                this.form.logistics_no = this.scope.logistics_no;
            }
        }
    }
</script>

<style scoped>

</style>