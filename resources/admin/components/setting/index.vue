<template>
    <page title="系统设置">
        <el-form v-model="form" label-width="150px">
            <el-form-item v-for="setting in settings"
                          :label="setting.name"
                          :prop="setting.key">
                <template v-if="setting.key === 'merchant_share_in_miniprogram'">
                    <el-switch
                            v-model="form[setting.key]"
                            active-text="共享"
                            inactive-text="不共享"
                            active-value="1"
                            inactive-value="0">
                    </el-switch>
                    <div>{{setting.desc}}</div>
                </template>
                <template v-else>
                    <el-input v-model="form[setting.key]" placeholder=""/>
                    <div>{{setting.desc}}</div>
                </template>
            </el-form-item>
            <el-form-item>
                <el-button type="primary" @click="save">保存</el-button>
            </el-form-item>
        </el-form>
    </page>
</template>

<script>
    import api from '../../../assets/js/api'

    export default {
        name: "index",
        data(){
            return {
                settings: [],
                form: {
                    merchant_share_in_miniprogram: ''
                }
            }
        },
        methods: {
            save(){
                api.post('/setting/edit', this.form).then(data => {
                    this.$message.success('保存配置成功');
                    this.getSetting();
                });
            },
            getSetting(){
                api.get('/settings').then(data => {
                    this.settings = data.list;
                    this.settings.forEach(item => {
                        this.form[item.key] = item.value;
                    })
                });
            }
        },
        created(){
            this.getSetting();
        }
    }
</script>

<style scoped>

</style>