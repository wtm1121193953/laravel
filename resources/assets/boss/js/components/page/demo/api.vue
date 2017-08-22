<template>
    <div>
        <!-- 页面路径 -->
        <el-breadcrumb class="p-b-10">
            <el-breadcrumb-item @click.native="$menu.change('/demo/index')">首页</el-breadcrumb-item>
            <el-breadcrumb-item>网络请求示例</el-breadcrumb-item>
        </el-breadcrumb>

        <!-- 页面内容部分 -->
        <el-row class="page-content" >
            <div class="page-title">
                网络请求示例
            </div>
            <el-row :gutter="25">
                <el-col class="pipeline" v-for="i in 6" :key="i" :span="4">
                    <el-card v-for="(item, index) in list" style="margin-top: 20px;" :key="item.id" v-if="index % 6 == i-1" >
                        <img width="100%" :src="item.qhimg_url" class="image">
                        <div style="padding: 14px;">
                            <span>{{item.group_title.substr(0, 10)}}</span>
                            <div style="margin-top: 13px;">
                                <el-tag
                                        style="margin: 3px 5px;"
                                        v-for="(tag, index) in (item.label).split(',')"
                                        :key="index"
                                        :type="['', 'gray', 'primary', 'success', 'warning', 'danger'][parseInt(Math.random() * 6)]"
                                        v-if="tag.length > 0">{{tag}}</el-tag>
                            </div>
                            <div style="margin-top: 13px; line-height: 12px;">
                                <time style="font-size: 13px; color: #999;">{{item.total_count}}张</time>
                                <el-button type="text" style="float: right; padding: 0;">查看套图</el-button>
                            </div>
                        </div>
                    </el-card>
                </el-col>
            </el-row>

        </el-row>
    </div>
</template>
<style>
</style>
<script>
    import api from '../../../../api';
    export default {
        data: function () {
            return {
                list: []
            }
        },
        methods: {
            getList(){
                let url = '/api/pics';
                let data = {
                    ch: 'beauty',
                    t1: 595,
                    src: 'banner_beauty',
                    eid: 't0117c39ba70f34f648.jpg',
                    sn: '30',
                    listtype: 'new',
                    temp:1
                };
                let _self = this;
                api.get(url, data).then((res) => {
                    api.handlerRes(res).then(data => {
                        _self.list = data.list;
                    })
                })
            },
            isMinPipeline(index){
                let pipelines = document.getElementsByClassName('pipeline');
                let minHeight = null;
                let minIndex = 0;
                for(let i = 0; i < pipelines.length; i ++){
                    let el = pipelines[i];
                    let height = el.scrollHeight;
                    if(minHeight == null || height < minHeight){
                        minHeight = height;
                        minIndex = i;
                    }

                    console.log(height);
                }
                console.log(minIndex);
                return minIndex == index;
                /*.forEach(el => function(){
                 console.log(el);
                 })*/
            }
        },
        computed: {

        },
        created: function () {
            this.getList();
            setTimeout(this.getList, 5000)
        }
    }
</script>