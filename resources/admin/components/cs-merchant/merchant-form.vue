<template>
    <el-form size="small" label-width="120px">
        <!-- 商户录入表单 -->
        <merchant-pool-info-form
                :data="poolInfoData || data"
                :readonly="poolInfoReadonly"
                :areaOptions="areaOptions"
                ref="poolForm"
        />
        <!-- 商户激活表单 -->
        <merchant-active-info-form
                :data="activeInfoData || data"
                :areaOptions="areaOptions"
                ref="activeForm"
        />
        <!-- 按钮区域 -->
        <el-col>
            <el-form-item>
                <el-button @click="cancel">取消</el-button>
                <el-button type="success" @click="save">保存</el-button>
            </el-form-item>
        </el-col>
    </el-form>
</template>
<script>
    import api from '../../../assets/js/api';
    import MerchantPoolInfoForm from './merchant-pool-info-form'
    import MerchantActiveInfoForm from './merchant-active-info-form'

    export default {
        name: 'merchant-form',
        props: {
            data: Object,
            poolInfoData: Object,
            activeInfoData: Object,
            poolInfoReadonly: {type: Boolean, default: false}, // 商户录入信息是否只读
            isDraft: {type: Boolean, default: true}
        },
        computed:{

        },
        data(){
            return {
                areaOptions: [],
            }
        },
        methods: {
            cancel(){
                this.$emit('cancel')
            },
            resetForm(){
                this.$refs.poolForm.resetForm();
                this.$refs.activeForm.resetForm();
            },
            save(){
                let poolForm = this.$refs.poolForm;
                let activeForm = this.$refs.activeForm;

                let data = poolForm.getData();

                if(this.data && this.data.id){
                    data.id = this.data.id;
                    data.audit_oper_id = this.data.audit_oper_id;
                    data.audit_status = this.data.audit_status;
                }

                data.merchant_category_id = (data.merchant_category.length != 0) ? data.merchant_category[data.merchant_category.length - 1] : 0;
                data.province_id = data.area[0];
                data.city_id = data.area[1];
                data.area_id = data.area[2];
                if(data.lng_and_lat){
                    data.lng = data.lng_and_lat[0];
                    data.lat = data.lng_and_lat[1];
                }

                poolForm.validate(() => {
                    activeForm.validate(() => {
                        Object.assign(data, activeForm.getData())
                        this.$emit('save', data)
                    })
                })

            },
            saveIntoDraft() {
                let poolForm = this.$refs.poolForm;
                let activeForm = this.$refs.activeForm;
                let data = poolForm.getData();
                Object.assign(data, activeForm.getData())
                this.$emit('saveDraft', data)
            }
        },
        created(){
            api.get('area/tree').then(data => {
                this.areaOptions = data.list;
            });
        },
        watch: {
        },
        components: {
            MerchantPoolInfoForm,
            MerchantActiveInfoForm,
        }
    }
</script>
<style scoped>
    .title {
        line-height: 60px;
        text-align: left;
    }
</style>
