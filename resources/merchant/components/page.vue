<template>
    <el-container>
        <el-header height="20px">
            <el-breadcrumb>
                <el-breadcrumb-item
                        v-for="(value, key) in breadcrumbs"
                        :key="key"
                        @click.native="typeof value === 'function' ? value() : toPath(value)"
                        style="cursor:pointer;"
                >
                    {{key}}
                </el-breadcrumb-item>
                <el-breadcrumb-item>{{title}}</el-breadcrumb-item>
            </el-breadcrumb>
        </el-header>
        <el-main>
            <slot/>
        </el-main>
    </el-container>
</template>

<script>
    export default {
        name: "page",
        props: {
            title: {type: String, required: true},
            /**
             * 数组元素 可以是一个字符串, 也可以是一个对象, 对象时包含字段为name和path
             */
            breadcrumbs: {type: Object, default: () => {} },
        },
        methods: {
            toPath(path){
                router.push(path);
            }
        },
    }
</script>

<style scoped>

</style>