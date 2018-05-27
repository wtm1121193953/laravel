<template>
    <page title="系统配置">
        <el-form :model="form">
            <el-form-item label="开启单品购买功能">
                <el-switch
                    v-model="form.dishes_function"
                    active-text="开启"
                    inactive-text="关闭"
                    active-value="1"
                    inactive-value="0"
                ></el-switch>
            </el-form-item>
            <el-form-item>
                <el-button type="primary" @click="save">保 存</el-button>
            </el-form-item>
        </el-form>
    </page>
</template>

<script>
    import api from '../../../assets/js/api'

    export default {
        name: "list",
        data() {
            return {
                form: {
                    dishes_function: '1',
                },
                info: {
                    dishes_function: '单品购买功能设置',
                }
            }
        },
        methods: {
            save() {
                let param = {
                    form: this.form,
                    info: this.info,
                };
                api.post('/setting/edit', param).then(() => {
                    this.$message.success('保存成功!');
                })
            },
            getList() {
                api.get('/setting/getList').then(data => {
                    this.form = data.list;
                })
            }
        },
        created() {
            this.getList();
        }
    }
</script>

<style scoped>

</style>