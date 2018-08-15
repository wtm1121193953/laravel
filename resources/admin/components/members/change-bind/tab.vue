<template>
    <el-tabs v-model="activeName" type="card" @tab-click="handleClick">
        <el-tab-pane label="渠道换绑" name="change_bind">
            <change-bind-list></change-bind-list>
        </el-tab-pane>
        <el-tab-pane label="换绑记录" name="change_bind_record">
            <change-bind-record></change-bind-record>
        </el-tab-pane>
    </el-tabs>
</template>

<script>
    import ChangeBindList from './list';
    import ChangeBindRecord from './record';

    export default {
        name: "change-bind-tab",
        data() {
            return {
                activeName: 'change_bind',
            }
        },
        methods: {
            handleClick(tab) {

            }
        },
        components: {
            ChangeBindList,
            ChangeBindRecord,
        }
    }
</script>

<style scoped>

</style>