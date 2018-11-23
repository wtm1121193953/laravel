<template>
    <el-row>
        <el-col :span="24">
            <el-form label-width="120px" label-position="left" size="small">
                <!--商户录入信息左侧块-->
                <el-col :span="11">
                    <el-form-item prop="name" label="商户名称">{{data.name}}</el-form-item>
                    <!--<el-form-item prop="merchant_category" label="所属行业">
                        <span v-for="item in data.categoryPath" :key="item.id">
                            {{ item.name }}
                        </span>
                    </el-form-item>-->

                </el-col>
                <el-col >
                    <el-form-item prop="audit_suggestion" label="审核意见">
                        <el-input  placeholder="最多输入50个汉字，可不填"  maxlength="50" v-model="data.audit_suggestion" :autosize="{minRows: 3}" type="textarea"/>
                    </el-form-item>
                </el-col>

                <!-- 商户激活信息右侧块 -->
                <el-col   >
                    <el-form-item >
                        <el-button @click="cancel">取消</el-button>
                        <el-button type="primary" @click="audit(data.type)">确定</el-button>
                    </el-form-item>

                </el-col>
            </el-form>

        </el-col>
    </el-row>

</template>

<script>

    import api from '../../../assets/js/api'
    export default {
        name: 'unaudit-message',
        props: {
            data: Object,
        },
        computed:{

        },
        data(){
            return {

            }
        },
        methods: {
            cancel(){
                this.$emit('cancel');
            },
            audit(type){
                api.post('/cs/merchant/audit', {id: this.data.id, type: type,audit_suggestion:this.data.audit_suggestion}).then(data => {
                    this.$message(['', '审核通过', '审核不通过', '打回商户池'][type] + ' 成功');
                    this.$emit('cancel');
                    this.$emit('change');
                })
            }
        },
        created(){

        },
        components: {

        }
    }

</script>

<style scoped>
    .title {
        font-weight: 600;
        line-height: 50px;
    }
</style>