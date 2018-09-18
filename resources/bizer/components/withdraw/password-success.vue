<template>
    <page title="提现设置">
        <el-form v-loading="formLoading" label-width="150px">
            <el-form-item label="提现银行卡号">
                {{form.bank_card_no}}
            </el-form-item>
            <el-form-item label="开户行">
                {{form.bank_name}}
            </el-form-item>
            <el-form-item label="账户姓名">
                {{form.bank_card_open_name}}
            </el-form-item>
            <el-form-item label="身份证号码">
                {{form.id_card_no}}
            </el-form-item>
            <el-form-item label="身份证正面">
                <div v-viewer>
                    <img :src="form.front_pic" alt="身份证正面" width="200px" height="100%" />
                </div>
            </el-form-item>
            <el-form-item label="身份证反面">
                <div v-viewer>
                    <img :src="form.opposite_pic" alt="身份证反面" width="200px" height="100%" />
                </div>
            </el-form-item>
        </el-form>
    </page>
</template>

<script>
    import api from '../../../assets/js/api'
    import 'viewerjs/dist/viewer.css'

    export default {
        name: "password-success",
        data() {
            return {
                formLoading: false,
                form: {
                    bank_card_no: '',
                    bank_name: '',
                    bank_card_open_name: '',
                    id_card_no: '',
                    front_pic: '',
                    opposite_pic: '',
                },
            }
        },
        methods: {
            getInfo() {
                this.formLoading = true;
                api.get('/wallet/withdraw/getBankCardAndIdCardInfo').then(data => {
                    console.log(data);
                    this.form.bank_card_no = data.bankCard.bank_card_no;
                    this.form.bank_name = data.bankCard.bank_name;
                    this.form.bank_card_open_name = data.bankCard.bank_card_open_name;
                    this.form.id_card_no = data.identityAuditRecord.id_card_no;
                    this.form.front_pic = data.identityAuditRecord.front_pic;
                    this.form.opposite_pic = data.identityAuditRecord.opposite_pic;
                    this.formLoading = false;
                });
            }
        },
        created() {
            this.getInfo();
        }
    }
</script>

<style scoped>

</style>