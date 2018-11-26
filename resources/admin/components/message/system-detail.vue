<template>
    <page title="">
        <el-row :span="24" >
            <h1 style="font-size:20px;text-align:center;">{{detail.title}}</h1>
        </el-row>
        <el-row :span="24">
            <p>{{detail.content}}</p>
        </el-row>
        <p>
            <b>接收角色：</b>
            <span v-for="object in detail.objectType">
                <el-tag>{{ object }}</el-tag>
            </span>
        </p>
        <p style="text-align:right">{{detail.createdAt}}</p>
    </page>
</template>

<script>
    let temp = [];
    temp[1] = '用户端';
    temp[2] = '商户端';
    temp[3] = '业务员';
    temp[4] = '运营中心';
    temp[5] = '超市商户端';
    const GuestTypeName = temp;
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
                    objectType:[],
                    createdAt:''
                },
            }
        },
        methods: {
            init:function(data){
                this.detail.title = data.title;
                this.detail.content = data.content;
                this.detail.createdAt = data.created_at;
                let arr = data.object_type.split(',');
                let length = arr.length;
                let objectType = [];

                for (let i=0; i<length; i++){
                    objectType[i] = GuestTypeName[arr[i]];
                }

                this.detail.objectType = objectType;
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
        line-height: 1.5;
        white-space: pre-line;
    }
</style>