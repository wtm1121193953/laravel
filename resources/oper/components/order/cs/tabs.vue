<template>
    <page title="超市订单管理">
        <el-tabs type="card" v-model="activeName" @tab-click="tabClick">
            <el-tab-pane name="all" label="全部">
                <cs-list activeTab="all" status=""></cs-list>
            </el-tab-pane>
            <el-tab-pane name="undelivered">
            <span slot="label">
                待发货 <span style="color: red">{{undeliveredNum}}</span>
            </span>
                <cs-list activeTab="undelivered" status="8"></cs-list>
            </el-tab-pane>
        </el-tabs>
    </page>
</template>

<script>
    import api from '../../../../assets/js/api'
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
                this.getUndeliveredNum();
            },
            getUndeliveredNum() {
                api.get('order/undelivered/num').then(data => {
                    this.undeliveredNum = data.total;
                })
            }
        },
        created() {
            this.getUndeliveredNum();
        },
        components: {
            CsList,
        }
    }
</script>

<style scoped>

</style>