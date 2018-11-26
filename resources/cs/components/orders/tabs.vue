<template>
    <page title="超市订单管理">
        <el-tabs type="card" v-model="activeName" @tab-click="tabClick">
            <el-tab-pane name="undelivered">
            <span slot="label">
                待发货 <span style="color: red">{{undeliveredNum}}</span>
            </span>
                <cs-list :activeTab="activeName" tab="undelivered" status="8" @refresh="getUndeliveredNum"></cs-list>
            </el-tab-pane>
            <el-tab-pane name="notTakeBySelf">
            <span slot="label">
                待自提 <span style="color: red">{{notTakeBySelfNum}}</span>
            </span>
                <cs-list :activeTab="activeName" tab="notTakeBySelf" status="9" @refresh="getUndeliveredNum"></cs-list>
            </el-tab-pane>
            <el-tab-pane name="delivered" label="已发货">
                <cs-list :activeTab="activeName" tab="delivered" status="10"></cs-list>
            </el-tab-pane>
            <el-tab-pane name="finished" label="已完成">
                <cs-list :activeTab="activeName" tab="finished" status="7"></cs-list>
            </el-tab-pane>
            <el-tab-pane name="refunded" label="已退款">
                <cs-list :activeTab="activeName" tab="refunded" status="6"></cs-list>
            </el-tab-pane>
            <el-tab-pane name="all" label="全部">
                <cs-list :activeTab="activeName" tab="all" status="" @refresh="getUndeliveredNum"></cs-list>
            </el-tab-pane>
        </el-tabs>
    </page>
</template>

<script>
    import api from '../../../assets/js/api'
    import CsList from './list'

    export default {
        data() {
            return {
                activeName: 'undelivered',
                undeliveredNum: 0,
                notTakeBySelfNum: 0,
            }
        },
        methods: {
            tabClick() {
                this.getUndeliveredNum();
            },
            getUndeliveredNum() {
                api.get('/orders/field/sta').then(data => {
                    this.undeliveredNum = data.undeliveredNum;
                    this.notTakeBySelfNum = data.notTakeBySelfNum;
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