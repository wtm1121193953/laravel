<template>
    <page title="">
        <el-row :span="24" >
            <h1 style="font-size:20px;text-align:center;">{{detail.title}}</h1>
        </el-row>
        <el-row :span="24">
            <p>{{detail.content}}</p>
        </el-row>
        <p style="text-align:right">{{detail.createdAt}}</p>
    </page>
</template>

<script>
    export default {
        name: "system-detail",
        props:{
            scope: {type: Object, required: true}
        },
        data(){
            return {
                detail:{
                    title:'',
                    content:'',
                    createdAt:''
                },
            }
        },
        methods: {
            init:function(data){
                this.detail.title = data.title;
                this.detail.content = data.content;
                this.detail.createdAt = data.created_at;
            }
        },
        created(){
            this.init(this.scope);
        },
        watch: {
            scope: function(data){
                this.init(data);
            }
        }
    }
</script>

<style scoped>
    p{
        white-space: pre;
        line-height: 1.5;
    }
</style>