<template>
    <page :title="form.title">
        <el-form :model="form">
            <el-form-item prop="content">
                <quill-editor v-model="form.content"></quill-editor>
            </el-form-item>
            <el-form-item>
                <el-button @click="cancel">取 消</el-button>
                <el-button type="primary" @click="commit">确 定</el-button>
            </el-form-item>
        </el-form>
    </page>
</template>

<script>
    export default {
        name: "page-setting-form",
        data() {
            return {
                form: {
                    code: '',
                    title: '',
                    content: '',
                }
            }
        },
        methods: {
            cancel() {
                router.push('/setting/page_setting');
            },
            commit() {

            }
        },
        created() {
            this.form.code = this.$route.query.code;
            this.form.title = this.$route.query.title;
        }
    }
</script>

<style scoped>

</style>