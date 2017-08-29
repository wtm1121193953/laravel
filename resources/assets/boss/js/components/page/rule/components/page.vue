<template>
    <!-- Vue file -->
    <el-row>

        <!-- 页面路径 -->
        <el-col :span="24">
            <el-breadcrumb class="p-b-10">
                <el-breadcrumb-item
                        v-for="(value, key) in breadcrumb"
                        :key="key"
                        @click.native="typeof value == 'function' ? value() : toPath(value)"
                >
                    {{key}}
                </el-breadcrumb-item>
                <el-breadcrumb-item>{{title}}</el-breadcrumb-item>
            </el-breadcrumb>
        </el-col>

        <!-- 页面内容部分 -->
        <el-col :span="24" class="page-content">
            <el-col :span="24">
                <div class="page-title">{{title}}</div>
            </el-col>

            <slot></slot>

        </el-col>
    </el-row>
</template>
<script>
    import api from '../../../../../../js/api.js'

    /*
     * 属性列表
     *      breadcrumb: 页面面包屑, 不需要包含当前页面的标题
     *      title: 页面标题 必须
     */
    export default {
        props: {
            breadcrumb: {type: Object},
            title: { type: String, required: true },
        },
        data: function () { return {} },
        methods: {
            toPath(path){
                router.push(path);
            }
        },
        created: function () { }
    }
</script>