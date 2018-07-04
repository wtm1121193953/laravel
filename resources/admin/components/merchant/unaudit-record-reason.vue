<template>
    <el-row>
        <el-col :span="24">
            <el-form  size="small">
                <!--商户录入信息左侧块-->
                <!--<el-col :span="15">-->
                    <!--<el-form-item prop="merchantName" label="商户名称">{{data.merchantName}}</el-form-item>-->
                    <!--<el-form-item prop="categoryName" label="所属行业">{{data.categoryName}}</el-form-item>-->
                <!--</el-col>-->
                <el-col >
                    <el-form-item prop="audit_suggestion" label="审核意见">{{data.audit_suggestion}}
                    </el-form-item>
                </el-col>
            </el-form>
        </el-col>
    </el-row>
</template>

<script>

    import api from '../../../assets/js/api'
    export default {
        name: 'unaudit-record-reason',
        props: {
            data: Object,
        },
        computed:{

        },
        data(){
            return {
                merchantName:'',
                categoryName:'',
                audit_suggestion:''
            }
        },
        methods: {

        },
        created(){

        },

    }

</script>

<style scoped>
    .title {
        font-weight: 600;
        line-height: 50px;
    }
</style>