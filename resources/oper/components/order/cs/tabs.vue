<template>
    <page title="超市订单管理">
        <el-tabs type="card" v-model="activeName" @tab-click="tabClick">
            <el-tab-pane name="all" label="全部">
                <cs-list></cs-list>
            </el-tab-pane>
            <el-tab-pane name="undelivered">
            <span slot="label">
                待发货 <span style="color: red">{{undeliveredNum}}</span>
            </span>
            </el-tab-pane>
        </el-tabs>
    </page>
</template>

<script>
    import CsList from './list'

    export default {
        data() {
            return {
                activeName: 'all',
                undeliveredNum: 0,
            }
        },
        methods: {
            tabClick() {

            }
        },
        components: {
            CsList,
        }
    }
</script>

<style scoped>

</style>