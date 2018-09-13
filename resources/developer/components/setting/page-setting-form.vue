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
    import api from '../../../assets/js/api'

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
                api.post('setting/setArticle', this.form).then(() => {
                    this.$message.success('编辑成功');
                    router.push('/setting/page_setting');
                })
            },
            getArticle(code) {
                api.get('setting/getArticle', {code: code}).then(data => {
                    if (data.article){
                        this.form.content = data.article.content;
                    }
                })
            }
        },
        created() {
            this.form.code = this.$route.query.code;
            this.form.title = this.$route.query.title;
            this.getArticle(this.form.code);
        }
    }
</script>

<style scoped>
    .quill-editor {
        height: 500px;
        width: 1000px;
        margin-bottom: 50px;
    }
</style>